<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SerializesChat;
use App\Models\Conversation;
use App\Models\Doctor;
use Illuminate\Http\Request;

/**
 * The patient's side of doctor chat. A patient can only reach a doctor who has
 * the feature switched on, and only while the follow-up window from their last
 * visit is still open — see Doctor::chatOpenFor().
 */
class ClientChatController extends Controller
{
    use SerializesChat;

    private const SIDE = Conversation::SIDE_CLIENT;

    public function index()
    {
        $conversations = $this->conversations();
        $inbox = $this->inboxPayload($conversations, self::SIDE);

        return view('client.chat.index', [
            'threads' => $inbox['threads'],
            // Doctors with an open window the patient has no thread with yet.
            'availableDoctors' => $this->doctorsOpenToChat()
                ->whereNotIn('id', $conversations->pluck('doctor_id'))
                ->values(),
        ]);
    }

    public function threads()
    {
        return response()->json($this->inboxPayload($this->conversations(), self::SIDE));
    }

    /** This patient's threads, newest first, with everything the inbox needs. */
    private function conversations()
    {
        return $this->client()->conversations()
            ->with(['doctor', 'client', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->get();
    }

    public function messages(Conversation $conversation)
    {
        $this->authorizeThread($conversation);
        $conversation->markReadBy(self::SIDE);

        $messages = $conversation->messages()->orderBy('id')->get()
            ->map(fn ($m) => $this->messagePayload($m, self::SIDE));

        return response()->json([
            'thread' => $this->singleThreadPayload($conversation->fresh()->load(['doctor', 'client', 'latestMessage']), self::SIDE),
            'messages' => $messages,
        ]);
    }

    public function send(Request $request, Conversation $conversation)
    {
        $this->authorizeThread($conversation);

        abort_unless(
            $conversation->doctor->chatOpenFor($this->client()),
            403,
            __('app.chat.window_closed')
        );

        $validated = $request->validate(['body' => 'required|string|max:2000']);
        $message = $conversation->post(self::SIDE, trim($validated['body']));

        return response()->json(['message' => $this->messagePayload($message, self::SIDE)], 201);
    }

    /**
     * Open (or reopen) the thread with a doctor the patient has seen recently.
     * Returns the thread id so the widget can jump straight into it.
     */
    public function start(Request $request, Doctor $doctor)
    {
        $client = $this->client();

        abort_unless($doctor->chatOpenFor($client), 403, __('app.chat.window_closed'));

        $conversation = Conversation::between($doctor, $client);

        if ($request->expectsJson()) {
            return response()->json(['conversation_id' => $conversation->id], 201);
        }

        return redirect()->route('client.chat.index', ['thread' => $conversation->id]);
    }

    /**
     * Doctors this patient may start a new chat with: chat switched on and a
     * visit recent enough that the window has not closed.
     */
    private function doctorsOpenToChat()
    {
        $client = $this->client();

        return Doctor::where('chat_enabled', true)
            ->whereHas('appointments', fn ($q) => $q
                ->where('client_id', $client->id)
                ->where('status', '!=', 'cancelled')
                ->where('scheduled_at', '<=', now()))
            ->with('specialization')
            ->get()
            ->filter(fn (Doctor $d) => $d->chatOpenFor($client))
            ->values();
    }

    private function client()
    {
        return auth('client')->user();
    }

    private function authorizeThread(Conversation $conversation): void
    {
        abort_unless($conversation->client_id === $this->client()->id, 403);
    }
}
