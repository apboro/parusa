<?php
/** @var array $positions */
?>
    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') }}</title>

    <link rel="stylesheet" href="{{ mix('/css/select.css') }}">
</head>
<body>
<div class="card">
    <h1 class="card__title">Выберите компанию</h1>
    <p class="card__sub-title">Вы являетесь представителем нескольких компаний. Выберите компанию, от которой хотите работать.</p>
    @foreach($positions as $position)
        <div class="card__item">
            <div class="card__item-head">
                <p class="card__item-head-title">{{ $position['partner'] }}</p>
                <p class="card__item-head-sub-title">{{ $position['title'] }}</p>
            </div>
            <div class="card__item-body">
                <form action="/login/select" method="post">
                    <input type="hidden" name="position" value="{{ $position['id'] }}">
                    @csrf
                    <button class="button" type="submit">Войти в кабинет</button>
                </form>
            </div>
        </div>
    @endforeach
</div>
</body>
</html>
