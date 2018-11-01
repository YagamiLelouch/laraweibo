<html>
<head>
    <title>{{ $data['subject'] }}</title>
</head>
<body>
{{ trans('emails.sign_in.welcome') }}<br>
{{ trans('emails.sign_in.button_desc') }}<br>
<a target="_blank" href="{{ trans('layouts.web_url').'/signup-confirm?email='.$data['email'].'&code='.$data['code'] }}">{{ trans('emails.sign_in.button_value') }}</a><br>
{{ trans('emails.sign_in.link_desc') }}<br>
{{ trans('layouts.web_url').'/email_signup?email='.$data['email'].'&code='.$data['code'] }}
</body>
</html>