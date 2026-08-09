<?php

namespace Src;

use OpenApi\Attributes as OAT;

class Login
{
	private const ACCESS_TOKEN_LIFE = 180;
	private static function accessSecret() { return getenv('DVWA_ACCESS_TOKEN_SECRET') ?: bin2hex(random_bytes(32)); }
	private const REFRESH_TOKEN_LIFE = 240;
	private static function refreshSecret() { return getenv('DVWA_REFRESH_TOKEN_SECRET') ?: bin2hex(random_bytes(32)); }
	
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
		if (hash_equals(self::accessSecret(), $decrypted['secret']) && $decrypted['expires'] > time()) {
			return true;
		}
		return false;
	}

	public static function check_refresh_token($token) {
		$tokenObj = new Token();
		$decrypted = $tokenObj->decrypt_token ($token);

		if (hash_equals(self::refreshSecret(), $decrypted['secret']) && $decrypted['expires'] > time()) {
			return true;
		}
		return false;
	}
}
