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

namespace pocketmine\utils;

use pocketmine\block\Block;
use pocketmine\block\Planks;
use pocketmine\block\Prismarine;
use pocketmine\block\StoneSlab;
use pocketmine\block\Stone;
use pocketmine\item\Dye;
use pocketmine\item\FilledMap as Map;
use pocketmine\Server;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntArrayTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\utils\Config;

class MapUtils {
	public static $BaseMapColors = [];
	public static $MapColors = [];
	public static $idConfig;
	private static $cachedMaps = [];

	public function __construct() {
		$path = Server::getInstance()->getDataPath() . "maps";
		@mkdir($path);
		self::$idConfig = new Config($path . "/idcounts.json" , Config::JSON, ["map" => 0]);
		self::$BaseMapColors = [
			new Color(127, 178, 56),
			new Color(247, 233, 163),
			new Color(167, 167, 167),
			new Color(255, 0, 0),
			new Color(160, 160, 255),
			new Color(0, 124, 0),
			new Color(255, 255, 255),
			new Color(199, 199, 199),
			new Color(164, 168, 184),
			new Color(151, 109, 77),
			new Color(112, 112, 112),
			new Color(64, 64, 255),
			new Color(104, 83, 50),
			new Color(255, 252, 245),
			new Color(216, 127, 51),
			new Color(178, 76, 216),
			new Color(102, 153, 216),
			new Color(229, 229, 51),
			new Color(127, 204, 25),
			new Color(242, 127, 165),
			new Color(76, 76, 76),
			new Color(153, 153, 153),
			new Color(76, 127, 153),
			new Color(127, 63, 178),
			new Color(51, 76, 178),
			new Color(102, 76, 51),
			new Color(102, 127, 51),
			new Color(153, 51, 51),
			new Color(25, 25, 25),
			new Color(250, 238, 77),
			new Color(92, 219, 213),
			new Color(74, 128, 255),
			new Color(0, 217, 58),
			new Color(112, 2, 0),
			new Color(129, 86, 49)];

		for($i = 0; $i < count(self::$BaseMapColors); ++$i){
			/** @var Color $bc */
			$bc = self::$BaseMapColors[$i];
			self::$MapColors[$i * 4 + 0] = new Color((int)($bc->getR() * 180.0 / 255.0 + 0.5), (int)($bc->getG() * 180.0 / 255.0 + 0.5), (int)($bc->getB() * 180.0 / 255.0 + 0.5), $bc->getA());
			self::$MapColors[$i * 4 + 1] = new Color((int)($bc->getR() * 220.0 / 255.0 + 0.5), (int)($bc->getG() * 220.0 / 255.0 + 0.5), (int)($bc->getB() * 220.0 / 255.0 + 0.5), $bc->getA());
			self::$MapColors[$i * 4 + 2] = $bc;
			self::$MapColors[$i * 4 + 3] = new Color((int)($bc->getR() * 135.0 / 255.0 + 0.5), (int)($bc->getG() * 135.0 / 255.0 + 0.5), (int)($bc->getB() * 135.0 / 255.0 + 0.5), $bc->getA());
		}
	}

	public static function getNewId(){
		$id = self::$idConfig->get("map");
		$id++;
		self::$idConfig->set("map", $id);
		self::$idConfig->save();
		return $id;
	}
	
	public function getMapColors() {
		return self::$MapColors;
	}

	public function getBaseMapColors(){
		return self::$BaseMapColors;
	}

	public static function cacheMap(Map $map){ //TODO: serialize?
		self::$cachedMaps[$map->getMapId()] = $map;
	}

	public static function getCachedMap(int $uuid){
		return self::$cachedMaps[$uuid]??null;
	}

	public function getAllCachedMaps(){
		return self::$cachedMaps;
	}

	/**
	 * Returns the closest map color to a Color
	 * This will ignore alpha
	 * @param Color $color
	 * @return Color
	 */
	public function getClosestMapColor(Color $color) {
		if ($color->getA() > 128) return self::$MapColors[0];
		$index = 0;
		$best = -1;
		for ($i = 4; $i < count(self::$MapColors); $i++) {
			$distance = Color::getDistance($color, self::$MapColors[$i]);
			if ($distance < $best || $best == -1) {
				$best = $distance;
				$index = $i;
			}
		}
		return self::$MapColors[$index];
	}

	public static function distanceHSV(array $hsv1, array $hsv2) {
		return ($hsv1['v'] - $hsv2['v']) ** 2
			+ ($hsv1['s'] * cos($hsv1['h']) - $hsv2['s'] * cos($hsv2['h'])) ** 2
			+ ($hsv1['s'] * sin($hsv1['h']) - $hsv2['s'] * sin($hsv2['h'])) ** 2;
	}

	public static function exportToPNG(Map $map){
		if (!extension_loaded("gd")){
			return false;
		}
		@mkdir(Server::getInstance()->getDataPath()."maps");
		$filename = Server::getInstance()->getDataPath()."maps/map_".$map->getMapId().".png";
		$image = imagecreatetruecolor($map->getWidth(), $map->getHeight());
		imagesavealpha($image, true);
		for ($y = 0; $y < $map->getHeight(); ++$y){
			for ($x = 0; $x < $map->getWidth(); ++$x){
				$color = $map->getColorAt($x, $y);
				imagesetpixel($image, $x, $y, imagecolorallocate($image, $color->getR(), $color->getG(), $color->getB()));
			}
		}
		return imagepng($image, $filename);
	}

	public function exportToNBT(Map $map, string $name){
		$data = [];
		@mkdir(Server::getInstance()->getDataPath()."maps");
		$filename = Server::getInstance()->getDataPath()."maps/map_".$map->getMapId().".dat";
		foreach ($map->getColors() as $y => $icolors){
			foreach ($icolors as $x => $c){
				$data[$x + ($y * $map->getHeight())] = $c->toABGR();
			}
		}
		$nbt = new NBT(NBT::BIG_ENDIAN);
		$t = new CompoundTag($name, [
					new ShortTag("width", $map->getWidth()),
					new ShortTag("height", $map->getHeight()),
					new ByteTag("scale", $map->getScale()),
					new ByteTag("fullyExplored", 1),
					new ByteTag("dimension", 0),
					new IntTag("xCenter", $map->getXOffset()),
					new IntTag("zCenter", $map->getYOffset()),
					new IntArrayTag("colors", $data),
					new ListTag("decorations", $map->getDecorations())]
			);
		$nbt->setData($t);
		file_put_contents($filename, $nbt->writeCompressed());
		return file_exists($filename);
	}

	public function loadFromNBT(string $path){
		if (!file_exists($path)) return false;
		$id = intval(str_replace(Server::getInstance()->getDataPath()."maps/map_", "", str_replace(".dat ", "", $path)));
		$map = new Map();
		$nbt = new NBT(NBT::BIG_ENDIAN);
		$nbt->readCompressed(file_get_contents($path));
		$data = $nbt->getData();
		$map->setMapId($id);
		$map->setWidth($data->width->getValue());
		$map->setHeight($data->height->getValue());
		$map->setXOffset($data->xCenter->getValue());
		$map->setYOffset($data->zCenter->getValue());
		/** @var Color[][] */
		$colors = [];
		$colordata = $data->colors->getValue();
		for ($y = 0; $y < $map->getHeight(); ++$y){
			for ($x = 0; $x < $map->getWidth(); ++$x){
				$colors[$y][$x] = Color::fromABGR($colordata[$x + ($y * $map->getHeight())]??0);
			}
		}
		$map->setColors($colors);
		$this::exportToPNG($map);
		return $map;
	}

	public static function getBlockColor(Block $block) {
		$id = $block->getId();
		$meta = $block->getDamage();
		// [BlockId, MetaData, isEncode]

		$blockColors = [
			[
				// (127, 178, 56)
				[Block::GRASS, -1],
				[Block::SLIME_BLOCK, -1]
			],
			[
				// (247, 233, 163)
				[Block::SAND, 0, false],
				[Block::SANDSTONE, -1],
				[Block::SANDSTONE_STAIRS, -1],
				[Block::STONE_SLAB, StoneSlab::SANDSTONE, true],
				[Block::DOUBLE_STONE_SLAB, StoneSlab::SANDSTONE, false],
				[Block::GLOWSTONE, -1],
				[Block::END_STONE, -1],
				[Block::PLANKS, Planks::BIRCH, false],
				[Block::LOG, Planks::BIRCH, false],
				[Block::FENCE, Planks::BIRCH, false],
				[Block::BIRCH_FENCE_GATE, -1],
				[Block::BIRCH_STAIRS, -1],
				[Block::WOODEN_SLAB, Planks::BIRCH, true],
				[Block::BONE_BLOCK, -1],
				[Block::END_BRICKS, -1]
			],
			[
				// (167, 167, 167)
				[Block::IRON_BLOCK, -1],
				[Block::IRON_DOOR_BLOCK, -1],
				[Block::IRON_TRAPDOOR, -1],
				[Block::IRON_BARS, -1],
				[Block::BREWING_STAND_BLOCK, -1],
				[Block::ANVIL, -1],
				[Block::HEAVY_WEIGHTED_PRESSURE_PLATE, -1]
			],
			[
				// (255, 0, 0)
				[Block::LAVA, -1],
				[Block::STILL_LAVA, -1],
				[Block::TNT, -1],
				[Block::FIRE, -1],
				[Block::REDSTONE_BLOCK, -1]
			],
			[
				// (160, 160, 255)
				[Block::ICE, -1],
				[Block::PACKED_ICE, -1],
				[Block::FROSTED_ICE, -1]
			],
			[
				// (0, 124, 0)
				[Block::SAPLING, -1],
				[Block::LEAVES, -1],
				[Block::LEAVES2, -1],
				[Block::TALL_GRASS, -1],
				[Block::DEAD_BUSH, -1],
				[Block::RED_FLOWER, -1],
				[Block::DOUBLE_PLANT, -1],
				[Block::BROWN_MUSHROOM, -1],
				[Block::RED_MUSHROOM, -1],
				[Block::WHEAT_BLOCK, -1],
				[Block::CARROT_BLOCK, -1],
				[Block::POTATO_BLOCK, -1],
				[Block::BEETROOT_BLOCK, -1],
				[Block::CACTUS, -1],
				[Block::SUGARCANE_BLOCK, -1],
				[Block::PUMPKIN_STEM, -1],
				[Block::MELON_STEM, -1],
				[Block::VINE, -1],
				[Block::LILY_PAD, -1]
			],
			[
				// (255, 255, 255)
				[Block::WOOL, Dye::WHITE, false],
				[Block::CARPET, Dye::WHITE, false],
				[Block::STAINED_HARDENED_CLAY, Dye::WHITE, false],
				[Block::SNOW_LAYER, -1],
				[Block::SNOW_BLOCK, -1]
			],
			[
				// (199, 199, 199)
				[Block::BED_BLOCK, -1],
				[Block::COBWEB, -1]
			],
			[
				// (164, 168, 184)
				[Block::CLAY_BLOCK, -1],
				[Block::MONSTER_EGG, -1]
			],
			[
				// (151, 109, 77)
				[Block::DIRT, -1],
				[Block::FARMLAND, -1],
				[Block::STONE, Stone::GRANITE, false],
				[Block::STONE, Stone::POLISHED_GRANITE, false],
				[Block::LOG, Planks::JUNGLE, false],
				[Block::PLANKS, Planks::JUNGLE, false],
				[Block::JUNGLE_FENCE_GATE, -1],
				[Block::FENCE, Planks::JUNGLE, false],
				[Block::JUNGLE_STAIRS, -1],
				[Block::WOODEN_SLAB, Planks::JUNGLE, true]
			],
			[
				// (112, 112, 112)
				[Block::STONE, -1],
				[Block::STONE_SLAB, StoneSlab::STONE, true],
				[Block::COBBLESTONE, -1],
				[Block::COBBLESTONE_STAIRS, -1],
				[Block::STONE_SLAB, StoneSlab::COBBLESTONE, true],
				[Block::COBBLESTONE_WALL, -1],
				[Block::MOSSY_COBBLESTONE, -1],
				[Block::STONE, Stone::ANDESITE, false],
				[Block::STONE, Stone::POLISHED_ANDESITE, false],
				[Block::BEDROCK, -1],
				[Block::GOLD_ORE, -1],
				[Block::IRON_ORE, -1],
				[Block::COAL_ORE, -1],
				[Block::LAPIS_ORE, -1],
				[Block::DISPENSER, -1],
				[Block::DROPPER, -1],
				[Block::STICKY_PISTON, -1],
				[Block::PISTON, -1],
				[Block::PISTON_ARM_COLLISION, -1],
				[Block::MONSTER_SPAWNER, -1],
				[Block::DIAMOND_ORE, -1],
				[Block::FURNACE, -1],
				[Block::STONE_PRESSURE_PLATE, -1],
				[Block::REDSTONE_ORE, -1],
				[Block::STONE_BRICK, -1],
				[Block::STONE_BRICK_STAIRS, -1],
				[Block::STONE_SLAB, StoneSlab::STONE_BRICK, true],
				[Block::ENDER_CHEST, -1],
				[Block::HOPPER_BLOCK, -1],
				[Block::GRAVEL, -1],
				[Block::OBSERVER, -1]
			],
			[
				// (64, 64, 255)
				[Block::WATER, -1],
				[Block::STILL_WATER, -1]
			],
			[
				// (104, 83, 50)
				[Block::WOOD, Planks::OAK, false],
				[Block::PLANKS, Planks::OAK, false],
				[Block::FENCE, Planks::OAK, false],
				[Block::OAK_FENCE_GATE, -1],
				[Block::OAK_STAIRS, -1],
				[Block::WOODEN_SLAB, Planks::OAK, true],
				[Block::NOTEBLOCK, -1],
				[Block::BOOKSHELF, -1],
				[Block::CHEST, -1],
				[Block::TRAPPED_CHEST, -1],
				[Block::CRAFTING_TABLE, -1],
				[Block::WOODEN_DOOR_BLOCK, -1],
				[Block::BIRCH_DOOR_BLOCK, -1],
				[Block::SPRUCE_DOOR_BLOCK, -1],
				[Block::JUNGLE_DOOR_BLOCK, -1],
				[Block::ACACIA_DOOR_BLOCK, -1],
				[Block::DARK_OAK_DOOR_BLOCK, -1],
				[Block::SIGN_POST, -1],
				[Block::WALL_BANNER, -1],
				[Block::WALL_SIGN, -1],
				[Block::WOODEN_PRESSURE_PLATE, -1],
				[Block::JUKEBOX, -1],
				[Block::WOODEN_TRAPDOOR, -1],
				[Block::BROWN_MUSHROOM_BLOCK, -1],
				[Block::STANDING_BANNER, -1],
				[Block::DAYLIGHT_SENSOR, -1],
				[Block::DAYLIGHT_SENSOR_INVERTED, -1]
			],
			[
				// (255, 252, 245)
				[Block::QUARTZ_BLOCK, -1],
				[Block::STONE_SLAB, StoneSlab::QUARTZ, true],
				[Block::QUARTZ_STAIRS, -1],
				[Block::STONE, Stone::DIORITE, false],
				[Block::STONE, Stone::POLISHED_DIORITE, false],
				[Block::SEA_LANTERN, -1]
			],
			[
				// (216, 127, 51)
				[Block::WOOL, Dye::ORANGE, false],
				[Block::CARPET, Dye::ORANGE, false],
				[Block::STAINED_HARDENED_CLAY, Dye::ORANGE, false],
				[Block::PUMPKIN, -1],
				[Block::JACK_O_LANTERN, -1],
				[Block::HARDENED_CLAY, -1],
				[Block::SAND, 1, false],
				[Block::RED_SANDSTONE, -1],
				[Block::RED_SANDSTONE_STAIRS, -1],
				[Block::WOOD, Planks::ACACIA, false],
				[Block::PLANKS, Planks::ACACIA, false],
				[Block::FENCE, Planks::ACACIA, false],
				[Block::ACACIA_FENCE_GATE, -1],
				[Block::ACACIA_STAIRS, -1],
				[Block::WOODEN_SLAB, Planks::ACACIA, true]
			],
			[
				// (178, 76, 216)
				[Block::WOOL, Dye::MAGENTA, false],
				[Block::CARPET, Dye::MAGENTA, false],
				[Block::STAINED_HARDENED_CLAY, Dye::MAGENTA, false],
				[Block::PURPUR_BLOCK, -1],
				[Block::PURPUR_STAIRS, -1]
			],
			[
				// (102, 153, 216)
				[Block::WOOL, Dye::LIGHT_BLUE, false],
				[Block::CARPET, Dye::LIGHT_BLUE, false],
				[Block::STAINED_HARDENED_CLAY, Dye::LIGHT_BLUE, false]
			],
			[
				// (229, 229, 51)
				[Block::WOOL, Dye::YELLOW, false],
				[Block::CARPET, Dye::YELLOW, false],
				[Block::STAINED_HARDENED_CLAY, Dye::YELLOW, false],
				[Block::HAY_BALE, -1],
				[Block::SPONGE, -1]
			],
			[
				// (127, 204, 25)
				[Block::WOOL, Dye::LIME, false],
				[Block::CARPET, Dye::LIME, false],
				[Block::STAINED_HARDENED_CLAY, Dye::LIME, false],
				[Block::MELON_BLOCK, -1]
			],
			[
				// (242, 127, 165)
				[Block::WOOL, Dye::PINK, false],
				[Block::CARPET, Dye::PINK, false],
				[Block::STAINED_HARDENED_CLAY, Dye::PINK, false]
			],
			[
				// (76, 76, 76)
				[Block::WOOL, Dye::GRAY, false],
				[Block::CARPET, Dye::GRAY, false],
				[Block::STAINED_HARDENED_CLAY, Dye::GRAY, false],
				[Block::CAULDRON_BLOCK, -1]
			],
			[
				// (153, 153, 153)
				[Block::WOOL, Dye::LIGHT_GRAY, false],
				[Block::CARPET, Dye::LIGHT_GRAY, false],
				[Block::STAINED_HARDENED_CLAY, Dye::LIGHT_GRAY, false],
				[Block::STRUCTURE_BLOCK, -1]
			],
			[
				// (76, 127, 153)
				[Block::WOOL, Dye::CYAN, false],
				[Block::CARPET, Dye::CYAN, false],
				[Block::STAINED_HARDENED_CLAY, Dye::CYAN, false],
				[Block::PRISMARINE, Prismarine::NORMAL, false]
			],
			[
				// (127, 63, 178)
				[Block::WOOL, Dye::PURPLE, false],
				[Block::CARPET, Dye::PURPLE, false],
				[Block::STAINED_HARDENED_CLAY, Dye::PURPLE, false],
				[Block::MYCELIUM, -1],
				[Block::REPEATING_COMMAND_BLOCK, -1],
				[Block::CHORUS_PLANT, -1],
				[Block::CHORUS_FLOWER, -1]
			],
			[
				// (51, 76, 178)
				[Block::WOOL, Dye::BLUE, false],
				[Block::CARPET, Dye::BLUE, false],
				[Block::STAINED_HARDENED_CLAY, Dye::BLUE, false],
			],
			[
				// (102, 76, 51)
				[Block::WOOL, Dye::BROWN, false],
				[Block::CARPET, Dye::BROWN, false],
				[Block::STAINED_HARDENED_CLAY, Dye::BROWN, false],
				[Block::SOUL_SAND, -1],
				[Block::WOOD, Planks::DARK_OAK, false],
				[Block::PLANKS, Planks::DARK_OAK, false],
				[Block::FENCE, Planks::DARK_OAK, false],
				[Block::DARK_OAK_FENCE_GATE, -1],
				[Block::DARK_OAK_STAIRS, -1],
				[Block::WOODEN_SLAB, Planks::DARK_OAK, true],
				[Block::COMMAND_BLOCK, -1]
			],
			[
				// (102, 127, 51)
				[Block::WOOL, Dye::GREEN, false],
				[Block::CARPET, Dye::GREEN, false],
				[Block::STAINED_HARDENED_CLAY, Dye::GREEN, false],
				[Block::END_PORTAL_FRAME, -1],
				[Block::CHAIN_COMMAND_BLOCK, -1]
			],
			[
				// (153, 51, 51)
				[Block::WOOL, Dye::RED, false],
				[Block::CARPET, Dye::RED, false],
				[Block::STAINED_HARDENED_CLAY, Dye::RED, false],
				[Block::RED_MUSHROOM_BLOCK, -1],
				[Block::BRICK_BLOCK, -1],
				[Block::STONE_SLAB, StoneSlab::BRICK, true],
				[Block::BRICK_STAIRS, -1],
				[Block::ENCHANTING_TABLE, -1],
				[Block::NETHER_WART_BLOCK, -1]
			],
			[
				// (25, 25, 25)
				[Block::WOOL, Dye::BLACK, false],
				[Block::CARPET, Dye::BLACK, false],
				[Block::STAINED_HARDENED_CLAY, Dye::BLACK, false],
				[Block::DRAGON_EGG, -1],
				[Block::COAL_BLOCK, -1],
				[Block::OBSIDIAN, -1],
				[Block::END_PORTAL_FRAME, -1],
			],
			[
				// (250, 238, 77)
				[Block::GOLD_BLOCK, -1],
				[Block::LIGHT_WEIGHTED_PRESSURE_PLATE, -1]
			],
			[
				// (92, 219, 213)
				[Block::DIAMOND_BLOCK, -1],
				[Block::PRISMARINE, Prismarine::DARK, false],
				[Block::PRISMARINE, Prismarine::BRICKS, false],
				[Block::BEACON, -1]
			],
			[
				// (74, 128, 255)
				[Block::LAPIS_BLOCK, -1]
			],
			[
				// (0, 217, 58)
				[Block::EMERALD_BLOCK, -1]
			],
			[
				// (112, 2, 0)
				[Block::NETHERRACK, -1],
				[Block::NETHER_QUARTZ_ORE, -1],
				[Block::NETHER_BRICK_FENCE, -1],
				[Block::NETHER_BRICK_BLOCK, -1],
				[Block::RED_NETHER_BRICK, -1],
				[Block::MAGMA, -1],
				[Block::NETHER_BRICK_STAIRS, -1],
				[Block::STONE_SLAB, StoneSlab::NETHER_BRICK, true]
			],
			[
				// (129, 86, 49)
				[Block::PODZOL, -1],
				[Block::WOOD, Planks::SPRUCE, false],
				[Block::PLANKS, Planks::SPRUCE, false],
				[Block::FENCE, Planks::SPRUCE, false],
				[Block::SPRUCE_FENCE_GATE, -1],
				[Block::SPRUCE_STAIRS, -1],
				[Block::WOODEN_SLAB, Planks::SPRUCE, true]
			]
		];

		$noneColorBlocks = [

		];

		foreach($noneColorBlocks as $blocks){
			if($blocks[0] === $id){
				if(($blocks[1] !== -1) && ($blocks[1] == $meta)){
					return null;
				}elseif($blocks[1] === -1){
					return null;
				}
			}
		}

		foreach($blockColors as $index => $colors){
			foreach($colors as $color){
				if($color[0] === $id){
					if($color[1] === -1){
						return self::$BaseMapColors[$index];
					}else{
						if($color[2]){
							if(($color[1] & 0x07) === $meta){
								return self::$BaseMapColors[$index];
							}
						}else{
							if($color[1] === $meta){
								return self::$BaseMapColors[$index];
							}
						}
					}
				}
			}
		}

		return new Color(0, 0, 0);
	}
}
