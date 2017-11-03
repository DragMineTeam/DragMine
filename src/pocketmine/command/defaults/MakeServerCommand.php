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


namespace pocketmine\command\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\network\mcpe\protocol\ProtocolInfo;

class MakeServerCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"Create a Phar server from src",
			"/makeserver",
			["ms"]
		);
		$this->setPermission("pocketmine.command.makeserver");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return false;
		}

		$server = $sender->getServer();
		$pharPath = Server::getInstance()->getPluginPath() . DIRECTORY_SEPARATOR . "DragMine" . DIRECTORY_SEPARATOR . $server->getName() . "_" . $server->getPocketMineVersion() . ".phar";
		if(file_exists($pharPath)){
			$sender->sendMessage("[DragMine]Phar file already exists, overwriting...");
			\Phar::unlinkArchive($pharPath);
		}
		$sender->sendMessage("[DragMine]Adding files...");
		$phar = new \Phar($pharPath);
		$phar->setMetadata([
			"name" => $server->getName(),
			"version" => $server->getPocketMineVersion(),
			"api" => $server->getApiVersion(),
			"minecraft" => $server->getVersion(),
			"protocol" => ProtocolInfo::CURRENT_PROTOCOL,
			
		]);
		$phar->setStub('<?php define("pocketmine\\\\PATH", "phar://". __FILE__ ."/"); require_once("phar://". __FILE__ ."/src/pocketmine/PocketMine.php");  __HALT_COMPILER();');
		$phar->setSignatureAlgorithm(\Phar::SHA1);
		$phar->startBuffering();

		$filePath = substr(\pocketmine\PATH, 0, 7) === "phar://" ? \pocketmine\PATH : realpath(\pocketmine\PATH) . "/";
		$filePath = rtrim(str_replace("\\", "/", $filePath), "/") . "/";
		$excludedSubstrings = [
			"/.",
			realpath($pharPath)
		];
		$includedPaths = ["src", "vendor"];
		$regex = sprintf('/^(?!.*(%s))^%s(%s).*/i',
			implode('|', $this->preg_quote_array($excludedSubstrings, '/')), //String may not contain any of these substrings
			preg_quote($filePath, "/"), //String must start with this path...
			implode('|', $this->preg_quote_array($includedPaths, '/')) //... and must be followed by one of these relative paths, if any were specified. If none, this will produce a null capturing group which will allow anything.
		);

		$count = count($phar->buildFromDirectory($filePath, $regex));
		$sender->sendMessage("[DragMine]Added " . $count . " files");

		foreach($phar as $file => $finfo){
			/** @var \PharFileInfo $finfo */
			if($finfo->getSize() > (1024 * 512)){
				$finfo->compress(\Phar::GZ);
			}
		}
		/*if(!isset($args[0]) or (isset($args[0]) and $args[0] != "nogz")){
			$phar->compressFiles(\Phar::GZ);
		}*/
		$phar->stopBuffering();

		$sender->sendMessage("[DragMine]" . $server->getName() . " " . $server->getPocketMineVersion() . " Phar file has been created on " . $pharPath);

		return true;
	}

	private function preg_quote_array(array $strings, string $delim = null) : array{
		return array_map(function(string $str) use ($delim) : string{ return preg_quote($str, $delim); }, $strings);
	}
}