<?php

use Xin\Support\Enum;
use Xin\Support\Enumable;

require_once '../vendor/autoload.php';

class Status extends Enum
{
	const SUCCESS = 1;
	const FAIL = 0;
	const UNKNOWN = -1;

	protected static $TEXT_MAP = [
		self::SUCCESS => 'success',
		self::FAIL => 'fail',
		self::UNKNOWN => 'unknown',
	];
}

enum Status2: int
{
	use Enumable;

	case SUCCESS = 1;
	case FAIL = 0;
	case UNKNOWN = -1;

	/**
	 * @inheritDoc
	 */
	public static function labels(): array
	{
		return [
			self::SUCCESS->value => 'success',
			self::FAIL->value => 'fail',
			self::UNKNOWN->value => 'unknown',
		];
	}
}

var_dump(Status::labels());
var_dump(Status::values());
var_dump(Status::SUCCESS);
var_dump(Status2::labelOf(Status2::SUCCESS->value));
