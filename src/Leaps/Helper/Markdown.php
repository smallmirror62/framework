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
namespace Leaps\Helper;

use Leaps;
use Leaps\Core\InvalidParamException;

class Markdown
{
	/**
	 *
	 * @var array a map of markdown flavor names to corresponding parser class configurations.
	 */
	public static $flavors = [ 'original' => [ 'className' => 'cebe\markdown\Markdown','html5' => true ],'gfm' => [ 'className' => 'cebe\markdown\GithubMarkdown','html5' => true ],'gfm-comment' => [ 'className' => 'cebe\markdown\GithubMarkdown','html5' => true,'enableNewlines' => true ],'extra' => [ 'className' => 'cebe\markdown\MarkdownExtra','html5' => true ] ];

	/**
	 *
	 * @var string the markdown flavor to use when none is specified explicitly.
	 *      Defaults to `original`.
	 * @see $flavors
	 */
	public static $defaultFlavor = 'original';

	/**
	 * Converts markdown into HTML.
	 *
	 * @param string $markdown the markdown text to parse
	 * @param string $flavor the markdown flavor to use. See [[$flavors]] for available values.
	 * @return string the parsed HTML output
	 * @throws \yii\base\InvalidParamException when an undefined flavor is given.
	 */
	public static function process($markdown, $flavor = 'original')
	{
		$parser = static::getParser ( $flavor );
		return $parser->parse ( $markdown );
	}

	/**
	 * Converts markdown into HTML but only parses inline elements.
	 *
	 * This can be useful for parsing small comments or description lines.
	 *
	 * @param string $markdown the markdown text to parse
	 * @param string $flavor the markdown flavor to use. See [[$flavors]] for available values.
	 * @return string the parsed HTML output
	 * @throws \Leaps\Core\InvalidParamException when an undefined flavor is given.
	 */
	public static function processParagraph($markdown, $flavor = 'original')
	{
		$parser = static::getParser ( $flavor );
		return $parser->parseParagraph ( $markdown );
	}

	/**
	 *
	 * @param string $flavor
	 * @return \cebe\markdown\Parser
	 * @throws \Leaps\Core\InvalidParamException when an undefined flavor is given.
	 */
	protected static function getParser($flavor)
	{
		/* @var $parser \cebe\markdown\Markdown */
		if (! isset ( static::$flavors [$flavor] )) {
			throw new InvalidParamException ( "Markdown flavor '$flavor' is not defined.'" );
		} elseif (! is_object ( $config = static::$flavors [$flavor] )) {
			$parser = Leaps::createObject ( $config );
			if (is_array ( $config )) {
				foreach ( $config as $name => $value ) {
					$parser->{$name} = $value;
				}
			}
			static::$flavors [$flavor] = $parser;
		}
		return static::$flavors [$flavor];
	}
}