<?php

namespace leapsunit\src\Mail;

use Leaps;
use leapsunit\TestCase;
use Leaps\Mail\BaseMailer;
use Leaps\Mail\BaseMessage;

/**
 * @group mail
 */
class BaseMessageTest extends TestCase
{

	public function setUp()
	{
		$this->mockApplication ( [
			'services'=> [
				'mailer'=> $this->createTestEmailComponent ()
			]
		] );
	}

	/**
	 * @return Mailer test email component instance.
	 */
	protected function createTestEmailComponent()
	{
		$component = new TestMailer ();

		return $component;
	}

	/**
	 * @return TestMailer mailer instance.
	 */
	protected function getMailer()
	{
		return Leaps::$app->get ( 'mailer' );
	}

	// Tests :
	public function testSend()
	{
		$mailer = $this->getMailer ();
		$message = $mailer->compose ();
		$message->send ( $mailer );
		$this->assertEquals ( $message, $mailer->sentMessages[0], 'Unable to send message!' );
	}

	public function testToString()
	{
		$mailer = $this->getMailer ();
		$message = $mailer->compose ();
		$this->assertEquals ( $message->toString (), '' . $message );
	}
}

/**
 * Test Mailer class
 */
class TestMailer extends BaseMailer
{

	public $messageClass = 'leapsunit\src\Mail\TestMessage';

	public $sentMessages = [ ];

	protected function sendMessage($message)
	{
		$this->sentMessages[] = $message;
	}
}

/**
 * Test Message class
 */
class TestMessage extends BaseMessage
{

	public $text;

	public $html;

	public function getCharset()
	{
		return '';
	}

	public function setCharset($charset)
	{
	}

	public function getFrom()
	{
		return '';
	}

	public function setFrom($from)
	{
	}

	public function getReplyTo()
	{
		return '';
	}

	public function setReplyTo($replyTo)
	{
	}

	public function getTo()
	{
		return '';
	}

	public function setTo($to)
	{
	}

	public function getCc()
	{
		return '';
	}

	public function setCc($cc)
	{
	}

	public function getBcc()
	{
		return '';
	}

	public function setBcc($bcc)
	{
	}

	public function getSubject()
	{
		return '';
	}

	public function setSubject($subject)
	{
	}

	public function setTextBody($text)
	{
		$this->text = $text;
	}

	public function setHtmlBody($html)
	{
		$this->html = $html;
	}

	public function attachContent($content, array $options = [])
	{
	}

	public function attach($fileName, array $options = [])
	{
	}

	public function embed($fileName, array $options = [])
	{
	}

	public function embedContent($content, array $options = [])
	{
	}

	public function toString()
	{
		return get_class ( $this );
	}
}
