<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2015 TintSoft
 * @license http://www.tintsoft.com/license/
 */

namespace Leaps\Rbac;

use Leaps;
use Leaps\Base\Object;

/**
 * Assignment represents an assignment of a role to a user.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class Assignment extends Object
{
    /**
     * @var string|integer user ID (see [[\Leaps\Web\User::id]])
     */
    public $userId;
    /**
     * @return string the role name
     */
    public $roleName;
    /**
     * @var integer UNIX timestamp representing the assignment creation time
     */
    public $createdAt;
}
