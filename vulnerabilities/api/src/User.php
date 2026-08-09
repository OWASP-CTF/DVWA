<?php declare(strict_types=1);

namespace Src;

use OpenApi\Attributes as OAT;

/*
#[OA\Schema()]
enum UserLevel {
    case ADMIN;
    case USER;
}
*/
#[OAT\Schema()]
final class User
{
    #[OAT\Property(type: 'integer', example: 1)]
    public int $id;

    #[OAT\Property(type: "string", example: "fred")]
    public string $name;

    #[OAT\Property(type: 'integer', example: 1)]
    public int $level;

	public string $password;

	function __construct ($id, $name, $level, $password) {
		if (is_null ($id)) {
			$id = mt_rand(50,100);
		}
		$this->id = $id;
		$this->name = $name;
		$this->level = $level;
		$this->password = $password;
	}

	public function toArray($version) {
		// The v1 route used to include the password hash in every response - an old, forgotten
		// API version leaking credential hashes with no additional access control. There is no
		// legitimate reason any API response needs to echo a user's password hash back, so it's
		// omitted for every version now, not just v2+.
		return array (
			"id" => $this->id,
			"name" => $this->name,
			"level" => $this->level,
		);
	}
}

#[OAT\Schema(required: ['level', 'name'])]
final class UserAdd
{
    #[OAT\Property(example: "fred")]
    public string $name;

    #[OAT\Property(type: 'integer', example: 1)]
    public string $level;
}

#[OAT\Schema(required: ['name'])]
final class UserUpdate
{
    #[OAT\Property(example: "fred")]
    public string $name;
}
