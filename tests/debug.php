<?php

use Xin\Support\Console\Printer;
use Xin\Support\Debug\DebugTime;

require_once '../vendor/autoload.php';

Printer::log('hello world');
Printer::info('hello world');
Printer::error('hello world');
Printer::success('hello world');
Printer::warn('hello world');
Printer::json('hello world');
Printer::errorJson('hello world');
Printer::successJson('hello world');

DebugTime::beginTime();
sleep(1);
DebugTime::endTime();
DebugTime::call(function () {
	sleep(1);
});
