<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password</title>
</head>
<body style="margin:0;padding:0;background:#fafaf9;font-family:'Segoe UI',Arial,Helvetica,sans-serif;color:#2f2a26;">
  <div style="max-width:560px;margin:0 auto;padding:36px 24px 40px;text-align:left;">
    {{-- <div style="text-align:center;margin-bottom:28px;">
      <img src="{{ $logoDataUri ?: $logoUrl }}" alt="Le Croissant" style="max-width:180px;width:100%;height:auto;display:inline-block;">
    </div> --}}

    <h1 style="margin:0 0 14px;font-size:24px;font-weight:600;line-height:1.35;color:#1f1b17;text-align:center;">
      Reset Password
    </h1>

    <p style="margin:0 0 12px;font-size:15px;line-height:1.8;color:#3f3a35;">
      Halo {{ $name }},
    </p>
    <p style="margin:0 0 18px;font-size:15px;line-height:1.8;color:#3f3a35;">
      We have received a request to reset your password.
      Click the button below to continue.
    </p>

    <div style="text-align:center;margin:26px 0;">
      <a href="{{ $resetUrl }}" style="display:inline-block;background:#2f2a26;color:#ffffff;text-decoration:none;font-size:14px;font-weight:600;letter-spacing:.2px;padding:12px 24px;border-radius:999px;">
        Reset Password
      </a>
    </div>

    <p style="margin:0 0 12px;font-size:14px;line-height:1.8;color:#5a534c;text-align:center;">
      Link is valid for {{ $expireMinutes }} minutes.
    </p>

    <p style="margin:18px 0 10px;font-size:13px;line-height:1.7;color:#6b645e;">
      If the button does not work, open the following link:
    </p>
    <p style="margin:0 0 20px;font-size:12px;line-height:1.7;word-break:break-all;">
      <a href="{{ $resetUrl }}" style="color:#6a4a2a;text-decoration:underline;">{{ $resetUrl }}</a>
    </p>

    <p style="margin:0;font-size:13px;line-height:1.8;color:#6b645e;">
      Jika Anda tidak merasa meminta reset password, abaikan email ini.
    </p>

    <p style="margin:28px 0 0;font-size:12px;line-height:1.7;color:#8b847d;text-align:center;">
        Le Croissant
      {{-- {{ $appName }} --}}
    </p>
  </div>
</body>
</html>
