<?php

use Hirasso\HTMLObfuscator\Strategies\ROT47Strategy;

test('ROT47Strategy is self-inverse', function () {
    $original = 'mail@example.com';
    $encoded = base64_decode((new ROT47Strategy($original))->obfuscate());
    $roundtrip = '';
    for ($i = 0, $len = strlen($encoded); $i < $len; $i++) {
        $c = ord($encoded[$i]);
        $roundtrip .= ($c >= 33 && $c <= 126) ? chr(33 + ($c - 33 + 47) % 94) : $encoded[$i];
    }
    expect($roundtrip)->toBe($original);
});
