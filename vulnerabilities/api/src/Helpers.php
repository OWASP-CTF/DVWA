<?php

namespace Src;

class Helpers {
	/*
	 * A secret that survives across requests.
	 *
	 * Taken from the environment when it is set. Otherwise it is generated
	 * once with random_bytes() and cached in a key file, so a fresh
	 * checkout still runs without a hardcoded, source-controlled secret,
	 * while staying stable across requests for the stateless bearer
	 * tokens that are signed with it.
	 */
	public static function persistentSecret($envName, $fileName) {
		$value = getenv($envName);
		if ($value !== false && $value !== '') {
			return $value;
		}

		$path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'dvwa_' . $fileName . '.key';

		if (self::ownedByUs($path)) {
			$existing = trim((string) file_get_contents($path));
			if ($existing !== '') {
				return $existing;
			}
		}

		// 'xb' fails if the file already exists, so two requests racing here
		// cannot both believe they created it.
		$generated = bin2hex(random_bytes(32));
		$previousUmask = umask(0077);
		$handle = @fopen($path, 'xb');
		umask($previousUmask);

		if ($handle !== false) {
			fwrite($handle, $generated);
			fclose($handle);
			return $generated;
		}

		// Somebody else won the race - re-read, but only if we own what is there.
		if (self::ownedByUs($path)) {
			$stored = trim((string) file_get_contents($path));
			if ($stored !== '') {
				return $stored;
			}
		}

		error_log('dvwa api: could not establish a persistent secret at ' . $path);
		return $generated;
	}

	private static function ownedByUs($path) {
		if (!is_file($path)) {
			return false;
		}
		if (!function_exists('posix_geteuid')) {
			// Cannot prove ownership, so do not assume it.
			return false;
		}
		return fileowner($path) === posix_geteuid();
	}

	/*
	 * The bearer token from the Authorization header, or null. Depending on
	 * the SAPI the header can turn up as HTTP_AUTHORIZATION or, when it
	 * arrives through a rewrite, as REDIRECT_HTTP_AUTHORIZATION.
	 */
	public static function bearerToken() {
		$header = null;
		foreach (array('HTTP_AUTHORIZATION', 'REDIRECT_HTTP_AUTHORIZATION') as $key) {
			if (array_key_exists($key, $_SERVER) && $_SERVER[$key] !== '') {
				$header = $_SERVER[$key];
				break;
			}
		}

		if ($header === null) {
			return null;
		}

		$bits = explode(' ', $header);
		if (count($bits) != 2 || strtolower($bits[0]) != 'bearer') {
			return null;
		}

		return $bits[1];
	}

	public static function check_content_type() {
		if (array_key_exists ("CONTENT_TYPE", $_SERVER) && $_SERVER['CONTENT_TYPE'] == "application/json") {
			return true;
		} else {
			$response['status_code_header'] = 'HTTP/1.1 415 Unsupported Media Type';
			$response['body'] = json_encode (array ("status" => "Invalid content type, expected JSON"));
			return $response;
		}
	}
}
