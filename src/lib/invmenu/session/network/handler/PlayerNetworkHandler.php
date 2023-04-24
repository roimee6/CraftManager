<?php

declare(strict_types=1);

namespace MaXoooZ\CraftManager\invmenu\session\network\handler;

use Closure;
use MaXoooZ\CraftManager\invmenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler
{

    public function createNetworkStackLatencyEntry(Closure $then): NetworkStackLatencyEntry;
}