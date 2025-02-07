<?php

namespace Xin\Support\Web;

class ServerInfo
{
	/**
	 * 获取主机名称
	 *
	 * @return string
	 */
	public static function getHostName()
	{
		return $_SERVER ['SERVER_NAME'];
	}

	/**
	 * 获取当前访问的文件
	 *
	 * @return string
	 */
	public static function getExecuteFile()
	{
		$urls = explode('/', strip_tags($_SERVER ['REQUEST_URI']), 2);

		return count($urls) > 1 ? $urls [1] : '';
	}

	/**
	 * 获取序列化参数
	 *
	 * @param bool $isExportStyle
	 * @return string
	 */
	public static function serializeParams(bool $isExportStyle = true)
	{
		if ($isExportStyle) {
			return var_export([
				"GET" => $_GET,
				"POST" => $_POST,
				"COOKIE" => $_COOKIE,
				"SESSION" => $_SESSION,
				"SERVER" => $_SERVER,
			], true);
		}

		return "[GET=" . http_build_query($_GET) . "],"
			. "[POST=" . http_build_query($_POST, false) . "]," .
			"[COOKIE=" . http_build_query($_COOKIE, false) . "]," .
			"[SESSION=" . http_build_query($_SESSION, false) . "]," .
			"[SERVER=" . http_build_query($_SERVER, false) . "]";
	}
}
