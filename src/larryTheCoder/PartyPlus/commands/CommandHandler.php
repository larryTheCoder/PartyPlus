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
	 * @param string        $commandLabel
	 * @param string[]      $args
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
					$sender->sendMessage("§d- §e/party invite [player] §c>§r Invite a player to your party.");
					$sender->sendMessage("§d- §e/party kick [player] §c>§r Kick a member from your party.");
					$sender->sendMessage("§d- §e/party accept [party] §c>§r Join a party from recent invitations");
					$sender->sendMessage("§d- §e/party decline [party] §c>§r Join a party from recent invitations");
					$sender->sendMessage("§d- §e/party invites §c>§r Check your pending invitations.");
					$sender->sendMessage("§d- §e/party disband §c>§r Disband your currently active party.");
					$sender->sendMessage("§d- §e/party leave §c>§r Leave your current party,");
					$sender->sendMessage("§d- §e/party create §c>§r Create a new party.");
					break;
				case "create":
					if($party->hasParty($sender->getName())){
						$sender->sendMessage(Utils::getPrefix() . "§cYou already owned a party.");

						break;
					}

					if($party->getParty($sender->getName()) == null){
						$sender->sendMessage(Utils::getPrefix() . "§cUnable to create you a party.");
					}
					break;
				case "accept":
					$inviteBus = $this->plugin->getInviteHandler();

					$playerInvites = $inviteBus->getInvites($sender);
					if($playerInvites == null){
						$sender->sendMessage(Utils::getPrefix() . "§6You have no new pending invitations.");

						break;
					}

					if(count($playerInvites) === 1){
						$inviteBus->acceptInvite($sender, 0);
					}else{
						if(isset($args[1])){
							$sender->sendMessage(Utils::getPrefix() . "§cYou have more than 1 pending invitations.");
							$sender->sendMessage(Utils::getPrefix() . "§cUse §d/p invites§c for more guidance.");
							break;
						}

						$pData = $args[1];
						if(is_numeric($pData) && isset($playerInvites[intval($pData)])){
							$inviteBus->acceptInvite($sender, intval($pData));
						}else{
							foreach($playerInvites as $inviteId => $handler){
								if(strtolower($handler->getLeader()->getName()) !== strtolower($pData)){
									continue;
								}

								$inviteBus->acceptInvite($sender, $inviteId);
								break 2;
							}

							$sender->sendMessage(Utils::getPrefix() . "§cInvalid parameters, §d/p invites§c for more guidance.");
						}
					}
					break;
				case "decline":
					$inviteBus = $this->plugin->getInviteHandler();

					$playerInvites = $inviteBus->getInvites($sender);
					if($playerInvites == null){
						$sender->sendMessage(Utils::getPrefix() . "§6You have no new pending invitations.");

						break;
					}

					if(count($playerInvites) === 1){
						$inviteBus->declineInvite($sender, 0);
					}else{
						if(isset($args[1])){
							$sender->sendMessage(Utils::getPrefix() . "§cYou have more than 1 pending invitations.");
							$sender->sendMessage(Utils::getPrefix() . "§cUse §d/p invites§c for more guidance.");
							break;
						}

						$pData = $args[1];
						if(is_numeric($pData) && isset($playerInvites[intval($pData)])){
							$inviteBus->declineInvite($sender, intval($pData));
						}else{
							foreach($playerInvites as $inviteId => $handler){
								if(strtolower($handler->getLeader()->getName()) !== strtolower($pData)){
									continue;
								}

								$inviteBus->declineInvite($sender, $inviteId);
								break 2;
							}

							$sender->sendMessage(Utils::getPrefix() . "§cInvalid parameters, §d/p invites§c for more guidance.");
						}
					}
					break;
				case "invites":
					$inviteBus = $this->plugin->getInviteHandler();

					$playerInvites = $inviteBus->getInvites($sender);
					if($playerInvites == null){
						$sender->sendMessage(Utils::getPrefix() . "§6You have no new pending invitations.");

						break;
					}

					$sender->sendMessage("§aYou have §6" . count($playerInvites) . "§a invites pending:");
					foreach($playerInvites as $id => $invite){
						$sender->sendMessage("§e$id §a-> §d{$invite->getLeader()->getName()}");
					}

					$sender->sendMessage("§aUse §d/party accept [Number/Player Name]");
					break;
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
					if(!$party->hasParty($sender->getName())){
						$sender->sendMessage(Utils::getPrefix() . "§cYou don't have a party!");

						break;
					}

					// Now check if the user is the leader.
					$plParty = $party->getParty($sender->getName());
					if(!$plParty->isLeader($sender->getName())){
						$sender->sendMessage(Utils::getPrefix() . "§cOnly your party leader can disband this party!");
						$sender->sendMessage(Utils::getPrefix() . "§cUse §e/p leave §c to leave your current party.");

						break;
					}

					// Now we can disband it.
					// Check if the player really wanted to disband the party.
					if(isset($this->disbandParties[$sender->getName()])
						&& (microtime(true) - $this->disbandParties[$sender->getName()]) <= 10){
						// The player decided to disband the party so lets do it.

						$party->disbandParty($sender);
						break;
					}

					$sender->sendMessage(Utils::getPrefix() . "§cAre you sure to disband your party?");
					$sender->sendMessage(Utils::getPrefix() . "§cType in §d/p disband§c again to confirm your request.");

					$this->disbandParties[$sender->getName()] = microtime(true);
					break;
				case "kick":
				case "setadmin":
				case "unsetadmin":
				case "leave":
				case "cancel":
					break;
			}
		}else{
			$sender->sendMessage("§cToo few parameter, Use /p help for a list of command");
		}

	}
}