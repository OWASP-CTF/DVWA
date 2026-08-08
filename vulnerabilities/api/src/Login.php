<?php

namespace Src;

use OpenApi\Attributes as OAT;

class Login
{
	private const ACCESS_TOKEN_LIFE = 180;
	private const REFRESH_TOKEN_LIFE = 240;

	// "12345" and "98765" were the only things standing between an attacker
	// and a self-issued bearer token, and both were printed right here in a
	// public repository.
	private static function accessTokenSecret() {
		return Helpers::persistent_secret ("access_token");
	}

	private static function refreshTokenSecret() {
		return Helpers::persistent_secret ("refresh_token");
	}


	public static function create_token() {
		$now = time();
		$tokenObj = new Token();
		$token = json_encode (array (
			"access_token" => $tokenObj->create_token(self::accessTokenSecret(), $now + self::ACCESS_TOKEN_LIFE),
			"refresh_token" => $tokenObj->create_token(self::refreshTokenSecret(), $now + self::REFRESH_TOKEN_LIFE),
			"token_type" => "bearer",
			"expires_in" => self::ACCESS_TOKEN_LIFE)
		);
		return $token;
	}

	public static function check_access_token($token) {
		$tokenObj = new Token();
		$decrypted = $tokenObj->decrypt_token ($token);

		if (!is_array ($decrypted) || !isset ($decrypted['secret']) || !isset ($decrypted['expires'])) {
			return false;
		}
		if (hash_equals (self::accessTokenSecret(), (string) $decrypted['secret']) && $decrypted['expires'] > time()) {
			return true;
		}
		return false;
	}

	public static function check_refresh_token($token) {
		$tokenObj = new Token();
		$decrypted = $tokenObj->decrypt_token ($token);

		// decrypt_token() returns false for a token it cannot open, so this
		// has to be checked before indexing into it.
		if (!is_array ($decrypted) || !isset ($decrypted['secret']) || !isset ($decrypted['expires'])) {
			return false;
		}
		if (hash_equals (self::refreshTokenSecret(), (string) $decrypted['secret']) && $decrypted['expires'] > time()) {
			return true;
		}
		return false;
	}
}
