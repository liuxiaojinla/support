<?php

use Xin\Support\Console\Printer;
use Xin\Support\File;

require_once '../vendor/autoload.php';

$files = File::files('../src');
foreach ($files as $key => $file) {
	Printer::log("files: ", $key, $file->getRealPath());
}
Printer::log('=====================================');

$files = File::filesIterator('../src');
foreach ($files as $key => $file) {
	Printer::log("filesIterator: ", $key, $file->getRealPath());
}
Printer::log('=====================================');

$files = File::treeFiles('../src');
foreach ($files as $key => $file) {
	Printer::log("treeFiles: ", $key, $file->getRealPath());
}
Printer::log('=====================================');

$files = File::filter('../src', '/Helper\.php$/');
foreach ($files as $key => $file) {
	Printer::log("filter regex: ", $key, $file->getRealPath());
}
Printer::log('=====================================');

$files = File::filter('../src', function (SplFileInfo $file) {
	return strpos($file->getPathname(), 'Helper.php') !== false;
});
foreach ($files as $key => $file) {
	Printer::log("filter callback: ", $key, $file->getRealPath());
}
Printer::log('=====================================');

$files = File::directories('../src');
foreach ($files as $key => $file) {
	Printer::log("directories: ", $key, $file->getRealPath());
}
Printer::log('=====================================');

File::mkdir('./test');
Printer::log('mkdir: ', './test');
Printer::log('=====================================');

$files = File::createMany([
	'./test/test.txt' => 'test',
	'./test/test2/',
]);
foreach ($files as $key => $file) {
	Printer::log("createFiles: ", $key, $file->getRealPath());
}
Printer::log('=====================================');

$file = File::putTempFile('test');
File::delete($file);
Printer::log('putTempFile: ', $file->getPathname());
Printer::log('=====================================');

$file = File::tempFilePath('test');
Printer::log('tempFilePath: ', $file);
Printer::log('=====================================');


$files = File::glob('./*.{php,txt}');
foreach ($files as $key => $file) {
	Printer::log("glob: ", $key, $file->getRealPath());
}
Printer::log('=====================================');

