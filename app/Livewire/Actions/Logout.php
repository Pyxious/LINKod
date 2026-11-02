<?php

namespace App\Livewire\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Logout
{
    /**
     * Log the current user out of the application.
     */
    public function __invoke(): void
    {
        $hadPilotAccess = (bool) Session::get('pilot_access_granted', false);

        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();

        if ($hadPilotAccess) {
            Session::put('pilot_access_granted', true);
        }
    }
}
