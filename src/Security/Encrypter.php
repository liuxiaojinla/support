<?php

namespace Xin\Support\Security;

use Exception;
use InvalidArgumentException;
use RuntimeException;

class Encrypter
{

	/**
	 * @var string
	 */
	protected $key = null;

	/**
	 * @var string
	 */
	protected $cipher = 'AES-256-CBC';

	/**
	 * @var array
	 */
	protected $config = [];

	/**
	 * @var static
	 */
	protected static $instance;

	/**
	 * 构造函数
	 * @param string $key 加密密钥
	 * @param string|null $cipher 加密算法
	 * @param array $config 配置项
	 */
	public function __construct(string $key, string $cipher = null, array $config = [])
	{
		$this->key = $key;
		$this->cipher = $cipher ?: $this->cipher;
		$this->config = array_replace_recursive($this->config, $config);
	}

	/**
	 * 获取加密密钥
	 * @return string
	 */
	public function getKey()
	{
		$encryptKey = $this->key;
		if (empty($encryptKey)) {
			throw new InvalidArgumentException('Encryption [app.key] not found');
		}

		return $encryptKey;
	}

	/**
	 * 获取加密算法
	 * @return string
	 */
	public function getCipherType()
	{
		if (!$this->cipher) {
			throw new InvalidArgumentException('Encryption [app.cipher] not found');
		}

		// 判断算法是否被支持
		if (!in_array($this->cipher, openssl_get_cipher_methods())) {
			throw new InvalidArgumentException('Encryption [app.cipher] not supported');
		}

		return $this->cipher;
	}

	/**
	 * 对数据进行加密
	 * @param string $data 要加密的数据
	 * @return string 加密后的字符串
	 */
	public function encrypt($data)
	{
		$cipher = $this->getCipherType();
		$encryptKey = $this->getKey();

		// 生成随机初始化向量
		$ivLength = openssl_cipher_iv_length($cipher);
		$iv = openssl_random_pseudo_bytes($ivLength);

		// 加密
		$encrypted = openssl_encrypt(
			$data,
			$cipher,
			$encryptKey,
			OPENSSL_RAW_DATA,
			$iv
		);

		// 拼接初始化向量和加密数据，并用base64编码
		return base64_encode($iv . $encrypted);
	}

	/**
	 * 对加密的数据进行解密
	 * @param string $encryptedData 加密的数据
	 * @return string|false 解密后的字符串，失败返回false
	 */
	public function decrypt($encryptedData)
	{
		$cipher = $this->getCipherType();
		$encryptKey = $this->getKey();

		try {
			// 解码
			$decoded = base64_decode($encryptedData);

			// 获取初始化向量长度
			$ivLength = openssl_cipher_iv_length($cipher);

			// 提取初始化向量和加密数据
			$iv = substr($decoded, 0, $ivLength);
			$data = substr($decoded, $ivLength);

			// 解密
			return openssl_decrypt(
				$data,
				$cipher,
				$encryptKey,
				OPENSSL_RAW_DATA,
				$iv
			);
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * 设置默认的加密实例
	 * @param string $key 加密密钥
	 * @param string|null $cipher 加密算法
	 * @param array $config 配置项
	 */
	public static function setDefaultInstance(string $key, string $cipher = null, array $config = [])
	{
		self::$instance = new static($key, $cipher, $config);
	}

	/**
	 * 获取默认的加密实例
	 * @return static
	 */
	public static function getInstance()
	{
		if (!self::$instance) {
			throw new RuntimeException('Encryption not set');
		}

		return self::$instance;
	}
}
