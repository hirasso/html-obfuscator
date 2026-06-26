<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator\Commands;

use Spatie\TypeScriptTransformer\Enums\RunnerMode;
use Spatie\TypeScriptTransformer\Formatters\PrettierFormatter;
use Spatie\TypeScriptTransformer\Runners\Runner;
use Spatie\TypeScriptTransformer\Support\Loggers\SymfonyConsoleLogger;
use Spatie\TypeScriptTransformer\Transformers\AttributedClassTransformer;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;
use Spatie\TypeScriptTransformer\Writers\GlobalNamespaceWriter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/** @codeCoverageIgnore */
#[AsCommand(name: 'typescript:transform')]
class GenerateTypeScriptCommand extends Command
{
    protected function configure(): void
    {
        $this->setDescription('Transform TypeScript types');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $runner = new Runner();

        $config = TypeScriptTransformerConfigFactory::create()
            ->transformer(new AttributedClassTransformer())
            ->transformer(new EnumTransformer())
            ->transformDirectories(dirname(__DIR__))
            ->writer(new GlobalNamespaceWriter())
            ->outputDirectory(dirname(__DIR__, 2) . '/resources/src/generated')
            ->formatter(new PrettierFormatter())
            ->get();

        return $runner->run(
            logger: new SymfonyConsoleLogger($output),
            config: $config,
            mode: RunnerMode::Direct,
        );
    }
}
