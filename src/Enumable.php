<?php

namespace Xin\Support;

use BackedEnum;

/**
 * PHP 8+ 原生 Enum 增强基类
 *
 * @requires PHP >= 8.1
 */
trait Enumable
{
	/**
	 * 获取值（兼容 Unit/Backed）
	 */
	public function value(): string|int
	{
		return $this instanceof BackedEnum ? $this->value : $this->name;
	}

	/**
	 * 获取字段标签
	 */
	public function label(?string $default = '--'): string
	{
		return static::labels()[$this->value] ?? $default;
	}

	/**
	 * 转为数组（实例方法）
	 */
	public function toArray(): array
	{
		return [
			'value' => $this->value(),
			'label' => $this->label(),
			'name' => $this->name,
		];
	}

	/**
	 * 获取所有字段标签（静态）
	 */
	abstract public static function labels(): array;

	/**
	 * 获取枚举类型数据
	 */
	public static function items(): array
	{
		return array_map(
			fn($case) => $case->toArray(),
			self::cases()
		);
	}

	/**
	 * 获取所有枚举值
	 */
	public static function values(): array
	{
		return array_map(
			fn($case) => $case->value(),
			self::cases()
		);
	}

	/**
	 * 获取所有枚举名称
	 */
	public static function names(): array
	{
		return array_map(
			fn($case) => $case->name,
			self::cases()
		);
	}

	/**
	 * 根据值获取枚举实例
	 */
	public static function fromValue(string|int $value): ?static
	{
		foreach (self::cases() as $case) {
			if ($case->value() === $value) {
				return $case;
			}
		}
		return null;
	}

	/**
	 * 根据值获取标签（静态快捷方法）
	 */
	public static function labelOf(string|int $value, ?string $default = '--'): string
	{
		$case = static::fromValue($value);
		return $case?->label($default) ?? $default;
	}
}
