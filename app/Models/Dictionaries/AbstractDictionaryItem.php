<?php

namespace App\Models\Dictionaries;

use App\Models\Dictionaries\Traits\GetById;
use App\Models\Model;

abstract class AbstractDictionaryItem extends Model
{
    use GetById;
}
