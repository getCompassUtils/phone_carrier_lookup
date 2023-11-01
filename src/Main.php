<?php

namespace PhoneCarrierLookup;

use PhoneCarrierLookup\Data\Helper;
use PhoneCarrierLookup\Exception\CarrierNotFound;

/**
 * класс для определения сотового оператора по номеру
 */
class Main {

	/**
	 * получаем название сотового оператора по номеру телефона
	 *
	 * @throws CarrierNotFound
	 * @throws Exception\UnsupportedPhoneNumber
	 */
	public static function getCarrierName(string $phone_number):string {

		// убираем + в начале
		$phone_number = ltrim($phone_number, "+");

		// получаем код номера, если он поддерживается пакетом
		$phone_code = Helper::getPhoneCode($phone_number);

		// получаем конфиг
		$carrier_config = Helper::getCarrierConfig($phone_code);

		// ищем подходящего оператора
		$matched_carrier_phone_code = null;
		foreach ($carrier_config as $carrier_phone_code => $carrier_name) {

			if (str_starts_with($phone_number, $carrier_phone_code) && (is_null($matched_carrier_phone_code)
					|| strlen($carrier_phone_code) > strlen($matched_carrier_phone_code))) {

				$matched_carrier_phone_code = $carrier_phone_code;
			}
		}

		// если не нашли совпадения
		if (is_null($matched_carrier_phone_code)) {
			throw new CarrierNotFound("carrier for phone number ($phone_number) not found");
		}

		return $carrier_config[$matched_carrier_phone_code];
	}
}