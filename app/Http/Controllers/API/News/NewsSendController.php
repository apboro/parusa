<?php

namespace App\Http\Controllers\API\News;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Jobs\SendNewsEmailJob;
use App\Models\Dictionaries\NewsStatus;
use App\Models\News\News;
use App\Models\Partner\Partner;
use App\Models\User\User;
use Illuminate\Http\Request;

class NewsSendController extends ApiEditController
{
    public function send(Request $request)
    {
        $news = News::findOrFail($request->get('id'));
        $news->update(['send_at' => now(), 'status_id' => NewsStatus::SENT]);

        $partners = Partner::with(['positions', 'positions.user.profile'])->get();

        foreach ($partners as $partner) {
            foreach ($partner->positions as $position) {
                $email = $position->user->profile->email;
                if ($email) {
                    SendNewsEmailJob::dispatch($email, $news, $partner)->delay(rand(5, 10));
                }
            }
        }

        return APIResponse::response([], [], 'Новость успешно отправлена');
    }

}
