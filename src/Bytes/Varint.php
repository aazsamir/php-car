<?php

namespace Aazsamir\PhpCar\Bytes;

final class Varint
{
    public static function encode(int $value): string
    {
        $out = '';

        while (true) {
            $byte = $value & 0x7f;
            $value >>= 7;

            if ($value !== 0) {
                $byte |= 0x80;
                $out .= chr($byte);
            } else {
                $out .= chr($byte);

                break;
            }
        }

        return $out;
    }
}