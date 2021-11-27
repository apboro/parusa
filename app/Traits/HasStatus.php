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
     *
     * @return  void
     *
     * @throws WrongStatusException
     */
    protected function checkAndSetStatus(string $class, $status, string $exception, bool $save = true): void
    {
        /** @var AbstractDictionary $class */
        if (is_int($status)) {
            $status = $class::get($status);
        }

        if ($status === null || !$status->exists) {
            throw new $exception;
        }

        $this->setAttribute('status_id', $status->id);

        if ($save) {
            $this->save();
        }
    }

    /**
     * Check if the model has the status.
     *
     * @param int|AbstractDictionary $status
     *
     * @return  bool
     */
    public function hasStatus($status): bool
    {
        if (is_int($status)) {
            return $this->status_id === $status;
        }

        return $this->status->id === $status->id;
    }
}
