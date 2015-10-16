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
namespace Leaps\Db\Query\Grammar;

use Leaps\Db\Query;

class SQLite extends Grammar
{

	/**
	 * Compile the ORDER BY clause for a query.
	 *
	 * @param Query $query
	 * @return string
	 */
	protected function orderings(Query $query)
	{
		foreach ( $query->orderings as $ordering ) {
			$sql [] = $this->wrap ( $ordering ['column'] ) . ' COLLATE NOCASE ' . strtoupper ( $ordering ['direction'] );
		}
		return 'ORDER BY ' . implode ( ', ', $sql );
	}

	/**
	 * Compile a SQL INSERT statement from a Query instance.
	 *
	 * This method handles the compilation of single row inserts and batch inserts.
	 *
	 * @param Query $query
	 * @param array $values
	 * @return string
	 */
	public function insert(Query $query, $values)
	{
		$table = $this->wrapTable ( $query->from );
		if (! is_array ( reset ( $values ) )) {
			$values = [$values];
		}
		if (count ( $values ) == 1) {
			return parent::insert ( $query, $values [0] );
		}
		$names = $this->columnize ( array_keys ( $values [0] ) );
		$columns = [];
		foreach ( array_keys ( $values [0] ) as $column ) {
			$columns [] = '? AS ' . $this->wrap ( $column );
		}
		$columns = array_fill ( 9, count ( $values ), implode ( ', ', $columns ) );
		return "INSERT INTO $table ($names) SELECT " . implode ( ' UNION SELECT ', $columns );
	}
}