<?php

declare(strict_types=1);

namespace Xiaoming0313883\Zspawn;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\world\Position;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\player\Player;

class Main extends PluginBase implements Listener{
    private $data = [];
    private $mob = [

    ];
    public function onEnable(): void{
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $config = new Config($this->getdatafolder() . "setting.yml",Config::YAML,array(
            "showtext" => true
        ));
        $data = new Config($this->getdatafolder() . "data.yml",Config::YAML,array(
            "id" => []
        ));
        $this->getLogger()->info("Plugin Enabled");
        $this->getLogger()->notice("Thank you for using Zspawn, support the developer https://paypal.me/gohkai1");
        if(count($data->get("id")) >= 1){
            foreach($data->get("id") as $id=>$data){
                $level = $this->getserver()->getWorldManager()->getWorldByName($data["level"]);
                $this->spawnon($data["x"],$data["y"],$data["z"],$data["delay"],$level,$id,$data["mob_id"]);
            }
        }
    }
    public function onCommand(CommandSender $sender, Command $cmd, String $Label, Array $args) : bool{
        if(!($sender instanceof Player)){
            $sender->sendMessage("USE IT IN GAME!");
            return true;
        }
        switch($cmd->getname()){
            case "zspawn":
                if(count($args) >= 1){
                    switch($args[0]){
                        case "spawn":
                            if(count($args) >= 3){
                                if(is_numeric($args[2])){
                                    if(is_numeric($args[1])){
                                        if(Ids::checkid($args[1])){
                                            $id = $args[1];
                                        } else {
                                            $sender->sendmessage("invalid mob id $args[1], please check it with /zspawn list");
                                            return true;
                                        }
                                    } else {
                                        if(Ids::getid($args[1])){
                                            $id = Ids::getid($args[1]);
                                        } else {
                                            $sender->sendMessage("invalid mob name $args[1], please check it with /zspawn list");
                                            return true;
                                        }
                                    }
                                    $position = $sender->getPosition();
                                    $x = $position->getX();
                                    $y = $position->getY();
                                    $z = $position->getZ();
                                    $this->spawn($x,$y,$z,$sender,(int)$args[2],$id);
                                    return true;
                                } else {
                                    $sender->sendmessage("delay must be a number");
                                }
                            }
                            $sender->sendmessage("Usage /zspawn spawn <delay(number)>");
                            return true;
                        case "delete":
                            if(count($args) >= 2){
                                if(is_numeric($args[1])){
                                    if(isset($this->data["$args[1]"])){
                                        $this->remove($args[1],$sender);
                                        return true;
                                    } else {
                                        $sender->sendmessage("can't find id $args[1] spawner");
                                    }
                                }
                            }
                            $sender->sendmessage("usege /zspawn delete <id>");
                            return true;

                        case "list":
                            $list = Ids::$mob_id;
                            $sender->sendMessage("mob's name => id");
                            foreach($list as $mob_name=>$mob_id){
                                $sender->sendMessage("$mob_name => $mob_id");
                            }
                            $sender->sendMessage("END");
                            break;

                        case "list-id":
                            if(file_exists($this->getDataFolder() . "data.yml")) {
                                $data = new Config($this->getDataFolder() . "data.yml",Config::YAML);
                                $sender->sendMessage("§e---------------------START---------------------");
                                foreach ($data->get("id") as $id=>$value){
                                    $sender->sendMessage("ID:$id  Position: X=".$value["x"]." Y=".$value["y"]." Z=".$value["z"] . " World:".$value["level"] . " Type:" . getid::getname($value["mob_id"]));
                                }
                                $sender->sendMessage("§e---------------------END---------------------");
                                return true;
                            } else {
                                $sender->sendMessage("§4You haven't create a spawner yet!");
                                return true;
                            }
                            break;
                    }
                }
                $sender->sendmessage("Zspawn Help");
                $sender->sendmessage("/zspawn spawn <mob's id | name> <delay>  -> spawn spawner");
                $sender->sendmessage("/zspawn delete <id>  -> delete spawner");
                $sender->sendmessage("/zspawn list-id -> list all spawner");
            break;
        }
        return true;
    }

    private function spawn($x,$y,$z,$player,$delay,$mob_id){
        $mobname = Ids::getname($mob_id);
        $data = new Config($this->getdatafolder() . "data.yml",Config::YAML);
        $id = $data->get("id");
        $ran = mt_rand(1,100);
        if(isset($id["$ran"])){
            $this->spawn($x,$y,$z,$player,$delay,$id);
        } else {
            $id["$ran"] = array("x" => $x,"y" => $y,"z" => $z,"delay" => $delay,"mob_id" => $mob_id,"level" => $player->getWorld()->getDisplayName());
            $data->set("id",$id);
            $data->save();
            $player->sendmessage("summon successfully, the id is $ran");
            $task = $this->getScheduler()->schedulerepeatingTask(new SpawnTask($delay,$player->getWorld(),$player->getposition(),$mob_id),20);
            $this->data[$ran] = array("task" => $task,"level" => $player->getlevel());
        }
    }
    
    private function spawnon($x,$y,$z,$delay,$level,$id,$mobid){
        $position = new Position($x,$y,$z,$level);
        $task = $this->getScheduler()->schedulerepeatingTask(new SpawnTask($delay,$level,$position,$mobid),20);
        $this->data[$id] = array("task" => $task,"level" => $level);
    }

    private function remove($id,$player){
        $data = $this->data[$id];
        $task = $data["task"];
        $task->cancel();
        $dataa = new Config($this->getdatafolder() . "data.yml",Config::YAML);
        $dataid = $dataa->get("id");
        unset($dataid[$id]);
        $dataa->set("id",$dataid);
        $dataa->save();
        unset($this->data[$id]);
        $player->sendmessage("successfully deleted");
    }

    public function ondamage(EntityDamageEvent $e){
        if($e->getentity()->getNameTag() === "pass"){
            $health = (int)$e->getentity()->gethealth() - $e->getFinalDamage();
            $maxhealth = (int)$e->getentity()->getmaxhealth();
            $namedtagtext = "Health\n($health/$maxhealth)";
            $e->getentity()->setnametag($namedtagtext);
            $e->getentity()->setNameTagAlwaysVisible(true);
        }
    }

}
