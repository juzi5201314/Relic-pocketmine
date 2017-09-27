<?php

namespace net\orange\soeur\relic\command;

use pocketmine\command\CommandSender;
use pocketmine\command\defaults\VanillaCommand;
use pocketmine\Player;
use pocketmine\math\Vector3;

use net\orange\soeur\relic\Main;

class ReCommand extends VanillaCommand {

private $pos1;
private $pos2;

public function __construct($name){
parent::__construct($name, '遗迹插件指令', '/re', ["re", "relic"]);
$this->setPermission("relic.command.re");
$this->main = Main::getThis();
}

public function execute(CommandSender $sender, $currentAlias, array $args){
if(!$this->testPermission($sender)){
return true;
}
if(!$sender instanceof Player){
$sender->sendMessage("§c请进入游戏中使用指令");
return true;
}

switch(count($args)){
case 1:
/*
重载遗迹数据
*/
if($args[0] == "reload"){
$this->main->reloadRelics();
$sender->sendMessage("§3重载遗迹数据成功");
}else if($args[0] == "load"){
/*
在当前世界生成一遍遗迹
*/
$sender->sendMessage("§2生成遗迹中");
foreach($sender->level->getChunks() as $chunk){
$this->main->createRelic($chunk, $sender->level);
}
$sender->sendMessage("§3遗迹生成完成");
}
break;

case 2:
/*
选择坐标
*/
if($args[0] == "pos"){
if($args[1] == "1"){
$this->pos1 = new Vector3($sender->x, $sender->y, $sender->z);
}else{
$this->pos2 = new Vector3($sender->x, $sender->y, $sender->z);
}
$sender->sendMessage("§6成功选择第".$args[1]."点");
}
break;

case 5:
/*
args1: 遗迹文件名字
args2: 生成几率
args3: 生成y轴高度
args4: 生成y轴高度
*/
if($args[0] == "create"){
if(!$this->pos1 instanceof Vector3 or !$this->pos2 instanceof Vector3){
$sender->sendMessage("§c请选择第一与第二点");
return true;
}
$sender->sendMessage("§3复制方块数据中，请安静等待。否则有可能出现错误");
$max_x = max($this->pos1->x, $this->pos2->x);
$min_x = min($this->pos1->x, $this->pos2->x);
$max_y = max($this->pos1->y, $this->pos2->y);
$min_y = min($this->pos1->y, $this->pos2->y);
$max_z = max($this->pos1->z, $this->pos2->z);
$min_z = min($this->pos1->z, $this->pos2->z);
$data = [];
/*
循环获取2个坐标之间的所有坐标与方块id，特殊值
*/
for($x = $min_x; $x <= $max_x; ++ $x){
for($y = $min_y; $y <= $max_y; ++ $y){
for($z = $min_z; $z <= $max_z; ++ $z){
$data[] = ($max_x - $x).':'.($max_y - $y).':'.($max_z - $z).':'.$sender->level->getBlockIdAt($x, $y, $z).':'.$sender->level->getBlockDataAt($x, $y, $z);
}
}
}

/*
创建遗迹文件并写入数据
*/
file_put_contents($this->main->getDataFolder().'/relics/'.$args[1].'.os', json_encode([
'odds' => $args[2],
'y_max' => max($args[3], $args[4]),
'y_min' => min($args[3], $args[4]),
'pos' => $data
]));
$sender->sendMessage("§i§6创建遗迹文件成功。请前往/plugins/relic/relics/文件夹查看");
}
break;

default:
return false;
}

return true;
}

}
