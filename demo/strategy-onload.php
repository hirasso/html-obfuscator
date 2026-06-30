<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/_layout.php';

use Hirasso\HTMLObfuscator\Enum\RevealTrigger;

render_example(RevealTrigger::Load);
