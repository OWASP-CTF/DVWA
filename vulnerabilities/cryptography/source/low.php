<?php

function cryptography_low_session_key( $name ) {
	if( !isset( $_SESSION[ $name ] ) || !is_string( $_SESSION[ $name ] ) || strlen( $_SESSION[ $name ] ) !== 64 || !ctype_xdigit( $_SESSION[ $name ] ) ) {
		$_SESSION[ $name ] = bin2hex( random_bytes( 32 ) );
	}

	return hex2bin( $_SESSION[ $name ] );
}

function cryptography_low_encrypt( $cleartext, $key ) {
	$nonce = random_bytes( 12 );
	$tag = '';
	$ciphertext = openssl_encrypt( $cleartext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $nonce, $tag );

	if( $ciphertext === false ) {
		throw new Exception( 'Encryption failed' );
	}

	return base64_encode( $nonce . $tag . $ciphertext );
}

function cryptography_low_decrypt( $encoded, $key ) {
	$payload = base64_decode( $encoded, true );
	if( $payload === false || strlen( $payload ) < 29 ) {
		throw new Exception( 'Invalid message' );
	}

	$nonce = substr( $payload, 0, 12 );
	$tag = substr( $payload, 12, 16 );
	$ciphertext = substr( $payload, 28 );
	$cleartext = openssl_decrypt( $ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $nonce, $tag );

	if( $cleartext === false ) {
		throw new Exception( 'Invalid message' );
	}

	return $cleartext;
}

function cryptography_low_password() {
	$configured_password = getenv( 'DVWA_CRYPTOGRAPHY_LOW_PASSWORD' );
	if( is_string( $configured_password ) && $configured_password !== '' ) {
		return $configured_password;
	}

	if( !isset( $_SESSION[ 'cryptography_low_password' ] ) || !is_string( $_SESSION[ 'cryptography_low_password' ] ) ) {
		$_SESSION[ 'cryptography_low_password' ] = bin2hex( random_bytes( 16 ) );
	}

	return $_SESSION[ 'cryptography_low_password' ];
}

$errors = "";
$success = "";
$messages = "";
$encoded = null;
$encode_radio_selected = " checked='checked' ";
$decode_radio_selected = " ";
$message = "";
$exchange_key = cryptography_low_session_key( 'cryptography_low_exchange_key' );
$intercept_key = cryptography_low_session_key( 'cryptography_low_intercept_key' );
$expected_password = cryptography_low_password();
$intercepted_message = cryptography_low_encrypt( "Your new password is: {$expected_password}", $intercept_key );

if( $_SERVER[ 'REQUEST_METHOD' ] == 'POST' ) {
	try {
		if( array_key_exists( 'message', $_POST ) ) {
			if( !is_string( $_POST[ 'message' ] ) ) {
				throw new Exception( 'Invalid message' );
			}

			$message = $_POST[ 'message' ];
			if( array_key_exists( 'direction', $_POST ) && $_POST[ 'direction' ] === 'decode' ) {
				$encoded = cryptography_low_decrypt( $message, $exchange_key );
				$encode_radio_selected = " ";
				$decode_radio_selected = " checked='checked' ";
			}
			else {
				$encoded = cryptography_low_encrypt( $message, $exchange_key );
			}
		}

		if( array_key_exists( 'password', $_POST ) ) {
			if( !is_string( $_POST[ 'password' ] ) ) {
				throw new Exception( 'Login Failed' );
			}

			$password = $_POST[ 'password' ];
			if( hash_equals( $expected_password, $password ) ) {
				$success = 'Welcome back user';
			}
			else {
				$errors = 'Login Failed';
			}
		}
	}
	catch( Exception $e ) {
		$errors = $e->getMessage();
	}
}

$safe_action = htmlspecialchars( $_SERVER[ 'PHP_SELF' ], ENT_QUOTES, 'UTF-8' );
$safe_message = htmlspecialchars( $message, ENT_QUOTES, 'UTF-8' );
$safe_intercepted_message = htmlspecialchars( $intercepted_message, ENT_QUOTES, 'UTF-8' );

$html = "
		<p>
		This super secure system will allow you to exchange messages with your friends without anyone else being able to read them. Use the box below to encode and decode messages.
		</p>
		<form name=\"xor\" method='post' action=\"{$safe_action}\">
			<p>
				<label for='message'>Message:</label><br />
				<textarea style='width: 600px; height: 56px' id='message' name='message'>{$safe_message}</textarea>
			</p>
			<p>
				<input type='radio' value='encode' name='direction' id='direction_encode' {$encode_radio_selected}><label for='direction_encode'>Encode</label> or
				<input type='radio' value='decode' name='direction' id='direction_decode' {$decode_radio_selected}><label for='direction_decode'>Decode</label>
			</p>
			<p>
				<input type=\"submit\" value=\"Submit\">
			</p>
		</form>
";

if( !is_null( $encoded ) ) {
	$safe_encoded = htmlspecialchars( $encoded, ENT_QUOTES, 'UTF-8' );
	$html .= "
			<p>
				<label for='encoded'>Message:</label><br />
				<textarea readonly='readonly' style='width: 600px; height: 56px' id='encoded' name='encoded'>{$safe_encoded}</textarea>
			</p>";
}

$html .= "
		<hr>
		<p>
		You have intercepted the following message, decode it and log in below.
		</p>
		<p>
		<textarea readonly='readonly' style='width: 600px; height: 28px' id='encoded' name='encoded'>{$safe_intercepted_message}</textarea>
		</p>
";

if( $errors != "" ) {
	$html .= '<div class="warning">' . htmlspecialchars( $errors, ENT_QUOTES, 'UTF-8' ) . '</div>';
}

if( $messages != "" ) {
	$html .= '<div class="nearly">' . htmlspecialchars( $messages, ENT_QUOTES, 'UTF-8' ) . '</div>';
}

if( $success != "" ) {
	$html .= '<div class="success">' . htmlspecialchars( $success, ENT_QUOTES, 'UTF-8' ) . '</div>';
}

$html .= "
		<form name=\"ecb\" method='post' action=\"{$safe_action}\">
			<p>
				<label for='password'>Password:</label><br />
				<input type='password' id='password' name='password'>
			</p>
			<p>
				<input type=\"submit\" value=\"Login\">
			</p>
		</form>
";

?>
