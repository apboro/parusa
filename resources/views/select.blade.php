<?php
/** @var array $variants */
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
    <?php foreach ($variants as $variant): ?>
    <div class="card__item<?php echo $variant['is_staff'] ? ' card__item-highlighted' : ''?>">
        <div class="card__item-head">
            <p class="card__item-head-title"><?php echo !$variant['is_staff'] ? '<span class="card__item-head-title-hint">партнёр:</span>' : ''?>{{ $variant['organization'] }}</p>
            <?php if(!$variant['is_staff']): ?>
            <p class="card__item-head-sub-title">должность: {{ $variant['position'] }}</p>
            <?php else: ?>
            <p class="card__item-head-sub-title">роль: {{ $variant['role'] }}</p>
            <?php endif; ?>
        </div>
        <div class="card__item-body">
            <form action="/login/select" method="post">
                <input type="hidden" name="position_id" value="{{ $variant['position_id'] }}">
                <input type="hidden" name="role_id" value="{{ $variant['role_id'] }}">
                <input type="hidden" name="terminal_id" value="{{ $variant['terminal_id'] }}">
                @csrf
                <button class="button" type="submit">Войти в кабинет</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>
</body>
</html>
