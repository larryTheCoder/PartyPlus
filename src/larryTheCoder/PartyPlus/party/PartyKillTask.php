<?php
/**
 *  This file is part of PartyPlus.
 *
 *  PartyPlus is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  PartyPlus is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with PartyPlus.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author larryTheCoder
 */

namespace larryTheCoder\PartyPlus\party;

use pocketmine\scheduler\Task;

class PartyKillTask extends Task {

	/** @var PartyHandler */
	private $partyHandler;

	public function __construct(PartyHandler $handler){
		$this->partyHandler = $handler;
	}

	/**
	 * Actions to execute when run
	 *
	 * @param int $currentTick
	 *
	 * @return void
	 */
	public function onRun(int $currentTick){
		if(!$this->partyHandler->getLeader()->isOnline()){
			$this->partyHandler->disbandParty(true);
		}
	}
}