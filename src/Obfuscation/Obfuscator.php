<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator\Obfuscation;

use Hirasso\HTMLObfuscator\Contracts\ObfuscationStrategy;
use InvalidArgumentException;

final class Obfuscator
{
    /** Maps client-side decoder name → strategy class, in index order */
    public const array STRATEGIES = [
        'xor'   => XorStrategy::class,
        'revxor'   => RevXorStrategy::class,
        'rot47' => Rot47Strategy::class,
    ];

    /** @var class-string<ObfuscationStrategy> */
    private string $strategy;

    public function __construct()
    {
        $this->setRandomStrategy();
    }

    /**
     * Set a random strategy
     */
    public function setRandomStrategy(): self
    {
        $keys = array_keys(self::STRATEGIES);

        shuffle($keys);

        $this->strategy = self::STRATEGIES[$keys[0]];

        return $this;
    }

    /**
     * Get the currently selected strategy
     *
     * @return class-string<ObfuscationStrategy>
     */
    public function getStrategy(): string
    {
        return $this->strategy;
    }

    /**
     * @param class-string<ObfuscationStrategy> $strategy
     */
    public function setStrategy(string $strategy): self
    {
        $key = array_find_key(
            self::STRATEGIES,
            fn (string $value) => $value === $strategy
        );

        if ($key === null) {
            throw new InvalidArgumentException(sprintf(
                'Strategy %s does not exist',
                htmlspecialchars($strategy)
            ));
        }

        $this->strategy = self::STRATEGIES[$key];

        return $this;
    }

    /**
     * Apply obfuscation to a value
     */
    public function obfuscate(string $value): string
    {
        return "{$this->getStrategyIndex()}:{$this->strategy::obfuscate($value)}";
    }

    /**
     * Get the attribute value
     */
    public function getAttribute(string $value): string
    {
        return base64_encode($this->obfuscate($value));
    }

    /**
     * Get the identifier of the currently selected strategy
     */
    public function getIdentifier(): string
    {
        return array_keys(self::STRATEGIES)[$this->getStrategyIndex()];
    }

    /**
     * Return the zero-based index of the currently selected strategy
     */
    private function getStrategyIndex(): int
    {
        $index = array_find_key(
            array_values(self::STRATEGIES),
            fn (string $value) => $value === $this->strategy
        );

        if ($index === null) {
            throw new \LogicException("Strategy {$this->strategy} not found in STRATEGIES");
        }

        return $index;
    }
}
