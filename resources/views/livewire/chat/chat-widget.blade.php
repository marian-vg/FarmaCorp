<div 
    x-data="{ 
        open: false,
        selectedId: @entangle('selectedConversationId'),
        scrollDown() {
            this.$nextTick(() => {
                const el = this.$refs.messages;
                if (el) {
                    el.scrollTop = el.scrollHeight;
                }
            });
        }
    }"
    x-on:message-received.window="scrollDown()"
    x-on:message-sent-locally.window="scrollDown()"
    x-init="
        $watch('selectedId', () => scrollDown());
    "
    class="fixed bottom-4 right-4 z-50 flex flex-col items-end"
>
    <!-- Chat Window -->
    <div 
        x-show="open" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-10 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-10 scale-95"
        class="mb-4 w-80 sm:w-96"
        style="display: none;"
    >
        <flux:card class="flex h-[500px] flex-col overflow-hidden p-0 shadow-xl">
            <!-- Header -->
            <div class="flex items-center justify-between border-b bg-zinc-50 p-3 dark:bg-zinc-900/50">
                <div class="flex items-center gap-2">
                    <flux:icon name="chat-bubble-left-right" class="text-zinc-500" />
                    <flux:heading size="sm" class="font-bold">Chat Interno</flux:heading>
                </div>
                <flux:button variant="ghost" icon="x-mark" size="xs" x-on:click="open = false" />
            </div>

            <div class="flex flex-1 overflow-hidden">
                <!-- Sidebar (Conversation List) -->
                <div class="w-20 overflow-y-auto border-r bg-zinc-50/50 p-2 dark:bg-zinc-900/20 sm:w-28">
                    <div class="flex flex-col gap-2">
                        @foreach ($this->conversations as $conversation)
                            <button 
                                wire:click="selectConversation({{ $conversation->id }})"
                                class="relative flex flex-col items-center gap-1 rounded-lg p-1 transition-colors hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ $selectedConversationId === $conversation->id ? 'bg-zinc-100 dark:bg-zinc-800 ring-1 ring-zinc-200 dark:ring-zinc-700' : '' }}"
                                title="{{ $conversation->is_group ? $conversation->name : ($conversation->participants->where('id', '!=', auth()->id())->first()?->name ?? 'Chat') }}"
                            >
                                <flux:avatar 
                                    size="sm" 
                                    name="{{ $conversation->is_group ? $conversation->name : ($conversation->participants->where('id', '!=', auth()->id())->first()?->name ?? '?') }}" 
                                    class="shadow-sm"
                                />
                                <span class="max-w-full overflow-hidden text-ellipsis whitespace-nowrap text-[10px] text-zinc-600 dark:text-zinc-400">
                                    {{ Str::limit($conversation->is_group ? $conversation->name : ($conversation->participants->where('id', '!=', auth()->id())->first()?->name ?? 'Chat'), 8) }}
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Chat Body -->
                <div class="flex flex-1 flex-col overflow-hidden bg-white dark:bg-zinc-950">
                    @if ($selectedConversationId)
                        <!-- Messages -->
                        <div 
                            x-ref="messages"
                            class="flex-1 overflow-y-auto p-4"
                        >
                            <div class="flex flex-col gap-3">
                                @forelse ($this->chatMessages as $message)
                                    <div 
                                        wire:key="msg-{{ $message->id }}"
                                        class="flex flex-col {{ $message->sender_id === auth()->id() ? 'items-end' : 'items-start' }}"
                                    >
                                        <div class="max-w-[85%] rounded-2xl px-3 py-2 text-sm {{ $message->sender_id === auth()->id() ? 'bg-zinc-800 text-white dark:bg-zinc-200 dark:text-zinc-900 rounded-tr-none' : 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100 rounded-tl-none' }}">
                                            @if ($message->sender_id !== auth()->id())
                                                <span class="mb-1 block text-[10px] font-bold opacity-70">{{ $message->sender->name }}</span>
                                            @endif
                                            <p class="leading-relaxed">{{ $message->body }}</p>
                                        </div>
                                        <span class="mt-1 text-[9px] text-zinc-400">{{ $message->created_at->format('H:i') }}</span>
                                    </div>
                                @empty
                                    <div class="flex h-full items-center justify-center text-zinc-400">
                                        <p class="text-xs italic">No hay mensajes aún.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Footer Input -->
                        <div class="border-t p-3">
                            <form 
                                wire:submit.prevent="sendMessage"
                                class="flex items-center gap-2"
                            >
                                <flux:input 
                                    wire:model="body" 
                                    placeholder="Escribe..." 
                                    size="sm"
                                    class="flex-1"
                                    autocomplete="off"
                                />
                                <flux:button 
                                    type="submit" 
                                    variant="primary" 
                                    size="xs" 
                                    icon="paper-airplane" 
                                    class="aspect-square rounded-full"
                                    wire:loading.attr="disabled"
                                />
                            </form>
                        </div>
                    @else
                        <div class="flex flex-1 flex-col items-center justify-center p-6 text-center text-zinc-400">
                            <flux:icon name="chat-bubble-oval-left" size="lg" class="mb-2 opacity-20" />
                            <p class="text-xs">Selecciona un chat para comenzar a conversar.</p>
                        </div>
                    @endif
                </div>
            </div>
        </flux:card>
    </div>

    <!-- Toggle Button -->
    <flux:button 
        variant="primary" 
        x-on:click="open = !open; if(open) scrollDown()"
        class="h-14 w-14 rounded-full shadow-2xl transition-transform hover:scale-110 active:scale-95"
    >
        <template x-if="!open">
            <flux:icon name="chat-bubble-left-right" class="h-6 w-6 text-white" />
        </template>
        <template x-if="open">
            <flux:icon name="chevron-down" class="h-6 w-6 text-white" />
        </template>
    </flux:button>
</div>
