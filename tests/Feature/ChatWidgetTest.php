<?php

use App\Livewire\Chat\ChatWidget;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('renders the component and lists conversations', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create();
    $conversation->participants()->attach($user->id);

    Livewire::actingAs($user)
        ->test(ChatWidget::class)
        ->assertSee('Conversaciones')
        ->assertSee($conversation->is_group ? $conversation->name : 'Chat');
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
        ->assertSee($message->body)
        ->assertSee($user->name);
});

it('can send a message and save it in the database', function () {
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
        'body' => 'Nuevo mensaje de prueba',
    ]);
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
