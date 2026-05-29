<?php /** @noinspection ALL */

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
	 * @return RecursiveIteratorIterator<SplFileInfo>|FilesystemIterator<SplFileInfo>
	 */
	public static function filesIterator(
		string $directory,
		bool   $recursive = true,
		?int   $recursiveIteratorIteratorFlag = null,
		?int   $filesystemIteratorFlags = null
	)
	{
		// 文件迭代器标志
		if ($filesystemIteratorFlags === null) {
			$filesystemIteratorFlags = FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO;
		}
		$filesystemIteratorFlags |= FilesystemIterator::SKIP_DOTS;

		// 非递归直接返回
		if (!$recursive) {
			return new FilesystemIterator($directory, $filesystemIteratorFlags);
		}

		// 递归迭代器标志
		if ($recursiveIteratorIteratorFlag === null) {
			$recursiveIteratorIteratorFlag = RecursiveIteratorIterator::LEAVES_ONLY;
		}

		return new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($directory, $filesystemIteratorFlags),
			$recursiveIteratorIteratorFlag,
			RecursiveIteratorIterator::CATCH_GET_CHILD
		);
	}

	/**
	 * 获取指定目录下所有的文件，包括子目录下的文件
	 *
	 * @param string $directory
	 * @param bool $recursive
	 * @return FilesystemIterator<SplFileInfo>|RecursiveIteratorIterator<SplFileInfo>
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
	 * @return RecursiveIteratorIterator<SplFileInfo>
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
	 * @return CallbackFilterIterator<SplFileInfo>|RegexIterator<SplFileInfo>
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
	 * @return RegexIterator<SplFileInfo>
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
	 * 按深度排序文件迭代器
	 * @param iterable<SplFileInfo> $files
	 * @return array<SplFileInfo>
	 */
	public static function sortByDepth(iterable $files): array
	{
		// 兼容 Iterator、Generator、IteratorAggregate 等所有非数组 iterable
		if (!is_array($files)) {
			$files = iterator_to_array($files, false);
		}

		// 方式一：使用 array_map 和 array_multisort 排序深度，优化性能
		$depths = array_map(static function (SplFileInfo $f) {
			return substr_count($f->getPathname(), DIRECTORY_SEPARATOR);
		}, $files);
		array_multisort($depths, SORT_ASC, $files);

		// // 方式二：使用 usort 排序深度，不优化性能
		// usort($files, static function (SplFileInfo $a, SplFileInfo $b) {
		// 	return substr_count($a->getPathname(), DIRECTORY_SEPARATOR)
		// 		<=> substr_count($b->getPathname(), DIRECTORY_SEPARATOR);
		// });

		return $files;
	}

	/**
	 * 按文件路径的字典序排序（升序）。
	 *
	 * 注意：FilesystemIterator 等迭代器的返回顺序依赖于底层文件系统，
	 * POSIX 标准不保证其有序性。此方法提供跨平台、确定性的排序保障。
	 * @param iterable<SplFileInfo> $files
	 * @return array<SplFileInfo>
	 */
	public static function sortByPathname(iterable $files): array
	{
		if (!is_array($files)) {
			$files = iterator_to_array($files, false);
		}

		usort($files, static function (SplFileInfo $a, SplFileInfo $b) {
			return strcmp($a->getPathname(), $b->getPathname());
		});

		return $files;
	}

	/**
	 * 获取指定目录下所有的目录迭代器，包括子目录下的目录
	 * @param string $directory
	 * @param bool $recursive
	 * @return CallbackFilterIterator<SplFileInfo>
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
	 * @see self::createMany
	 */
	public static function createDirOrFiles(array $files)
	{
		self::createMany($files);
	}

	/**
	 * 基于数组创建目录和文件
	 *
	 * @param array $files
	 * @return array
	 */
	public static function createMany(array $files)
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
	 * 判断文件是否存在
	 * @param string|SplFileInfo $path
	 * @return bool
	 */
	public static function exists($path)
	{
		if ($path instanceof SplFileInfo) {
			$path = $path->getRealPath();
		}

		return file_exists($path);
	}

	/**
	 * 判断是否为目录
	 * @param string|SplFileInfo $path
	 * @return bool
	 */
	public static function isDirectory($path)
	{
		if ($path instanceof SplFileInfo) {
			$path = $path->getRealPath();
		}

		return is_dir($path);
	}

	/**
	 * 判断是否为文件
	 * @param string|SplFileInfo $path
	 * @return bool
	 */
	public static function isFile($path)
	{
		if ($path instanceof SplFileInfo) {
			$path = $path->getRealPath();
		}

		return is_file($path);
	}

	/**
	 * 判断文件是否可读
	 * @param string|SplFileInfo $path
	 * @return bool
	 */
	public static function isReadable($path)
	{
		if ($path instanceof SplFileInfo) {
			$path = $path->getRealPath();
		}

		return is_readable($path);
	}

	/**
	 * 判断文件是否可写
	 * @param string|SplFileInfo $path
	 * @return bool
	 */
	public static function isWritable($path)
	{
		if ($path instanceof SplFileInfo) {
			$path = $path->getRealPath();
		}

		return is_writable($path);
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
	 * @param string|SplFileInfo $path
	 * @param mixed $data
	 * @param int $flags
	 * @param resource|null $context
	 * @return false|int
	 */
	public static function put($path, $data, int $flags = 0, $context = null)
	{
		if ($path instanceof SplFileInfo) {
			$path = $path->getRealPath();
		}

		self::mkdir(dirname($path));

		if ($data instanceof StreamInterface) {
			$data = $data->getContents();
		} elseif (is_object($data)) {
			if (method_exists($data, '__toString')) {
				$data = (string)$data;
			} elseif (method_exists($data, 'toString')) {
				$data = (string)$data->toString();
			} elseif (method_exists($data, 'toArray')) {
				$data = Json::encode($data->toArray());
			} elseif (method_exists($data, 'toJson')) {
				$data = $data->toJson();
			} else {
				$data = Json::encode($data);
			}
		} elseif (is_array($data)) {
			$data = Json::encode($data);
		}

		return file_put_contents($path, $data, $flags, $context);
	}

	/**
	 * 追加写入文件
	 * @param string|SplFileInfo $path
	 * @param mixed $data
	 * @param mixed|null $context
	 * @return false|int
	 */
	public static function append($path, $data, $context = null)
	{
		return self::put($path, $data, FILE_APPEND, $context);
	}

	/**
	 * 读取文件
	 * @param string|SplFileInfo $path
	 * @param bool $useIncludePath
	 * @param resource|null $context
	 * @param int $offset
	 * @param int|null $length
	 * @return false|string
	 */
	public static function get($path, bool $useIncludePath = false, $context = null, int $offset = 0, ?int $length = null)
	{
		if ($path instanceof SplFileInfo) {
			$path = $path->getRealPath();
		}

		if (!file_exists($path)) {
			return '';
		}

		return file_get_contents($path, $useIncludePath, $context, $offset, $length);
	}

	/**
	 * 获取文件信息
	 * @param SplFileInfo|string $file
	 * @return SplFileInfo|null
	 */
	public function file($file)
	{
		if ($file instanceof SplFileInfo) {
			return $file;
		}

		if (self::exists($file)) {
			return null;
		}

		return new SplFileInfo($file);
	}

	/**
	 * 获取文件路径
	 * @param SplFileInfo|string $path
	 * @return mixed|string
	 */
	public function path($path)
	{
		if ($path instanceof SplFileInfo) {
			return $path->getPathname();
		}

		return $path;
	}

	/**
	 * 获取文件真实路径
	 * @param SplFileInfo|string $path
	 * @return false|string
	 */
	public function realPath($path)
	{
		if ($path instanceof SplFileInfo) {
			return $path->getRealPath();
		}

		return realpath($path);
	}

	/**
	 * 获取文件基础名
	 * @param SplFileInfo|string $file
	 * @param string|null $suffix
	 * @return string
	 */
	public function basename($file, string $suffix = '')
	{
		if ($file instanceof SplFileInfo) {
			return $file->getBasename($suffix);
		}

		return basename($file, $suffix);
	}

	/**
	 * 获取文件扩展名
	 * @param SplFileInfo|string $file
	 * @return string
	 */
	public static function extension($file)
	{
		$file = self::file($file);
		if (!$file) {
			return '';
		}

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
	 * @param SplFileInfo|string $file
	 * @return string
	 */
	public static function mime($file)
	{
		$file = self::file($file);
		if (!$file) {
			return '';
		}

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
		$realPath = self::realPath($file);

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
