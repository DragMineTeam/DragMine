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

declare(strict_types=1);

namespace pocketmine\inventory;

use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\types\WindowTypes;
use pocketmine\Player;
use pocketmine\tile\EnchantTable;

class EnchantInventory extends ContainerInventory{

	protected $holder;

	public function __construct(Position $pos){
		parent::__construct(new FakeBlockMenu($this, $pos));
	}

	public function getNetworkType() : int{
		return WindowTypes::ENCHANTMENT;
	}

	public function getName() : string{
		return "Enchantment Table";
	}

	public function getDefaultSize() : int{
		return 2; //1 input, 1 lapis
	}

	public function getHolder(){
		return $this->holder;
	}

	public function onClose(Player $who) : void{
		parent::onClose($who);

		$inventory = $who->getInventory();
		for($i=0,$size=$this->getDefaultSize();$i<$size;++$i){
			$item = $this->getItem($i);
			if(!$item->isNull()){
				if($inventory->canAddItem($item)){
					$inventory->addItem($item);
				}else{
					$who->dropItem($item);
				}
			}
		}
	}
}