<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Appointment;
use App\Models\ChatMessage;
use App\Models\Client;
use App\Models\Conversation;
use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Shared shape for the chat inbox, used by both the JSON endpoints and the
 * full-page lists. The doctor and the patient see the very same widget, only
 * mirrored: each sees the other party's name and avatar, and "mine" flips
 * depending on which side is looking.
 *
 * The inbox is polled, so unread counts and chat windows are resolved for the
 * whole list in two queries rather than two per thread.
 */
trait SerializesChat
{
    /**
     * Every thread in one side's inbox, plus the total the badge shows.
     *
     * @param  Collection<int, Conversation>  $conversations
     */
    protected function inboxPayload(Collection $conversations, string $side): array
    {
        $unread = $this->unreadCounts($conversations, $side);
        $windows = $this->chatWindowEnds($conversations);

        $threads = $conversations
            ->map(fn (Conversation $c) => $this->threadPayload(
                $c,
                $side,
                $unread[$c->id] ?? 0,
                $windows[$c->id] ?? null,
            ))
            ->values();

        return [
            'threads' => $threads,
            'unread_total' => $threads->sum('unread'),
        ];
    }

    /** A single thread, for the one-thread endpoints. */
    protected function singleThreadPayload(Conversation $conversation, string $side): array
    {
        return $this->threadPayload(
            $conversation,
            $side,
            $conversation->unreadCountFor($side),
            $conversation->doctor->chatWindowEndsFor($conversation->client),
        );
    }

    /** One bubble in the open thread. */
    protected function messagePayload(ChatMessage $message, string $side): array
    {
        return [
            'id' => $message->id,
            'body' => $message->body,
            'mine' => $message->isFrom($side),
            'at' => $message->created_at?->format('H:i'),
            'date' => $message->created_at?->translatedFormat('j M Y'),
            'read' => $message->read_at !== null,
        ];
    }

    private function threadPayload(Conversation $conversation, string $side, int $unread, ?Carbon $windowEnds): array
    {
        $other = $side === Conversation::SIDE_DOCTOR ? $conversation->client : $conversation->doctor;
        $last = $conversation->latestMessage;
        $windowOpen = (bool) $windowEnds?->isFuture();

        return [
            'id' => $conversation->id,
            'name' => $other?->name ?? '—',
            'avatar' => $this->avatarFor($other),
            'preview' => $last ? Str::limit($last->body, 60) : null,
            'preview_mine' => $last?->isFrom($side) ?? false,
            'last_at' => $conversation->last_message_at?->diffForHumans(),
            'unread' => $unread,
            // The doctor may always answer an existing thread; the patient only
            // while the window from their last visit is still open.
            'can_send' => $side === Conversation::SIDE_DOCTOR
                ? (bool) $conversation->doctor->chat_enabled
                : $windowOpen,
            'window_ends' => $windowEnds?->translatedFormat('j M Y'),
            'window_open' => $windowOpen,
        ];
    }

    /**
     * Unread count per thread in one query.
     *
     * @return array<int, int>
     */
    private function unreadCounts(Collection $conversations, string $side): array
    {
        if ($conversations->isEmpty()) {
            return [];
        }

        return ChatMessage::query()
            ->whereIn('conversation_id', $conversations->pluck('id'))
            ->where('sender', Conversation::otherSide($side))
            ->whereNull('read_at')
            ->groupBy('conversation_id')
            ->selectRaw('conversation_id, COUNT(*) as total')
            ->pluck('total', 'conversation_id')
            ->map(fn ($n) => (int) $n)
            ->all();
    }

    /**
     * When each thread's chat window closes, resolved for the whole list in one
     * query: the patient's most recent past, non-cancelled visit with that
     * doctor plus the doctor's configured number of days. Null means the window
     * never opened — chat is off, or there is no qualifying visit.
     *
     * @return array<int, Carbon|null>
     */
    private function chatWindowEnds(Collection $conversations): array
    {
        if ($conversations->isEmpty()) {
            return [];
        }

        $lastVisits = Appointment::query()
            ->whereIn('doctor_id', $conversations->pluck('doctor_id')->unique())
            ->whereIn('client_id', $conversations->pluck('client_id')->unique())
            ->where('status', '!=', 'cancelled')
            ->where('scheduled_at', '<=', now())
            ->groupBy('doctor_id', 'client_id')
            ->selectRaw('doctor_id, client_id, MAX(scheduled_at) as last_visit')
            ->get()
            ->keyBy(fn ($row) => $row->doctor_id . ':' . $row->client_id);

        $ends = [];
        foreach ($conversations as $conversation) {
            $visit = $lastVisits->get($conversation->doctor_id . ':' . $conversation->client_id);

            $ends[$conversation->id] = ($visit && $conversation->doctor->chat_enabled)
                ? Carbon::parse($visit->last_visit)->addDays($conversation->doctor->chatWindowDays())
                : null;
        }

        return $ends;
    }

    private function avatarFor(Doctor|Client|null $party): string
    {
        $name = $party?->name ?: '?';

        $url = $party instanceof Doctor
            ? $party->getFirstMediaUrl('profile_image')
            : ($party?->avatar ? asset('storage/' . $party->avatar) : '');

        return $url ?: 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=0d9488&color=fff';
    }
}
