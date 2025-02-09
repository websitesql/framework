<?php declare(strict_types=1);

namespace AlanTiller\Framework\Interfaces;

interface MailInterface
{
    public function send(string $to, string $subject, string $body, string $altBody = ''): bool;
}