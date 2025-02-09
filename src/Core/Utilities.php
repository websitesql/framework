<?php declare(strict_types=1);

namespace AlanTiller\Framework\Core;

use AlanTiller\Framework\Interfaces\UtilitiesInterface;

class Utilities
{
    private UtilitiesInterface $utilities;

    public function __construct(UtilitiesInterface $utilities)
    {
        $this->utilities = $utilities;
    }

    public function getUtilities(): UtilitiesInterface
    {
        return $this->utilities;
    }
}