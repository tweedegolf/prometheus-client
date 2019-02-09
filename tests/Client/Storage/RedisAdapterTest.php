<?php
declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use TweedeGolf\PrometheusClient\Storage\AdapterTrait;
use TweedeGolf\PrometheusClient\Storage\RedisAdapter;
use TweedeGolf\PrometheusClient\Storage\StorageAdapterInterface;

class RedisAdapterTest extends TestCase
{
    use AdapterTrait;

    private $host = 'localhost';

    /**
     * @test
     * @dataProvider redisDriverDataProvider
     * @param string $redisClientClassName
     */
    public function itSetsValue(string $redisClientClassName)
    {
        $redisClient = $this->initializeInstance($redisClientClassName);
        $redisClient->flushall();

        $testKey = 'test222';
        $testLabels = ['label11'];
        $redisAdapter = new RedisAdapter($redisClient);

        $this->setValue($redisAdapter, $testKey, $testLabels);

        $writtenResult = $redisClient->keys('*');

        $labelHash = $this->getLabelHash($testLabels);
        $expectedResult = [
            StorageAdapterInterface::DEFAULT_KEY_PREFIX . '||' . $testKey . '|' . $labelHash,
            StorageAdapterInterface::DEFAULT_KEY_PREFIX . '|' . $testKey,
            StorageAdapterInterface::DEFAULT_KEY_PREFIX . '|__labels|' . $testKey . '|' . $labelHash
        ];
        foreach ($writtenResult as $item) {
            static::assertContains($item, $expectedResult);
        }
    }

    /**
     * @test
     * @dataProvider redisDriverDataProvider
     * @param string $redisClientClassName
     */
    public function itGetsValues(string $redisClientClassName)
    {
        $redisClient = $this->initializeInstance($redisClientClassName);
        $redisClient->flushall();

        $testKey = 'test333';
        $testLabels = ['label22'];

        $redisAdapter = new RedisAdapter($redisClient);
        $this->setValue($redisAdapter, $testKey, $testLabels);

        $values = $redisAdapter->getValues($testKey);

        $expected = [
            [
                0,
                $testLabels
            ]
        ];
        static::assertEquals($expected, $values);
    }

    /**
     * @test
     * @dataProvider redisDriverDataProvider
     * @param string $redisClientClassName
     */
    public function itGetsValue(string $redisClientClassName)
    {
        $redisClient = $this->initializeInstance($redisClientClassName);
        $redisClient->flushall();

        $testKey = 'test333';
        $testLabels = ['label22'];

        $redisAdapter = new RedisAdapter($redisClient);
        $this->setValue($redisAdapter, $testKey, $testLabels);

        static::assertTrue((bool)$redisAdapter->hasValue($testKey, $testLabels));
        $value = $redisAdapter->getValue($testKey, $testLabels);

        static::assertInternalType('float', $value);
        static::assertSame(0.0, $value);
    }

    /**
     * @test
     * @dataProvider redisDriverDataProvider
     * @param string $redisClientClassName
     */
    public function itIncs(string $redisClientClassName)
    {
        $redisClient = $this->initializeInstance($redisClientClassName);
        $redisClient->flushall();

        $testKey = 'test333';
        $testLabels = ['label22'];

        $redisAdapter = new RedisAdapter($redisClient);
        $this->setValue($redisAdapter, $testKey, $testLabels);

        $redisAdapter->incValue($testKey, 1, 0, $testLabels);
        $value = $redisAdapter->getValue($testKey, $testLabels);
        static::assertSame(1.0, $value);

        $redisAdapter->incValue($testKey, 1, 0, $testLabels);
        $value = $redisAdapter->getValue($testKey, $testLabels);
        static::assertSame(2.0, $value);

        $redisClient->flushall();

        $redisAdapter->incValue($testKey, 1, 0, $testLabels);
        $value = $redisAdapter->getValue($testKey, $testLabels);
        static::assertSame(1.0, $value);
    }

    /**
     * @return array
     */
    public function redisDriverDataProvider(): array
    {
        return [
            [\Redis::class],
            [Predis\Client::class],
        ];
    }

    /**
     * @param $redisClientClassName
     * @return \Predis\Client|\Redis
     */
    private function initializeInstance($redisClientClassName)
    {
        if ($redisClientClassName === \Redis::class) {
            $redisClient = new \Redis();
            $redisClient->connect($this->host);
        }

        if ($redisClientClassName === \Predis\Client::class) {
            $redisClient = new Predis\Client(
                [
                    'scheme' => 'tcp',
                    'host' => $this->host,
                ]
            );
        }
        return $redisClient;
    }

    /**
     * @param $redisClient
     * @param string $testKey
     * @param array $testLabels
     */
    private function setValue(RedisAdapter $redisAdapter, string $testKey, array $testLabels)
    {
        $redisAdapter->setValue($testKey, 0, $testLabels);
    }

    /**
     * Dummy implementation to use trait
     */
    protected function getPrefix()
    {
    }
}