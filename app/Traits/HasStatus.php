<?php

namespace App\Traits;

use App\Exceptions\Base\WrongStatusException;
use App\Models\Dictionaries\AbstractDictionary;

trait HasStatus
{
    /**
     * Check and set new status for the model having `status_id` attribute.
     *
     * @param string $class
     * @param int|AbstractDictionary $status
     * @param string $exception
     * @param bool $save
     * @param string $name
     *
     * @return  void
     *
     */
    protected function checkAndSetStatus(string $class, $status, string $exception, bool $save = true, string $name = 'status_id'): void
    {
        /** @var AbstractDictionary $class */
        if (is_string($status) || is_int($status)) {
            $status = $class::get((int)$status);
        }

        if ($status === null || !$status->exists) {
            throw new $exception;
        }

        $this->setAttribute($name, $status->id);

        if ($save) {
            $this->save();
        }
    }

    /**
     * Check if the model has the status.
     *
     * @param int|AbstractDictionary $status
     * @param string $name
     *
     * @return  bool
     */
    public function hasStatus($status, string $name = 'status_id'): bool
    {
        if (is_string($status) || is_int($status)) {
            return (int)$this->getAttribute($name) === (int)$status;
        }

        return (int)$this->getAttribute($name) === (int)$status->id;
    }
}
