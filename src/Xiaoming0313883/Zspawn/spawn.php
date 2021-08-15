<?php

declare(strict_types=1);

namespace Xiaoming0313883\Zspawn;

use pocketmine\entity\Entity;
use pocketmine\scheduler\Task as task;
use pocketmine\nbt\tag\StringTag;

class spawn extends task {
    private $plugin;	
	private $x;	
	private $y;	
	private $z;	
	private $id;	
	private $particle;	
	private $delay;
    private $level;	
    private $position;
    private $count;

    public function __construct($plugin,$x,$y,$z,$id,$particle,$delay,$level,$position){
        $this->plugin = $plugin;	
		$this->x = $x;	
		$this->y = $y;	
		$this->z = $z;	
		$this->id = $id;	
		$this->particle = $particle;	
		$this->delay = $delay;
        $this->level = $level;	
        $this->position = $position;
        $this->count = $delay;
    }

    public function onRun($tick){
        if($this->count == "0"){
            $nbt = Entity::createBaseNBT($this->position, null, 0, 0);
            $entity = Entity::createEntity("Zombie", $this->level, $nbt);
            $entity->spawnToAll();
            $entity->namedtag->setstring("pass","true");
            $this->count = $this->delay;
            $text = "Zombie summon every $this->delay second\n$this->count left to spawn next zombie\nspawn id: $this->id";
            $this->particle->setText($text);
            $this->position->getLevel()->addParticle($this->particle);
            if($entity->namedtag->hasTag("pass")){
                $health = (int)$entity->gethealth();
                $maxhealth = (int)$entity->getmaxhealth();
                $currnohealth = $maxhealth - $health . "\n";
                $namedtagtext = "Health";
                for($i = 0;$i < (int)$health;$i++){
                    $namedtagtext = $namedtagtext . "§2|";
                }
                for($a = 0;$a < (int)$currnohealth;$a++){
                    $namedtagtext = $namedtagtext . "§4|";
                }
                $entity->setnametag($namedtagtext);
                $entity->setNameTagAlwaysVisible(true);
            }
        } else {
            $this->count = ($this->count - 1);
            $text = "Zombie summon every $this->delay second\n" . $this->count . " left to spawn next zombie\nspawn id: $this->id";
            $this->particle->setText($text);
            $this->position->getLevel()->addParticle($this->particle);
        }
    }
}
