<?php

declare(strict_types=1);

namespace MaXoooZ\CraftManager\formapi;

use pocketmine\form\Form as IForm;
use pocketmine\player\Player;

abstract class Form implements IForm
{

    /** @var array */
    protected $data = [];
    /** @var callable|null */
    private $callable;

    /**
     * @param callable|null $callable
     */
    public function __construct(?callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     * @param Player $player
     * @see Player::sendForm()
     *
     * @deprecated
     */
    public function sendToPlayer(Player $player): void
    {
        $player->sendForm($this);
    }

    public function handleResponse(Player $player, $data): void
    {
        $this->processData($data);
        $callable = $this->getCallable();
        if ($callable !== null) {
            $callable($player, $data);
        }
    }

    public function processData(&$data): void
    {
    }

    public function getCallable(): ?callable
    {
        return $this->callable;
    }

    public function setCallable(?callable $callable)
    {
        $this->callable = $callable;
    }

    public function jsonSerialize()
    {
        return $this->data;
    }
}
