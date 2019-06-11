<?php

use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use src\Integration\DataProvider\DataProviderInterface;

/**
 * Class ServiceSomeData
 */
class ServiceSomeData
{
    /**
     * @var DataProviderInterface
     */
    private $dataProvider;

    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * @var LoggerInterface
     */
    private $logger;

    private $cacheKeyPrefix = 'some_';

    /**
     * @var int
     */
    private $cacheLifeTimeSecond;

    public function __construct(
        DataProviderInterface $dataProvider,
        CacheItemPoolInterface $cache,
        LoggerInterface $logger,
        int $cacheLifeTimeSecond
    ) {
        $this->dataProvider = $dataProvider;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->cacheLifeTimeSecond = $cacheLifeTimeSecond;
    }

    /**
     * @param array $input
     * @return array
     */
    public function getData(array $input)
    {
        $cacheKey = $this->getCacheKey($input);
        if (!$this->isCacheIsset($cacheKey)) {
            $this->refreshData($input);
        }

        return $this->getCacheData($cacheKey);
    }


    /**
     * @param $input
     * @return bool
     */
    public function refreshData($input): bool
    {
        try {
            $data = $this->dataProvider->getData($input);
            $cacheKey = $this->getCacheKey($input);
            $this->saveCacheData($cacheKey, $data);
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage(), [
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return false;
        }

        return true;
    }

    /**
     * @param string $cacheKey
     * @return array
     */
    private function getCacheData(string $cacheKey): array
    {
        /** @var CacheItemInterface $cacheItem */
        $cacheItem = $this->cache->getItem($cacheKey);

        return $cacheItem->isHit() ? $cacheItem->get() : [];
    }

    /**
     * @param string $cacheKey
     * @param array $data
     * @return bool
     * @throws Exception
     */
    private function saveCacheData(string $cacheKey, array $data): bool
    {
        /** @var CacheItemInterface $cacheItem */
        $cacheItem = $this->cache->getItem($cacheKey);
        $cacheItem->set($data)->expiresAt(
            (new DateTime())->modify('+' . $this->cacheLifeTimeSecond . ' sec')
        );

        return $this->cache->save($cacheItem);
    }

    /**
     * @param string $cacheKey
     * @return bool
     */
    private function isCacheIsset(string $cacheKey): bool
    {
        return $this->cache->hasItem($cacheKey);
    }

    /**
     * @param array $input
     * @return string
     */
    private function getCacheKey(array $input)
    {
        return sprintf('%s%s', $this->cacheKeyPrefix, json_encode($input));
    }
}