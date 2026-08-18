<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication — BU-GSO LINKod</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f8fafc;
            color: #1f2937;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            -webkit-font-smoothing: antialiased;
        }

        .auth-card {
            background: #fffde7;
            border: 1.5px solid #2563eb;
            border-radius: 16px;
            padding: 36px 32px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            text-align: center;
        }

        .logo-img {
            height: 52px;
            width: auto;
            margin: 0 auto 16px auto;
            display: block;
        }

        .card-title {
            font-size: 20px;
            font-weight: 800;
            color: #1e3a8a;
            margin-bottom: 6px;
        }

        .subtitle {
            font-size: 13px;
            color: #6b7280;
            line-height: 1.5;
            margin-bottom: 20px;
            max-width: 340px;
            margin-left: auto;
            margin-right: auto;
            font-weight: 500;
        }

        .qr-container {
            background: #ffffff;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            margin: 0 auto 20px auto;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
            max-width: 200px;
        }

        .qr-container svg {
            width: 160px;
            height: 160px;
            display: block;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 10px 14px;
            color: #dc2626;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 18px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-label {
            display: block;
            font-size: 12.5px;
            font-weight: 700;
            color: #374151;
            margin-bottom: 6px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .totp-input {
            width: 100%;
            padding: 12px 16px;
            background: #ffffff;
            border: 2px solid #cbd5e1;
            border-radius: 10px;
            font-size: 22px;
            font-weight: 800;
            color: #1e293b;
            text-align: center;
            letter-spacing: 6px;
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
            font-family: monospace, inherit;
        }

        .totp-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .btn-submit {
            width: 100%;
            padding: 12px 16px;
            background: #2563eb;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background-color 0.15s ease, transform 0.1s ease;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
        }

        .btn-submit:hover {
            background: #1d4ed8;
        }

        .btn-submit:active {
            transform: scale(0.99);
        }

        .divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 22px 0 16px 0;
            border: none;
        }

        .footer-link {
            font-size: 12.5px;
            color: #64748b;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.15s ease;
        }

        .footer-link:hover {
            color: #1e3a8a;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <a href="{{ route('home') }}">
            <img src="{{ asset('images/LINKOD logo.png') }}" alt="BU-GSO LINKod Logo" class="logo-img">
        </a>

        <h2 class="card-title">Two-Factor Authentication</h2>

        <p class="subtitle">
            Scan the QR code below using your authenticator app (e.g., Google Authenticator), then enter the 6-digit verification code.
        </p>

        <div class="qr-container">
            {!! $qrCodeSvg !!}
        </div>

        @if($errors->any())
            <div class="alert-error">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('2fa.verify') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="totp_code" class="form-label">Enter 6-Digit Code</label>
                <input type="text" 
                       name="totp_code" 
                       id="totp_code" 
                       class="totp-input" 
                       placeholder="••••••" 
                       required 
                       autocomplete="one-time-code" 
                       inputmode="numeric" 
                       autofocus 
                       maxlength="6">
            </div>
            
            <button type="submit" class="btn-submit">
                <span>Verify &amp; Continue</span>
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </button>
        </form>

        <hr class="divider">

        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="footer-link" style="background: none; border: none; cursor: pointer;">
                &larr; Cancel and Log Out
            </button>
        </form>
    </div>
</body>
</html>
