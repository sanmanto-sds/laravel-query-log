<?php

declare(strict_types=1);

namespace Codivapps\LaravelQueryLog\Listeners;

use Illuminate\Database\Events\{ConnectionEvent, TransactionBeginning, TransactionCommitted, TransactionRolledBack};
use Illuminate\Log\Logger;
use RuntimeException;

class ConnectionLogger
{
    private Logger $logger;

    /**
     * ConnectionLogger constructor
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Handle ConnectionEvent's
     */
    public function handle(ConnectionEvent $event): void
    {
        $msg = sprintf(
            "Query from '%s' connection:\n%s",
            $event->connectionName,
            $this->getEventText(get_class($event))
        );

        $this->logger->debug($msg);
    }

    /**
     * Get text by event name
     *
     * @throws RuntimeException
     */
    private function getEventText(string $eventName): string
    {
        $events = [
            TransactionBeginning::class => 'TRANSACTION BEGIN',
            TransactionCommitted::class => 'TRANSACTION COMMIT',
            TransactionRolledBack::class => 'TRANSACTION ROLLBACK'
        ];

        if (!isset($events[$eventName])) {
            throw new RuntimeException(sprintf("Not supported event '%s'.", $eventName));
        }

        return $events[$eventName];
    }
}