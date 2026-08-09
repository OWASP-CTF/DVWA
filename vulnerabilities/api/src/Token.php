<?php

namespace Src;

use OpenApi\Attributes as OAT;

#[OAT\Schema(required: ['token'])]
class Token {
	private const ENCRYPTION_CIPHER = "aes-128-gcm";

    # Not sure if this is needed
    #[OAT\Property(example: "11111")]
	public string $token;

	private string $secret;
	private int $expires;

	public function __construct () {
	}

	private static function encryptionKey() {
		$path = sys_get_temp_dir() . '/dvwa-api-token.key';
		$key = @file_get_contents($path);
		if ($key !== false && strlen($key) === 32) {
			return $key;
		}
		$key = random_bytes(32);
		$handle = @fopen($path, 'x');
		if ($handle !== false) {
			fwrite($handle, $key);
			fclose($handle);
			@chmod($path, 0600);
			return $key;
		}
		$storedKey = @file_get_contents($path);
		return ($storedKey !== false && strlen($storedKey) === 32) ? $storedKey : $key;
	}

	private static function encrypt($cleartext) {
		$ivlen = openssl_cipher_iv_length(self::ENCRYPTION_CIPHER);
		$iv = random_bytes($ivlen);
		$ciphertext = openssl_encrypt($cleartext, self::ENCRYPTION_CIPHER, self::encryptionKey(), $options=0, $iv, $tag);
		$ret = base64_encode ($tag . ":::::" . $iv . ":::::" . $ciphertext);
		return $ret;
	}

	private static function decrypt($ciphertext) {
		$str = base64_decode ($ciphertext, true);
		if ($str === false) {
			return false;
		}
		$bits = explode (":::::", $str);
		if (count ($bits) != 3) {
			return false;
		}
		$value = $bits[2];
		$iv = $bits[1];
		$tag = $bits[0];
		$cleartext = openssl_decrypt($value, self::ENCRYPTION_CIPHER, self::encryptionKey(), $options=0, $iv, $tag);
		return $cleartext;
	}
	public function create_token($secret, $expires) {
		$token = self::encrypt (json_encode (array (
						"secret" => $secret,
						"expires" => $expires,
					)));
		return $token;
	}

	public function decrypt_token($token) {
		$decrypted = self::decrypt($token);

		if ($decrypted === false) {
			return false;
		}

		$token = json_decode ($decrypted, true);
		return $token;
	}
}

?>
