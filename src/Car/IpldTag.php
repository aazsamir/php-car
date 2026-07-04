<?php

declare(strict_types=1);

namespace Aazsamir\PhpCar\Car;

use Aazsamir\PhpCar\Car\CID;
use CBOR\ByteStringObject;
use CBOR\CBORObject;
use CBOR\Normalizable;
use CBOR\Tag;
use InvalidArgumentException;

final class IpldTag extends Tag implements Normalizable
{
    private CID $cid;
    private const int TAG_ID = 42;

    public function __construct(int $additionalInformation, ?string $data, CBORObject $object)
    {
        if (! $object instanceof ByteStringObject) {
            throw new InvalidArgumentException('This tag only accepts a Byte String object.');
        }
        parent::__construct($additionalInformation, $data, $object);
        $this->cid = CID::fromBytes($object->getValue());
    }

    public static function getTagId(): int
    {
        return self::TAG_ID;
    }

    public static function createFromLoadedData(int $additionalInformation, ?string $data, CBORObject $object): Tag
    {
        return new self($additionalInformation, $data, $object);
    }

    public static function create(CBORObject $object): Tag
    {
        [$ai, $data] = self::determineComponents(self::TAG_ID);

        return new self($ai, $data, $object);
    }

    public function normalize(): string
    {
        return $this->cid->toString();
    }

    public function getCid(): CID
    {
        return $this->cid;
    }
}
