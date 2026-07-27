<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // If 2FA is newly requested, generate a secret for QR code
        $qrCodeSvg = null;
        if (session('show_2fa_setup') && !$user->totp_secret) {
            $google2fa = app('pragmarx.google2fa');
            $secret = $google2fa->generateSecretKey();
            session(['2fa_setup_secret' => $secret]);
            
            $qrCodeUrl = $google2fa->getQRCodeUrl(
                config('app.name'),
                $user->email_account,
                $secret
            );

            $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(250),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
            );
            $writer = new \BaconQrCode\Writer($renderer);
            $qrCodeSvg = $writer->writeString($qrCodeUrl);
        }

        return view('profile.index', compact('user', 'qrCodeSvg'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        
        if ($user->isClient()) {
            $validated = $request->validate([
                'office' => 'nullable|string|max:255',
                'campus' => 'nullable|string|max:100',
            ]);
            
            \App\Models\Client::updateOrCreate(
                ['user_id' => $user->user_id],
                $validated
            );
        }
        
        return redirect()->route('profile.index')->with('success', 'Profile updated successfully.');
    }

    public function initiate2fa()
    {
        return redirect()->route('profile.index')->with('show_2fa_setup', true);
    }

    public function enable2fa(Request $request)
    {
        $request->validate(['one_time_password' => 'required|string']);

        $user = auth()->user();
        $google2fa = app('pragmarx.google2fa');
        $secret = session('2fa_setup_secret');

        if (!$secret) {
            return redirect()->route('profile.index')->with('error', 'Session expired. Please try again.');
        }

        $valid = $google2fa->verifyKey($secret, $request->one_time_password);

        if ($valid) {
            $user->update(['totp_secret' => $secret]);
            session()->forget('2fa_setup_secret');
            session(['2fa_verified' => true]);
            
            return redirect()->route('profile.index')->with('success', 'Two-Factor Authentication enabled successfully!');
        }

        return redirect()->route('profile.index')
            ->with('show_2fa_setup', true)
            ->with('error', 'Invalid verification code. Please try again.');
    }

    public function disable2fa(Request $request)
    {
        // Require password/verification for actual production apps, but here we can just disable it since it's already auth'd
        $user = auth()->user();
        $user->update(['totp_secret' => null]);
        session()->forget('2fa_verified');

        return redirect()->route('profile.index')->with('success', 'Two-Factor Authentication disabled.');
    }
}
