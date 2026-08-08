<?php

namespace Src;

use OpenApi\Attributes as OAT;

class Login
{
	private const ACCESS_TOKEN_LIFE = 180;
	// This is a type marker embedded *inside* the AES-GCM encrypted,
	// authenticated payload, not a security boundary by itself - the
	// token's unforgeability comes entirely from Token::getEncryptionKey()
	// no longer being a value published in the application source.
	private const ACCESS_TOKEN_SECRET = "access_token_v1";
	private const REFRESH_TOKEN_LIFE = 240;
	private const REFRESH_TOKEN_SECRET = "refresh_token_v1";

	public static function create_token($subject = null) {
		$now = time();
		$tokenObj = new Token();
		$token = json_encode (array (
			"access_token" => $tokenObj->create_token(self::ACCESS_TOKEN_SECRET, $now + self::ACCESS_TOKEN_LIFE, $subject),
			"refresh_token" => $tokenObj->create_token(self::REFRESH_TOKEN_SECRET, $now + self::REFRESH_TOKEN_LIFE, $subject),
			"token_type" => "bearer",
			"expires_in" => self::ACCESS_TOKEN_LIFE)
		);
		return $token;
	}

	public static function check_access_token($token) {
		return self::get_access_token_subject($token) !== false;
	}

	// Returns the authenticated subject (identity that logged in) for a
	// valid, unexpired access token, or false if the token is invalid,
	// expired, or is a refresh token presented as an access token.
	// Callers use the subject to scope object access to the caller's
	// own data instead of treating "any valid token" as "full access".
	public static function get_access_token_subject($token) {
		$tokenObj = new Token();
		$decrypted = $tokenObj->decrypt_token ($token);

		if ($decrypted === false || !is_array($decrypted)) {
			return false;
		}
		if (($decrypted['secret'] ?? null) === self::ACCESS_TOKEN_SECRET && ($decrypted['expires'] ?? 0) > time()) {
			return $decrypted['sub'] ?? '';
		}
		return false;
	}

	public static function check_refresh_token($token) {
		return self::get_refresh_token_subject($token) !== false;
	}

	public static function get_refresh_token_subject($token) {
		$tokenObj = new Token();
		$decrypted = $tokenObj->decrypt_token ($token);

		if ($decrypted === false || !is_array($decrypted)) {
			return false;
		}
		if (($decrypted['secret'] ?? null) === self::REFRESH_TOKEN_SECRET && ($decrypted['expires'] ?? 0) > time()) {
			return $decrypted['sub'] ?? '';
		}
		return false;
	}
}
