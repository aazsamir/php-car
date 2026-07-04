<?php

declare(strict_types=1);

namespace Aazsamir\PhpCar\Bytes;

final class ByteReader
{
    private int $offset = 0;

    public function __construct(private readonly string $bytes) {}

    public function readVarint(): int
    {
        $result = 0;
        $shift = 0;

        while (true) {
            if (!isset($this->bytes[$this->offset])) {
                throw new UnexpectedEof('Unexpected EOF while reading varint');
            }

            $byte = \ord($this->bytes[$this->offset++]);
            $result |= ($byte & 0x7F) << $shift;

            if (($byte & 0x80) === 0) {
                break;
            }

            $shift += 7;

            if ($shift > 63) {
                throw new VarintTooLarge('Varint too large');
            }
        }

        return $result;
    }

    public function readBytes(int $length): string
    {
        $data = substr($this->bytes, $this->offset, $length);

        if (\strlen($data) !== $length) {
            throw new UnexpectedEof('Unexpected EOF while reading bytes');
        }

        $this->offset += $length;

        return $data;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function remaining(): string
    {
        return substr($this->bytes, $this->offset);
    }

    public function reset(): void
    {
        $this->offset = 0;
    }
}
