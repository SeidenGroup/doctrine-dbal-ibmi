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
    private $string;

    /** @param string $string */
    private function __construct($string)
    {
        $this->string = $string;
    }

    /** @return string */
    public function toString()
    {
        return $this->string;
    }

    /**
     * Creates the object from an array representation
     *
     * @param array<string, string> $params
     */
    public static function fromArray(array $params): self
    {
        if (!isset($params['protocol']) || '' === $params['protocol']) {
            $params['protocol'] = 'TCPIP';
        }

        $chunks = [];

        foreach ($params as $key => $value) {
            $chunks[] = sprintf('%s=%s', $key, $value);
        }

        return new self(implode(';', $chunks));
    }

    /**
     * Creates the object from the given DBAL connection parameters.
     *
     * @param array<string, string> $params
     */
    public static function fromConnectionParameters(array $params): self
    {
        if (isset($params['dbname']) && strpos($params['dbname'], '=') !== false) {
            return new self($params['dbname']);
        }

        $dsnParams = [];

        foreach (
            [
                'host'     => 'HOSTNAME',
                'port'     => 'PORT',
                'protocol' => 'PROTOCOL',
                'dbname'   => 'DATABASE',
                'user'     => 'UID',
                'password' => 'PWD',
            ] as $dbalParam => $dsnParam
        ) {
            if (! isset($params[$dbalParam])) {
                continue;
            }

            $dsnParams[$dsnParam] = $params[$dbalParam];
        }

        return self::fromArray($dsnParams);
    }
}
