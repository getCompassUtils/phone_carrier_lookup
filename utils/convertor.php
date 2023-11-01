<?php

/**
 * скрипт конертит txt_format в php_format
 *
 * usage: php convertor.php <path/to/txt_format> <path/to/php_format>
 */

class Convertor {

	/**
	 * запускаем скрипт
	 */
	public static function run():void {

		// получаем аргументы
		[$path_to_txt_format, $path_to_php_format] = self::_parseArgs();

		// получаем все .txt файлы в директории
		$txt_file_list = self::_getTxtFileList($path_to_txt_format);

		// пробегаемся по каждому файлу
		foreach ($txt_file_list as $file) {

			// конвертируем содержимое файла
			self::_convertFile($path_to_txt_format, $file, $path_to_php_format);
		}

		// в директории php_format сохраняем файл с массивом всех существующих phone_code
		self::_saveAvailablePhoneCodeList($txt_file_list, $path_to_php_format);

		echo "Скрипт успешно отработал";
	}

	/**
	 * получаем аргументы скрипта
	 *
	 * @return array
	 */
	protected static function _parseArgs():array {

		global $argv;
		if (count($argv) != 3) {

			echo "usage: php convertor.php <path/to/txt_format> <path/to/php_format>";
			exit;
		}

		// проверяем наличие директории
		if (!is_dir($argv[1])) {

			echo "incorrect path to txt format directory ($argv[1])";
			exit;
		}

		// проверяем наличие директории
		if (!is_dir($argv[2])) {

			echo "incorrect path to php format directory ($argv[1])";
			exit;
		}

		return [$argv[1], $argv[2]];
	}

	/**
	 * получаем список .txt файлов, которые будем конвертировать
	 */
	protected static function _getTxtFileList(string $path):array {

		$output = [];

		$file_list = scandir($path);
		foreach ($file_list as $file) {

			if (str_contains($file, ".txt")) {
				$output[] = $file;
			}
		}

		return $output;
	}

	/**
	 * конвертируем файл
	 */
	protected static function _convertFile(string $path_to_txt_format, string $file_name, string $path_to_php_format):void {

		// открываем файл на чтение
		$source_file_path = "$path_to_txt_format/$file_name";
		$file             = fopen($source_file_path, "r");

		// конвертируем содержимое
		$converted_content = self::_convertFileContent($file, $file_name);

		// закрываем файл
		fclose($file);

		// создаем файл с результатами
		$temp             = explode(".", $file_name);
		$result_file_name = "$temp[0].php";
		$result_file_path = "$path_to_php_format/$result_file_name";
		file_put_contents($result_file_path, $converted_content);
	}

	/**
	 * конвертируем содержимое файла
	 * @mixed
	 */
	protected static function _convertFileContent($file, string $file_name):string {

		// подготавливаем ответ
		$output = "<?php\n\nreturn [";

		// берем первый символ из файла
		$country_phone_code_first_char = $file_name[0];

		// пробегаемся по содержимому
		/** @noinspection PhpAssignmentInConditionInspection */
		while (($line = fgets($file)) !== false) {

			// если первый символ строки !== первому символу файла, то пропускаем такую строку
			// почему:
			// каждый файл в названии имеет паттерн – {country_phone_code}.txt
			// при этом каждая полезная строка файла имеет формат {carrier_phone_code}|{carrier_name}
			// при этом carrier_phone_code всегда начинается с country_phone_code
			if ($line[0] !== $country_phone_code_first_char) {
				continue;
			}

			// $line имеет содержимое – 7995998|Tele2
			$temp         = explode("|", $line);
			$carrier_name = trim($temp[1]);
			$output       .= "\n\t$temp[0] => \"$carrier_name\",";
		}

		$output .= "\r\n];";

		return $output;
	}

	/**
	 * сохраняем массив поддерживаемых телефонных кодов
	 */
	protected static function _saveAvailablePhoneCodeList(array $txt_file_list, string $path_to_php_format):void {

		$available_phone_code_list_string = "";
		foreach ($txt_file_list as $file_name) {

			$temp                             = explode(".", $file_name);
			$available_phone_code_list_string .= "\"$temp[0]\",";
		}

		$content   = "<?php\n\nreturn [\n\t$available_phone_code_list_string\n];";
		$file_path = "$path_to_php_format/available_phone_code_list.php";
		file_put_contents($file_path, $content);
	}
}

Convertor::run();