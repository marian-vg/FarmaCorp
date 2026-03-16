<?php

use App\Events\MessageSent;
use App\Livewire\Chat\ChatWidget;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Broadcast;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('dispatches MessageSent event when a message is sent via Livewire', function () {
    Event::fake();

    $user = User::factory()->create();
    $conversation = Conversation::factory()->create();
    $conversation->participants()->attach($user->id);

    Livewire::actingAs($user)
        ->test(ChatWidget::class)
        ->set('selectedConversationId', $conversation->id)
        ->set('body', 'Test message for broadcasting')
        ->call('sendMessage');

    Event::assertDispatched(MessageSent::class, function ($event) use ($conversation) {
        return $event->message->body === 'Test message for broadcasting' 
            && (int) $event->message->conversation_id === (int) $conversation->id;
    });
});

it('authorizes channel access correctly in routes/channels.php', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create(); // Not a participant
    $conversation = Conversation::factory()->create();
    $conversation->participants()->attach($userA->id);

    $channels = Broadcast::getChannels();
    $callback = $channels["chat.{conversationId}"];

    // Directly call the callback with different users
    expect($callback($userA, $conversation->id))->toBeTrue()
        ->and($callback($userB, $conversation->id))->toBeFalse();
});
