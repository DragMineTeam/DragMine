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

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\utils\BinaryStream;
use pocketmine\Server;

class AvailableCommandsPacket extends DataPacket{
	const NETWORK_ID = ProtocolInfo::AVAILABLE_COMMANDS_PACKET;
	
	const ARG_FLAG_VALID = 0x100000;
	const ARG_FLAG_ENUM = 0x200000;
	const ARG_TYPE_INT = 0x01;
	const ARG_TYPE_FLOAT = 0x02;
	const ARG_TYPE_VALUE = 0x03;
	const ARG_TYPE_WILDCARD_INT = 0x04;
	const ARG_TYPE_TARGET = 0x05;
	const ARG_TYPE_WILDCARD_TARGET = 0x06;
	const ARG_TYPE_STRING   = 0x0f;
	const ARG_TYPE_POSITION = 0x10;
	const ARG_TYPE_MESSAGE = 0x13;
	const ARG_TYPE_RAWTEXT = 0x15;
	const ARG_TYPE_JSON = 0x18;
	const ARG_TYPE_COMMAND = 0x1f;

	public $commands = [];

	/**
	 * @param string $paramName
	 * @return int
	 */
	private static function getFlag($paramName){
		switch($paramName){
			case "int":
				return self::ARG_TYPE_INT;
			case "float":
				return self::ARG_TYPE_FLOAT;
			case "mixed":
				return self::ARG_TYPE_VALUE;
			case "target":
				return self::ARG_TYPE_TARGET;
			case "string":
				return self::ARG_TYPE_STRING;
			case "xyz":
				return self::ARG_TYPE_POSITION;
			case "text":
				return self::ARG_TYPE_TEXT;
			case "json":
				return self::ARG_TYPE_JSON;
			case "command":
				return self::ARG_TYPE_COMMAND;
			case "message":
				return self::ARG_TYPE_MESSAGE;
		}
		return 0;
	}

	protected function decodePayload(){
	}

	protected function encodePayload(){
		$enumValues = [];
		$enumValuesCount = 0;
		$enumAdditional = [];
		$enums = [];
		$commandsStream = new BinaryStream();
		foreach($this->commands as $commandName => $commandData){
			if($commandName === "help"){
				continue;
			}
			$commandsStream->putString($commandName);
			$description = $commandData["versions"][0]["description"];
			if(substr($description, 0, 1) === "%"){
				$description = Server::getInstance()->getLanguage()->translateString(substr($description, 1));
			}
			$commandsStream->putString($description);
			$commandsStream->putByte(0);
			$commandsStream->putByte(0);
			if(isset($commandData["versions"][0]["aliases"]) && !empty($commandData["versions"][0]["aliases"])){
				$aliases = [];
				foreach($commandData["versions"][0]["aliases"] as $alias){
					if (!isset($enumAdditional[$alias])) {
						$enumValues[$enumValuesCount] = $alias;
						$enumAdditional[$alias] = $enumValuesCount;
						$targetIndex = $enumValuesCount;
						$enumValuesCount++;
					}else{
						$targetIndex = $enumAdditional[$alias];
					}
					$aliases[] = $targetIndex;
				}
				$enums[] = [
					"name" => $commandName . "CommandAliases",
					"data" => $aliases,
				];
				$aliasesEnumId = count($enums) - 1;
			}else{
				$aliasesEnumId = -1;
			}
			$commandsStream->putLInt($aliasesEnumId);
			$commandsStream->putUnsignedVarInt(count($commandData["versions"][0]["overloads"])); // overloads
			foreach($commandData["versions"][0]["overloads"] as $overloadData){
				$commandsStream->putUnsignedVarInt(count($overloadData["input"]["parameters"]));
				$paramNum = count($overloadData["input"]["parameters"]);
				foreach ($overloadData["input"]["parameters"] as $paramData) {
					$commandsStream->putString($paramData["name"]);
					$isParamOneAndOptional = ($paramNum == 1 && isset($paramData["optional"]) && $paramData["optional"]);
					if($paramData["type"] == "rawtext" && ($paramNum > 1 || $isParamOneAndOptional)){
						$commandsStream->putLInt(self::ARG_FLAG_VALID | self::getFlag("string"));
					}else{
						$commandsStream->putLInt(self::ARG_FLAG_VALID | self::getFlag($paramData["type"]));
					}
					$commandsStream->putBool(isset($paramData["optional"]) && $paramData["optional"]);
				}
			}
		}
		$this->putUnsignedVarInt($enumValuesCount);
		for($i = 0; $i < $enumValuesCount; $i++){
			$this->putString($enumValues[$i]);
		}
		$this->putUnsignedVarInt(0);
		$enumsCount = count($enums);
		$this->putUnsignedVarInt($enumsCount);
		for($i = 0; $i < $enumsCount; $i++){
			$this->putString($enums[$i]["name"]);
			$dataCount = count($enums[$i]["data"]);
			$this->putUnsignedVarInt($dataCount);
			for($j = 0; $j < $dataCount; $j++){
				if($enumValuesCount < 256){
					$this->putByte($enums[$i]["data"][$j]);
				}elseif($enumValuesCount < 65536){
					$this->putLShort($enums[$i]["data"][$j]);
				}else{
					$this->putLInt($enums[$i]["data"][$j]);
				}	
			}
		}
		
		$this->putUnsignedVarInt(count($this->commands));
		$this->put($commandsStream->buffer);
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleAvailableCommands($this);
	}

}
