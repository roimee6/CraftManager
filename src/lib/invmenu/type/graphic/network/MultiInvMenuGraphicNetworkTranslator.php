<?php

declare(strict_types=1);

namespace MaXoooZ\CraftManager\invmenu\type\graphic\network;

use MaXoooZ\CraftManager\invmenu\session\InvMenuInfo;
use MaXoooZ\CraftManager\invmenu\session\PlayerSession;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;

final class MultiInvMenuGraphicNetworkTranslator implements InvMenuGraphicNetworkTranslator
{

    /**
     * @param InvMenuGraphicNetworkTranslator[] $translators
     */
    public function __construct(
        private array $translators
    )
    {
    }

    public function translate(PlayerSession $session, InvMenuInfo $current, ContainerOpenPacket $packet): void
    {
        foreach ($this->translators as $translator) {
            $translator->translate($session, $current, $packet);
        }
    }
}