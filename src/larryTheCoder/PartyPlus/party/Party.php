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
use pocketmine\Player;

class Party {

	/** @var PartyMain */
	private $plugin;
	/** @var PartyHandler[] */
	private $parties;

	public function __construct(PartyMain $plugin){
		$this->plugin = $plugin;
		$this->parties = [];
	}

	/**
	 * Creates a new party for the player.
	 *
	 * @param Player $p
	 */
	public function createParty(Player $p){
		// This check is required as this is inside the API handler
		if($this->hasParty($p->getName())){
			$p->sendMessage($this->plugin->getPrefix() . "§cYou already created a party.");

			return;
		}

		$party = new PartyHandler($this->plugin, $p);
		$this->parties[strtolower($p->getName())] = $party;
	}

	/**
	 * Disband existing party from a player.
	 *
	 * @param Player $p
	 */
	public function disbandParty(Player $p){
		// Same as above, API boundaries
		if(!$this->hasParty($p->getName())){
			$p->sendMessage($this->plugin->getPrefix() . "§cYou don't have a party yet.");

			return;
		}

		$party = new PartyHandler($this->plugin, $p);
		$this->parties[strtolower($p->getName())] = $party;
	}

	public function hasParty(string $user): bool{
		return isset($this->parties[strtolower($user)]);
	}
}