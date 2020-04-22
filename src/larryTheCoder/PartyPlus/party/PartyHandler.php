<?php
/**
 * This file is part of PartyPlus.
 *
 * PartyPlus is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PartyPlus is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with PartyPlus.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author larryTheCoder
 */

namespace larryTheCoder\PartyPlus\party;

use larryTheCoder\PartyPlus\PartyMain;
use larryTheCoder\PartyPlus\Utils;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\scheduler\TaskHandler;

class PartyHandler implements Listener {

	const USER_ADMIN = 0;
	const USER_NORMAL = 1;

	/** @var Player */
	private $leader;
	/** @var Player[] */
	private $members;
	/** @var Player[] */
	private $admins;
	/** @var PartyMain */
	private $plugin;

	/** @var TaskHandler */
	private $partyKillTask;

	public function __construct(PartyMain $plugin, Player $leader){
		$this->plugin = $plugin;
		$this->leader = $leader;
		$this->members = [];
		$this->admins = [];
	}

	/**
	 * Place a player into the list of this
	 * party. It used to register a new user to
	 * this party.
	 *
	 * @param Player $p The player itself
	 */
	public function addMember(Player $p){
		if(isset($this->members[$p->getName()])){
			return;
		}

		$this->members[$p->getName()] = $p;
	}

	/**
	 * Remove a member of this party. Will return true if the player
	 * has been removed from the list. If its not, then its indicates
	 * that the player is not in the party.
	 *
	 * @param string $pl The player name itself
	 * @return bool      Return true if succeeded, false if otherwise
	 */
	public function removeMember(string $pl): bool{
		if(isset($this->members[$pl])){
			return false;
		}
		unset($this->members[$pl]);

		return true;
	}

	/**
	 * Checks if the user is in this party.
	 *
	 * @param string $user The name of the player
	 * @return bool        Return true if the name of the player is
	 *                     the member of this party.
	 */
	public function isMember(string $user): bool{
		return isset($this->members[$user]);
	}

	/**
	 * Returns the list of players in this party.
	 *
	 * @return Player[]
	 */
	public function getMembers(): array{
		return $this->members;
	}

	/**
	 * Adds or removes the special privileges
	 * for the user.
	 *
	 * @param string $user
	 * @param int    $type
	 */
	public function setUserPermission(string $user, int $type){
		switch($type){
			case self::USER_ADMIN:
				// Just check if this user already an admin
				if($this->isAdmin($user)){
					break;
				}

				$this->admins[$user] = $user;
				break;
			case self::USER_NORMAL:
				// A normal user must have an admin
				if(!$this->isAdmin($user)){
					break;
				}

				unset($this->admins[$user]);
				break;
			default:
				throw new \InvalidArgumentException("Unknown permission type is given for the user's party privileges.");
		}
	}

	/**
	 * Checks if the user is already in party
	 *
	 * @param string $user
	 * @return bool
	 */
	public function isInParty(string $user): bool{
		return $this->isLeader($user) || $this->isMember($user);
	}

	/**
	 * Checks if the user has a privileges to use leader
	 * features.
	 *
	 * @param string $user
	 * @return bool
	 */
	public function isAdmin(string $user): bool{
		return isset($this->admins[$user]) || $this->isLeader($user);
	}

	/**
	 * Checks if the user is this party leader.
	 *
	 * @param string $user
	 * @return bool
	 */
	public function isLeader(string $user){
		return strtolower($this->leader->getName()) === strtolower($user);
	}

	/**
	 * Disbands this party and notifies all players
	 * who joined this party about it and remove them
	 * from this party list.
	 *
	 * @param bool $markAsLeave
	 */
	public function disbandParty(bool $markAsLeave = false){
		/** @var Player $pl */
		foreach($this->members as $key => $pl){
			if(!$markAsLeave){
				$pl->sendMessage(Utils::getPrefix() . "§cYour leader just disbanded his party.");
			}else{
				$pl->sendMessage(Utils::getPrefix() . "§eYour leader just left the server and didn't joined back in time.");
				$pl->sendMessage(Utils::getPrefix() . "§eThe party has now disbanded.");
			}
			unset($this->members[$key]);
		}

		unset($this->admins);
	}

	/**
	 * Notifies this party leader about the current happening in its party.
	 *
	 * @param string $message
	 */
	public function notifyLeader(string $message){
		$this->leader->sendMessage(Utils::getPrefix() . $message);
	}

	/**
	 * @param PlayerQuitEvent $event
	 */
	public function onPlayerLeave(PlayerQuitEvent $event){
		$plName = $event->getPlayer()->getName();
		if(isset($this->members[$plName])){
			$this->notifyLeader("§e$plName §cjust left your party prior disconnection from server.");

			if(isset($this->admins[$plName])){
				unset($this->admins[$plName]);
			}
			$this->members[$plName];
		}
		if($event->getPlayer()->getName() !== $this->leader->getName()){
			return;
		}

		$this->partyKillTask = PartyMain::getInstance()->getScheduler()->scheduleDelayedTask(new PartyKillTask($this), 1200);
	}

	/**
	 * @param PlayerJoinEvent $event
	 */
	public function onPlayerJoin(PlayerJoinEvent $event){
		if($event->getPlayer()->getName() !== $this->leader->getName()){
			return;
		}

		PartyMain::getInstance()->getScheduler()->cancelTask($this->partyKillTask->getTaskId());
	}

	/**
	 * Returns the leader of this party.
	 *
	 * @return Player
	 */
	public function getLeader(){
		return $this->leader;
	}
}