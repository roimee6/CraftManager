<?php

declare(strict_types=1);

namespace MaXoooZ\CraftManager\invmenu\type\graphic\network;

use MaXoooZ\CraftManager\invmenu\session\InvMenuInfo;
use MaXoooZ\CraftManager\invmenu\session\PlayerSession;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;

final class WindowTypeInvMenuGraphicNetworkTranslator implements InvMenuGraphicNetworkTranslator
{

    public function __construct(
        private int $window_type
    )
    {
    }

    public function translate(PlayerSession $session, InvMenuInfo $current, ContainerOpenPacket $packet): void
    {
        $packet->windowType = $this->window_type;
    }
}