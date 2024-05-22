<?php

namespace Xin\Support;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;

class CallbackUrl
{
    /**
     * @var Client
     */
    protected static $client = null;

    /**
     * @var callable
     */
    protected static $hook;

    /**
     * @return Client
     */
    protected static function client(): Client
    {
        if (self::$client === null) {
            self::$client = new Client([
                'timeout' => 5,
                'connect_timeout' => 5,
            ]);
        }

        return self::$client;
    }

    /**
     * @param Client $client
     * @return void
     */
    public static function setClient(Client $client)
    {
        self::$client = $client;
    }

    /**
     * @param callable $hook
     * @return void
     */
    public static function setHook(callable $hook)
    {
        self::$hook = $hook;
    }

    /**
     *
     * @param string $url
     * @param array $data
     * @param array $options
     * @return string|null
     */
    public static function post(string $url, array $data = [], array $options = []): ?string
    {
        try {
            $options = self::buildRequestOptions($data, $options);
            self::hook("request", [
                'url' => $url,
                'async' => false,
                'options' => $options,
            ]);
            return self::client()->post($url, $options)->getBody()->getContents();
        } catch (GuzzleException $exception) {
            self::hook("error", $exception);
        }

        return null;
    }

    /**
     * @param string $url
     * @param array $data
     * @param array $options
     * @return PromiseInterface
     */
    public static function postSync(string $url, array $data = [], array $options = []): PromiseInterface
    {
        self::hook("request", [
            'url' => $url,
            'async' => true,
            'options' => $options,
        ]);
        $promise = self::client()->postAsync($url, self::buildRequestOptions($data, $options))->then(function (Response $response) {
            return $response->getBody()->getContents();
        }, function ($e) {
            self::hook("error", $e);
        });

        $promise->wait();

        return $promise;
    }

    /**
     * @param array $data
     * @param array $options
     * @return array
     */
    protected static function buildRequestOptions(array $data, array $options): array
    {
        return array_replace_recursive(['json' => $data], $options);
    }

    /**
     * @param string $type
     * @param mixed $data
     * @return void
     */
    protected static function hook(string $type, $data)
    {
        self::$hook && call_user_func(self::$hook, $type, $data);
    }
}
