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

use pocketmine\entity\Living;
use pocketmine\item\Potion;
use pocketmine\level\Level;
use pocketmine\level\particle\SplashPotionParticle;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;

class ThrownPotion extends Projectile{

	const NETWORK_ID = 86;

	const DATA_POTION_ID = 37;

	public $width = 0.25;
	public $length = 0.25;
	public $height = 0.25;

	protected $gravity = 0.1;
	protected $drag = 0.05;

	private $hasSplashed = false;

	/**
	 * ThrownPotion constructor.
	 *
	 * @param Level       $level
	 * @param CompoundTag $nbt
	 * @param Entity|null $shootingEntity
	 */
	public function __construct(Level $level, CompoundTag $nbt, Entity $shootingEntity = null){
		if(!isset($nbt->PotionId)){
			$nbt->PotionId = new ShortTag("PotionId", Potion::AWKWARD);
		}

		parent::__construct($level, $nbt, $shootingEntity);

		unset($this->dataProperties[self::DATA_SHOOTER_ID]);
		$this->setDataProperty(self::DATA_POTION_ID, self::DATA_TYPE_SHORT, $this->getPotionId());
	}

	/**
	 * @return int
	 */
	public function getPotionId() : int{
		return (int) $this->namedtag["PotionId"];
	}

	public function splash(){
		if (!$this->hasSplashed) {
			$this->hasSplashed = true;
			$color = Potion::getColor($this->getPotionId());
			$this->getLevel()->addParticle(new SplashPotionParticle($this, $color[0], $color[1], $color[2]));
			$players = $this->getViewers();
			foreach ($players as $p) {
				if ($p->distance($this) <= 6) {
					foreach (Potion::getEffectsById($this->getPotionId()) as $effect) {
						$p->addEffect($effect);
					}
				}
			}
			$this->kill();
		}
	}

	public function onCollideWithEntity(Entity $entity){
		$this->splash();
	}

	/**
	 * @param $currentTick
	 *
	 * @return bool
	 */
	public function entityBaseTick(int $tickDiff = 1) : bool{
		if($this->closed){
			return false;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);

		if($this->age > 1200 or $this->isCollided){
			$this->splash();
			$hasUpdate = true;
		}

		return $hasUpdate;
	}

	/**
	 * @param Player $player
	 */
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