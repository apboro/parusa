<?php

namespace App\Http\Controllers\API\Account;

use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Account\AccountTransaction;
use App\Models\Dictionaries\AccountTransactionType;
use App\Models\Partner\Partner;
use App\Models\User\Helpers\Currents;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class TransactionsListController extends ApiController
{
    protected array $defaultFilters = [
        'date_from' => null,
        'date_to' => null,
        'transaction_type_id' => null,
    ];

    protected array $rememberFilters = [
    ];

    protected string $rememberKey = CookieKeys::transactions_list;

    /**
     * Get transactions list.
     *
     * @param ApiListRequest $request
     *
     * @return  JsonResponse
     */
    public function list(APIListRequest $request): JsonResponse
    {
        $current = Currents::get($request);

        $id = $current->isStaff() ? $request->input('id') : $current->partnerId();

        /** @var Partner $partner */
        if (null === ($partner = Partner::query()->with(['account'])->where('id', $id)->first())) {
            return APIResponse::notFound('Партнёр не найден');
        }

        $account = $partner->account;

        $query = $account->transactions()->with(['committer', 'committer.user.profile']);

        $this->defaultFilters['date_from'] = Carbon::now()->day(1)->format('Y-m-d');
        $this->defaultFilters['date_to'] = Carbon::now()->format('Y-m-d');

        $filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey);

        // apply filters
        if (!empty($filters['date_from'])) {
            $query->whereDate('timestamp', '>=', Carbon::parse($filters['date_from']));
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('timestamp', '<=', Carbon::parse($filters['date_to']));
        }
        $transactionTypes = null;
        if (!empty($filters['transaction_type_id'])) {
            if (null === ($transactionType = AccountTransactionType::get($filters['transaction_type_id']))) {
                return APIResponse::notFound('Неверный тип транзакции');
            }
            /** @var AccountTransactionType $transactionType */
            if ($transactionType->final) {
                $query->where('type_id', $transactionType->id);
                $transactionTypes = [$transactionType->id];
            } else {
                $ids = AccountTransactionType::query()->where('parent_type_id', $transactionType->id)->pluck('id')->toArray();
                $query->whereIn('type_id', $ids);
                $transactionTypes = $ids;
            }
        }

        // current page automatically resolved from request via `page` parameter
        $transactions = $query->orderBy('timestamp', 'desc')->orderBy('id', 'desc')->paginate($request->perPage(10, $this->rememberKey));

        $pageTotal = 0;

        /** @var LengthAwarePaginator $transactions */
        $transactions->transform(function (AccountTransaction $transaction) use (&$pageTotal) {
            $pageTotal += $transaction->type->sign * $transaction->amount;
            return [
                'id' => $transaction->id,
                'sign' => $transaction->type->sign,
                'timestamp' => $transaction->timestamp->format('d.m.Y H:i'),
                'date' => $transaction->timestamp->format('Y-m-d'),
                'type_id' => $transaction->type_id,
                'type' => $transaction->type->name,
                'amount' => $transaction->amount,
                'reason' => $transaction->reason,
                'reason_date' => $transaction->reason_date ? $transaction->reason_date->format('Y-m-d') : null,
                'reason_title' => $transaction->reasonTitle,
                'reason_raw' => $transaction->reasonRaw,
                'comments' => $transaction->comments,
                'operator' => $transaction->committer ? $transaction->committer->user->profile->compactName : null,
                'editable' => $transaction->type->editable,
                'deletable' => $transaction->type->deletable,
            ];
        });

        $fromDate = Carbon::parse($filters['date_from'])->hour(0)->minute(0)->second(-1);
        $toDate = Carbon::parse($filters['date_to'])->hour(23)->minute(59)->second(59);

        $periodStartAmount = $account->calcAmount($fromDate);
        $periodEndAmount = $account->calcAmount($toDate);

        $total = $account->calcAmount($toDate, $fromDate, $transactionTypes);

        return APIResponse::list(
            $transactions,
            [
                'date' => 'Дата и время операции',
                'type' => 'Тип операции',
                'amount' => 'Сумма, руб.',
                'reason' => 'Основание',
                'operator' => 'Оператор',
            ],
            $filters,
            $this->defaultFilters,
            [
                'selected_page_total' => $pageTotal,
                'selected_total' => $total,
                'balance' => $account->amount,
                'limit' => $account->limit,
                'period_start_amount' => $periodStartAmount,
                'period_end_amount' => $periodEndAmount,
                'period_income_amount' => $periodEndAmount - $periodStartAmount,
                'period_sell_amount' => 0,
                'period_commission_amount' => 0,
                'period_sell_orders' => 0,
                'period_sell_tickets' => 0,
            ])->withCookie(cookie($this->rememberKey, $request->getToRemember()));
    }
}
