<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\entity;

use pocketmine\entity\Entity;
use pocketmine\item\FireworkRocket;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Random;

class FireworksRocket extends Projectile{

    public const NETWORK_ID = 72;

    public const DATA_FIREWORK_ITEM = 16;

    public $width = 0.25;
    public $height = 0.25;

    protected $gravity = 0.0;
    protected $drag = 0.1;

    /** @var FireworkRocket */
    public $fireworksItem;
    /** @var int */
    public $lifeTime;

    public function __construct(Level $level, CompoundTag $nbt, Entity $shootingEntity = null, FireworkRocket $fireworks, Random $random = null){
        $this->fireworksItem = $fireworks;
        $random = $random ?? new Random();

        $flyTime = 1;
        $lifeTime = null;

        try{
            if(isset($nbt->Fireworks)){
                $fireworkCompound = $nbt->Fireworks;
                $flyTime = $fireworkCompound->Flight ?? 1;
                //$lifeTime = $fireworkCompound->LifeTime ?? 20 * $flyTime;
                $lifeTime = 20;
            }
        }catch(\Exception $exception){
            Server::getInstance()->getLogger()->debug($exception);
        }

        $this->lifeTime = $lifeTime ?? 20;

        $nbt->Life = new IntTag("Life", $this->lifeTime);
        $nbt->LifeTime = new IntTag("LifeTime", $this->lifeTime);

        parent::__construct($level, $nbt, $shootingEntity);
    }

    protected function initEntity(){
        $this->setGenericFlag(self::DATA_FLAG_AFFECTED_BY_GRAVITY, true);
        $this->setGenericFlag(self::DATA_FLAG_HAS_COLLISION, true);

        parent::initEntity();
    }

    public function spawnTo(Player $player){
        $this->setMotion($this->getDirectionVector());
        //$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_LAUNCH);

        $pk = new AddEntityPacket();
        $pk->type = self::NETWORK_ID;
        $pk->entityRuntimeId = $this->getId();
        $pk->position = $this->asVector3();
        $pk->motion = $this->getMotion();
        $pk->metadata = $this->dataProperties;
        $player->dataPacket($pk);

        parent::spawnTo($player);
    }

    public function kill(){
        //$this->broadcastEntityEvent(EntityEventPacket::FIREWORK_PARTICLES, 0);
        //$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_BLAST);
        parent::kill();
    }

    public function entityBaseTick(int $tickDiff = 1) : bool{
        if($this->closed){
            return false;
        }

        if($this->lifeTime-- <= 0)
            $this->kill();
        else
            return parent::entityBaseTick($tickDiff);

        return true;
    }
}