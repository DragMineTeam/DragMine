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

use pocketmine\command\CommandSender;
use pocketmine\event\TranslationContainer;
use pocketmine\plugin\FolderPluginLoader;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class MakePluginCommand extends VanillaCommand {

	/**
	 * MakePluginCommand constructor.
	 *
	 * @param $name
	 */
	public function __construct($name){
		parent::__construct(
			$name,
			"Creates a Phar plugin from a unarchived",
			"/makeplugin <pluginName>",
			["mp"]
		);
		$this->setPermission("pocketmine.command.makeplugin");
	}

	/**
	 * @param CommandSender $sender
	 * @param string        $commandLabel
	 * @param array         $args
	 *
	 * @return bool
	 */
	public function execute(CommandSender $sender, $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return false;
		}

		if(count($args) === 0){
			$sender->sendMessage(TextFormat::RED . "Usage: " . $this->usageMessage);
			return true;
		}

		$pluginName = trim(implode(" ", $args));
		if($pluginName === "" or !(($plugin = Server::getInstance()->getPluginManager()->getPlugin($pluginName)) instanceof Plugin)){
			$sender->sendMessage(TextFormat::RED . "[DragMine]Invalid plugin name, check the name case.");
			$this->sendPluginList($sender);
			return true;
		}
		$description = $plugin->getDescription();

		if(!($plugin->getPluginLoader() instanceof FolderPluginLoader)){
			$sender->sendMessage(TextFormat::RED . "[DragMine]Plugin " . $description->getName() . " is not in folder structure.");
			return true;
		}

		$pharPath = Server::getInstance()->getPluginPath()  . "DragMine" . DIRECTORY_SEPARATOR . $description->getName() . "_v" . $description->getVersion() . ".phar";
		if(file_exists($pharPath)){
			$sender->sendMessage("[DragMine]Phar plugin already exists, overwriting...");
			\Phar::unlinkArchive($pharPath);
		}
		$sender->sendMessage("[DragMine]Adding files...");
		$phar = new \Phar($pharPath);
		$phar->setMetadata([
			"name" => $description->getName(),
			"version" => $description->getVersion(),
			"main" => $description->getMain(),
			"api" => $description->getCompatibleApis(),
			"depend" => $description->getDepend(),
			"description" => $description->getDescription(),
			"authors" => $description->getAuthors(),
			"website" => $description->getWebsite(),
			"creationDate" => time()
		]);
		if($description->getName() === "DevTools"){
			$phar->setStub('<?php require("phar://". __FILE__ ."/src/DevTools/ConsoleScript.php"); __HALT_COMPILER();');
		}else{
			$phar->setStub('<?php echo "DragMine plugin ' . $description->getName() . ' v' . $description->getVersion() . '\nThis file has been generated using DragMine at ' . date("r") . '\n----------------\n";if(extension_loaded("phar")){$phar = new \Phar(__FILE__);foreach($phar->getMetadata() as $key => $value){echo ucfirst($key).": ".(is_array($value) ? implode(", ", $value):$value)."\n";}} __HALT_COMPILER();');
		}
		$phar->setSignatureAlgorithm(\Phar::SHA1);
		$reflection = new \ReflectionClass("pocketmine\\plugin\\PluginBase");
		$file = $reflection->getProperty("file");
		$file->setAccessible(true);
		$filePath = rtrim(str_replace("\\", "/", $file->getValue($plugin)), "/") . "/";
		$phar->startBuffering();
		$excludedSubstrings = [
			"/.",
			realpath($pharPath)
		];
		$includedPaths = ["src", "vendor", "plugin.yml"];
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
		$phar->stopBuffering();
		$sender->sendMessage("[DragMine]Phar plugin " . $description->getName() . " v" . $description->getVersion() . " has been created on " . $pharPath);
		return true;
	}

	/**
	 * @param CommandSender $sender
	 */
	private function sendPluginList(CommandSender $sender){
		$list = "";
		foreach(($plugins = $sender->getServer()->getPluginManager()->getPlugins()) as $plugin){
			if(strlen($list) > 0){
				$list .= TextFormat::WHITE . ", ";
			}
			$list .= $plugin->isEnabled() ? TextFormat::GREEN : TextFormat::RED;
			$list .= $plugin->getDescription()->getFullName();
		}

		$sender->sendMessage(new TranslationContainer("pocketmine.command.plugins.success", [count($plugins), $list]));
	}

	private function preg_quote_array(array $strings, string $delim = null) : array{
		return array_map(function(string $str) use ($delim) : string{ return preg_quote($str, $delim); }, $strings);
	}
}