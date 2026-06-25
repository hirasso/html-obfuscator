<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator\Enum;

enum UserInteraction: string
{
    case None = 'none';
    case General = 'general';
    case Intentional = 'intentional';
};
