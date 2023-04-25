<?php /** @noinspection PhpUnused */

namespace MaXoooZ\CraftManager;

use MaXoooZ\CraftManager\lib\invmenu\InvMenuHandler;
use pocketmine\crafting\CraftingRecipe;
use pocketmine\crafting\ShapedRecipe;
use pocketmine\crafting\ShapelessRecipe;
use pocketmine\item\ItemFactory;
use pocketmine\network\mcpe\cache\CraftingDataCache;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use ReflectionClass;
use ReflectionProperty;

class Base extends PluginBase
{
    use SingletonTrait;

    /**
     * @var CraftingRecipe[]
     * @phpstan-var array<int, CraftingRecipe>
     */
    public array $recipes;

    protected function onLoad(): void
    {
        self::setInstance($this);
        $this->saveDefaultConfig();
    }

    public function getCraftsFile(): Config
    {
        return new Config($this->getDataFolder() . "crafts.json", Config::JSON);
    }

    protected function onEnable(): void
    {
        $craftMgr = $this->getServer()->getCraftingManager();
        $this->recipes = $craftMgr->getCraftingRecipeIndex();

        $this->refreshCrafts(true);

        if ($this->getConfig()->getNested("craft-manager.enable")) {
            if (!InvMenuHandler::isRegistered()) {
                InvMenuHandler::register($this);
            }

            $this->getServer()->getCommandMap()->register("craftmanager", new CraftManager());
        }
    }

    public function refreshCrafts(bool $start): void
    {
        $craftMgr = $this->getServer()->getCraftingManager();
        $crafts = $this->getCraftsFile();

        $reflectionClass = new ReflectionClass($craftMgr);

        $recipes = $this->recipes;
        $newRecipes = [];

        $delete = $crafts->get("delete", []);
        $new = $crafts->get("new", []);

        foreach ($recipes as $recipe) {
            $valid = true;

            if ($recipe instanceof ShapedRecipe || $recipe instanceof ShapelessRecipe) {
                foreach ($recipe->getResults() as $item) {
                    foreach ($delete as $value) {
                        $split = explode(":", $value);

                        $id = $split[0];
                        $meta = $split[1] ?? 0;

                        if ($item->getId() == $id) {
                            if (0 > $meta && $meta === $item->getId()) {
                                continue;
                            }

                            $valid = false;
                        }
                    }
                }
            }

            if ($valid) {
                $newRecipes[] = $recipe;
            }
        }

        foreach ($reflectionClass->getProperties(ReflectionProperty::IS_PRIVATE) as $property) {
            if ($property->getName() === "craftingRecipeIndex") {
                $property->setAccessible(true);
                $property->setValue($craftMgr, $newRecipes);
                $property->setAccessible(false);
            }
        }

        foreach ($new as $value) {
            $input = array_map(function (string $data) {
                $split = explode(":", $data);
                return ItemFactory::getInstance()->get($split[0] ?? 0, $split[1] ?? 0, $split[2] ?? 1);
            }, $value["input"]);

            $split = explode(":", $value["output"]);
            $result = ItemFactory::getInstance()->get($split[0] ?? 0, $split[1] ?? 0, $split[2] ?? 1);

            $maxLength = max(array_map("strlen", $value["shape"]));

            foreach ($value["shape"] as $key => $line) {
                $length = strlen($line);

                if ($maxLength > strlen($length)) {
                    $value["shape"][$key] = $line . str_repeat(" ", $maxLength - $length);
                }
            }

            $craftMgr->registerShapedRecipe(new ShapedRecipe(
                $value["shape"],
                $input,
                [$result]
            ));
        }

        if (!$start) {
            foreach ($this->getServer()->getOnlinePlayers() as $player) {
                $session = $player->getNetworkSession();
                $session->sendDataPacket(CraftingDataCache::getInstance()->getCache($craftMgr));
            }
        }
    }
}