<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator\Commands;

use Parsedown;
use Spatie\ShikiPhp\Shiki;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/** @codeCoverageIgnore */
#[AsCommand(name: 'readme:generate')]
class GenerateReadmeCommand extends Command
{
    protected function configure(): void
    {
        $this->setDescription('Generate demo/readme.html from README.md');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $root = dirname(__DIR__, 2);
        $readmePath = "{$root}/README.md";
        $outputPath = "{$root}/demo/readme.html";

        $markdown = file_get_contents($readmePath);
        if ($markdown === false) {
            $output->writeln("<error>Could not read {$readmePath}</error>");
            return Command::FAILURE;
        }

        // Strip everything before the first ## heading (title + badges)
        $markdown = preg_replace('/\A.*?(?=^## )/ms', '', $markdown) ?? $markdown;

        // Render > [!NOTE] as a styled blockquote
        $markdown = preg_replace('/^> \[!NOTE\]$/m', '> **Note:**', $markdown) ?? $markdown;

        $parsedown = new class () extends Parsedown {
            /**
             * @param array<string, mixed> $Block
             * @return array<string, string>
             */
            protected function blockFencedCodeComplete($Block): array
            {
                $language = str_replace('language-', '', $Block['element']['element']['attributes']['class'] ?? 'text');
                $code = $Block['element']['element']['text'] ?? '';
                return ['markup' => Shiki::highlight(rtrim($code), $language, 'nord')];
            }
        };

        // Parsedown has known undefined-key warnings on PHP 8 — suppress at call site
        $html = @$parsedown->text($markdown);

        // Rewrite relative hrefs to absolute GitHub URLs
        $repoUrl = 'https://github.com/hirasso/html-obfuscator/tree/main';
        $html = preg_replace_callback(
            '/href="((?!https?:|#|mailto:|tel:|\/)[^"]+)"/',
            fn ($m) => sprintf('href="%s/%s"', $repoUrl, ltrim($m[1], './')),
            $html
        ) ?? $html;

        file_put_contents($outputPath, $html);

        $output->writeln("<info>Generated {$outputPath}</info>");

        return Command::SUCCESS;
    }
}
