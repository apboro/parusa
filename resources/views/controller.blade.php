<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') }}</title>

    <link rel="stylesheet" href="{{ mix('/css/app.css') }}">
    <script>
        window.user = '{!! $user !!}';
        window.roles = '{!! $roles !!}';
    </script>
</head>
<body>
<div id="app"></div>
</body>
<script src="{{ mix('/js/controller.js') }}"></script>
</html>
