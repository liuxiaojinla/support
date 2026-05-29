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

// 测试 sortByDepth
$treeFiles = File::treeFiles('../src');
Printer::log('sortByDepth - 排序前:');
foreach ($treeFiles as $file) {
	$depth = substr_count($file->getPathname(), DIRECTORY_SEPARATOR);
	Printer::log("  深度 {$depth}: ", $file->getPathname());
}
Printer::log('=====================================');

$sortedByDepth = File::sortByDepth($treeFiles);
Printer::log('sortByDepth - 排序后（按深度升序）:');
foreach ($sortedByDepth as $file) {
	$depth = substr_count($file->getPathname(), DIRECTORY_SEPARATOR);
	Printer::log("  深度 {$depth}: ", $file->getPathname());
}
Printer::log('=====================================');

// 测试 sortByPathname
$unsortedFiles = File::filesIterator('../src');
Printer::log('sortByPathname - 排序前:');
foreach ($unsortedFiles as $file) {
	Printer::log("  ", $file->getPathname());
}
Printer::log('=====================================');

$sortedByPathname = File::sortByPathname($unsortedFiles);
Printer::log('sortByPathname - 排序后（按路径字典序升序）:');
foreach ($sortedByPathname as $file) {
	Printer::log("  ", $file->getPathname());
}
Printer::log('=====================================');

// 测试生成器输入
function generateFiles(string $dir): Generator
{
	foreach (File::filesIterator($dir) as $file) {
		yield $file;
	}
}

$generatorFiles = generateFiles('../src');
$sortedFromGenerator = File::sortByPathname($generatorFiles);
Printer::log('sortByPathname - 从生成器排序:');
foreach ($sortedFromGenerator as $file) {
	Printer::log("  ", $file->getPathname());
}
Printer::log('=====================================');
