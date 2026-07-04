<?php

declare(strict_types=1);

namespace Aazsamir\PhpCar\Bytes;

final readonly class Multihash
{
    public function __construct(
        public int $code,
        public int $length,
        public string $digest,
    ) {}

    public static function fromBytes(string $bytes): self
    {
        $reader = new ByteReader($bytes);

        $code = $reader->readVarint();
        $length = $reader->readVarint();

        $digest = $reader->remaining();

        if (\strlen($digest) !== $length) {
            throw new InvalidInput('Invalid multihash length');
        }

        return new self($code, $length, $digest);
    }
}
