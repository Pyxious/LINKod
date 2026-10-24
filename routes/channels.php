<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) ($user->user_id ?? $user->getKey()) === (int) $id;
});

Broadcast::channel('requests.global', function ($user) {
    return $user !== null;
});

Broadcast::channel('requests.{requestId}', function ($user, $requestId) {
    return $user !== null;
});

Broadcast::channel('projects.{projectId}', function ($user, $projectId) {
    return $user !== null;
});

