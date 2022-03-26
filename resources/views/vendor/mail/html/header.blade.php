<table class="header" align="center" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td class="content-cell" align="left">
            <table cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                    <td align="center">
                        <a href="{{ $url }}" style="display: block;">
                            <img src="{{ config('app.url') }}/img/notification-logo.png" class="logo" alt="logo"
                                 width="64px" height="64px">
                        </a>
                    </td>
                    <td align="left" style="padding: 0 16px">
                        <a href="{{ $url }}" style="display: inline-block;">{{ $slot }}</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
