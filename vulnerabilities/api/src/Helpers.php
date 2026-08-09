<?php

namespace Src;

class Helpers {
	/*
	 * Returns a secret value that stays the same across every request for
	 * the lifetime of this deployment, without ever being written into the
	 * source tree.
	 *
	 * An operator can pin the value explicitly via $envVar. Absent that,
	 * one is minted the first time it's needed and kept in a private file
	 * under the system temp directory, guarded with flock() so concurrent
	 * requests racing to create it converge on a single value instead of
	 * each generating their own - which would make every previously issued
	 * token unverifiable the moment a second worker process started.
	 */
	public static function deploymentSecret($envVar, $slot) {
		$fromEnv = getenv($envVar);
		if ($fromEnv !== false && $fromEnv !== '') {
			return $fromEnv;
		}

		$store = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'dvwa-api-secrets';
		if (!is_dir($store)) {
			@mkdir($store, 0700, true);
		}

		$file = $store . DIRECTORY_SEPARATOR . preg_replace('/[^A-Za-z0-9_-]/', '_', $slot);

		$handle = @fopen($file, 'c+');
		if ($handle === false) {
			// No writable place to keep it - hand back a value scoped to
			// this single request rather than ever falling back to a
			// fixed, predictable string.
			return bin2hex(random_bytes(32));
		}

		$secret = null;
		if (flock($handle, LOCK_EX)) {
			$current = stream_get_contents($handle);
			if ($current !== false && strlen(trim($current)) === 64) {
				$secret = trim($current);
			} else {
				$secret = bin2hex(random_bytes(32));
				ftruncate($handle, 0);
				rewind($handle);
				fwrite($handle, $secret);
				fflush($handle);
				chmod($file, 0600);
			}
			flock($handle, LOCK_UN);
		}
		fclose($handle);

		return $secret ?? bin2hex(random_bytes(32));
	}

	/*
	 * Pulls the bearer token out of the Authorization header, if present.
	 * PHP under some SAPI/rewrite combinations exposes it as
	 * REDIRECT_HTTP_AUTHORIZATION rather than HTTP_AUTHORIZATION, so both
	 * are checked.
	 */
	public static function extractBearerToken() {
		$raw = $_SERVER['HTTP_AUTHORIZATION']
			?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
			?? null;

		if ($raw === null || !preg_match('/^Bearer\s+(\S+)$/i', trim($raw), $m)) {
			return null;
		}

		return $m[1];
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
