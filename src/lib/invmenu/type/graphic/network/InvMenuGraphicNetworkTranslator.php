<?php

declare(strict_types=1);

namespace MaXoooZ\CraftManager\lib\invmenu\type\graphic\network;

use MaXoooZ\CraftManager\lib\invmenu\session\InvMenuInfo;
use MaXoooZ\CraftManager\lib\invmenu\session\PlayerSession;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;

interface InvMenuGraphicNetworkTranslator
{

    public function translate(PlayerSession $session, InvMenuInfo $current, ContainerOpenPacket $packet): void;
}