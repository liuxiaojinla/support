<?php
/**
 * I know no such things as genius,it is nothing but labor and diligence.
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @author 晋<657306123@qq.com>
 */

namespace Xin\Support\Contracts;

interface Renderable
{

	/**
	 * Get the evaluated contents of the object.
	 *
	 * @return string
	 */
	public function render();

}
