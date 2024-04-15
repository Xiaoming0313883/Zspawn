<?php

declare(strict_types=1);

namespace Xiaoming0313883\Zspawn;

use pocketmine\entity\Entity;
use pocketmine\entity\EntityFactory;
use pocketmine\scheduler\Task as task;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\world\Position;

class SpawnTask extends task {
	private $delay;
    private $level;	
    private Position $position;
    private $count;
    private $mobid;

    public function __construct($delay,$level,Position $position,$mobid){
		$this->delay = $delay;
        $this->level = $level;	
        $this->position = $position;
        $this->count = $delay;
        $this->mobid = $mobid;
    }

    public function onRun(): void{
        if($this->count == "0"){
            $nbt = (new CompoundTag())->setString("typeId", $this->mobid);
            $entity = new Living($this->position,$nbt);
            $entity->spawnToAll();
            $entity->setNameTag("pass");
            $this->count = $this->delay;
            $health = (int)$entity->gethealth();
            $maxhealth = (int)$entity->getmaxhealth();
            $namedtagtext = "Health\n($health/$maxhealth)";
            $entity->setnametag($namedtagtext);
            $entity->setNameTagAlwaysVisible(true);
        } else {
            $this->count = ($this->count - 1);
        }
        return;
    }
}

