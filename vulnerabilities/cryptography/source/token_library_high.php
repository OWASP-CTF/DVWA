<?php

define ("KEY", "rainbowclimbinghigh");
define ("ALGO", "aes-128-cbc");
// A dedicated key for the integrity tag - never reuse the encryption key for
// a MAC as well.
define ("MAC_KEY", "prognostication-token-mac-9f2e7c1b");

// CBC mode alone gives no way to tell a legitimate ciphertext from one an
// attacker has tampered with. Historically this let an attacker flip bytes
// in the token/IV and use the *shape* of the error that came back (bad
// padding vs. a decrypt that "succeeded" but produced nonsense/garbage
// content) as an oracle, byte-by-byte, to decrypt or even forge tokens
// without ever knowing KEY. Encrypt-then-MAC removes that oracle: the tag is
// checked, in constant time, before a single byte of the ciphertext is ever
// passed to openssl_decrypt, and every failure path below (bad tag, bad
// padding, bad IV) reports back the exact same generic error so nothing
// about *why* it failed leaks to the caller.

function encrypt ($plaintext, $iv) {
	# Default padding is PKCS#7 which is interchangeable with PKCS#5
	# https://en.wikipedia.org/wiki/Padding_%28cryptography%29#PKCS#5_and_PKCS#7

	if (strlen ($iv) != 16) {
		throw new Exception ("IV must be 16 bytes, " . strlen ($iv) . " passed");
	}
	$tag = "";
	$e = openssl_encrypt($plaintext, ALGO, KEY, OPENSSL_RAW_DATA, $iv, $tag);
	if ($e === false) {
		throw new Exception ("Encryption failed");
	}
	$mac = hash_hmac ('sha256', $iv . $e, MAC_KEY, true);
	return $mac . $e;
}

function decrypt ($ciphertext, $iv) {
	if (strlen ($iv) != 16) {
		// Same generic message as every other failure below - the caller
		// should not be able to distinguish "bad IV" from "bad MAC" from
		// "bad padding".
		throw new Exception ("Invalid token");
	}
	if (strlen ($ciphertext) <= 32) {
		throw new Exception ("Invalid token");
	}
	$mac        = substr ($ciphertext, 0, 32);
	$encrypted  = substr ($ciphertext, 32);
	$expected   = hash_hmac ('sha256', $iv . $encrypted, MAC_KEY, true);
	if (!hash_equals ($expected, $mac)) {
		throw new Exception ("Invalid token");
	}
	$e = openssl_decrypt($encrypted, ALGO, KEY, OPENSSL_RAW_DATA, $iv);
	if ($e === false) {
		throw new Exception ("Invalid token");
	}
	return $e;
}

// Added the debug flag so that when calling from the script
// the function can print the data used to create the token

function create_token ($debug = false) {
	$token = "userid:2";

	// A fresh, random IV per token (rather than one hard-coded constant
	// reused for every token) removes the identical-ciphertext-for-
	// identical-plaintext pattern a fixed IV produces.
	$iv = openssl_random_pseudo_bytes (16);

	if ($debug) {
		print "Clear text token: " . $token . "\n";
		print "Encryption key: " . KEY . "\n";
		print "IV: " . bin2hex ($iv) . "\n";
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

	if (is_null ($data_array)) {
		$ret = array (
						"status" => 522,
						"message" => "Data in wrong format"
					);
	} else {
		if (!array_key_exists ("token", $data_array)) {
			$ret = array (
							"status" => 523,
							"message" => "Missing token"
						);
			return json_encode ($ret);
		}
		if (!array_key_exists ("iv", $data_array)) {
			$ret = array (
							"status" => 524,
							"message" => "Missing IV"
						);
			return json_encode ($ret);
		}
			
		$ciphertext = base64_decode ($data_array['token']);
		$iv = base64_decode ($data_array['iv']);

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
			$ret = array (
							"status" => 526,
							"message" => "Unable to decrypt token",
							"extra" => $exp->getMessage()
						);
		}
	}
	return json_encode ($ret);
}
