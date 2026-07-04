<?php

declare(strict_types=1);

namespace Aazsamir\PhpCar\Tests;

use Aazsamir\PhpCar\Car\CarDecoder;
use PHPUnit\Framework\TestCase;

class DecoderTest extends TestCase
{
    public function testDecode(): void
    {
        $data = \file_get_contents(__DIR__.'/fixtures/repo.car');
        $expected = \file_get_contents(__DIR__.'/fixtures/repo.json');
        $decoder = CarDecoder::new();
        $carData = $decoder->decode($data);

        $this->assertSame(
            $expected,
            \json_encode(
                $carData->toArray(),
                \JSON_THROW_ON_ERROR | \JSON_INVALID_UTF8_IGNORE,
            ),
        );
    }
}
