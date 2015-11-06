<?php
// +----------------------------------------------------------------------
// | Leaps Framework [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2011-2014 Leaps Team (http://www.tintsoft.com)
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author XuTongle <xutongle@gmail.com>
// +----------------------------------------------------------------------
namespace Leaps\Db\Schema\Grammar;

use Leaps\Core\Registry;
use Leaps\Db\Schema\Table;

abstract class Grammar extends \Leaps\Db\Grammar
{

	/**
	 * Generate the SQL statement for creating a foreign key.
	 *
	 * @param Table $table
	 * @param Registry $command
	 * @return string
	 */
	public function foreign(Table $table, Registry $command)
	{
		$name = $command->name;
		$table = $this->wrap ( $table );
		$on = $this->wrapTable ( $command->on );
		$foreign = $this->columnize ( $command->columns );
		$referenced = $this->columnize ( ( array ) $command->references );
		$sql = "ALTER TABLE $table ADD CONSTRAINT $name ";
		$sql .= "FOREIGN KEY ($foreign) REFERENCES $on ($referenced)";
		if (! is_null ( $command->on_delete )) {
			$sql .= " ON DELETE {$command->on_delete}";
		}
		if (! is_null ( $command->on_update )) {
			$sql .= " ON UPDATE {$command->on_update}";
		}
		return $sql;
	}

	/**
	 * Generate the SQL statement for a drop table command.
	 *
	 * @param Table $table
	 * @param Registry $command
	 * @return string
	 */
	public function drop(Table $table, Registry $command)
	{
		return 'DROP TABLE ' . $this->wrap ( $table );
	}

	/**
	 * Drop a constraint from the table.
	 *
	 * @param Table $table
	 * @param Registry $command
	 * @return string
	 */
	protected function drop_constraint(Table $table, Registry $command)
	{
		return "ALTER TABLE " . $this->wrap ( $table ) . " DROP CONSTRAINT " . $command->name;
	}

	/**
	 * Wrap a value in keyword identifiers.
	 *
	 * @param Table|string $value
	 * @return string
	 */
	public function wrap($value)
	{
		if ($value instanceof Table) {
			return $this->wrapTable ( $value->name );
		} elseif ($value instanceof Registry) {
			$value = $value->name;
		}
		return parent::wrap ( $value );
	}

	/**
	 * Get the appropriate data type definition for the column.
	 *
	 * @param Registry $column
	 * @return string
	 */
	protected function type(Registry $column)
	{
		return $this->{'type_' . $column->type} ( $column );
	}

	/**
	 * Format a value so that it can be used in SQL DEFAULT clauses.
	 *
	 * @param mixed $value
	 * @return string
	 */
	protected function default_value($value)
	{
		if (is_bool ( $value )) {
			return intval ( $value );
		}

		return strval ( $value );
	}
}