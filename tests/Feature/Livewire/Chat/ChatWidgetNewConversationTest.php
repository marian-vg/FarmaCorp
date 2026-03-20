<?php

use App\Livewire\Chat\ChatWidget;
use App\Models\Conversation;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
});

it('can search for users to start a conversation', function () {
    Livewire::actingAs($this->user)
        ->test(ChatWidget::class)
        ->set('userSearch', substr($this->otherUser->name, 0, 3))
        ->assertSet('availableUsers', function ($users) {
            return $users->contains($this->otherUser);
        });
});

it('creates a new conversation if it does not exist', function () {
    expect(Conversation::count())->toBe(0);

    Livewire::actingAs($this->user)
        ->test(ChatWidget::class)
        ->call('startConversation', $this->otherUser->id)
        ->assertSet('selectedConversationId', function ($id) {
            return $id !== null;
        });

    expect(Conversation::count())->toBe(1);
    $conversation = Conversation::first();
    expect($conversation->participants)->toHaveCount(2);
    expect($conversation->participants->pluck('id'))->toContain($this->user->id, $this->otherUser->id);
});

it('opens an existing conversation instead of creating a new one', function () {
    // Manually create a conversation
    $conversation = Conversation::create(['is_group' => false]);
    $conversation->participants()->attach([$this->user->id, $this->otherUser->id]);

    expect(Conversation::count())->toBe(1);

    Livewire::actingAs($this->user)
        ->test(ChatWidget::class)
        ->call('startConversation', $this->otherUser->id)
        ->assertSet('selectedConversationId', $conversation->id);

    expect(Conversation::count())->toBe(1); // Still 1
});
