<?php

namespace net\orange\soeur\relic\listener\level;

use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\event\level\ChunkLoadEvent;
use pocketmine\scheduler\PluginTask;

use net\orange\soeur\relic\Main;

class ChunkLoadListener implements Listener{

private $main;

public function __construct(){
$this->main = Main::getThis();
}

/*
区块加载事件
*/
public function chunkLoad(ChunkLoadEvent $event){
/*
不是新的区块不生成遗迹
*/
if($event->isNewChunk()){
/*
延迟一秒生成遗迹
因为浪费我半天的时间发现了在直接事件里生成会boom
*/
$this->main->getServer()->getScheduler()->scheduleDelayedTask(new class($this->main, $event->getChunk(), $event->getLevel()) extends PluginTask {

private $chunk;
private $level;

public function __construct(Main $main, Chunk $chunk, Level $level){
parent::__construct($main);
$this->chunk = $chunk;
$this->level = $level;
}

public function onRun($currentTick){
$this->owner->createRelic($this->chunk, $this->level);
}
}, 20);
}
}

}