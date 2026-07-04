<?php

declare(strict_types=1);

namespace Aazsamir\PhpCar\Car;

use Aazsamir\PhpCar\Car\CID;
use Aazsamir\PhpCar\Bytes\Multihash;
use Aazsamir\PhpCar\Bytes\ByteReader;
use CBOR\Decoder as CBORDecoder;
use CBOR\StringStream;
use CBOR\Tag\Base16EncodingTag;
use CBOR\Tag\Base64EncodingTag;
use CBOR\Tag\Base64Tag;
use CBOR\Tag\Base64UrlEncodingTag;
use CBOR\Tag\Base64UrlTag;
use CBOR\Tag\BigFloatTag;
use CBOR\Tag\CBOREncodingTag;
use CBOR\Tag\CBORTag;
use CBOR\Tag\DatetimeTag;
use CBOR\Tag\DecimalFractionTag;
use CBOR\Tag\MimeTag;
use CBOR\Tag\NegativeBigIntegerTag;
use CBOR\Tag\TagManager;
use CBOR\Tag\TimestampTag;
use CBOR\Tag\UnsignedBigIntegerTag;
use CBOR\Tag\UriTag;

readonly class CarDecoder
{
    public function __construct(
        private CBORDecoder $decoder,
        private bool $verify = true,
    ) {}

    public static function new(bool $verify = true): self
    {
        return new self(
            CBORDecoder::create(
                self::tagManager(),
            ),
            $verify
        );
    }

    private static function tagManager(): TagManager
    {
        return TagManager::create([
            0 => DatetimeTag::class,
            1 => TimestampTag::class,
            2 => UnsignedBigIntegerTag::class,
            3 => NegativeBigIntegerTag::class,
            4 => DecimalFractionTag::class,
            5 => BigFloatTag::class,
            6 => Base64UrlEncodingTag::class,
            7 => Base64EncodingTag::class,
            8 => Base16EncodingTag::class,
            9 => CBOREncodingTag::class,
            10 => UriTag::class,
            11 => Base64UrlTag::class,
            12 => Base64Tag::class,
            13 => MimeTag::class,
            14 => CBORTag::class,
            42 => IpldTag::class,
        ]);
    }

    public function decode(string $data): CarData
    {
        $reader = new ByteReader($data);
        $headerLength = $reader->readVarint();
        $header = $this->decoder->decode(StringStream::create($reader->readBytes($headerLength)));
        $blocks = [];

        while (strlen($reader->remaining()) > 0) {
            $blockLength = $reader->readVarint();

            if ($blockLength === 0) {
                break;
            }

            $blockData = $reader->readBytes($blockLength);
            $cidLength = $this->parseCidLength($blockData);

            $cid = substr($blockData, 0, $cidLength);
            $cid = CID::fromBytes($cid);

            $dataPart = substr($blockData, $cidLength);
            $block = new CarBlock($this->decoder->decode(StringStream::create($dataPart)));

            $this->verify($cid, $block);

            $blocks[$cid->toString()] = $block;
        }

        return new CarData(
            new CarHeader($header),
            new CarBlocks($blocks),
        );
    }

    private function parseCidLength(string $cidData): int
    {
        if (strlen($cidData) < 2) {
            throw new CarException('CID data too short');
        }

        $firstByte = ord($cidData[0]);

        // handle CIDv1
        if ($firstByte === 0x01) {
            $reader = new ByteReader(substr($cidData, 1)); // skip version byte
            $reader->readVarint(); // codec
            $reader->readVarint(); // hash code
            $hashLength = $reader->readVarint();

            // version(1) + codec(varint) + hash code(varint) + hash length(varint)
            return 1 + $reader->getOffset() + $hashLength;
        }

        throw new CarException('Unsupported CID version');
    }

    private function verify(CID $cid, CarBlock $block): void
    {
        if (!$this->verify) {
            return;
        }

        $calculatedCid = new CID(
            version: 1,
            codec: 0x71,
            multihash: new Multihash(
                code: 0x12,
                length: 32,
                digest: \hash('sha256', (string) $block->cbor, true)
            ),
        );

        if ($cid->toString() !== $calculatedCid->toString()) {
            throw new CarException('CID mismatch');
        }
    }
}
