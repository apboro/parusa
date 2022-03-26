@component('mail::layout')
    @component('mail::header', ['url' => config('app.url')])
        @lang('notifications.title')
    @endcomponent

    {{ $slot }}

    @component('mail::footer')
        © {{ date('Y') }} @lang('notifications.copyright')
    @endcomponent
@endcomponent
