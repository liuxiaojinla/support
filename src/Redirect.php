<?php

namespace Xin\Support;

/**
 * URL重定向
 */
class Redirect
{
	/**
	 * @var string
	 */
	protected $url;

	/**
	 * @var string
	 */
	protected $msg;

	/**
	 * @var int
	 */
	protected $time;

	/**
	 * @param string $url 重定向的URL地址
	 * @param integer $time 重定向的等待时间（秒）
	 * @param string $msg 重定向前的提示信息
	 */
	public function __construct($url, $time = 0, $msg = '')
	{
		$this->url = $url;
		$this->time = $time;
		$this->msg = $msg;
	}

	/**
	 * URL重定向
	 *
	 * @return void
	 */
	public function send()
	{
		//多行URL地址支持
		$url = str_replace(["\n", "\r"], '', $this->url);
//		if (empty($this->msg)) {
//			$msg = "系统将在{$this->time}秒之后自动跳转到{$this->url}！";
//		}

		if (!headers_sent()) {
			// redirect
			if (0 === $this->time) {
				header('Location: ' . $this->url);
			} else {
				header("refresh:{$this->time};url={$this->url}");
				echo($this->msg);
			}
			exit();
		}

		$str = "<meta http-equiv=\"Refresh\" content=\"{$this->time};URL={$this->url}\">";
		if (0 != $this->time) {
			$str .= $this->msg;
		}

		echo $str;
		die(0);
	}

	/**
	 * @param string $url 重定向的URL地址
	 * @param integer $time 重定向的等待时间（秒）
	 * @param string $msg 重定向前的提示信息
	 * @return void
	 */
	public static function redirect($url, $time = 0, $msg = '')
	{
		$redirect = new static($url, $time, $msg);
		$redirect->send();
	}
}
