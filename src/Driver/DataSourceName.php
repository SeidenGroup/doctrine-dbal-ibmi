<?php

declare(strict_types=1);

namespace DoctrineDbalIbmi\Driver;

/**
 * IBM DB2 DSN
 *
 * @see https://github.com/doctrine/dbal/pull/4066
 */
final class DataSourceName
{
    /** @var string */
    private $dsn;

    private function __construct(string $dsn)
    {
        $this->dsn = $dsn;
    }

    public function toString(): string
    {
        return $this->dsn;
    }

    /**
     * Get the connection parameters.
     *
     * @return array<string, string>
     */
    public function getConnectionParameters(): array
    {
        $params = [];

        foreach (explode(';', $this->dsn) as $param) {
            [$key, $value] = explode('=', $param, 2);
            $params[$key] = $value;   
        }

        return $params;
    }

    /**
     * Creates the object from an array representation
     *
     * @param array<string, mixed> $params
     */
    public static function fromArray(array $params): self
    {
        $chunks = [];

        foreach ($params as $key => $value) {
            if (!is_scalar($value)) {
                continue;
            }

            $chunks[] = sprintf('%s=%s', $key, (string) $value);
        }

        return new self(implode(';', $chunks));
    }

    /**
     * Creates the object from the given DBAL connection parameters.
     *
     * @param array<string, mixed> $params
     */
    public static function fromConnectionParameters(array $params): self
    {
        assert(isset($params['driverClass']));

        if (!isset($params['host'])) { 
            if (OdbcDriver::class === $params['driverClass'] && isset($params['dsn']) && is_string($params['dsn'])) {
                return new self($params['dsn']);
            }

            if (DB2Driver::class === $params['driverClass'] && isset($params['dbname']) && is_string($params['dbname'])) {
                return new self($params['dbname']);
            }
        }

        $dsnParams = [];

        foreach (
            [
                'driver' => 'DRIVER',
                'host' => OdbcDriver::class === $params['driverClass'] ? 'SYSTEM' : 'HOSTNAME',
                'port' => 'PORT',
                'protocol' => 'PROTOCOL',
                'dbname' => 'DATABASE',
                'user' => 'UID',
                'password' => 'PWD',
            ] as $dbalParam => $dsnParam
        ) {
            if (!isset($params[$dbalParam]) || !is_scalar($params[$dbalParam])) {
                continue;
            }

            $dsnParams[$dsnParam] = $params[$dbalParam];
        }

        return self::fromArray($dsnParams);
    }
}
