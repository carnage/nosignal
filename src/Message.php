<?php

declare(strict_types=1);

namespace NoSignal;

class Message
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

    public function toString(): string
    {
        return implode('.', [
            'M',
            \sodium_bin2base64($this->cipherText, \SODIUM_BASE64_VARIANT_ORIGINAL),
            \sodium_bin2base64($this->nonce, \SODIUM_BASE64_VARIANT_ORIGINAL),
            \sodium_bin2base64((string) $this->previousSendingChainLength, \SODIUM_BASE64_VARIANT_ORIGINAL),
            \sodium_bin2base64((string) $this->sequenceNumber, \SODIUM_BASE64_VARIANT_ORIGINAL),
            \sodium_bin2base64($this->ratchetPublicKey, \SODIUM_BASE64_VARIANT_ORIGINAL),
        ]);
    }

    public static function fromString(string $data): self
    {
        $data = explode('.', $data);
        if ($data[0] !== 'M' || count($data) !== 6) {
            throw new \RuntimeException('Invalid message');
        }

        return new self(
            \sodium_base642bin($data[1], \SODIUM_BASE64_VARIANT_ORIGINAL),
            \sodium_base642bin($data[2], \SODIUM_BASE64_VARIANT_ORIGINAL),
            (int) \sodium_base642bin($data[3], \SODIUM_BASE64_VARIANT_ORIGINAL),
            (int) \sodium_base642bin($data[4], \SODIUM_BASE64_VARIANT_ORIGINAL),
            \sodium_base642bin($data[5], \SODIUM_BASE64_VARIANT_ORIGINAL),
        );
    }
}
