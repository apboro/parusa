<?php

namespace Tests\Relations;

use App\Interfaces\Statusable;
use App\Models\Dictionaries\AbstractDictionary;
use App\Models\Model;
use Exception;
use Tests\HelperTraits\GetsFreshModel;

trait StatusTestTrait
{
    use GetsFreshModel;

    protected function runStatusTests(string $modelClass, string $statusClass, string $exceptionClass, int $statusDefault, $factory = null): void
    {
        /** @var Model|Statusable $model */
        $model = $this->freshModel($modelClass, $factory);

        // default status
        self::assertEquals(true, $model->hasStatus($statusDefault), "Wrong default status for new [$modelClass]");

        // test for all statuses
        /** @var AbstractDictionary $statusClass */
        $all = $statusClass::all();
        foreach ($all as $status) {
            /** @var AbstractDictionary $status */
            $model->setStatus($status);
            $model->refresh();
            self::assertEquals(true, $model->hasStatus($status), "Error saving status [$status->id] for [$modelClass]");
        }

        // test status by id
        $status = $statusClass::query()->first();
        $model->setStatus($status->id);
        $model->refresh();
        self::assertEquals(true, $model->hasStatus($status), "Error saving status by id [$status->id] for [$modelClass]");

        // test wrong status by id
        $exception = null;
        try {
            $model->setStatus(0);
        } catch (Exception $e) {
            $exception = $e;
        }
        self::assertEquals($exceptionClass, get_class($exception), "Wrong status for [$modelClass] must throw exception for [$exceptionClass]");

        // test wrong status
        $exception = null;
        try {
            $model->setStatus(new $statusClass);
        } catch (Exception $e) {
            $exception = $e;
        }
        self::assertEquals($exceptionClass, get_class($exception), "Wrong status for [$modelClass] must throw exception for [$exceptionClass]");

        // test null status
        $exception = null;
        try {
            $model->setStatus(null);
        } catch (Exception $e) {
            $exception = $e;
        }
        self::assertEquals($exceptionClass, get_class($exception), "Null status for [$modelClass] must throw exception for [$exceptionClass]");
    }
}
