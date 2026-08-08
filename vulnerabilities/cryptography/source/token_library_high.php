<?php

require_once( "crypto_lib.php" );

/*
 * This library used aes-128-cbc with the key "rainbowclimbinghigh" and the IV
 * "1234567812345678" both written into the source, and check_token() accepted
 * an IV chosen by the caller. CBC with no MAC lets an attacker flip bits in
 * the first block by flipping the matching bits of the IV, which turned
 * "userid:2" into "userid:1" and handed over the admin account.
 *
 * Encryption is now AES-256-GCM through crypto_lib.php: the server picks the
 * IV, the key comes from the environment, and the authentication tag means a
 * modified token fails to decrypt rather than decrypting into a chosen value.
 */

function create_token ($debug = false) {
	$token = "userid:2";

	if ($debug) {
		print "Clear text token: " . $token . "\n";
	}

	$data = array (
		"token" => dvwaCryptoEncrypt ($token),
	);

	return json_encode($data);
}

function check_token ($data) {
	$users = array ();
	$users[1] = array ("name" => "Geoffery", "level" => "admin");
	$users[2] = array ("name" => "Bungle", "level" => "user");
	$users[3] = array ("name" => "Zippy", "level" => "user");
	$users[4] = array ("name" => "George", "level" => "user");

	$data_array = json_decode ($data, true);

	if (!is_array ($data_array)) {
		return json_encode (array (
						"status" => 522,
						"message" => "Data in wrong format"
					));
	}

	if (!array_key_exists ("token", $data_array)) {
		return json_encode (array (
						"status" => 523,
						"message" => "Missing token"
					));
	}

	// Note there is deliberately no "iv" parameter any more. The IV is part of
	// the token and is chosen by the server.
	$plaintext = dvwaCryptoDecrypt ($data_array['token']);

	if ($plaintext === false) {
		return json_encode (array (
						"status" => 526,
						"message" => "Unable to decrypt token"
					));
	}

	if (!preg_match ("/^userid:(\d+)$/", $plaintext, $matches)) {
		return json_encode (array (
						"status" => 527,
						"message" => "No user specified"
					));
	}

	$id = intval ($matches[1]);
	if (!array_key_exists ($id, $users)) {
		return json_encode (array (
						"status" => 525,
						"message" => "User not found"
					));
	}

	$user = $users[$id];
	return json_encode (array (
					"status" => 200,
					"user" => $user["name"],
					"level" => $user['level']
				));
}
