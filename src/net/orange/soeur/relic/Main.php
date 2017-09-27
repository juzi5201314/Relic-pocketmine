<?php

/*
作者: 橘子(orange)
QQ: 1034236490
日期: 2017/09/26 星期二
*/

/*
开源与注释仅供各位新人朋友参考
拒绝倒卖与抄袭
大佬勿进(喷)
*/

/*
因为上学用手机写
懒得缩进
见谅
*/

/*
1.0.0 插件完成 2017/09/27 8:18 AM
*/

namespace net\orange\soeur\relic;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\level\Level;
use pocketmine\level\format\Chunk;
use pocketmine\block\Block;
use pocketmine\math\Vector3;

use net\orange\soeur\relic\listener\level\ChunkLoadListener;
use net\orange\soeur\relic\command\ReCommand;

class Main extends PluginBase {

private static $obj;

private $relics = [];

public function onLoad(){
$this->initConfig();
}

public function onEnable(){
self::$obj = $this;
$this->initListener();
$this->initCommand();
$this->getLogger()->info("加载遗迹文件中");
$this->loadRelics();
$this->getLogger()->info("遗迹文件加载完成, 共加载 ".count($this->relics)."个遗迹");
$this->getLogger()->info("作者: 橘子");
}

/*
创建配置文件与文件夹
*/
private function initConfig(){
@mkdir($this->getDataFolder());
@mkdir($this->getDataFolder().'/relics');
}

/*
注册事件
*/
private function initListener(){
$this->getServer()->getPluginManager()->registerEvents(new ChunkLoadListener(), $this);
}

/*
注册指令
*/
private function initCommand(){
$this->getServer()->getCommandMap()->register("net.orange.soeur", new ReCommand("re"), null, true);
}

/*
加载遗迹文件数据
*/
private function loadRelics(){
foreach($this->getFilePath($this->getDataFolder().'/relics') as $path){
/*
获取文件json格式的内容并转为数组
将数据存入全局变量
*/
$this->relics[] = json_decode(file_get_contents($path), true);
}
$this->checkRelics();
}

/*
检查遗迹文件是否损坏
如损坏则从数据中剔除
*/
private function checkRelics(){
foreach($this->relics as $relic){
if(!isset($relic['odds']) or !isset($relic['y_max']) or !isset($relic['y_min']) or !isset($relic['pos']) or $relic['y_max'] > Level::Y_MAX or $relic['y_min'] < 0 or !is_array($relic['pos'])){
unset($this->relics[array_search($relic)]);
}
}
}

public function getRelicData() : array {
return $this->relics;
}

public static function getThis() : Main {
return self::$obj;
}

/*
循环文件夹内文件返回文件路径
*/
private function getFilePath(String $dir) : array {
if(!$handle = @opendir($dir)){
return [];
}
$files = [];
while($file = @readdir($handle)){
if('.' === $file || '..' === $file){
continue;
}
$files[] = $dir.'/'.$file;
}
@closedir($handle);
return $files;
}

/*
指定区块生成遗迹
*/
public function createRelic(Chunk $chunk, Level $level){
/*
算出区块大概坐标
*/
$x = ($chunk->getX() << 4);
$z = ($chunk->getZ() << 4);
foreach($this->getRelicData() as $data){
/*
随机生成遗迹
*/
if(mt_rand(0, 100) > $data['odds']){
continue;
}
/*
随机高度
*/
$y = mt_rand($data['y_min'], $data['y_max']);
/*
根据遗迹数据的坐标生成遗迹
*/
foreach($data['pos'] as $pos){
$pos = explode(':', $pos);
$level->setBlock(new Vector3($x + $pos[0], $y + $pos[1], $z + $pos[2]), Block::get($pos[3], $pos[4]));
}
}
}

/*
重新根据relics文件夹里的遗迹文件加载数据
*/
public function reloadRelics(){
$this->relics = [];
$this->loadRelics();
}

}
