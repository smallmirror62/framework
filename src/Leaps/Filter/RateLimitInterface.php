<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2015 TintSoft
 * @license http://www.tintsoft.com/license/
 */

namespace Leaps\Filter;

/**
 * RateLimitInterface is the interface that may be implemented by an identity object to enforce rate limiting.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
interface RateLimitInterface
{
    /**
     * Returns the maximum number of allowed requests and the window size.
     * @param \Leaps\Web\Request $request the current request
     * @param \Leaps\Base\Action $action the action to be executed
     * @return array an array of two elements. The first element is the maximum number of allowed requests,
     * and the second element is the size of the window in seconds.
     */
    public function getRateLimit($request, $action);

    /**
     * Loads the number of allowed requests and the corresponding timestamp from a persistent storage.
     * @param \Leaps\Web\Request $request the current request
     * @param \Leaps\Base\Action $action the action to be executed
     * @return array an array of two elements. The first element is the number of allowed requests,
     * and the second element is the corresponding UNIX timestamp.
     */
    public function loadAllowance($request, $action);

    /**
     * Saves the number of allowed requests and the corresponding timestamp to a persistent storage.
     * @param \Leaps\Web\Request $request the current request
     * @param \Leaps\Base\Action $action the action to be executed
     * @param integer $allowance the number of allowed requests remaining.
     * @param integer $timestamp the current timestamp.
     */
    public function saveAllowance($request, $action, $allowance, $timestamp);
}
