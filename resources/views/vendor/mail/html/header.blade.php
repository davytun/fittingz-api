@props(['url'])
<tr>
<td style="padding: 48px 48px 20px 48px; text-align: left;">
<a href="{{ $url }}" style="text-decoration: none;" aria-label="Fittingz homepage">
    <table cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td style="padding-right: 12px; vertical-align: middle;">
                {{-- Replace src with an absolute URL to a hosted PNG for maximum email client compatibility --}}
                <img src="{{ asset('images/email/logo-icon.png') }}" width="28" height="28" alt="Fittingz" style="display:block; border:0; line-height:0;">
            </td>
            <td style="vertical-align: middle;">
                <span style="color: #0f4c75; font-size: 24px; font-weight: bold; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; letter-spacing: -0.2px;">Fittingz</span>
            </td>
        </tr>
    </table>
</a>
</td>
</tr>
