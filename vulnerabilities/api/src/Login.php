<?php

namespace Src;

use OpenApi\Attributes as OAT;

class Login
{
	private const ACCESS_TOKEN_LIFE = 180;
	private const REFRESH_TOKEN_LIFE = 240;

	// These used to be the literals "12345" and "98765" here - a secret
	// committed to source control is public, and guessable to boot. Each
	// is now generated with random_bytes() and kept only outside the
	// repository.
	private static function accessSecret() {
		return Helpers::persistentSecret('DVWA_API_ACCESS_SECRET', 'api_access');
	}

	private static function refreshSecret() {
		return Helpers::persistentSecret('DVWA_API_REFRESH_SECRET', 'api_refresh');
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

		if ($decrypted === false || !is_array ($decrypted) || !isset ($decrypted['secret']) || !isset ($decrypted['expires'])) {
			return false;
		}
		if (hash_equals (self::accessSecret(), (string) $decrypted['secret']) && $decrypted['expires'] > time()) {
			return true;
		}
		return false;
	}

	public static function check_refresh_token($token) {
		$tokenObj = new Token();
		$decrypted = $tokenObj->decrypt_token ($token);

		if ($decrypted === false || !is_array ($decrypted) || !isset ($decrypted['secret']) || !isset ($decrypted['expires'])) {
			return false;
		}
		if (hash_equals (self::refreshSecret(), (string) $decrypted['secret']) && $decrypted['expires'] > time()) {
			return true;
		}
		return false;
	}
}
