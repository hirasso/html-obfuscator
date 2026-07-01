<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
final class ScriptSettings
{
    public function __construct(
        public string $tagName = HTMLObfuscator::DEFAULT_TAG_NAME,
    ) {
    }
}
