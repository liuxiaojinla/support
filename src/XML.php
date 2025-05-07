<?php

namespace Xin\Support;

use SimpleXMLElement;

final class XML
{

	/**
	 * 将XML转换成数组
	 *
	 * @param string $xml
	 * @return mixed
	 */
	public static function parse($xml, int $options = LIBXML_NOCDATA)
	{
		return json_decode(json_encode(self::parseAsElement($xml, $options)), true);
	}

	/**
	 * 将XML转换成SimpleXMLElement对象
	 *
	 * @param string $xml
	 * @return SimpleXMLElement
	 */
	public static function parseAsElement($xml, int $options = LIBXML_NOCDATA)
	{
		// 检查 PHP 版本以决定是否需要调用 libxml_disable_entity_loader()
		if (version_compare(PHP_VERSION, '8.0.0', '<')) {
			//将XML转为array,禁止引用外部xml实体
			libxml_disable_entity_loader(true);
		}

		return simplexml_load_string($xml, 'SimpleXMLElement', $options);
	}

	/**
	 * 数组转XML(微信)
	 *
	 * @param array $param 要转换的数组
	 * @param string $root 根元素
	 * @param string $tag 指定元素标签名称，主要用于索引数组
	 * @return string
	 */
	public static function encode($param, $root = 'xml', $tag = '')
	{
		if (!is_array($param) || count($param) <= 0) {
			return '';
		}

		$xml = '';
		foreach ($param as $key => $val) {
			$key = empty($tag) ? $key : $tag;
			if (is_int($val)) {
				$xml .= "<" . $key . ">" . $val . "</" . $key . ">";
				$root = !empty($tag) ? '' : $root;
			} elseif (is_array($val)) {
				$tempRoot = Arr::isAssoc($param) ? $key : '';
				$tempTag = (Arr::isAssoc($param) && !Arr::isAssoc($val)) ? $key : '';
				$xml .= self::encode($val, $tempRoot, $tempTag);
			} else {
				$xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
				$root = !empty($tag) ? '' : $root;
			}
		}

		return (empty($root) ? "" : "<$root>") . $xml . (empty($root) ? "" : "</$root>");
	}

}
