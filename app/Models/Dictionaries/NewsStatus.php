<?php

namespace App\Models\Dictionaries;

use Illuminate\Database\Eloquent\Model;

class NewsStatus extends Model
{
    const DRAFT = 1;
    const SENT = 5;

    protected $table = 'dictionary_news_statuses';
    protected $guarded = [];
}
