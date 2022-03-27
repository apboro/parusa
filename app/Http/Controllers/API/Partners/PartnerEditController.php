<?php

namespace App\Http\Controllers\API\Partners;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\Common\File;
use App\Models\Partner\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerEditController extends ApiEditController
{
    protected array $rules = [
        'name' => 'required',
        'type_id' => 'required',
        'status_id' => 'required',
        'tickets_for_guides' => 'required|integer|min:0',
    ];

    protected array $titles = [
        'name' => 'Название партнера',
        'type_id' => 'Тип партнера',
        'status_id' => 'Статус',
        'tickets_for_guides' => 'Билеты для гидов',
        'can_reserve_tickets' => 'Бронирование билетов',
        'documents' => 'Документы',
        'notes' => 'Заметки',
    ];

    /**
     * Get edit data for partner.
     * id === 0 is for new
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function get(Request $request): JsonResponse
    {
        /** @var Partner|null $partner */
        $partner = $this->firstOrNew(Partner::class, $request);

        if ($partner === null) {
            return APIResponse::notFound('Партнёр не найден');
        }

        // send response
        return APIResponse::form(
            [
                'name' => $partner->name,
                'type_id' => $partner->type_id,
                'status_id' => $partner->status_id,
                'tickets_for_guides' => $partner->profile->tickets_for_guides,
                'can_reserve_tickets' => $partner->profile->can_reserve_tickets ? 1 : 0,
                'documents' => $partner->files->map(function (File $file) {
                    return ['id' => $file->id, 'name' => $file->original_filename, 'url' => $file->url, 'type' => $file->mime, 'size' => $file->size];
                }),
                'notes' => $partner->profile->notes,
            ],
            $this->rules,
            $this->titles,
            [
                'title' => $partner->exists ? $partner->name : 'Добавление партнёра',
            ]
        );
    }

    /**
     * Update excursion data.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        /** @var Partner|null $partner */
        $partner = $this->firstOrNew(Partner::class, $request);

        if ($partner === null) {
            return APIResponse::notFound('Партнёр не найден');
        }

        $data = $this->getData($request);

        if ($errors = $this->validate($data, $this->rules, $this->titles)) {
            return APIResponse::formError($data, $this->rules, $this->titles, $errors);
        }

        $partner->name = $data['name'];
        $partner->type_id = $data['type_id'];
        $partner->status_id = $data['status_id'];
        $partner->save();

        $profile = $partner->profile;

        $profile->tickets_for_guides = $data['tickets_for_guides'];
        $profile->can_reserve_tickets = $data['can_reserve_tickets'];
        $profile->notes = $data['notes'];
        $profile->save();

        // documents
        $files = File::createFromMany($data['documents'], 'partner_files');
        $fileIds = $files->pluck('id')->toArray();
        $partner->files()->sync($fileIds);

        return APIResponse::success('Данные партнёра обновлены', [
            'id' => $partner->id,
            'title' => $partner->name,
        ]);
    }
}
