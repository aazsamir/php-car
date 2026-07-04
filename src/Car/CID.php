<?php

declare(strict_types=1);

namespace Aazsamir\PhpCar\Car;

use Aazsamir\PhpCar\Bytes\Multibase;
use Aazsamir\PhpCar\Bytes\Varint;
use Aazsamir\PhpCar\Bytes\ByteReader;
use Aazsamir\PhpCar\Bytes\Multihash;

final readonly class CID
{
    public function __construct(
        public int $version,
        public int $codec,
        public Multihash $multihash
    ) {}

    public static function fromBytes(string $bytes): self
    {
        // CAR/IPLD prefix byte
        if (isset($bytes[0]) && $bytes[0] === "\x00") {
            $bytes = substr($bytes, 1);
        }

        $reader = new ByteReader($bytes);

        $version = $reader->readVarint();
        $codec = $reader->readVarint();
        $mh = $reader->remaining();

        return new self(
            $version,
            $codec,
            Multihash::fromBytes($mh),
        );
    }

    public static function fromString(string $cid): self
    {
        $decoded = Multibase::decode($cid);

        $reader = new ByteReader($decoded);

        $version = $reader->readVarint();
        $codec = $reader->readVarint();
        $mh = $reader->remaining();

        return new self(
            $version,
            $codec,
            Multihash::fromBytes($mh)
        );
    }

    public function toString(): string
    {
        $bytes =
            Varint::encode($this->version) .
            Varint::encode($this->codec) .
            Varint::encode($this->multihash->code) .
            Varint::encode($this->multihash->length) .
            $this->multihash->digest;

        return Multibase::encodeBase32($bytes);
    }

    public function equals(self $other): bool
    {
        return
            $this->version === $other->version &&
            $this->codec === $other->codec &&
            $this->multihash === $other->multihash;
    }
}
