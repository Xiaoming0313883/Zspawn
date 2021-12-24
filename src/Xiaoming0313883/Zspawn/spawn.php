<?php

declare(strict_types=1);

namespace Xiaoming0313883\Zspawn;

use pocketmine\entity\Entity;
use pocketmine\scheduler\Task as task;
use pocketmine\nbt\tag\StringTag;

class spawn extends task {
	private $delay;
    private $level;	
    private $position;
    private $count;
    private $mobid;

    public function __construct($delay,$level,$position,$mobid){
		$this->delay = $delay;
        $this->level = $level;	
        $this->position = $position;
        $this->count = $delay;
        $this->mobid = $mobid;
    }

    public function onRun($tick){
        if($this->count == "0"){
            $nbt = Entity::createBaseNBT($this->position, null, 0, 0);
            $entity = Entity::createEntity($this->mobid, $this->level, $nbt);
            $entity->spawnToAll();
            $entity->namedtag->setstring("pass","true");
            $this->count = $this->delay;
            if($entity->namedtag->hasTag("pass")){
                $health = (int)$entity->gethealth();
                $maxhealth = (int)$entity->getmaxhealth();
                $currnohealth = $maxhealth - $health . "\n";
                $namedtagtext = "Health\n($health/$maxhealth)";
                $entity->setnametag($namedtagtext);
                $entity->setNameTagAlwaysVisible(true);
            }
        } else {
            $this->count = ($this->count - 1);
        }
    }
}

