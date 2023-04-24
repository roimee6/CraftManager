<?php

declare(strict_types=1);

namespace MaXoooZ\CraftManager\invmenu\type\util\builder;

use MaXoooZ\CraftManager\invmenu\type\InvMenuType;

interface InvMenuTypeBuilder
{

    public function build(): InvMenuType;
}