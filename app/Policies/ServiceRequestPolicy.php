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

        // User owns the request (client or worker acting on client side)
        if ($user->client && $user->client->client_id === $serviceRequest->client_id) {
            return true;
        }

        // Worker can view if assigned to the project associated with the request
        if ($user->role === 'worker') {
            $worker = $user->staff?->worker;
            if ($worker && $serviceRequest->project && $serviceRequest->project->workers->contains('worker_id', $worker->worker_id)) {
                return true;
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

        // User owns the request
        if ($user->client && $user->client->client_id === $serviceRequest->client_id) {
            return true;
        }

        return false;
    }
}
