<?php

namespace Src;

use OpenApi\Attributes as OAT;

class Login
{
	private const ACCESS_TOKEN_LIFE = 180;
	private const REFRESH_TOKEN_LIFE = 240;

	/*
	 * The token secrets used to be the literals "12345" and "98765" sitting in
	 * this file. A secret in version control is public. They now come from the
	 * environment, falling back to a per session value so a fresh checkout
	 * still runs without shipping a usable secret.
	 */
	private static function accessSecret() {
		return self::secretFromEnv('DVWA_API_ACCESS_SECRET', 'access');
	}

	private static function refreshSecret() {
		return self::secretFromEnv('DVWA_API_REFRESH_SECRET', 'refresh');
	}

	private static function secretFromEnv($name, $kind) {
		return Helpers::persistentSecret($name, 'api_' . $kind);
	}
	
	public static function create_token() {
		$now = time();
		$tokenObj = new Token();
		$token = json_encode (array (
			"access_token" => $tokenObj->create_token(self::accessSecret(), $now + self::ACCESS_TOKEN_LIFE),
			"refresh_token" => $tokenObj->create_token(self::refreshSecret(), $now + self::REFRESH_TOKEN_LIFE),
			"token_type" => "bearer",
			"expires_in" => self::ACCESS_TOKEN_LIFE)
		);
		return $token;
	}

	public static function check_access_token($token) {
		$tokenObj = new Token();
		$decrypted = $tokenObj->decrypt_token ($token);

		if ($decrypted === false) {
			return false;
		}
		if (!is_array($decrypted) || !isset($decrypted['secret']) || !isset($decrypted['expires'])) {
			return false;
		}
		if (hash_equals(self::accessSecret(), (string) $decrypted['secret']) && $decrypted['expires'] > time()) {
			return true;
		}
		return false;
	}

	public static function check_refresh_token($token) {
		$tokenObj = new Token();
		$decrypted = $tokenObj->decrypt_token ($token);

		// check_access_token() guarded against a false return here but this one
		// did not, so a token that failed to decrypt was indexed as an array
		// (A10:2025 Mishandling of Exceptional Conditions).
		if ($decrypted === false || !is_array($decrypted)
			|| !isset($decrypted['secret']) || !isset($decrypted['expires'])) {
			return false;
		}

		if (hash_equals(self::refreshSecret(), (string) $decrypted['secret']) && $decrypted['expires'] > time()) {
			return true;
		}
		return false;
	}
}
