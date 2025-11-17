<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Récupérer les conversations (messages envoyés et reçus)
        $messages = Message::where(function($query) use ($user) {
            $query->where('from_user_id', $user->id)
                  ->orWhere('to_user_id', $user->id);
        })
        ->with(['fromUser', 'toUser'])
        ->latest()
        ->paginate(30);

        return response()->json($messages);
    }

    /**
     * Récupérer le thread de conversation avec un utilisateur spécifique
     */
    public function thread(User $user, Request $request)
    {
        $currentUser = $request->user();

        // Vérifier que l'utilisateur peut accéder à cette conversation
        if ($currentUser->isPatient() && $user->isPatient()) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $messages = Message::where(function($query) use ($currentUser, $user) {
            $query->where(function($q) use ($currentUser, $user) {
                $q->where('from_user_id', $currentUser->id)
                  ->where('to_user_id', $user->id);
            })->orWhere(function($q) use ($currentUser, $user) {
                $q->where('from_user_id', $user->id)
                  ->where('to_user_id', $currentUser->id);
            });
        })
        ->with(['fromUser', 'toUser'])
        ->orderBy('created_at', 'asc')
        ->get();

        // Marquer les messages comme lus
        Message::where('from_user_id', $user->id)
            ->where('to_user_id', $currentUser->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json($messages);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'to_user_id' => 'required|exists:users,id',
            'content' => 'required|string|max:5000',
        ]);

        // Vérifier les permissions (clinicien peut envoyer à n'importe qui, patient uniquement à son clinicien)
        $toUser = User::findOrFail($validated['to_user_id']);
        $fromUser = $request->user();

        if ($fromUser->isPatient() && $toUser->isPatient()) {
            return response()->json(['error' => 'Un patient ne peut pas envoyer de message à un autre patient'], 403);
        }

        $message = Message::create([
            'from_user_id' => $fromUser->id,
            'to_user_id' => $validated['to_user_id'],
            'content' => $validated['content'],
        ]);

        return response()->json($message->load(['fromUser', 'toUser']), 201);
    }

    public function markAsRead(Message $message, Request $request)
    {
        if ($message->to_user_id !== $request->user()->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $message->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json($message);
    }
}

