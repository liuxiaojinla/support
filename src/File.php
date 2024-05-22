<?php


namespace Xin\Support;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * 目录操作类
 */
final class File
{

    /**
     * 获取指定目录下所有的文件，包括子目录下的文件
     *
     * @param string $dir
     * @return array
     */
    public static function getFiles($dir)
    {
        $files = [];

        $each = function ($dir) use (&$each, &$files) {
            $it = new \FilesystemIterator($dir);
            /**@var $file \SplFileInfo */
            foreach ($it as $file) {
                if ($file->isDir()) {
                    $each($file->getPathname());
                } else {
                    $files[] = $file;
                }
            }
        };
        $each($dir);

        return $files;
    }

    /**
     * 递归指定目录下所有的文件，包括子目录下的文件
     *
     * @param string $dir
     * @param callable $callback
     */
    public static function each($dir, callable $callback)
    {
        $each = function ($dir) use (&$each, $callback) {
            $it = new \FilesystemIterator($dir);

            /**@var $file \SplFileInfo */
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

        $each($dir);
    }

    /**
     * 删除文件或目录
     *
     * @param string $dir
     * @return bool
     */
    public static function delete($dir)
    {
        $iterator = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $fileInfo) {
            $fileInfo->isDir() ? rmdir($fileInfo->getRealPath()) : unlink($fileInfo->getRealPath());
        }

        return rmdir($dir);

//		$each = function ($dir) use (&$each) {
//			if (!is_dir($dir)) {
//				return true;
//			}
//
//			$it = new \FilesystemIterator($dir);
//			$flag = true;
//			/**@var $file \SplFileInfo */
//			foreach ($it as $file) {
//				if ($file->isDir()) {
//					if ($each($file->getPathname()) === true) {
//						if (!@rmdir($file->getPathname()))
//							$flag = false;
//					} else {
//						$flag = false;
//					}
//				} else {
//					if (!@unlink($file->getPathname()))
//						$flag = false;
//				}
//			}
//
//			return $flag;
//		};
//
//		if ($each($dir) === true) {
//			if (!is_dir($dir) || @rmdir($dir)) {
//				return true;
//			}
//		}

//        return false;
    }

    /**
     * 基于数组创建目录和文件
     *
     * @param array $files
     */
    public static function createDirOrFiles(array $files)
    {
        foreach ($files as $key => $value) {
            $deep = substr($value, -1);
            if ($deep == DIRECTORY_SEPARATOR) {
                @mkdir($value, 0777, true);
            } else {
                @file_put_contents($value, '');
            }
        }
    }

    /**
     * 建立一个具有唯一文件名的文件
     *
     * @param string $prefix
     * @return false|string
     */
    public static function tempFilePath($prefix = '')
    {
        return tempnam(sys_get_temp_dir(), empty($prefix) ? uniqid() : $prefix);
    }

    /**
     * 写入数据到临时文件中
     *
     * @param mixed $data
     * @param string $prefix
     * @return false|string
     */
    public static function putTempFile($data, $prefix = '')
    {
        $filePath = self::tempFilePath($prefix);
        if ($filePath === false) {
            return false;
        }

        if (file_put_contents($filePath, $data) === false) {
            return false;
        }

        return $filePath;
    }

}
