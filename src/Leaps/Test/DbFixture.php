<?php

/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2015 TintSoft
 * @license http://www.tintsoft.com/license/
 */
namespace Leaps\Test;

use Leaps;
use Leaps\Di\Instance;
use Leaps\Base\Object;
use Leaps\Db\Connection;

/**
 * DbFixture is the base class for DB-related fixtures.
 *
 * DbFixture provides the [[db]] connection to be used by DB fixtures.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class DbFixture extends Fixture
{
	/**
	 *
	 * @var Connection|array|string the DB connection object or the application component ID of the DB connection.
	 *      After the DbFixture object is created, if you want to change this property, you should only assign it
	 *      with a DB connection object.
	 *      Starting from version 2.0.2, this can also be a configuration array for creating the object.
	 */
	public $db = 'db';

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init ();
		$this->db = Instance::ensure ( $this->db, Object::className () );
	}
}
