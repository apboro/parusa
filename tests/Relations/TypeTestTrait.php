<?php

namespace Tests\Relations;

use App\Interfaces\Typeable;
use App\Models\Dictionaries\AbstractDictionary;
use App\Models\Model;
use Exception;
use Tests\HelperTraits\GetsFreshModel;

trait TypeTestTrait
{
    use GetsFreshModel;

    protected function runTypeTests(string $modelClass, string $typeClass, string $exceptionClass, ?int $typeDefault, $factory = null): void
    {
        /** @var Model|Typeable $model */
        $model = $this->freshModel($modelClass, $factory, ['type_id' => $typeDefault]);

        // default type
        self::assertEquals(true, $model->hasType($typeDefault), "Error saving type for [$modelClass]");

        // test for all typees
        /** @var AbstractDictionary $typeClass */
        $all = $typeClass::all();
        foreach ($all as $type) {
            /** @var AbstractDictionary $type */
            $model->setType($type);
            $model->refresh();
            self::assertEquals(true, $model->hasType($type), "Error saving type [$type->id] for [$modelClass]");
        }

        // test type by id
        $type = $typeClass::query()->first();
        $model->setType($type->id);
        $model->refresh();
        self::assertEquals(true, $model->hasType($type), "Error saving type by id [$type->id] for [$modelClass]");

        // test wrong type by id
        $exception = null;
        try {
            $model->setType(0);
        } catch (Exception $e) {
            $exception = $e;
        }
        self::assertEquals($exceptionClass, get_class($exception), "Wrong type for [$modelClass] must throw exception for [$exceptionClass]");

        // test wrong type
        $exception = null;
        try {
            $model->setType(new $typeClass);
        } catch (Exception $e) {
            $exception = $e;
        }
        self::assertEquals($exceptionClass, get_class($exception), "Wrong type for [$modelClass] must throw exception for [$exceptionClass]");

        // test null type
        if ($model->hasNullType()) {
            $model->setType(null);
            $model->refresh();
            self::assertEquals(null, $model->getAttribute('type_id'), "Error saving null type value for [$modelClass]");
            self::assertEquals(true, $model->hasType(null), "Error for checking null type value for [$modelClass]");
        } else {
            $exception = null;
            try {
                $model->setType(new $typeClass);
            } catch (Exception $e) {
                $exception = $e;
            }
            self::assertEquals($exceptionClass, get_class($exception), "Wrong type for [$modelClass] must throw exception for [$exceptionClass]");
        }
    }
}
