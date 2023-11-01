<?php

namespace PhoneCarrierLookup\Data;

use PhoneCarrierLookup\Exception\UnsupportedPhoneNumber;

/**
 * вспомогательный класс для работы с конфигами
 * @package PhoneCarrierLookup\Data
 */
class Helper {

	/**
	 * получаем код телефона из существующих конфиго
	 *
	 * @throws UnsupportedPhoneNumber
	 */
	public static function getPhoneCode(string $phone_number):string {

		$matched_phone_code        = null;
		$available_phone_code_list = self::getAvailablePhoneCodeList();
		foreach ($available_phone_code_list as $phone_code) {

			// если нашли совпадение с кодом, то еще проверяем что это самое длинное совпадение
			if (str_starts_with($phone_number, $phone_code) && (is_null($matched_phone_code) || strlen($phone_code) > strlen($matched_phone_code))) {

				$matched_phone_code = $phone_code;
			}
		}

		// если не нашли подходящий код
		if (is_null($matched_phone_code)) {
			throw new UnsupportedPhoneNumber();
		}

		return $matched_phone_code;
	}

	/**
	 * получаем массив поддерживаемых кодов телефонов
	 *
	 * @return array
	 */
	public static function getAvailablePhoneCodeList():array {

		return include "php_format/available_phone_code_list.php";
	}

	/**
	 * получаем конфиг сотовых операторов по коду номера
	 *
	 * @throws \Exception
	 */
	public static function getCarrierConfig(string $phone_code):array {

		$file_path = __DIR__ . "/php_format/$phone_code.php";
		if (!file_exists($file_path)) {
			throw new \Exception("unavailable carrier config – $file_path");
		}

		return include $file_path;
	}
}