<?php

namespace App\Http\Controllers\API\Account;

use App\Exceptions\Account\AccountException;
use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\Account\AccountTransaction;
use App\Models\Dictionaries\AccountTransactionType;
use App\Models\Partner\Partner;
use App\Models\User\Helpers\Currents;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountRefillController extends ApiEditController
{
    protected array $rules = [
        'type_id' => 'required',
        'amount' => 'required|numeric|gt:zero',
        'timestamp' => 'required',
    ];

    protected array $titles = [
        'type_id' => 'Допустимый остаток по счёту',
        'amount' => 'Допустимый остаток по счёту',
        'timestamp' => 'Дата операции',
    ];

    /**
     * Set tickets for guides quantity.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     *
     * @throws AccountException
     */
    public function refill(Request $request): JsonResponse
    {
        $id = $request->input('partnerId');
        $transactionId = $request->input('transactionId');

        /** @var Partner $partner */
        if ($id === null || null === ($partner = Partner::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Партнёр не найден');
        }

        /** @var AccountTransaction $transaction */
        $transaction = AccountTransaction::query()->where('id', $transactionId)->first();

        $data = $this->getData($request);
        $data['zero'] = 0;

        /** @var AccountTransactionType $type */
        $type = AccountTransactionType::get($data['type_id']);

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
            return APIResponse::formError($data, $this->rules, $this->titles, $errors);
        }

        if (!$transaction) {
            $partner->account->attachTransaction(new AccountTransaction([
                'type_id' => $data['type_id'],
                'timestamp' => $data['timestamp'],
                'amount' => $data['amount'],
                'reason' => $type->has_reason ? $data['reason'] : null,
                'reason_date' => $type->has_reason_date ? $data['reason_date'] : null,
                'committer_id' => Currents::get($request)->positionId(),
                'comments' => $data['comments'],
            ]));
        } else {
            $partner->account->updateTransaction($transaction, [
                'type_id' => $data['type_id'],
                'timestamp' => $data['timestamp'],
                'amount' => $data['amount'],
                'reason' => $type->has_reason ? $data['reason'] : null,
                'reason_date' => $type->has_reason_date ? $data['reason_date'] : null,
                'committer_id' => Currents::get($request)->positionId(),
                'comments' => $data['comments'],
            ]);
        }

        return APIResponse::formSuccess('Данные обновлены', [
            'id' => $partner->id,
        ]);
    }
}
