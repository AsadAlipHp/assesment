<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{id}', function ($user, $id) {
    // Only allow the logged-in user to listen to their own private channel
    return (int) $user->id === (int) $id;
});
