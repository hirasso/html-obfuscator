<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator\Enum;

enum RevealTrigger: string
{
    /** reveal immediately on load */
    case Load = 'load';
    /** require interaction with the site before rendering */
    case Interaction = 'interaction';
    /** let the developer reveal manually */
    case Manual = 'manual';
};
