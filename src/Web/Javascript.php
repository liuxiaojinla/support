<?php

namespace Xin\Support\Web;

class Javascript
{
	/**
	 * @var string
	 */
	protected $content;

	/**
	 * @param string $content
	 */
	public function __construct(string $content)
	{
		$this->content = $content;
	}

	/**
	 * 调用浏览器端的JS函数
	 * @param string $callbackName
	 * @param array $params
	 * @return void
	 */
	public static function call(string $callbackName, array $params = [])
	{
		$params = array_map(static function ($param) {
			if (is_object($param) || is_array($param)) {
				return json_encode($param);
			}

			return '"' . addslashes($param) . '"';
		}, $params);

		$paramsStr = implode(",", $params);
		$js = new static("{$callbackName} ( " . json_encode($paramsStr) . " )");
		$js->sendBrowser();
	}

	/**
	 * 发送到浏览器中
	 * @return void
	 */
	public function sendBrowser()
	{
		echo "<script type=\"text/javascript\">{$this->content}</script>";
		flush();
		ob_flush();
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->content;
	}
}
