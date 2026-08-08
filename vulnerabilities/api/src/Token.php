<?php

namespace Src;

use OpenApi\Attributes as OAT;

#[OAT\Schema(required: ['token'])]
class Token {
	private const ENCRYPTION_CIPHER = "aes-256-gcm";

    # Not sure if this is needed
    #[OAT\Property(example: "11111")]
	public string $token;

	private string $secret;
	private int $expires;

	public function __construct () {
	}

	// The encryption key must never be a literal that ships in the
	// application source. DVWA is public on GitHub, so a fixed string
	// constant here (it used to be "Paintbrush") would let anyone who
	// has ever read the source forge valid access/refresh tokens for
	// *every* DVWA install without ever authenticating - that is a
	// broken authentication flaw (OWASP API2), not just a code smell.
	// Instead the key is derived from values that are fixed for the
	// life of this container, are never returned in an HTTP response,
	// and require no on-disk state, so it is correct on the very
	// first request after a fresh boot with no setup step needed.
	private static function getEncryptionKey() {
		static $key = null;
		if ($key !== null) {
			return $key;
		}
		$host = gethostname();
		if ($host === false || $host === '') {
			$host = php_uname('n');
		}
		// Per-boot kernel randomness, unreadable to a remote HTTP caller.
		$bootId = @file_get_contents('/proc/sys/kernel/random/boot_id');
		if ($bootId === false) {
			$bootId = '';
		}
		$material = $host . '|' . trim($bootId) . '|dvwa-api-token-key';
		$key = hash('sha256', $material, true);
		return $key;
	}

	private static function encrypt($cleartext) {
		$ivlen = openssl_cipher_iv_length(self::ENCRYPTION_CIPHER);
		$iv = openssl_random_pseudo_bytes($ivlen);
		$ciphertext = openssl_encrypt($cleartext, self::ENCRYPTION_CIPHER, self::getEncryptionKey(), $options=0, $iv, $tag);
		$ret = base64_encode ($tag . ":::::" . $iv . ":::::" . $ciphertext);
		return $ret;
	}

	private static function decrypt($ciphertext) {
		$str = base64_decode ($ciphertext);
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
		$cleartext = openssl_decrypt($value, self::ENCRYPTION_CIPHER, self::getEncryptionKey(), $options=0, $iv, $tag);
		return $cleartext;
	}

	// $subject identifies who authenticated (e.g. "mrbennett"). It is
	// embedded in the encrypted payload so that callers holding a
	// valid token can be tied back to the identity that logged in,
	// which OrderController uses to enforce that a token only grants
	// access to that identity's own objects (OWASP API1 - BOLA).
	public function create_token($secret, $expires, $subject = null) {
		$token = self::encrypt (json_encode (array (
						"secret" => $secret,
						"expires" => $expires,
						"sub" => $subject,
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
