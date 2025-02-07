<?php

namespace Xin\Support\Security;

/**
 * 简易加解密器
 */
final class SimpleEncrypt
{
	/**
	 * 系统加密方法
	 *
	 * @param string $data 要加密的字符串
	 * @param string $key 加密密钥
	 * @param int $expire 过期时间 单位 秒
	 * @return string
	 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
	 */
	public static function encrypt(string $data, string $key = '', int $expire = 0)
	{
		$key = md5($key);
		$data = base64_encode($data);
		$x = 0;
		$len = strlen($data);
		$l = strlen($key);
		$char = '';

		for ($i = 0; $i < $len; $i++) {
			if ($x == $l) {
				$x = 0;
			}
			$char .= $key[$x];
			$x++;
		}

		$str = sprintf('%010d', $expire ? $expire + time() : 0);

		for ($i = 0; $i < $len; $i++) {
			$str .= chr(ord($data[$i]) + (ord($char[$i])) % 256);
		}

		return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($str));
	}

	/**
	 * 系统解密方法
	 *
	 * @param string $data 要解密的字符串
	 * @param string $key 加密密钥
	 * @return string
	 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
	 */
	public static function decrypt(string $data, string $key = '')
	{
		$key = md5($key);
		$data = str_replace(['-', '_'], ['+', '/'], $data);
		$mod4 = strlen($data) % 4;
		if ($mod4) {
			$data .= substr('====', $mod4);
		}
		$data = base64_decode($data);
		$expire = substr($data, 0, 10);
		$data = substr($data, 10);

		if ($expire > 0 && $expire < time()) {
			return '';
		}
		$x = 0;
		$len = strlen($data);
		$l = strlen($key);
		$char = $str = '';

		for ($i = 0; $i < $len; $i++) {
			if ($x == $l) {
				$x = 0;
			}
			$char .= $key[$x];
			$x++;
		}

		for ($i = 0; $i < $len; $i++) {
			if (ord($data[$i]) < ord($char[$i])) {
				$str .= chr((ord($data[$i]) + 256) - ord($char[$i]));
			} else {
				$str .= chr(ord($data[$i]) - ord($char[$i]));
			}
		}

		return base64_decode($str);
	}
}
