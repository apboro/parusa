<?php

namespace App\Interfaces;

use App\Exceptions\Base\WrongTypeException;
use App\Models\Dictionaries\AbstractDictionary;
use Illuminate\Database\Eloquent\Relations\HasOne;

interface Typeable
{
    /**
     * Status of the model.
     *
     * @return  HasOne
     */
    public function type(): HasOne;

    /**
     * Weather model has null value for type.
     *
     * @return  bool
     */
    public function hasNullType(): bool;

    /**
     * Check and set new type for model.
     *
     * @param int|AbstractDictionary|null $type
     * @param bool $save
     *
     * @return  void
     *
     * @throws WrongTypeException
     */
    public function setType($type, bool $save = true): void;

    /**
     * Check if the model has the type.
     *
     * @param int|AbstractDictionary|null $type
     *
     * @return  void
     */
    public function hasType($type): bool;
}
