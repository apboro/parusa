<?php

namespace App\Http\Controllers\API\News;

use App\Http\Controllers\ApiEditController;
use App\Models\Common\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NewsUploadController extends ApiEditController
{

    public function images(Request $request)
    {
        $imagePath = Storage::disk('public_images')->putFile('news', $request->file('upload'));

        return response()->json(['url' => Storage::disk('public_images')->url($imagePath)]);
    }

}
