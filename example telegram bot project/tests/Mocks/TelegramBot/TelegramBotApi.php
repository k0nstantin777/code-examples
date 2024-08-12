<?php

namespace Tests\Mocks\TelegramBot;

use Psr\Http\Message\RequestInterface;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\Update;

class TelegramBotApi extends Api
{
    public const MESSAGE = 'message';
    public const CALLBACK_QUERY = 'callback_query';
    public const COMMAND = 'command';

    protected array $responses = [];

    /**
     * @var Message[]
     */
    protected array $sentMessages = [];

    public function getWebhookUpdate($shouldEmitEvent = true, ?RequestInterface $request = null): Update
    {
        return $this->getResponse();
    }

    public function sendMessage(array $params) : Message
    {
        $message = new Message($params);

        $this->sentMessages[] = $message;

        return $message;
    }

    public function sendChatAction(array $params): bool
    {
        return true;
    }

    /**
     * @return Message[]
     */
    public function getSentMessages() : array
    {
        return $this->sentMessages;
    }

    public function setResponses(array $responses): void
    {
        $this->responses = $responses;
    }

    public function setMessageUpdate(array $updateData = []) : Update
    {
        return new Update(
            array_merge([
                    'update_id' => 217632305,
                ], [
                    'message' => array_replace_recursive($this->getDefaultUpdateMessageData(), $updateData)
                ],
            ));
    }

    public function setCallbackQueryUpdate(array $updateData = []) : Update
    {
        return new Update(array_merge([
                'update_id' => 217632305,
            ], [
                'callback_query' => array_replace_recursive($this->getDefaultUpdateCallbackQueryData(), $updateData)
            ]
        ));
    }

    public function setCommandUpdate(array $updateData = []) : Update
    {
        return new Update(array_merge([
            'update_id' => 217632305,
        ], [
                'message' => array_replace_recursive($this->getDefaultUpdateCommandData(), $updateData)
           ]
        ));
    }

    private function getDefaultUpdateMessageData() : array
    {
        return [
            "message_id" => 442,
            "from" => [
                "id" => 468431435,
                "is_bot" => false,
                "first_name" => "Test",
                "username" => "TestUsername",
            ],
            "chat" => [
                "id" => 468431435,
                "first_name" => "Test",
                "username" => "TestUsername",
                "type" => "private"
            ],
            "date" => 1633101759,
            "text" => "test message"
        ];
    }

    private function getDefaultUpdateCommandData() : array
    {
        return [
            "message_id" => 442,
            "from" => [
                "id" => 468431435,
                "is_bot" => false,
                "first_name" => "Test",
                "username" => "TestUsername",
            ],
            "chat" => [
                "id" => 468431435,
                "first_name" => "Test",
                "username" => "TestUsername",
                "type" => "private"
            ],
            "date" => 1633101759,
            "text" => "/start_exchange",
            "entities" => [
                [
                    "offset" => 0,
                    "length" => 15,
                    "type" => "bot_command"
                ]
            ],
        ];
    }

    private function getDefaultUpdateCallbackQueryData() : array
    {
        return [
            "id" => "2011897697374352782",
            "from" => [
                "id" => 468431435,
                "is_bot" => false,
                "first_name" => "Test User",
                "username" => "TestUsername",
                "language_code" => "en"
            ],
            "message" => [
                "message_id" => 451,
                "from" => [
                    "id" => 2034726561,
                    "is_bot" => true,
                    "first_name" => "Exchanger Bot",
                    "username" => "exchanger_bot_test",
                ],
                "chat" => [
                    "id" => 468431435,
                    "first_name" => "Test User",
                    "username"=> "TestUsername",
                    "type" => "private",
                ],
                "date" => 1633102009,
                "text" => "Please select given currency",
                "reply_markup" => [
                    "inline_keyboard"=> [
                        [
                            "text" => "Bitcoin",
                            "callback_data" => "callback_data1"
                        ],
                        [
                            "text" => "Ethereum",
                            "callback_data" => "callback_data2"
                        ]
                    ]
                ],
            ],
            "chat_instance" => "6461658131202421598",
            "data" => "visit_section::1"
        ];
    }

    private function getResponse() : Update
    {
        $update = null;
        if (isset($this->responses[self::MESSAGE]) && is_array($this->responses[self::MESSAGE])) {
            $update = $this->setMessageUpdate($this->responses[self::MESSAGE]);
        }

        if (!$update && isset($this->responses[self::CALLBACK_QUERY]) &&
            is_array($this->responses[self::CALLBACK_QUERY])) {
            $update = $this->setCallbackQueryUpdate($this->responses[self::CALLBACK_QUERY]);
        }

        if (!$update && isset($this->responses[self::COMMAND]) &&
            is_array($this->responses[self::COMMAND])) {
            $update = $this->setCommandUpdate($this->responses[self::COMMAND]);
        }

        if (!$update) {
            $update = $this->setMessageUpdate();
        }

        return $update;
    }
}
