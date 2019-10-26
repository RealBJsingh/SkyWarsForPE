<?php
/**
 * Adapted from the Wizardry License
 *
 * Copyright (c) 2015-2019 larryTheCoder and contributors
 *
 * Permission is hereby granted to any persons and/or organizations
 * using this software to copy, modify, merge, publish, and distribute it.
 * Said persons and/or organizations are not allowed to use the software or
 * any derivatives of the work for commercial use or any other means to generate
 * income, nor are they allowed to claim this software as their own.
 *
 * The persons and/or organizations are also disallowed from sub-licensing
 * and/or trademarking this software without explicit permission from larryTheCoder.
 *
 * Any persons and/or organizations using this software must disclose their
 * source code and have it publicly available, include this license,
 * provide sufficient credit to the original authors of the project (IE: larryTheCoder),
 * as well as provide a link to the original project.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,FITNESS FOR A PARTICULAR
 * PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE
 * USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace larryTheCoder\arena\tasks;


use larryTheCoder\arena\api\DefaultGameAPI;
use larryTheCoder\arena\Arena;
use larryTheCoder\arena\State;
use larryTheCoder\SkyWarsPE;
use pocketmine\Player;
use pocketmine\scheduler\Task;

class ArenaGameTick extends Task {

	// In a classic SW game, there is not ending time.
	// Therefore, all the players have to die in order to find
	// The true winner.

	/**@var Arena */
	private $arena;
	/** @var SkyWarsPE */
	private $plugin;
	/** @var DefaultGameAPI */
	private $gameAPI;

	/** @var int */
	private $startTime;
	/** @var int */
	private $stopTime;

	public function __construct(Arena $arena, DefaultGameAPI $gameAPI){
		$this->arena = $arena;
		$this->plugin = $gameAPI->plugin;
		$this->gameAPI = $gameAPI;
	}

	/**
	 * Actions to execute when run
	 *
	 * @param int $currentTick
	 *
	 * @return void
	 */
	public function onRun(int $currentTick){
		$this->checkLevelTime();
		$this->gameAPI->statusUpdate();
		switch($this->arena->getStatus()){
			case State::STATE_WAITING:
				if($this->arena->getPlayersCount() > $this->arena->minimumPlayers - 1){
					$this->arena->setStatus(State::STATE_SLOPE_WAITING);
					break;
				}

				foreach($this->arena->getPlayers() as $p){
					if($this->startTime < 60){
						$p->sendPopup($this->plugin->getMsg($p, "arena-low-players", false));
					}else{
						$p->sendPopup($this->plugin->getMsg($p, "arena-wait-players", false));
					}
				}

				$this->startTime = 60;
				$this->stopTime = 0;
				break;
			case State::STATE_SLOPE_WAITING:
			case State::STATE_ARENA_RUNNING:
				break;
			case State::STATE_ARENA_CELEBRATING:
				break;
		}

		foreach($this->arena->getPlayers() as $pl){
			$this->gameAPI->scoreboard->updateScoreboard($pl);
		}
	}

	private function useScoreboard(){

	}

	private function tickBossBar(Player $p, int $id, $data = null){
		// TODO: Boss bar feature.
	}

	private function checkLevelTime(){
		$tickTime = $this->arena->arenaTime;
		if(!$tickTime){
			return;
		}

		$this->arena->getLevel()->setTime($tickTime);
		$this->arena->getLevel()->stopTime();
	}
}