<?php

namespace MaXoooZ\CraftManager;

use MaXoooZ\CraftManager\formapi\SimpleForm;
use MaXoooZ\CraftManager\invmenu\InvMenu;
use MaXoooZ\CraftManager\invmenu\transaction\InvMenuTransaction;
use MaXoooZ\CraftManager\invmenu\transaction\InvMenuTransactionResult;
use MaXoooZ\CraftManager\invmenu\type\InvMenuTypeIds;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\utils\Config;

class CraftManager extends Command
{
    private array $air;

    public function __construct()
    {
        $this->air = [
            12, 13, 14,
            19,
            21, 22, 23,
            30, 31, 32
        ];

        parent::__construct("craftmanager");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("Vous devez être connecté");
            return;
        } else if ($sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            $sender->sendMessage("§cVous n'avez pas la permission d'utiliser cette commande");
            return;
        }

        $form = new SimpleForm(function (Player $player, mixed $data) {
            if (!is_int($data)) {
                return;
            }

            switch ($data) {
                case 0:
                    $this->addCraftMenu($player);
                    break;
                case 1:
                    $this->removeAddedCraftMenu($player);
                    break;
                case 2:
                    $this->removeCraftMenu($player);
                    break;
                case 3:
                    $this->restoreRemovedCraftMenu($player);
                    break;
            }
        });
        $form->setTitle("CraftManager");
        $form->addButton("Add Craft");
        $form->addButton("Remove Added Craft");
        $form->addButton("Remove Craft");
        $form->addButton("Restore removed Craft");
        $sender->sendForm($form);
    }

    private function removeAddedCraftMenu(Player $player): void
    {
        
    }

    private function addCraftMenu(Player $player)
    {
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
        $menu->setName("Craft Manager");

        $air = $this->air;

        $menu->setListener(function (InvMenuTransaction $transaction) use ($menu, $air): InvMenuTransactionResult {
            $player = $transaction->getPlayer();
            $out = $transaction->getOut();

            $inventory = $transaction->getAction()->getInventory();

            if ($inventory === $menu->getInventory()) {
                if (in_array($transaction->getAction()->getSlot(), $air)) {
                    return $transaction->continue();
                }

                if (!is_null($out->getNamedTag()->getTag("confirm"))) {
                    $lines = [];
                    $i = 1;

                    foreach ($air as $slot) {
                        if ($slot === 19) {
                            continue;
                        }

                        $i++;
                        $lines[round($i / 3) - 1][] = $inventory->getItem($slot);
                    }

                    $this->addCraft($lines, $inventory->getItem(19));
                    Base::getInstance()->refreshCrafts(false);

                    $player->sendMessage("§aCraft ajouté");
                    $player->removeCurrentWindow();
                } else if (!is_null($out->getNamedTag()->getTag("cancel"))) {
                    $player->sendMessage("§aCraft annulé");
                    $player->removeCurrentWindow();
                }

                return $transaction->discard();
            }

            return $transaction->continue();
        });

        for ($i = 0; $i <= 53; $i++) {
            if (!in_array($i, $air)) {
                $menu->getInventory()->setItem($i, VanillaBlocks::IRON_BARS()->asItem()->setCustomName(""));
            }
        }

        $confirm = VanillaBlocks::CONCRETE_POWDER()->setColor(DyeColor::GREEN())->asItem();
        $cancel = VanillaBlocks::CONCRETE_POWDER()->setColor(DyeColor::RED())->asItem();

        $confirm->getNamedTag()->setInt("confirm", 1);
        $cancel->getNamedTag()->setInt("cancel", 1);

        $menu->getInventory()->setItem(34, $confirm->setCustomName("§aConfirmer"));
        $menu->getInventory()->setItem(43, $cancel->setCustomName("§cAnnuler"));

        $menu->send($player);
    }

    private function addCraft(array $lines, Item $result): void
    {
        $crafts = new Config(Base::getInstance()->getDataFolder() . "crafts.json", Config::JSON);

        $alphabet = "ABCDEFGHI";
        $inputIndex = 0;

        $shape = [];
        $input = [];

        foreach ($lines as $shapeIndex => $items) {
            foreach ($items as $item) {
                if ($item instanceof Item && $item->getId() !== 0) {
                    $letter = $alphabet[$inputIndex];
                    $input[$letter] = $item->getId() . ":" . $item->getMeta();

                    $inputIndex++;
                } else {
                    $letter = " ";
                }

                $shape[$shapeIndex] = ($shape[$shapeIndex] ?? "") . $letter;
            }
        }

        foreach ($shape as $index => $line) {
            if ($line === "") {
                unset($shape[$index]);
            }

            $shape[$index] = rtrim($line);
        }

        $new = $crafts->get("new", []);

        $new[] = [
            "shape" => $shape,
            "input" => $input,
            "output" => $result->getId() . ":" . $result->getMeta() . ":" . $result->getCount(),
        ];

        $crafts->set("new", $new);
        $crafts->save();
    }
}