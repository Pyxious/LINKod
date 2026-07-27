<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;

class TwoFactorController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $google2fa = app('pragmarx.google2fa');
        
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email_account ?? $user->email ?? 'user@bicol-u.edu.ph',
            $user->totp_secret
        );

        // We use an external service or bacon-qr-code to generate the SVG
        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(250),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );
        $writer = new \BaconQrCode\Writer($renderer);
        $qrCodeSvg = $writer->writeString($qrCodeUrl);

        return view('auth.2fa', compact('qrCodeSvg'));
    }

    public function verify(Request $request, AuthService $authService)
    {
        $request->validate([
            'totp_code' => 'required|numeric',
        ]);

        $user = auth()->user();
        $google2fa = app('pragmarx.google2fa');

        $valid = $google2fa->verifyKey($user->totp_secret, $request->totp_code);

        if ($valid) {
            $request->session()->put('2fa_verified', true);
            return redirect($authService->dashboardRoute($user));
        }

        return back()->withErrors(['totp_code' => 'Invalid or expired code. Please try again.']);
    }
}
