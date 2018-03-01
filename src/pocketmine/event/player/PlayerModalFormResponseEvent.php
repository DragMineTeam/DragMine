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

namespace pocketmine\event\player;

use pocketmine\Player;

class PlayerModalFormResponseEvent extends PlayerEvent{
	public static $handlerList = null;

	/** @var int */
	protected $formId;
	/** @var string */
	protected $formData;

	/**
	 * PlayerFormResponceEvent constructor.
	 *
	 * @param Player $player
	 * @param int $formId
	 * @param string $formData
	 */
	public function __construct(Player $player, int $formId, string $formData){
		$this->player = $player;
		$this->formId = $formId;
		$this->formData = $formData;
	}

	public function getFormId() : int{
		return $this->formId;
	}

	public function getFormData() : string{
		return $this->formData;
	}

}
