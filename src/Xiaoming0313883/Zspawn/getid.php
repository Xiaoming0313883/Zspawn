<?php
declare(strict_types=1);

namespace Xiaoming0313883\Zspawn;

class getid {
    /**
     * @var int[]
     */
    public static $mob_id = array("CHICKEN" => 10,
        "COW" => 11,
        "PIG" => 12,
        "SHEEP" => 13,
        "WOLF" => 14,
        "VILLAGER" => 15,
        "MOOSHROOM" => 16,
        "SQUID" => 17,
        "RABBIT" => 18,
        "BAT" => 19,
        "IRON_GOLEM" => 20,
        "SNOW_GOLEM" => 21,
        "OCELOT" => 22,
        "HORSE" => 23,
        "DONKEY" => 24,
        "MULE" => 25,
        "SKELETON_HORSE" => 26,
        "ZOMBIE_HORSE" => 27,
        "POLAR_BEAR" => 28,
        "LLAMA" => 29,
        "PARROT" => 30,
        "DOLPHIN" => 31,
        "ZOMBIE" => 32,
        "CREEPER" => 33,
        "SKELETON" => 34,
        "SPIDER" => 35,
        "ZOMBIE_PIGMAN" => 36,
        "SLIME" => 37,
        "ENDERMAN" => 38,
        "SILVERFISH" => 39,
        "CAVE_SPIDER" => 40,
        "GHAST" => 41,
        "MAGMA_CUBE" => 42,
        "BLAZE" => 43,
        "ZOMBIE_VILLAGER" => 44,
        "WITCH" => 45,
        "STRAY" => 46,
        "HUSK" => 47,
        "WITHER_SKELETON" => 48,
        "GUARDIAN" => 49,
        "ELDER_GUARDIAN" => 50,
        "NPC" => 51,
        "WITHER" => 52,
        "ENDER_DRAGON" => 53,
        "SHULKER" => 54,
        "ENDERMITE" => 55,
        "AGENT" => 56,
        "VINDICATOR" => 57,
        "PHANTOM" => 58,
        "TURTLE" => 74,
        "CAT" => 75,
        "PANDA" => 113
    );



    public static function getid($name){
        if(isset(self::$mob_id[strtoupper($name)])){
            return self::$mob_id[strtoupper($name)];
        }
        return false;
    }

    public static function checkid($id){
        if(in_array($id,self::$mob_id)){
            return true;
        }
        return false;
    }

    public static function getname($id){
        $array_s = array_search($id,self::$mob_id);
        return $array_s;
    }
    
}
