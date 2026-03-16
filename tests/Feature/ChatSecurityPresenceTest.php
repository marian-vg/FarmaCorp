<?php

use App\Events\MessageSent;
use App\Livewire\Chat\ChatWidget;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('stores the message body encrypted in the database', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create();
    $conversation->participants()->attach($user->id);

    $plainText = 'Este es un mensaje secreto corporativo.';
    
    $message = Message::create([
        'conversation_id' => $conversation->id,
        'sender_id' => $user->id,
        'body' => $plainText,
    ]);

    expect($message->body)->toBe($plainText);

    $rawMessage = DB::table('messages')->where('id', $message->id)->first();
    expect($rawMessage->body)->not->toContain($plainText);
});

it('updates last_read_at when selecting a conversation', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create();
    $conversation->participants()->attach($user->id, ['last_read_at' => now()->subDay()->toDateTimeString()]);

    $oldReadAt = $user->conversations()->find($conversation->id)->pivot->last_read_at;

    Livewire::actingAs($user)
        ->test(ChatWidget::class)
        ->call('selectConversation', $conversation->id);

    $newReadAt = $user->conversations()->find($conversation->id)->pivot->last_read_at;
    
    expect($newReadAt->gt($oldReadAt))->toBeTrue();
});

it('correctly calculates unread messages for each conversation', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    
    // 1. Establecemos el pasado absoluto
    Carbon::setTestNow(now()->subMinutes(120)->startOfMinute());

    $conversation = Conversation::factory()->create();
    $conversation->participants()->attach($user->id);
    $conversation->participants()->attach($otherUser->id);

    // 2. Mensaje Antiguo (Leído)
    Carbon::setTestNow(now()->subMinutes(90)->startOfMinute());
    Message::create([
        'conversation_id' => $conversation->id,
        'sender_id' => $otherUser->id,
        'body' => 'Leído',
    ]);

    // 3. El usuario marca como leído en este punto
    Carbon::setTestNow(now()->subMinutes(60)->startOfMinute());
    $conversation->participants()->updateExistingPivot($user->id, [
        'last_read_at' => now()
    ]);

    // 4. Mensaje Nuevo (No Leído)
    Carbon::setTestNow(now()->subMinutes(30)->startOfMinute());
    Message::create([
        'conversation_id' => $conversation->id,
        'sender_id' => $otherUser->id,
        'body' => 'No leído',
    ]);

    // Devolvemos el reloj a la normalidad
    Carbon::setTestNow();

    // 5. LA NUEVA FORMA DE TESTEAR COMPUTED PROPERTIES EN LIVEWIRE 3
    $component = Livewire::actingAs($user)->test(ChatWidget::class);
    
    // Obtenemos la propiedad mágica directamente de la instancia
    $conversations = $component->instance()->conversations;

    // Buscamos nuestra conversación
    $currentChat = $conversations->firstWhere('id', $conversation->id);
    
    // Salvavidas de Pest: Verificamos que la conversación exista en la colección
    expect($currentChat)->not->toBeNull('La conversación no cargó en el ChatWidget.');
    
    // Validación de la lógica de negocio
    expect((int) $currentChat->unread_count)->toBe(1);
});

it('marks as read when receiving a message in an active conversation', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create();
    $conversation->participants()->attach($user->id, ['last_read_at' => now()->subDay()->toDateTimeString()]);

    $component = Livewire::actingAs($user)
        ->test(ChatWidget::class)
        ->set('selectedConversationId', $conversation->id);

    $beforeReadAt = $user->conversations()->find($conversation->id)->pivot->last_read_at;

    $component->call('onMessageSent', [
        'id' => 123,
        'conversation_id' => $conversation->id,
    ]);

    $afterReadAt = $user->conversations()->find($conversation->id)->pivot->last_read_at;
    
    expect($afterReadAt->gt($beforeReadAt))->toBeTrue();
});
