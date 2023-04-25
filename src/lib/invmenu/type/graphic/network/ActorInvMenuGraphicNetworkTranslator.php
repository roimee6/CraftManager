<?php

declare(strict_types=1);

namespace MaXoooZ\CraftManager\lib\invmenu\type\graphic\network;

use MaXoooZ\CraftManager\lib\invmenu\session\InvMenuInfo;
use MaXoooZ\CraftManager\lib\invmenu\session\PlayerSession;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;

final class ActorInvMenuGraphicNetworkTranslator implements InvMenuGraphicNetworkTranslator
{

    public function __construct(
        private int $actor_runtime_id
    )
    {
    }

    public function translate(PlayerSession $session, InvMenuInfo $current, ContainerOpenPacket $packet): void
    {
        $packet->actorUniqueId = $this->actor_runtime_id;
        $packet->blockPosition = new BlockPosition(0, 0, 0);
    }
}