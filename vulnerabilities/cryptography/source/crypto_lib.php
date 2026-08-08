<?php

/*
 * Authenticated encryption for the cryptography module (A04:2025).
 *
 * AES-256-GCM rather than XOR, ECB or bare CBC. GCM produces an authentication
 * tag alongside the ciphertext, so a token that has been altered in any way,
 * including block reordering and bit flipping, fails to decrypt instead of
 * decrypting into something the attacker chose.
 *
 * The key is never written into the source. It comes from the environment, and
 * falls back to a random per session value so a fresh checkout still works
 * without shipping a usable secret in the repository.
 */

define( 'DVWA_CRYPTO_CIPHER', 'aes-256-gcm' );
define( 'DVWA_CRYPTO_IV_LEN', 12 );
define( 'DVWA_CRYPTO_TAG_LEN', 16 );

function dvwaCryptoKey() {
	$key = getenv( 'DVWA_CRYPTO_KEY' );

	if( $key === false || $key === '' ) {
		// check_token_high.php is reached directly by fetch() and does not go
		// through dvwaPage.inc.php, so the session has to be opened here.
		// Otherwise the fallback key would differ between issuing a token and
		// checking it, and every token would fail to decrypt.
		if( session_status() !== PHP_SESSION_ACTIVE ) {
			session_start();
		}
		if( !isset( $_SESSION[ 'dvwa_crypto_key' ] ) ) {
			$_SESSION[ 'dvwa_crypto_key' ] = bin2hex( random_bytes( 32 ) );
		}
		$key = $_SESSION[ 'dvwa_crypto_key' ];
	}

	// Stretch whatever we were given into a full width key.
	return hash( 'sha256', $key, true );
}

function dvwaCryptoEncrypt( $plaintext ) {
	// A fresh IV per message, generated here on the server. The caller never
	// supplies one: letting the client choose the IV is what made the old high
	// level token forgeable.
	$iv  = random_bytes( DVWA_CRYPTO_IV_LEN );
	$tag = '';

	$ciphertext = openssl_encrypt( $plaintext, DVWA_CRYPTO_CIPHER, dvwaCryptoKey(), OPENSSL_RAW_DATA, $iv, $tag );

	if( $ciphertext === false ) {
		throw new Exception( 'Encryption failed' );
	}

	return base64_encode( $iv . $tag . $ciphertext );
}

function dvwaCryptoDecrypt( $token ) {
	if( !is_string( $token ) || $token === '' ) {
		return false;
	}

	$raw = base64_decode( $token, true );
	// A zero length plaintext is a valid message, so the ciphertext may be
	// exactly the IV plus the tag and no more.
	if( $raw === false || strlen( $raw ) < DVWA_CRYPTO_IV_LEN + DVWA_CRYPTO_TAG_LEN ) {
		return false;
	}

	$iv         = substr( $raw, 0, DVWA_CRYPTO_IV_LEN );
	$tag        = substr( $raw, DVWA_CRYPTO_IV_LEN, DVWA_CRYPTO_TAG_LEN );
	$ciphertext = substr( $raw, DVWA_CRYPTO_IV_LEN + DVWA_CRYPTO_TAG_LEN );

	// Returns false when the tag does not verify, which is the whole point.
	return openssl_decrypt( $ciphertext, DVWA_CRYPTO_CIPHER, dvwaCryptoKey(), OPENSSL_RAW_DATA, $iv, $tag );
}

?>
