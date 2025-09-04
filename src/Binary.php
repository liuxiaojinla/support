<?php

namespace Xin\Support;

final class Binary
{
	/**
	 * 判断数据是否为二进制数据
	 * @param mixed $data
	 * @return bool
	 */
	public static function is($data)
	{
		if (!is_string($data)) {
			return false;
		}

		// 允许 UTF-8 和其他编码中的可读文本
		$encoding = mb_detect_encoding($data, 'UTF-8, ASCII, ISO-8859-1', true);

		if ($encoding === 'UTF-8' && mb_check_encoding($data, 'UTF-8')) {
			return false; // 明确是 UTF-8 文本
		}

		// 检查是否存在 ASCII 不可打印字符（排除 \r\n 和空格）
		return preg_match('/[^\x20-\x7E\x0A\x0D\x09]/', $data) > 0;
	}
}
