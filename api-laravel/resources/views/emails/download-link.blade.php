<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('checkout.email_subject') }}</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; max-width: 560px; margin: 24px auto; color: #1f2937;">
    <h2 style="color: #111827;">{{ __('checkout.email_greeting') }}</h2>

    <p>{!! __('checkout.email_body', ['title' => '<strong>' . e($imageTitle) . '</strong>']) !!}</p>

    <p style="text-align: center; margin: 32px 0;">
        <a href="{{ $downloadUrl }}"
           style="display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600;">
            {{ __('checkout.email_button') }}
        </a>
    </p>

    <p style="font-size: 14px; color: #6b7280;">
        {{ __('checkout.email_expiry', ['date' => $expiresAt->isoFormat('LLL')]) }}
    </p>
</body>
</html>
