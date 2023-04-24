<?php

declare(strict_types=1);

namespace MaXoooZ\CraftManager\invmenu\type;

use MaXoooZ\CraftManager\invmenu\inventory\InvMenuInventory;
use MaXoooZ\CraftManager\invmenu\InvMenu;
use MaXoooZ\CraftManager\invmenu\type\graphic\BlockActorInvMenuGraphic;
use MaXoooZ\CraftManager\invmenu\type\graphic\BlockInvMenuGraphic;
use MaXoooZ\CraftManager\invmenu\type\graphic\InvMenuGraphic;
use MaXoooZ\CraftManager\invmenu\type\graphic\MultiBlockInvMenuGraphic;
use MaXoooZ\CraftManager\invmenu\type\graphic\network\InvMenuGraphicNetworkTranslator;
use MaXoooZ\CraftManager\invmenu\type\util\InvMenuTypeHelper;
use pocketmine\block\Block;
use pocketmine\block\tile\Chest;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\Inventory;
use pocketmine\math\Facing;
use pocketmine\player\Player;

final class DoublePairableBlockActorFixedInvMenuType implements FixedInvMenuType
{

    public function __construct(
        private Block                            $block,
        private int                              $size,
        private string                           $tile_id,
        private ?InvMenuGraphicNetworkTranslator $network_translator = null,
        private int                              $animation_duration = 0
    )
    {
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function createGraphic(InvMenu $menu, Player $player): ?InvMenuGraphic
    {
        $position = $player->getPosition();
        $origin = $position->addVector(InvMenuTypeHelper::getBehindPositionOffset($player))->floor();
        if (!InvMenuTypeHelper::isValidYCoordinate($origin->y)) {
            return null;
        }

        $graphics = [];
        $menu_name = $menu->getName();
        $world = $position->getWorld();
        foreach ([
                     [$origin, $origin->east(), [Facing::NORTH, Facing::SOUTH, Facing::WEST]],
                     [$origin->east(), $origin, [Facing::NORTH, Facing::SOUTH, Facing::EAST]]
                 ] as [$origin_pos, $pair_pos, $connected_sides]) {
            $graphics[] = new BlockActorInvMenuGraphic(
                $this->block,
                $origin_pos,
                BlockActorInvMenuGraphic::createTile($this->tile_id, $menu_name)
                    ->setInt(Chest::TAG_PAIRX, $pair_pos->x)
                    ->setInt(Chest::TAG_PAIRZ, $pair_pos->z),
                $this->network_translator,
                $this->animation_duration
            );
            foreach (InvMenuTypeHelper::findConnectedBlocks("Chest", $world, $origin_pos, $connected_sides) as $side) {
                $graphics[] = new BlockInvMenuGraphic(VanillaBlocks::BARRIER(), $side);
            }
        }

        return count($graphics) > 1 ? new MultiBlockInvMenuGraphic($graphics) : $graphics[0];
    }

    public function createInventory(): Inventory
    {
        return new InvMenuInventory($this->size);
    }
}