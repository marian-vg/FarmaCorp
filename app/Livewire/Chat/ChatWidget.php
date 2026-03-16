<?php

namespace App\Livewire\Chat;

use App\Models\Conversation;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ChatWidget extends Component
{
    public string $body = '';

    public ?int $selectedConversationId = null;

    /**
     * Define listeners dynamically to avoid issues with null placeholders.
     */
    public function getListeners(): array
    {
        if (! $this->selectedConversationId) {
            return [];
        }

        return [
            "echo-private:chat.{$this->selectedConversationId},MessageSent" => 'onMessageSent',
        ];
    }

    /**
     * Get the list of conversations for the authenticated user.
     * Eager loading to avoid N+1 and sorting by the latest message.
     */
    #[Computed]
    public function conversations(): Collection
    {
        return auth()->user()->conversations()
            ->with([
                'participants',
                'messages' => fn($query) => $query->latest()->limit(1)
            ])
            ->get()
            ->sortByDesc(fn($conversation) => $conversation->messages->first()?->created_at ?? $conversation->created_at);
    }

    /**
     * Get the messages for the selected conversation.
     */
    #[Computed]
    public function chatMessages(): Collection
    {
        if (!$this->selectedConversationId) {
            return collect();
        }

        $conversation = Conversation::find($this->selectedConversationId);

        if (!$conversation || !$this->isParticipant($conversation)) {
            return collect();
        }

        return $conversation->messages()
            ->with('sender')
            ->latest()
            ->limit(20)
            ->get()
            ->reverse(); // Show in chronological order for the chat
    }

    /**
     * Select a conversation to view its messages.
     */
    public function selectConversation(int $id): void
    {
        $this->selectedConversationId = $id;
        $this->reset('body');
    }

    /**
     * Send a message to the active conversation.
     */
    public function sendMessage(): void
    {
        if (!$this->selectedConversationId) {
            return;
        }

        $conversation = Conversation::findOrFail($this->selectedConversationId);

        // Security check: ensure the user is a participant
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

        \App\Events\MessageSent::dispatch($message);

        $this->reset('body');
        
        $this->dispatch('message-sent-locally');
    }

    /**
     * Listen for the MessageSent event via Laravel Echo.
     */
    public function onMessageSent($payload): void
    {
        // Re-render happens automatically. We dispatch a browser event for scrolling.
        $this->dispatch('message-received');
    }

    /**
     * Helper to check if the current user is a participant in a conversation.
     */
    private function isParticipant(Conversation $conversation): bool
    {
        return $conversation->participants()->where('user_id', auth()->id())->exists();
    }

    public function render()
    {
        return view('livewire.chat.chat-widget');
    }
}
