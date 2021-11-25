<?php

namespace App\Models\Traits;

use App\Models\Dictionaries\AbstractDictionaryItem;

trait HasType
{
    /**
     * Check and set new type for the model having `type_id` attribute.
     *
     * @param string $type
     * @param int $id
     * @param string $exception
     * @param bool $save
     *
     * @return  void
     *
     */
    protected function checkAndSetType(string $type, int $id, string $exception, bool $save = true): void
    {
        /** @var AbstractDictionaryItem $type */
        $type = $type::get($id);

        if ($type === null) {
            throw new $exception;
        }

        $this->setAttribute('type_id', $type->id);

        if ($save) {
            $this->save();
        }
    }
}
