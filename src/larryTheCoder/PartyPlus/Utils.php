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


use pocketmine\Server;
use pocketmine\utils\TextFormat;

class Utils {

	public static function send($msg){
		Server::getInstance()->getLogger()->info(self::getPrefix() . TextFormat::colorize($msg));
	}

	/**
	 * Gets the prefix for this plugin.
	 *
	 * @return string
	 */
	public static function getPrefix(): string{
		return "§aParty §c>>§r ";
	}
}