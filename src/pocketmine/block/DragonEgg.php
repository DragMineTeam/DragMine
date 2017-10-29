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

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\Tool;

use pocketmine\Player;

use pocketmine\math\Vector3;

use pocketmine\level\Level;

class DragonEgg extends Fallable{

	protected $id = self::DRAGON_EGG;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getHardness() : float{
		return 3;
	}

	public function getToolType() : int{
		return Tool::TYPE_PICKAXE;
	}

	public function getName() : string{
		return "Dragon Egg";
	}

	public function canBeActivated() : bool{
		return true;
	}

	public function onActivate(Item $item, Player $player = null) : bool{
		$teleport = false;
		do{
			$newx = mt_rand($this->x - 16,$this->x + 16);
			$newy = mt_rand($this->y, $this->y + 3);
			$newz = mt_rand($this->z - 16, $this->z + 16);
			$newId = $this->getLevel()->getBlock(new Vector3($newx, $newy, $newz))->getID();
			switch($newId){
				case 0:
				case 8:
				case 9:
				case 10:
				case 11:
					$teleport = true;
					$this->getLevel()->setBlock($this, new Air(), true, true);
					$this->getLevel()->setBlock(new Vector3($newx, $newy, $newz), $this, true, true);
					break;
			}
		}while(!$teleport);
		return true;
	}
}