<?php

declare(strict_types=1);

class Message implements \JsonSerializable
{
    public function __construct(
        private string $cipherText,
        private string $nonce,
        private int $sequenceNumber,
        private int $previousSendingChainLength,
        private string $ratchetPublicKey,
    ) {}

    public function getCipherText(): string
    {
        return $this->cipherText;
    }

    public function getNonce(): string
    {
        return $this->nonce;
    }

    public function getPreviousSendingChainLength(): int
    {
        return $this->previousSendingChainLength;
    }

    public function getSequenceNumber(): int
    {
        return $this->sequenceNumber;
    }

    public function getRatchetPublicKey(): string
    {
        return $this->ratchetPublicKey;
    }

    public function jsonSerialize(): array
    {
        return [
            $this->cipherText,
            $this->nonce,
            $this->sequenceNumber,
            $this->previousSendingChainLength,
            $this->ratchetPublicKey,
        ];
    }

    public function toString(): string
    {
        return \sodium_bin2base64(json_encode($this), \SODIUM_BASE64_VARIANT_ORIGINAL);
    }
}
