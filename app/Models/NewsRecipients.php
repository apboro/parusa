<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsRecipients extends Model
{
    const PARTNERS = 1;
    const CLIENTS = 5;

    protected $table = 'dictionary_news_recipients';
}
