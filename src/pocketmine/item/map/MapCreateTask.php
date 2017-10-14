<?php
 
namespace pocketmine\item\map;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\item\FilledMap;
use pocketmine\math\Vector3;
use pocketmine\utils\MapUtils;
use pocketmine\utils\Color;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\ClientboundMapItemDataPacket;

class MapCreateTask extends AsyncTask{

	public static $id, $vec, $player, $size, $level;

	public function __construct($id, $vec, $player, $size, $level){
		self::$id = $id;
		self::$vec = $vec;
		self::$player = $player;
		self::$size = $size;
		self::$level = $level;
	}

	public function onRun(){
		$result = [];
		$vec = self::$vec;
		$size = 128;
		for($i=1;$i<self::$size;$i++){
			$size *= 2;
		}
		for($x=$vec->x+($size/2);$x>=$vec->x-($size/2);$x--){
			for($z=$vec->z+($size/2);$z>=$vec->z-($size/2);$z--){
				for($y=256;$y>=0;$y--){
					// TODO
					$result[$z+($size/2)][$x+($size/2)] = Color::getRGB(mt_rand(0,255), mt_rand(0,255), mt_rand(0,255));
					break;
				}
			}
		}
		$result["size"] = $size;
		$this->setResult($result);
	}

	public function onCompletion(Server $server){
		$result = $this->getResult();
		$id = self::$id;
		$size = $result["size"];
		unset($result["size"]);
		$map = new FilledMap($id, $result, self::$size, $size, $size);
		$tag = new CompoundTag("", []);
		$tag->map_uuid = new StringTag("map_uuid", (string)$id);
		$map->setCompoundTag($tag);
		self::$player->getInventory()->addItem($map);
		$map->update(ClientboundMapItemDataPacket::BITFLAG_TEXTURE_UPDATE);
		$server->getMapUtils()->cacheMap($map);
	}
}