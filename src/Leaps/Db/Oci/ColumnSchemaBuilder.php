<?php

/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2015 TintSoft
 * @license http://www.tintsoft.com/license/
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
