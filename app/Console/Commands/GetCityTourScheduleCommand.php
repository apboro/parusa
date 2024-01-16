<?php

namespace App\Console\Commands;

use DOMDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Str;

class GetCityTourScheduleCommand extends Command
{
    protected $signature = 'get:city-tour-schedule';

    protected $description = 'Скачать картинку расписания обзорной экскурсии с сайта Сити Тур';

    public function handle(): void
    {
        $this->saveImg();
    }

    private function findScheduleImgUrl()
    {
        $url = 'https://citytourspb.ru/';
        $html = file_get_contents($url);
        $dom = new DOMDocument;
        @$dom->loadHTML($html);
        $anchors = $dom->getElementsByTagName('a');
        foreach ($anchors as $anchor) {
            $innerText = trim($anchor->textContent);
            if (Str::contains($innerText, 'Скачать расписание на сегодня')) {
                $url = $anchor->getAttribute('href');
                $this->info("Link URL: $url");
            }
        }

        return $url;
    }

    private function saveImg(): void
    {
        $imageUrl = $this->findScheduleImgUrl();
        $response = Http::get($imageUrl);
        if ($response->successful()) {
            $manager = ImageManager::imagick();
            $image = $manager->read($response->body());
            $image->drawRectangle(0, 0, function ($rectangle) {
                $rectangle->size(350, 200);
                $rectangle->background('#f04e47');
            });

            Storage::disk('public_images')->put('city_tour-schedule.png', $image->encode());
        } else {
            Log::error('Could`t download CITY TOUR schedule');
        }
    }
}
