<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Your Email Address</title>
    <style>
        /* Import your brand colors */
        :root {
            --color-navy: #0A2240;
            --color-navy-light: #143462;
            --color-teal: #007C91;
            --color-teal-light: #0D9488;
            --color-blue-600: #0066FF;
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
            background: linear-gradient(135deg, var(--color-navy) 0%, var(--color-navy-light) 100%);
            padding: 40px 30px;
            text-align: center;
        }

        .logo {
            max-width: 120px;
            height: auto;
            margin-bottom: 20px;
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

        .button-container {
            text-align: center;
            margin: 40px 0;
        }

        .verify-button {
            display: inline-block;
            background: linear-gradient(135deg, var(--color-teal) 0%, var(--color-teal-light) 100%);
            color: white;
            padding: 16px 32px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 14px 0 rgba(0, 124, 145, 0.3);
            transition: all 0.2s ease;
        }

        .verify-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px 0 rgba(0, 124, 145, 0.4);
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
            color: var(--color-teal);
            background: white;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid var(--color-gray-200);
            font-family: 'Monaco', 'Consolas', monospace;
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

            .verify-button {
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
            <h1 class="email-title">Welcome to Apixies!</h1>
            <p class="email-subtitle">Your API toolkit awaits</p>
        </div>

        <div class="email-body">
            <div class="greeting">Hello {{ $userName }},</div>

            <p class="content-text">
                Thank you for joining Apixies! We're excited to have you on board. To get started with our powerful API collection, please verify your email address by clicking the button below.
            </p>

            <div class="button-container">
                <a href="{{ $verificationUrl }}" class="verify-button">
                    ✓ Verify Email Address
                </a>
            </div>

            <div class="expiry-notice">
                <p class="expiry-text">
                    ⏱️ This verification link will expire in 60 minutes for security reasons.
                </p>
            </div>

            <div class="link-section">
                <p class="link-label">If the button above doesn't work, copy and paste this link into your browser:</p>
                <div class="link-text">{{ $verificationUrl }}</div>
            </div>

            <p class="content-text">
                Once verified, you'll have access to our complete suite of API tools and documentation.
            </p>
        </div>

        <div class="footer">
            <p class="footer-text">
                If you didn't create an account with Apixies, please ignore this email.
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
