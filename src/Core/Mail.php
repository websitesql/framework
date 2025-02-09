<?php declare(strict_types=1);

namespace AlanTiller\Framework\Core;

use AlanTiller\Framework\Interfaces\MailInterface;

class Mail
{
    private MailInterface $mail;

    public function __construct(MailInterface $mail)
    {
        $this->mail = $mail;
    }

    public function getMail(): MailInterface
    {
        return $this->mail;
    }
}