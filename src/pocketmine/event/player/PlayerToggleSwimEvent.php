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

namespace pocketmine\event\player;

use pocketmine\event\Cancellable;
use pocketmine\Player;

class PlayerToggleSwimEvent extends PlayerEvent implements Cancellable{
	public static $handlerList = null;

	/** @var bool */
	protected $isSwimming;

	/**
	 * @param Player $player
	 * @param bool   $isSprinting
	 */
	public function __construct(Player $player, bool $isSwimming){
		$this->player = $player;
		$this->isSwimming = $isSwimming;
	}

	/**
	 * @return bool
	 */
	public function isSwimming() : bool{
		return $this->isSwimming;
	}

}