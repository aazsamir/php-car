<?php

declare(strict_types=1);

namespace Aazsamir\PhpCar;

use CBOR\CBORObject;
use PhpParser\Node\Stmt\Block;

class CarData
{
    /**
     * @param array<string, CarBlock> $blocks
     */
    public function __construct(
        private CarHeader $header,
        private array $blocks,
    ) {}

    public function header(): CarHeader
    {
        return $this->header;
    }

    /**
     * @return array<string, CarBlock>
     */
    public function blocks(): array
    {
        return $this->blocks;
    }

    public function toArray(): array
    {
        return [
            'header' => $this->header()->toArray(),
            'blocks' => array_map(fn (CarBlock $block) => $block->toArray(), $this->blocks)
        ];
    }
}
