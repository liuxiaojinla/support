<?php
/** @noinspection PhpComposerExtensionStubsInspection */

namespace Xin\Support\Contracts;

interface Jsonable
{

	/**
	 * 将对象转换为其 JSON 表示形式。
	 *
	 * @param int $flags
	 * @return string
	 */
	public function toJson(int $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

}
