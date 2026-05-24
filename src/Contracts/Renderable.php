<?php


namespace Xin\Support\Contracts;

interface Renderable
{

	/**
	 * 获取对象的评估内容。
	 *
	 * @return string
	 */
	public function render();

}
