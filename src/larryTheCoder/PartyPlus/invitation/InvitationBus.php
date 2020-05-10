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

namespace larryTheCoder\PartyPlus\invitation;

use larryTheCoder\PartyPlus\party\PartyHandler;
use larryTheCoder\PartyPlus\PartyMain;
use larryTheCoder\PartyPlus\Utils;
use pocketmine\Player;
use pocketmine\scheduler\Task;

/**
 * Adds an invitation bus for the specific user
 * that tried to being invited to join the party.
 *
 * @package larryTheCoder\PartyPlus\invitation
 */
class InvitationBus {

	/** @var PartyMain */
	private $plugin;
	/** @var PartyHandler[][] */
	private $userPool;
	/** @var int[][] */
	private $userTimeout = [];
	/** @var int */
	private $inviteTimeout = 20;

	public function __construct(PartyMain $plugin){
		$this->plugin = $plugin;

		$plugin->getScheduler()->scheduleRepeatingTask(new class($this) extends Task {

			private $handler;

			public function __construct(InvitationBus $handler){
				$this->handler = $handler;
			}

			/**
			 * Actions to execute when run
			 *
			 * @param int $currentTick
			 *
			 * @return void
			 */
			public function onRun(int $currentTick){
				$this->handler->handleInvitePool();
			}
		}, 20);
	}

	/**
	 * Returns the available invites from the player
	 * given. This will return the list of the
	 * PartyHandler itself.
	 *
	 * @param Player $p
	 * @return PartyHandler[]|null
	 */
	public function getInvites(Player $p){
		return !isset($this->userPool[$p->getName()]) ? null : $this->userPool[$p->getName()];
	}

	/**
	 * Used to accept the invitation from a party leader.
	 * All other invites will be revoked after an invite is
	 * being accepted.
	 *
	 * @param Player $p
	 * @param int    $inviteId
	 */
	public function acceptInvite(Player $p, int $inviteId = 0){
		if(isset($this->userPool[$p->getName()]) && isset($this->userPool[$p->getName()][$inviteId])){
			/** @var PartyHandler $party */
			$party = $this->userPool[$p->getName()][$inviteId][1];
			$party->addMember($p);

			unset($this->userPool[$p->getName()][$inviteId]);
			unset($this->userTimeout[$p->getName()][$inviteId]);

			$party->notifyLeader(Utils::getPrefix() . "§d{$p->getName()}§6 accepted your party invite request.");
		}
	}

	/**
	 * Declines an invite from a leader, this also notifies leader about the player
	 * rejecting his/her request from earlier invite.
	 *
	 * @param Player $p
	 * @param int    $inviteId
	 */
	public function declineInvite(Player $p, int $inviteId = 0){
		if(isset($this->userPool[$p->getName()]) && isset($this->userPool[$p->getName()][$inviteId])){
			/** @var PartyHandler $party */
			$party = $this->userPool[$p->getName()][$inviteId][1];

			unset($this->userPool[$p->getName()][$inviteId]);
			unset($this->userTimeout[$p->getName()][$inviteId]);

			$party->notifyLeader(Utils::getPrefix() . "§d{$p->getName()}§c rejected your party invite request.");
		}
	}

	/**
	 * Adds the player to the invite pool.
	 * The player will be ticked every x seconds and after
	 * the timeout, the player will be notified about it and
	 * that player will not be able to join it again.
	 *
	 * @param Player       $p
	 * @param PartyHandler $party
	 */
	public function addInvitePool(Player $p, PartyHandler $party){
		$this->userPool[$p->getName()][] = [$p, $party];
		$this->userTimeout[$p->getName()][] = 0;

		$p->sendMessage(Utils::getPrefix() . "§bYou have a party invitation from §d" . $party->getLeader()->getName());
		$p->sendMessage(Utils::getPrefix() . "§bUse §e/p accept §bto accept that party invitation.");
	}

	function handleInvitePool(){
		foreach($this->userTimeout as $user => $inviteId){
			foreach($inviteId as $useId => $time){
				$this->userTimeout[$user]++;
				if($time >= $this->inviteTimeout){
					/** @var Player $p */
					/** @var PartyHandler $party */
					$p = $this->userPool[$user][$useId][0];
					$party = $this->userPool[$user][$useId][1];

					// Messages
					$p->sendMessage(Utils::getPrefix() . "§cYour invitation from §e{$party->getLeader()->getName()}§c has expired.");
					$party->notifyLeader(Utils::getPrefix() . "§d{$user}§c rejected your party invite request.");

					unset($this->userPool[$user][$useId]);
					unset($this->userTimeout[$user][$useId]);
				}
			}
		}
	}
}