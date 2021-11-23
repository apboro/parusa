<?php

namespace App\Models\Traits;

trait HasStatus
{
    /**
     * Check and set new status for the model having `status_id` attribute.
     *
     * @param string $status
     * @param int $id
     * @param string $exception
     * @param bool $save
     *
     * @return  void
     *
     */
    protected function checkAndSetStatus(string $status, int $id, string $exception, bool $save = true): void
    {
        /** @var \App\Models\Dictionaries\AbstractDictionaryItem $status */
        $status = $status::get($id);

        if ($status === null) {
            throw new $exception;
        }

        $this->setAttribute('status_id', $status->id);

        if ($save) {
            $this->save();
        }
    }
}
