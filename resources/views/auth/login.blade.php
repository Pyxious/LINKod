<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BU-GSO LINKod — Sign In</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #ffffff;
            color: #1f2937;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            -webkit-font-smoothing: antialiased;
        }

        .login-card {
            background: #fffde7;
            border: 1.5px solid #2563eb;
            border-radius: 16px;
            padding: 40px 36px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            text-align: center;
        }

        .logo-img {
            height: 60px;
            width: auto;
            margin: 0 auto 16px auto;
            display: block;
        }

        .subtitle {
            font-size: 12.5px;
            color: #9ca3af;
            line-height: 1.5;
            margin-bottom: 28px;
            max-width: 320px;
            margin-left: auto;
            margin-right: auto;
            font-weight: 500;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 10px 14px;
            color: #dc2626;
            font-size: 13px;
            margin-bottom: 20px;
            text-align: center;
        }

        .btn-google {
            width: 100%;
            padding: 12px 16px;
            background: #ffffff;
            color: #374151;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s ease;
            text-decoration: none;
            margin-bottom: 24px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        }

        .btn-google:hover {
            background: #f9fafb;
            border-color: #d1d5db;
        }

        .divider {
            height: 1px;
            background-color: #e5e7eb;
            margin-bottom: 24px;
            border: none;
        }

        .trouble-text {
            font-size: 12px;
            color: #9ca3af;
            font-weight: 500;
            margin-bottom: 12px;
        }

        .icto-box {
            display: block;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 14px 16px;
            text-align: left;
            text-decoration: none;
            transition: border-color 0.15s ease, shadow 0.15s ease;
        }

        .icto-box:hover {
            border-color: #cbd5e1;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
        }

        .icto-title {
            font-size: 14px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 2px;
        }

        .icto-title span {
            color: #0033a0;
        }

        .icto-desc {
            font-size: 12px;
            color: #6b7280;
        }

        .terms-footer {
            margin-top: 24px;
            font-size: 11.5px;
            color: #9ca3af;
            text-align: center;
        }

        .terms-footer a {
            color: #9ca3af;
            text-decoration: underline;
        }

        .terms-footer a:hover {
            color: #4b5563;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <a href="{{ route('home') }}">
            <img src="{{ asset('images/LINKOD logo.png') }}" alt="BU-GSO LINKod Logo" class="logo-img">
        </a>

        <p class="subtitle">
            Welcome back! Sign in with your BU email account to access LINKod.
        </p>

        @if(session('error'))
            <div class="alert-error">
                {{ session('error') }}
            </div>
        @endif

        <a href="{{ route('google.redirect') }}" class="btn-google">
            Sign In with Google
        </a>

        <hr class="divider">

        <p class="trouble-text">Having trouble signing in?</p>

        <a href="https://icto.bicol-u.edu.ph/request/email-password-reset" target="_blank" class="icto-box">
            <div class="icto-title">Contact <span>ICTO</span></div>
            <div class="icto-desc">For BU account/password issues</div>
        </a>
    </div>

    <p class="terms-footer">
        By signing in, you agree to our <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a> .
    </p>
</body>
</html>
