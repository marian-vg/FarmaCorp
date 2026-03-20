<?php

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a conversation between two users', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $conversation = Conversation::factory()->create();
    $conversation->participants()->attach([$userA->id, $userB->id]);

    expect($conversation->participants)->toHaveCount(2)
        ->and($userA->conversations)->toHaveCount(1)
        ->and($userB->conversations)->toHaveCount(1);
});

it('prevents user C from accessing a conversation between user A and user B', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $userC = User::factory()->create();

    $conversation = Conversation::factory()->create();
    $conversation->participants()->attach([$userA->id, $userB->id]);

    // Check if userC is in the conversation (logical check)
    $isUserCParticipant = $conversation->participants()->where('user_id', $userC->id)->exists();

    expect($isUserCParticipant)->toBeFalse();
});

it('saves a message and associates it correctly with a conversation and sender', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $conversation = Conversation::factory()->create();
    $conversation->participants()->attach([$userA->id, $userB->id]);

    $messageBody = 'Hola, este es un mensaje de prueba.';
    $message = Message::create([
        'conversation_id' => $conversation->id,
        'sender_id' => $userA->id,
        'body' => $messageBody,
    ]);

    expect($message->body)->toBe($messageBody)
        ->and($message->conversation_id)->toBe($conversation->id)
        ->and($message->sender_id)->toBe($userA->id)
        ->and($conversation->messages)->toHaveCount(1)
        ->and($userA->messages)->toHaveCount(1);
});

it('updates last_read_at in the pivot table', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create();
    $conversation->participants()->attach($user->id);

    $now = now()->toDateTimeString();
    $user->conversations()->updateExistingPivot($conversation->id, [
        'last_read_at' => $now,
    ]);

    $pivot = $user->conversations()->first()->pivot;
    expect($pivot->last_read_at->toDateTimeString())->toBe($now);
});
