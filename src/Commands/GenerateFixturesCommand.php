<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator\Commands;

use Hirasso\HTMLObfuscator\Obfuscation\Obfuscator;
use Hirasso\HTMLObfuscator\Obfuscation\Rot47Strategy;
use Hirasso\HTMLObfuscator\Obfuscation\RevXorStrategy;
use Hirasso\HTMLObfuscator\Obfuscation\XorStrategy;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/** @codeCoverageIgnore */
#[AsCommand(name: 'fixtures:generate')]
class GenerateFixturesCommand extends Command
{
    private const array INPUTS = [
        'hello@example.com',
        '+1 (555) 123-4567',
        'user+tag@sub.domain.example.org',
        'münchen@ümlaute.example.com',
        '😍😍😍@emojis.example.com'
    ];

    protected function configure(): void
    {
        $this->setDescription('Generate JS unit test fixtures from PHP obfuscation output');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $root = dirname(__DIR__, 2);
        $outputPath = "{$root}/tests/vitest/fixtures/decode.json";

        $obfuscator = new Obfuscator();
        $fixtures = [];

        foreach ([XorStrategy::class, RevXorStrategy::class, Rot47Strategy::class] as $strategyClass) {
            $obfuscator->setStrategy($strategyClass);
            foreach (self::INPUTS as $plain) {
                $fixtures[] = [
                    'strategy' => $obfuscator->getIdentifier(),
                    'plain'    => $plain,
                    'encoded'  => $obfuscator->getAttribute($plain),
                ];
            }
        }

        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            mkdir($dir, recursive: true);
        }

        file_put_contents(
            $outputPath,
            json_encode($fixtures, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n"
        );

        $output->writeln("<info>Generated {$outputPath}</info>");

        return Command::SUCCESS;
    }
}
