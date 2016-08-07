<?php

/**
 * Email class
 * 
 * @package    Deraemon/Email
 * @category   Base
 * @author     kohx by Deraemons
 * @copyright  (c) 2015-2016 Deraemons
 * @license    http://emon-cms.com/license
 */
require_once 'AutoLoader.php';
require_once VENPATH . 'autoload.php';

class Email {

	protected $_config;
	protected $_subject;
	protected $_body;
	protected $_from;
	protected $_to;
	protected $_attach = [];

	/**
	 * fact
	 * 
	 * @param array $param
	 * @return Email
	 */
	public static function fact(array $config = [])
	{
		return new Email($config);
	}

	/**
	 * construct
	 * 
	 * @param array $param
	 */
	public function __construct(array $config = [])
	{

		$this->_config = $config ? : Config::fact('email')->get('config');
	}

	public function subject($subject)
	{
		$this->_subject = $subject;

		return $this;
	}

	public function body($body)
	{
		$this->_body = $body;

		return $this;
	}

	public function from($from)
	{
		$this->_from = $from;

		return $this;
	}

	public function to($to)
	{
		$this->_to = $to;

		return $this;
	}

	public function attach($path)
	{
		$this->_attach[] = Swift_Attachment::fromPath($path);

		return $this;
	}

	public function send()
	{
		// setting japanese
		Swift::init(function ()
		{
			Swift_DependencyContainer::getInstance()
					->register('mime.qpheaderencoder')
					->asAliasOf('mime.base64headerencoder');
			Swift_Preferences::getInstance()->setCharset('iso-2022-jp');
		});

		// make smtp instance
		$transport = Swift_SmtpTransport::newInstance($this->_config['host'], $this->_config['port'])
				->setUsername($this->_config['user'])
				->setPassword($this->_config['pass'])
				->setEncryption($this->_config['encryption']);

		// make mailer instance
		$mailer = Swift_Mailer::newInstance($transport);

		// make massage instance
		$message = Swift_Message::newInstance();

		// atache files
		if ($this->_attach)
		{
			foreach ($this->_attach as $attach)
			{
				$message->attach($attach);
			}
		}

		// build message
		$message
				->setSubject(mb_convert_encoding($this->_subject, 'iso-2022-jp', 'utf-8'))
				->setFrom($this->_from)
				->setTo($this->_to)
				->setBody($this->_body)
				->setCharset('iso-2022-jp')
				->setEncoder(Swift_Encoding::get7BitEncoding())
		;

		// send
		$result = $mailer->send($message);

		return $result;
	}

}
