<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Leaps Software LLC
 * @license http://www.yiiframework.com/license/
 */
namespace leapsunit\src\Web;

use Leaps\Web\Controller;
use leapsunit\src\Web\Stub\Bar;
use Leaps\Validator\EmailValidator;
use leapsunit\src\Di\Stub\QuxInterface;

/**
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 2.0
 */
class FakeController extends Controller
{

	public $enableCsrfValidation = false;

	public function actionAksi1(Bar $bar, $fromGet, $other = 'default')
	{
	}

	public function actionAksi2(Bar $barBelongApp, QuxInterface $qux)
	{
	}

	public function actionAksi3(QuxInterface $quxApp)
	{
	}

	public function actionAksi4(Bar $bar, QuxInterface $quxApp, $q)
	{
		return [
			$bar->foo,
			$quxApp->quxMethod (),
			$q
		];
	}

	public function actionAksi5($q, Bar $bar, QuxInterface $quxApp)
	{
		return [
			$q,
			$bar->foo,
			$quxApp->quxMethod ()
		];
	}

	public function actionAksi6($q, EmailValidator $validator)
	{
		return [
			$q,
			$validator->validate ( $q ),
			$validator->validate ( 'misbahuldmunir@gmail.com' )
		];
	}

	public function actionAksi7($q, \StdClass $validator)
	{
		return [
			$q,
			$validator->test
		];
	}

	public function actionAksi8($q, \StdClass $validator)
	{
		return [
			$q,
			gettype ( $validator )
		];
	}
}
