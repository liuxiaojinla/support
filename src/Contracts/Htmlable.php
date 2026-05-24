<?php


namespace Xin\Support\Contracts;

interface Htmlable
{

	/**
	 * 将内容作为 HTML 字符串获取。
	 *
	 * @return string
	 */
	public function toHtml();

}
