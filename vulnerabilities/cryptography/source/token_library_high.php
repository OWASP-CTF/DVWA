<?php

define ("ALGO", "aes-256-gcm");

// The key used to be the literal "rainbowclimbinghigh" here. A key
// committed to source control is public, and an authenticated cipher mode
// does not help if the attacker already has the key: they can forge any
// token they like directly, without needing to tamper with one. It is now
// generated once with random_bytes() and cached outside the repository.
function crypto_high_key() {
	static $key = null;
	if ($key !== null) {
		return $key;
	}

	$env = getenv ('DVWA_CRYPTO_HIGH_KEY');
	if ($env !== false && $env !== "") {
		$key = base64_decode ($env, true);
		if ($key !== false && strlen ($key) === 32) {
			return $key;
		}
	}

	$key_file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'dvwa_crypto_high.key';

	if (is_readable ($key_file)) {
		$stored = @file_get_contents ($key_file);
		if ($stored !== false && strlen ($stored) === 32) {
			$key = $stored;
			return $key;
		}
	}

	$generated = random_bytes (32);
	$previousUmask = umask (0077);
	$handle = @fopen ($key_file, 'xb');
	umask ($previousUmask);
	if ($handle !== false) {
		fwrite ($handle, $generated);
		fclose ($handle);
		$key = $generated;
		return $key;
	}

	// Somebody else won the race to create the file - re-read it.
	$stored = @file_get_contents ($key_file);
	if ($stored !== false && strlen ($stored) === 32) {
		$key = $stored;
		return $key;
	}

	$key = $generated;
	return $key;
}

// AES-CBC with no integrity check and a *fixed, reused* IV lets an attacker
// forge a valid token without ever knowing the key: for the first block,
// plaintext = decrypt(ciphertext) XOR IV, so flipping bits in the IV flips
// the exact same bits in the recovered plaintext ("userid:2" -> "userid:1"),
// and there's no MAC to detect the tampering. Use an authenticated mode
// (AES-GCM) with a fresh random IV per token instead, matching the
// impossible level - any tampering with the ciphertext, IV or tag now makes
// decryption fail outright.

function encrypt ($plaintext, $iv) {
	if (strlen ($iv) != 12) {
		throw new Exception ("IV must be 12 bytes, " . strlen ($iv) . " passed");
	}

	$e = openssl_encrypt($plaintext, ALGO, crypto_high_key(), OPENSSL_RAW_DATA, $iv, $tag);
	if ($e === false) {
		throw new Exception ("Encryption failed");
	}
	return $e . $tag;
}

function decrypt ($ciphertext, $iv) {
	if (strlen ($iv) != 12) {
		throw new Exception ("IV must be 12 bytes, " . strlen ($iv) . " passed");
	}

	$tag  = substr($ciphertext, -16);
	$text = substr($ciphertext, 0, -16);

	$e = openssl_decrypt($text, ALGO, crypto_high_key(), OPENSSL_RAW_DATA, $iv, $tag);
	if ($e === false) {
		throw new Exception ("Decryption failed");
	}
	return $e;
}

// Added the debug flag so that when calling from the script
// the function can print the data used to create the token

function create_token ($debug = false) {
	$token = "userid:2";
	$iv = openssl_random_pseudo_bytes(12);

	if ($debug) {
		print "Clear text token: " . $token . "\n";
		print "Encryption key: " . base64_encode (crypto_high_key()) . "\n";
		print "IV: " . base64_encode($iv) . "\n";
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
