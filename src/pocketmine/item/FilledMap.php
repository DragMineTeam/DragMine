<?php

/*
 *
 *  ____                  __  __ _            
 * |  _ \ _ __ __ _  __ _|  \/  (_)_ __   ___ 
 * | | | | '__/ _` |/ _` | |\/| | | '_ \ / _ \
 * | |_| | | | (_| | (_| | |  | | | | | |  __/
 * |____/|_|  \__,_|\__, |_|  |_|_|_| |_|\___|
 *                  |___/                     
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author DragMine Team
 *
 *
*/

namespace pocketmine\item;

use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\ClientboundMapItemDataPacket;
use pocketmine\Server;
use pocketmine\utils\Color;

class FilledMap extends Item{

	/**
	 * @var int $map_id
	 * @var Color[][] $colors
	 * @var int $scale
	 * @var int $width
	 * @var int $height
	 * @var array $decorationEntityUniqueIds
	 * @var array $decorations
	 * @var int $xOffset
	 * @var int $yOffset
	 */
	 
	public $map_id, $colors = [], $scale, $width, $height, $decorationEntityUniqueIds = [], $decorations = [], $xOffset, $yOffset;
	
	public function __construct(int $map_id = -1, array $colors = [], int $scale = 1, int $width = 128, int $height = 128, $decorationEntityUniqueIds = [], $decorations = [], int $xOffset = 0, int $yOffset = 0){
		parent::__construct(self::FILLED_MAP, 0, "Filled Map");
		$this->map_id = $map_id;
		$this->colors = $colors;
		$this->scale = $scale;
		$this->width = $width;
		$this->height = $height;
		$this->decorationEntityUniqueIds = $decorationEntityUniqueIds;
		$this->decorations = $decorations;
		$this->xOffset = $xOffset;
		$this->yOffset = $yOffset;
		$this->update(ClientboundMapItemDataPacket::BITFLAG_TEXTURE_UPDATE);
		Server::getInstance()->getMapUtils()->cacheMap($this);
	}
	/**
	 * @return int $id
	 */
	public function getMapId(){
		return $this->map_id;
	}

	public function setMapId(int $map_id){
		$this->map_id = $map_id;
	}

	public function getScale(){
		return $this->scale;
	}

	public function setScale(int $scale){
		$this->scale = $scale;
		$this->update(ClientboundMapItemDataPacket::BITFLAG_TEXTURE_UPDATE);
	}

	public function getDecorationEntityUniqueIds(){
		return $this->decorationEntityUniqueIds;
	}

	public function setDecorationEntityUniqueIds($decorationEntityUniqueIds){
		$this->decorationEntityUniqueIds = $decorationEntityUniqueIds;
		$this->update(ClientboundMapItemDataPacket::BITFLAG_DECORATION_UPDATE);
	}

	public function addDecorationEntityUniqueId($decoration){
		$this->decorationEntityUniqueIds[] = $decoration;
		end($this->decorationEntityUniqueIds);
		$this->update(ClientboundMapItemDataPacket::BITFLAG_DECORATION_UPDATE);
		return key($this->decorationEntityUniqueIds);
	}

	public function removeDecorationEntityUniqueId(int $id){
		unset($this->decorations[$id]);
		$this->update(ClientboundMapItemDataPacket::BITFLAG_DECORATION_UPDATE);
	}

	public function getDecorations(){
		return $this->decorations;
	}

	public function setDecorations($decorations){
		$this->decorations = $decorations;
		$this->update(ClientboundMapItemDataPacket::BITFLAG_DECORATION_UPDATE);
	}

	public function addDecoration($decoration){
		$this->decorations[] = $decoration;
		end($this->decorations);
		$this->update(ClientboundMapItemDataPacket::BITFLAG_DECORATION_UPDATE);
		return key($this->decorations);
	}

	public function removeDecoration(int $id){
		unset($this->decorations[$id]);
		$this->update(ClientboundMapItemDataPacket::BITFLAG_DECORATION_UPDATE);
	}

	public function getWidth(){
		return $this->width;
	}

	public function setWidth(int $width){
		$this->width = $width;
		$this->update(ClientboundMapItemDataPacket::BITFLAG_TEXTURE_UPDATE);
	}

	public function getHeight(){
		return $this->height;
	}

	public function setHeight(int $height){
		$this->height = $height;
		$this->update(ClientboundMapItemDataPacket::BITFLAG_TEXTURE_UPDATE);
	}

	public function getXOffset(){
		return $this->xOffset;
	}

	public function setXOffset(int $xOffset){
		$this->xOffset = $xOffset;
		$this->update(ClientboundMapItemDataPacket::BITFLAG_TEXTURE_UPDATE);
	}

	public function getYOffset(){
		return $this->yOffset;
	}

	public function setYOffset(int $yOffset){
		$this->yOffset = $yOffset;
		$this->update(ClientboundMapItemDataPacket::BITFLAG_TEXTURE_UPDATE);
	}

	/**
	 * @return Color[][]
	 */
	public function getColors(){
		return $this->colors;
	}

	/**
	 * Returns a color at a position, transparent black if not found or "out of map"
	 * @param int $x
	 * @param int $y
	 * @return Color
	 */
	public function getColorAt(int $x, int $y){
		if (isset($this->getColors()[$y]) && isset($this->getColors()[$y][$x]))
			return $this->getColors()[$y][$x];
		return Server::getInstance()->getMapUtils()->getBaseMapColors()[0];
	}

	public function setColors(array $colors){
		$this->colors = $colors;
		$this->update(ClientboundMapItemDataPacket::BITFLAG_TEXTURE_UPDATE);
	}

	public function setColorAt(Color $color, int $x, int $y){
		$this->colors[$y][$x] = $color;
		$this->update(ClientboundMapItemDataPacket::BITFLAG_TEXTURE_UPDATE);
	}

	public function update($type = 0x00){
		$pk = new ClientboundMapItemDataPacket();
		$pk->mapId = $this->getMapId();
		$pk->type = $type;
		$pk->eids = [];
		$pk->scale = $this->getScale();
		$pk->decorationEntityUniqueIds = $this->getDecorationEntityUniqueIds();
		$pk->decorations = $this->getDecorations();
		$pk->width = $this->getWidth();
		$pk->height = $this->getHeight();
		$pk->xOffset = $this->getXOffset();
		$pk->yOffset = $this->getYOffset();
		$pk->colors = $this->getColors();
		Server::getInstance()->broadcastPacket(Server::getInstance()->getOnlinePlayers(), $pk);
	}

	public function save(){
		return Server::getInstance()->getMapUtils()->exportToNBT($this, $this->getMapId());
	}
}