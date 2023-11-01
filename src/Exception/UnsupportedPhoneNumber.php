<?php

namespace PhoneCarrierLookup\Exception;

use Exception;

/**
 * неподдерживаемый номер телефона
 * исключение выбрасывается в случае, если для phone_code переданного номера телефона нет конфига
 *
 * @package PhoneCarrierLookup\Exception
 */
class UnsupportedPhoneNumber extends Exception {

}