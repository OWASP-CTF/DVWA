<?php

namespace Src;

class Helpers {
	public static function check_content_type() {
		$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
		$mediaType = strtolower(trim(explode(';', $contentType, 2)[0]));
		if ($mediaType === "application/json") {
			return true;
		} else {
			$response['status_code_header'] = 'HTTP/1.1 415 Unsupported Media Type';
			$response['body'] = json_encode (array ("status" => "Invalid content type, expected JSON"));
			return $response;
		}
	}
}
