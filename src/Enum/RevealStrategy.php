<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator\Enum;

enum RevealStrategy: string
{
    /** reveal immediately on load */
    case OnLoad = 'onload';
    /** require interaction with the site before rendering */
    case OnInteraction = 'oninteraction';
    /** require the developer to manually reveal the element */
    case Manually = 'manually';
};
