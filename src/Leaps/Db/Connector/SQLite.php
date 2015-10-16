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
namespace Leaps\Db\Connector;

use PDO;
use Leaps\Kernel;

class SQLite extends Connector
{

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \Leaps\Db\Connector\Connector::connect()
	 */
	public function connect($config)
	{
		$options = $this->options ( $config );
		if ($config ['database'] == ':memory:') {
			return new PDO ( 'sqlite::memory:', null, null, $options );
		}

		$path = Kernel::getAlias ( '@Storage' ) . '/database' . DIRECTORY_SEPARATOR . $config ['database'] . '.sqlite';

		return new PDO ( 'sqlite:' . $path, null, null, $options );
	}
}
