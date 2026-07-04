<?php

declare(strict_types=1);

namespace Aazsamir\PhpCar\Car;

readonly class CarBlocks
{
    /**
     * @param array<string, CarBlock> $items
     */
    public function __construct(
        public array $items,
    ) {}

    /**
     * @return array<string, CarBlock>
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_map(fn (CarBlock $block) => $block->toArray(), $this->items);
    }
}
