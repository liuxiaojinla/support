<?php

namespace Xin\Support;

final class Json
{
	public const ENCODE_DEFAULT = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

	/**
	 * 编码JSON，并输出到指定的文件
	 * @param string $file
	 * @param mixed $value
	 * @param int $flags
	 * @param int $depth
	 * @return false|int
	 */
	public static function encodeToFile(string $file, $value, int $flags = self::ENCODE_DEFAULT, int $depth = 512)
	{
		return file_put_contents($file, self::encode($value, $flags, $depth));
	}

	/**
	 * 编码JSON
	 * @param mixed $value
	 * @param int $flags
	 * @param int $depth
	 * @return false|string
	 */
	public static function encode($value, int $flags = self::ENCODE_DEFAULT, int $depth = 512)
	{
		return json_encode($value, $flags, $depth);
	}

	/**
	 * 编码JSON，并格式化输出后到指定的文件
	 * @param string $file
	 * @param mixed $value
	 * @param int $flags
	 * @param int $depth
	 * @return false|int
	 */
	public static function prettyToFile(string $file, $value, int $flags = self::ENCODE_DEFAULT, int $depth = 512)
	{
		return file_put_contents($file, self::pretty($value, $flags, $depth));
	}

	/**
	 * 编码JSON，并格式化输出
	 * @param mixed $value
	 * @param int $flags
	 * @param int $depth
	 * @return false|string
	 */
	public static function pretty($value, int $flags = self::ENCODE_DEFAULT, int $depth = 512)
	{
		return self::encode($value, $flags | JSON_PRETTY_PRINT, $depth);
	}

	/**
	 * 从文件里面读取JSON，并进行解码
	 * @param string $file
	 * @param bool|null $associative
	 * @param int $depth
	 * @param int $flags
	 * @return mixed
	 */
	public static function decodeFromFile(string $file, ?bool $associative = null, int $depth = 512, int $flags = 0)
	{
		return self::decode(file_get_contents($file), $associative, $depth, $flags);
	}

	/**
	 * JSON解码
	 * @param string $json
	 * @param bool|null $associative
	 * @param int $depth
	 * @param int $flags
	 * @return mixed
	 */
	public static function decode(string $json, ?bool $associative = null, int $depth = 512, int $flags = 0)
	{
		return json_decode($json, $associative, $depth, $flags);
	}

	/**
	 * 从文件里面读取JSON解码，并转换成Array
	 * @param string $file
	 * @param int $depth
	 * @param int $flags
	 * @return array
	 */
	public static function arrayFromFile(string $file, int $depth = 512, int $flags = 0)
	{
		return self::array(file_get_contents($file), $depth, $flags);
	}

	/**
	 * JSON解码，并转换成Array
	 * @param string $json
	 * @param int $depth
	 * @param int $flags
	 * @return array
	 */
	public static function array(string $json, int $depth = 512, int $flags = 0)
	{
		return self::decode($json, true, $depth, $flags);
	}

	/**
	 * 从文件里面读取JSON解码，并转换成 \stdClass
	 * @param string $file
	 * @param int $depth
	 * @param int $flags
	 * @return array
	 */
	public static function objectFromFile(string $file, int $depth = 512, int $flags = 0)
	{
		return self::object(file_get_contents($file), $depth, $flags);
	}

	/**
	 * JSON解码，并转换成 \stdClass
	 * @param string $json
	 * @param int $depth
	 * @param int $flags
	 * @return array
	 */
	public static function object(string $json, int $depth = 512, int $flags = 0)
	{
		return self::decode($json, null, $depth, $flags);
	}
}
