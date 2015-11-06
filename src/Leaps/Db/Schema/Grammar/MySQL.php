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

class MySQL extends Grammar
{

	/**
	 * The keyword identifier for the database system.
	 *
	 * @var string
	 */
	public $wrapper = '`%s`';

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
		$sql = 'CREATE TABLE ' . $this->wrap ( $table ) . ' (' . $columns . ')';
		if (! is_null ( $table->engine )) {
			$sql .= ' ENGINE = ' . $table->engine;
		}
		return $sql;
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
			$elements = ['unsigned','nullable','defaults','incrementer' ];
			foreach ( $elements as $element ) {
				$sql .= $this->$element ( $table, $column );
			}
			$columns [] = $sql;
		}
		return $columns;
	}

	/**
	 * Get the SQL syntax for indicating if a column is unsigned.
	 *
	 * @param Table $table
	 * @param Registry $column
	 * @return string
	 */
	protected function unsigned(Table $table, Registry $column)
	{
		if ($column->type == 'integer' && ($column->unsigned || $column->increment)) {
			return ' UNSIGNED';
		}
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
		return ($column->nullable) ? ' NULL' : ' NOT NULL';
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
			return " DEFAULT '" . $this->default_value ( $column->default ) . "'";
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
			return ' AUTO_INCREMENT PRIMARY KEY';
		}
	}

	/**
	 * Generate the SQL statement for creating a primary key.
	 *
	 * @param Table $table
	 * @param Registry $command
	 * @return string
	 */
	public function primary(Table $table, Registry $command)
	{
		return $this->key ( $table, $command->name ( null ), 'PRIMARY KEY' );
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
		return $this->key ( $table, $command, 'UNIQUE' );
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
		return $this->key ( $table, $command, 'FULLTEXT' );
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
		return $this->key ( $table, $command, 'INDEX' );
	}

	/**
	 * Generate the SQL statement for creating a new index.
	 *
	 * @param Table $table
	 * @param Registry $command
	 * @param string $type
	 * @return string
	 */
	protected function key(Table $table, Registry $command, $type)
	{
		$keys = $this->columnize ( $command->columns );
		$name = $command->name;
		return 'ALTER TABLE ' . $this->wrap ( $table ) . " ADD {$type} {$name}({$keys})";
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
		return 'RENAME TABLE ' . $this->wrap ( $table ) . ' TO ' . $this->wrap ( $command->name );
	}

	/**
	 * Generate the SQL statement for a drop column command.
	 *
	 * @param Table $table
	 * @param Registry $command
	 * @return string
	 */
	public function dropColumn(Table $table, Registry $command)
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
	 * @param Registry $command
	 * @return string
	 */
	public function dropPrimary(Table $table, Registry $command)
	{
		return 'ALTER TABLE ' . $this->wrap ( $table ) . ' DROP PRIMARY KEY';
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
	 * Generate the SQL statement for a drop full-text key command.
	 *
	 * @param Table $table
	 * @param Registry $command
	 * @return string
	 */
	public function dropFulltext(Table $table, Registry $command)
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
		return 'ALTER TABLE ' . $this->wrap ( $table ) . " DROP INDEX {$command->name}";
	}

	/**
	 * Drop a foreign key constraint from the table.
	 *
	 * @param Table $table
	 * @param Registry $command
	 * @return string
	 */
	public function dropForeign(Table $table, Registry $command)
	{
		return "ALTER TABLE " . $this->wrap ( $table ) . " DROP FOREIGN KEY " . $command->name;
	}

	/**
	 * Generate the data-type definition for a string.
	 *
	 * @param Registry $column
	 * @return string
	 */
	protected function typeString(Registry $column)
	{
		return 'VARCHAR(' . $column->length . ')';
	}

	/**
	 * Generate the data-type definition for an integer.
	 *
	 * @param Registry $column
	 * @return string
	 */
	protected function typeInteger(Registry $column)
	{
		return 'INT';
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
		return "DECIMAL({$column->precision}, {$column->scale})";
	}

	/**
	 * Generate the data-type definition for a boolean.
	 *
	 * @param Registry $column
	 * @return string
	 */
	protected function typeBoolean(Registry $column)
	{
		return 'TINYINT(1)';
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
		return 'TIMESTAMP';
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
