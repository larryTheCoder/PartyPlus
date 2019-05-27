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

namespace larryTheCoder\PartyPlus;

use larryTheCoder\PartyPlus\commands\CommandHandler;
use larryTheCoder\PartyPlus\invitation\InvitationBus;
use larryTheCoder\PartyPlus\party\Party;
use pocketmine\plugin\PluginBase;

class PartyMain extends PluginBase {

	/** @var PartyMain */
	private static $instance = null;
	/** @var Party */
	private $party = null;
	/** @var InvitationBus */
	private $inviteHandler;

	public static function getInstance(){
		return self::$instance;
	}

	public function onLoad(){
		self::$instance = $this;
	}

	public function onEnable(){
		$this->party = new Party($this);
		$this->inviteHandler = new InvitationBus($this);

		$this->getServer()->getCommandMap()->register("Party", new CommandHandler($this));

		Utils::send("&aEnabled &ePartyPlus &7v1.0");
	}

	/**
	 * Returns a class that handles all of the
	 * invitations from all of the party leaders.
	 *
	 * @return InvitationBus
	 */
	public function getInviteHandler(): InvitationBus{
		return $this->inviteHandler;
	}

	/**
	 * Gets the party class that handles all
	 * the player parties.
	 *
	 * @return Party class
	 */
	public function getParty(): Party{
		return $this->party;
	}

	/**
	 * Gets the prefix for this plugin.
	 *
	 * @return string
	 */
	public function getPrefix(): string{
		return "&c&lParty >&r ";
	}
}