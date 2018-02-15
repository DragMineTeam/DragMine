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

namespace pocketmine\entity;

use pocketmine\level\Level;
use pocketmine\level\sound\EndermanTeleportSound;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\Network;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\Player;

class EnderPearl extends Projectile{

	const NETWORK_ID = 87;

	public $width = 0.25;
	public $length = 0.25;
	public $height = 0.25;

	protected $gravity = 0.03;
	protected $drag = 0.01;

	private $player;
	private $hasTeleported = false;

	public function __construct(Level $level, CompoundTag $nbt, Entity $shootingEntity = null){
		parent::__construct($level, $nbt, $shootingEntity);
		if($shootingEntity instanceof Player){
			$this->setSpawner($shootingEntity);
		}
	}

	public function entityBaseTick(int $tickDiff = 1) : bool{
		if($this->closed){
			return false;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);

		if($this->age > 1200 or $this->isCollided){
			$this->teleportPlayer();
			$hasUpdate = true;
		}

		return $hasUpdate;
	}

	/** @return Player */
	public function getSpawner(){
		return $this->player;
	}

	public function setSpawner(Player $player){
		$this->player = $player;
	}

	public function onCollideWithEntity(Entity $entity){
		$this->teleportPlayer();
	}

	public function teleportPlayer(){
		if(!$this->hasTeleported){
			$this->hasTeleported = true;
			if(($this->getSpawner() instanceof Player) && ($this->y > 0)){
				$this->getLevel()->addSound(new EndermanTeleportSound($this));
				$this->getSpawner()->teleport($this->asPosition());
				if($this->getSpawner()->isSurvival()){
					$this->getSpawner()->attack(new EntityDamageEvent($this->getSpawner(), EntityDamageEvent::CAUSE_FALL, 4));
				}
			}
			$this->kill();
		}
	}

	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->type = self::NETWORK_ID;
		$pk->entityRuntimeId = $this->getId();
		$pk->position = $this->asVector3();
		$pk->motion = $this->getMotion();
		$pk->metadata = $this->dataProperties;
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}
}