<?php

declare(strict_types=1);

namespace Aazsamir\PhpCar;

use CBOR\ByteStringObject;
use CBOR\CBORObject;
use CBOR\ListObject;
use CBOR\MapObject;
use CBOR\NegativeIntegerObject;
use CBOR\OtherObject\NullObject;
use CBOR\Tag;
use CBOR\TextStringObject;
use CBOR\UnsignedIntegerObject;

class CBORToArray
{
    public static function toArray(CBORObject $object): mixed
    {
        $data = [];

        switch (true) {
            case $object instanceof ByteStringObject:
                return bin2hex((string) $object);

            case $object instanceof ListObject:
                foreach ($object as $item) {
                    $data[] = self::toArray($item);
                }

            case $object instanceof MapObject:
                foreach ($object as $key => $value) {
                    // $key = self::toArray($value->getKey());
                    $data[$key] = self::toArray($value->getValue());
                }

                return $data;

            case $object instanceof TextStringObject:
                return $object->getValue();

            case $object instanceof NegativeIntegerObject:
            case $object instanceof UnsignedIntegerObject:
                return (int) $object->getValue();

            case $object instanceof Tag:
                return self::toArray($object->getValue());

            case $object instanceof NullObject:
                return null;
                
            default:
                dump([$object::class, (string) $object, $object]);
                return (string) $object;
        }

        return $data;
    }
}