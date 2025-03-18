<?php

declare(strict_types=1);

namespace Aazsamir\PhpCar;

use CBOR\Decoder as CBORDecoder;
use CBOR\StringStream;

class Decoder
{
    public function __construct(
        private CBORDecoder $decoder,
    ) {}

    public static function new(): self
    {
        return new self(CBORDecoder::create());
    }

    public function decode(string $data): CarData
    {
        $offset = 0;
        $header = $this->readVar($data, $offset);
        $header = $this->decoder->decode(StringStream::create($header));
        $blocks = [];

        while ($offset < strlen($data)) {
            $blockLength = $this->readVarInt($data, $offset);

            if ($blockLength === 0) {
                break;
            }

            $blockData = substr($data, $offset, $blockLength);

            if (strlen($blockData) !== $blockLength) {
                throw new \RuntimeException('Truncated block data');
            }

            $offset += $blockLength;

            // parse CID and data from blockData
            $cidLength = $this->parseCidLength($blockData);

            $cid = substr($blockData, 0, $cidLength);
            $dataPart = substr($blockData, $cidLength);

            $blocks[bin2hex($cid)] = $dataPart;
        }

        dd($blocks);

        $decoded = [];

        // decode each block data
        foreach ($blocks as $cid => $data) {
            $decoded[] = new CarBlock($this->decoder->decode(StringStream::create($data)));
        }

        return new CarData(
            new CarHeader($header),
            $decoded
        );
    }

    private function readVar(string $data, int &$offset = 0): string
    {
        $length = $this->readVarInt($data, $offset);
        $var = substr($data, $offset, $length);
        $offset += $length;

        return $var;
    }

    private function readVarInt(string $data, int &$offset = 0): int
    {
        $value = 0;
        $shift = 0;
        $i = $offset;

        while (isset($data[$i])) {
            $byte = ord($data[$i]);
            $i++;
            $value |= ($byte & 0x7F) << $shift;

            if (($byte & 0x80) === 0) {
                break;
            }

            $shift += 7;
        }

        $offset = $i;

        return $value;
    }

    private function parseCidLength(string $cidData): int
    {
        if (strlen($cidData) < 2) {
            throw new \RuntimeException('CID data too short');
        }

        $firstByte = ord($cidData[0]);

        // handle CIDv0
        if ($firstByte === 0x12) { // SHA2-256
            if (strlen($cidData) < 34) {
                throw new \RuntimeException('CIDv0 too short');
            }
            $secondByte = ord($cidData[1]);
            if ($secondByte === 0x20) { // 32 bytes
                return 34;
            }
            throw new \RuntimeException('Invalid CIDv0 length');
        }

        // handle CIDv1
        if ($firstByte === 0x01) {
            $offset = 1;
            $this->readVarInt($cidData, $offset); // read codec (varint)
            $multihash = substr($cidData, $offset);

            if ($multihash === false) {
                throw new \RuntimeException('Invalid multihash');
            }

            $hashOffset = 0;
            $this->readVarInt($multihash, $hashOffset); // hash code
            $hashLength = $this->readVarInt($multihash, $hashOffset); // hash length

            // total CID length: version(1) + codec varint length + multihash bytes
            $codecVarintLength = $offset - 1;
            $multihashLength = $hashOffset + $hashLength;

            return 1 + $codecVarintLength + $multihashLength;
        }

        throw new \RuntimeException('Unsupported CID version');
    }
}
