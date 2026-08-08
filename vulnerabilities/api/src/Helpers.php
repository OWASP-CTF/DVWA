<?php

namespace Src;

class Helpers {
	/*
	 * A secret that survives across requests.
	 *
	 * Taken from the environment when it is set. The fallback is a key file so
	 * a fresh checkout still runs without a usable secret in the repository,
	 * while staying stable for stateless bearer tokens: a per session fallback
	 * handed out a token that the very next request could not decrypt.
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
