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

namespace larryTheCoder\PartyPlus\commands;


use larryTheCoder\PartyPlus\PartyMain;
use larryTheCoder\PartyPlus\Utils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;

class CommandHandler extends Command {

	private $plugin;
	private $disbandParties = [];

	public function __construct(PartyMain $plugin){
		parent::__construct("party", "Main party command", "[args]", ["p"]);
		$this->plugin = $plugin;
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param string[] $args
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!($sender instanceof Player)){
			$sender->sendMessage("Please run this command in-game");

			return;
		}
		$party = $this->plugin->getParty();

		if(isset($args[0])){
			switch(strtolower($args[0])){
				case "help":
					$sender->sendMessage("§aAll Party commands:");
					$sender->sendMessage("§d- §e/party invite [player]");
					$sender->sendMessage("§d- §e/party invites");
					$sender->sendMessage("§d- §e/party disband");
					$sender->sendMessage("§d- §e/party kick [player]");
					$sender->sendMessage("§d- §e/party leave");
					$sender->sendMessage("§d- §e/party join [party]");
					break;
				case "invites":

				case "invite":
					// Check if the player in a party
					if($args[1] == null){
						$sender->sendMessage(Utils::getPrefix() . "§c /party invite [player]");

						break;
					}

					// Check if the player has a party and the player administrate that party.
					if($party->hasParty($sender->getName()) && $party->getParty($sender->getName())->isAdmin($sender->getName())){
						$sender->sendMessage(Utils::getPrefix() . "§cYou don't have privileges to invite that user to your party!");
						$sender->sendMessage(Utils::getPrefix() . "§cAsk your party leader to invite that user.");

						break;
					}

					$p = Server::getInstance()->getPlayer($args[1]);
					$party = $party->getParty($sender->getName());
					if(is_null($p)){
						$sender->sendMessage(Utils::getPrefix() . "§cAre you sure that player is online?");
					}else{
						$this->plugin->getInviteHandler()->addInvitePool($p, $party);
						$sender->sendMessage(Utils::getPrefix() . "§bYou have successfully invited §d" . $args[1] . "§b to your party");
					}
					break;
				case "disband":
					// Check if the user has a party.
					if($party->hasParty($sender->getName())){
						$sender->sendMessage(Utils::getPrefix() . "§cYou don't have a party!");

						break;
					}

					// Now check if the user is the leader.
					if($party->hasParty($sender->getName()) && $party->getParty($sender->getName())->isLeader($sender->getName())){
						$sender->sendMessage(Utils::getPrefix() . "§cOnly your party leader can disband this party!");
						$sender->sendMessage(Utils::getPrefix() . "§cUse &e/p leave &c to leave your current party.");

						break;
					}

					// Now we can disband it.
					// Check if the player really wanted to disband the party.
					if(isset($this->disbandParties[$sender->getName()])
						&& (microtime(true) - $this->disbandParties[$sender->getName()]) <= 10){
						// The player decided to disband the party so lets do it.

						$party->disbandParty($sender);
					}

					$sender->sendMessage(Utils::getPrefix() . "§cAre you sure to disband your party?");
					$sender->sendMessage(Utils::getPrefix() . "§cType in §d/p disband§c again to confirm your request.");

					$this->disbandParties[$sender->getName()] = microtime(true);
					break;
				case "kick":
					break;
				case "setadmin":
					break;
				case "unsetadmin":
					break;
				case "leave":
					break;
				case "cancel":
					break;
			}
		}else{
			$sender->sendMessage("§cToo few parameter, Use /p help for a list of command");
		}

	}
}