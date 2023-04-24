<?php

declare(strict_types=1);

namespace MaXoooZ\CraftManager\invmenu\type;

use MaXoooZ\CraftManager\invmenu\InvMenu;
use MaXoooZ\CraftManager\invmenu\type\graphic\InvMenuGraphic;
use pocketmine\inventory\Inventory;
use pocketmine\player\Player;

interface InvMenuType
{

    public function createGraphic(InvMenu $menu, Player $player): ?InvMenuGraphic;

    public function createInventory(): Inventory;
}