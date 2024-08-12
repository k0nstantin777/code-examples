<?php

namespace App\Services\TelegramBot\Events;

use App\Domains\User\Models\User;
use App\Services\Language\Enums\LanguageCode;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LanguageSelected
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var User
     */
    public $user;

    /**
     * @var LanguageCode
     */
    public $lang;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user, LanguageCode $lang)
    {
        $this->user = $user;
        $this->lang = $lang;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|PrivateChannel
     */
    public function broadcastOn() : Channel|PrivateChannel
    {
        return new PrivateChannel('channel-name');
    }
}
