<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use function Hirasso\HTMLObfuscator\obfuscate;

echo obfuscate(<<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Fixture</title>
</head>
<body>
  <p>mail@example.com</p>
  <p>contact@example.com</p>
  <p>+1 555 123 4567</p>
  <p>+1 555 123-4567</p>
  <a href="mailto:mail@example.com">Send email</a>
</body>
</html>
HTML);
