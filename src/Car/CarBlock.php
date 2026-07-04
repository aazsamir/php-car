<?php

declare(strict_types=1);

namespace Aazsamir\PhpCar\Car;

use CBOR\CBORObject;
use CBOR\Normalizable;

readonly class CarBlock
{
    public function __construct(
        public CBORObject&Normalizable $cbor,
    ) {}

    public function toArray(): mixed
    {
        return $this->cbor->normalize();
    }
}
