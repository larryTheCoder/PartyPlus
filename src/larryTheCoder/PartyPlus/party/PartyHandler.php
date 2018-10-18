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
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;

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

	public function __construct(PartyMain $plugin, Player $leader){
		$this->plugin = $plugin;
		$this->leader = $leader;
		$this->members = [];
		$this->admins = [];
	}

	/**
	 * Checks if the user is in this party.
	 *
	 * @param string $user
	 * @return bool
	 */
	public function isMember(string $user): bool{
		return isset($this->members[$user]);
	}

	/**
	 * Adds or removes the special privileges
	 * for the user.
	 *
	 * @param Player $user
	 * @param int $type
	 */
	public function setUserPermission(Player $user, int $type){
		switch($type){
			case self::USER_ADMIN:
				// Just check if this user already an admin
				if($this->isAdmin($user->getName())){
					break;
				}

				$this->admins[$user->getName()] = $user;
				break;
			case self::USER_NORMAL:
				// A normal user must have an admin
				if(!$this->isAdmin($user->getName())){
					break;
				}

				unset($this->admins[$user->getName()]);
				break;
			default:
				throw new \InvalidArgumentException("Unknown permission handler been given for user permission.");
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
		return isset($this->admins[$user]);
	}

	/**
	 * @param string $user
	 * @return bool
	 */
	public function isLeader(string $user){
		return strtolower($this->leader->getName()) === strtolower($user);
	}

	/**
	 * @param PlayerQuitEvent $event
	 */
	public function onPlayerLeave(PlayerQuitEvent $event){
		if($event->getPlayer() !== $this->leader){
			return;
		}

		foreach($this->members as $key => $player){
			$player->sendMessage($this->plugin->getPrefix() . "§eThe leader for this party has quit the server, disbanding");
			unset($this->members[$key]);
		}
		unset($this->admins);
	}
}