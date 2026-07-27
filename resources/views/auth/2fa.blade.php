<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication - LINKod</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold text-center text-[#1a3c8f] mb-6">Two-Factor Authentication</h2>
        
        <p class="text-gray-600 text-sm mb-4 text-center">
            Please scan the QR code below using an authenticator app (like Google Authenticator), then enter the 6-digit code to log in.
        </p>

        <div class="flex justify-center mb-6 border border-gray-200 p-4 rounded-lg bg-gray-50">
            {!! $qrCodeSvg !!}
        </div>

        @if($errors->any())
            <div class="bg-red-50 text-red-600 text-sm p-3 rounded-lg mb-4 text-center">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('2fa.verify') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="totp_code" class="block text-sm font-medium text-gray-700 mb-1">6-Digit Code</label>
                <input type="text" name="totp_code" id="totp_code" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#1a3c8f] focus:border-[#1a3c8f]" 
                       required autocomplete="off" autofocus maxlength="6">
            </div>
            
            <button type="submit" class="w-full bg-[#1a3c8f] text-white py-2 rounded-lg font-semibold hover:bg-[#152e6e] transition">
                Verify & Continue
            </button>
        </form>
    </div>
</body>
</html>
