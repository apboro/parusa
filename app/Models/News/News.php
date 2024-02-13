<?php

namespace App\Models\News;

use App\Models\Common\Image;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class News extends Model
{
    use HasFactory;

    public function images(): BelongsToMany
    {
        return $this->belongsToMany(Image::class, 'news_has_image', 'news_id', 'image_id');
    }
}
