<?php

/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2015 TintSoft
 * @license http://www.tintsoft.com/license/
 */
namespace Leaps\Rbac;

use Leaps\Base\Object;

/**
 * Rule represents a business constraint that may be associated with a role, permission or assignment.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
abstract class Rule extends Object
{
	/**
	 *
	 * @var string name of the rule
	 */
	public $name;
	/**
	 *
	 * @var integer UNIX timestamp representing the rule creation time
	 */
	public $createdAt;
	/**
	 *
	 * @var integer UNIX timestamp representing the rule updating time
	 */
	public $updatedAt;
	
	/**
	 * Executes the rule.
	 *
	 * @param string|integer $user the user ID. This should be either an integer or a string representing
	 *        the unique identifier of a user. See [[\Leaps\Web\User::id]].
	 * @param Item $item the role or permission that this rule is associated with
	 * @param array $params parameters passed to [[ManagerInterface::checkAccess()]].
	 * @return boolean a value indicating whether the rule permits the auth item it is associated with.
	 */
	abstract public function execute($user, $item, $params);
}
