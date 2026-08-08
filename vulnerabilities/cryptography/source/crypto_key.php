<?php

/**
 * Per-install random key management for the Cryptography module.
 *
 * There is no key-management service available in this environment, so
 * each named key is generated once with a CSPRNG the first time it is
 * needed and then persisted so every subsequent request (and every
 * level that shares a name) keeps using the same key for the lifetime
 * of this install - mirroring how impossible.php's token library uses
 * a single constant KEY throughout, just generated instead of literal.
 *
 * The key material is written outside the web root (the system temp
 * directory) rather than under vulnerabilities/cryptography/source/,
 * because everything under that directory - including dotfiles - is
 * served verbatim over HTTP by this app's Apache config.
 */

function dvwa_crypto_get_key(string $name, int $length = 32): string {
	static $cache = array();

	if (isset($cache[$name])) {
		return $cache[$name];
	}

	$safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '', $name);
	$dir = rtrim(sys_get_temp_dir(), '/') . '/dvwa_cryptography_keys';
	$path = $dir . '/' . $safeName . '.key';

	if (!is_dir($dir)) {
		@mkdir($dir, 0700, true);
	}

	$key = null;

	$fp = @fopen($path, 'c+b');
	if ($fp !== false) {
		if (flock($fp, LOCK_EX)) {
			$existing = stream_get_contents($fp);
			if ($existing !== false && strlen($existing) === $length) {
				$key = $existing;
			} else {
				$key = random_bytes($length);
				ftruncate($fp, 0);
				rewind($fp);
				fwrite($fp, $key);
				fflush($fp);
			}
			flock($fp, LOCK_UN);
		}
		fclose($fp);
		@chmod($path, 0600);
	}

	if ($key === null) {
		// Filesystem is not writable for some reason; fall back to a
		// process-lifetime key so the page still works rather than
		// dying, or worse, using a predictable value.
		$key = random_bytes($length);
	}

	$cache[$name] = $key;
	return $key;
}
