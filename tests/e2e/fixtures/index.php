<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

use Hirasso\HTMLObfuscator\HTMLObfuscator;

use function Hirasso\HTMLObfuscator\obfuscate;

$defaultTagName = HTMLObfuscator::DEFAULT_TAG_NAME;

echo obfuscate(<<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Fixture</title>
    <style>
        $defaultTagName, a[href=""] { color: red }
    </style>
</head>
<body>
  <p>This is a plaintext email address: mail@example.com. It should not cause layout shift when de-obfuscated.</p>
  <p>contact@example.com</p>
  <p>This is a plaintext phone number: +1 555 123 4567. It should not cause layout shift when de-obfuscated.</p>
  <p>+1 555 123-4567</p>
  <a href="mailto:mail@example.com">Send email</a>
</body>
</html>
HTML, passphrase: 'html obfuscator');
