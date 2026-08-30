<?php

namespace App\Http\Controllers;

use App\Events\ReactedEvent;
use App\Events\UserTypeEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReactionController extends Controller
{
    //
    public function reaction(Request $request) {
        event(
            new ReactedEvent(
                buttonId: $request->input('buttonId'),
                message: $request->input('message') ?? 'Пустое сообщение'
            )
        );
    }

    public function userType(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);


        event(
            new UserTypeEvent(
                message: trim($validated['message']),
            )
        );
    }
}
