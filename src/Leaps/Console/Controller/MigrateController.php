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
namespace Leaps\Console\Controller;

use Leaps;
use Leaps\Db\Connection;
use Leaps\Db\Query;
use Leaps\Di\Instance;
use Leaps\Helper\ArrayHelper;
use Leaps\Helper\Console;

/**
 * Manages application migrations.
 *
 * A migration means a set of persistent changes to the application environment
 * that is shared among different developers. For example, in an application
 * backed by a database, a migration may refer to a set of changes to
 * the database, such as creating a new table, adding a new table column.
 *
 * This command provides support for tracking the migration history, upgrading
 * or downloading with migrations, and creating new migration skeletons.
 *
 * The migration history is stored in a database table named
 * as [[migrationTable]]. The table will be automatically created the first time
 * this command is executed, if it does not exist. You may also manually
 * create it as follows:
 *
 * ~~~
 * CREATE TABLE migration (
 * version varchar(180) PRIMARY KEY,
 * apply_time integer
 * )
 * ~~~
 *
 * Below are some common usages of this command:
 *
 * ~~~
 * # creates a new migration named 'create_user_table'
 * leaps migrate/create create_user_table
 *
 * # applies ALL new migrations
 * leaps migrate
 *
 * # reverts the last applied migration
 * leaps migrate/down
 * ~~~
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class MigrateController extends BaseMigrateController
{
	/**
	 *
	 * @var string the name of the table for keeping applied migration information.
	 */
	public $migrationTable = '{{%migration}}';
	/**
	 * @inheritdoc
	 */
	public $templateFile = '@Leaps/View/migration.php';
	/**
	 *
	 * @var Connection|array|string the DB connection object or the application component ID of the DB connection to use
	 *      when applying migrations. Starting from version 2.0.3, this can also be a configuration array
	 *      for creating the object.
	 */
	public $db = 'db';
	
	/**
	 * @inheritdoc
	 */
	public function options($actionID)
	{
		return array_merge ( parent::options ( $actionID ), [ 
			'migrationTable',
			'db' 
		] ); // global for all actions
	}
	
	/**
	 * This method is invoked right before an action is to be executed (after all possible filters.)
	 * It checks the existence of the [[migrationPath]].
	 *
	 * @param \Leaps\Base\Action $action the action to be executed.
	 * @return boolean whether the action should continue to be executed.
	 */
	public function beforeAction($action)
	{
		if (parent::beforeAction ( $action )) {
			if ($action->id !== 'create') {
				$this->db = Instance::ensure ( $this->db, Connection::className () );
			}
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Creates a new migration instance.
	 *
	 * @param string $class the migration class name
	 * @return \Leaps\Db\Migration the migration instance
	 */
	protected function createMigration($class)
	{
		$file = $this->migrationPath . DIRECTORY_SEPARATOR . $class . '.php';
		require_once ($file);
		
		return new $class ( [ 
			'db' => $this->db 
		] );
	}
	
	/**
	 * @inheritdoc
	 */
	protected function getMigrationHistory($limit)
	{
		if ($this->db->schema->getTableSchema ( $this->migrationTable, true ) === null) {
			$this->createMigrationHistoryTable ();
		}
		$query = new Query ();
		$rows = $query->select ( [ 
			'version',
			'apply_time' 
		] )->from ( $this->migrationTable )->orderBy ( 'apply_time DESC, version DESC' )->limit ( $limit )->createCommand ( $this->db )->queryAll ();
		$history = ArrayHelper::map ( $rows, 'version', 'apply_time' );
		unset ( $history [self::BASE_MIGRATION] );
		
		return $history;
	}
	
	/**
	 * Creates the migration history table.
	 */
	protected function createMigrationHistoryTable()
	{
		$tableName = $this->db->schema->getRawTableName ( $this->migrationTable );
		$this->stdout ( "Creating migration history table \"$tableName\"...", Console::FG_YELLOW );
		$this->db->createCommand ()->createTable ( $this->migrationTable, [ 
			'version' => 'varchar(180) NOT NULL PRIMARY KEY',
			'apply_time' => 'integer' 
		] )->execute ();
		$this->db->createCommand ()->insert ( $this->migrationTable, [ 
			'version' => self::BASE_MIGRATION,
			'apply_time' => time () 
		] )->execute ();
		$this->stdout ( "Done.\n", Console::FG_GREEN );
	}
	
	/**
	 * @inheritdoc
	 */
	protected function addMigrationHistory($version)
	{
		$command = $this->db->createCommand ();
		$command->insert ( $this->migrationTable, [ 
			'version' => $version,
			'apply_time' => time () 
		] )->execute ();
	}
	
	/**
	 * @inheritdoc
	 */
	protected function removeMigrationHistory($version)
	{
		$command = $this->db->createCommand ();
		$command->delete ( $this->migrationTable, [ 
			'version' => $version 
		] )->execute ();
	}
}