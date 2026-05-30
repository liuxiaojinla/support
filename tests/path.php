<?php

use Xin\Support\Path;

require_once '../vendor/autoload.php';

echo "========== Path 类测试 ==========\n\n";

// 测试 extension 方法
echo "【测试 extension 方法】\n";
echo "extension('test.txt'): " . Path::extension('test.txt') . "\n";
echo "extension('test.'): " . Path::extension('00.') . "\n";
echo "extension('test'): " . Path::extension('00') . "\n";
echo "extension('.env'): " . Path::extension('.env') . "\n";
echo "extension('/path/to/file.php'): " . Path::extension('/path/to/file.php') . "\n";
echo "extension('archive.tar.gz'): " . Path::extension('archive.tar.gz') . "\n";
echo "\n";

// 测试 basename 方法
echo "【测试 basename 方法】\n";
echo "basename('/path/to/test.txt', false): " . Path::basename('/path/to/test.txt', false) . "\n";
echo "basename('/path/to/test.txt', true): " . Path::basename('/path/to/test.txt', true) . "\n";
echo "basename('file.php', false): " . Path::basename('file.php', false) . "\n";
echo "basename('file.php', true): " . Path::basename('file.php', true) . "\n";
echo "basename('noext', false): " . Path::basename('noext', false) . "\n";
echo "\n";

// 测试 replaceFilename 方法
echo "【测试 replaceFilename 方法】\n";
echo "replaceFilename('/path/to/old.txt', 'new'): " . Path::replaceFilename('/path/to/old.txt', 'new') . "\n";
echo "replaceFilename('/path/to/old.txt', 'newname'): " . Path::replaceFilename('/path/to/old.txt', 'newname') . "\n";
echo "replaceFilename('file.php', 'index'): " . Path::replaceFilename('file.php', 'index') . "\n";
echo "replaceFilename('/dir/file', 'newfile'): " . Path::replaceFilename('/dir/file', 'newfile') . "\n";
echo "\n";

// 测试 replaceSuffix 方法
echo "【测试 replaceExtension 方法】\n";
echo "replaceExtension('test.txt', 'jpg'): " . Path::replaceExtension('test.txt', 'jpg') . "\n";
echo "replaceExtension('/path/file.php', 'html'): " . Path::replaceExtension('/path/file.php', 'html') . "\n";
echo "replaceExtension('image.png', 'jpeg'): " . Path::replaceExtension('image.png', 'jpeg') . "\n";
echo "\n";

// 测试 joins 方法
echo "【测试 joins 方法】\n";
echo "joins('/path', 'to', 'file'): " . Path::join('/path', 'to', 'file') . "\n";
echo "joins('dir', 'subdir', 'file.txt'): " . Path::join('dir', 'subdir', 'file.txt') . "\n";
echo "joins(['/path', 'to'], 'file'): " . Path::join(['/path', 'to'], 'file') . "\n";
echo "joins('', 'path', '', 'file'): " . Path::join('', 'path', '', 'file') . "\n";
echo "\n";

// 测试 concat 方法
echo "【测试 concat 方法】\n";
echo "concat('/path/', '/to/file'): " . Path::concat('/path/', '/to/file') . "\n";
echo "concat('/path', 'to/file'): " . Path::concat('/path', 'to/file') . "\n";
echo "concat('base/', 'sub/'): " . Path::concat('base/', 'sub/') . "\n";
echo "concat('/', 'root'): " . Path::concat('/', 'root') . "\n";
echo "\n";

echo "========== 测试完成 ==========\n";
