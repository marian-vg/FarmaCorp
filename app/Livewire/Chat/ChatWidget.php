<?php

namespace App\Livewire\Chat;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ChatWidget extends Component
{
    public string $body = '';

    public string $userSearch = '';

    public ?int $selectedConversationId = null;

    /**
     * Define listeners dynamically to avoid issues with null placeholders.
     */
    public function getListeners(): array
    {
        $userId = auth()->id();
        $listeners = [
            'echo-presence:online,here' => 'onPresenceUpdate',
            'echo-presence:online,joining' => 'onPresenceUpdate',
            'echo-presence:online,leaving' => 'onPresenceUpdate',
            "echo-private:App.Models.User.{$userId},MessageSent" => 'onGlobalMessageReceived',
        ];

        if ($this->selectedConversationId) {
            $listeners["echo-private:chat.{$this->selectedConversationId},MessageSent"] = 'onMessageSent';
        }

        return $listeners;
    }

    public function onGlobalMessageReceived($payload): void
    {
        // This will refresh the conversations list and the unread count
        unset($this->conversations);
        unset($this->totalUnreadCount);
    }

    /**
     * Get the list of conversations for the authenticated user.
     * Optimized query to count unread messages based on pivot data.
     */
    #[Computed]
    public function conversations(): Collection
    {
        $userId = auth()->id();

        return auth()->user()->conversations()
            ->with([
                'participants',
                'messages' => fn ($query) => $query->latest()->limit(1),
            ])
            ->withCount(['messages as unread_count' => function ($query) use ($userId) {
                $query->where('sender_id', '!=', $userId)
                    ->whereColumn('messages.created_at', '>', 'conversation_user.last_read_at');
            }])
            ->get()
            ->sortByDesc(fn ($conversation) => $conversation->messages->first()?->created_at ?? $conversation->created_at);
    }

    #[Computed]
    public function totalUnreadCount(): int
    {
        return $this->conversations->sum('unread_count');
    }

    #[Computed]
    public function availableUsers(): Collection
    {
        $query = User::where('id', '!=', auth()->id());

        if (strlen($this->userSearch) >= 2) {
            $query->where(function ($q) {
                $q->where('name', 'ilike', "%{$this->userSearch}%")
                    ->orWhere('email', 'ilike', "%{$this->userSearch}%");
            });
        }

        return $query->limit(20)->get();
    }

    /**
     * Get the messages for the selected conversation.
     */
    #[Computed]
    public function chatMessages(): Collection
    {
        if (! $this->selectedConversationId) {
            return collect();
        }

        $conversation = Conversation::find($this->selectedConversationId);

        if (! $conversation || ! $this->isParticipant($conversation)) {
            return collect();
        }

        return $conversation->messages()
            ->with('sender')
            ->latest()
            ->limit(20)
            ->get()
            ->reverse();
    }

    public function selectConversation(int $id): void
    {
        $this->selectedConversationId = $id;
        $this->markAsRead();
        $this->reset(['body', 'userSearch']);
    }

    public function startConversation(int $userId): void
    {
        $currentUserId = auth()->id();

        // Buscar si ya existe una conversación privada entre ambos
        $existingConversation = Conversation::where('is_group', false)
            ->whereHas('participants', function ($query) use ($currentUserId) {
                $query->where('user_id', $currentUserId);
            })
            ->whereHas('participants', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->first();

        if ($existingConversation) {
            $this->selectedConversationId = $existingConversation->id;
        } else {
            // Crear nueva conversación
            $conversation = Conversation::create([
                'is_group' => false,
            ]);

            $conversation->participants()->attach([$currentUserId, $userId]);
            $this->selectedConversationId = $conversation->id;
        }

        $this->markAsRead();
        $this->reset(['body', 'userSearch']);
        $this->dispatch('close-modal', name: 'new-conversation-modal');
    }

    public function markAsRead(): void
    {
        if (! $this->selectedConversationId) {
            return;
        }

        auth()->user()->conversations()->updateExistingPivot($this->selectedConversationId, [
            'last_read_at' => now(),
        ]);
    }

    public function sendMessage(): void
    {
        if (! $this->selectedConversationId) {
            return;
        }

        $conversation = Conversation::findOrFail($this->selectedConversationId);

        abort_unless(
            $this->isParticipant($conversation),
            403,
            'No tienes permiso para enviar mensajes en esta conversación.'
        );

        $this->validate([
            'body' => 'required|string|max:1000',
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => auth()->id(),
            'body' => $this->body,
        ]);

        MessageSent::dispatch($message);

        $this->markAsRead();
        $this->reset('body');
        $this->dispatch('message-sent-locally');
    }

    public function onMessageSent($payload): void
    {
        $this->markAsRead();
        $this->dispatch('message-received');
    }

    public function onPresenceUpdate($users): void
    {
        $userIds = collect($users)->pluck('id')->toArray();
        $this->dispatch('presence-updated', ['userIds' => $userIds]);
    }

    private function isParticipant(Conversation $conversation): bool
    {
        return $conversation->participants()->where('user_id', auth()->id())->exists();
    }

    public function render()
    {
        return view('livewire.chat.chat-widget');
    }
}
