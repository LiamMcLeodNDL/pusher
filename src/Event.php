<?php

declare(strict_types=1);

namespace ApiClients\Client\Pusher;

class Event implements \JsonSerializable
{
    /**
     * @var string
     */
    private $event;

    /**
     * @var string
     */
    private $channel;

    /**
     * @var array
     */
    public $data;

    /**
     * @param string $event
     * @param array  $data
     * @param string $channel
     */
    public function __construct(string $event, array $data, string $channel = '')
    {
        $this->event = $event;
        $this->data = $data;
        $this->channel = $channel;
    }

    public static function createFromMessage(array $message): self
    {
        //* Standard Out for Pusher based events
        if (strpos($message['event'], "pusher") !== false) {
            echo "[" . date("Y-m-d H:i:s") . "] Received: " . $message['event'], PHP_EOL;
        }
        //* For Connection + Other Websocket Related Events
        if (isset($message['data'])) {
            return new self(
                $message['event'],
                \is_array($message['data']) ? $message['data'] : \json_decode($message['data'], true),
                $message['channel'] ?? ''
            );
        } else {
            return new self(
                $message['event'],
                [],
                $message['channel'] ?? ''
            );
        }
    }


    public function jsonSerialize()
    {
        return \json_encode(['event' => $this->event, 'data' => $this->data, 'channel' => $this->channel]);
    }

    /**
     * @return string
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    public static function isError(Event $event): bool
    {
        return $event->getEvent() === 'pusher:error';
    }

    public static function subscriptionSucceeded(Event $event): bool
    {
        return $event->getEvent() !== 'pusher_internal:subscription_succeeded';
    }

    public static function connectionEstablished(Event $event): bool
    {
        return $event->getEvent() === 'pusher:connection_established';
    }

    public static function subscribeOn(string $appId, string $channel, string $secret, string $socketId): array
    {
        $channel = 'private-' . $channel;
        $signature = "{$socketId}:{$channel}";
        return [
            'event' => 'pusher:subscribe', 'data' => [
                'auth' => $appId . ':' . hash_hmac('sha256', $signature, $secret),
                'channel' => $channel,
            ]
        ];
    }

    public static function unsubscribeOn(string $channel): array
    {
        return ['event' => 'pusher:unsubscribe', 'data' => ['channel' => $channel]];
    }

    public static function ping(): array
    {
        return ['event' => 'pusher:ping'];
    }
}
