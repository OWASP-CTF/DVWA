<?php

namespace Src;

class Helpers {
	/**
	 * A secret that is stable for the life of the deployment but is not
	 * committed to source control.
	 *
	 * Signing and encryption keys that live as literals in a public
	 * repository are not secrets at all -- anyone can mint their own bearer
	 * token with them. An operator can pin the value through the
	 * environment; otherwise one is generated from the CSPRNG on first use
	 * and cached so that every worker process agrees on it.
	 */
	public static function persistent_secret($name) {
		$safe_name = preg_replace ('/[^A-Za-z0-9_]/', '', $name);

		$from_env = getenv ('DVWA_API_SECRET_' . strtoupper ($safe_name));
		if ($from_env !== false && $from_env !== '') {
			return $from_env;
		}

		$path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'dvwa_api_' . $safe_name . '.key';

		$cached = @file_get_contents ($path);
		if ($cached !== false && strlen (trim ($cached)) >= 32) {
			return trim ($cached);
		}

		$secret = bin2hex (random_bytes (32));

		// Write to a private temporary name and move it into place, so two
		// concurrent requests cannot leave a half-written key behind.
		$staging = $path . '.' . getmypid();
		if (@file_put_contents ($staging, $secret) !== false) {
			@rename ($staging, $path);
		}

		$settled = @file_get_contents ($path);
		if ($settled !== false && strlen (trim ($settled)) >= 32) {
			return trim ($settled);
		}

		return $secret;
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
