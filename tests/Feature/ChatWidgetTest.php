<?php

use App\Events\MessageSent;
use App\Livewire\Chat\ChatWidget;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('renders the component and lists conversations', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create();
    $conversation->participants()->attach($user->id);

    Livewire::actingAs($user)
        ->test(ChatWidget::class)
        ->assertSee('Chat Interno');
});

it('can select a conversation and see its messages', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create();
    $conversation->participants()->attach($user->id);

    $message = Message::create([
        'conversation_id' => $conversation->id,
        'sender_id' => $user->id,
        'body' => 'Hola, este es un mensaje previo.',
    ]);

    Livewire::actingAs($user)
        ->test(ChatWidget::class)
        ->call('selectConversation', $conversation->id)
        ->assertSet('selectedConversationId', $conversation->id)
        ->assertSee('Hola, este es un mensaje previo.');
});

it('can send a message and save it in the database', function () {
    // Fake events to avoid broadcasting attempt to Reverb server
    Event::fake([MessageSent::class]);

    $user = User::factory()->create();
    $conversation = Conversation::factory()->create();
    $conversation->participants()->attach($user->id);

    Livewire::actingAs($user)
        ->test(ChatWidget::class)
        ->set('selectedConversationId', $conversation->id)
        ->set('body', 'Nuevo mensaje de prueba')
        ->call('sendMessage')
        ->assertSet('body', '');

    $this->assertDatabaseHas('messages', [
        'conversation_id' => $conversation->id,
        'sender_id' => $user->id,
    ]);

    $message = Message::where('conversation_id', $conversation->id)->latest()->first();
    expect($message->body)->toBe('Nuevo mensaje de prueba');
    
    Event::assertDispatched(MessageSent::class);
});

it('reacts to Echo MessageSent event and updates messages', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create();
    $conversation->participants()->attach($user->id);

    // Create a real message in DB so it shows up when component re-renders
    $message = Message::create([
        'id' => 999,
        'body' => 'Mensaje desde el WebSocket',
        'sender_id' => User::factory()->create()->id,
        'conversation_id' => $conversation->id,
    ]);

    $component = Livewire::actingAs($user)
        ->test(ChatWidget::class)
        ->set('selectedConversationId', $conversation->id);

    // Simulate the Echo event dispatching. 
    // In Livewire 4, this triggers the method linked to the listener.
    $payload = [
        'id' => $message->id,
        'body' => $message->body,
        'sender_id' => $message->sender_id,
        'sender_name' => 'Otro Usuario',
        'created_at' => now()->format('H:i'),
        'conversation_id' => $conversation->id,
    ];

    $component->dispatch("echo-private:chat.{$conversation->id},MessageSent", $payload)
        ->assertSee('Mensaje desde el WebSocket');
});

it('authorizes message sending only to participants', function () {
    $userA = User::factory()->create();
    $userC = User::factory()->create();
    $conversation = Conversation::factory()->create();
    $conversation->participants()->attach($userA->id); // User C is NOT a participant

    Livewire::actingAs($userC)
        ->test(ChatWidget::class)
        ->set('selectedConversationId', $conversation->id)
        ->set('body', 'Mensaje intruso')
        ->call('sendMessage')
        ->assertStatus(403);

    $this->assertDatabaseMissing('messages', [
        'body' => 'Mensaje intruso',
    ]);
});

it('requires a body to send a message', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create();
    $conversation->participants()->attach($user->id);

    Livewire::actingAs($user)
        ->test(ChatWidget::class)
        ->set('selectedConversationId', $conversation->id)
        ->set('body', '')
        ->call('sendMessage')
        ->assertHasErrors(['body' => 'required']);
});
