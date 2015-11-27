<?php
namespace leapsunit\src\Console\Controller;


use Leaps;
use Leaps\Helper\FileHelper;
use Leaps\I18n\GettextPoFile;

/**
 * Tests that [[\Leaps\Console\Controller\MessageController]] works as expected with PO message format.
 */
class POMessageControllerTest extends BaseMessageControllerTest
{
    protected $messagePath;
    protected $catalog = 'messages';

    public function setUp()
    {
        parent::setUp();

        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('POMessageControllerTest can not run on HHVM because it relies on saving and re-including PHP files which is not supported by HHVM.');
        }

        $this->messagePath = Leaps::getAlias('@leapsunit/runtime/test_messages');
        FileHelper::createDirectory($this->messagePath, 0777);
    }

    public function tearDown()
    {
        parent::tearDown();
        FileHelper::removeDirectory($this->messagePath);
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultConfig()
    {
        return [
            'format' => 'po',
            'languages' => [$this->language],
            'sourcePath' => $this->sourcePath,
            'messagePath' => $this->messagePath,
            'overwrite' => true,
        ];
    }

    /**
     * @return string message file path
     */
    protected function getMessageFilePath()
    {
        return $this->messagePath . '/' . $this->language . '/' . $this->catalog . '.po';
    }

    /**
     * @inheritdoc
     */
    protected function saveMessages($messages, $category)
    {
        $messageFilePath = $this->getMessageFilePath();
        FileHelper::createDirectory(dirname($messageFilePath), 0777);
        $gettext = new GettextPoFile();

        $data = [];
        foreach ($messages as $message => $translation) {
            $data[$category . chr(4) . $message] = $translation;
        }

        $gettext->save($messageFilePath, $data);
    }

    /**
     * @inheritdoc
     */
    protected function loadMessages($category)
    {
        $messageFilePath = $this->getMessageFilePath();
        if (!file_exists($messageFilePath)) {
            return [];
        }

        $gettext = new GettextPoFile();
        return $gettext->load($messageFilePath, $category);
    }
}