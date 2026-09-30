<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="margin:0; padding:0; background:#F7F4EF; font-family:Helvetica,Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 0;">
        <tr>
            <td align="center">
                <table width="480" cellpadding="0" cellspacing="0" style="background:#FFFFFF; border:1px solid #E3DACE;">
                    <tr>
                        <td style="padding:32px; text-align:center; letter-spacing:6px; font-size:20px; font-weight:600; color:#1C1C1C;">
                            VELORA
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 32px 32px; text-align:center;">
                            <p style="color:#3A3A3A; font-size:15px; margin:0 0 24px;">
                                Hi {{ $user->name }}, use this code to verify your email address.
                            </p>
                            <div style="font-size:36px; font-weight:700; letter-spacing:10px; color:#1C1C1C; background:#F7F4EF; padding:16px; margin:0 0 24px;">
                                {{ $code }}
                            </div>
                            <p style="color:#8A7F72; font-size:13px; margin:0;">
                                This code expires in 10 minutes. If you didn't request this, you can ignore this email.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
