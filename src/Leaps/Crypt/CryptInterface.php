<?php
// +----------------------------------------------------------------------
// | Leaps Framework [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2011-2014 Leaps Team (http://www.tintsoft.com)
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author XuTongle <xutongle@gmail.com>
// +----------------------------------------------------------------------
namespace Leaps\Crypt;

/**
 * Leaps\CryptInterface
 *
 * Interface for Leaps\Crypt
 */
interface CryptInterface
{

	/**
	 * Sets the cipher algorithm
	 *
	 * @param string cipher
	 * @return Leaps\EncryptInterface
	 */
	public function setCipher($cipher);

	/**
	 * Returns the current cipher
	 *
	 * @return string
	*/
	public function getCipher();

	/**
	 * Sets the encrypt/decrypt mode
	 *
	 * @param string cipher
	 * @return Leaps\EncryptInterface
	*/
	public function setMode($mode);

	/**
	 * Returns the current encryption mode
	 *
	 * @return string
	*/
	public function getMode();

	/**
	 * Sets the encryption key
	 *
	 * @param string key
	 * @return Leaps\EncryptInterface
	*/
	public function setKey($key);

	/**
	 * Returns the encryption key
	 *
	 * @return string
	*/
	public function getKey();

	/**
	 * Encrypts a text
	 *
	 * @param string text
	 * @param string key
	 * @return string
	*/
	public function encrypt($text, $key = null);

	/**
	 * Decrypts a text
	 *
	 * @param string text
	 * @param string key
	 * @return string
	*/
	public function decrypt($text, $key = null);

	/**
	 * Encrypts a text returning the result as a base64 string
	 *
	 * @param string text
	 * @param string key
	 * @return string
	*/
	public function encryptBase64($text, $key = null);

	/**
	 * Decrypt a text that is coded as a base64 string
	 *
	 * @param string text
	 * @param string key
	 * @return string
	*/
	public function decryptBase64($text, $key = null);

	/**
	 * 获取受支持的加密算法。
	 *
	 * @return array
	*/
	public function getAvailableCiphers();

	/**
	 * 获取受支持的加密模式
	 *
	 * @return array
	*/
	public function getAvailableModes();
}