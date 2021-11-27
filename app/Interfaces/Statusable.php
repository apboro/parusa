<?php

namespace App\Interfaces;

use App\Exceptions\Base\WrongStatusException;
use App\Models\Dictionaries\AbstractDictionary;
use Illuminate\Database\Eloquent\Relations\HasOne;

interface Statusable
{
    /**
     * Status of the model.
     *
     * @return  HasOne
     */
    public function status(): HasOne;

    /**
     * Check and set new status for model.
     *
     * @param int|AbstractDictionary $status
     * @param bool $save
     *
     * @return  void
     *
     * @throws WrongStatusException
     */
    public function setStatus($status, bool $save = true): void;

    /**
     * Check if the model has the status.
     *
     * @param int|AbstractDictionary $status
     *
     * @return  void
     */
    public function hasStatus($status): bool;
}
