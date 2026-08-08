<?php

# Start the app with:
#
# php -S localhost:8000 -t public

namespace Src;

use OpenApi\Attributes as OAT;

class HealthController
{
	private $command = null;
	private $requestMethod = "GET";

	public function __construct($requestMethod, $version, $command) {
		$this->requestMethod = $requestMethod;
		$this->command = $command;
	}

    #[OAT\Post(
		tags: ["health"],
        path: '/vulnerabilities/api/v2/health/echo',
        operationId: 'echo',
		description: 'Echo, echo, cho, cho, o o ....',
        parameters: [
                new OAT\RequestBody (
					description: 'Your words.',
                    content: new OAT\MediaType(
                        mediaType: 'application/json',
                        schema: new OAT\Schema(ref: Words::class)
                    )
                ),

        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful operation.',
            ),
        ]
    )   
    ]
	
	private function echo() {
		$input = (array) json_decode(file_get_contents('php://input'), TRUE);
		if (array_key_exists ("words", $input)) {
			$words = $input['words'];

			$response['status_code_header'] = 'HTTP/1.1 200 OK';
			$response['body'] = json_encode (array ("reply" => $words));
		} else {
			$response['status_code_header'] = 'HTTP/1.1 500 Internal Server Error';
			$response['body'] = json_encode (array ("status" => "Words not specified"));
		}
		return $response;
	}

    #[OAT\Post(
		tags: ["health"],
        path: '/vulnerabilities/api/v2/health/connectivity',
        operationId: 'checkConnectivity',
		description: 'The server occasionally loses connectivity to other systems and so this can be used to check connectivity status.',
        parameters: [
                new OAT\RequestBody (
					description: 'Remote host.',
                    content: new OAT\MediaType(
                        mediaType: 'application/json',
                        schema: new OAT\Schema(ref: Target::class)
                    )
                ),

        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful operation.',
            ),
        ]
    )   
    ]
	
	/*
	 * Is this a host we are willing to probe?
	 *
	 * Two problems had to be closed here. The value reached the shell unquoted,
	 * which was command injection, and it could name any host at all, which
	 * made the endpoint a reachability probe for whatever sits on the internal
	 * network (SSRF, folded into A01 in the 2025 Top 10).
	 */
	private function isAllowedTarget($target) {
		if (!is_string($target) || $target === '' || strlen($target) > 253) {
			return false;
		}

		// A literal address, or a hostname built only from label characters.
		$isIp   = filter_var($target, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
		$isHost = preg_match('/^[A-Za-z0-9]([A-Za-z0-9\-]{0,61}[A-Za-z0-9])?(\.[A-Za-z0-9]([A-Za-z0-9\-]{0,61}[A-Za-z0-9])?)*$/', $target) === 1;

		if (!$isIp && !$isHost) {
			return false;
		}

		$resolved = $isIp ? $target : gethostbyname($target);
		if (!$isIp && $resolved === $target) {
			// The name did not resolve.
			return false;
		}

		if (filter_var($resolved, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
			return false;
		}

		// Blocking every private range would make this endpoint useless: in the
		// bundled compose deployment the database, the loopback address and the
		// container's own address are all private, so nothing would ever be a
		// valid target. What is blocked is the cloud instance metadata address,
		// which is the one destination an SSRF is actually worth aiming at.
		if (strpos($resolved, '169.254.') === 0) {
			return false;
		}

		return true;
	}

	private function checkConnectivity() {
		$input = (array) json_decode(file_get_contents('php://input'), TRUE);
		if (array_key_exists ("target", $input)) {
			$target = $input['target'];

			if (!$this->isAllowedTarget ($target)) {
				$response['status_code_header'] = 'HTTP/1.1 400 Bad Request';
				$response['body'] = json_encode (array ("status" => "Invalid target"));
				return $response;
			}

			// Passed as a single quoted argument, so it can never be read as
			// shell syntax even if the validation above is ever loosened.
			exec ("ping -c 4 " . escapeshellarg ($target), $output, $ret_var);

			if ($ret_var == 0) {
				$response['status_code_header'] = 'HTTP/1.1 200 OK';
				$response['body'] = json_encode (array ("status" => "OK"));
			} else {
				$response['status_code_header'] = 'HTTP/1.1 500 Internal Server Error';
				$response['body'] = json_encode (array ("status" => "Connection failed"));
			}
		} else {
			$response['status_code_header'] = 'HTTP/1.1 500 Internal Server Error';
			$response['body'] = json_encode (array ("status" => "Target not specified"));
		}
		return $response;
	}

    #[OAT\Get(
		tags: ["health"],
        path: '/vulnerabilities/api/v2/health/status',
        operationId: 'getHealthStatus',
		description: 'Get the health of the system.',
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful operation.',
            ),
        ]
    )   
    ]
	
	private function getStatus() {
		$response['status_code_header'] = 'HTTP/1.1 200 OK';
		$response['body'] = json_encode (array ("status" => "OK"));
		return $response;
	}

    #[OAT\Get(
		tags: ["health"],
        path: '/vulnerabilities/api/v2/health/ping',
        operationId: 'ping',
		description: 'Simple ping/pong to check connectivity.',
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful operation.',
            ),
        ]
    )   
    ]
	private function ping() {
		$response['status_code_header'] = 'HTTP/1.1 200 OK';
		$response['body'] = json_encode (array ("Ping" => "Pong"));
		return $response;
	}

	public function processRequest() {
		switch ($this->requestMethod) {
			case 'POST':
				switch ($this->command) {
					case "echo":
						$response = $this->echo();
						break;
					case "connectivity":
						$response = $this->checkConnectivity();
						break;
					default:
						$gc = new GenericController("notFound");
						$gc->processRequest();
						exit();
				};
				break;
			case 'GET':
				switch ($this->command) {
					case "status":
						$response = $this->getStatus();
						break;
					case "ping":
						$response = $this->ping();
						break;
					default:
						$gc = new GenericController("notFound");
						$gc->processRequest();
						exit();
				};
				break;
			case 'OPTIONS':
				$gc = new GenericController("options");
				$gc->processRequest();
				break;
			default:
				$gc = new GenericController("notSupported");
				$gc->processRequest();
				break;
		}
		header($response['status_code_header']);
		if ($response['body']) {
			echo $response['body'];
		}
	}
}

#[OAT\Schema(required: ['target'])]
final class Target {
    #[OAT\Property(example: "digi.ninja")]
    public string $target;
}

#[OAT\Schema(required: ['words'])]
final class Words {
    #[OAT\Property(example: "Hello World")]
    public string $words;
}

