<?php

/*
 * DoorLocker (v1.0) by EvolSoft
 * Developer: EvolSoft (TheDiamondYT)
 * Website: http://www.evolsoft.tk
 * Date: 13/05/2016 06:11 PM (UTC)
 * Copyright & License: (C) 2016 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/DoorLocker/blob/master/LICENSE)
 */

namespace DoorLocker;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\level\Level;
use pocketmine\permission\Permission;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\math\Vector3;
use pocketmine\block\WoodDoor;

class EventListener extends PluginBase implements Listener{
	
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}
	
	public function onPlayerJoin(PlayerJoinEvent $event){
		$this->plugin->setCommandStatus(0, $event->getPlayer()->getName());
	}
	
	public function onPlayerQuit(PlayerQuitEvent $event){
		$this->plugin->endCommandSession($event->getPlayer()->getName());
	}
	
	public function onDoorOpen(PlayerInteractEvent $event){
		if($event->getBlock()->getID() == Main::ITEM_ID){
			$door = $event->getPlayer()->getLevel()->getTile($event->getBlock());
			if($door instanceof WoodDoor){
				//Check Command status
				//0
				if($this->plugin->getCommandStatus($event->getPlayer()->getName())==0){
					//Check bypass permission
					if($event->getPlayer()->hasPermission("doorlocker.bypass") == false){
						//Check if Door is registered
						if($this->plugin->isDoorRegistered($door->getLevel()->getName(), $door->getX(), $door->getY(), $door->getZ())){
							if($this->plugin->getChestOwner($door->getLevel()->getName(), $door->getX(), $door->getY(), $door->getZ()) != strtolower($event->getPlayer()->getName())){
								$event->setCancelled(true);
								$event->getPlayer()->sendMessage($this->plugin->translateColors("&", Main::PREFIX . "&4You aren't the owner of this " . Main::ITEM_NAME_2 . "."));
							}
						}
					}
				}
				//1
				if($this->plugin->getCommandStatus($event->getPlayer()->getName())==1){
					//Check if Door is registered
					if($this->plugin->isDoorRegistered($door->getLevel()->getName(), $door->getX(), $door->getY(), $door->getZ())){
						if($this->plugin->getDoorOwner($door->getLevel()->getName(), $door->getX(), $door->getY(), $door->getZ()) != strtolower($event->getPlayer()->getName())){
							$event->getPlayer()->sendMessage($this->plugin->translateColors("&", Main::PREFIX . "&4You aren't the owner of this " . Main::ITEM_NAME_2 . "."));
						}else{
							$event->getPlayer()->sendMessage($this->plugin->translateColors("&", Main::PREFIX . "&2" . Main::ITEM_NAME . " already locked"));
						}
					}else{
						$event->getPlayer()->sendMessage($this->plugin->translateColors("&", Main::PREFIX . "&2" . Main::ITEM_NAME . " locked"));
						$this->plugin->lockDoor($door->getLevel()->getName(), $door->getX(), $door->getY(), $door->getZ(), $event->getPlayer()->getName());
					}
					$event->setCancelled(true);
					$this->plugin->setCommandStatus(0, $event->getPlayer()->getName());
				}
				//2
				if($this->plugin->getCommandStatus($event->getPlayer()->getName())==2){
					//Check if Door is registered
					if($this->plugin->isDoorRegistered($door->getLevel()->getName(), $door->getX(), $door->getY(), $door->getZ())){
						if($this->plugin->getDoorOwner($door->getLevel()->getName(), $door->getX(), $door->getY(), $door->getZ()) != strtolower($event->getPlayer()->getName())){
							$event->getPlayer()->sendMessage($this->plugin->translateColors("&", Main::PREFIX . "&4You aren't the owner of this " . Main::ITEM_NAME_2 . "."));
						}else{
							$event->getPlayer()->sendMessage($this->plugin->translateColors("&", Main::PREFIX . "&2" . Main::ITEM_NAME . " unlocked"));
							$this->plugin->unlockChest($door->getLevel()->getName(), $door->getX(), $door->getY(), $door->getZ(), $event->getPlayer()->getName());
						}
					}else{
						$event->getPlayer()->sendMessage($this->plugin->translateColors("&", Main::PREFIX . "&2" . Main::ITEM_NAME . " not registered"));
					}
					$event->setCancelled(true);
					$this->plugin->setCommandStatus(0, $event->getPlayer()->getName());
				}
			}
		}
	}

	public function onBlockDestroy(BlockBreakEvent $event){
		$this->cfg = $this->plugin->getConfig()->getAll();
		$player = $event->getPlayer();
		if($event->getBlock()->getID() == Main::ITEM_ID){
			$door = $event->getPlayer()->getLevel()->getTile($event->getBlock());
			if($door instanceof WoodDoor){
				$level = $door->getLevel()->getName();
				$x = $door->getX();
				$y = $door->getY();
				$z = $door->getZ();
				//Check if door is registered
				if($this->plugin->isDoorRegistered($level, $x, $y, $z)){
					//Check bypass permission
					if($event->getPlayer()->hasPermission("doorlocker.bypass") == false){
						if($this->cfg["protect-doors"] == true){
							$player->sendMessage($this->plugin->translateColors("&", Main::PREFIX . "&4You aren't the owner of this " . Main::ITEM_NAME_2 . "."));
							$event->setCancelled(true);
						}else{
							$this->plugin->unlockDoor($level, $x, $y, $z, $this->plugin->getDoorOwner($level, $x, $y, $z));
						}
					}else{
						$this->plugin->unlockDoor($level, $x, $y, $z, $this->plugin->getDoorOwner($level, $x, $y, $z));
				    }
				}
			}
		}
	}
}
?>
