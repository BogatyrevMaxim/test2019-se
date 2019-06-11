<?php

namespace src\Integration\DataProvider;

class SomeDataProvider implements DataProviderInterface
{
    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $user;

    public function __construct($host, $user, $password)
    {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * @return array
     * @throws DataProviderException
     */
    public function getData(): array
    {
        // prepare url
        $data = [];
        try {
            // use guzzle
            // $data = ...
        } catch (\Throwable $exception) {
            // Выше по типу можно понять какого рода ошибка и сделать спец обработку
            throw new DataProviderException($exception->getMessage(), $exception->getCode(), $exception);
        }

        return $data;
    }
}