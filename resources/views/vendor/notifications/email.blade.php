@component('mail::message')

    @if (!empty($greeting))
        <h1>{{ $greeting }}</h1>
    @endif

    @foreach ($introLines as $line)
        <p>{{ $line }}</p>
    @endforeach

    @isset($actionText)
        @component('mail::button', ['url' => $actionUrl, 'level' => $level])
            {{ $actionText }}
        @endcomponent
    @endisset

    @foreach ($outroLines as $line)
        <p>{{ $line }}</p>
    @endforeach

    @if (!empty($disclaimer))
        <p>{{ $disclaimer }}</p>
    @endif

    @if (!empty($salutation))
        <p>{{ $salutation }}</p>
    @endif

@endcomponent
