<?php

namespace Xin\Support;

class SerializeLike extends Fluent
{
	/**
	 * @var bool
	 */
	protected $isChanged = false;

	/**
	 * @var string $likeFilepath
	 */
	protected $likeFilepath;

	protected $automaticStorage = false;

	/**
	 * @param string $likeFilepath
	 */
	public function __construct($likeFilepath, array $items = [])
	{
		parent::__construct($items);

		$this->setAutomaticStorage(true);

		$this->likeFilepath = $likeFilepath;

		$this->load();
	}

	/**
	 * @return false|void
	 */
	protected function load()
	{
		if (!is_readable($this->likeFilepath)) {
			return false;
		}

		$data = (array)Json::arrayFromFile($this->likeFilepath);

		$originalChanged = $this->isChanged;
		$this->setAutomaticStorage(false);
		foreach ($data as $key => $value) {
			$this->set($key, $value);
		}
		$this->setAutomaticStorage(true);
		$this->isChanged = $originalChanged;
	}

	/**
	 * @inerhitDoc
	 */
	public function add($key, $value)
	{
		parent::add($key, $value);
		$this->shouldStorage();
	}

	/**
	 * @inerhitDoc
	 */
	public function set($key, $value)
	{
		parent::set($key, $value);

		$this->shouldStorage();
	}

	/**
	 * @inerhitDoc
	 */
	public function forget($key)
	{
		parent::forget($key);
		$this->shouldStorage();
	}

	/**
	 * 应该存储数据到文件中
	 * @return void
	 */
	protected function shouldStorage()
	{
		$this->isChanged = true;
		if (!$this->automaticStorage) {
			return;
		}

		File::put(
			$this->likeFilepath,
			$this->toSerialize()
		);
	}

	/**
	 * @return string
	 */
	protected function toSerialize()
	{
		return $this->toJson();
	}

	/**
	 * 是否自动的进行存储
	 * @return bool
	 */
	public function isAutomaticStorage(): bool
	{
		return $this->automaticStorage;
	}

	/**
	 * 设置自动存储
	 * @param bool $automaticStorage
	 */
	public function setAutomaticStorage(bool $automaticStorage)
	{
		$this->automaticStorage = $automaticStorage;
	}

	/**
	 * 不要自动存储
	 * @param callable $callback
	 * @param bool $shouldStorage
	 * @return mixed
	 */
	protected function dontAutomaticStorage(callable $callback, $shouldStorage = true)
	{
		try {
			$this->setAutomaticStorage(false);
			return $callback();
		} finally {
			$this->setAutomaticStorage(true);
			if ($shouldStorage) {
				$this->shouldStorage();
			}
		}
	}
}
