<?php

namespace Src;

class NetworkTarget
{
	public static function resolvePublicTarget($target, ?callable $lookup = null): ?string
	{
		$target = trim($target);
		if ($target === '') {
			return null;
		}

		if (filter_var($target, FILTER_VALIDATE_IP) !== false) {
			return self::isPublicIp($target) ? $target : null;
		}

		if (filter_var($target, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false) {
			return null;
		}

		$records = $lookup === null
			? dns_get_record($target, DNS_A | DNS_AAAA)
			: $lookup($target);
		if (!is_array($records)) {
			return null;
		}

		foreach ($records as $record) {
			$address = $record['ip'] ?? $record['ipv6'] ?? null;
			if (is_string($address) && self::isPublicIp($address)) {
				return $address;
			}
		}

		return null;
	}

	private static function isPublicIp(string $address): bool
	{
		return filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
	}
}
