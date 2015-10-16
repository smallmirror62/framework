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

class SQLServer extends Grammar
{

	/**
	 * The keyword identifier for the database system.
	 *
	 * @var string
	 */
	protected $wrapper = '[%s]';

	/**
	 * The format for properly saving a DateTime.
	 *
	 * @var string
	 */
	public $datetime = 'Y-m-d H:i:s.000';

	/**
	 * Compile a SQL SELECT statement from a Query instance.
	 *
	 * @param Query $query
	 * @return string
	 */
	public function select(Query $query)
	{
		$sql = parent::components ( $query );
		if ($query->offset > 0) {
			return $this->ansiOffset ( $query, $sql );
		}
		return $this->concatenate ( $sql );
	}

	/**
	 * Compile the SELECT clause for a query.
	 *
	 * @param Query $query
	 * @return string
	 */
	protected function selects(Query $query)
	{
		if (! is_null ( $query->aggregate ))
			return;
		$select = ($query->distinct) ? 'SELECT DISTINCT ' : 'SELECT ';
		if ($query->limit > 0 and $query->offset <= 0) {
			$select .= 'TOP ' . $query->limit . ' ';
		}
		return $select . $this->columnize ( $query->selects );
	}

	/**
	 * Generate the ANSI standard SQL for an offset clause.
	 *
	 * @param Query $query
	 * @param array $components
	 * @return array
	 */
	protected function ansiOffset(Query $query, $components)
	{
		if (! isset ( $components ['orderings'] )) {
			$components ['orderings'] = 'ORDER BY (SELECT 0)';
		}
		$orderings = $components ['orderings'];
		$components ['selects'] .= ", ROW_NUMBER() OVER ({$orderings}) AS RowNum";
		unset ( $components ['orderings'] );
		$start = $query->offset + 1;
		if ($query->limit > 0) {
			$finish = $query->offset + $query->limit;
			$constraint = "BETWEEN {$start} AND {$finish}";
		} else {
			$constraint = ">= {$start}";
		}
		$sql = $this->concatenate ( $components );
		return "SELECT * FROM ($sql) AS TempTable WHERE RowNum {$constraint}";
	}

	/**
	 * Compile the LIMIT clause for a query.
	 *
	 * @param Query $query
	 * @return string
	 */
	protected function limit(Query $query)
	{
		return '';
	}

	/**
	 * Compile the OFFSET clause for a query.
	 *
	 * @param Query $query
	 * @return string
	 */
	protected function offset(Query $query)
	{
		return '';
	}
}