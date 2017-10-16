<?php

namespace classicprison\mine\task;

use classicprison\Main;
use classicprison\mine\Mine;
use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class MineResetTask extends AsyncTask {

	/** @var string */
	private $name;

	/** @var string */
	private $chunks;

	/** @var Vector3 */
	private $a;

	/** @var Vector3 */
	private $b;

	/** @var array */
	private $ratioData;

	/** @var int */
	private $levelId;

	/** @var int */
	private $chunkCount = -1;

	/** @var int */
	private $totalBlocks = -1;

	/** @var int */
	private $resetBlocks = -1;

	public function __construct(Mine $mine) {
		$this->name = $mine->getName();
		$this->chunks = [];
		$this->a = $mine->getA();
		$this->b = $mine->getB();
		$this->ratioData = serialize($mine->getRatios());

		$chunks = [];
		for($maxX = $this->b->getX(), $x = $this->a->getX(); $x - 16 <= $maxX; $x += 16) {
			for($maxZ = $this->b->getZ(), $z = $this->a->getZ(); $z - 16 <= $maxZ; $z += 16) {
				$chunks[Level::chunkHash($chunkX, $chunkZ)] = $mine->getLevel()->getChunk($chunkX = $x >> 4, $chunkZ = $z >> 4)->fastSerialize();
			}
		}

		$this->chunks = serialize($chunks);
	}

	public function onRun() {
		/** @var Chunk[] $chunks */
		$chunks = unserialize($this->chunks);
		foreach($chunks as $hash => $binary) {
			$chunks[$hash] = Chunk::fastDeserialize($binary);
		}

		$ratioData = unserialize($this->ratioData);
		$blocks = [];
		foreach($ratioData as $i => $blockData) {
			$block = explode(":", $blockData[1]);
			$blocks[$i] = $block;
		}

		$sum = [];
		for($l = 0, $count = count($ratioData); $l < $count; $l++) {
			$sum[$l] = $sum[$l] + $ratioData[$l][0];
		}

		$sumCount = count($sum);

		$a = $this->a;
		$b = $this->b;

		$chunkCount = 0;
		$totalBlocks = ($b->x - $a->x + 1) * ($b->y - $a->y + 1) * ($b->z - $a->z + 1);
		$currentBlocks = 0;

		$currentChunkX = $a->x >> 4;
		$currentChunkZ = $a->z >> 4;

		$currentChunkY = $a->y >> 4;

		$currentChunk = null;
		$currentSubChunk = null;

		for($x = $a->getX(), $x2 = $b->getX(); $x <= $x2; $x++) {
			$chunkX = $x >> 4;
			for($z = $a->getZ(), $z2 = $b->getZ(); $z <= $z2; $z++) {
				$chunkZ = $z >> 4;
				if($currentChunk === null or $chunkX !== $currentChunkX or $currentChunkZ !== $currentChunkZ) {
					$currentChunkX = $chunkX;
					$currentChunkZ = $chunkZ;

					$hash = Level::chunkHash($chunkX, $chunkZ);
					$currentChunk = $chunks[$hash];
					if($currentChunk === null) {
						continue;
					}
					$chunkCount++;
				}

				for($y = $a->getY(), $y2 = $b->getY(); $y <= $y2; $y++) {
					$chunkY = $y >> 4;

					if($currentSubChunk === null or $chunkY !== $currentChunkY) {
						$currentChunkY = $chunkY;

						$currentSubChunk = $currentChunk->getSubChunk($chunkY, true);
						if($currentSubChunk === null) {
							continue;
						}
					}

					$a = rand(0, end($sun));
					for($l = 0; $l < $sumCount; $l++) {
						if($a <= $sum[$l]) {
							$currentSubChunk->setBlock($x & 0x0f, $y & 0x0f, $z & 0x0f, $blocks[$l][0] & 0xff, $blocks[$l][1] & 0xff);
							$currentBlocks++;
						}
						break;
					}
				}
			}
		}

		$this->chunkCount = $chunkCount;
		$this->totalBlocks = $totalBlocks;
		$this->resetBlocks = $currentBlocks;
		$this->setResult($chunks);
	}

	public function onCompletion(Server $server) {
		$chunks = $this->getResult();
		$plugin = $server->getPluginManager()->getPlugin("ClassicPrisonCore");
		if($plugin instanceof Main and $plugin->isEnabled()) {
			$level = $server->getLevel($this->levelId);
			if($level instanceof Level) {
				foreach($chunks as $hash => $chunk) {
					Level::getXZ($hash, $x, $z);
					$level->setChunk($x, $z, true);
				}
			}
			$plugin->getLogger()->debug("Reset {$this->resetBlocks}/{$this->totalBlocks} across " . count($chunks) . " chunks!");
		}
	}

}