<?php

namespace App\Http\Controllers\API\News;

use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\ApiEditController;
use App\Http\Requests\APIListRequest;
use App\Jobs\SendNewsEmailJob;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\NewsStatus;
use App\Models\Hit\Hit;
use App\Models\News\News;
use App\Models\NewsRecipients;
use App\Models\Partner\Partner;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class NewsController extends ApiEditController
{
    protected array $rules = [
        'title' => 'required',
        'description' => 'nullable',
    ];

    protected array $titles = [
        'title' => 'Название',
        'description' => 'Текст',
        'recipients' => 'Получатели'
    ];
    protected array $defaultFilters = [
        'status_id' => NewsStatus::DRAFT,
    ];

    protected array $rememberFilters = [
        'status_id',
    ];

    protected string $rememberKey = CookieKeys::news_list;

    public function list(ApiListRequest $request): JsonResponse
    {
        Hit::register(HitSource::admin);
        $query = News::query()
            ->with(['status'])
            ->orderBy('created_at');

        // apply filters
        if (!empty($filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey)) && !empty($filters['status_id'])) {
            $query->where('status_id', $filters['status_id']);
        }

        // current page automatically resolved from request via `page` parameter
        $news = $query->paginate($request->perPage(10, $this->rememberKey));

        /** @var LengthAwarePaginator $news */
        $news->transform(function (News $news) {
            return [
                'id' => $news->id,
                'title' => $news->title,
                'created_at' => $news->created_at->translatedFormat('D, d M Y H:i'),
                'send_at' => $news->send_at?->translatedFormat('D, d M Y H:i'),
                'recipient' => $news->recipients->name,
                'status' => $news->status->name,
            ];
        });

        return APIResponse::list(
            $news,
            ['Заголовок', 'Дата', 'Получатель', 'Статус', ''],
            $filters,
            $this->defaultFilters,
            []
        )->withCookie(cookie($this->rememberKey, $request->getToRemember()));
    }

    public function get(Request $request): JsonResponse
    {
        Hit::register(HitSource::admin);

        /** @var News|null $news */
        $news = $this->firstOrNew(News::class, $request, ['status']);

        if ($news === null) {
            return APIResponse::notFound('Новость не найдена');
        }

        // send response
        return APIResponse::form(
            [
                'title' => $news->title,
                'description' => $news->description,
                'recipients' => NewsRecipients::PARTNERS,
            ],
            $this->rules,
            $this->titles,
            [
                'name' => $news->exists ? $news->title : 'Добавление новости',
            ]
        );
    }

    public function update(Request $request): JsonResponse
    {
        Hit::register(HitSource::admin);
        $data = $this->getData($request);

        if ($errors = $this->validate($data, $this->rules, $this->titles)) {
            return APIResponse::validationError($errors);
        }

        /**  @var News $news */
        $news = $this->firstOrNew(News::class, $request);

        if ($news === null) {
            return APIResponse::notFound('Новость не найдена');
        }

        $news->title = $data['title'];
        $news->description = $data['description'];
        $news->status_id = NewsStatus::DRAFT;
        $news->recipients_id = NewsRecipients::PARTNERS;
        $news->save();

        return APIResponse::success(
            'Черновик новости сохранен',
            [
                'id' => $news->id,
                'name' => $news->name,
            ]
        );
    }

    public function view(Request $request): JsonResponse
    {
        Hit::register(HitSource::admin);
        $id = $request->input('id');

        if ($id === null ||
            null === ($news = News::query()->with(['status', 'recipients'])->where('id', $id)->first())) {
            return APIResponse::notFound('Экскурсия не найдена');
        }

        /** @var News $news */

        // fill data
        $values = [
            'id' => $news->id,
            'title' => $news->title,
            'description' => $news->description,
            'created_at' => $news->created_at->translatedFormat('D, d M Y H:i'),
            'send_at' => $news->send_at?->translatedFormat('D, d M Y H:i'),
            'recipient' => $news->recipients->name,
            'status' => $news->status->name,
        ];

        // send response
        return APIResponse::response($values);
    }

    public function delete(Request $request): JsonResponse
    {
        Hit::register(HitSource::admin);
        $id = $request->input('id');

        if ($id === null || null === ($news = News::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Новость не найдена');
        }

        /** @var News $news */
        $title = $news->title;

        try {
            $news->delete();
        } catch (QueryException $exception) {
            return APIResponse::error("Невозможно удалить новость \"$title\". Есть блокирующие связи.");
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::response([], [], "Новость \"$title\" удалена");
    }

    public function copy(Request $request)
    {
        $news = News::findOrFail($request->get('id'));

        News::create([
            'title' => $news->title . '(копия)',
            'description' => $news->description,
            'recipients_id' => $news->recipients_id,
            'status_id' => NewsStatus::DRAFT,
        ]);

        return APIResponse::success('Копия успешно создана');
    }

    public function test(Request $request)
    {
        $partner = Partner::inRandomOrder()->first();
        $news = News::findOrFail($request->get('id'));
        $email = $request->get('email');

        SendNewsEmailJob::dispatch($email, $news, $partner);
        return APIResponse::success('Тестовое письмо отправлено');
    }

}
