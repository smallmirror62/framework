<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2015 TintSoft
 * @license http://www.tintsoft.com/license/
 */

namespace Leaps\Helper;

/**
 * HtmlPurifier provides an ability to clean up HTML from any harmful code.
 *
 * Basic usage is the following:
 *
 * ```php
 * echo HtmlPurifier::process($html);
 * ```
 *
 * If you want to configure it:
 *
 * ```php
 * echo HtmlPurifier::process($html, [
 *     'Attr.EnableID' => true,
 * ]);
 * ```
 *
 * For more details please refer to [HTMLPurifier documentation](http://htmlpurifier.org/).
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class HtmlPurifier extends BaseHtmlPurifier
{
}
