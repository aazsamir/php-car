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

    /**
     * @return mixed
     */
    public function toArray(): mixed
    {
        return $this->cbor->normalize();
    }
}
