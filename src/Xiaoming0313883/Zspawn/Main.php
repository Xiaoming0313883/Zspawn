<?php

declare(strict_types=1);

namespace Xiaoming0313883\Zspawn;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\level\particle\FloatingTextParticle;
use Xiaoming0313883\Zspawn\spawn;
use pocketmine\level\Position;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageEvent;

class Main extends PluginBase implements Listener{
    private $data = [];
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $config = new Config($this->getdatafolder() . "setting.yml",Config::YAML,array(
            "showtext" => true
        ));
        $data = new Config($this->getDataFolder() . "data.yml",Config::YAML,array(
            "id" => []
        ));
        if(count($data->get("id")) >= 1){
            foreach($data->get("id") as $id=>$data){
                $level = $this->getServer()->getLevelByName($data["level"]);
                $this->databasespawn($data["x"],$data["y"],$data["z"],$data["delay"],$level,$id);
            }
        }
    }
    public function onCommand(CommandSender $sender, Command $cmd, String $Label, Array $args) : bool{
        switch($cmd->getname()){
            case "zspawn":
                if(count($args) >= 1){
                    switch($args[0]){
                        case "spawn":
                            if(count($args) >= 2){
                                if(is_numeric($args[1])){
                                    $x = $sender->getX();
                                    $y = $sender->getY();
                                    $z = $sender->getZ();
                                    $this->spawn($x,$y,$z,$sender,(int)$args[1]);
                                    return true;
                                } else {
                                    $sender->sendMessage("delay must be a number");
                                }
                            }
                            $sender->sendMessage("Usage /zspawn spawn <delay(number)>");
                            return true;
                        break;

                        case "delete":
                            if(count($args) >= 2){
                                if(is_numeric($args[1])){
                                    if(isset($this->data["$args[1]"])){
                                        $this->removespawner($args[1],$sender);
                                        return true;
                                    } else {
                                        $sender->sendMessage("can't find id $args[1] spawner");
                                    }
                                }
                            }
                            $sender->sendMessage("usege /zspawn delete <id>");
                            return true;
                        break;
                    }
                }
                $sender->sendMessage("Zspawn Help");
                $sender->sendMessage("/zspawn spawn <delay>  ->>spawn zombie");
                $sender->sendMessage("/zspawn delete <id>  ->>delete spawner");
            break;
        }
        return true;
    }

    private function spawn($x,$y,$z,$player,$delay){
        $data = new Config($this->getDataFolder() . "data.yml",Config::YAML);
        $id = $data->get("id");
        $ran = mt_rand(1,100);
        if(isset($id["$ran"])){
            $this->spawn($x,$y,$z,$player,$delay);
        } else {
            $id["$ran"] = array("x" => $x,"y" => $y,"z" => $z,"delay" => $delay,"level" => $player->getlevel()->getname());
            $data->set("id",$id);
            $data->save();
            $player->sendMessage("summon successfully, the id is $ran");
            $text = "Zombie summon every $delay second\n$delay left to spawn next zombie\nspawn id: $ran";
            $particle = new FloatingTextParticle($player->getPosition(), $text);
            $player->getPosition()->getLevel()->addParticle($particle);
            $task = $this->getScheduler()->schedulerepeatingTask(new spawn($this,$x,$y,$z,$ran,$particle,$delay,$player->getlevel(),$player->getposition()),20);
            $this->data[$ran] = array("taskid" => $task->getTaskId(),"particle" => $particle,"level" => $player->getLevel());
        }
    }
    
    private function databasespawn($x,$y,$z,$delay,$level,$id){
        $position = new Position($x,$y,$z,$level);
        $text = "Zombie summon every $delay second\n$delay left to spawn next zombie\nspawn id: $id";
        $particle = new FloatingTextParticle($position, $text);
        $level->addParticle($particle);
        $task = $this->getScheduler()->schedulerepeatingTask(new spawn($this,$x,$y,$z,$id,$particle,$delay,$level,$position),20);
        $this->data[$id] = array("taskid" => $task->getTaskId(),"particle" => $particle,"level" => $level);
    }

    private function removespawner($id,$player){
        $data = $this->data[$id];
        $taskId = $data["taskid"];
        $particle = $data["particle"];
        $this->getScheduler()->cancelTask($taskId);
        $particle->setInvisible();
        $data["level"]->addParticle($particle);
        $dataa = new Config($this->getDataFolder() . "data.yml",Config::YAML);
        $dataId = $dataa->get("id");
        unset($dataId[$id]);
        $dataa->set("id",$dataId);
        $dataa->save();
        unset($this->data[$id]);
        $player->sendMessage("successfully deleted");
    }

    public function updateHealthBar(EntityDamageEvent $e){
        if($e->getEntity()->namedtag->hasTag("pass")){
            $health = (int)$e->getEntity()->getHealth();
            $maxHealth = (int)$e->getEntity()->getMaxHealth();
            $currNoHealth = $maxHealth - $health . "\n";
            $namedTagText = "Health";
            for($i = 0;$i < (int)$health;$i++){
                $namedTagText = $namedTagText . "§2|";
            }
            for($a = 0;$a < (int)$currNoHealth;$a++){
                $namedTagText = $namedTagText . "§4|";
            }
            $e->getEntity()->setNameTag($namedTagText);
            $e->getEntity()->setNameTagAlwaysVisible(true);
        }
    }

}
