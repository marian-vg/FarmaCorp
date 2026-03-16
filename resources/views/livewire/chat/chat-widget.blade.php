<div>
    <div style="display: flex; height: 400px; border: 1px solid #ccc;">
        <!-- Conversation List -->
        <div style="width: 30%; border-right: 1px solid #ccc; overflow-y: auto;">
            <h3 style="padding: 10px;">Conversaciones</h3>
            <ul>
                @foreach ($this->conversations as $conversation)
                    <li 
                        wire:click="selectConversation({{ $conversation->id }})"
                        style="cursor: pointer; padding: 10px; border-bottom: 1px solid #eee; background: {{ $selectedConversationId === $conversation->id ? '#f0f0f0' : 'transparent' }}"
                    >
                        <strong>{{ $conversation->is_group ? $conversation->name : ($conversation->participants->where('id', '!=', auth()->id())->first()?->name ?? 'Chat') }}</strong>
                        <p style="font-size: 0.8em; color: #666; margin: 0;">{{ $conversation->messages->first()?->body }}</p>
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Message Box -->
        <div style="flex-grow: 1; display: flex; flex-direction: column;">
            @if ($selectedConversationId)
                <div style="flex-grow: 1; overflow-y: auto; padding: 10px;">
                    @foreach ($this->chatMessages as $message)
                        <div style="margin-bottom: 10px; text-align: {{ $message->sender_id === auth()->id() ? 'right' : 'left' }}">
                            <div style="display: inline-block; padding: 8px; border-radius: 8px; background: {{ $message->sender_id === auth()->id() ? '#dcf8c6' : '#fff' }}; border: 1px solid #ddd;">
                                <small style="display: block; font-weight: bold;">{{ $message->sender->name }}</small>
                                <span>{{ $message->body }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div style="padding: 10px; border-top: 1px solid #ccc;">
                    <form wire:submit.prevent="sendMessage" style="display: flex;">
                        <input 
                            wire:model="body" 
                            type="text" 
                            placeholder="Escribe un mensaje..." 
                            style="flex-grow: 1; padding: 8px;"
                        >
                        <button type="submit" style="padding: 8px 16px;">Enviar</button>
                    </form>
                </div>
            @else
                <div style="flex-grow: 1; display: flex; align-items: center; justify-content: center; color: #999;">
                    Selecciona una conversación para empezar
                </div>
            @endif
        </div>
    </div>
</div>
