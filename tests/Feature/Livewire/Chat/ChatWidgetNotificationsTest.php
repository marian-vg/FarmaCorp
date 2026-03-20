<?php

use App\Livewire\Chat\ChatWidget;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
    
    $this->conversation = Conversation::create(['is_group' => false]);
    $this->conversation->participants()->attach([$this->user->id, $this->otherUser->id], ['last_read_at' => now()->subDay()]);
});

it('correctly calculates total unread count', function () {
    Message::create([
        'conversation_id' => $this->conversation->id,
        'sender_id' => $this->otherUser->id,
        'body' => 'Unread message 1',
        'created_at' => now(),
    ]);

    Message::create([
        'conversation_id' => $this->conversation->id,
        'sender_id' => $this->otherUser->id,
        'body' => 'Unread message 2',
        'created_at' => now(),
    ]);

    Livewire::actingAs($this->user)
        ->test(ChatWidget::class)
        ->assertSet('totalUnreadCount', 2);
});

it('resets unread count when selecting a conversation', function () {
    Message::create([
        'conversation_id' => $this->conversation->id,
        'sender_id' => $this->otherUser->id,
        'body' => 'Unread message',
        'created_at' => now(),
    ]);

    Livewire::actingAs($this->user)
        ->test(ChatWidget::class)
        ->assertSet('totalUnreadCount', 1)
        ->call('selectConversation', $this->conversation->id)
        ->assertSet('totalUnreadCount', 0);
});

it('lists all users when search is empty', function () {
    Livewire::actingAs($this->user)
        ->test(ChatWidget::class)
        ->set('userSearch', '')
        ->assertSet('availableUsers', function ($users) {
            return $users->contains($this->otherUser);
        });
});

it('refreshes on global message notification', function () {
    $component = Livewire::actingAs($this->user)
        ->test(ChatWidget::class)
        ->assertSet('totalUnreadCount', 0);

    // Simulate another message from the other user
    Message::create([
        'conversation_id' => $this->conversation->id,
        'sender_id' => $this->otherUser->id,
        'body' => 'New message',
        'created_at' => now(),
    ]);

    // Dispatch the Echo event manually to the component
    $component->dispatch('echo-private:App.Models.User.' . $this->user->id . ',MessageSent', [
        'id' => 1,
        'body' => 'New message',
        'sender_id' => $this->otherUser->id,
        'conversation_id' => $this->conversation->id,
    ]);

    $component->assertSet('totalUnreadCount', 1);
});
