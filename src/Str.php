<?php


namespace Xin\Support;

use Closure;
use Exception;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use RuntimeException;

/**
 * 字符串工具类
 */
final class Str
{

	/**
	 * 驼峰转下划线缓存
	 *
	 * @var array
	 */
	protected static $snakeCache = [];

	/**
	 * 下划线转驼峰(首字母小写) 缓存
	 *
	 * @var array
	 */
	protected static $camelCache = [];

	/**
	 * 下划线转驼峰(首字母大写)
	 *
	 * @var array
	 */
	protected static $studlyCache = [];

	/**
	 * 检查字符串中是否包含某些字符串
	 *
	 * @param string $haystack
	 * @param string|array $needles
	 * @return bool
	 */
	public static function contains($haystack, $needles)
	{
		foreach ((array)$needles as $needle) {
			if ($needle != '' && mb_strpos($haystack, $needle) !== false) {
				return true;
			}
		}

		return false;
	}

	/**
	 * 检查字符串是否以某些字符串开头
	 *
	 * @param string $haystack
	 * @param string|array $needles
	 * @return bool
	 */
	public static function startsWith($haystack, $needles)
	{
		foreach ((array)$needles as $needle) {
			if ('' != $needle && mb_strpos($haystack, $needle) === 0) {
				return true;
			}
		}

		return false;
	}

	/**
	 * 检查字符串是否以某些字符串结尾
	 *
	 * @param string $haystack
	 * @param string|array $needles
	 * @return bool
	 */
	public static function endsWith($haystack, $needles)
	{
		foreach ((array)$needles as $needle) {
			if ((string)$needle === self::substr($haystack, -self::length($needle))) {
				return true;
			}
		}

		return false;
	}

	/**
	 * 截取字符串
	 *
	 * @param string $string
	 * @param int $start
	 * @param int|null $length
	 * @return string
	 */
	public static function substr($string, $start, $length = null, $encoding = 'UTF-8')
	{
		return mb_substr($string, $start, $length, $encoding);
	}

	/**
	 * 获取字符串的长度
	 *
	 * @param string $value
	 * @return int
	 */
	public static function length($value)
	{
		return mb_strlen($value);
	}

	// /**
	//  * 字符串截取，支持中文和其他编码
	//  *
	//  * @param string $value 验证的值
	//  * @param int $start 开始位置
	//  * @param int $length 截取长度
	//  * @param string $charset 字符编码
	//  * @return string
	//  * @deprecated 废弃，请使用 substr()，mb_substr()
	//  */
	// public static function subString($value, $start = 0, $length = null, $charset = null)
	// {
	// 	if (function_exists("mb_substr")) {
	// 		$slice = mb_substr($value, $start, $length, $charset);
	// 	} elseif (function_exists('iconv_substr')) {
	// 		$length = is_null($length) ? iconv_strlen($value, $charset) : $length;
	// 		$charset = is_null($charset) ? ini_get("iconv.internal_encoding") : $charset;
	// 		$slice = iconv_substr($value, $start, $length, $charset);
	// 		if (false === $slice) {
	// 			$slice = '';
	// 		}
	// 	} else {
	// 		$re ['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
	// 		$re ['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
	// 		$re ['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
	// 		$re ['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
	// 		preg_match_all($re [$charset], $value, $match);
	// 		$slice = join("", array_slice($match [0], $start, $length));
	// 	}
	//
	// 	return $slice;
	// }

	/**
	 * 字符串转大写
	 *
	 * @param string $value
	 * @return string
	 */
	public static function upper($value)
	{
		return mb_strtoupper($value, 'UTF-8');
	}

	/**
	 * 字符串转小写
	 *
	 * @param string $value
	 * @return string
	 */
	public static function lower($value)
	{
		return mb_strtolower($value, 'UTF-8');
	}

	/**
	 * 驼峰转下划线
	 *
	 * @param string $value
	 * @param string $delimiter
	 * @param bool $isCache
	 * @return string
	 */
	public static function snake($value, $delimiter = '_', $isCache = true)
	{
		$key = $value;

		if (isset(self::$snakeCache[$key][$delimiter])) {
			return self::$snakeCache[$key][$delimiter];
		}

		if (!ctype_lower($value)) {
			$value = preg_replace('/\s+/u', '', $value);

			$value = self::lower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $value));
		}

		return $isCache ? self::$snakeCache[$key][$delimiter] = $value : $value;
	}

	/**
	 * 下划线转驼峰(首字母小写)
	 *
	 * @param string $value
	 * @param bool $isCache
	 * @return string
	 */
	public static function camel($value, $isCache = true)
	{
		if (isset(self::$camelCache[$value])) {
			return self::$camelCache[$value];
		}

		$value = lcfirst(self::studly($value));

		return $isCache ? self::$camelCache[$value] = $value : $value;
	}

	/**
	 * 下划线转驼峰(首字母大写)
	 *
	 * @param string $value
	 * @param bool $isCache
	 * @return string
	 */
	public static function studly($value, $isCache = true)
	{
		$key = $value;

		if (isset(self::$studlyCache[$key])) {
			return self::$studlyCache[$key];
		}

		$value = ucwords(str_replace(['-', '_'], ' ', $value));
		$value = str_replace(' ', '', $value);

		return $isCache ? self::$studlyCache[$key] = $value : $value;
	}

	/**
	 * 清除驼峰转下划线缓存
	 */
	public static function clearSnakeCache()
	{
		self::$snakeCache = [];
	}

	/**
	 * 清除下划线转驼峰(首字母小写)缓存
	 */
	public static function clearCamelCache()
	{
		self::$snakeCache = [];
	}

	/**
	 * 清除下划线转驼峰(首字母大写)缓存
	 */
	public static function clearStudlyCache()
	{
		self::$snakeCache = [];
	}

	/**
	 * 转为首字母大写的标题格式
	 *
	 * @param string $value
	 * @return string
	 */
	public static function title($value)
	{
		return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
	}

	/**
	 * 将英语的最后一个单词复数化，大写大小写字符串。
	 *
	 * @param string $value
	 * @param int $count
	 * @return string
	 */
	public static function pluralStudly($value, $count = 2)
	{
		$parts = preg_split('/(.)(?=[A-Z])/u', $value, -1, PREG_SPLIT_DELIM_CAPTURE);

		$lastWord = array_pop($parts);

		return implode('', $parts) . self::plural($lastWord, $count);
	}

	/**
	 * 获取英语单词的复数形式。
	 *
	 * @param string $value
	 * @param int $count
	 * @return string
	 */
	public static function plural($value, $count = 2)
	{
		return Pluralizer::plural($value, $count);
	}

	/**
	 * 获取英语单词的单数形式。
	 *
	 * @param string $value
	 * @return string
	 */
	public static function singular($value)
	{
		return Pluralizer::singular($value);
	}

	/**
	 * 转换字符编码
	 *
	 * @param string $input 数据源
	 * @param string $outputCharset 输出的字符编码
	 * @param string $inputCharset 输入的字符编码
	 * @return string|null
	 */
	public static function encoding($input, $outputCharset, $inputCharset = null)
	{
		if (is_null($input)) {
			return null;
		}

		if (function_exists("mb_convert_encoding")) {
			$output = mb_convert_encoding($input, $outputCharset, $inputCharset);
		} elseif (function_exists("iconv")) {
			$output = iconv($inputCharset, $outputCharset, $input);
		} else {
			throw new RuntimeException("不支持 $inputCharset 到 $outputCharset 编码！");
		}

		return $output;
	}

	/**
	 * 获取随机字符串
	 *
	 * @param int $length
	 * @param int $type
	 * @return string
	 */
	public static function random($length = 16, $type = 5)
	{
		$pool = [
			0 => '0123456789',
			1 => 'abcdefghijklmnopqrstuvwxyz',
			2 => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
		];

		$poolStr = '';
		if (0 == $type) {
			$poolStr = $pool[0];
		} elseif (1 == $type) {
			$poolStr = $pool[1];
		} elseif (2 == $type) {
			$poolStr = $pool[2];
		} elseif (3 == $type) {
			$poolStr = $pool[0] . $pool[1];
		} elseif (4 == $type) {
			$poolStr = $pool[1] . $pool[2];
		} elseif (5 == $type) {
			$poolStr = $pool[0] . $pool[1] . $pool[2];
		}

		return self::substr(str_shuffle(str_repeat($poolStr, $length)), 0, $length);
	}

	/**
	 * 生成一个 UUID (version 4).
	 *
	 * @return UuidInterface
	 */
	public static function uuid()
	{
		try {
			return Uuid::uuid4();
		} catch (Exception $e) {
		}

		return null;
	}

	/**
	 * 获取 UUID
	 *
	 * @return string
	 */
	public static function assignUuid()
	{
		return str_replace('-', '', self::uuid()->toString());
	}

	/**
	 * 生成随机字符串
	 *
	 * @param string $factor
	 * @return string
	 */
	public static function nonceHash32($factor = '')
	{
		return md5(uniqid(md5(microtime(true) . $factor), true));
	}

	/**
	 * 创建订单编号
	 *
	 * @param string $prefix
	 * @return string
	 */
	public static function orderNumber($prefix = '')
	{
		// 取出订单编号
		$datetime = date('YmdHis');
		$microtime = explode(' ', microtime());
		$microtime = (int)($microtime[0] ? $microtime[0] * 100000 : 100000);

		$nonceStr = substr(uniqid('', true), 7, 13);
		$nonceStr = str_split($nonceStr, 1);
		$nonceStr = array_map('ord', $nonceStr);
		$nonceStr = substr(implode('', $nonceStr), -8);

		return $prefix . $datetime . $microtime . $nonceStr;
	}

	/**
	 * 创建订单编号
	 *
	 * @param string $prefix
	 * @return string
	 * @deprecated
	 */
	public static function makeOrderSn($prefix = '')
	{
		return self::orderNumber($prefix);
	}

	/**
	 * 解析Url Query
	 *
	 * @param string $url url地址或URL query参数
	 * @return array
	 */
	public static function parseUrlQuery($url)
	{
		$index = strpos($url, "?");
		if ($index !== false) {
			$url = substr($url, $index);
		}

		parse_str($url, $result);

		return $result;
	}

	/**
	 * 匹配URL
	 *
	 * @param string $checkUrl
	 * @param string $currentPath
	 * @param string $currentQuery
	 * @return bool
	 */
	public static function matchUrl($checkUrl, $currentPath, $currentQuery = [])
	{
		$checkUrlArr = explode("?", $checkUrl, 2);
		$checkPath = $checkUrlArr[0];

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
	 * 把数组所有元素按照“参数=参数值”的模式用“&”字符拼接成字符串
	 *
	 * @param array $params 关联数组
	 * @param callable $valueHandler 值处理函数
	 * @return string
	 */
	public static function buildUrlQuery($params, $valueHandler = null)
	{
		if (!is_callable($valueHandler)) {
			$valueHandler = static function ($key, $val) {
				$type = gettype($val);
				if ($type == 'object' || $type == 'array') {
					return '';
				}

				$val = urlencode($val);

				return $key . '=' . $val;
			};
		}

		$result = '';
		$i = 0;
		foreach ($params as $key => $val) {
			$str = $valueHandler($key, $val);
			if ($str === '') {
				continue;
			}
			$result .= ($i === 0 ? '' : '&') . $str;
			$i++;
		}

		return $result;
	}

	/**
	 * 将数组转换为查询字符串
	 *
	 * @param array $array
	 * @return string
	 */
	public static function queryString($array)
	{
		return http_build_query($array, null, '&', PHP_QUERY_RFC3986);
	}

	/**
	 * 安全处理-数组转字符串
	 *
	 * @param mixed $value
	 * @param string $format
	 * @param string $delimiter
	 * @return string
	 */
	public static function implode($value, $format = 'intval', $delimiter = ',')
	{
		//先转换为数组，进行安全过滤
		$value = self::explode($value, $format, $delimiter);

		//去除重复
		$value = array_unique($value);

		//再次转换为字符串
		return implode(",", $value);
	}

	/**
	 * 安全处理-字符串或数组转数组
	 *
	 * @param mixed $value
	 * @param string $format
	 * @param string $delimiter
	 * @param bool|Closure $filter
	 * @return array
	 */
	public static function explode($value, $format = 'intval', $delimiter = ',', $filter = true)
	{
		if (!is_array($value)) {
			$value = is_string($value) ? explode($delimiter, $value) : [$value];
		}

		if ($format) {
			$value = array_map($format, $value);
		}

		if ($filter !== false) {
			if ($filter === true) {
				$value = array_filter($value);
			} else {
				$value = array_filter($value, $filter);
			}
		}

		return array_values($value);
	}

	/**
	 * 确定给定字符串是否与给定模式匹配。
	 *
	 * @param string|array $pattern
	 * @param string $value
	 * @return bool
	 */
	public static function is($pattern, $value)
	{
		$patterns = Arr::wrap($pattern);

		if (empty($patterns)) {
			return false;
		}

		foreach ($patterns as $pattern) {
			// If the given value is an exact match we can of course return true right
			// from the beginning. Otherwise, we will translate asterisks and do an
			// actual pattern match against the two strings to see if they match.
			if ($pattern == $value) {
				return true;
			}

			$pattern = preg_quote($pattern, '#');

			// Asterisks are translated into zero-or-more regular expression wildcards
			// to make it convenient to check if the strings starts with the given
			// pattern such as "library/*", making any string check convenient.
			$pattern = str_replace('\*', '.*', $pattern);

			if (preg_match('#^' . $pattern . '\z#u', $value) === 1) {
				return true;
			}
		}

		return false;
	}

	/**
	 * 返回给定值首次出现后字符串的剩余部分
	 *
	 * @param string $subject
	 * @param string $search
	 * @return string
	 */
	public static function after($subject, $search)
	{
		return $search === '' ? $subject : array_reverse(explode($search, $subject, 2))[0];
	}

	/**
	 * 返回给定值最后一次出现后字符串的剩余部分
	 *
	 * @param string $subject
	 * @param string $search
	 * @return string
	 */
	public static function afterLast($subject, $search)
	{
		if ($search === '') {
			return $subject;
		}

		$position = mb_strrpos($subject, (string)$search);

		if ($position === false) {
			return $subject;
		}

		return self::substr($subject, $position + mb_strlen($search));
	}

	/**
	 * 获取给定值第一次出现之前的字符串部分
	 *
	 * @param string $subject
	 * @param string $search
	 * @return string
	 */
	public static function before($subject, $search)
	{
		return $search === '' ? $subject : explode($search, $subject)[0];
	}

	/**
	 * 获取给定值最后一次出现之前的字符串部分。
	 *
	 * @param string $subject
	 * @param string $search
	 * @return string
	 */
	public static function beforeLast($subject, $search)
	{
		if ($search === '') {
			return $subject;
		}

		$pos = mb_strrpos($subject, $search);

		if ($pos === false) {
			return $subject;
		}

		return self::substr($subject, 0, $pos);
	}

	/**
	 * 移除Emoji表情
	 *
	 * @param string $str
	 * @return string
	 */
	public static function rejectEmoji($str)
	{
		return preg_replace_callback('/./u', static function (array $match) {
			return strlen($match[0]) >= 4 ? '' : $match[0];
		}, $str);
	}

	/**
	 * 渲染Stub
	 * @param string $tpl
	 * @param array $data
	 * @return array|string|string[]
	 */
	public static function stub($tpl, $data, $options = [])
	{
		$options = array_replace_recursive([
			'tag_start' => '{',
			'tag_end' => '}',
		], $options);

		$variables = [];
		$values = [];
		foreach ($data as $key => $value) {
			$variables[] = "{$options['tag_start']}{$key}{$options['tag_end']}";
			$values[] = $value;
		}

		return str_replace($variables, $values, $tpl);
	}

	/**
	 * 从渲染模板中获取变量
	 * @param string $tpl
	 * @param array $options
	 * @return array
	 */
	public static function extractStubVariables($tpl, $options = [])
	{
		$options = array_replace_recursive([
			'tag_start' => '{',
			'tag_end' => '}',
		], $options);

		$matches = [];
		$pattern = '/' . preg_quote($options['tag_start'], '/') . '([a-zA-Z0-9_-]+)' . preg_quote($options['tag_end'], '/') . '/';
		if (preg_match_all($pattern, $tpl, $matches) === false) {
			return [];
		}

		return $matches[1];
	}

	/**
	 * 查找分割符在字符串中第一个出现的索引
	 * @param string $str 要检查的字符串
	 * @param array $delimiters 分隔符数组(单字符)
	 * @return int 分隔符索引，未找到返回-1
	 */
	public static function findDelimiterIndex($str, array $delimiters)
	{
		// 构建快速查找的映射表 [分隔符 => 索引]
		$map = array_flip($delimiters);

		// 遍历字符串每个字符
		for ($i = 0, $len = strlen($str); $i < $len; $i++) {
			$char = $str[$i];
			// 当找到第一个存在于映射表的字符时
			if (isset($map[$char])) {
				return $map[$char];
			}
		}

		return -1;
	}

	/**
	 * 查找分割符在字符串中最后一个出现的索引
	 * @param string $str 要检查的字符串
	 * @param array $delimiters 分隔符数组(单字符)
	 * @return int 分隔符索引，未找到返回-1
	 */
	public static function findLastDelimiterIndex($str, array $delimiters)
	{
		// 构建快速查找的映射表 [分隔符 => 索引]
		$map = array_flip($delimiters);

		// 遍历字符串每个字符
		for ($i = strlen($str) - 1; $i >= 0; $i--) {
			$char = $str[$i];
			// 当找到第一个存在于映射表的字符时
			if (isset($map[$char])) {
				return $map[$char];
			}
		}

		return -1;
	}

	/**
	 * 查找分割符在字符串中第一个出现的字符
	 * @param string $str 要检查的字符串
	 * @param array $delimiters 分隔符数组(单字符)
	 * @return string|null 分隔符，未找到返回null
	 */
	public static function findDelimiter($str, array $delimiters)
	{
		$index = self::findDelimiterIndex($str, $delimiters);
		return $index !== -1 ? $delimiters[$index] : null;
	}

	/**
	 * 查找分割符在字符串中最后一个出现的字符
	 * @param string $str 要检查的字符串
	 * @param array $delimiters 分隔符数组(单字符)
	 * @return string|null 分隔符，未找到返回null
	 */
	public static function findLastDelimiter($str, array $delimiters)
	{
		$index = self::findLastDelimiterIndex($str, $delimiters);
		return $index !== -1 ? $delimiters[$index] : null;
	}

	/**
	 * 使用自定义分隔符，取任意区间段（从 0 开始）
	 *
	 * @param string $text 原始字符串
	 * @param int $start 起始行号（含）
	 * @param int $end 结束行号（含）
	 * @param string $delimiter 任意分隔字符串
	 * @return string 指定区间内容
	 * @throws InvalidArgumentException
	 */
	public static function sliceWithDelimiter($text, $start, $end = 0, $delimiter = "\n")
	{
		if ($start < 0 || $end < 0) {
			throw new InvalidArgumentException('start or end out of range');
		}

		if ($end > 0 && $start > $end) {
			throw new InvalidArgumentException('start must less than end');
		}

		if ($text === '' || $delimiter === '') {
			return '';
		}

		$dLen = strlen($delimiter);
		$len = strlen($text);

		// 截取范围
		$cutStart = 0;
		$cutEnd = $len;

		// 查找分隔符位置
		$pos = 0;
		$count = 0;
		while (($pos = strpos($text, $delimiter, $pos)) !== false) {
			$pos += $dLen;
			$count++;

			// 找到起始分隔符
			if ($count === $start) {
				$cutStart = $pos;
			}

			// 找到结束分隔符
			if ($count === $end) {
				$cutEnd = $pos - $dLen;
				break;
			}
		}

		return substr($text, $cutStart, $cutEnd - $cutStart);
	}

	/**
	 * 对一段文本进行分段返回
	 * @param string $text
	 * @return array
	 */
	public static function semanticSegment(string $text)
	{
		// 使用 preg_split 按表情符号、问号、感叹号等来分割文本
		$segments = preg_split('/([~？！；。!;]+)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

		// 处理数组中的空值
		$filteredSegments = array_filter($segments, function ($value) {
			return !empty(trim($value));
		});

		// 合并分割符和其后的文本段落
		$result = [];
		for ($i = 0; $i < count($filteredSegments); $i++) {
			if ($i % 2 == 0) { // 文本段落
				if (isset($filteredSegments[$i + 1])) {
					$result[] = $filteredSegments[$i] . $filteredSegments[$i + 1];
					$i++;
				} else {
					$result[] = $filteredSegments[$i];
				}
			}
		}

		return $result;
	}

	public static function extractCode($code, &$language = null, $failOrNull = true)
	{
		if ($language) {
			$start = strpos($code, "```{$language}");
		} else {
			$start = strpos($code, "```");
			// 自动识别目标语言
			if ($start !== false) {
				$language = substr($code, $start + 3, strpos($code, "\n", $start + 3) - $start - 3);
			}
		}

		if ($start !== false) {
			$fLength = strlen("```{$language}");
			$end = strpos($code, "```", $start + $fLength);
			if ($end === false) {
				$code = substr($code, $start + $fLength);
			} else {
				$code = substr($code, $start + $fLength, $end - $start - $fLength);
			}
		} elseif ($failOrNull) {
			return null;
		}

		return trim($code);
	}

	/**
	 * 提取JSON数据
	 * @param string $code
	 * @param bool $failOrNull
	 * @return string
	 */
	public static function extractJson(string $code, $failOrNull = true)
	{
		$language = 'json';
		return self::extractCode($code, $language, $failOrNull);
	}

	/**
	 * 提取YAML数据
	 * @param string $code
	 * @param bool $failOrNull
	 * @return string
	 */
	public static function extractYaml(string $code, $failOrNull = true)
	{
		$language = 'yaml';
		return self::extractCode($code, $language, $failOrNull);
	}

	/**
	 * 提取Markdown数据
	 * @param string $code
	 * @param bool $failOrNull
	 * @return string
	 */
	public static function extractMarkdown(string $code, $failOrNull = true)
	{
		$language = 'markdown';
		return self::extractCode($code, $language, $failOrNull);
	}

	/**
	 * 提取HTML数据
	 * @param string $code
	 * @param bool $failOrNull
	 * @return string
	 */
	public static function extractHtml(string $code, $failOrNull = true)
	{
		$language = 'html';
		return self::extractCode($code, $language, $failOrNull);
	}

	/**
	 * 解析UBB语法
	 * @return string
	 */
	public static function ubbToHtml($content)
	{
		$content = trim($content);
		$content = htmlspecialchars($content);
		$content = preg_replace("/\\t/is", "  ", $content);
		$content = preg_replace("/\\[h1\\](.+?)\\[\\/h1\\]/is", "<h1>\\1</h1>", $content);
		$content = preg_replace("/\\[h2\\](.+?)\\[\\/h2\\]/is", "<h2>\\1</h2>", $content);
		$content = preg_replace("/\\[h3\\](.+?)\\[\\/h3\\]/is", "<h3>\\1</h3>", $content);
		$content = preg_replace("/\\[h4\\](.+?)\\[\\/h4\\]/is", "<h4>\\1</h4>", $content);
		$content = preg_replace("/\\[h5\\](.+?)\\[\\/h5\\]/is", "<h5>\\1</h5>", $content);
		$content = preg_replace("/\\[h6\\](.+?)\\[\\/h6\\]/is", "<h6>\\1</h6>", $content);
		$content = preg_replace("/\\[separator\\]/is", "", $content);
		$content = preg_replace("/\\[center\\](.+?)\\[\\/center\\]/is", "<span style=\"text-align: center\">\\1</span>", $content);
		$content = preg_replace("/\\[url=http:\\/\\/([^\\[]*)\\](.+?)\\[\\/url\\]/is", "<a href=\"http://\\1\" target=\"_blank\">\\2</a>", $content);
		$content = preg_replace("/\\[url=([^\\[]*)\\](.+?)\\[\\/url\\]/is", "<a href=\"http://\\1\" target=\"_blank\">\\2</a>", $content);
		$content = preg_replace("/\\[url\\]http:\\/\\/([^\\[]*)\\[\\/url\\]/is", "<a href=\"http://\\1\" target=\"_blank\">\\1</a>", $content);
		$content = preg_replace("/\\[url\\]([^\\[]*)\\[\\/url\\]/is", "<a href=\"\\1\" target=\"_blank\">\\1</a>", $content);
		$content = preg_replace("/\\[img\\](.+?)\\[\\/img\\]/is", "<img src=\"\\1\">", $content);
		$content = preg_replace("/\\[color=(.+?)\\](.+?)\\[\\/color\\]/is", "<span style=\"color: \\1\">\\2</span>", $content);
		$content = preg_replace("/\\[size=(.+?)\\](.+?)\\[\\/size\\]/is", "<span style=\"font-size: \\1\">\\2</span>", $content);
		$content = preg_replace("/\\[sup\\](.+?)\\[\\/sup\\]/is", "<sup>\\1</sup>", $content);
		$content = preg_replace("/\\[sub\\](.+?)\\[\\/sub\\]/is", "<sub>\\1</sub>", $content);
		$content = preg_replace("/\\[pre\\](.+?)\\[\\/pre\\]/is", "<pre>\\1</pre>", $content);
		$content = preg_replace("/\\[email\\](.+?)\\[\\/email\\]/is", "<a href=\"mailto:\\1\">\\1</a>", $content);
		$content = preg_replace("/\\[colorTxt\\](.+?)\\[\\/colorTxt\\]/eis", "color_txt('\\1')", $content);
		$content = preg_replace("/\\[emot\\](.+?)\\[\\/emot\\]/eis", "emot('\\1')", $content);
		$content = preg_replace("/\\[i\\](.+?)\\[\\/i\\]/is", "<i>\\1</i>", $content);
		$content = preg_replace("/\\[u\\](.+?)\\[\\/u\\]/is", "<u>\\1</u>", $content);
		$content = preg_replace("/\\[b\\](.+?)\\[\\/b\\]/is", "<b>\\1</b>", $content);
		$content = preg_replace("/\\[quote\\](.+?)\\[\\/quote\\]/is", " <div class=\"quote\"><h5>引用:</h5><blockquote>\\1</blockquote></div>", $content);
		$content = preg_replace("/\\[code\\](.+?)\\[\\/code\\]/eis", "highlight_code('\\1')", $content);
		$content = preg_replace("/\\[php\\](.+?)\\[\\/php\\]/eis", "highlight_code('\\1')", $content);
		$content = preg_replace("/\\[sig\\](.+?)\\[\\/sig\\]/is", "<div class=\"sign\">\\1</div>", $content);
		$content = preg_replace("/\\n/is", "<br/>", $content);

		return $content;
	}
}
