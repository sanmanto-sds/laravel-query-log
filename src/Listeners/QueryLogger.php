<?php

declare(strict_types=1);

namespace Codivapps\LaravelQueryLog\Listeners;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Log\Logger;
use PDO;

class QueryLogger
{
    private Logger $logger;
    private PDO $pdo;

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
        $this->pdo = $event->connection->getPdo();

        $formattedSql = $this->sqlFormatter($event->sql, $event->bindings);

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
    private function sqlFormatter(string $sql, array $bindings): string
    {
        if (empty($bindings)) {
            return $sql;
        }

        foreach ($bindings as $key => $binding) {
            $regex = $this->selectRegex($key);
            $correctBinding = $this->typeCorrection($binding);

            $sql = preg_replace($regex, $correctBinding, $sql, 1);
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

    /**
     * Correction binding value type
     *
     * @param mixed $value
     */
    private function typeCorrection($value): string
    {
        if (is_string($value)) {
            return $this->pdo->quote($value);
        }

        if (is_bool($value)) {
            return ($value === true) ? 'true' : 'false';
        }

        if (is_null($value)) {
            return 'null';
        }

        if (is_float($value)) {
            return $this->floatCorrection($value);
        }

        return (string) $value;
    }

    /**
     * Correction float binding value
     */
    private function floatCorrection(float $value): string
    {
        $strValue = (string) $value;
        $regex = '/^(?<base>\d+\.\d+)E(?<sign>[+-])(?<power>\d+)$/';

        if (preg_match($regex, $strValue, $match) !== 1) {
            return $strValue;
        }

        $decimal = $match['sign'] === '-'
            ? ((int) $match['power'] + strlen($match['base']))
            : 0;

        return rtrim(number_format($value, $decimal, '.', ''), '0');
    }
}