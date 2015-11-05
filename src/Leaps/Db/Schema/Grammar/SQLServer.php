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

use Leaps\Core\Fluent;
use Leaps\Db\Schema\Table;

class SQLServer extends Grammar
{

	/**
	 * The keyword identifier for the database system.
	 *
	 * @var string
	 */
	public $wrapper = '[%s]';

	/**
	 * Generate the SQL statements for a table creation command.
	 *
	 * @param Table $table
	 * @param Fluent $command
	 * @return array
	 */
	public function create(Table $table, Fluent $command)
	{
		$columns = implode ( ', ', $this->columns ( $table ) );
		$sql = 'CREATE TABLE ' . $this->wrap ( $table ) . ' (' . $columns . ')';
		return $sql;
	}

	/**
	 * Generate the SQL statements for a table modification command.
	 *
	 * @param Table $table
	 * @param Fluent $command
	 * @return array
	 */
	public function add(Table $table, Fluent $command)
	{
		$columns = $this->columns ( $table );
		$columns = implode ( ', ', array_map ( function ($column)
		{
			return 'ADD ' . $column;
		}, $columns ) );

		return 'ALTER TABLE ' . $this->wrap ( $table ) . ' ' . $columns;
	}

	/**
	 * Create the individual column definitions for the table.
	 *
	 * @param Table $table
	 * @return array
	 */
	protected function columns(Table $table)
	{
		$columns = [];
		foreach ( $table->columns as $column ) {
			$sql = $this->wrap ( $column ) . ' ' . $this->type ( $column );
			$elements = ['incrementer','nullable','defaults'];
			foreach ( $elements as $element ) {
				$sql .= $this->$element ( $table, $column );
			}
			$columns [] = $sql;
		}
		return $columns;
	}

	/**
	 * Get the SQL syntax for indicating if a column is nullable.
	 *
	 * @param Table $table
	 * @param Fluent $column
	 * @return string
	 */
	protected function nullable(Table $table, Fluent $column)
	{
		return ($column->nullable) ? ' NULL' : ' NOT NULL';
	}

	/**
	 * Get the SQL syntax for specifying a default value on a column.
	 *
	 * @param Table $table
	 * @param Fluent $column
	 * @return string
	 */
	protected function defaults(Table $table, Fluent $column)
	{
		if (! is_null ( $column->default )) {
			return " DEFAULT '" . $this->defaultValue ( $column->default ) . "'";
		}
	}

	/**
	 * Get the SQL syntax for defining an auto-incrementing column.
	 *
	 * @param Table $table
	 * @param Fluent $column
	 * @return string
	 */
	protected function incrementer(Table $table, Fluent $column)
	{
		if ($column->type == 'integer' and $column->increment) {
			return ' IDENTITY PRIMARY KEY';
		}
	}

	/**
	 * Generate the SQL statement for creating a primary key.
	 *
	 * @param Table $table
	 * @param Fluent $command
	 * @return string
	 */
	public function primary(Table $table, Fluent $command)
	{
		$name = $command->name;

		$columns = $this->columnize ( $command->columns );

		return 'ALTER TABLE ' . $this->wrap ( $table ) . " ADD CONSTRAINT {$name} PRIMARY KEY ({$columns})";
	}

	/**
	 * Generate the SQL statement for creating a unique index.
	 *
	 * @param Table $table
	 * @param Fluent $command
	 * @return string
	 */
	public function unique(Table $table, Fluent $command)
	{
		return $this->key ( $table, $command, true );
	}

	/**
	 * Generate the SQL statement for creating a full-text index.
	 *
	 * @param Table $table
	 * @param Fluent $command
	 * @return string
	 */
	public function fulltext(Table $table, Fluent $command)
	{
		$columns = $this->columnize ( $command->columns );
		$table = $this->wrap ( $table );
		$sql [] = "CREATE FULLTEXT CATALOG {$command->catalog}";
		$create = "CREATE FULLTEXT INDEX ON " . $table . " ({$columns}) ";
		$sql [] = $create .= "KEY INDEX {$command->key} ON {$command->catalog}";
		return $sql;
	}

	/**
	 * Generate the SQL statement for creating a regular index.
	 *
	 * @param Table $table
	 * @param Fluent $command
	 * @return string
	 */
	public function index(Table $table, Fluent $command)
	{
		return $this->key ( $table, $command );
	}

	/**
	 * Generate the SQL statement for creating a new index.
	 *
	 * @param Table $table
	 * @param Fluent $command
	 * @param bool $unique
	 * @return string
	 */
	protected function key(Table $table, Fluent $command, $unique = false)
	{
		$columns = $this->columnize ( $command->columns );
		$create = ($unique) ? 'CREATE UNIQUE' : 'CREATE';
		return $create . " INDEX {$command->name} ON " . $this->wrap ( $table ) . " ({$columns})";
	}

	/**
	 * Generate the SQL statement for a rename table command.
	 *
	 * @param Table $table
	 * @param Fluent $command
	 * @return string
	 */
	public function rename(Table $table, Fluent $command)
	{
		return 'ALTER TABLE ' . $this->wrap ( $table ) . ' RENAME TO ' . $this->wrap ( $command->name );
	}

	/**
	 * Generate the SQL statement for a drop column command.
	 *
	 * @param Table $table
	 * @param Fluent $command
	 * @return string
	 */
	public function dropColumn(Table $table, Fluent $command)
	{
		$columns = array_map ( array ($this,'wrap' ), $command->columns );
		$columns = implode ( ', ', array_map ( function ($column)
		{
			return 'DROP ' . $column;
		}, $columns ) );

		return 'ALTER TABLE ' . $this->wrap ( $table ) . ' ' . $columns;
	}

	/**
	 * Generate the SQL statement for a drop primary key command.
	 *
	 * @param Table $table
	 * @param Fluent $command
	 * @return string
	 */
	public function dropPrimary(Table $table, Fluent $command)
	{
		return 'ALTER TABLE ' . $this->wrap ( $table ) . ' DROP CONSTRAINT ' . $command->name;
	}

	/**
	 * Generate the SQL statement for a drop unique key command.
	 *
	 * @param Table $table
	 * @param Fluent $command
	 * @return string
	 */
	public function dropUnique(Table $table, Fluent $command)
	{
		return $this->dropKey ( $table, $command );
	}

	/**
	 * Generate the SQL statement for a drop full-text key command.
	 *
	 * @param Table $table
	 * @param Fluent $command
	 * @return string
	 */
	public function dropFulltext(Table $table, Fluent $command)
	{
		$sql [] = "DROP FULLTEXT INDEX " . $command->name;

		$sql [] = "DROP FULLTEXT CATALOG " . $command->catalog;

		return $sql;
	}

	/**
	 * Generate the SQL statement for a drop index command.
	 *
	 * @param Table $table
	 * @param Fluent $command
	 * @return string
	 */
	public function dropIndex(Table $table, Fluent $command)
	{
		return $this->dropKey ( $table, $command );
	}

	/**
	 * Generate the SQL statement for a drop key command.
	 *
	 * @param Table $table
	 * @param Fluent $command
	 * @return string
	 */
	protected function dropKey(Table $table, Fluent $command)
	{
		return "DROP INDEX {$command->name} ON " . $this->wrap ( $table );
	}

	/**
	 * Drop a foreign key constraint from the table.
	 *
	 * @param Table $table
	 * @param Fluent $command
	 * @return string
	 */
	public function dropForeign(Table $table, Fluent $command)
	{
		return $this->dropConstraint ( $table, $command );
	}

	/**
	 * Generate the data-type definition for a string.
	 *
	 * @param Fluent $column
	 * @return string
	 */
	protected function typeString(Fluent $column)
	{
		return 'NVARCHAR(' . $column->length . ')';
	}

	/**
	 * Generate the data-type definition for an integer.
	 *
	 * @param Fluent $column
	 * @return string
	 */
	protected function typeInteger(Fluent $column)
	{
		return 'INT';
	}

	/**
	 * Generate the data-type definition for an integer.
	 *
	 * @param Fluent $column
	 * @return string
	 */
	protected function typeFloat(Fluent $column)
	{
		return 'FLOAT';
	}

	/**
	 * Generate the data-type definition for a decimal.
	 *
	 * @param Fluent $column
	 * @return string
	 */
	protected function typeDecimal(Fluent $column)
	{
		return "DECIMAL({$column->precision}, {$column->scale})";
	}

	/**
	 * Generate the data-type definition for a boolean.
	 *
	 * @param Fluent $column
	 * @return string
	 */
	protected function typeBoolean(Fluent $column)
	{
		return 'TINYINT';
	}

	/**
	 * Generate the data-type definition for a date.
	 *
	 * @param Fluent $column
	 * @return string
	 */
	protected function typeDate(Fluent $column)
	{
		return 'DATETIME';
	}

	/**
	 * Generate the data-type definition for a timestamp.
	 *
	 * @param Fluent $column
	 * @return string
	 */
	protected function typeTimestamp(Fluent $column)
	{
		return 'TIMESTAMP';
	}

	/**
	 * Generate the data-type definition for a text column.
	 *
	 * @param Fluent $column
	 * @return string
	 */
	protected function typeText(Fluent $column)
	{
		return 'NVARCHAR(MAX)';
	}

	/**
	 * Generate the data-type definition for a blob.
	 *
	 * @param Fluent $column
	 * @return string
	 */
	protected function typeBlob(Fluent $column)
	{
		return 'VARBINARY(MAX)';
	}
}