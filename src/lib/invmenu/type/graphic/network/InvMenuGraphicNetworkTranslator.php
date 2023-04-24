<?php

declare(strict_types=1);

namespace MaXoooZ\CraftManager\invmenu\type\graphic\network;

use MaXoooZ\CraftManager\invmenu\session\InvMenuInfo;
use MaXoooZ\CraftManager\invmenu\session\PlayerSession;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;

interface InvMenuGraphicNetworkTranslator
{

    public function translate(PlayerSession $session, InvMenuInfo $current, ContainerOpenPacket $packet): void;
}