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
use pocketmine\Player;
use pocketmine\Server;

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
	 * @param bool   $silence
	 */
	public function createParty(Player $p, bool $silence = false){
		// This check is required as this is inside the API handler
		if($this->hasParty($p->getName())){
			if(!$silence) $p->sendMessage(Utils::getPrefix() . "§cYou already created a party.");

			return;
		}

		$party = new PartyHandler($this->plugin, $p);
		$this->parties[strtolower($p->getName())] = $party;

		if(!$silence) $p->sendMessage(Utils::getPrefix() . "§bCreated you a new party");
	}

	public function hasParty(string $user): bool{
		return isset($this->parties[strtolower($user)]);
	}

	/**
	 * Gets the party handler for the user.
	 * A new party will be created if the player
	 * don't have a party.
	 *
	 * @param $user string
	 * @return null|PartyHandler
	 */
	public function getParty(string $user): ?PartyHandler{
		if(!$this->hasParty($user)){
			$pl = Server::getInstance()->getPlayer($user);
			if(is_null($pl)){
				return null;
			}
			$this->createParty($pl);

			return $this->getParty($user);
		}

		return $this->parties[strtolower($user)];
	}

	/**
	 * Disband existing party from a player.
	 *
	 * @param Player $p
	 * @param bool   $silence
	 */
	public function disbandParty(Player $p, bool $silence = false){
		// Same as above, API boundaries
		if(!$this->hasParty($p->getName())){
			if(!$silence) $p->sendMessage(Utils::getPrefix() . "§cYou don't have a party.");

			return;
		}

		$this->getParty($p->getName())->disbandParty();
		unset($this->parties[strtolower($p->getName())]);

		$p->sendMessage(Utils::getPrefix() . "§cYour party is now disbanded.");
	}
}