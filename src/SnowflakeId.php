<?php

namespace Xin\Support;

/**
 * Snowflake ID 生成器
 */
class SnowflakeId
{
	private const EPOCH_OFFSET = 1478476800000;

	// 默认数据中心ID和节点ID
	private static int $defaultDataCenterId = 0;
	private static int $defaultNodeId = 0;

	private int $dataCenterId;
	private int $nodeId;
	private int $lastTimestamp = -1;
	private int $sequence = 0;

	public function __construct(int $dataCenterId, int $nodeId)
	{
		if (!$this->isValidCenterId($dataCenterId)) {
			throw new \InvalidArgumentException("Invalid data center ID: $dataCenterId");
		}

		if (!$this->isValidNodeId($nodeId)) {
			throw new \InvalidArgumentException("Invalid node ID: $nodeId");
		}

		$this->dataCenterId = $dataCenterId;
		$this->nodeId = $nodeId;
	}

	/**
	 * 生成唯一ID
	 *
	 * @param string|null $source 可选的源字符串用于生成序列号
	 * @return int 64位唯一ID
	 */
	public function generate(?string $source = null): int
	{
		$timestamp = $this->getCurrentTimestamp();

		// 等待下一毫秒
		if ($timestamp < $this->lastTimestamp) {
			$backwardMilliseconds = $this->lastTimestamp - $timestamp;
			throw new \RuntimeException("Clock moved backwards. Refusing to generate id for {$backwardMilliseconds} milliseconds");
		}

		// 同一毫秒内序列号自增
		if ($timestamp === $this->lastTimestamp) {
			if ($source !== null) {
				$this->sequence = crc32($source) & 0xFFF;
			} else {
				$this->sequence = ($this->sequence + 1) & 0xFFF;
			}

			// 序列号溢出，等待下一毫秒
			if ($this->sequence === 0) {
				$timestamp = $this->waitNextMillis($this->lastTimestamp);
			}
		} else {
			// 新的毫秒，重置序列号
			if ($source !== null) {
				$this->sequence = crc32($source) & 0xFFF;
			} else {
				$this->sequence = 0;
			}
		}

		$this->lastTimestamp = $timestamp;

		return $this->buildId($timestamp, $this->dataCenterId, $this->nodeId, $this->sequence);
	}

	/**
	 * 解析ID
	 *
	 * @param int $id 需要解析的ID
	 * @return array 解析后的数据
	 */
	public function parse(int $id): array
	{
		if ($id <= 0) {
			throw new \InvalidArgumentException("Invalid ID: $id");
		}

		return [
			'timestamp' => ((($id >> 22) & 0x1FFFFFFFFFF) + self::EPOCH_OFFSET),
			'dataCenter' => (($id >> 17) & 0x1F),
			'node' => (($id >> 12) & 0x1F),
			'sequence' => ($id & 0xFFF),
		];
	}

	/**
	 * 验证ID是否有效
	 *
	 * @param int $id 需要验证的ID
	 * @return bool 是否有效
	 */
	public function isValid(int $id): bool
	{
		try {
			$parsed = $this->parse($id);
			return $parsed['dataCenter'] >= 0 && $parsed['dataCenter'] <= 31
				&& $parsed['node'] >= 0 && $parsed['node'] <= 31
				&& $parsed['sequence'] >= 0 && $parsed['sequence'] <= 0xFFF
				&& $parsed['timestamp'] >= self::EPOCH_OFFSET;
		} catch (\InvalidArgumentException $e) {
			return false;
		}
	}

	/**
	 * 静态方法：快速生成ID
	 *
	 * @param string|null $source 可选的源字符串用于生成序列号
	 * @param int|null $dataCenterId 数据中心ID，默认使用默认值
	 * @param int|null $nodeId 节点ID，默认使用默认值
	 * @return int 64位唯一ID
	 */
	public static function generateId(?string $source = null, ?int $dataCenterId = null, ?int $nodeId = null): int
	{
		$dcId = $dataCenterId ?? self::$defaultDataCenterId;
		$nId = $nodeId ?? self::$defaultNodeId;

		// 创建临时实例用于生成ID
		$generator = new self($dcId, $nId);
		return $generator->generate($source);
	}

	/**
	 * 静态方法：快速解析ID
	 *
	 * @param int $id 需要解析的ID
	 * @return array 解析后的数据
	 */
	public static function parseId(int $id): array
	{
		// 创建临时实例用于解析ID
		$parser = new self(self::$defaultDataCenterId, self::$defaultNodeId);
		return $parser->parse($id);
	}

	/**
	 * 设置默认数据中心ID
	 *
	 * @param int $dataCenterId
	 */
	public static function setDefaultDataCenterId(int $dataCenterId): void
	{
		if ($dataCenterId < 0 || $dataCenterId > 31) {
			throw new \InvalidArgumentException("Data center ID must be between 0 and 31");
		}
		self::$defaultDataCenterId = $dataCenterId;
	}

	/**
	 * 设置默认节点ID
	 *
	 * @param int $nodeId
	 */
	public static function setDefaultNodeId(int $nodeId): void
	{
		if ($nodeId < 0 || $nodeId > 31) {
			throw new \InvalidArgumentException("Node ID must be between 0 and 31");
		}
		self::$defaultNodeId = $nodeId;
	}

	/**
	 * 构建ID
	 */
	private function buildId(int $timestamp, int $dataCenterId, int $nodeId, int $sequence): int
	{
		return (($timestamp - self::EPOCH_OFFSET) << 22)
			| ($dataCenterId << 17)
			| ($nodeId << 12)
			| $sequence;
	}

	/**
	 * 等待下一毫秒
	 */
	private function waitNextMillis(int $lastTimestamp): int
	{
		$timestamp = $this->getCurrentTimestamp();
		while ($timestamp <= $lastTimestamp) {
			$timestamp = $this->getCurrentTimestamp();
		}
		return $timestamp;
	}

	/**
	 * 获取当前时间戳（毫秒）
	 */
	private function getCurrentTimestamp(): int
	{
		return (int)floor(microtime(true) * 1000);
	}

	/**
	 * 验证数据中心ID
	 */
	private function isValidCenterId(int $val): bool
	{
		return $val >= 0 && $val <= 31;
	}

	/**
	 * 验证节点ID
	 */
	private function isValidNodeId(int $val): bool
	{
		return $val >= 0 && $val <= 31;
	}
}
