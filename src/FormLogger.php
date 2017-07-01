<?php

namespace Palmtree\WordPress\Form;

use Monolog\Formatter\HtmlFormatter;
use Monolog\Handler\SwiftMailerHandler;
use Monolog\Logger;
use Palmtree\ArgParser\ArgParser;

class FormLogger
{
    public static $defaultArgs = [
        'from'         => '',
        'to'           => '',
        'subject'      => 'New Form Submission',
        'content_type' => 'text/html',
        'smtp'         => [
            'hostname' => 'localhost',
            'port'     => 25,
            'username' => '',
            'password' => '',
        ],
    ];

    protected $args = [];
    protected $logger;

    public function __construct($args = [])
    {
        $this->args   = $this->parseArgs($args);
        $this->logger = $this->createLogger();
    }

    public function log($message)
    {
        $this->logger->alert($message);
    }

    protected function createLogger()
    {
        $message = new \Swift_Message();

        $message->setFrom($this->args['from'])
            ->setTo($this->args['to'])
            ->setSubject($this->args['subject'])
            ->setContentType($this->args['content_type']);

        $handler = new SwiftMailerHandler($this->getMailer(), $message, Logger::INFO);
        $handler->setFormatter(new HtmlFormatter());

        $logger = new Logger('contact');
        $logger->pushHandler($handler);

        return $logger;
    }

    protected function getMailer()
    {
        $transport = new \Swift_SmtpTransport($this->args['smtp']['hostname'], $this->args['smtp']['port']);

        $transport->setUsername($this->args['smtp']['username'])
            ->setPassword($this->args['smtp']['password']);

        $mailer = new \Swift_Mailer($transport);

        return $mailer;
    }

    protected function parseArgs($args)
    {
        $parser = new ArgParser($args);

        return $parser->resolveOptions(static::$defaultArgs);
    }
}
