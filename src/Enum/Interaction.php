<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator\Enum;

enum Interaction: string
{
    /** require interaction anywhere on the document before rendering */
    case OnDocument = 'onDocument';
    /** require interaction on an obfuscated element before rendering */
    case OnElement = 'onElement';
    /** require manual setup */
    case Manual = 'manual';
};
