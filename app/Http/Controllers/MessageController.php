<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Отправка сообщения в чат.
     */
    public function store(Request $request, Chat $chat): RedirectResponse
    {
        $userId = $request->user()->id;

        if (! in_array($userId, [$chat->buyer_id, $chat->seller_id], true)) {
            abort(403);
        }

        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        Message::query()->create([
            'chat_id' => $chat->id,
            'user_id' => $userId,
            'message' => $data['message'],
            'is_read' => false,
        ]);

        $chat->update(['last_message_at' => now()]);

        return back();
    }
}
