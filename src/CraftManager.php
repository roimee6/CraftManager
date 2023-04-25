<?php

namespace MaXoooZ\CraftManager;

use MaXoooZ\CraftManager\lib\formapi\SimpleForm;
use MaXoooZ\CraftManager\lib\invmenu\InvMenu;
use MaXoooZ\CraftManager\lib\invmenu\transaction\InvMenuTransaction;
use MaXoooZ\CraftManager\lib\invmenu\transaction\InvMenuTransactionResult;
use MaXoooZ\CraftManager\lib\invmenu\type\InvMenuTypeIds;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\VanillaItems;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;

class CraftManager extends Command
{
    private array $air;

    public function __construct()
    {
        $this->air = [
            19,
            12, 13, 14,
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
        } else if (!$sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
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
                    $this->removeAddedCraftForm($player);
                    break;
                case 2:
                    $this->removeCraftMenu($player);
                    break;
                case 3:
                    $this->restoreRemovedCraftForm($player);
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

    private function restoreRemovedCraftForm(Player $player): void
    {
        $crafts = Base::getInstance()->getCraftsFile();

        $form = new SimpleForm(function (Player $player, mixed $data) {
            if (!is_int($data)) {
                return;
            }

            $this->restoreRemovedCraftMenu($player, $data);
        });

        foreach ($crafts->get("delete", []) as $item) {
            list($id, $meta) = explode(":", $item);
            $item = ItemFactory::getInstance()->get($id, $meta);

            $form->addButton($item->getName());
        }

        $form->setTitle("Restore Removed Craft");
        $player->sendForm($form);
    }

    private function restoreRemovedCraftMenu(Player $player, int $key): void
    {
        $file = Base::getInstance()->getCraftsFile();
        $craft = $file->get("delete")[$key];

        $menu = InvMenu::create(InvMenuTypeIds::TYPE_CHEST);
        $menu->setName("Craft Manager");

        $menu->setListener(function (InvMenuTransaction $transaction) use ($menu, $key): InvMenuTransactionResult {
            $player = $transaction->getPlayer();
            $out = $transaction->getOut();

            $inventory = $transaction->getAction()->getInventory();

            if ($inventory !== $menu->getInventory()) {
                return $transaction->continue();
            }

            if (!is_null($out->getNamedTag()->getTag("confirm"))) {
                $file = Base::getInstance()->getCraftsFile();
                $deleted = $file->get("delete", []);

                unset($deleted[$key]);

                $file->set("delete", $deleted);
                $file->save();

                Base::getInstance()->refreshCrafts(false);

                $player->sendMessage("§aRestoration du Craft succès");
                $player->removeCurrentWindow();
            } else if (!is_null($out->getNamedTag()->getTag("cancel"))) {
                $player->sendMessage("§aRestoration du Craft annulé");
                $player->removeCurrentWindow();
            }

            return $transaction->discard();
        });

        for ($i = 0; $i <= 26; $i++) {
            $menu->getInventory()->setItem($i, VanillaBlocks::IRON_BARS()->asItem()->setCustomName(" "));
        }

        list($id, $meta) = explode(":", $craft);
        $item = ItemFactory::getInstance()->get($id, $meta);

        $menu->getInventory()->setItem(11, $item);

        $confirm = VanillaBlocks::CONCRETE_POWDER()->setColor(DyeColor::RED())->asItem();
        $cancel = VanillaBlocks::CONCRETE_POWDER()->setColor(DyeColor::GREEN())->asItem();

        $confirm->getNamedTag()->setInt("confirm", 1);
        $cancel->getNamedTag()->setInt("cancel", 1);

        $menu->getInventory()->setItem(14, $confirm->setCustomName("§cRestorer le craft"));
        $menu->getInventory()->setItem(15, $cancel->setCustomName("§aAnnuler la restoration"));

        $menu->send($player);
    }

    private function removeCraftMenu(Player $player): void
    {
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_CHEST);
        $menu->setName("Craft Manager");

        $menu->setListener(function (InvMenuTransaction $transaction) use ($menu): InvMenuTransactionResult {
            $player = $transaction->getPlayer();
            $out = $transaction->getOut();

            $inventory = $transaction->getAction()->getInventory();

            if ($inventory !== $menu->getInventory()) {
                return $transaction->continue();
            }

            if ($transaction->getAction()->getSlot() === 11) {
                return $transaction->continue();
            }

            if (!is_null($out->getNamedTag()->getTag("confirm"))) {
                $file = Base::getInstance()->getCraftsFile();
                $deleted = $file->get("delete", []);

                $item = $inventory->getItem(11);
                $deleted[] = $item->getId() . ":" . $item->getMeta();

                $file->set("delete", $deleted);
                $file->save();

                Base::getInstance()->refreshCrafts(false);

                $player->sendMessage("§aSupression du Craft succès");
                $player->removeCurrentWindow();
            } else if (!is_null($out->getNamedTag()->getTag("cancel"))) {
                $player->sendMessage("§asupression du Craft annulé");
                $player->removeCurrentWindow();
            }

            return $transaction->discard();
        });

        for ($i = 0; $i <= 26; $i++) {
            $menu->getInventory()->setItem($i, VanillaBlocks::IRON_BARS()->asItem()->setCustomName(" "));
        }

        $menu->getInventory()->setItem(11, VanillaItems::AIR());

        $confirm = VanillaBlocks::CONCRETE_POWDER()->setColor(DyeColor::GREEN())->asItem();
        $cancel = VanillaBlocks::CONCRETE_POWDER()->setColor(DyeColor::RED())->asItem();

        $confirm->getNamedTag()->setInt("confirm", 1);
        $cancel->getNamedTag()->setInt("cancel", 1);

        $menu->getInventory()->setItem(14, $confirm->setCustomName("§aConfirmer"));
        $menu->getInventory()->setItem(15, $cancel->setCustomName("§cAnnuler"));

        $menu->send($player);
    }

    private function removeAddedCraftForm(Player $player): void
    {
        $crafts = Base::getInstance()->getCraftsFile();

        $form = new SimpleForm(function (Player $player, mixed $data) {
            if (!is_int($data)) {
                return;
            }

            $this->removeAddedCraftMenu($player, $data);
        });

        foreach ($crafts->get("new", []) as $craft) {
            list($id, $meta,) = explode(":", $craft["output"]);
            $item = ItemFactory::getInstance()->get($id, $meta);

            $form->addButton($item->getName());
        }

        $form->setTitle("Remove Craft");
        $player->sendForm($form);
    }

    private function removeAddedCraftMenu(Player $player, int $key): void
    {
        $file = Base::getInstance()->getCraftsFile();

        $menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
        $menu->setName("Craft Manager");

        $craft = $file->get("new", [])[$key];

        $menu->setListener(function (InvMenuTransaction $transaction) use ($menu, $key): InvMenuTransactionResult {
            $player = $transaction->getPlayer();
            $out = $transaction->getOut();

            $inventory = $transaction->getAction()->getInventory();

            if ($inventory !== $menu->getInventory()) {
                return $transaction->continue();
            }

            if (!is_null($out->getNamedTag()->getTag("confirm"))) {
                $file = Base::getInstance()->getCraftsFile();
                $crafts = $file->get("new", []);

                unset($crafts[$key]);

                $file->set("new", $crafts);
                $file->save();

                Base::getInstance()->refreshCrafts(false);

                $player->sendMessage("§aCraft supprimé");
                $player->removeCurrentWindow();
            } else if (!is_null($out->getNamedTag()->getTag("cancel"))) {
                $player->sendMessage("§aSupression annulé");
                $player->removeCurrentWindow();
            }

            return $transaction->discard();
        });

        for ($i = 0; $i <= 53; $i++) {
            if (!in_array($i, $this->air)) {
                $menu->getInventory()->setItem($i, VanillaBlocks::IRON_BARS()->asItem()->setCustomName(" "));
            }
        }

        $i = 0;

        foreach ($this->air as $slot) {
            if ($slot === 19) {
                list($id, $meta, $count) = explode(":", $craft["output"]);
                $item = ItemFactory::getInstance()->get($id, $meta, $count);

                $menu->getInventory()->setItem($slot, $item);
                continue;
            }

            $line = floor($i / 3);
            $letterIndex = round((($i / 3) - $line) * 3);

            $line = $craft["shape"][$line] ?? "";
            $letter = $line[$letterIndex] ?? " ";

            if ($letter === " ") {
                $item = VanillaItems::AIR();
            } else {
                list($id, $meta) = explode(":", $craft["input"][$letter]);
                $item = ItemFactory::getInstance()->get($id, $meta);
            }

            $menu->getInventory()->setItem($slot, $item);
            $i++;
        }

        $confirm = VanillaBlocks::CONCRETE_POWDER()->setColor(DyeColor::RED())->asItem();
        $cancel = VanillaBlocks::CONCRETE_POWDER()->setColor(DyeColor::GREEN())->asItem();

        $confirm->getNamedTag()->setInt("confirm", 1);
        $cancel->getNamedTag()->setInt("cancel", 1);

        $menu->getInventory()->setItem(34, $confirm->setCustomName("§cSupprimer"));
        $menu->getInventory()->setItem(43, $cancel->setCustomName("§aAnnuler la supression"));

        $menu->send($player);
    }

    private function addCraftMenu(Player $player)
    {
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
        $menu->setName("Craft Manager");

        $menu->setListener(function (InvMenuTransaction $transaction) use ($menu): InvMenuTransactionResult {
            $player = $transaction->getPlayer();
            $out = $transaction->getOut();

            $inventory = $transaction->getAction()->getInventory();

            if ($inventory === $menu->getInventory()) {
                if (in_array($transaction->getAction()->getSlot(), $this->air)) {
                    return $transaction->continue();
                }

                if (!is_null($out->getNamedTag()->getTag("confirm"))) {
                    $lines = [];
                    $i = 1;

                    foreach ($this->air as $slot) {
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
            if (!in_array($i, $this->air)) {
                $menu->getInventory()->setItem($i, VanillaBlocks::IRON_BARS()->asItem()->setCustomName(" "));
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
        $crafts = Base::getInstance()->getCraftsFile();

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