<?php

namespace Src;

use OpenApi\Attributes as OAT;

class OrderController
{
	private $data = array ();
	private $orderId = null;
	private $requestMethod = "GET";
	private $version = null;

	public function __construct($requestMethod, $version, $orderId) {
		// These pre-existing orders belong to fixed, fictional
		// customers who never log in through this API - "owner" values
		// that deliberately never match a real authenticated subject,
		// so no bearer token can read/modify someone else's order
		// (OWASP API1 - BOLA). A caller only ever gets access to
		// orders they create themselves, via addOrder().
		$this->data = array (
			1 => new Order (1, "Tony", "BBC Television Centre, London W3 6XZ", "5 * brushes", 0, "tony"),
			2 => new Order (2, "Morph", "Wooden Box, Corner of the table, The Studio", "plasticine", 0, "morph"),
			3 => new Order (3, "Nailbrush", "BBC Television Centre, London W3 6XZ", "Spare bristles", 1, "nailbrush"),
		);
		$this->requestMethod = $requestMethod;
		$this->orderId = $orderId;
		$this->version = $version;
	}

	// Returns the authenticated subject (e.g. "mrbennett") for a valid
	// bearer token, or false if there is none/it is invalid. This is
	// used both as an authentication gate (false => 401) and as the
	// identity object ownership is checked against (OWASP API1 - BOLA):
	// a valid token only ever proves *who* you are, not that you may
	// see every order in the system.
	private function checkToken() {
		if (array_key_exists ("HTTP_AUTHORIZATION", $_SERVER)) {
			$header = $_SERVER['HTTP_AUTHORIZATION'];
			$bits = explode (" ", $header);
			if (count ($bits) == 2) {
				if (strtolower($bits[0]) == "bearer") {
					return Login::get_access_token_subject($bits[1]);
				}
			}
		}

		return false;
	}

	private function validateAdd($input)
	{
		if (! isset($input['name'])) {
			return false;
		}
		if (! isset($input['address'])) {
			return false;
		}
		if (! isset($input['items'])) {
			return false;
		}
		return true;
	}

	private function validateUpdate($input)
	{
		if (isset($input['name']) || isset($input['address']) || isset ($input['items'])) {
			return true;
		}
		return false;
	}

	/*
	type can be "http", "apiKey", "oauth2", "openIdConnect" 
	* https://zircote.github.io/swagger-php/guide/cookbook.html#referencing-a-security-scheme
	*/

	#[OAT\SecurityScheme(
		name :"authorization",
		securityScheme :"http",
		type :"http",
	)
	]

    #[OAT\Get(
		tags: ["order"],
        path: '/vulnerabilities/api/v2/order/{id}',
        operationId: 'getOrderByID',
		description: 'Get a order by ID.',
		security: [ "basicAuth" ],
        parameters: [
            new OAT\Parameter(name: 'id', in: 'path', required: true, schema: new OAT\Schema(type: 'integer')),
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful operation.',
                content: new OAT\JsonContent (ref: '#/components/schemas/Order'),

            ),
            new OAT\Response(
                response: 404,
                description: 'Order not found.',
            ),
        ]
    )   
    ]  
	
	private function getOrder($id)
	{
		$subject = $this->checkToken();
		if ($subject === false) {
			$response['status_code_header'] = 'HTTP/1.1 401 Unauthorized';
			$response['body'] = json_encode (array ("status" => "Invalid or missing token"));
			return $response;
		}

		// Treat "exists but belongs to someone else" the same as "does
		// not exist" - a 403 here would confirm to an unrelated caller
		// that order IDs they're probing are valid (OWASP API1 - BOLA).
		if (!array_key_exists ($id, $this->data) || $this->data[$id]->owner !== $subject) {
			$gc = new GenericController("notFound");
			$gc->processRequest();
			exit();
		}
		$response['status_code_header'] = 'HTTP/1.1 200 OK';
		$response['body'] = json_encode ($this->data[$id]->toArray($this->version));
		return $response;
	}

    #[OAT\Get(
		tags: ["order"],
        path: '/vulnerabilities/api/v2/order/',
        operationId: 'getOrders',
		description: 'Get all orders.',
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful operation.',
                content: new OAT\JsonContent(
                    type: 'array',
                    items: new OAT\Items(ref: '#/components/schemas/Order')
                )
            ),
        ]
    )   
    ]  

	private function getAllOrders() {
		$subject = $this->checkToken();
		if ($subject === false) {
			$response['status_code_header'] = 'HTTP/1.1 401 Unauthorized';
			$response['body'] = json_encode (array ("status" => "Invalid or missing token"));
			return $response;
		}

		$response['status_code_header'] = 'HTTP/1.1 200 OK';
		$all = array();
		foreach ($this->data as $order) {
			// Only ever return the caller's own orders (OWASP API1 - BOLA).
			if ($order->owner === $subject) {
				$all[] = $order->toArray($this->version);
			}
		}
		$response['body'] = json_encode($all);
		return $response;
	}

    #[OAT\Post(
		tags: ["order"],
        path: '/vulnerabilities/api/v2/order/',
        operationId: 'addOrder',
		description: 'Create a new order.',
        parameters: [
                new OAT\RequestBody (
					description: 'Order data.',
                    content: new OAT\MediaType(
                        mediaType: 'application/json',
                        schema: new OAT\Schema(ref: OrderAdd::class)
                    )
                ),

        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful operation.',
                content: new OAT\JsonContent (ref: '#/components/schemas/Order'),
            ),
            new OAT\Response(
                response: 422,
                description: 'Invalid order object provided',
            ),
        ]
    )   
    ]  

	private function addOrder()
	{
		$subject = $this->checkToken();
		if ($subject === false) {
			$response['status_code_header'] = 'HTTP/1.1 401 Unauthorized';
			$response['body'] = json_encode (array ("status" => "Invalid or missing token"));
			return $response;
		}

		$input = (array) json_decode(file_get_contents('php://input'), TRUE);
		if (! $this->validateAdd($input)) {
			$gc = new GenericController("unprocessable");
			$gc->processRequest();
			exit();
		}
		// The order is owned by whoever authenticated to create it.
		$order = new Order(null, $input['name'], $input['address'], $input['items'], 0, $subject);
		$this->data[] = $order;
		$response['status_code_header'] = 'HTTP/1.1 201 Created';
		$response['body'] = json_encode($order->toArray($this->version));
		return $response;
	}

    #[OAT\Put(
		tags: ["order"],
        path: '/vulnerabilities/api/v2/order/{id}',
        operationId: 'updateOrder',
		description: 'Update an order by ID.',
        parameters: [
            new OAT\Parameter(name: 'id', in: 'path', required: true, schema: new OAT\Schema(type: 'integer')),
                new OAT\RequestBody (
					description: 'New order data.',
                    content: new OAT\MediaType(
                        mediaType: 'application/json',
                        schema: new OAT\Schema(ref: OrderUpdate::class)
                    )
                ),

        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful operation.',
                content: new OAT\JsonContent (ref: '#/components/schemas/Order'),
            ),
            new OAT\Response(
                response: 404,
                description: 'Order not found',
            ),
            new OAT\Response(
                response: 422,
                description: 'Invalid order object provided',
            ),
        ]
    )   
    ]  
	
	private function updateOrder($id)
	{
		$subject = $this->checkToken();
		if ($subject === false) {
			$response['status_code_header'] = 'HTTP/1.1 401 Unauthorized';
			$response['body'] = json_encode (array ("status" => "Invalid or missing token"));
			return $response;
		}

		if (!array_key_exists ($id, $this->data) || $this->data[$id]->owner !== $subject) {
			$gc = new GenericController("notFound");
			$gc->processRequest();
			exit();
		}
		$input = (array) json_decode(file_get_contents('php://input'), TRUE);
		if (! $this->validateUpdate($input)) {
			$gc = new GenericController("unprocessable");
			$gc->processRequest();
			exit();
		}
		if (array_key_exists ("name", $input)) {
			$this->data[$id]->name = $input['name'];
		}
		if (array_key_exists ("address", $input)) {
			$this->data[$id]->address = $input['address'];
		}
		if (array_key_exists ("items", $input)) {
			$this->data[$id]->items = $input['items'];
		}
		$response['status_code_header'] = 'HTTP/1.1 200 OK';
		$response['body'] = json_encode ($this->data[$id]->toArray($this->version));
		return $response;
	}	

    #[OAT\Delete(
		tags: ["order"],
        path: '/vulnerabilities/api/v2/order/{id}',
        operationId: 'deleteOrderById',
		description: 'Delete order by ID.',
        parameters: [
            new OAT\Parameter(name: 'id', in: 'path', required: true, schema: new OAT\Schema(type: 'integer')),
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful operation.',
            ),
            new OAT\Response(
                response: 404,
                description: 'Order not found',
            ),
        ]
    )   
    ]  
	
	private function deleteOrder($id) {
		$subject = $this->checkToken();
		if ($subject === false) {
			$response['status_code_header'] = 'HTTP/1.1 401 Unauthorized';
			$response['body'] = json_encode (array ("status" => "Invalid or missing token"));
			return $response;
		}

		if (!array_key_exists ($id, $this->data) || $this->data[$id]->owner !== $subject) {
			$gc = new GenericController("notFound");
			$gc->processRequest();
			exit();
		}
		unset ($this->data[$id]);
		$response['status_code_header'] = 'HTTP/1.1 200 OK';
		$response['body'] = null;
		return $response;
	}

	public function processRequest() {
		switch ($this->requestMethod) {
			case 'GET':
				if (isset ($this->orderId) && is_numeric ($this->orderId)) {
					$response = $this->getOrder($this->orderId);
				} else {
					$response = $this->getAllOrders();
				};
				break;
			case 'POST':
				$response = $this->addOrder();
				break;
			case 'PUT':
				$response = $this->updateOrder($this->orderId);
				break;
			case 'DELETE':
				$response = $this->deleteOrder($this->orderId);
				break;
			case 'OPTIONS':
				$gc = new GenericController("options");
				$gc->processRequest();
				break;
			default:
				$gc = new GenericController("notSupported");
				$gc->processRequest();
				exit();
				break;
		}
		header($response['status_code_header']);
		if ($response['body']) {
			echo $response['body'];
		}
	}
}
