<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Chat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    /**
     * Список чатов пользователя.
     */
    public function index(Request $request): View
    {
        $userId = $request->user()->id;

        $chats = Chat::query()
            ->with(['ad', 'buyer', 'seller'])
            ->where('buyer_id', $userId)
            ->orWhere('seller_id', $userId)
            ->latest('last_message_at')
            ->paginate(20);

        return view('chats.index', compact('chats'));
    }

    /**
     * Создать/открыть чат по объявлению.
     */
    public function store(Request $request, Ad $ad): RedirectResponse
    {
        if ($request->user()->id === $ad->user_id) {
            return back()->with('status', 'Нельзя писать самому себе.');
        }

        $chat = Chat::query()->firstOrCreate([
            'ad_id' => $ad->id,
            'buyer_id' => $request->user()->id,
        ], [
            'seller_id' => $ad->user_id,
            'last_message_at' => now(),
        ]);

        return redirect()->route('chats.show', $chat);
    }

    /**
     * Просмотр чата.
     */
    public function show(Request $request, Chat $chat): View
    {
        $userId = $request->user()->id;

        if (! in_array($userId, [$chat->buyer_id, $chat->seller_id], true)) {
            abort(403);
        }

        $chat->load(['ad', 'buyer', 'seller', 'messages.user']);

        return view('chats.show', compact('chat'));
    }
}
