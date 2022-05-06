<?php
/**
 * I know no such things as genius,it is nothing but labor and diligence.
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @author 晋<657306123@qq.com>
 */

namespace Xin\Support\Contracts;

interface Jsonable
{

	/**
	 * Convert the object to its JSON representation.
	 *
	 * @param int $options
	 * @return string
	 */
	public function toJson($options = 0);

}
