<?php

namespace Xin\Support;

use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use InvalidArgumentException;
use RuntimeException;

/**
 * 时间处理类
 */
final class Time
{
	/**
	 * @var string
	 */
	// public static $timezone = 'Asia/Shanghai';
	public static $timezone = 'PRC';

	/**
	 * 返回指定时区
	 * @param DateTimeZone|string|null $tz
	 * @return DateTimeZone
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public static function timezone($tz = null)
	{
		$tz = $tz ?: self::$timezone;

		if ($tz instanceof DateTimeZone) {
			return $tz;
		} elseif (is_string($tz)) {
			/** @noinspection PhpUnhandledExceptionInspection */
			return new DateTimeZone($tz);
		} else {
			return null;
		}
	}

	/**
	 * 返回当前时间
	 * @param DateTimeZone|string|null $tz
	 * @return Carbon|DateTime
	 * @noinspection PhpUnhandledExceptionInspection
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public static function now($tz = null)
	{
		$tz = self::timezone($tz);

		if (!class_exists('Carbon\Carbon')) {
			return new DateTime('now', $tz);
		}

		return Carbon::now($tz);
	}

	/**
	 * 返回指定时间戳的DateTime实例
	 * @param DateTimeInterface|string|int|null $datetime
	 * @param DateTimeZone|string|null $tz
	 * @return DateTime
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpUnhandledExceptionInspection
	 */
	public static function date($datetime = null, $tz = null)
	{
		// 兼容 DateTimeInterface
		if ($datetime instanceof DateTime) {
			return $datetime;
		} elseif ($datetime instanceof DateTimeInterface) {
			if (!$tz) {
				$tz = $datetime->getTimezone();
			}

			return new DateTime('@' . $datetime->getTimestamp(), $tz);
		} elseif (is_numeric($datetime)) {
			return new DateTime('@' . $datetime, self::timezone($tz));
		} else {
			$datetime = $datetime ?: 'now';
			return new DateTime($datetime, self::timezone($tz));
		}
	}

	/**
	 * 返回当前时间戳
	 * @return int
	 */
	public static function nowTimestamp()
	{
		return self::date()->getTimestamp();
	}

	/**
	 * 添加时间
	 * @param DateTimeInterface|string|int|null $target
	 * @param string $type
	 * @param DateInterval|DateTimeInterface|int $value
	 * @param bool $floorTime
	 * @return DateTimeInterface
	 */
	protected static function add($target, $type, $value, $floorTime = false)
	{
		$target = self::date($target);

		if ($value instanceof DateInterval) {
			$target->add($value);
		} elseif ($value instanceof DateTimeInterface) {
			$target->setTimestamp($value->getTimestamp());
		} elseif (is_numeric($value)) {
			$target->modify('+' . $value . $type);
		} else {
			throw new InvalidArgumentException('Invalid ' . $type);
		}

		if ($floorTime) {
			$target->setTime((int)$target->format('H'), 0, 0);
		}

		return $target;
	}

	/**
	 * 减少时间
	 * @param DateTimeInterface|string|int|null $target
	 * @param string $type
	 * @param DateInterval|DateTimeInterface|int $value
	 * @param bool $floorTime
	 * @return DateTimeInterface
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	protected static function sub($target, $type, $value, $floorTime = false)
	{
		$target = self::date($target);

		if ($value instanceof DateInterval) {
			$target->sub($value);
		} elseif ($value instanceof DateTimeInterface) {
			$target->setTimestamp($value->getTimestamp());
		} elseif (is_int($value)) {
			$target->modify('-' . $value . $type);
		} else {
			throw new InvalidArgumentException('Invalid ' . $type);
		}

		if ($floorTime) {
			$target->setTime((int)$target->format('H'), 0, 0);
		}

		return $target;
	}

	/**
	 * 添加秒数
	 * @param DateTimeInterface|string|int|null $target
	 * @param DateInterval|DateTimeInterface|int $seconds
	 * @return DateTimeInterface
	 */
	public static function addSeconds($target, $seconds = 1)
	{
		return self::add($target, 'seconds', $seconds);
	}

	/**
	 * 减少秒数
	 * @param DateTimeInterface|string|int|null $target
	 * @param DateInterval|DateTimeInterface|int $seconds
	 * @return DateTimeInterface
	 */
	public static function subSeconds($target, $seconds = 1)
	{
		return self::sub($target, 'seconds', $seconds);
	}

	/**
	 * 添加分钟
	 * @param DateTimeInterface|string|int|null $target
	 * @param DateInterval|DateTimeInterface|int $minutes
	 * @return DateTimeInterface
	 */
	public static function addMinutes($target, $minutes = 1)
	{
		return self::add($target, 'minutes', $minutes);
	}

	/**
	 * 减少分钟
	 * @param DateTimeInterface|string|int|null $target
	 * @param DateInterval|DateTimeInterface|int $minutes
	 * @return DateTimeInterface
	 */
	public static function subMinutes($target, $minutes = 1)
	{
		return self::sub($target, 'minutes', $minutes);
	}

	/**
	 * 添加小时
	 * @param DateTimeInterface|string|int|null $target
	 * @param DateInterval|DateTimeInterface|int $hours
	 * @return DateTimeInterface
	 */
	public static function addHours($target, $hours = 1, $floorTime = false)
	{
		return self::add($target, 'hours', $hours, $floorTime);
	}

	/**
	 * 减少小时
	 * @param DateTimeInterface|string|int|null $target
	 * @param DateInterval|DateTimeInterface|int $hours
	 * @return DateTimeInterface
	 */
	public static function subHours($target, $hours = 1, $floorTime = false)
	{
		return self::sub($target, 'hours', $hours, $floorTime);
	}

	/**
	 * 添加天
	 * @param DateTimeInterface|string|int|null $target
	 * @param DateInterval|DateTimeInterface|int $days
	 * @param bool $floorTime
	 * @return DateTimeInterface
	 */
	public static function addDays($target, $days = 1, $floorTime = false)
	{
		return self::add($target, 'days', $days, $floorTime);
	}

	/**
	 * 减少天
	 * @param DateTimeInterface|string|int|null $target
	 * @param DateInterval|DateTimeInterface|int $days
	 * @param bool $floorTime
	 * @return DateTimeInterface
	 */
	public static function subDays($target, $days = 1, $floorTime = false)
	{
		return self::sub($target, 'days', $days, $floorTime);
	}

	/**
	 * 添加周
	 * @param DateTimeInterface|string|int|null $target
	 * @param DateInterval|DateTimeInterface|int $weeks
	 * @param bool $floorTime
	 * @return DateTimeInterface
	 */
	public static function addWeeks($target, $weeks = 1, $floorTime = false)
	{
		return self::add($target, 'weeks', $weeks, $floorTime);
	}

	/**
	 * 减少周
	 * @param DateTimeInterface|string|int|null $target
	 * @param DateInterval|DateTimeInterface|int $weeks
	 * @param bool $floorTime
	 * @return DateTimeInterface
	 */
	public static function subWeeks($target, $weeks = 1, $floorTime = false)
	{
		return self::sub($target, 'weeks', $weeks, $floorTime);
	}


	/**
	 * 添加月
	 * @param DateTimeInterface|string|int|null $target
	 * @param DateInterval|DateTimeInterface|int $months
	 * @param bool $floorTime
	 * @return DateTimeInterface
	 */
	public static function addMonths($target, $months = 1, $floorTime = false)
	{
		return self::add($target, 'months', $months, $floorTime);
	}

	/**
	 * 减少月
	 * @param DateTimeInterface|string|int|null $target
	 * @param DateInterval|DateTimeInterface|int $months
	 * @param bool $floorTime
	 * @return DateTimeInterface
	 */
	public static function subMonths($target, $months = 1, $floorTime = false)
	{
		return self::sub($target, 'months', $months, $floorTime);
	}

	/**
	 * 添加月
	 * @param DateTimeInterface|string|int|null $target
	 * @param DateInterval|DateTimeInterface|int $years
	 * @param bool $floorTime
	 * @return DateTimeInterface
	 */
	public static function addYears($target, $years = 1, $floorTime = false)
	{
		return self::add($target, 'years', $years, $floorTime);
	}

	/**
	 * 减少月
	 * @param DateTimeInterface|string|int|null $target
	 * @param DateInterval|DateTimeInterface|int $years
	 * @param bool $floorTime
	 * @return DateTimeInterface
	 */
	public static function subYears($target, $years = 1, $floorTime = false)
	{
		return self::sub($target, 'years', $years, $floorTime);
	}

	/**
	 * 返回指定日期的分钟起始DateTime实例
	 *
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @param int $seconds
	 * @return DateTime
	 */
	public static function minuteDate($timestamp = null, $seconds = 0)
	{
		$date = self::date($timestamp);

		return $date->setTime(
			(int)$date->format('H'),
			(int)$date->format('i'),
			$seconds
		);
	}

	/**
	 * 返回指定日期的分钟结束DateTime实例
	 *
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return DateTime
	 */
	public static function endOfMinuteDate($timestamp = null)
	{
		return self::minuteDate($timestamp, 59);
	}

	/**
	 * 返回指定日期的分钟起始时间戳
	 *
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @param int $second
	 * @return int
	 */
	public static function minute($timestamp = null, $second = 0)
	{
		return self::minuteDate($timestamp, $second)->getTimestamp();
	}

	/**
	 * 返回指定日期的分钟起始时间戳
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return int
	 */
	public static function startOfMinute($timestamp = null)
	{
		return self::minuteDate($timestamp)->getTimestamp();
	}

	/**
	 * 返回指定日期的分钟结束时间戳
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return int
	 */
	public static function endOfMinute($timestamp = null)
	{
		return self::endOfMinuteDate($timestamp)->getTimestamp();
	}

	/**
	 * 返回指定日期的分钟开始和结束时间区间
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return array
	 */
	public static function minuteRange($timestamp = null, $asDate = false)
	{
		return [
			$asDate ? self::minuteDate($timestamp) : self::startOfMinute($timestamp),
			$asDate ? self::endOfMinuteDate($timestamp) : self::endOfMinute($timestamp),
		];
	}

	/**
	 * 返回指定日期的小时起始的DateTime实例
	 *
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @param int $minute
	 * @param int $second
	 * @return DateTimeInterface
	 */
	public static function hourDate($timestamp = null, $minute = 0, $second = 0)
	{
		$date = self::date($timestamp);
		$date->setTime((int)$date->format('H'), $minute, $second);

		return $date;
	}

	/**
	 * 返回指定日期的小时结束的DateTime实例
	 *
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return DateTimeInterface
	 */
	public static function endOfHourDate($timestamp = null)
	{
		return self::hourDate($timestamp, 59, 59);
	}

	/**
	 * 返回指定日期的小时起始时间戳
	 *
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @param int $minute
	 * @param int $second
	 * @return int
	 */
	public static function hour($timestamp = null, $minute = 0, $second = 0)
	{
		return self::hourDate($timestamp, $minute, $second)->getTimestamp();
	}

	/**
	 * 返回指定日期的小时起始时间戳
	 *
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @param int $minute
	 * @param int $second
	 * @return int
	 */
	public static function startOfHour($timestamp = null, $minute = 0, $second = 0)
	{
		return self::hourDate($timestamp, $minute, $second)->getTimestamp();
	}

	/**
	 * 返回指定日期的小时结束时间戳
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return int
	 */
	public static function endOfHour($timestamp = null)
	{
		return self::endOfHourDate($timestamp)->getTimestamp();
	}

	/**
	 * 返回指定日期的小时开始和结束时间
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @param bool $asDate
	 * @return array
	 */
	public static function hourRange($timestamp = null, $asDate = false)
	{
		return [
			$asDate ? self::hourDate($timestamp) : self::startOfHour($timestamp),
			$asDate ? self::endOfHourDate($timestamp) : self::endOfHour($timestamp),
		];
	}

	/**
	 * 返回指定日期天的起始DateTime实例
	 *
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @param int $hours
	 * @param int $minutes
	 * @param int $seconds
	 * @return DateTimeInterface
	 */
	public static function todayDate($timestamp = null, $hours = 0, $minutes = 0, $seconds = 0)
	{
		return self::date($timestamp)->setTime($hours, $minutes, $seconds);
	}

	/**
	 * 返回指定日期的天结束DateTime实例
	 *
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return DateTimeInterface
	 */
	public static function endOfTodayDate($timestamp = null)
	{
		return self::date($timestamp)->setTime(23, 59, 59);
	}

	/**
	 * 获取今天日期的特定时间点
	 * 如果没有提供时间戳，默认使用当前时间
	 *
	 * @param DateTimeInterface|string|int|null $timestamp 时间戳，用于计算今天的日期，默认为null
	 * @param int $hours 小时，默认为0，表示午夜
	 * @param int $minutes 分钟，默认为0
	 * @param int $seconds 秒，默认为0
	 *
	 * @return int
	 */
	public static function today($timestamp = null, $hours = 0, $minutes = 0, $seconds = 0)
	{
		return self::todayDate($timestamp, $hours, $minutes, $seconds)->getTimestamp();
	}

	/**
	 * 获取今天开始的时间戳
	 *
	 * 该方法用于获取给定日期所在天的开始时间（即00:00:00）的时间戳如果未提供时间戳，则默认为当前时间
	 * 这在需要计算或比较时间时非常有用，例如，确定某个事件是否发生在今天
	 *
	 * @param DateTimeInterface|string|int|null $timestamp 可选的时间戳，默认为null如果提供时间戳，则函数返回该时间戳所在天的开始时间戳
	 * @return int
	 */
	public static function startOfToday($timestamp = null)
	{
		return self::todayDate($timestamp)->getTimestamp();
	}

	/**
	 * 返回指定日期的天结束时间戳
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return int
	 */
	public static function endOfToday($timestamp = null)
	{
		return self::endOfTodayDate($timestamp)->getTimestamp();
	}

	/**
	 * 返回指定日期的天开始和结束时间
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @param bool $asDate
	 * @return int[]|DateTimeInterface[]
	 */
	public static function todayRange($timestamp = null, $asDate = false)
	{
		return [
			$asDate ? self::todayDate($timestamp) : self::startOfToday($timestamp),
			$asDate ? self::endOfTodayDate($timestamp) : self::endOfToday($timestamp),
		];
	}

	/**
	 * 返回指定日期昨天的开始DataTime实例
	 *
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @param int $hours
	 * @param int $minutes
	 * @param int $seconds
	 * @return DateTimeInterface
	 */
	public static function yesterdayDate($timestamp = null, $hours = 0, $minutes = 0, $seconds = 0)
	{
		return self::date($timestamp)->modify('-1 day')->setTime($hours, $minutes, $seconds);
	}

	/**
	 * 返回指定日期昨天的结束DateTime实例
	 *
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return DateTimeInterface
	 */
	public static function endOfYesterdayDate($timestamp = null)
	{
		return self::yesterdayDate($timestamp, 23, 59, 59);
	}

	/**
	 * 返回指定日期昨天的开始时间
	 *
	 * @param DateTimeInterface|string|int|null $timestamp 时间戳，用于计算昨天的日期，默认为null
	 * @param int $hours 小时，默认为0，表示午夜
	 * @param int $minutes 分钟，默认为0
	 * @param int $seconds 秒，默认为0
	 * @return int
	 */
	public static function yesterday($timestamp = null, int $hours = 0, int $minutes = 0, int $seconds = 0)
	{
		return self::yesterdayDate($timestamp, $hours, $minutes, $seconds)->getTimestamp();
	}

	/**
	 * 返回指定日期的天开始时间戳
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return int
	 */
	public static function startOfYesterday($timestamp = null)
	{
		return self::yesterdayDate($timestamp)->getTimestamp();
	}

	/**
	 * 返回指定日期的天结束时间戳
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return int
	 */
	public static function endOfYesterday($timestamp = null)
	{
		return self::endOfYesterdayDate($timestamp)->getTimestamp();
	}

	/**
	 * 返回指定日期的昨天开始和结束时间
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @param bool $asDate
	 * @return int[]|DateTimeInterface[]
	 */
	public static function yesterdayRange($timestamp = null, $asDate = false)
	{
		return [
			$asDate ? self::yesterdayDate($timestamp) : self::startOfYesterday($timestamp),
			$asDate ? self::endOfYesterdayDate($timestamp) : self::endOfYesterday($timestamp),
		];
	}

	/**
	 * 返回几天前的时间戳
	 *
	 * @param int $days
	 * @param bool $floorTime
	 * @return int
	 */
	public static function daysAgo($days = 1, $floorTime = false)
	{
		return self::subDays(null, $days, $floorTime)->getTimestamp();
	}

	/**
	 * 返回几天后的时间戳
	 *
	 * @param int $days
	 * @return int
	 */
	public static function daysAfter($days = 1, $floorTime = false)
	{
		return self::addDays(null, $days, $floorTime)->getTimestamp();
	}

	/**
	 * 获取几天前的开始和几天后的结束时间戳
	 *
	 * @param int $pastDays 天数
	 * @param int $futureDays 天数
	 * @return int[]|DateTimeInterface[]
	 */
	public static function dayRange($pastDays = 1, $futureDays = 1, $asDate = false)
	{
		$startTimestamp = self::subDays(null, $pastDays)->setTime(0, 0, 0)->getTimestamp();
		$endTimestamp = self::addDays(null, $futureDays)->setTime(23, 59, 59)->getTimestamp();

		return [
			$asDate ? self::date($startTimestamp) : $startTimestamp,
			$asDate ? self::date($endTimestamp) : $endTimestamp,
		];
	}

	/**
	 * 获取几天前零点到现在/昨日结束的时间戳
	 *
	 * @param int $pastDays 天数
	 * @param bool $useNow 返回现在或者昨天结束时间戳
	 * @param bool $asDate
	 * @return int[]|DateTimeInterface[]
	 */
	public static function dayAgoRange($pastDays = 1, bool $useNow = true, $asDate = false)
	{
		$startTimestamp = self::subDays(null, $pastDays)->getTimestamp();
		$endTimestamp = $useNow ? self::nowTimestamp() : self::endOfYesterday();

		return [
			$asDate ? self::date($startTimestamp) : $startTimestamp,
			$asDate ? self::date($endTimestamp) : $endTimestamp,
		];
	}

	/**
	 * 返回本周开始的DateTime实例
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @param int $hours 小时，默认为0，表示午夜
	 * @param int $minutes 分钟，默认为0
	 * @param int $seconds 秒，默认为0
	 * @return DateTimeInterface
	 */
	public static function weekDate($timestamp = null, $hours = 0, $minutes = 0, $seconds = 0)
	{
		$date = self::date($timestamp);
		$date->modify('monday this week');
		$date->setTime($hours, $minutes, $seconds);

		return $date;
	}

	/**
	 * 返回本周结束的DateTime实例
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @param int $hours 小时，默认为23
	 * @param int $minutes 分钟，默认为59
	 * @param int $seconds 秒，默认为59
	 * @return DateTimeInterface
	 */
	public static function endOfWeekDate($timestamp = null, $hours = 23, $minutes = 59, $seconds = 59)
	{
		$date = self::date($timestamp);
		$date->modify('sunday this week');
		$date->setTime($hours, $minutes, $seconds);

		return $date;
	}

	/**
	 * 返回本周开始的时间戳
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @param int $hours 小时，默认为0，表示午夜
	 * @param int $minutes 分钟，默认为0
	 * @param int $seconds 秒，默认为0
	 * @return int
	 */
	public static function week($timestamp = null, $hours = 0, $minutes = 0, $seconds = 0)
	{
		return self::weekDate($timestamp, $hours, $minutes, $seconds)->getTimestamp();
	}

	/**
	 * 返回指定日期的周开始时间戳
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return int
	 */
	public static function startOfWeek($timestamp = null)
	{
		return self::weekDate($timestamp)->getTimestamp();
	}

	/**
	 * 返回指定日期的周结束时间戳
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return int
	 */
	public static function endOfWeek($timestamp = null)
	{
		return self::endOfWeekDate($timestamp)->getTimestamp();
	}

	/**
	 * 返回指定日期的周开始和结束时间
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @param bool $asDate
	 * @return array
	 */
	public static function weekRange($timestamp = null, $asDate = false)
	{
		return [
			$asDate ? self::weekDate($timestamp) : self::startOfWeek($timestamp),
			$asDate ? self::endOfWeekDate($timestamp) : self::endOfWeek($timestamp),
		];
	}

	/**
	 * 返回上周开始的DateTime实例
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @param int $hours 小时，默认为0，表示午夜
	 * @param int $minutes 分钟，默认为0
	 * @param int $seconds 秒，默认为0
	 * @return DateTimeInterface
	 */
	public static function lastWeekDate($timestamp = null, $hours = 0, $minutes = 0, $seconds = 0)
	{
		$date = self::date($timestamp);
		$date->modify('monday last week');
		$date->setTime($hours, $minutes, $seconds);

		return $date;
	}

	/**
	 * 返回上周结束的DateTime实例
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @param int $hours 小时，默认为0，表示午夜
	 * @param int $minutes 分钟，默认为0
	 * @param int $seconds 秒，默认为0
	 * @return DateTimeInterface
	 */
	public static function endOfLastWeekDate($timestamp = null, $hours = 23, $minutes = 59, $seconds = 59)
	{
		$date = self::date($timestamp);
		$date->modify('sunday last week');
		$date->setTime($hours, $minutes, $seconds);

		return $date;
	}

	/**
	 * 获取给定时间戳的上周同一时间点的时间戳
	 * 如果给定的时间戳对应的上周时间点不存在（例如，1月1日的上周可能是12月的某一天），则返回false
	 *
	 * @param int $timestamp 输入的时间戳，基于此时间戳计算上周的时间戳
	 * @param int $hours 可选参数，指定返回时间戳的小时部分，默认为0
	 * @param int $minutes 可选参数，指定返回时间戳的分钟部分，默认为0
	 * @param int $seconds 可选参数，指定返回时间戳的秒部分，默认为0
	 *
	 * @return int
	 */
	public static function lastWeek($timestamp = null, $hours = 0, $minutes = 0, $seconds = 0)
	{
		return self::lastWeekDate($timestamp, $hours, $minutes, $seconds)->getTimestamp();
	}

	/**
	 * 返回指定日期的上周开始时间
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return int
	 */
	public static function startOfLastWeek($timestamp = null)
	{
		return self::lastWeekDate($timestamp)->getTimestamp();
	}

	/**
	 * 返回指定日期的上周结束时间
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return int
	 */
	public static function endOfLastWeek($timestamp = null)
	{
		return self::endOfLastWeekDate($timestamp, 23, 59, 59)->getTimestamp();
	}

	/**
	 * 返回指定日期的上周开始和结束的时间
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return array
	 */
	public static function lastWeekRange($timestamp = null, $asDate = false)
	{
		return [
			$asDate ? self::lastWeekDate($timestamp) : self::startOfLastWeek($timestamp),
			$asDate ? self::endOfLastWeekDate($timestamp) : self::endOfLastWeek($timestamp),
		];
	}

	/**
	 * 返回本月开始和结束的时间戳
	 * @param int $timestamp
	 * @param int $days
	 * @return DateTimeInterface
	 */
	public static function monthDate($timestamp = null, $days = 1)
	{
		$date = self::date($timestamp)->modify('first day of this month')->setTime(0, 0, 0);
		if ($days > 1) {
			$date->modify('+' . ($days - 1) . ' days');
		}

		return $date;
	}

	/**
	 * 返回本月结束的时间戳
	 * @param int $timestamp
	 * @return DateTimeInterface
	 */
	public static function endOfMonthDate($timestamp = null)
	{
		return self::date($timestamp)->modify('last day of this month')->setTime(23, 59, 59);
	}

	/**
	 * 返回本月开始和结束的时间戳
	 * @param int $timestamp
	 * @param int $days
	 * @return int
	 */
	public static function month($timestamp = null, $days = 1)
	{
		return self::monthDate($timestamp, $days)->getTimestamp();
	}

	/**
	 * 返回本月开始时间戳
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return int
	 */
	public static function startOfMonth($timestamp = null)
	{
		return self::monthDate($timestamp)->getTimestamp();
	}

	/**
	 * 返回本月结束的时间戳
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return int
	 */
	public static function endOfMonth($timestamp = null)
	{
		return self::endOfMonthDate($timestamp)->getTimestamp();
	}

	/**
	 * 返回指定日期的本月开始和结束的时间
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return array
	 */
	public static function monthRange($timestamp = null, $asDate = false)
	{
		return [
			$asDate ? self::monthDate($timestamp) : self::startOfMonth($timestamp),
			$asDate ? self::endOfMonthDate($timestamp) : self::endOfMonth($timestamp),
		];
	}


	/**
	 * 返回上个月开始和结束的时间戳
	 *
	 * @return DateTimeInterface
	 */
	public static function lastMonthDate($timestamp = null, $days = 1)
	{
		return self::date()->modify('first day of last month')->setTime(0, 0, 0);
	}

	/**
	 * 返回上个月结束的时间戳
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return DateTime
	 */
	public static function endOfLastMonthDate($timestamp = null)
	{
		return self::date($timestamp)->modify('last day of last month')->setTime(23, 59, 59);
	}

	/**
	 * 返回上个月开始和结束的时间戳
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @param int $days
	 * @return int
	 */
	public static function lastMonth($timestamp = null, $days = 1)
	{
		return self::lastMonthDate($timestamp, $days)->getTimestamp();
	}

	/**
	 * 返回上个月开始时间
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return int
	 */
	public static function startOfLastMonth($timestamp = null)
	{
		return self::lastMonthDate($timestamp)->getTimestamp();
	}

	/**
	 * 返回上个月结束的时间戳
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return int
	 */
	public static function endOfLastMonth($timestamp = null)
	{
		return self::endOfLastMonthDate($timestamp)->getTimestamp();
	}

	/**
	 * 返回指定日期的上月开始和结束的时间
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return array
	 */
	public static function lastMonthRange($timestamp = null, $asDate = false)
	{
		return [
			$asDate ? self::lastMonthDate($timestamp) : self::startOfLastMonth($timestamp),
			$asDate ? self::endOfLastMonthDate($timestamp) : self::endOfLastMonth($timestamp),
		];
	}

	/**
	 * 返回今年开始和结束的时间戳
	 *
	 * @return DateTimeInterface
	 */
	public static function yearDate($timestamp = null, $days = 1)
	{
		$date = self::date($timestamp)->modify('first day of this year')->setTime(0, 0, 0);
		if ($days > 1) {
			$date->modify('+' . ($days - 1) . ' days');
		}
		return $date;
	}

	/**
	 * 获取指定时间戳的结束的时间
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return DateTime
	 */
	public static function endOfYearDate($timestamp = null)
	{
		return self::date($timestamp)->modify('last day of this year')->setTime(23, 59, 59);
	}

	/**
	 * 返回今年开始和结束的时间戳
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @param int $days
	 * @return int
	 */
	public static function year($timestamp = null, $days = 1)
	{
		return self::yearDate($timestamp, $days)->getTimestamp();
	}

	/**
	 * 获取指定时间戳的年开始时间
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return int
	 */
	public static function startOfYear($timestamp = null)
	{
		return self::yearDate($timestamp)->getTimestamp();
	}

	/**
	 * 返回今年结束的时间戳
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return int
	 */
	public static function endOfYear($timestamp = null)
	{
		return self::endOfYearDate($timestamp)->getTimestamp();
	}

	/**
	 * 返回指定日期的年开始和结束的时间
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @param bool $asDate
	 * @return array
	 */
	public static function yearRange($timestamp = null, $asDate = false)
	{
		return [
			$asDate ? self::yearDate($timestamp) : self::startOfYear($timestamp),
			$asDate ? self::endOfYearDate($timestamp) : self::endOfYear($timestamp),
		];
	}

	/**
	 * 返回去年开始和结束的时间戳
	 *
	 * @return DateTimeInterface
	 */
	public static function lastYearDate($timestamp = null, $days = 1)
	{
		return self::date($timestamp)->modify('first day of last year')->setTime(0, 0, 0);
	}

	/**
	 * 获取指定日期的上年结束时间
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return DateTime
	 */
	public static function endOfLastYearDate($timestamp = null)
	{
		return self::date($timestamp)->modify('last day of last year')->setTime(23, 59, 59);
	}

	/**
	 * 获取指定日期的上年时间戳
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @param int $days
	 * @return int
	 */
	public static function lastYear($timestamp = null, $days = 1)
	{
		return self::lastYearDate($timestamp, $days)->getTimestamp();
	}

	/**
	 * 返回去年开始时间戳
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return int
	 */
	public static function startOfLastYear($timestamp = null)
	{
		return self::lastYearDate($timestamp)->getTimestamp();
	}

	/**
	 * 返回去年结束的时间戳
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return int
	 */
	public static function endOfLastYear($timestamp = null)
	{
		return self::endOfLastYearDate($timestamp)->getTimestamp();
	}

	/**
	 * 返回指定日期的上年开始和结束的时间
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @param bool $asDate
	 * @return array
	 */
	public static function lastYearRange($timestamp = null, $asDate = false)
	{
		return [
			$asDate ? self::lastYearDate($timestamp) : self::startOfLastYear($timestamp),
			$asDate ? self::endOfLastYearDate($timestamp) : self::endOfLastYear($timestamp),
		];
	}


	/**
	 * 获取毫秒级别的时间戳
	 */
	public static function milliseconds()
	{
		// 获取当前时间的微秒数
		$microtime = microtime(true); // 返回浮点数，单位为秒，包含微秒部分
		// 将秒转换为毫秒
		return (int)round($microtime * 1000);
	}

	/**
	 * 获取微秒级别的时间戳
	 *
	 * @param bool $onlyMicroseconds
	 * @return int
	 */
	public static function microseconds($onlyMicroseconds = false)
	{
		if (!$onlyMicroseconds) {
			return microtime(true);
		}

		// 获取当前时间的微秒数
		[$microseconds, $seconds] = explode(' ', microtime());
		return (int)round($microseconds * 1000000); // 将微秒部分转换为整数
	}

	/**
	 * 获取相对时间
	 *
	 * @param int $timestamp
	 * @return string
	 */
	public static function formatRelative($timestamp, $nowTimestamp = null)
	{
		$nowTimestamp = self::date($nowTimestamp)->getTimestamp();

		// 判断传入时间戳是否早于当前时间戳
		$isEarly = $timestamp <= $nowTimestamp;

		// 获取两个时间戳差值
		$diff = abs($nowTimestamp - $timestamp);

		$dirStr = $isEarly ? '前' : '后';

		if ($diff < 60) { // 一分钟之内
			$resStr = $diff . '秒' . $dirStr;
		} elseif ($diff < 3600) { // 多于59秒，少于等于59分钟59秒
			$resStr = floor($diff / 60) . '分钟' . $dirStr;
		} elseif ($diff < 86400) { // 多于59分钟59秒，少于等于23小时59分钟59秒
			$resStr = floor($diff / 3600) . '小时' . $dirStr;
		} elseif ($diff < 2623860) { // 多于23小时59分钟59秒，少于等于29天59分钟59秒
			$resStr = floor($diff / 86400) . '天' . $dirStr;
		} elseif ($diff <= 31567860 && $isEarly) { // 多于29天59分钟59秒，少于364天23小时59分钟59秒，且传入的时间戳早于当前
			$resStr = date('m-d H:i', $timestamp);
		} else {
			$resStr = date('Y-m-d', $timestamp);
		}

		return $resStr;
	}

	/**
	 * 格式化时长
	 * @param int|float $seconds
	 * @param int $decimalPlaces
	 * @param array $units
	 * @return string
	 */
	public static function formatDuration($seconds, $decimalPlaces = 2, $units = ['秒', '分', '时', '天', '月', '年'])
	{
		// 检查输入是否为非负数字
		if (!is_numeric($seconds) || $seconds < 0) {
			return '无效的时间';
		}

		// 默认单位
		$defaultUnits = ['秒', '分', '时', '天', '月', '年'];
		$units = array_pad($units, count($defaultUnits), null); // 确保单位数组长度一致
		$units = array_map(function ($unit) use ($defaultUnits) {
			return $unit ?? $defaultUnits[array_search($unit, $defaultUnits)];
		}, $units);

		// 提取单位
		[$secondUnit, $minuteUnit, $hourUnit, $dayUnit, $monthUnit, $yearUnit] = $units;

		// 计算时间
		$years = (int)($seconds / 31536000); // 31536000 秒 = 1年
		$remainingSeconds = $seconds - ($years * 31536000); // 浮点数减法

		$months = (int)($remainingSeconds / 2592000); // 2592000 秒 ≈ 1月（按30天计算）
		$remainingSeconds -= $months * 2592000; // 浮点数减法

		$days = (int)($remainingSeconds / 86400); // 86400 秒 = 1天
		$remainingSeconds -= $days * 86400; // 浮点数减法

		$hours = (int)($remainingSeconds / 3600); // 3600 秒 = 1小时
		$remainingSeconds -= $hours * 3600; // 浮点数减法

		$minutes = (int)($remainingSeconds / 60); // 60 秒 = 1分钟
		$remainingSeconds -= $minutes * 60; // 浮点数减法

		$seconds = round($remainingSeconds, $decimalPlaces); // 保留小数点

		// 格式化输出
		if ($years > 0) {
			return "{$years}{$yearUnit}{$months}{$monthUnit}{$days}{$dayUnit}{$hours}{$hourUnit}{$minutes}{$minuteUnit}{$seconds}{$secondUnit}";
		} elseif ($months > 0) {
			return "{$months}{$monthUnit}{$days}{$dayUnit}{$hours}{$hourUnit}{$minutes}{$minuteUnit}{$seconds}{$secondUnit}";
		} elseif ($days > 0) {
			return "{$days}{$dayUnit}{$hours}{$hourUnit}{$minutes}{$minuteUnit}{$seconds}{$secondUnit}";
		} elseif ($hours > 0) {
			return "{$hours}{$hourUnit}{$minutes}{$minuteUnit}{$seconds}{$secondUnit}";
		} elseif ($minutes > 0) {
			return "{$minutes}{$minuteUnit}{$seconds}{$secondUnit}";
		} else {
			return "{$seconds}{$secondUnit}";
		}

		// // 格式化输出
		// $output = [];
		// if ($years > 0) {
		//     $output[] = "{$years}{$yearUnit}";
		// }
		// if ($months > 0) {
		//     $output[] = "{$months}{$monthUnit}";
		// }
		// if ($days > 0) {
		//     $output[] = "{$days}{$dayUnit}";
		// }
		// if ($hours > 0) {
		//     $output[] = "{$hours}{$hourUnit}";
		// }
		// if ($minutes > 0) {
		//     $output[] = "{$minutes}{$minuteUnit}";
		// }
		// if ($seconds > 0 || empty($output)) { // 如果秒数为0且没有其他单位，仍显示秒数
		//     $output[] = "{$seconds}{$secondUnit}";
		// }
		//
		// return implode('', $output);
	}

	/**
	 * 格式化时长
	 * @param int|float $seconds
	 * @param int $decimalPlaces
	 * @return string
	 */
	public static function formatDurationEn($seconds, $decimalPlaces = 2)
	{
		return self::formatDuration($seconds, $decimalPlaces, ['s', 'm', 'hour', 'day', 'month', 'year']);
	}

	/**
	 * 范围日期转换时间戳
	 *
	 * @param string $rangeDatetime
	 * @param int $maxRange 最大时间间隔
	 * @param string[] $delimiters
	 * @return array
	 */
	public static function parseRange($rangeDatetime, $maxRange = 0, $delimiters = ['-', ','])
	{
		$delimiters = !is_array($delimiters) ? [$delimiters] : $delimiters;
		$delimiter = null;
		foreach ($delimiters as $char) {
			if (strpos($rangeDatetime, $char) !== false) {
				$delimiter = $char;
				break;
			}
		}
		$delimiter = $delimiter ?: $delimiters[0];

		// 获取时间间隔
		$rangeDatetime = explode($delimiter, $rangeDatetime, 2);
		$rangeDatetime[0] = trim($rangeDatetime[0]);
		$rangeDatetime[1] = isset($rangeDatetime[1]) ? trim($rangeDatetime[1]) : '';

		// 转换为时间戳
		$rangeDatetime[0] = is_numeric($rangeDatetime[0]) ? intval($rangeDatetime[0]) : strtotime($rangeDatetime[0]);
		$rangeDatetime[1] = is_numeric($rangeDatetime[1]) ? intval($rangeDatetime[1]) : strtotime($rangeDatetime[1]);
		$rangeDatetime[0] = intval($rangeDatetime[0]);
		$rangeDatetime[1] = intval($rangeDatetime[1]);

		// 兼容毫秒时间戳
		if ($rangeDatetime[0] > 9999999999) {
			$rangeDatetime[0] = intval($rangeDatetime[0] / 1000);
		}

		// 兼容毫秒时间戳
		if ($rangeDatetime[1] > 9999999999) {
			$rangeDatetime[1] = intval($rangeDatetime[1] / 1000);
		}

		// 确保开始时间小于等于结束时间
		$rangeDatetime = [
			min($rangeDatetime[0], $rangeDatetime[1]),
			max($rangeDatetime[0], $rangeDatetime[1]),
		];


		// 如果大于最大时间间隔 则用结束时间减去最大时间间隔获得开始时间
		if ($maxRange > 0 && $rangeDatetime[1] - $rangeDatetime[0] > $maxRange) {
			$rangeDatetime[0] = $rangeDatetime[1] - $maxRange;
		}

		return $rangeDatetime;
	}

	/**
	 * 获取指定时间范围内的日期数组
	 * @param DateTimeInterface|string|int|null $startTime
	 * @param DateTimeInterface|string|int|null $endTime
	 * @return DatePeriod
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public static function daysUntilOfTimestamp($startTime, $endTime)
	{
		// self::checkCarbonInstalled();
		//
		// $startTime = Carbon::createFromTimestamp($startTime);
		// $endTime = Carbon::createFromTimestamp($endTime);
		//
		// return $startTime->daysUntil($endTime);

		$interval = new DateInterval('P1D');
		return new DatePeriod(
			self::date($startTime),
			$interval,
			self::date($endTime)
		);
	}

	/**
	 * 时间排序
	 *
	 * @param array $times
	 * @return array
	 */
	public static function sort(array $times)
	{
		usort($times, function ($com1, $com2) {
			$com1 = strtotime($com1);
			$com2 = strtotime($com2);

			return $com1 < $com2 ? -1 : 1;
		});

		return $times;
	}

	/**
	 * 检查 Carbon 是否安装
	 * @return void
	 */
	protected static function checkCarbonInstalled()
	{
		if (!class_exists('Carbon\Carbon')) {
			throw new RuntimeException('Carbon is not installed. Please run `composer require nesbot/carbon`.');
		}
	}

	/**
	 * 获取当前的秒
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return int
	 */
	public static function currentSecond($timestamp = null)
	{
		return (int)self::date($timestamp)->format('s');
	}

	/**
	 * 获取当前的分钟
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return int
	 */
	public static function currentMinute($timestamp = null)
	{
		return (int)self::date($timestamp)->format('i');
	}

	/**
	 * 返回当前的小时
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return int
	 */
	public static function currentHour($timestamp = null)
	{
		return (int)self::date($timestamp)->format('H');
	}

	/**
	 * 返回当前的星期几
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return int
	 */
	public static function dayOfWeek($timestamp = null)
	{
		return intval(self::date($timestamp)->format('N'));
	}

	/**
	 * 返回当前的星期几的中文
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return string
	 */
	public static function dayOfWeekName($timestamp = null)
	{
		return self::date($timestamp)->format('l');
	}


	/**
	 * 返回当月的第几天
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return int
	 */
	public static function dayOfMonth($timestamp = null)
	{
		return (int)self::date($timestamp)->format('d');
	}

	/**
	 * 返回本年的第几天
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return int
	 */
	public static function dayOfYear($timestamp = null)
	{
		return intval(self::date($timestamp)->format('z')) + 1;
	}


	/**
	 * 返回当前的月
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return string
	 */
	public static function currentMonth($timestamp = null)
	{
		return (int)self::date($timestamp)->format('m');
	}

	/**
	 * 返回当前的年
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return int
	 */
	public static function currentYear($timestamp = null)
	{
		return (int)self::date($timestamp)->format('Y');
	}

	/**
	 * 获取当前月份的天数
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return int
	 */
	public static function dayCountInMonth($timestamp = null)
	{
		return intval(self::date($timestamp)->format('t'));
	}

	/**
	 * 获取当前年份的天数
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return int
	 */
	public static function dayCountInYear($timestamp = null)
	{
		return intval(self::date($timestamp)->format('L')) ? 366 : 365;
	}

	/**
	 * 是否是闰年
	 * @param DateTimeInterface|string|int|null $timestamp
	 * @return bool
	 */
	public static function isLeapYear($timestamp = null)
	{
		return (bool)intval(self::date($timestamp)->format('L'));
	}

	/**
	 * 天数转换成秒数
	 *
	 * @param int $day
	 * @return int
	 */
	public static function daysToSeconds(int $day = 1)
	{
		return $day * 86400;
	}

	/**
	 * 周数转换成秒数
	 *
	 * @param int $week
	 * @return int
	 */
	public static function weekToSeconds(int $week = 1)
	{
		return self::daysToSeconds(7) * $week;
	}
}
