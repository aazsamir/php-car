<?php

declare(strict_types=1);

namespace Aazsamir\PhpCar\Car;

use CBOR\ListObject;
use CBOR\MapObject;

readonly class CarHeader
{
    public function __construct(
        public MapObject $cbor,
    ) {}

    public function cborRoots(): ListObject
    {
        if (!$this->cbor->has('roots')) {
            throw new CarException('Missing roots in CAR header');
        }

        if (!$this->cbor->get('roots') instanceof ListObject) {
            throw new CarException('Invalid roots in CAR header');
        }

        return $this->cbor->get('roots');
    }

    /**
     * @return string[]
     */
    public function roots(): array
    {
        $roots = [];

        foreach ($this->cborRoots() as $root) {
            if (!$root instanceof IpldTag) {
                throw new CarException('Expected IpldTag, got ' . $root::class);
            }

            $roots[] = $root->normalize();
        }

        return $roots;
    }

    public function toArray(): mixed
    {
        return $this->cbor->normalize();
    }
}
