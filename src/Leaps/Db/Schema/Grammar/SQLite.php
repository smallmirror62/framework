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
use Leaps\Helper\ArrayHelper;

class SQLite extends Grammar
{

	/**
	 * Generate the SQL statements for a table creation command.
	 *
	 * @param Table $table
	 * @param Registry $command
	 * @return array
	 */
	public function create(Table $table, Registry $command)
	{
		$columns = implode ( ', ', $this->columns ( $table ) );
		$sql = 'CREATE TABLE ' . $this->wrap ( $table ) . ' (' . $columns;
		$primary = ArrayHelper::arrayFirst ( $table->commands, function ($key, $value)
		{
			return $value->type == 'primary';
		} );
		if (! is_null ( $primary )) {
			$columns = $this->columnize ( $primary->columns );

			$sql .= ", PRIMARY KEY ({$columns})";
		}

		return $sql .= ')';
	}

	/**
	 * Generate the SQL statements for a table modification command.
	 *
	 * @param Table $table
	 * @param Registry $command
	 * @return array
	 */
	public function add(Table $table, Registry $command)
	{
		$columns = $this->columns ( $table );

		// Once we have the array of column definitions, we need to add "add" to the
		// front of each definition, then we'll concatenate the definitions
		// using commas like normal and generate the SQL.
		$columns = array_map ( function ($column)
		{
			return 'ADD COLUMN ' . $column;
		}, $columns );

		// SQLite only allows one column to be added in an ALTER statement,
		// so we will create an array of statements and return them all to
		// the schema manager for separate execution.
		foreach ( $columns as $column ) {
			$sql [] = 'ALTER TABLE ' . $this->wrap ( $table ) . ' ' . $column;
		}

		return ( array ) $sql;
	}

	/**
	 * Create the individual column definitions for the table.
	 *
	 * @param Table $table
	 * @return array
	 */
	protected function columns(Table $table)
	{
		$columns = array ();

		foreach ( $table->columns as $column ) {
			// Each of the data type's have their own definition creation method
			// which is responsible for creating the SQL for the type. This lets
			// us keep the syntax easy and Registry, while translating the
			// types to the types used by the database.
			$sql = $this->wrap ( $column ) . ' ' . $this->type ( $column );

			$elements = array ('nullable','defaults','incrementer' );

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
	 * @param Registry $column
	 * @return string
	 */
	protected function nullable(Table $table, Registry $column)
	{
		return ' NULL';
	}

	/**
	 * Get the SQL syntax for specifying a default value on a column.
	 *
	 * @param Table $table
	 * @param Registry $column
	 * @return string
	 */
	protected function defaults(Table $table, Registry $column)
	{
		if (! is_null ( $column->default )) {
			return ' DEFAULT ' . $this->wrap ( $this->default_value ( $column->default ) );
		}
	}

	/**
	 * Get the SQL syntax for defining an auto-incrementing column.
	 *
	 * @param Table $table
	 * @param Registry $column
	 * @return string
	 */
	protected function incrementer(Table $table, Registry $column)
	{
		if ($column->type == 'integer' and $column->increment) {
			return ' PRIMARY KEY AUTOINCREMENT';
		}
	}

	/**
	 * Generate the SQL statement for creating a unique index.
	 *
	 * @param Table $table
	 * @param Registry $command
	 * @return string
	 */
	public function unique(Table $table, Registry $command)
	{
		return $this->key ( $table, $command, true );
	}

	/**
	 * Generate the SQL statement for creating a full-text index.
	 *
	 * @param Table $table
	 * @param Registry $command
	 * @return string
	 */
	public function fulltext(Table $table, Registry $command)
	{
		$columns = $this->columnize ( $command->columns );

		return 'CREATE VIRTUAL TABLE ' . $this->wrap ( $table ) . " USING fts4({$columns})";
	}

	/**
	 * Generate the SQL statement for creating a regular index.
	 *
	 * @param Table $table
	 * @param Registry $command
	 * @return string
	 */
	public function index(Table $table, Registry $command)
	{
		return $this->key ( $table, $command );
	}

	/**
	 * Generate the SQL statement for creating a new index.
	 *
	 * @param Table $table
	 * @param Registry $command
	 * @param bool $unique
	 * @return string
	 */
	protected function key(Table $table, Registry $command, $unique = false)
	{
		$columns = $this->columnize ( $command->columns );
		$create = ($unique) ? 'CREATE UNIQUE' : 'CREATE';
		return $create . " INDEX {$command->name} ON " . $this->wrap ( $table ) . " ({$columns})";
	}

	/**
	 * Generate the SQL statement for a rename table command.
	 *
	 * @param Table $table
	 * @param Registry $command
	 * @return string
	 */
	public function rename(Table $table, Registry $command)
	{
		return 'ALTER TABLE ' . $this->wrap ( $table ) . ' RENAME TO ' . $this->wrap ( $command->name );
	}

	/**
	 * Generate the SQL statement for a drop unique key command.
	 *
	 * @param Table $table
	 * @param Registry $command
	 * @return string
	 */
	public function dropUnique(Table $table, Registry $command)
	{
		return $this->dropKey ( $table, $command );
	}

	/**
	 * Generate the SQL statement for a drop unique key command.
	 *
	 * @param Table $table
	 * @param Registry $command
	 * @return string
	 */
	public function dropIndex(Table $table, Registry $command)
	{
		return $this->dropKey ( $table, $command );
	}

	/**
	 * Generate the SQL statement for a drop key command.
	 *
	 * @param Table $table
	 * @param Registry $command
	 * @return string
	 */
	protected function dropKey(Table $table, Registry $command)
	{
		return 'DROP INDEX ' . $this->wrap ( $command->name );
	}

	/**
	 * Generate the data-type definition for a string.
	 *
	 * @param Registry $column
	 * @return string
	 */
	protected function typeString(Registry $column)
	{
		return 'VARCHAR';
	}

	/**
	 * Generate the data-type definition for an integer.
	 *
	 * @param Registry $column
	 * @return string
	 */
	protected function typeInteger(Registry $column)
	{
		return 'INTEGER';
	}

	/**
	 * Generate the data-type definition for an integer.
	 *
	 * @param Registry $column
	 * @return string
	 */
	protected function typeFloat(Registry $column)
	{
		return 'FLOAT';
	}

	/**
	 * Generate the data-type definition for a decimal.
	 *
	 * @param Registry $column
	 * @return string
	 */
	protected function typeDecimal(Registry $column)
	{
		return 'FLOAT';
	}

	/**
	 * Generate the data-type definition for a boolean.
	 *
	 * @param Registry $column
	 * @return string
	 */
	protected function typeBoolean(Registry $column)
	{
		return 'INTEGER';
	}

	/**
	 * Generate the data-type definition for a date.
	 *
	 * @param Registry $column
	 * @return string
	 */
	protected function typeDate(Registry $column)
	{
		return 'DATETIME';
	}

	/**
	 * Generate the data-type definition for a timestamp.
	 *
	 * @param Registry $column
	 * @return string
	 */
	protected function typeTimestamp(Registry $column)
	{
		return 'DATETIME';
	}

	/**
	 * Generate the data-type definition for a text column.
	 *
	 * @param Registry $column
	 * @return string
	 */
	protected function typeText(Registry $column)
	{
		return 'TEXT';
	}

	/**
	 * Generate the data-type definition for a blob.
	 *
	 * @param Registry $column
	 * @return string
	 */
	protected function typeBlob(Registry $column)
	{
		return 'BLOB';
	}
}