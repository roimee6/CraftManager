<?php

declare(strict_types=1);

namespace MaXoooZ\CraftManager\lib\invmenu\session;

use MaXoooZ\CraftManager\lib\invmenu\InvMenu;
use MaXoooZ\CraftManager\lib\invmenu\type\graphic\InvMenuGraphic;

final class InvMenuInfo
{

    public function __construct(
        public InvMenu        $menu,
        public InvMenuGraphic $graphic,
        public ?string        $graphic_name
    )
    {
    }
}