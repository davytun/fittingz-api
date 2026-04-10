<tr>
<td style="background-color: #0f4c75; padding: 48px; text-align: left;">
<table class="footer" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="content-cell" align="left" style="padding: 0; text-align: left;">
    <p style="color: #ffffff; font-size: 14px; margin: 0 0 32px 0; line-height: 1.6; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
        This email was sent to <a href="mailto:{{ $notifiable->email ?? 'you' }}" style="color: #ffffff; text-decoration: underline;">{{ $notifiable->email ?? 'you' }}</a>.
        Don't want any more emails from Fittingz? <a href="{{ URL::signedRoute('unsubscribe', ['userId' => $notifiable->id ?? '']) }}" style="color: #ffffff; text-decoration: underline;">Unsubscribe</a>.
    </p>

    <table cellpadding="0" cellspacing="0" role="presentation" width="100%">
        <tr>
            <td style="vertical-align: middle;">
                <p style="color: #ffffff; font-size: 14px; margin: 0; line-height: 1.6; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
                    3rd floor, Opposite Cathedral of St. Peter Ang. Sec. Sch,<br>
                    Along Oba Ademola Maternity Hospital, Ake, Abeokuta.<br>
                    +234 707 719 5098<br>
                    © {{ date('Y') }} Fittingz
                </p>
            </td>
            <td align="right" style="vertical-align: middle; text-align: right;">
                <table cellpadding="0" cellspacing="0" role="presentation" style="display: inline-table;">
                    <tr>
                        <td style="padding-left: 14px;">
                            <a href="https://linkedin.com/company/fittingz" target="_blank" rel="noopener" style="display: inline-block; background-color: #ffffff; border-radius: 4px; padding: 8px; line-height: 0;">
                                <img src="{{ asset('images/email/linkedin.png') }}" width="18" height="18" alt="LinkedIn" style="display: block;">
                            </a>
                        </td>
                        <td style="padding-left: 14px;">
                            <a href="https://instagram.com/fittingz" target="_blank" rel="noopener" style="display: inline-block; background-color: #ffffff; border-radius: 4px; padding: 8px; line-height: 0;">
                                <img src="{{ asset('images/email/instagram.png') }}" width="18" height="18" alt="Instagram" style="display: block;">
                            </a>
                        </td>
                        <td style="padding-left: 14px;">
                            <a href="https://x.com/fittingz" target="_blank" rel="noopener" style="display: inline-block; background-color: #ffffff; border-radius: 4px; padding: 8px; line-height: 0;">
                                <img src="{{ asset('images/email/x.png') }}" width="18" height="18" alt="X" style="display: block;">
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</td>
</tr>
</table>
</td>
</tr>
