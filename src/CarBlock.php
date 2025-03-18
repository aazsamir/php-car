<?php

declare(strict_types=1);

namespace Aazsamir\PhpCar;

use CBOR\CBORObject;

class CarBlock
{
    public function __construct(
        private CBORObject $cbor,
    ) {}

    public function cbor(): CBORObject
    {
        return $this->cbor;
    }

    public function toArray(): mixed
    {
        return CBORToArray::toArray($this->cbor);
    }
}
