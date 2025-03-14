<?php

declare(strict_types=1);

namespace Aazsamir\PhpCar;

use CBOR\CBORObject;

class CarData
{
    /**
     * @param array<string, CBORObject> $blocks
     */
    public function __construct(
        private CBORObject $header,
        private array $blocks,
    ) {}

    public function header(): CBORObject
    {
        return $this->header;
    }

    /**
     * @return array<string, CBORObject>
     */
    public function blocks(): array
    {
        return $this->blocks;
    }
}
