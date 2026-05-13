<?php

namespace Xin\Support;

final class Url
{

	/**
	 * 解析 URL 组件
	 * @param string|null $url URL
	 * @param int $component URL 组件
	 * @return mixed
	 */
	private static function parseUrl(?string $url, int $component)
	{
		if (empty($url)) {
			return '';
		}

		return parse_url($url, $component);
	}

	/**
	 * 获取 URL 协议
	 * @param string|null $url URL
	 * @return string
	 */
	public static function schema(?string $url = null)
	{
		return self::parseUrl($url, PHP_URL_SCHEME) ?? '';
	}

	/**
	 * 获取 URL 主机名
	 * @param string|null $url URL
	 * @return string
	 */
	public static function host(?string $url = null)
	{
		return self::parseUrl($url, PHP_URL_HOST) ?? '';
	}

	/**
	 * 获取 URL 端口
	 * @param string|null $url URL
	 * @return int|null
	 */
	public static function port(?string $url = null, int $defaultPort = null)
	{
		$port = self::parseUrl($url, PHP_URL_PORT);
		return $port ?? $defaultPort;
	}

	/**
	 * 获取 URL 路径
	 * @param string|null $url URL
	 * @return string
	 */
	public static function path(?string $url = null, bool $withQuery = true)
	{
		if ($withQuery) {
			if (empty($url)) {
				return '';
			}

			$parts = parse_url($url);
			return self::build([
				'path' => $parts['path'] ?? '',
				'query' => $parts['query'] ?? '',
				'fragment' => $parts['fragment'] ?? '',
			]);
		} else {
			return self::parseUrl($url, PHP_URL_PATH) ?? '';
		}
	}

	/**
	 * 获取 URL 查询参数
	 * @param string|null $url URL
	 * @return string
	 */
	public static function fragment(?string $url = null)
	{
		return self::parseUrl($url, PHP_URL_FRAGMENT) ?? '';
	}

	/**
	 * 获取 URL 查询字符串
	 * @param string|null $url URL
	 * @return string
	 */
	public static function query(?string $url = null)
	{
		return self::parseUrl($url, PHP_URL_QUERY) ?? '';
	}

	/**
	 * 将数组转换为查询字符串
	 *
	 * @param array $params
	 * @return string
	 */
	public static function queryString(array $params)
	{
		return http_build_query($params, '', '&', PHP_QUERY_RFC3986);
	}

	/**
	 * 获取 URL 域名
	 * @param string|null $url URL
	 * @return string
	 */
	public static function domain(?string $url = null, bool $withPort = false)
	{
		if (empty($url)) {
			return '';
		}

		$parts = parse_url($url);

		return self::build([
			'scheme' => $parts['scheme'] ?? '',
			'host' => $parts['host'] ?? '',
			'port' => $withPort ? $parts['port'] ?? '' : '',
		]);
	}

	/**
	 * 解析 URL 查询字符串
	 * @param string|null $queryString
	 * @return array
	 */
	public static function parseQuery(?string $queryString = null)
	{
		if (empty($queryString)) {
			return [];
		}

		// 移除查询字符串前缀
		$separator = strpos($queryString, '?');
		if ($separator !== false) {
			$queryString = self::query($queryString);
		}

		parse_str($queryString, $query);

		return empty($query) ? [] : $query;
	}

	/**
	 * 替换或追加 URL 查询参数
	 * @param string|null $url URL
	 * @param array $replace 要替换或追加的参数键值对
	 * @return string 处理后的 URL
	 */
	public static function withQuery(?string $url = null, array $replace = [], int $encoding_type = PHP_QUERY_RFC1738)
	{
		if (empty($url)) {
			return $url;
		}

		$parts = parse_url($url);

		// 解析现有查询参数，合并/替换参数
		$query = array_merge(self::parseQuery($parts['query'] ?? ''), $replace);

		// 重新构建查询字符串
		$parts['query'] = http_build_query($query, '', '&', $encoding_type);

		return self::build($parts);
	}

	/**
	 * 构建 URL
	 * @param array $parts URL 组件键值对
	 * @return string
	 */
	public static function build(array $parts)
	{
		$url = '';

		if (!empty($parts['scheme'])) {
			$url .= $parts['scheme'] . '://';
		}

		if (!empty($parts['user'])) {
			$url .= $parts['user'];
			if (!empty($parts['pass'])) {
				$url .= ':' . $parts['pass'];
			}
			$url .= '@';
		}

		if (!empty($parts['host'])) {
			$url .= $parts['host'];
		}

		if (!empty($parts['port'])) {
			$url .= ':' . $parts['port'];
		}

		if (!empty($parts['path'])) {
			$url .= $parts['path'];
		}

		if (isset($parts['query']) && $parts['query'] !== '') {
			$url .= '?' . $parts['query'];
		}

		if (!empty($parts['fragment'])) {
			$url .= '#' . $parts['fragment'];
		}

		return $url;
	}

	/**
	 * 匹配 URL
	 * @param string|null $url
	 * @param string $pattern
	 * @return false|int
	 */
	public static function match(?string $url = null, string $pattern)
	{
		if (empty($url)) {
			return false;
		}

		return preg_match($pattern, $url);

		$checkUrlArr = explode("?", $checkUrl, 2);
		$checkPath = $checkUrlArr[0];

		// 匹配路径
		if ($checkPath != $currentPath) {
			return false;
		}

		$checkQueryStr = isset($checkUrlArr[1]) ? $checkUrlArr[1] : '';
		if ($checkQueryStr) {
			parse_str($checkQueryStr, $checkQuery);
		} else {
			$checkQuery = [];
		}

		foreach ($checkQuery as $k => $v) {
			if (!isset($currentQuery[$k]) || $currentQuery[$k] != $v) {
				return false;
			}
		}

		return true;
	}

	/**
	 * 将路径连接到一个URL。
	 *
	 * @param string|null $baseUri 基础URI
	 * @param string|null $subPath 子路径
	 * @return string
	 */
	public static function concat(?string $baseUri, ?string $subPath)
	{
		if (empty($baseUri)) {
			$baseUri = '';
		}

		if (empty($subPath)) {
			$subPath = '';
		}

		return rtrim($baseUri, '/') . '/' . ltrim($subPath, '/');
	}
}
