<?php

abstract class NetWorkRequest implements IRequest
{
    /**
     * @var EzConnection $connection
     */
    private $connection;

    /**
     * @return EzConnection
     */
    public function getConnection(): EzConnection
    {
        return $this->connection;
    }

    /**
     * @param EzConnection $connection
     */
    public function setConnection(EzConnection $connection): void
    {
        $this->connection = $connection;
    }

    public function toArray(): array {
        return get_object_vars($this);
    }
}