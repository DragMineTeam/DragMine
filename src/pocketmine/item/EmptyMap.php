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

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\math\Vector3;
use pocketmine\utils\MapUtils;
use pocketmine\utils\Color;
use pocketmine\block\Block;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\ClientboundMapItemDataPacket;

class EmptyMap extends Item{

	private $size;
	
	public function __construct(int $meta = 0, $size = 1){
		parent::__construct(self::EMPTY_MAP, $meta, "Empty Map");
		$this->size = $size;	
	}

	public function onClickAir(Player $player, Vector3 $directionVector) : bool{
		$id = MapUtils::getNewId();
		$vec = new Vector3($player->x, $player->y, $player->z);
		$result = [];
		$size = 128;
		for($i=1;$i<$this->size;$i++){
			$size *= 2;
		}
		$xx = $size;
		$yy = $size;
		for($x=$vec->x+($size/2);$x>=$vec->x-($size/2);$x--, $xx--){
			for($z=$vec->z+($size/2);$z>=$vec->z-($size/2);$z--, $yy--){
				if($yy < 0){
					$yy = $size;
				}
				$y = $player->getLevel()->getHighestBlockAt($x, $z);
				if($y === -1){
					$result[$yy][$xx] = Color::getRGB(0, 0, 0);
				}else{
					$block = $player->getLevel()->getBlock(new Vector3($x, $y, $z));
					$result[$yy][$xx] = MapUtils::getBlockColor($block);
				}
			}
		}
		$map = new FilledMap($id, $result, $this->size, $size, $size);
		$tag = new CompoundTag("", []);
		$tag->map_uuid = new StringTag("map_uuid", (string)$id);
		$map->setCompoundTag($tag);
		$player->getInventory()->addItem($map);
		$map->update(ClientboundMapItemDataPacket::BITFLAG_TEXTURE_UPDATE);
		Server::getInstance()->getMapUtils()->cacheMap($map);

		return true;
	}
}