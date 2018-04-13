<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\entity\utils;

use pocketmine\entity\FireworksRocket;
use pocketmine\item\FireworkRocket;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\utils\Random;

class FireworksUtils{

    /**
     * @param int $flight
     * @param CompoundTag[] $explosionTags
     * @return CompoundTag
     */
    public static function createNBT($flight = 1, array $explosionTags = []) : CompoundTag{
        $tag = new CompoundTag();

        $explosions = new ListTag("Explosions", $explosionTags, NBT::TAG_Compound);

        $fireworkTag = new CompoundTag("Fireworks");
        $fireworkTag->Explosions = $explosions;
        $fireworkTag->Flight = new ByteTag("Flight", 1);
        $tag->Fireworks = $fireworkTag;

        return $tag;
    }

    public static function createExplosion(int $fireworkColor = 0, int $fireworkFade = 0, bool $fireworkFlicker = false, bool $fireworkTrait = false, int $fireworkType = -1) : CompoundTag{
        $expTag = new CompoundTag();
        $expTag->FireworkColor = new ByteArrayTag("FireworkColor", strval($fireworkColor));
        $expTag->ForeworkFade = new ByteArrayTag("FireworkFade", strval($fireworkFade));
        $expTag->FireworkFlicker = new ByteTag("FireworkFlicker", $fireworkFlicker ? 1 : 0);
        $expTag->FireworkTrait = new ByteTag("FireworkTrait", $fireworkTrait ? 1 : 0);
        $expTag->FireworkType = new ByteTag("FireworkType", $fireworkType);
        return $expTag;
    }

    public static function createNBTforEntity(Vector3 $pos, ?Vector3 $motion = null, FireworkRocket $rocket, float $spread = 5.0, ?Random $random = null, ?float $yaw = null, ?float $pitch = null) : CompoundTag{
        $random = $random ?? new Random();
        $pos = $pos->add(0.5, 0, 0.5);
        $yaw = $yaw ?? $random->nextBoundedInt(360);
        $pitch = $pitch ?? -1 * (float) (90 + ($random->nextFloat() * $spread - $spread / 2));
        $nbt = FireworksRocket::createBaseNBT($pos, $motion, $yaw, $pitch);

        /** @var CompoundTag $tags */
        $tags = $rocket->getNamedTagEntry("Fireworks");
        if (!is_null($tags)){
            $nbt->Fireworks = $tags;
        }

        return $nbt;
    }

}