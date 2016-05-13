<?php

/*
 * DoorLocker (v1.0) by EvolSoft
 * Developer: EvolSoft (TheDiamondYT)
 * Website: http://www.evolsoft.tk
 * Date: 13/05/2016 06:08 PM (UTC)
 * Copyright & License: (C) 2016 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/DoorLocker/blob/master/LICENSE)
 */

namespace DoorLocker;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\command\CommandExecutor;
use pocketmine\permission\Permission;
use pocketmine\Player;
use pocketmine\level\Level;
use pocketmine\utils\TextFormat;

class Main extends PluginBase{
	
	//About Plugin Const
	const PRODUCER = "EvolSoft";
	const VERSION = "1.0";
	const MAIN_WEBSITE = "http://www.evolsoft.tk";
	//Other Const
	//Prefix
	const PREFIX = "&8[&4Door&6Locker&8] ";
	const _FILE = ".";
	const _DIRECTORY = "doors/";
	//Item Name/ID
	const ITEM_NAME = "Door";
	const ITEM_NAME_2 = "Door";
	const ITEM_ID = 64;
	
	public $status;
	public $data;

	public function translateColors($symbol, $message){
		
		$message = str_replace($symbol."0", TextFormat::BLACK, $message);
		$message = str_replace($symbol."1", TextFormat::DARK_BLUE, $message);
		$message = str_replace($symbol."2", TextFormat::DARK_GREEN, $message);
		$message = str_replace($symbol."3", TextFormat::DARK_AQUA, $message);
		$message = str_replace($symbol."4", TextFormat::DARK_RED, $message);
		$message = str_replace($symbol."5", TextFormat::DARK_PURPLE, $message);
		$message = str_replace($symbol."6", TextFormat::GOLD, $message);
		$message = str_replace($symbol."7", TextFormat::GRAY, $message);
		$message = str_replace($symbol."8", TextFormat::DARK_GRAY, $message);
		$message = str_replace($symbol."9", TextFormat::BLUE, $message);
		$message = str_replace($symbol."a", TextFormat::GREEN, $message);
		$message = str_replace($symbol."b", TextFormat::AQUA, $message);
		$message = str_replace($symbol."c", TextFormat::RED, $message);
		$message = str_replace($symbol."d", TextFormat::LIGHT_PURPLE, $message);
		$message = str_replace($symbol."e", TextFormat::YELLOW, $message);
		$message = str_replace($symbol."f", TextFormat::WHITE, $message);
		
		$message = str_replace($symbol."k", TextFormat::OBFUSCATED, $message);
		$message = str_replace($symbol."l", TextFormat::BOLD, $message);
		$message = str_replace($symbol."m", TextFormat::STRIKETHROUGH, $message);
		$message = str_replace($symbol."n", TextFormat::UNDERLINE, $message);
		$message = str_replace($symbol."o", TextFormat::ITALIC, $message);
		$message = str_replace($symbol."r", TextFormat::RESET, $message);
		
		return $message;
	}
	
    public function onEnable(){
        @mkdir($this->getDataFolder());
        @mkdir($this->getDataFolder() . Main::_DIRECTORY);
        $this->saveDefaultConfig();
        $this->data = $this->getDataFolder();
        $this->getCommand("doorlocker")->setExecutor(new Commands\Commands($this));
        $this->getCommand("lockdoor")->setExecutor(new Commands\LockDoor($this));
        $this->getCommand("unlockdoor")->setExecutor(new Commands\UnlockDoor($this));
	    $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    }
    
    public function setCommandStatus($int, $player){
    	//0 Empty
    	//1 Lock
    	//2 Unlock
    	if($int >= 0 && $int <= 3){
    		$this->status[strtolower($player)] = $int;
    	}
    }
    
    public function getCommandStatus($player){
    	if(isset($this->status[strtolower($player)])){
    		return $this->status[strtolower($player)];
    	}else{
    		$this->status[strtolower($player)] = 0;
    		return $this->status[strtolower($player)];
    	}
    }
    
    public function endCommandSession($player){
    	unset($this->status[strtolower($player)]);
    }
    
    public function isDoorRegistered($level, $x, $y, $z){
    	return file_exists($this->data . Main::_DIRECTORY . strtolower($level . "/") . strtolower($x . Main::_FILE . $y.Main::_FILE . $z . ".yml"));
    }
    
    public function getDoorOwner($level, $x, $y, $z){
    	if(file_exists($this->data . Main::_DIRECTORY . strtolower($level . "/") . strtolower($x . Main::_FILE . $y . Main::_FILE . $z . ".yml"))){
    		$door = new Config($this->data . Main::_DIRECTORY . strtolower($level . "/") . strtolower($x . Main::_FILE . $y . Main::_FILE . $z . ".yml"), Config::YAML);
    		$tmp = $door->get("player");
    		return strtolower($tmp); //Success!
    	}else{
    		return false; //Failed: Chest not registered
    	}
    }
    
    public function lockDoor($level, $x, $y, $z, $player){
    	@mkdir($this->data . Main::_DIRECTORY . strtolower($level . "/"));
    	if(file_exists($this->data . Main::_DIRECTORY . strtolower($level . "/") . strtolower($x . Main::_FILE . $y.Main::_FILE . $z . ".yml"))){
    		return false; //Error: Door already registered
    	}else{
    		$door = new Config($this->data . Main::_DIRECTORY . strtolower($level . "/") . strtolower($x . Main::_FILE . $y . Main::_FILE. $z . ".yml"), Config::YAML);
    		$door->set("player", $player);
    		$door->save();
    		return true; //Success!
    	}
    }
    
    public function unlockDoor($level, $x, $y, $z, $player){
    	if(file_exists($this->data . Main::_DIRECTORY . strtolower($level . "/") . strtolower($x . Main::_FILE . $y . Main::_FILE . $z . ".yml"))){
    		$door = new Config($this->data . Main::_DIRECTORY . strtolower($level . "/") . strtolower($x . Main::_FILE . $y . Main::_FILE . $z . ".yml"), Config::YAML);
    		$tmp = $door->get("player");
    	    if(strtolower($player)==strtolower($tmp)){
    	    	unlink($this->data . Main::_DIRECTORY . strtolower($level . "/") . strtolower($x . Main::_FILE . $y.Main::_FILE . $z . ".yml"));
    	    	return 2; //Success!
    	    }else{
    	    	return 1; //Failed: Player is not owner of chest
    	    }
    	}else{
    		return 0; //Failed: Chest not registered
    	}
    }
    
}
?>
