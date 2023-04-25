<?php

declare(strict_types=1);

namespace MaXoooZ\CraftManager\lib\invmenu\type\util\builder;

use MaXoooZ\CraftManager\lib\invmenu\type\InvMenuType;

interface InvMenuTypeBuilder
{

    public function build(): InvMenuType;
}