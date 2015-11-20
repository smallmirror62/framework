<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2015 TintSoft
 * @license http://www.tintsoft.com/license/
 */

namespace Leaps\Db;

/**
 * Exception represents an exception that is caused by violation of DB constraints.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class IntegrityException extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Integrity constraint violation';
    }
}
