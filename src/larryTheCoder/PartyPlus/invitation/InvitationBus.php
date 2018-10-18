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

use larryTheCoder\PartyPlus\PartyMain;
use pocketmine\scheduler\AsyncTask;
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
	/** @var int[] */
	private $userPool;

	public function __construct(PartyMain $plugin){
		$this->plugin = $plugin;

		// This actually unnecessary to tick them on other thread.
		// But its good to practice to use it ¯\_(ツ)_/¯
		$plugin->getServer()->getAsyncPool()->submitTask(new class($this) extends AsyncTask {

			private $inviteBus;
			private $pool;

			public function __construct(InvitationBus $bus){
				$this->inviteBus = $bus;
				$this->pool = $bus->getInvitePool();
			}

			public function onRun(){
				foreach($this->pool as $user => $time){
					$this->pool[$user]++;
				}
			}

			public function onCompletion(Server $server){
				$this->inviteBus->flagInvites($this->pool);
				if(empty($this->pool)){
					$this->inviteBus->getPlugin()->getScheduler()->scheduleDelayedTask(new class($this, $server) extends Task {

						private $pool;
						private $server;

						public function __construct($anonymousClass, Server $server){
							$this->pool = $anonymousClass;
							$this->server = $server;
						}

						public function onRun(int $currentTick){
							if(!$this->pool->inviteBus->getPlugin()->isDisabled()){
								$this->server->getAsyncPool()->submitTask($this); // Submit this task again
							}
						}
					}, 60);

					return;
				}
				if(!$this->inviteBus->getPlugin()->isDisabled()){
					$server->getAsyncPool()->submitTask($this); // Submit this task again
				}
			}
		});
	}

	/**
	 * Retrieves the invitation pool
	 */
	public function getInvitePool(): array{
		return $this->userPool;
	}

	/**
	 * @param $pool
	 */
	public function flagInvites($pool){
	}

	public function getPlugin(): PartyMain{
		return $this->plugin;
	}

	public function addInvitePool(string $player){

	}
}