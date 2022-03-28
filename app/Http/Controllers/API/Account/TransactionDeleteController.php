<?php

namespace App\Http\Controllers\API\Account;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Account\AccountTransaction;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionDeleteController extends ApiController
{
    /**
     * Delete partner.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $id = $request->input('id');

        if ($id === null || null === ($transaction = AccountTransaction::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Операция не найдена');
        }

        /** @var AccountTransaction $transaction */

        if (!$transaction->type->deletable) {
            return APIResponse::error('Невозможно удалить операцию.');
        }

        try {
            $transaction->account->detachTransaction($transaction);
        } catch (QueryException $exception) {
            return APIResponse::error('Невозможно удалить операцию. Есть блокирующие связи.');
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::success('Операция удалена');
    }
}
