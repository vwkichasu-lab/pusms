<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $communication->subject ?? 'Pentecost University Scholarship Update' }}</title>
</head>
<body style="margin:0;background:#f6f8fb;color:#111827;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f6f8fb;padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:680px;background:#ffffff;border:1px solid #dbe3ef;border-radius:8px;overflow:hidden;">
                    <tr>
                        <td style="padding:22px 28px;border-bottom:1px solid #e5edf7;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="width:74px;">
                                        <img src="{{ asset('images/pentvars-display-logo.png') }}" alt="Pentecost University" width="62" style="display:block;height:auto;">
                                    </td>
                                    <td>
                                        <div style="font-size:18px;font-weight:700;color:#082f63;">Pentecost University</div>
                                        <div style="font-size:13px;color:#64748b;margin-top:3px;">Scholarship Management System</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;font-size:15px;line-height:1.65;color:#1f2937;">
                            {!! nl2br(e($messageBody)) !!}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 28px;background:#f8fafc;border-top:1px solid #e5edf7;font-size:12px;color:#64748b;">
                            This message was sent by Pentecost University Scholarship Management System.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
