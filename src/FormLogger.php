<?php

declare(strict_types=1);

namespace Palmtree\WordPress\Form;

use Monolog\Formatter\HtmlFormatter;
use Monolog\Handler\SymfonyMailerHandler;
use Monolog\Logger;
use Palmtree\ArgParser\ArgParser;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Email;

class FormLogger
{
    public static array $defaultArgs = [
        'from' => '',
        'to' => '',
        'subject' => 'New Form Submission',
        'smtp' => [
            'hostname' => 'localhost',
            'port' => 25,
            'username' => '',
            'password' => '',
        ],
    ];

    protected array $args = [];
    protected LoggerInterface $logger;

    public function __construct($args = [])
    {
        $this->args = $this->parseArgs($args);
        $this->logger = $this->createLogger();
    }

    public function log($message): void
    {
        $this->logger->alert($message);
    }

    protected function createLogger(): LoggerInterface
    {
        $message = new Email();

        $message
            ->to($this->args['to'])
            ->from($this->args['from'])
            ->subject($this->args['subject'])
        ;

        $handler = new SymfonyMailerHandler($this->getMailer(), $message, Logger::INFO);
        $handler->setFormatter(new HtmlFormatter());

        $logger = new Logger('contact');
        $logger->pushHandler($handler);

        return $logger;
    }

    protected function getMailer(): MailerInterface
    {
        $transport = new EsmtpTransport($this->args['smtp']['hostname'], $this->args['smtp']['port']);
        $transport
            ->setUsername($this->args['smtp']['username'])
            ->setPassword($this->args['smtp']['password'])
        ;

        return new Mailer($transport);
    }

    protected function parseArgs($args): array
    {
        $parser = new ArgParser($args);

        return $parser->resolveOptions(static::$defaultArgs);
    }
}
