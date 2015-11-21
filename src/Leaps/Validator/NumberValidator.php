<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2015 TintSoft
 * @license http://www.tintsoft.com/license/
 */

namespace Leaps\Validator;

use Leaps;
use Leaps\Web\JsExpression;
use Leaps\Helper\Json;

/**
 * NumberValidator validates that the attribute value is a number.
 *
 * The format of the number must match the regular expression specified in [[integerPattern]] or [[numberPattern]].
 * Optionally, you may configure the [[max]] and [[min]] properties to ensure the number
 * is within certain range.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class NumberValidator extends Validator
{
    /**
     * @var boolean whether the attribute value can only be an integer. Defaults to false.
     */
    public $integerOnly = false;
    /**
     * @var integer|float upper limit of the number. Defaults to null, meaning no upper limit.
     * @see tooBig for the customized message used when the number is too big.
     */
    public $max;
    /**
     * @var integer|float lower limit of the number. Defaults to null, meaning no lower limit.
     * @see tooSmall for the customized message used when the number is too small.
     */
    public $min;
    /**
     * @var string user-defined error message used when the value is bigger than [[max]].
     */
    public $tooBig;
    /**
     * @var string user-defined error message used when the value is smaller than [[min]].
     */
    public $tooSmall;
    /**
     * @var string the regular expression for matching integers.
     */
    public $integerPattern = '/^\s*[+-]?\d+\s*$/';
    /**
     * @var string the regular expression for matching numbers. It defaults to a pattern
     * that matches floating numbers with optional exponential part (e.g. -1.23e-10).
     */
    public $numberPattern = '/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/';


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = $this->integerOnly ? Leaps::t('leaps', '{attribute} must be an integer.')
                : Leaps::t('leaps', '{attribute} must be a number.');
        }
        if ($this->min !== null && $this->tooSmall === null) {
            $this->tooSmall = Leaps::t('leaps', '{attribute} must be no less than {min}.');
        }
        if ($this->max !== null && $this->tooBig === null) {
            $this->tooBig = Leaps::t('leaps', '{attribute} must be no greater than {max}.');
        }
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        if (is_array($value)) {
            $this->addError($model, $attribute, $this->message);
            return;
        }
        $pattern = $this->integerOnly ? $this->integerPattern : $this->numberPattern;
        if (!preg_match($pattern, "$value")) {
            $this->addError($model, $attribute, $this->message);
        }
        if ($this->min !== null && $value < $this->min) {
            $this->addError($model, $attribute, $this->tooSmall, ['min' => $this->min]);
        }
        if ($this->max !== null && $value > $this->max) {
            $this->addError($model, $attribute, $this->tooBig, ['max' => $this->max]);
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        if (is_array($value)) {
            return [Leaps::t('leaps', '{attribute} is invalid.'), []];
        }
        $pattern = $this->integerOnly ? $this->integerPattern : $this->numberPattern;
        if (!preg_match($pattern, "$value")) {
            return [$this->message, []];
        } elseif ($this->min !== null && $value < $this->min) {
            return [$this->tooSmall, ['min' => $this->min]];
        } elseif ($this->max !== null && $value > $this->max) {
            return [$this->tooBig, ['max' => $this->max]];
        } else {
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        $label = $model->getAttributeLabel($attribute);

        $options = [
            'pattern' => new JsExpression($this->integerOnly ? $this->integerPattern : $this->numberPattern),
            'message' => Leaps::$app->getI18n()->format($this->message, [
                'attribute' => $label,
            ], Leaps::$app->language),
        ];

        if ($this->min !== null) {

            $options['min'] = is_string($this->min) ? (float) $this->min : $this->min;
            $options['tooSmall'] = Leaps::$app->getI18n()->format($this->tooSmall, [
                'attribute' => $label,
                'min' => $this->min,
            ], Leaps::$app->language);
        }
        if ($this->max !== null) {

            $options['max'] = is_string($this->max) ? (float) $this->max : $this->max;
            $options['tooBig'] = Leaps::$app->getI18n()->format($this->tooBig, [
                'attribute' => $label,
                'max' => $this->max,
            ], Leaps::$app->language);
        }
        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        ValidationAsset::register($view);

        return 'leaps.validation.number(value, messages, ' . Json::htmlEncode($options) . ');';
    }
}
