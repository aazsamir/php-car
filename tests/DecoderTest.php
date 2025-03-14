<?php

declare(strict_types=1);

namespace Aazsamir\PhpCar\Tests;

use Aazsamir\PhpCar\Decoder;
use PHPUnit\Framework\TestCase;

class DecoderTest extends TestCase
{
    public function testDecode(): void
    {
        $data = \file_get_contents(__DIR__.'/fixtures/carv1-basic.car');
        $decoder = Decoder::new();
        $carData = $decoder->decode($data);
        dd($carData);
    }
}