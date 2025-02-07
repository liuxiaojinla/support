<?php

namespace Xin\Support;

final class OS
{
	/**
	 * 检测是否是 Windows 环境
	 * @return bool
	 */
	public static function isWindows(): bool
	{
		return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
	}

	/**
	 * 检测是否是 Linux 环境
	 * @return bool
	 */
	public static function isLinux(): bool
	{
		return strtoupper(PHP_OS) === 'LINUX';
	}

	/**
	 * 检测是否是 Mac 环境
	 * @return bool
	 */
	public static function isMac(): bool
	{
		return strtoupper(PHP_OS) === 'DARWIN';
	}

	/**
	 * 检测是否是 Docker 环境
	 * @return bool
	 */
	public static function isDocker(): bool
	{
		return file_exists('/.dockerenv') || self::isContainerized();
	}

	/**
	 * 检测是否是虚拟机环境
	 * @return bool
	 */
	public static function isVirtualMachine(): bool
	{
		return self::isVirtualBox() || self::isVMware() || self::isHyperV();
	}

	/**
	 * 检测是否是 VirtualBox 虚拟机
	 * @return bool
	 */
	public static function isVirtualBox(): bool
	{
		return self::checkDmi('VirtualBox');
	}

	/**
	 * 检测是否是 VMware 虚拟机
	 * @return bool
	 */
	public static function isVMware(): bool
	{
		return self::checkDmi('VMware');
	}

	/**
	 * 检测是否是 Hyper-V 虚拟机
	 * @return bool
	 */
	public static function isHyperV(): bool
	{
		return self::checkDmi('Microsoft');
	}

	/**
	 * 检测是否是容器化环境
	 * @return bool
	 */
	public static function isContainerized(): bool
	{
		return file_exists('/proc/1/cgroup') && self::checkCgroup();
	}

	/**
	 * 检测 cgroup 文件是否包含容器标识
	 * @return bool
	 */
	public static function checkCgroup(): bool
	{
		$cgroupFile = '/proc/1/cgroup';
		if (file_exists($cgroupFile)) {
			$content = file_get_contents($cgroupFile);
			return strpos($content, ':/') === false;
		}
		return false;
	}

	/**
	 * 检测 DMI 信息
	 * @param string $searchString
	 * @return bool
	 */
	public static function checkDmi(string $searchString): bool
	{
		$dmiFile = '/sys/class/dmi/id/product_name';
		if (file_exists($dmiFile)) {
			$content = file_get_contents($dmiFile);
			return strpos($content, $searchString) !== false;
		}
		return false;
	}

	/**
	 * 获取 CPU 数量
	 * @return int
	 */
	public static function getCpuCount(): int
	{
		if (self::isWindows()) {
			$output = [];
			exec('wmic cpu get NumberOfLogicalProcessors', $output);
			return (int)trim($output[1]);
		} elseif (self::isLinux() || self::isMac()) {
			$output = [];
			exec('nproc', $output);
			return (int)trim($output[0]);
		} else {
			return 0;
		}
	}

	/**
	 * 获取 CPU 型号
	 * @return string
	 */
	public static function getCpuModel(): string
	{
		if (self::isWindows()) {
			$output = [];
			exec('wmic cpu get name', $output);
			return trim($output[1]);
		} elseif (self::isLinux()) {
			$output = [];
			exec('cat /proc/cpuinfo | grep "model name" | head -n 1', $output);
			preg_match('/model name\s+:\s+(.+)/', $output[0], $matches);
			return $matches[1] ?? 'Unknown';
		} elseif (self::isMac()) {
			$output = [];
			exec('sysctl -n machdep.cpu.brand_string', $output);
			return trim($output[0]);
		} else {
			return 'Unknown';
		}
	}

	/**
	 * 获取内存大小（以字节为单位）
	 * @return int
	 */
	public static function getTotalMemory(): int
	{
		if (self::isWindows()) {
			$output = [];
			exec('wmic OS get TotalVisibleMemorySize', $output);
			return (int)trim($output[1]) * 1024; // 转换为字节
		} elseif (self::isLinux()) {
			$output = [];
			exec('grep MemTotal /proc/meminfo', $output);
			preg_match('/MemTotal:\s+(\d+)/', $output[0], $matches);
			return (int)$matches[1] * 1024; // 转换为字节
		} elseif (self::isMac()) {
			$output = [];
			exec('sysctl hw.memsize', $output);
			preg_match('/hw.memsize:\s+(\d+)/', $output[0], $matches);
			return (int)$matches[1];
		} else {
			return 0;
		}
	}

	/**
	 * 获取已使用的内存大小（以字节为单位）
	 * @return int
	 */
	public static function getUsedMemory(): int
	{
		if (self::isWindows()) {
			$output = [];
			exec('wmic OS get FreePhysicalMemory', $output);
			$freeMemory = (int)trim($output[1]) * 1024; // 转换为字节
			return self::getTotalMemory() - $freeMemory;
		} elseif (self::isLinux()) {
			$output = [];
			exec('grep MemAvailable /proc/meminfo', $output);
			preg_match('/MemAvailable:\s+(\d+)/', $output[0], $matches);
			$availableMemory = (int)$matches[1] * 1024; // 转换为字节
			return self::getTotalMemory() - $availableMemory;
		} elseif (self::isMac()) {
			$output = [];
			exec('vm_stat', $output);
			$page_size = 4096; // macOS 页面大小为 4096 字节
			$usedMemory = 0;
			foreach ($output as $line) {
				if (strpos($line, 'Pages active') !== false) {
					preg_match('/Pages active:\s+(\d+)/', $line, $matches);
					$usedMemory += (int)$matches[1] * $page_size;
				}
				if (strpos($line, 'Pages inactive') !== false) {
					preg_match('/Pages inactive:\s+(\d+)/', $line, $matches);
					$usedMemory += (int)$matches[1] * $page_size;
				}
				if (strpos($line, 'Pages speculative') !== false) {
					preg_match('/Pages speculative:\s+(\d+)/', $line, $matches);
					$usedMemory += (int)$matches[1] * $page_size;
				}
			}
			return $usedMemory;
		} else {
			return 0;
		}
	}

	/**
	 * 获取磁盘大小（以字节为单位）
	 * @param string $path
	 * @return int
	 */
	public static function getTotalDiskSpace(string $path = '/'): int
	{
		return disk_total_space($path);
	}

	/**
	 * 获取已使用的磁盘大小（以字节为单位）
	 * @param string $path
	 * @return int
	 */
	public static function getUsedDiskSpace(string $path = '/'): int
	{
		return self::getTotalDiskSpace($path) - disk_free_space($path);
	}

	/**
	 * 获取系统主目录
	 * @return string
	 */
	public static function home()
	{
		if (self::isWindows()) {
			return getenv('USERPROFILE') ?: getenv('HOMEPATH');
		} elseif (self::isLinux() || self::isMac()) {
			return getenv('HOME');
		}
		return '';
	}

	/**
	 * 获取桌面目录
	 * @return string
	 */
	public static function desktop()
	{
		$homeDir = self::home();

		return $homeDir . DIRECTORY_SEPARATOR . 'Desktop';
	}

	/**
	 * 获取文档目录
	 * @return string
	 */
	public static function documents()
	{
		$homeDir = self::home();

		return $homeDir . DIRECTORY_SEPARATOR . 'Documents';
	}
}

