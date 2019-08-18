<?php

declare(strict_types=1);

namespace MediaWiki\Api;

use InvalidArgumentException;
use LogicException;
use RuntimeException;

class QueryLog
{
    protected const AVAILABLE_FIELDS = ['method', 'parameters', 'headers', 'cookies', 'response'];

    /**
     * @var array
     */
    protected $queryLog = [];

    /**
     * @param string $method
     * @param array $parameters
     * @param array $headers
     * @param array $cookies
     */
    public function logQuery(string $method, array $parameters, array $headers, array $cookies): void
    {
        $this->queryLog[] = [
            'method' => $method,
            'parameters' => $parameters,
            'headers' => $headers,
            'cookies' => $cookies,
        ];
    }

    /**
     * @param string|array $response
     */
    public function appendResponse($response): void
    {
        $lastLogRecord = array_pop($this->queryLog);

        $lastLogRecord['response'] = $response;

        $this->queryLog[] = $lastLogRecord;
    }

    /**
     * @param string[]|null $fields
     * @param int|null $count
     *
     * @return array
     */
    public function getLog($fields = null, $count = null): array
    {
        $defaultFields = ['method', 'parameters', 'response'];

        $fields = $fields ?? $defaultFields;

        if (count(array_diff($fields, self::AVAILABLE_FIELDS)) > 0) {
            $unknownFields = array_diff($fields, self::AVAILABLE_FIELDS);

            throw new RuntimeException(sprintf('Unknown log fields: %s', implode(', ', $unknownFields)));
        }

        if (count($fields) === 0) {
            throw new LogicException('At least one field should be specified');
        }

        if ( ! is_int($count) && $count !== null) {
            throw new InvalidArgumentException(sprintf('%s expects parameter 2 to be integer or null, %s given', __METHOD__, gettype($count)));
        }

        $log = $count === null ? $this->queryLog : array_slice($this->queryLog, $count * -1);

        if ($fields === self::AVAILABLE_FIELDS) {
            return $log;
        }

        $result = [];

        foreach ($log as $record) {
            $newRecord = [];

            foreach ($fields as $field) {
                if ( ! array_key_exists($field, $record)) {
                    continue;
                }

                $newRecord[$field] = $record[$field];
            }

            $result[] = $newRecord;
        }

        return $result;
    }

    /**
     * Clears query log and returns it.
     *
     * @return array
     */
    public function clearLog(): array
    {
        $result = $this->queryLog;

        $this->queryLog = [];

        return $result;
    }
}
