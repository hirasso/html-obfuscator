<?php

declare(strict_types=1);

namespace Tests\php\Benchmarks;

use Hirasso\HTMLObfuscator\HTMLObfuscator;
use Hirasso\HTMLObfuscator\ObfuscatorConfig;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;

/**
 * Benchmarks for obfuscateTextNode — measuring the cost of the DOM→string→DOM
 * round-trip inside preg_replace_callback per matched text node.
 */
#[BeforeMethods('setUp')]
#[Warmup(2)]
#[Iterations(5)]
#[Revs(50)]
class ObfuscateTextNodeBench
{
    private string $sparse;
    private string $dense;

    public function setUp(): void
    {
        ObfuscatorConfig::reset();

        /** ~5 obfuscatable items — realistic single page section */
        $this->sparse = <<<HTML
            <p>Contact us at hello@example.com or call +49 12 345 67.</p>
            <p>Support: support@example.com</p>
            <p>Sales: sales@example.com or +49 98 765 43.</p>
            <p>Some unrelated paragraph with no sensitive data.</p>
        HTML;

        /** ~20 obfuscatable items — dense contact page */
        $emails = implode("\n", array_map(
            fn ($i) => "<p>user{$i}@example.com — +49 {$i}0 {$i}00 {$i}0</p>",
            range(1, 10)
        ));
        $this->dense = "<div>{$emails}</div>";
    }

    public function benchSparse(): void
    {
        ObfuscatorConfig::reset();
        HTMLObfuscator::createFromString($this->sparse)
            ->injectClientScript(false)
            ->saveHTML();
    }

    public function benchDense(): void
    {
        ObfuscatorConfig::reset();
        HTMLObfuscator::createFromString($this->dense)
            ->injectClientScript(false)
            ->saveHTML();
    }
}
