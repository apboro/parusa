<?php

namespace Tests\Relations;

use App\Exceptions\Base\WrongStatusException;
use App\Interfaces\Statusable;
use App\Models\Dictionaries\AbstractDictionary;
use App\Models\Model;
use Exception;

trait StatusTestTrait
{
    private function freshModel(string $modelClass, $factory = null): Statusable
    {
        if ($factory !== null) {
            return call_user_func($factory);
        }

        return new $modelClass;
    }

    protected function runStatusTests(string $modelClass, string $statusClass, string $exceptionClass, int $statusDefault, $factory = null): void
    {
        /** @var Model|Statusable $model */
        $model = $this->freshModel($modelClass, $factory);

        // default status
        self::assertEquals(true, $model->hasStatus($statusDefault), "Wrong default status for new [$modelClass]");

        // saved with default status
        $model->save();
        self::assertEquals(true, $model->hasStatus($statusDefault), "Error saving status for [$modelClass]");

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
        }catch (Exception $e) {
            $exception = $e;
        }
        self::assertEquals($exceptionClass, get_class($exception), "Wrong status for [$modelClass] must throw exception for [$exceptionClass]");

        // test wrong status
        $exception = null;
        try {
            $model->setStatus(new $statusClass);
        }catch (Exception $e) {
            $exception = $e;
        }
        self::assertEquals($exceptionClass, get_class($exception), "Wrong status for [$modelClass] must throw exception for [$exceptionClass]");

    }
}
