<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Your Password</title>
    <style>
        /* Import your brand colors */
        :root {
            --color-navy: #0A2240;
            --color-navy-light: #143462;
            --color-teal: #007C91;
            --color-red-500: #EF4444;
            --color-red-600: #DC2626;
            --color-gray-50: #F9FAFB;
            --color-gray-100: #F3F4F6;
            --color-gray-200: #E5E7EB;
            --color-gray-600: #4B5563;
            --color-gray-700: #374151;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: var(--color-gray-700);
            background-color: var(--color-gray-50);
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .email-content {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .email-header {
            background: linear-gradient(135deg, var(--color-red-500) 0%, var(--color-red-600) 100%);
            padding: 40px 30px;
            text-align: center;
        }

        .logo {
            max-width: 120px;
            height: auto;
            margin-bottom: 20px;
            filter: brightness(0) invert(1);
        }

        .email-title {
            color: white;
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .email-subtitle {
            color: rgba(255, 255, 255, 0.9);
            margin: 8px 0 0 0;
            font-size: 16px;
            font-weight: 400;
        }

        .email-body {
            padding: 40px 30px;
        }

        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: var(--color-navy);
            margin-bottom: 20px;
        }

        .content-text {
            font-size: 16px;
            margin-bottom: 20px;
            color: var(--color-gray-600);
        }

        .security-notice {
            background: linear-gradient(135deg, #FEF2F2 0%, #FEE2E2 100%);
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
            border-left: 4px solid var(--color-red-500);
        }

        .security-text {
            font-size: 14px;
            color: #991B1B;
            font-weight: 500;
            margin: 0;
        }

        .button-container {
            text-align: center;
            margin: 40px 0;
        }

        .reset-button {
            display: inline-block;
            background: linear-gradient(135deg, var(--color-red-500) 0%, var(--color-red-600) 100%);
            color: white;
            padding: 16px 32px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 14px 0 rgba(239, 68, 68, 0.3);
            transition: all 0.2s ease;
        }

        .reset-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px 0 rgba(239, 68, 68, 0.4);
        }

        .expiry-notice {
            background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%);
            padding: 16px;
            border-radius: 8px;
            margin: 30px 0;
            border-left: 4px solid #F59E0B;
        }

        .expiry-text {
            font-size: 14px;
            color: #92400E;
            font-weight: 500;
            margin: 0;
        }

        .link-section {
            background: var(--color-gray-50);
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
        }

        .link-label {
            font-size: 14px;
            color: var(--color-gray-600);
            margin-bottom: 10px;
        }

        .link-text {
            word-break: break-all;
            font-size: 12px;
            color: var(--color-red-500);
            background: white;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid var(--color-gray-200);
            font-family: 'Monaco', 'Consolas', monospace;
        }

        .footer {
            background: var(--color-gray-100);
            padding: 30px;
            text-align: center;
            border-top: 1px solid var(--color-gray-200);
        }

        .footer-text {
            font-size: 13px;
            color: var(--color-gray-600);
            margin: 5px 0;
        }

        .brand-signature {
            font-weight: 600;
            color: var(--color-navy);
        }

        @media (max-width: 600px) {
            .email-container {
                padding: 10px;
            }

            .email-header {
                padding: 30px 20px;
            }

            .email-body {
                padding: 30px 20px;
            }

            .reset-button {
                padding: 14px 24px;
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
<div class="email-container">
    <div class="email-content">
        <div class="email-header">
            <img src="{{ url('logo_nobg.png') }}" alt="Apixies Logo" class="logo">
            <h1 class="email-title">Password Reset Request</h1>
            <p class="email-subtitle">Secure your account</p>
        </div>

        <div class="email-body">
            <div class="greeting">Hello {{ $userName }},</div>

            <p class="content-text">
                We received a request to reset the password for your Apixies account. If you made this request, click the button below to create a new password.
            </p>

            <div class="security-notice">
                <p class="security-text">
                    🔒 This is a security-sensitive action. Only proceed if you requested this password reset.
                </p>
            </div>

            <div class="button-container">
                <a href="{{ $resetUrl }}" class="reset-button">
                    🔑 Reset My Password
                </a>
            </div>

            <div class="expiry-notice">
                <p class="expiry-text">
                    ⏱️ This password reset link will expire in {{ $expireMinutes }} minutes.
                </p>
            </div>

            <p class="content-text">
                <strong>If you did not request a password reset:</strong> No action is required. Your password will remain unchanged and your account stays secure.
            </p>

            <div class="link-section">
                <p class="link-label">If the button above doesn't work, copy and paste this link into your browser:</p>
                <div class="link-text">{{ $resetUrl }}</div>
            </div>
        </div>

        <div class="footer">
            <p class="footer-text">
                This email was sent because a password reset was requested for your Apixies account.
            </p>
            <p class="footer-text">
                Best regards,<br>
                <span class="brand-signature">The Apixies Team</span>
            </p>
        </div>
    </div>
</div>
</body>
</html>
