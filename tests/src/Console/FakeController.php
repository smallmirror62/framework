<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Leaps Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace leapsunit\src\Console;

use Leaps\Console\Controller;
use leapsunit\src\Web\Stub\Bar;
use Leaps\Validator\EmailValidator;
use leapsunit\src\di\stubs\QuxInterface;


/**
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 2.0
 */
class FakeController extends Controller
{

    public function actionAksi1(Bar $bar, $fromParam, $other = 'default')
    {
        return[$bar, $fromParam, $other];
    }

    public function actionAksi2(Bar $barBelongApp, QuxInterface $qux)
    {
        return[$barBelongApp, $qux];
    }

    public function actionAksi3(QuxInterface $quxApp)
    {
        return[$quxApp];
    }

    public function actionAksi4(Bar $bar, QuxInterface $quxApp, array $values, $value)
    {
        return [$bar->foo, $quxApp->quxMethod(), $values, $value];
    }

    public function actionAksi5($q, Bar $bar, QuxInterface $quxApp)
    {
        return [$q, $bar->foo, $quxApp->quxMethod()];
    }

    public function actionAksi6($q, EmailValidator $validator)
    {
        return [$q, $validator->validate($q), $validator->validate('misbahuldmunir@gmail.com')];
    }

    public function actionAksi7(Bar $bar, $avaliable, $missing)
    {

    }

    public function actionAksi8($arg1, $arg2)
    {
        return func_get_args();
    }

    public function actionAksi9($arg1, $arg2, QuxInterface $quxApp)
    {
        return func_get_args();
    }
}
