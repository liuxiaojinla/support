<?php

namespace Xin\Support\Security;

use RuntimeException;
use Xin\Support\Arr;

class Hash
{
	/**
	 * 默认成本系数。
	 *
	 * @var int
	 */
	public const rounds = 12;

	/**
	 * 配置项
	 *
	 * @var array
	 */
	protected $config = [
		'rounds' => self::rounds,
		'algo' => PASSWORD_DEFAULT,
	];

	/**
	 * 构造函数
	 * @param array $config 配置项
	 */
	public function __construct(array $config = [])
	{
		$this->config = array_replace_recursive($this->config, $config);
	}

	/**
	 * 获取哈希算法
	 * @return string
	 */
	protected function getAlgo()
	{
		return Arr::get($this->config, 'algo', PASSWORD_DEFAULT);
	}

	/**
	 * 对密码进行哈希加密
	 * @param string $password 明文密码
	 * @return string 加密后的哈希字符串
	 */
	public function make(string $password, array $options = [])
	{
		// PASSWORD_DEFAULT 会自动使用当前最安全的算法（目前是 bcrypt）
		// 自动生成随机盐值，无需手动指定
		$hash = password_hash($password, $this->getAlgo(), [
			'cost' => $this->cost($options),
		]);

		if ($hash === false) {
			throw new RuntimeException('Bcrypt hashing not supported.');
		}

		return $hash;
	}

	/**
	 * 验证密码与哈希值是否匹配
	 * @param string $password 明文密码
	 * @param string|null $hashedValue 哈希字符串
	 * @param array $options
	 * @return bool 匹配返回true，否则返回false
	 */
	public function verify(string $password, ?string $hashedValue = null, array $options = [])
	{
		if (is_null($hashedValue) || strlen($hashedValue) === 0) {
			return false;
		}

		// 自动提取哈希中包含的盐值和算法，与明文密码重新计算比对
		return password_verify($password, $hashedValue);
	}

	/**
	 * 检查哈希算法是否需要更新（当系统算法升级时）
	 * @param string $hashedValue 哈希字符串
	 * @return bool 需要更新返回true，否则返回false
	 */
	public function needsRehash(string $hashedValue, array $options = [])
	{
		return password_needs_rehash($hashedValue, $this->getAlgo(), [
			'cost' => $this->cost($options),
		]);
	}

	/**
	 * 获取有关给定哈希值的信息。
	 *
	 * @param string $hashedValue
	 * @return array
	 */
	public function info($hashedValue)
	{
		return password_get_info($hashedValue);
	}

	/**
	 * 从选项数组中提取成本值。
	 *
	 * @param array $options
	 * @return int
	 */
	protected function cost(array $options = [])
	{
		return $options['rounds'] ?? Arr::get($this->config, 'rounds', self::rounds);
	}
}
