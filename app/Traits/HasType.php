<?php

namespace App\Traits;

use App\Models\Dictionaries\AbstractDictionary;

trait HasType
{
    /**
     * Check and set new status for the model having `status_id` attribute.
     *
     * @param string $class
     * @param int|AbstractDictionary $type
     * @param string $exception
     * @param bool $save
     *
     * @return  void
     *
     */
    protected function checkAndSetType(string $class, $type, string $exception, bool $save = true): void
    {
        /** @var AbstractDictionary $class */

        if (is_int($type)) {
            $type = $class::get($type);
        }

        if ($type === null && $this->hasNullType()) {

            $this->type_id = null;

        } else if ($type === null || !$type->exists) {

            throw new $exception;
        } else {

            $this->type_id = $type->id;
        }

        if ($save) {
            $this->save();
        }
    }

    /**
     * Check if the model has the status.
     *
     * @param int|AbstractDictionary $type
     *
     * @return  bool
     */
    public function hasType($type): bool
    {
        if (is_int($type)) {
            return $this->type_id === $type;
        }

        if ($type === null && $this->hasNullType()) {
            return $this->type_id === null;
        }

        return ($this->type !== null) && ($type !== null) && ($this->type->id === $type->id);
    }

    /**
     * Weather model has null value for type.
     * Disabled by default.
     *
     * @return  bool
     */
    public function hasNullType(): bool
    {
        return $this->canHasNullType ?? false;
    }
}
