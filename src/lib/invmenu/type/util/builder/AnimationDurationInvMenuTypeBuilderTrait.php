<?php

declare(strict_types=1);

namespace MaXoooZ\CraftManager\invmenu\type\util\builder;

trait AnimationDurationInvMenuTypeBuilderTrait
{

    private int $animation_duration = 0;

    protected function getAnimationDuration(): int
    {
        return $this->animation_duration;
    }

    public function setAnimationDuration(int $animation_duration): self
    {
        $this->animation_duration = $animation_duration;
        return $this;
    }
}