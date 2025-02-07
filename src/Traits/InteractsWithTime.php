<?php

namespace Xin\Support\Traits;

use DateInterval;
use DateTime;
use DateTimeInterface;
use Xin\Support\Time;

trait InteractsWithTime
{

	/**
	 * Get the number of seconds until the given DateTime.
	 *
	 * @param DateTimeInterface|DateInterval|int $delay
	 * @return int
	 */
	protected function secondsUntil($delay)
	{
		$delay = $this->parseDateInterval($delay);

		return $delay instanceof DateTimeInterface
			? max(0, $delay->getTimestamp() - $this->currentTime())
			: (int)$delay;
	}

	/**
	 * If the given value is an interval, convert it to a DateTime instance.
	 *
	 * @param DateTimeInterface|DateInterval|int $delay
	 * @return DateTimeInterface|int
	 */
	protected function parseDateInterval($delay)
	{
		if ($delay instanceof DateInterval) {
			$delay = Time::datetimeOf(Time::now()->add($delay));
		}

		return $delay;
	}

	/**
	 * Get the current system time as a UNIX timestamp.
	 *
	 * @return int
	 */
	protected function currentTime()
	{
		return Time::now()->getTimestamp();
	}

	/**
	 * Get the "available at" UNIX timestamp.
	 *
	 * @param DateTimeInterface|DateInterval|int $delay
	 * @return int
	 */
	protected function availableAt($delay = 0)
	{
		$delay = $this->parseDateInterval($delay);

		return $delay instanceof DateTimeInterface
			? $delay->getTimestamp()
			: Time::addSeconds(Time::now(),$delay)->getTimestamp();
	}

	/**
	 * 解析TTL
	 * @param mixed $ttl
	 * @return int
	 */
	protected function parseTTL($ttl)
	{
		$ttl = $this->parseDateInterval($ttl);

		if ($ttl === null) {
			$ttl = 0;
		} elseif ($ttl instanceof DateTimeInterface) {
			$ttl = $ttl->getTimestamp() - $this->currentTime();
		} elseif ($ttl instanceof DateInterval) {
			$ttl = DateTime::createFromFormat('U', (string)$this->currentTime())
					->add($ttl)
					->format('U') - $this->currentTime();
		}

		return (int)$ttl;
	}

}
