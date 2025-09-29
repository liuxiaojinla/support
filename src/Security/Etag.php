<?php

namespace Xin\Support\Security;

final class Etag
{
	//4*1024*1024 分块上传块大小，该参数为接口规格，不能修改
	public const BLOCK_SIZE = 4194304;

	/**
	 * @param mixed $v
	 * @param mixed $a
	 * @return mixed
	 */
	private static function packArray($v, $a)
	{
		return call_user_func_array('pack', array_merge([$v], (array)$a));
	}

	/**
	 * @param int $fsize
	 * @return int
	 */
	private static function blockCount($fsize)
	{
		return (int)(($fsize + (self::BLOCK_SIZE - 1)) / self::BLOCK_SIZE);
	}

	/**
	 * @param string $data
	 * @return array
	 */
	private static function calcSha1($data)
	{
		$sha1Str = sha1($data, true);
		$err = error_get_last();
		if ($err !== null) {
			return [null, $err];
		}
		$byteArray = unpack('C*', $sha1Str);
		return [$byteArray, null];
	}

	/**
	 * @param string $filename
	 * @return string
	 */
	public static function sum($filename)
	{
		$fhandler = fopen($filename, 'rb');
		$err = error_get_last();
		if ($err !== null) {
			return null;
		}

		$fstat = fstat($fhandler);
		$fsize = $fstat['size'];
		if ((int)$fsize === 0) {
			fclose($fhandler);
			return 'Fto5o-5ea0sNMlW_75VgGJCv2AcJ';
		}

		$blockCnt = self::blockCount($fsize);
		$sha1Buf = [];

		if ($blockCnt <= 1) {
			$sha1Buf[] = 0x16;
			$fdata = fread($fhandler, self::BLOCK_SIZE);
			[$sha1Code,] = self::calcSha1($fdata);
			$sha1Buf = array_merge($sha1Buf, $sha1Code);
		} else {
			$sha1Buf[] = 0x96;
			$sha1BlockBuf = [];
			for ($i = 0; $i < $blockCnt; $i++) {
				$fdata = fread($fhandler, self::BLOCK_SIZE);
				[$sha1Code, $err] = self::calcSha1($fdata);
				if ($err !== null) {
					fclose($fhandler);
					return null;
				}
				$sha1BlockBuf = array_merge($sha1BlockBuf, $sha1Code);
			}
			$tmpData = self::packArray('C*', $sha1BlockBuf);
			[$sha1Final,] = self::calcSha1($tmpData);
			$sha1Buf = array_merge($sha1Buf, $sha1Final);
		}

		return self::base64_urlSafeEncode(self::packArray('C*', $sha1Buf));
	}

	/**
	 * 对提供的数据进行urlsafe的base64编码。
	 *
	 * @param string $data 待编码的数据，一般为字符串
	 *
	 * @return string 编码后的字符串
	 * @link http://developer.qiniu.com/docs/v6/api/overview/appendix.html#urlsafe-base64
	 */
	private static function base64_urlSafeEncode($data)
	{
		$find = ['+', '/'];
		$replace = ['-', '_'];
		return str_replace($find, $replace, base64_encode($data));
	}
}
