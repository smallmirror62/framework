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
namespace Leaps\Db;

use Closure;
use Laravel\Fluent;

class Schema
{

	/**
	 * Begin a fluent schema operation on a database table.
	 *
	 * @param string $table
	 * @param Closure $callback
	 * @return void
	 */
	public static function table($table, $callback)
	{
		call_user_func ( $callback, $table = new Schema\Table ( $table ) );
		return static::execute ( $table );
	}

	/**
	 * Create a new database table schema.
	 *
	 * @param string $table
	 * @param Closure $callback
	 * @return void
	 */
	public static function create($table, $callback)
	{
		$table = new Schema\Table ( $table );
		$table->create ();
		call_user_func ( $callback, $table );
		return static::execute ( $table );
	}

	/**
	 * Rename a database table in the schema.
	 *
	 * @param string $table
	 * @param string $new_name
	 * @return void
	 */
	public static function rename($table, $new_name)
	{
		$table = new Schema\Table ( $table );
		$table->rename ( $new_name );
		return static::execute ( $table );
	}

	/**
	 * Drop a database table from the schema.
	 *
	 * @param string $table
	 * @param string $connection
	 * @return void
	 */
	public static function drop($table, $connection = null)
	{
		$table = new Schema\Table ( $table );
		$table->on ( $connection );
		$table->drop ();
		return static::execute ( $table );
	}

	/**
	 * Execute the given schema operation against the database.
	 *
	 * @param Schema\Table $table
	 * @return void
	 */
	public static function execute($table)
	{
		static::implications ( $table );
		foreach ( $table->commands as $command ) {
			$connection = DB::connection ( $table->connection );
			$grammar = static::grammar ( $connection );
			if (method_exists ( $grammar, $method = $command->type )) {
				$statements = $grammar->$method ( $table, $command );
				foreach ( ( array ) $statements as $statement ) {
					$connection->query ( $statement );
				}
			}
		}
	}

	/**
	 * Add any implicit commands to the schema table operation.
	 *
	 * @param Schema\Table $table
	 * @return void
	 */
	protected static function implications($table)
	{
		if (count ( $table->columns ) > 0 and ! $table->creating ()) {
			$command = new Fluent ( array ('type' => 'add' ) );
			array_unshift ( $table->commands, $command );
		}
		foreach ( $table->columns as $column ) {
			foreach ( [ 'primary','unique','fulltext','index' ] as $key ) {
				if (isset ( $column->$key )) {
					if ($column->$key === true) {
						$table->$key ( $column->name );
					} else {
						$table->$key ( $column->name, $column->$key );
					}
				}
			}
		}
	}

	/**
	 * Create the appropriate schema grammar for the driver.
	 *
	 * @param Connection $connection
	 * @return Grammar
	 */
	public static function grammar(Connection $connection)
	{
		$driver = $connection->driver ();
		if (isset ( \Leaps\Db\Db::$registrar [$driver] )) {
			return new \Leaps\Db\Db::$registrar [$driver] ['schema'] ();
		}
		switch ($driver) {
			case 'mysql' :
				return new \Leaps\Db\Schema\Grammar\MySQL ( $connection );

			case 'pgsql' :
				return new \Leaps\Db\Schema\Grammar\Postgres ( $connection );

			case 'sqlsrv' :
				return new \Leaps\Db\Schema\Grammar\SQLServer ( $connection );

			case 'sqlite' :
				return new \Leaps\Db\Schema\Grammar\SQLite ( $connection );
		}

		throw new \Leaps\Db\Exception ( "Schema operations not supported for [$driver]." );
	}
}
