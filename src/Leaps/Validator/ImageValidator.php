<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2015 TintSoft
 * @license http://www.tintsoft.com/license/
 */

namespace Leaps\Validator;

use Leaps;
use Leaps\Web\UploadedFile;

/**
 * ImageValidator verifies if an attribute is receiving a valid image.
 *
 * @author Taras Gudz <gudz.taras@gmail.com>
 * @since 2.0
 */
class ImageValidator extends FileValidator
{
    /**
     * @var string the error message used when the uploaded file is not an image.
     * You may use the following tokens in the message:
     *
     * - {attribute}: the attribute name
     * - {file}: the uploaded file name
     */
    public $notImage;
    /**
     * @var integer the minimum width in pixels.
     * Defaults to null, meaning no limit.
     * @see underWidth for the customized message used when image width is too small.
     */
    public $minWidth;
    /**
     * @var integer the maximum width in pixels.
     * Defaults to null, meaning no limit.
     * @see overWidth for the customized message used when image width is too big.
     */
    public $maxWidth;
    /**
     * @var integer the minimum height in pixels.
     * Defaults to null, meaning no limit.
     * @see underHeight for the customized message used when image height is too small.
     */
    public $minHeight;
    /**
     * @var integer the maximum width in pixels.
     * Defaults to null, meaning no limit.
     * @see overWidth for the customized message used when image height is too big.
     */
    public $maxHeight;
    /**
     * @var string the error message used when the image is under [[minWidth]].
     * You may use the following tokens in the message:
     *
     * - {attribute}: the attribute name
     * - {file}: the uploaded file name
     * - {limit}: the value of [[minWidth]]
     */
    public $underWidth;
    /**
     * @var string the error message used when the image is over [[maxWidth]].
     * You may use the following tokens in the message:
     *
     * - {attribute}: the attribute name
     * - {file}: the uploaded file name
     * - {limit}: the value of [[maxWidth]]
     */
    public $overWidth;
    /**
     * @var string the error message used when the image is under [[minHeight]].
     * You may use the following tokens in the message:
     *
     * - {attribute}: the attribute name
     * - {file}: the uploaded file name
     * - {limit}: the value of [[minHeight]]
     */
    public $underHeight;
    /**
     * @var string the error message used when the image is over [[maxHeight]].
     * You may use the following tokens in the message:
     *
     * - {attribute}: the attribute name
     * - {file}: the uploaded file name
     * - {limit}: the value of [[maxHeight]]
     */
    public $overHeight;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->notImage === null) {
            $this->notImage = Leaps::t('leaps', 'The file "{file}" is not an image.');
        }
        if ($this->underWidth === null) {
            $this->underWidth = Leaps::t('leaps', 'The image "{file}" is too small. The width cannot be smaller than {limit, number} {limit, plural, one{pixel} other{pixels}}.');
        }
        if ($this->underHeight === null) {
            $this->underHeight = Leaps::t('leaps', 'The image "{file}" is too small. The height cannot be smaller than {limit, number} {limit, plural, one{pixel} other{pixels}}.');
        }
        if ($this->overWidth === null) {
            $this->overWidth = Leaps::t('leaps', 'The image "{file}" is too large. The width cannot be larger than {limit, number} {limit, plural, one{pixel} other{pixels}}.');
        }
        if ($this->overHeight === null) {
            $this->overHeight = Leaps::t('leaps', 'The image "{file}" is too large. The height cannot be larger than {limit, number} {limit, plural, one{pixel} other{pixels}}.');
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($file)
    {
        $result = parent::validateValue($file);

        return empty($result) ? $this->validateImage($file) : $result;
    }

    /**
     * Validates an image file.
     * @param UploadedFile $image uploaded file passed to check against a set of rules
     * @return array|null the error message and the parameters to be inserted into the error message.
     * Null should be returned if the data is valid.
     */
    protected function validateImage($image)
    {
        if (false === ($imageInfo = getimagesize($image->tempName))) {
            return [$this->notImage, ['file' => $image->name]];
        }

        list($width, $height) = $imageInfo;

        if ($width == 0 || $height == 0) {
            return [$this->notImage, ['file' => $image->name]];
        }

        if ($this->minWidth !== null && $width < $this->minWidth) {
            return [$this->underWidth, ['file' => $image->name, 'limit' => $this->minWidth]];
        }

        if ($this->minHeight !== null && $height < $this->minHeight) {
            return [$this->underHeight, ['file' => $image->name, 'limit' => $this->minHeight]];
        }

        if ($this->maxWidth !== null && $width > $this->maxWidth) {
            return [$this->overWidth, ['file' => $image->name, 'limit' => $this->maxWidth]];
        }

        if ($this->maxHeight !== null && $height > $this->maxHeight) {
            return [$this->overHeight, ['file' => $image->name, 'limit' => $this->maxHeight]];
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);
        return 'leaps.validation.image(attribute, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ', deferred);';
    }

    /**
     * @inheritdoc
     */
    protected function getClientOptions($model, $attribute)
    {
        $options = parent::getClientOptions($model, $attribute);

        $label = $model->getAttributeLabel($attribute);

        if ($this->notImage !== null) {
            $options['notImage'] = Leaps::$app->getI18n()->format($this->notImage, [
                'attribute' => $label
            ], Leaps::$app->language);
        }

        if ($this->minWidth !== null) {
            $options['minWidth'] = $this->minWidth;
            $options['underWidth'] = Leaps::$app->getI18n()->format($this->underWidth, [
                'attribute' => $label,
                'limit' => $this->minWidth
            ], Leaps::$app->language);
        }

        if ($this->maxWidth !== null) {
            $options['maxWidth'] = $this->maxWidth;
            $options['overWidth'] = Leaps::$app->getI18n()->format($this->overWidth, [
                'attribute' => $label,
                'limit' => $this->maxWidth
            ], Leaps::$app->language);
        }

        if ($this->minHeight !== null) {
            $options['minHeight'] = $this->minHeight;
            $options['underHeight'] = Leaps::$app->getI18n()->format($this->underHeight, [
                'attribute' => $label,
                'limit' => $this->minHeight
            ], Leaps::$app->language);
        }

        if ($this->maxHeight !== null) {
            $options['maxHeight'] = $this->maxHeight;
            $options['overHeight'] = Leaps::$app->getI18n()->format($this->overHeight, [
                'attribute' => $label,
                'limit' => $this->maxHeight
            ], Leaps::$app->language);
        }

        return $options;
    }
}
