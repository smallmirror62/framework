<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2015 TintSoft
 * @license http://www.tintsoft.com/license/
 */

namespace Leaps\Captcha;

use Leaps;
use Leaps\Base\InvalidConfigException;
use Leaps\Validator\ValidationAsset;
use Leaps\Validator\Validator;

/**
 * CaptchaValidator validates that the attribute value is the same as the verification code displayed in the CAPTCHA.
 *
 * CaptchaValidator should be used together with [[CaptchaAction]].
 *
 * Note that once CAPTCHA validation succeeds, a new CAPTCHA will be generated automatically. As a result,
 * CAPTCHA validation should not be used in AJAX validation mode because it may fail the validation
 * even if a user enters the same code as shown in the CAPTCHA image which is actually different from the latest CAPTCHA code.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CaptchaValidator extends Validator
{
    /**
     * @var boolean whether to skip this validator if the input is empty.
     */
    public $skipOnEmpty = false;
    /**
     * @var boolean whether the comparison is case sensitive. Defaults to false.
     */
    public $caseSensitive = false;
    /**
     * @var string the route of the controller action that renders the CAPTCHA image.
     */
    public $captchaAction = 'site/captcha';


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = Leaps::t('leaps', 'The verification code is incorrect.');
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        $captcha = $this->createCaptchaAction();
        $valid = !is_array($value) && $captcha->validate($value, $this->caseSensitive);

        return $valid ? null : [$this->message, []];
    }

    /**
     * Creates the CAPTCHA action object from the route specified by [[captchaAction]].
     * @return \Leaps\Captcha\CaptchaAction the action object
     * @throws InvalidConfigException
     */
    public function createCaptchaAction()
    {
        $ca = Leaps::$app->createController($this->captchaAction);
        if ($ca !== false) {
            /* @var $controller \Leaps\Base\Controller */
            list($controller, $actionID) = $ca;
            $action = $controller->createAction($actionID);
            if ($action !== null) {
                return $action;
            }
        }
        throw new InvalidConfigException('Invalid CAPTCHA action ID: ' . $this->captchaAction);
    }

    /**
     * @inheritdoc
     */
    public function clientValidateAttribute($object, $attribute, $view)
    {
        $captcha = $this->createCaptchaAction();
        $code = $captcha->getVerifyCode(false);
        $hash = $captcha->generateValidationHash($this->caseSensitive ? $code : strtolower($code));
        $options = [
            'hash' => $hash,
            'hashKey' => 'leapsCaptcha/' . $this->captchaAction,
            'caseSensitive' => $this->caseSensitive,
            'message' => Leaps::$app->getI18n()->format($this->message, [
                'attribute' => $object->getAttributeLabel($attribute),
            ], Leaps::$app->language),
        ];
        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        ValidationAsset::register($view);

        return 'leaps.validation.captcha(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
    }
}
