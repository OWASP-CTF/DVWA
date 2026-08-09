<?php

/*
 * CBC on its own only gives you confidentiality. Because the ciphertext and
 * the IV are not authenticated, anyone holding a token can flip bits in the
 * IV to rewrite the first block of plaintext, and the different error
 * messages returned by a failed unpad turn the server into a padding oracle.
 *
 * The fix is an AEAD mode (AES-256-GCM), a fresh random IV for every token
 * and a key that is not written into the source code.
 */

define ("ALGO", "aes-256-gcm");
define ("IV_LENGTH", 12);
define ("TAG_LENGTH", 16);

if (!function_exists ('crypto_secret')) {
	function crypto_secret() {
		static $secret = null;

		if ($secret !== null) {
			return $secret;
		}

		$env = getenv ('DVWA_CRYPTO_KEY');
		if ($env !== false && $env !== "") {
			$secret = $env;
			return $secret;
		}

		$key_file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'dvwa_crypto.key';

		if (is_readable ($key_file)) {
			$stored = @file_get_contents ($key_file);
			if ($stored !== false && $stored !== "") {
				$secret = $stored;
				return $secret;
			}
		}

		$new = base64_encode (random_bytes (32));
		if (@file_put_contents ($key_file, $new) !== false) {
			$secret = $new;
			return $secret;
		}

		// Nowhere to persist a key. Fall back to something stable for this
		// installation rather than changing it on every request.
		$secret = hash ('sha256', php_uname() . '|' . __FILE__ . '|' . @filemtime (__FILE__));
		return $secret;
	}
}

if (!function_exists ('crypto_key')) {
	// A separate key is derived for each purpose so that one part of the
	// application can never be used as an oracle for another.
	function crypto_key ($context) {
		return hash_hkdf ('sha256', crypto_secret(), 32, $context);
	}
}

function token_key() {
	return crypto_key ("dvwa/cryptography/high/tokens");
}

function encrypt ($plaintext, $iv) {
	if (strlen ($iv) != IV_LENGTH) {
		throw new Exception ("IV must be " . IV_LENGTH . " bytes, " . strlen ($iv) . " passed");
	}

	$tag = "";
	$e = openssl_encrypt ($plaintext, ALGO, token_key(), OPENSSL_RAW_DATA, $iv, $tag);
	if ($e === false) {
		throw new Exception ("Encryption failed");
	}

	// The authentication tag travels with the ciphertext.
	return $e . $tag;
}

function decrypt ($ciphertext, $iv) {
	if (strlen ($iv) != IV_LENGTH) {
		throw new Exception ("IV must be " . IV_LENGTH . " bytes, " . strlen ($iv) . " passed");
	}

	if (strlen ($ciphertext) < TAG_LENGTH) {
		throw new Exception ("Decryption failed");
	}

	$tag = substr ($ciphertext, -TAG_LENGTH);
	$text = substr ($ciphertext, 0, -TAG_LENGTH);

	$e = openssl_decrypt ($text, ALGO, token_key(), OPENSSL_RAW_DATA, $iv, $tag);

	// A single generic failure. Nothing here tells an attacker whether the
	// key, the IV, the padding or the tag was the problem.
	if ($e === false) {
		throw new Exception ("Decryption failed");
	}

	return $e;
}

// Added the debug flag so that when calling from the script
// the function can print the data used to create the token

function create_token ($debug = false) {
	$token = "userid:2";

	// A fresh random IV for every token, never a fixed one.
	$iv = random_bytes (IV_LENGTH);

	if ($debug) {
		print "Clear text token: " . $token . "\n";
		print "IV: " . base64_encode ($iv) . "\n";
	}

	$e = encrypt ($token, $iv);
	$data = array (
					"token" => base64_encode ($e),
					"iv" => base64_encode ($iv)
				);
	return json_encode($data);
}

function check_token ($data) {
	$users = array ();
	$users[1] = array ("name" => "Geoffery", "level" => "admin");
	$users[2] = array ("name" => "Bungle", "level" => "user");
	$users[3] = array ("name" => "Zippy", "level" => "user");
	$users[4] = array ("name" => "George", "level" => "user");

	$data_array = false;
	try {
		$data_array = json_decode ($data, true);
	} catch (TypeError $exp) {
		$ret = array (
						"status" => 521,
						"message" => "Data not in JSON format",
						"extra" => $exp->getMessage()
					);
	}

	if (!is_array ($data_array)) {
		$ret = array (
						"status" => 522,
						"message" => "Data in wrong format"
					);
	} else {
		if (!array_key_exists ("token", $data_array) || !is_string ($data_array['token'])) {
			$ret = array (
							"status" => 523,
							"message" => "Missing token"
						);
			return json_encode ($ret);
		}
		if (!array_key_exists ("iv", $data_array) || !is_string ($data_array['iv'])) {
			$ret = array (
							"status" => 524,
							"message" => "Missing IV"
						);
			return json_encode ($ret);
		}

		$ciphertext = base64_decode ($data_array['token'], true);
		$iv = base64_decode ($data_array['iv'], true);

		if ($ciphertext === false || $iv === false) {
			return json_encode (array (
							"status" => 526,
							"message" => "Unable to decrypt token"
						));
		}

		# Assume failure
		$ret = array (
						"status" => 500,
						"message" => "Unknown error"
					);
		try {
			$d = decrypt ($ciphertext, $iv);
			if (preg_match ("/^userid:(\d+)$/", $d, $matches)) {
				$id = $matches[1];
				if (array_key_exists ($id, $users)) {
					$user = $users[$id];
					$ret = array (
									"status" => 200,
									"user" => $user["name"],
									"level" => $user['level']
								);
				} else {
					$ret = array (
									"status" => 525,
									"message" => "User not found"
								);
				}
			} else {
				$ret = array (
								"status" => 527,
								"message" => "No user specified"
							);
			}
		} catch (Exception $exp) {
			// Do not echo the internal reason back to the caller, it is the
			// difference between messages that makes an oracle useful.
			$ret = array (
							"status" => 526,
							"message" => "Unable to decrypt token"
						);
		}
	}
	return json_encode ($ret);
}
