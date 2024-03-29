<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2015 TintSoft
 * @license http://www.tintsoft.com/license/
 */

namespace Leaps\I18n;

use Leaps\Base\Service;

/**
 * GettextFile is the base class for representing a Gettext message file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class GettextFile extends Service
{
    /**
     * Loads messages from a file.
     * @param string $filePath file path
     * @param string $context message context
     * @return array message translations. Array keys are source messages and array values are translated messages:
     * source message => translated message.
     */
    abstract public function load($filePath, $context);

    /**
     * Saves messages to a file.
     * @param string $filePath file path
     * @param array $messages message translations. Array keys are source messages and array values are
     * translated messages: source message => translated message. Note if the message has a context,
     * the message ID must be prefixed with the context with chr(4) as the separator.
     */
    abstract public function save($filePath, $messages);
}
