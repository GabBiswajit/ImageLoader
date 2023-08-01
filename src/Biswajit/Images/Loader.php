<?php

declare(strict_types=1);

namespace Biswajit\Images;

use pocketmine\entity\Attribute;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\NetworkStackLatencyPacket;
use pocketmine\network\mcpe\protocol\types\entity\Attribute as NetworkAttribute;
use pocketmine\network\mcpe\protocol\UpdateAttributesPacket;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\ClosureTask;

final class Loader extends PluginBase implements Listener{

    protected function onEnable() : void{
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onDataPacketSend(DataPacketSendEvent $event) : void{
        foreach($event->getPackets() as $packet){
            if($packet instanceof ModalFormRequestPacket){
                foreach($event->getTargets() as $target){
                    $player = $target->getPlayer();
                    $times = 5;
                    $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(static function() use($player, &$times) : void {
                        if($times-- === 0 || !$player->isOnline()){
                            throw new CancelTaskException();
                        }
                        $attr = $player->getAttributeMap()->get(Attribute::EXPERIENCE_LEVEL);
                        $entries = [new NetworkAttribute($attr->getId(), $attr->getMinValue(), $attr->getMaxValue(), $attr->getValue(), $attr->getDefaultValue(), [])];
                        $player->getNetworkSession()->sendDataPacket(UpdateAttributesPacket::create($player->getId(), $entries, 0));
                    }), 10);
                }
            }
        }
    }
}
