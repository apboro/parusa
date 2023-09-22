<?php

namespace Tests\Relations\Account;

use App\Exceptions\Account\WrongAccountTransactionStatusException;
use App\Exceptions\Account\WrongAccountTransactionTypeException;
use App\Models\Account\Account;
use App\Models\Account\AccountTransaction;
use App\Models\Dictionaries\AccountTransactionStatus;
use App\Models\Dictionaries\AccountTransactionType;
use App\Models\Partner\Partner;
use App\Models\Positions\Position;
use App\Models\User\User;
use Illuminate\Database\QueryException;
use Tests\Relations\StatusTestTrait;
use Tests\Relations\TypeTestTrait;
use Tests\TestCase;

class AccountTransactionTest extends TestCase
{
    use StatusTestTrait, TypeTestTrait;

    /**
     * Local user factory.
     *
     * @param array|null $arguments
     *
     * @return AccountTransaction
     */
    protected function makeTransaction(?array $arguments = null): AccountTransaction
    {
        // create partner
        /** @var Partner $partner */
        $partner = Partner::factory()->create();

        // create account
        /** @var Account $account */
        $account = $partner->account()->create();


        //create transaction for account
        $transaction = new AccountTransaction();
        $transaction->account_id = $account->id;
        $transaction->type_id = AccountTransactionType::account_refill;
        $transaction->amount = 0;
        if ($arguments !== null) {
            foreach ($arguments as $key => $argument) {
                $transaction->setAttribute($key, $argument);
            }
        }
        $transaction->save();

        return $transaction;
    }

    public function testTransactionStatus(): void
    {
        $this->runStatusTests(
            AccountTransaction::class,
            AccountTransactionStatus::class,
            WrongAccountTransactionStatusException::class,
            AccountTransactionStatus::default,
            [$this, 'makeTransaction']
        );
    }

    public function testPartnerTypes(): void
    {
        $this->runTypeTests(
            AccountTransaction::class,
            AccountTransactionType::class,
            WrongAccountTransactionTypeException::class,
            AccountTransactionType::account_refill,
            [$this, 'makeTransaction']
        );
    }

    public function testLocking(): void
    {
        /** @var User $user */
        $position = Position::factory()->create();

        $transaction = $this->makeTransaction(['committer_id' => $position->id]);

        $account = $transaction->account;

        // try to delete user
        $deleted = null;
        try {
            $position->user->delete();
            $deleted = true;
        } catch (QueryException $e) {
            $deleted = false;
        }
        $this->assertEquals(false, $deleted, "User deletion must be blocked by AccountTransaction");
        // try to delete account
        $deleted = null;
        try {
            $account->delete();
            $deleted = true;
        } catch (QueryException $e) {
            $deleted = false;
        }
        $this->assertEquals(false, $deleted, "Account deletion must be blocked by AccountTransaction");
    }
}
