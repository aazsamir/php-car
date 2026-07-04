<?php

declare(strict_types=1);

namespace Aazsamir\PhpCar\Bytes;

final class Multibase
{
    public static function decode(string $input): string
    {
        if ($input === '') {
            throw new InvalidInput('Empty multibase string');
        }

        if ($input[0] === 'b') {
            return self::base32Decode(substr($input, 1));
        }

        throw new InvalidInput("Unsupported multibase prefix: {$input[0]}");
    }

    public static function encodeBase32(string $bytes): string
    {
        return 'b' . self::base32Encode($bytes);
    }

    private static function base32Decode(string $input): string
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyz234567';

        $input = strtolower($input);
        $input = rtrim($input, '=');

        $buffer = 0;
        $bits = 0;
        $output = '';

        $len = strlen($input);

        for ($i = 0; $i < $len; $i++) {
            $pos = strpos($alphabet, $input[$i]);

            if ($pos === false) {
                throw new InvalidInput("Invalid base32 character: {$input[$i]}");
            }

            $buffer = ($buffer << 5) | $pos;
            $bits += 5;

            if ($bits >= 8) {
                $bits -= 8;
                $output .= chr(($buffer >> $bits) & 0xFF);
            }
        }

        return $output;
    }

    private static function base32Encode(string $bytes): string
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyz234567';

        $buffer = 0;
        $bits = 0;
        $output = '';

        $len = strlen($bytes);

        for ($i = 0; $i < $len; $i++) {
            $buffer = ($buffer << 8) | ord($bytes[$i]);
            $bits += 8;

            while ($bits >= 5) {
                $bits -= 5;
                $output .= $alphabet[($buffer >> $bits) & 31];
            }
        }

        if ($bits > 0) {
            $output .= $alphabet[($buffer << (5 - $bits)) & 31];
        }

        return $output;
    }
}
