<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<title>{{ config('app.name') }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style>
body {
    background-color: #f9fafb;
    color: #4b5563;
    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
    margin: 0;
    padding: 0;
    -webkit-text-size-adjust: none;
    -ms-text-size-adjust: none;
}
p {
    margin-top: 0;
    margin-bottom: 20px;
    font-size: 15px;
    line-height: 1.5;
    color: #4b5563;
}
@media only screen and (max-width: 600px) {
    .inner-body { width: 100% !important; border: none !important; }
}
</style>
{!! $head ?? '' !!}
</head>
<body style="background-color: #f9fafb; margin: 0; padding: 0;">

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #f9fafb; padding: 40px 0;">
<tr>
<td align="center">
    <table class="inner-body" width="600" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #ffffff; width: 600px; max-width: 100%; margin: 0 auto; border: 1px solid #e5e7eb;">
        <!-- Header -->
        {!! $header ?? '' !!}

        <!-- Body content -->
        <tr>
            <td style="padding: 10px 48px 20px 48px; text-align: left;">
                {!! Illuminate\Mail\Markdown::parse($slot) !!}
                {!! $subcopy ?? '' !!}
            </td>
        </tr>

        <!-- Footer -->
        {!! $footer ?? '' !!}
    </table>
</td>
</tr>
</table>

</body>
</html>
