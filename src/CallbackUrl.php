<?php

namespace Xin\Support;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Str;

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
	 * 公共回调
	 * @param $url
	 * @param array $params
	 * @param bool $async
	 * @return mixed
	 */
	public static function callback($url, $params = [], $async = true)
	{
		if (class_exists($url)) {
			$instance = app($url);
			return app()->call([$instance, 'handle'], $params);
		} elseif (is_callable($url)) {
			return app()->call($url, $params);
		} elseif (Str::is("/http[s]?:\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is", $url)) {
			return $async ? self::postSync($url, $params) : self::post($url, $params);
		}

		throw new \InvalidArgumentException('Invalid callback url');
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
			'url'     => $url,
			'async'   => true,
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
	 * @param string $type
	 * @param mixed $data
	 * @return void
	 */
	protected static function hook(string $type, $data)
	{
		self::$hook && call_user_func(self::$hook, $type, $data);
	}

	/**
	 * @return Client
	 */
	protected static function client(): Client
	{
		if (self::$client === null) {
			self::$client = new Client([
				'timeout'         => 5,
				'connect_timeout' => 5,
			]);
		}

		return self::$client;
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
				'url'     => $url,
				'async'   => false,
				'options' => $options,
			]);
			return self::client()->post($url, $options)->getBody()->getContents();
		} catch (GuzzleException $exception) {
			self::hook("error", $exception);
		}

		return null;
	}
}
