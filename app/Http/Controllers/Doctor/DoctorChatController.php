<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Concerns\SerializesChat;
use App\Models\Conversation;
use Illuminate\Http\Request;

/**
 * The doctor's side of patient chat: the inbox, one thread at a time, and the
 * on/off switch that governs the whole feature for this doctor.
 */
class DoctorChatController extends DoctorDashboardController
{
    use SerializesChat;

    private const SIDE = Conversation::SIDE_DOCTOR;

    public function index(Request $request)
    {
        $doctor = $this->doctor($request);

        $inbox = $this->inboxPayload($this->conversationsFor($doctor), self::SIDE);

        return view('doctor.chat.index', [
            'doctor' => $doctor,
            'threads' => $inbox['threads'],
        ]);
    }

    /** Inbox rows for the header dropdown (polled). */
    public function threads(Request $request)
    {
        $conversations = $this->conversationsFor($this->doctor($request));

        return response()->json($this->inboxPayload($conversations, self::SIDE));
    }

    /** This doctor's threads, newest first, with everything the inbox needs. */
    private function conversationsFor($doctor)
    {
        return $doctor->conversations()
            ->with(['client', 'doctor', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->get();
    }

    /** Open a thread: its messages, marking the patient's lines as read. */
    public function messages(Request $request, Conversation $conversation)
    {
        $this->authorizeThread($request, $conversation);
        $conversation->markReadBy(self::SIDE);

        $messages = $conversation->messages()->orderBy('id')->get()
            ->map(fn ($m) => $this->messagePayload($m, self::SIDE));

        return response()->json([
            'thread' => $this->singleThreadPayload($conversation->fresh()->load(['client', 'doctor', 'latestMessage']), self::SIDE),
            'messages' => $messages,
        ]);
    }

    public function send(Request $request, Conversation $conversation)
    {
        $doctor = $this->authorizeThread($request, $conversation);

        // The switch is the doctor's own; turning chat off closes their outbox too.
        abort_unless($doctor->chat_enabled, 403, __('app.chat.disabled'));

        $validated = $request->validate(['body' => 'required|string|max:2000']);
        $message = $conversation->post(self::SIDE, trim($validated['body']));

        return response()->json(['message' => $this->messagePayload($message, self::SIDE)], 201);
    }

    /** The doctor's own chat settings: the switch and the follow-up window. */
    public function updateSettings(Request $request)
    {
        $doctor = $this->doctor($request);

        $validated = $request->validate([
            'chat_enabled' => 'nullable|boolean',
            'chat_window_days' => 'required|integer|min:1|max:365',
        ]);

        $doctor->update([
            'chat_enabled' => $request->boolean('chat_enabled'),
            'chat_window_days' => $validated['chat_window_days'],
        ]);

        return redirect()->route('doctor.chat.index')->with('success', __('app.chat.settings_saved'));
    }

    /** A doctor may only touch their own threads. */
    private function authorizeThread(Request $request, Conversation $conversation)
    {
        $doctor = $this->doctor($request);
        abort_unless($conversation->doctor_id === $doctor->id, 403);

        return $doctor;
    }
}
