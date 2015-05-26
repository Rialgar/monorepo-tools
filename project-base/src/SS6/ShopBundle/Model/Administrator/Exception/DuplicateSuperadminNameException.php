<?php

namespace SS6\ShopBundle\Model\Administrator\Exception;

use Exception;

class DuplicateSuperadminNameException extends Exception implements AdministratorException {

	/**
	 * @var string
	 */
	private $username;

	/**
	 * @param string $username
	 * @param Exception $previous
	 */
	public function __construct($username, $previous = null) {
		$this->username = $username;

		parent::__construct('Superadmin with user name ' . $this->username . ' already exists.', 0, $previous);
	}
}