<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMqService
{
    private string $host     = 'iae-sso.virtualfri.id';
    private int    $port     = 5672;
    private string $user     = 'KEY-MHS-157';
    private string $password = 'KEY-MHS-157';
    private string $exchange = 'iae.central.exchange';

    /**
     * Publish event ke RabbitMQ dosen
     * Dipanggil saat resep baru berhasil dibuat
     */
    public function publishEvent(string $eventName, array $payload): array
    {
        try {
            $connection = new AMQPStreamConnection(
                $this->host,
                $this->port,
                $this->user,
                $this->password
            );

            $channel = $connection->channel();

            $channel->exchange_declare(
                $this->exchange,
                'topic',
                false,
                true,
                false
            );

            $message = new AMQPMessage(
                json_encode([
                    'event'     => $eventName,
                    'timestamp' => now()->toIso8601String(),
                    'service'   => 'E-Healthcare-Farmasi-dan-Obat',
                    'team_id'   => 'TEAM-13',
                    'payload'   => $payload,
                ]),
                ['content_type' => 'application/json', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
            );

            $channel->basic_publish($message, $this->exchange, $eventName);

            $channel->close();
            $connection->close();

            Log::info('[RabbitMQ] Event published', [
                'event'   => $eventName,
                'payload' => $payload,
            ]);

            return ['success' => true, 'event' => $eventName];
        } catch (\Exception $e) {
            Log::error('[RabbitMQ] Exception saat publish event', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
