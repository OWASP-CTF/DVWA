<?php

namespace Src;

class Helpers {
	public static function check_content_type() {
		$contentType = array_key_exists ("CONTENT_TYPE", $_SERVER) && is_string($_SERVER['CONTENT_TYPE'])
			? strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'], 2)[0]))
			: "";
		if ($contentType === "application/json") {
			return true;
		} else {
			$response['status_code_header'] = 'HTTP/1.1 415 Unsupported Media Type';
			$response['body'] = json_encode (array ("status" => "Invalid content type, expected JSON"));
			return $response;
		}
	}
}
