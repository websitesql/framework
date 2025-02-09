<?php declare(strict_types=1);

namespace AlanTiller\Framework\Providers;

use AlanTiller\Framework\Interfaces\MailInterface;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Psr\Log\LoggerInterface;

class MailProvider implements MailInterface
{
    private array $config;
    private LoggerInterface $logger;

    public function __construct(array $config, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    public function send(string $to, string $subject, string $body, string $altBody = ''): bool
    {
        $mailer = new PHPMailer(true);

        try {
            // Server settings
            $mailer->SMTPDebug = $this->config['debug'] ? SMTP::DEBUG_SERVER : SMTP::DEBUG_OFF;
            $mailer->isSMTP();
            $mailer->Host       = $this->config['host'];
            $mailer->SMTPAuth   = $this->config['auth'];
            $mailer->Username   = $this->config['username'];
            $mailer->Password   = $this->config['password'];
            $mailer->SMTPSecure = $this->config['encryption'];
            $mailer->Port       = $this->config['port'];

            // Recipients
            $mailer->setFrom($this->config['from_address'], $this->config['from_name']);
            $mailer->addAddress($to);

            // Content
            $mailer->isHTML(true);
            $mailer->Subject = $subject;
            $mailer->Body    = $body;
            $mailer->AltBody = $altBody;

            $mailer->send();
            $this->logger->info('Email sent to ' . $to . ' with subject ' . $subject);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Email could not be sent. Error: ' . $mailer->ErrorInfo);
            return false;
        }
    }
}