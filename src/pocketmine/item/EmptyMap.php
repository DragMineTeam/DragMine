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

namespace pocketmine\item;

use pocketmine\math\Vector3;
use pocketmine\utils\MapUtils;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\item\map\MapCreateTask;
use pocketmine\scheduler\AsyncTask;

class EmptyMap extends Item{

	private $size;
	
	public function __construct(int $meta = 0, $size = 1){
		parent::__construct(self::EMPTY_MAP, $meta, "Empty Map");
		$this->size = $size;	
	}

	public function onClickAir(Player $player, Vector3 $directionVector) : bool{
		$id = MapUtils::getNewId();
		$vec = new Vector3($player->x, $player->y, $player->z);
		Server::getInstance()->getScheduler()->scheduleAsyncTask(new MapCreateTask($id, $vec, $player, $this->size, $player->getLevel()));
		return true;
	}
}