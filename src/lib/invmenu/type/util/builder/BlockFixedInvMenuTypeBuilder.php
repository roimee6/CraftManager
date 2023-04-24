<?php

declare(strict_types=1);

namespace MaXoooZ\CraftManager\invmenu\type\util\builder;

use MaXoooZ\CraftManager\invmenu\type\BlockFixedInvMenuType;
use MaXoooZ\CraftManager\invmenu\type\graphic\network\BlockInvMenuGraphicNetworkTranslator;

final class BlockFixedInvMenuTypeBuilder implements InvMenuTypeBuilder
{
    use BlockInvMenuTypeBuilderTrait;
    use FixedInvMenuTypeBuilderTrait;
    use GraphicNetworkTranslatableInvMenuTypeBuilderTrait;

    public function __construct()
    {
        $this->addGraphicNetworkTranslator(BlockInvMenuGraphicNetworkTranslator::instance());
    }

    public function build(): BlockFixedInvMenuType
    {
        return new BlockFixedInvMenuType($this->getBlock(), $this->getSize(), $this->getGraphicNetworkTranslator());
    }
}