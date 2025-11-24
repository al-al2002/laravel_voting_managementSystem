<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Code - VoteMaster</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .container {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 30px;
        }

        .code-box {
            background-color: #fff;
            border: 2px solid #4CAF50;
            border-radius: 5px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }

        .code {
            font-size: 32px;
            font-weight: bold;
            color: #4CAF50;
            letter-spacing: 5px;
        }

        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2 style="color: #4CAF50;">Password Reset Request</h2>

        <p>Hello,</p>

        <p>We received a request to reset your password for your VoteMaster account. Use the verification code below to
            proceed:</p>

        <div class="code-box">
            <div class="code">{{ $code }}</div>
        </div>

        <p><strong>Important:</strong> This code will expire in 3 minutes for security reasons.</p>

        <p>If you didn't request a password reset, please ignore this email. Your account remains secure.</p>

        <div class="footer">
            <p>Best regards,<br />
                <strong>VoteMaster Team</strong>
            </p>

            <p style="color: #999;">This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>

</html>
