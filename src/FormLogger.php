<?php declare(strict_types=1);

namespace Palmtree\WordPress\Form;

use Monolog\Formatter\HtmlFormatter;
use Monolog\Handler\SwiftMailerHandler;
use Monolog\Logger;
use Palmtree\ArgParser\ArgParser;
use Psr\Log\LoggerInterface;

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

    /** @var array */
    protected $args = [];
    /** @var LoggerInterface */
    protected $logger;

    public function __construct($args = [])
    {
        $this->args   = $this->parseArgs($args);
        $this->logger = $this->createLogger();
    }

    public function log($message): void
    {
        $this->logger->alert($message);
    }

    protected function createLogger(): LoggerInterface
    {
        $message = new \Swift_Message();

        $message
            ->setFrom($this->args['from'])
            ->setTo($this->args['to'])
            ->setSubject($this->args['subject'])
            ->setContentType($this->args['content_type']);

        $handler = new SwiftMailerHandler($this->getMailer(), $message, Logger::INFO);
        $handler->setFormatter(new HtmlFormatter());

        $logger = new Logger('contact');
        $logger->pushHandler($handler);

        return $logger;
    }

    protected function getMailer(): \Swift_Mailer
    {
        $transport = new \Swift_SmtpTransport($this->args['smtp']['hostname'], $this->args['smtp']['port']);

        $transport
            ->setUsername($this->args['smtp']['username'])
            ->setPassword($this->args['smtp']['password']);

        return new \Swift_Mailer($transport);
    }

    protected function parseArgs($args): array
    {
        $parser = new ArgParser($args);

        return $parser->resolveOptions(static::$defaultArgs);
    }
}
