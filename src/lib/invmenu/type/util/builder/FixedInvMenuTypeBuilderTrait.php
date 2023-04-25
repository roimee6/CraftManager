<?php

declare(strict_types=1);

namespace MaXoooZ\CraftManager\lib\invmenu\type\util\builder;

use LogicException;

trait FixedInvMenuTypeBuilderTrait
{

    private ?int $size = null;

    protected function getSize(): int
    {
        return $this->size ?? throw new LogicException("No size was provided");
    }

    public function setSize(int $size): self
    {
        $this->size = $size;
        return $this;
    }
}