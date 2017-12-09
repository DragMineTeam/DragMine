<?php

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol;

use pocketmine\network\mcpe\NetworkSession;

class PingPacket extends DataPacket{
	const NETWORK_ID = ProtocolInfo::PING_PACKET;

	public $ping;

	protected function decodePayload(){
		$this->ping = $this->getVarInt();
	}

	protected function encodePayload(){
		$this->putVarInt($this->ping);
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handlePing($this);
	}
}