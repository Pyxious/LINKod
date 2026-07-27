<?php

namespace App\Policies;

use App\Models\ServiceRequest;
use App\Models\User;

class ServiceRequestPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ServiceRequest $serviceRequest): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'client') {
            return $user->client && $user->client->client_id === $serviceRequest->client_id;
        }

        if ($user->role === 'worker') {
            // Worker can view if assigned to the project associated with the request
            // This assumes a relationship between Worker->Team and Project exists.
            // For now, allow if they have an active project associated.
            if ($serviceRequest->project) {
                // Simplified: check if worker is assigned
                return true; // Requires robust assignment check based on actual schema
            }
        }

        return false;
    }

    /**
     * Determine whether the user can cancel/update the model.
     */
    public function update(User $user, ServiceRequest $serviceRequest): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'client') {
            return $user->client && $user->client->client_id === $serviceRequest->client_id;
        }

        return false;
    }
}
