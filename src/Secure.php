<?php
/**
 * I know no such things as genius,it is nothing but labor and diligence.
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @author 晋<657306123@qq.com>
 */

namespace Xin\Support;

/**
 * 安全相关工具类
 */
final class Secure
{

	/**
	 * 获取签名证书ID
	 *
	 * @param string $certPath 签名证书路径
	 * @param string $certPassword 签名证书密码
	 * @return mixed
	 */
	public static function getSignCertId($certPath, $certPassword)
	{
		$pkcs12CertData = file_get_contents($certPath);
		openssl_pkcs12_read($pkcs12CertData, $certs, $certPassword);
		$x509data = $certs ['cert'];
		openssl_x509_read($x509data);
		$certificates = openssl_x509_parse($x509data);

		return $certificates['serialNumber'];
	}

	/**
	 * 返回(签名)证书私钥
	 *
	 * @param string $certPath
	 * @param string $certPassword
	 * @return mixed
	 */
	public static function getPrivateKey($certPath, $certPassword)
	{
		$pkcs12 = file_get_contents($certPath);
		openssl_pkcs12_read($pkcs12, $certificates, $certPassword);

		return $certificates['pkey'];
	}

	/**
	 * 根据证书ID 加载 证书
	 *
	 * @param string $certId
	 * @param string $certDir
	 * @return string
	 */
	public static function getPublicKeyByCertId($certId, $certDir)
	{
		$handle = opendir($certDir);
		if (!$handle) {
			return null;
		}

		while ($file = readdir($handle)) {
			clearstatcache();
			$filePath = $certDir . '/' . $file;
			if (is_file($filePath) && pathinfo($file, PATHINFO_EXTENSION) == 'cer' && self::getCertIdByCerPath($filePath) == $certId) {
				closedir($handle);

				return file_get_contents($filePath);
			}
		}

		closedir($handle);

		return null;
	}

	/**
	 * 取证书ID(.cer)
	 *
	 * @param string $certPath 证书路径
	 * @return string
	 */
	public static function getCertIdByCerPath($certPath)
	{
		$x509data = file_get_contents($certPath);
		openssl_x509_read($x509data);
		$certData = openssl_x509_parse($x509data);

		return $certData['serialNumber'];
	}

	/**
	 * 签名字符串
	 *
	 * @param string $content 要加密的内容
	 * @param string $publicKey 证书公钥
	 * @return string
	 */
	public static function rsaSign($content, $publicKey)
	{
		$pKeyId = openssl_get_privatekey($publicKey);
		openssl_sign($content, $sign, $pKeyId);
		openssl_free_key($pKeyId);
		return base64_encode($sign);
	}

	/**
	 * 验证签名
	 *
	 * @param string $content 要解密的内容
	 * @param string $sign 签名字符串
	 * @param string $public_key 证书公钥
	 * @return bool
	 */
	public static function rsaVerify($content, $sign, $public_key)
	{
		$sign = base64_decode($sign);
		$pKeyId = openssl_get_publickey($public_key);

		$verify = '';
		if ($pKeyId) {
			$verify = openssl_verify($content, $sign, $pKeyId);
			openssl_free_key($pKeyId);
		}

		if ($verify) {
			return true;
		}

		return false;
	}

}
