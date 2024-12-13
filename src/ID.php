<?php

namespace Xin\Support;

class ID
{
	/**
	 *  Offset from Unix Epoch
	 *
	 *  Unix Epoch :    January 1, 1970 00:00:00 GMT
	 *  Epoch Offset :  November 7, 2016 00:00:00 GMT
	 */
	const EPOCH_OFFSET = 1478476800000;
	protected static $instance;

	protected function __construct()
	{
	}

	public static function getId()
	{
		$id = self::getInstance();

		return $id->createId(mt_rand(0, 31), mt_rand(0, 31), mt_rand(0, 4095));
	}

	/**
	 * @return self
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance) || !isset(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 *  Generate an unique id
	 *
	 * @param $nCenter int data center id ( 0 ~ 31 )
	 * @param $nNode int   data node id ( 0 ~ 31 )
	 * @param $sSource string  source string for calculating crc32 hash value
	 * @param $arrData &array  details about the id
	 * @return int(64) id
	 */
	public function createId($nCenter, $nNode, $sSource = null, &$arrData = null)
	{
		if (!$this->isValidCenterId($nCenter)) {
			return null;
		}
		if (!$this->isValidNodeId($nNode)) {
			return null;
		}

		//  ...
		$nRet = 0;
		$nTime = $this->getEscapedTime();
		$nCenter = intval($nCenter);
		$nNode = intval($nNode);

		if (is_string($sSource) && strlen($sSource) > 0) {
			//  use crc32 hash value instead of rand
			$nRand = crc32($sSource);
		} else {
			//  0 ~ 4095
			$nRand = rand(0, 0xFFF);
		}

		//  ...
		$nCenterV = (($nCenter << 17) & 0x00000000003E0000);
		$nNodeV = (($nNode << 12) & 0x000000000001F000);
		$nTimeV = (($nTime << 22) & 0x7FFFFFFFFFC00000);
		$nRandV = (($nRand << 0) & 0x0000000000000FFF);

		$nRet = ($nCenterV + $nNodeV + $nTimeV + $nRandV);

		//  ...
		if (!is_null($arrData)) {
			$arrData =
				[
					'center' => $nCenter,
					'node'   => $nNode,
					'time'   => $nTime,
					'rand'   => $nRandV,
				];
		}

		return intval($nRet);
	}

	/**
	 *  Get escaped time in millisecond
	 *
	 * @return int time in millisecond
	 */
	public function getEscapedTime()
	{
		return intval($this->getUnixTimestamp() - self::EPOCH_OFFSET);
	}

	/**
	 *  Get UNIX timestamp in millisecond
	 *
	 * @return float Timestamp in millisecond, for example: 1501780592275
	 */
	public function getUnixTimestamp()
	{
		return floor(microtime(true) * 1000);
	}

	/**
	 *  Verify whether the id is valid
	 *
	 * @param $nVal int    64 bits unique id
	 * @return boolean     true or false
	 */
	public function isValidId($nVal)
	{
		$bRet = false;
		$arrD = $this->parseId($nVal);
		if (is_array($arrD) &&
			array_key_exists('center', $arrD) &&
			array_key_exists('node', $arrD) &&
			array_key_exists('time', $arrD) &&
			array_key_exists('rand', $arrD)) {
			if ($this->isValidCenterId($arrD['center']) &&
				$this->isValidNodeId($arrD['node']) &&
				$this->isValidTime($arrD['time']) &&
				$this->isValidRand($arrD['rand'])) {
				$bRet = true;
			}
		}

		return $bRet;
	}

	/**
	 *  Parse an unique id
	 *
	 * @param $nId int     64 bits unique id
	 * @return array       details about the id
	 */
	public function parseId($nId)
	{
		if (!is_numeric($nId) || $nId <= 0) {
			return null;
		}

		//  ...
		$nId = intval($nId);
		$nCenter = (($nId & 0x00000000003E0000) >> 17);
		$nNode = (($nId & 0x000000000001F000) >> 12);
		$nTime = (($nId & 0x7FFFFFFFFFC00000) >> 22);
		$nRand = (($nId & 0x0000000000000FFF) >> 0);

		return
			[
				'center' => $nCenter,
				'node'   => $nNode,
				'time'   => $nTime,
				'rand'   => $nRand,
			];
	}

	/**
	 * @param $nVal int    64 bits unique id
	 * @return boolean     true or false
	 */
	public function isValidCenterId($nVal)
	{
		return is_numeric($nVal) && ($nVal >= 0) && ($nVal <= 31);
	}

	/**
	 * @param $nVal int    64 bits unique id
	 * @return boolean     true or false
	 */
	public function isValidNodeId($nVal)
	{
		return is_numeric($nVal) && ($nVal >= 0) && ($nVal <= 31);
	}

	/**
	 * @param $nVal int    64 bits unique id
	 * @return boolean     true or false
	 */
	public function isValidTime($nVal)
	{
		return is_numeric($nVal) && ($nVal >= 0);
	}

	/**
	 * @param $nVal int    64 bits unique id
	 * @return boolean     true or false
	 */
	public function isValidRand($nVal)
	{
		return is_numeric($nVal) && ($nVal >= 0) && ($nVal <= 0xFFF);
	}
}
