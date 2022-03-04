<?php

declare(strict_types=1);

namespace Codivapps\LaravelQueryLog\Listeners;

use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Log\Logger;

class QueryLogger
{
    private Logger $logger;

    /**
     * QueryLogger constructor
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Handle QueryExecuted event
     */
    public function handle(QueryExecuted $event): void
    {
        $formattedSql = $this->sqlFormatter($event->sql, $event->bindings, $event->connection);

        $msg = sprintf(
            "Query from '%s' connection. Time %f ms. SQL:\n%s",
            $event->connectionName,
            $event->time,
            $formattedSql
        );

        $this->logger->debug($msg);
    }

    /**
     * Insert bindings and formats sql
     */
    private function sqlFormatter(string $sql, array $bindings, Connection $connection): string
    {
        if (empty($bindings)) {
            return $sql;
        }

        $pdo = $connection->getPdo();

        foreach ($bindings as $key => $binding) {
            $regex = $this->selectRegex($key);

            if (is_string($binding)) {
                $binding = $pdo->quote($binding);
            } elseif (is_bool($binding)) {
                $binding = $bindings === true ? 'true' : 'false';
            } elseif (is_null($binding)) {
                $binding = 'null';
            }

            $sql = preg_replace($regex, $binding, $sql, 1);
        }

        return $sql;
    }

    /**
     * Select regex for binding
     *
     * @param int|string $key
     */
    private function selectRegex($key): string
    {
        return is_int($key)
            ? "/\?(?=(?:[^'\\\']*'[^'\\\']*')*[^'\\\']*$)/"
            : "/:{$key}(?=(?:[^'\\\']*'[^'\\\']*')*[^'\\\']*$)/";
    }
}