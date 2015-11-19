<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
namespace Leaps\Db\Oci;

use Leaps\Db\ColumnSchemaBuilder as AbstractColumnSchemaBuilder;

/**
 * ColumnSchemaBuilder is the schema builder for Oracle databases.
 *
 * @author Vasenin Matvey <vaseninm@gmail.com>
 * @since 2.0.6
 */
class ColumnSchemaBuilder extends AbstractColumnSchemaBuilder
{
	/**
	 * @inheritdoc
	 */
	public function __toString()
	{
		return $this->type . $this->buildLengthString () . $this->buildDefaultString () . $this->buildNotNullString () . $this->buildCheckString ();
	}
}
