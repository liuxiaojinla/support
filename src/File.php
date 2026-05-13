<?php

namespace Xin\Support;

use CallbackFilterIterator;
use FilesystemIterator;
use League\Flysystem\Config as FlysystemConfig;
use League\Flysystem\Filesystem as FlysystemFilesystem;
use League\Flysystem\PathNormalizer as FlysystemPathNormalizer;
use Psr\Http\Message\StreamInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionException;
use RegexIterator;
use RuntimeException;
use SplFileInfo;
use Xin\Support\Security\Etag;

/**
 * 目录操作类
 */
final class File
{
	/**
	 * ETag 算法
	 */
	public const HASH_ETAG = 'etag';

	/**
	 * md5 算法
	 */
	public const HASH_MD5 = 'md5';

	/**
	 * Sha1 算法
	 */
	public const HASH_SHA1 = 'sha1';

	/**
	 * 获取指定目录下所有的文件迭代器，包括子目录下的文件
	 * @param string $directory
	 * @param bool $recursive
	 * @param int|null $recursiveIteratorIteratorFlag
	 * @param int|null $filesystemIteratorFlags
	 * @return RecursiveIteratorIterator|FilesystemIterator
	 */
	public static function filesIterator(
		string $directory,
		bool   $recursive = true,
		?int   $recursiveIteratorIteratorFlag = null,
		?int   $filesystemIteratorFlags = null
	)
	{
		// 递归迭代器标志
		if ($recursiveIteratorIteratorFlag === null) {
			$recursiveIteratorIteratorFlag = RecursiveIteratorIterator::LEAVES_ONLY;
		}

		// 文件迭代器标志
		if ($filesystemIteratorFlags === null) {
			$filesystemIteratorFlags = FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO;
		}
		$filesystemIteratorFlags |= FilesystemIterator::SKIP_DOTS;

		return $recursive ? new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($directory, $filesystemIteratorFlags),
			$recursiveIteratorIteratorFlag,
			RecursiveIteratorIterator::CATCH_GET_CHILD
		) : new FilesystemIterator($directory, $filesystemIteratorFlags);
	}

	/**
	 * 获取指定目录下所有的文件，包括子目录下的文件
	 *
	 * @param string $directory
	 * @param bool $recursive
	 * @return FilesystemIterator|RecursiveIteratorIterator
	 */
	public static function files(string $directory, bool $recursive = true)
	{
		return self::filesIterator(
			$directory,
			$recursive,
			RecursiveIteratorIterator::LEAVES_ONLY,
			FilesystemIterator::SKIP_DOTS
		);
	}

	/**
	 * 获取指定目录下所有的文件迭代器，包括子目录下的文件
	 * @param string $directory
	 * @param int $filesystemIteratorFlags
	 * @return RecursiveIteratorIterator
	 */
	public static function treeFiles(string $directory, int $filesystemIteratorFlags = FilesystemIterator::SKIP_DOTS)
	{
		return self::filesIterator($directory, true, RecursiveIteratorIterator::SELF_FIRST, $filesystemIteratorFlags);
	}

	/**
	 * 获取指定目录下所有的文件迭代器，包括子目录下的文件
	 * @param string $directory
	 * @param callable|string $filter 过滤函数或正则表达式
	 * @param bool $recursive
	 * @param int|null $recursiveIteratorIteratorFlag
	 * @param int|null $filesystemIteratorFlags
	 * @return CallbackFilterIterator|RegexIterator
	 */
	public static function filter(
		string $directory,
		       $filter,
		bool   $recursive = true,
		?int   $recursiveIteratorIteratorFlag = null,
		?int   $filesystemIteratorFlags = null
	)
	{
		$files = self::filesIterator($directory, $recursive, $recursiveIteratorIteratorFlag, $filesystemIteratorFlags);

		if (is_callable($filter)) {
			$files = new CallbackFilterIterator($files, $filter);
		} else {
			$files = new RegexIterator($files, $filter);
		}

		return $files;
	}

	/**
	 * 寻找与模式匹配的文件路径
	 * @param string $glob
	 * @param string $directory
	 * @param bool $recursive
	 * @param int|null $recursiveIteratorIteratorFlag
	 * @param int|null $filesystemIteratorFlags
	 * @return RegexIterator
	 */
	public static function glob(
		string $glob,
		string $directory = '.',
		bool   $recursive = true,
		?int   $recursiveIteratorIteratorFlag = null,
		?int   $filesystemIteratorFlags = null)
	{
		// 1. 统一并清理开头的 ./ 或 .\ (同时兼容 Windows 环境)
		// $glob = preg_replace('#^\.\\\?/#', '', $glob);

		// 2. 保护 ** 和 []
		$glob = str_replace(
			['**/*', '**', '[', ']'],
			["\x01GLOBSTAR\x01", "\x01GLOBSTAR\x01", "\x01LBRACKET\x01", "\x01RBRACKET\x01"],
			$glob
		);
		// 使用正则查找并替换所有花括号内的逗号分隔项
		$glob = preg_replace_callback('/\{([^{}]+)\}/', function ($matches) {
			return "\x01BRACE_START\x01"
				. str_replace(',', "\x01PIPE\x01", $matches[1])
				. "\x01BRACE_END\x01";
		}, $glob);

		// 3. 转义所有正则特殊字符
		$glob = preg_quote($glob, '#');

		// 4. 还原占位符并进行 Glob 语义转换
		// 还原字符组符号
		$glob = str_replace(
			[
				'\*', // 单 * ：匹配除斜杠外的任意字符
				'\?', // 单 ? ：匹配除斜杠外的任意单个字符
				"\x01GLOBSTAR\x01", // ** ：匹配任意层级目录（包含斜杠）
				"\x01LBRACKET\x01", // [ ：还原字符]
				"\x01RBRACKET\x01", // ] ：还原字符]
				"\x01PIPE\x01", // | ：还原字符
				"\x01BRACE_START\x01", // 还原字符(
				"\x01BRACE_END\x01" // 还原字符)
			],
			['[^/]*', '[^/]', '.*', '[', ']', '|', '(', ')'],
			$glob
		);

		// 5. 组装完整的正则表达式
		$glob = '#^' . $glob . '$#u';

		return self::filter($directory, $glob, $recursive, $recursiveIteratorIteratorFlag, $filesystemIteratorFlags);
	}

	/**
	 * 获取指定目录下所有的目录迭代器，包括子目录下的目录
	 * @param string $directory
	 * @param bool $recursive
	 * @return CallbackFilterIterator
	 */
	public static function directories(string $directory, bool $recursive = true)
	{
		return self::filter($directory, function (SplFileInfo $file) {
			return $file->isDir();
		}, $recursive, RecursiveIteratorIterator::SELF_FIRST, FilesystemIterator::SKIP_DOTS);
	}

	/**
	 * 递归指定目录下所有的文件，包括子目录下的文件
	 *
	 * @param string $directory
	 * @param callable $callback
	 * @deprecated 请使用 self::treeFiles() 方法
	 * @see self::treeFiles()
	 */
	public static function each(string $directory, callable $callback)
	{
		$each = function ($dir) use (&$each, $callback) {
			$it = new FilesystemIterator($dir);

			/**@var $file SplFileInfo */
			foreach ($it as $file) {
				if ($callback($file) === false) {
					return false;
				}

				if ($file->isDir()) {
					if ($each($file->getPathname()) === false) {
						return false;
					}
				}
			}

			return true;
		};

		$each($directory);
	}

	/**
	 * 删除文件或目录
	 *
	 * @param string|SplFileInfo $fileOrDirectory
	 * @return bool
	 */
	public static function delete($fileOrDirectory)
	{
		if ($fileOrDirectory instanceof SplFileInfo) {
			$fileOrDirectory = $fileOrDirectory->getRealPath();
		}

		if (is_dir($fileOrDirectory)) {
			$files = self::filesIterator($fileOrDirectory, true, RecursiveIteratorIterator::CHILD_FIRST, FilesystemIterator::SKIP_DOTS);
			foreach ($files as $fileInfo) {
				$fileInfo->isDir() ? rmdir($fileInfo->getRealPath()) : unlink($fileInfo->getRealPath());
			}

			return rmdir($fileOrDirectory);
		} else {
			return unlink($fileOrDirectory);
		}
	}

	/**
	 * 批量删除文件或目录
	 * @param array<string|SplFileInfo> $fileOrDirectories
	 * @return void
	 */
	public static function deletes(array $fileOrDirectories)
	{
		foreach ($fileOrDirectories as $fileOrDirectory) {
			self::delete($fileOrDirectory);
		}
	}

	/**
	 * 创建目录
	 * @param string $directory
	 * @param int $permissions
	 * @param bool $recursive
	 * @param mixed $context
	 * @return bool
	 */
	public static function mkdir(string $directory, int $permissions = 0777, bool $recursive = true, $context = null)
	{
		if (is_dir($directory)) {
			return true;
		}

		return mkdir($directory, $permissions, $recursive, $context);
	}

	/**
	 * 不存在则创建目录
	 * @param string $directory
	 * @param int $permissions
	 * @param bool $recursive
	 * @param mixed $context
	 * @return bool
	 * @deprecated
	 * @see self::mkdir
	 */
	public static function mkdirOrExists(string $directory, int $permissions = 0777, bool $recursive = true, $context = null)
	{
		return self::mkdir($directory, $permissions, $recursive, $context);
	}

	/**
	 * 基于数组创建目录和文件
	 *
	 * @param array $files
	 * @return void
	 * @deprecated
	 * @see self::createFiles
	 */
	public static function createDirOrFiles(array $files)
	{
		self::createFiles($files);
	}

	/**
	 * 基于数组创建目录和文件
	 *
	 * @param array $files
	 * @return array
	 */
	public static function createFiles(array $files)
	{
		$result = [];

		foreach ($files as $key => $value) {
			$filepath = $key;
			if (is_int($key)) {
				$filepath = $value;
				$value = '';
			}

			$deep = substr($filepath, -1);
			if ($deep == DIRECTORY_SEPARATOR) {
				if (is_dir($filepath)) {
					$result[] = new SplFileInfo($filepath);
				} elseif (mkdir($filepath, 0777, true)) {
					$result[] = new SplFileInfo($filepath);
				}
			} else {
				if (@file_put_contents($filepath, $value) !== false) {
					$result[] = new SplFileInfo($filepath);
				}
			}
		}

		return $result;
	}

	/**
	 * 写入数据到临时文件中
	 *
	 * @param mixed $data
	 * @param string $prefix
	 * @return SplFileInfo|null
	 */
	public static function putTempFile($data, string $prefix = '')
	{
		$filePath = self::tempFilePath($prefix);
		if ($filePath === false) {
			return null;
		}

		if (file_put_contents($filePath, $data) === false) {
			return null;
		}

		return new SplFileInfo($filePath);
	}

	/**
	 * 建立一个具有唯一文件名的文件
	 *
	 * @param string $prefix
	 * @return false|string
	 */
	public static function tempFilePath(string $prefix = '')
	{
		return tempnam(sys_get_temp_dir(), empty($prefix) ? uniqid() : $prefix);
	}

	/**
	 * 获取上传的真实路径
	 * @param string $path
	 * @param FlysystemFilesystem $filesystem
	 * @return string
	 * @throws ReflectionException
	 */
	public static function filesystemRealpath(string $path, FlysystemFilesystem $filesystem)
	{
		/** @var FlysystemConfig $config */
		$config = Reflect::get($filesystem, 'config');
		/** @var FlysystemPathNormalizer $pathNormalizer */
		$pathNormalizer = Reflect::get($filesystem, 'pathNormalizer');

		$realPath = $config->get('root', $config->get('path', '')) . DIRECTORY_SEPARATOR . $path;

		return $pathNormalizer->normalizePath($realPath);
	}

	/**
	 * 写入文件
	 * @param string $path
	 * @param mixed $data
	 * @param int $flags
	 * @param resource|null $context
	 * @return false|int
	 */
	public static function put(string $path, $data, int $flags = 0, $context = null)
	{
		self::mkdir(dirname($path));

		if ($data instanceof StreamInterface) {
			$data = $data->getContents();
		} elseif (is_object($data) || is_array($data)) {
			$data = Json::encode($data);
		}

		return file_put_contents($path, $data, $flags, $context);
	}

	/**
	 * 追加写入文件
	 * @param string $path
	 * @param mixed $data
	 * @param mixed|null $context
	 * @return false|int
	 */
	public static function append(string $path, $data, $context = null)
	{
		return self::put($path, $data, FILE_APPEND, $context);
	}

	/**
	 * 读取文件
	 * @param string $path
	 * @param bool $useIncludePath
	 * @param resource|null $context
	 * @param int $offset
	 * @param int|null $length
	 * @return false|string
	 */
	public static function get(string $path, bool $useIncludePath = false, $context = null, int $offset = 0, ?int $length = null)
	{
		return file_get_contents($path, $useIncludePath, $context, $offset, $length);
	}

	/**
	 * 获取文件扩展
	 * @return string
	 */
	public static function extension(SplFileInfo $file)
	{
		$extension = $file->getExtension();
		if (empty($extension)) {
			$mime = self::mime($file);
			$mime = explode("/", $mime);
			if (empty($mime)) {
				return '';
			}

			return strtolower(end($mime));
		}

		return strtolower($extension);
	}

	/**
	 * 获取mime类型
	 * @return string
	 */
	public static function mime(SplFileInfo $file)
	{
		return mime_content_type($file->getPathname());
	}

	/**
	 * 获取文件hash
	 * @param SplFileInfo|string $file
	 * @param string|null $hashType
	 * @return string
	 */
	public static function hash($file, ?string $hashType = null)
	{
		$hashType = $hashType ?: self::HASH_ETAG;
		$realPath = $file instanceof SplFileInfo ? $file->getRealPath() : $file;

		if (self::HASH_ETAG === $hashType) {
			return Etag::sum($realPath);
		}

		if (self::HASH_MD5 === $hashType) {
			return md5_file($realPath);
		}

		if (self::HASH_SHA1 === $hashType) {
			return sha1_file($realPath);
		}

		if (in_array($hashType, hash_algos(), true)) {
			return hash_file($hashType, $realPath, true);
		}

		throw new RuntimeException("hash_type[{$hashType}] is not support.");
	}
}
