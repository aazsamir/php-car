<?php

declare(strict_types=1);

namespace Aazsamir\PhpCar\Car;

readonly class CarData
{
    public function __construct(
        public CarHeader $header,
        public CarBlocks $blocks,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'header' => $this->header->toArray(),
            'blocks' => array_map(static fn (CarBlock $block) => $block->toArray(), $this->blocks->items()),
        ];
    }
}
