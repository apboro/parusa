<?php

namespace App\Http\Controllers\API\Account;

use App\Exceptions\Account\AccountException;
use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\Account\AccountTransaction;
use App\Models\Dictionaries\AccountTransactionType;
use App\Models\Dictionaries\HitSource;
use App\Models\Hit\Hit;
use App\Models\Partner\Partner;
use App\Models\User\Helpers\Currents;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountSubtractController extends ApiEditController
{
    protected array $rules = [
        'amount' => 'required|numeric|min:1',
        'timestamp' => 'required',
        'reason' => 'required',
        'reason_date' => 'required',
    ];

    protected array $titles = [
        'amount' => 'Допустимый остаток по счёту',
        'timestamp' => 'Дата операции',
        'reason' => 'Номер счёта',
        'reason_date' => 'Дата счёта',
    ];

    /**
     * Deduct from the balance
     *
     * @param Request $request
     *
     * @return  JsonResponse
     *
     * @throws AccountException
     */
    public function subtract(Request $request): JsonResponse
    {
        Hit::register(HitSource::admin);
        $id = $request->input('partnerId');
        $transactionId = $request->input('transactionId');

        /** @var Partner $partner */
        if ($id === null || null === ($partner = Partner::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Партнёр не найден');
        }

        /** @var AccountTransaction $transaction */
        $transaction = AccountTransaction::query()->where('id', $transactionId)->first();

        $data = $this->getData($request);

        $typeID = AccountTransactionType::account_write_balance_refill;

        /** @var AccountTransactionType $type */
        $type = AccountTransactionType::get($typeID);

        if (!$type->editable || ($transaction && !$transaction->type->editable)) {
            return APIResponse::error('Этот тип операций нельзя редактировать.');
        }

        if ($type->has_reason) {
            $this->rules['reason'] = 'required';
            $this->titles['reason'] = $type->reason_title;
        }
        if ($type->has_reason_date) {
            $this->rules['reason_date'] = 'required';
            $this->titles['reason_date'] = $type->reason_date_title;
        }

        if ($errors = $this->validate($data, $this->rules, $this->titles)) {
            return APIResponse::validationError($errors);
        }

        $timestamp = Carbon::parse($data['timestamp']);
        if ($timestamp->isToday()) {
            $now = Carbon::now();
            $timestamp->hours($now->hour);
            $timestamp->minutes($now->minute);
        }

        try {
            if (!$transaction) {
                $partner->account->attachTransaction(new AccountTransaction([
                    'type_id' => $typeID,
                    'timestamp' => $timestamp,
                    'amount' => $data['amount'],
                    'reason' => $type->has_reason ? $data['reason'] : null,
                    'reason_date' => $type->has_reason_date ? $data['reason_date'] : null,
                    'committer_id' => Currents::get($request)->positionId(),
                    'comments' => $data['comments'],
                ]));
            } else {
                $partner->account->updateTransaction($transaction, [
                    'type_id' => $typeID,
                    'timestamp' => $timestamp,
                    'amount' => $data['amount'],
                    'reason' => $type->has_reason ? $data['reason'] : null,
                    'reason_date' => $type->has_reason_date ? $data['reason_date'] : null,
                    'committer_id' => Currents::get($request)->positionId(),
                    'comments' => $data['comments'],
                ]);
            }
        } catch (AccountException $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::success('Данные обновлены', [
            'id' => $partner->id,
        ]);
    }
}
