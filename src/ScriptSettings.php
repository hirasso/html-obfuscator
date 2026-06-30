<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator;

use Hirasso\HTMLObfuscator\Enum\RevealTrigger;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
final class ScriptSettings
{
    public function __construct(
        public bool $debug = false,
        public RevealTrigger $revealTrigger = RevealTrigger::Load,
        public string $tagName = HTMLObfuscator::DEFAULT_TAG_NAME,
        public bool $renderPlaceholders = true,
    ) {
    }
}
