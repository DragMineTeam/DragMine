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

namespace pocketmine\item;

use pocketmine\Player;
use pocketmine\math\Vector3;
use pocketmine\entity\Entity;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ShortTag;

class SplashPotion extends Item{

	public function __construct($meta = 0){
		parent::__construct(self::SPLASH_POTION, $meta, $this->getNameByMeta($meta));
	}

	public function getMaxStackSize() : int{
		return 1;
	}

	public function getNameByMeta($meta){
		return "Splash " . Potion::getNameByMeta($meta);
	}

	public function onClickAir(Player $player, Vector3 $directionVector) : bool{
		$yawRad = $player->yaw / 180 * M_PI;
		$pitchRad = $player->pitch / 180 * M_PI;
		$nbt = new CompoundTag("", [
			"Pos" => new ListTag("Pos", [
				new DoubleTag("", $player->x),
				new DoubleTag("", $player->y + $player->getEyeHeight()),
				new DoubleTag("", $player->z)
			]),
			"Motion" => new ListTag("Motion", [
				new DoubleTag("", -sin($yawRad) * cos($pitchRad)),
				new DoubleTag("", -sin($pitchRad)),
				new DoubleTag("", cos($yawRad) * cos($pitchRad))
			]),
			"Rotation" => new ListTag("Rotation", [
				new FloatTag("", $player->yaw),
				new FloatTag("", $player->pitch)
			]),
		]);
		$f = 1.1;
		$nbt["PotionId"] = new ShortTag("PotionId", $this->getDamage());
		$projectile = Entity::createEntity("ThrownPotion", $player->getLevel(), $nbt, $player);

		$projectile->spawnToAll();

		$this->count--;

		return true;
	}
}