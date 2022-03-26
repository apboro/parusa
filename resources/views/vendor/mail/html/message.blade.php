@component('mail::layout')

    <table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">

        <tr>
            <td class="header">
                @component('mail::header', ['url' => config('app.url')])
                    @lang('notifications.title')
                @endcomponent
            </td>
        </tr>

        <!-- Email Body -->
        <tr>
            <td class="body" width="100%">
                <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0"
                       role="presentation">
                    <!-- Body content -->
                    <tr>
                        <td class="content-cell">
                            {{ $slot }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td>
                @component('mail::footer')
                    © {{ date('Y') }} @lang('notifications.copyright')
                @endcomponent
            </td>
        </tr>

    </table>
@endcomponent
