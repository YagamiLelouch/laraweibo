<html>
<head>
    <title>{{ $data['subject'] }}</title>
</head>
<body>
{{ trans('emails.reset_password.welcome') }}<br>
{{ trans('emails.reset_password.button_desc') }}<br>
<a target="_blank" href="{{ trans('layouts.web_url').'/password/reset/'.$data['code'] }}">{{ trans('emails.reset_password.button_value') }}</a><br>
{{ trans('emails.reset_password.link_desc') }}<br>
{{ trans('layouts.web_url').'/password/reset/'.$data['code'] }}
</body>
</html>