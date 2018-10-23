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
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;

/**
 * Adds an invitation bus for the specific user
 * that tried to being invited to join the party.
 *
 * @package larryTheCoder\PartyPlus\invitation
 */
class InvitationBus {

	/** @var PartyMain */
	private $plugin;
	/** @var array */
	private $userPool;
	/** @var int[] */
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

	public function addInvitePool(Player $p, PartyHandler $party){
		$this->userPool[$p->getName()] = [$p, $party];
		$this->userTimeout[$p->getName()] = 0;
	}

	public function handleInvitePool(){
		foreach($this->userTimeout as $user => $time){
			if($time >= $this->inviteTimeout){
				$p = Server::getInstance()->getPlayer($user);
				$p->sendMessage($this->plugin->getPrefix() . "§cYour invitation has expired.");
				unset($this->userPool[$user]);
				unset($this->userTimeout[$user]);
			}
			$this->userTimeout[$user]++;
		}
	}
}