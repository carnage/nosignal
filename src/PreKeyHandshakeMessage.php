<?php

declare(strict_types=1);

namespace NoSignal;

class PreKeyHandshakeMessage
{
    public function __construct(
        private string $identityKey,
        private string $preKey,
        private string $preKeySignature,
        private string $oneTimeKey
    ) {}

    public function getIdentityKey(): string
    {
        return $this->identityKey;
    }

    public function getPreKey(): string
    {
        return $this->preKey;
    }

    public function getPreKeySignature(): string
    {
        return $this->preKeySignature;
    }

    public function getOneTimeKey(): string
    {
        return $this->oneTimeKey;
    }

    public function toString(): string
    {
        return implode('.', [
            'H',
            \sodium_bin2base64($this->identityKey, \SODIUM_BASE64_VARIANT_ORIGINAL),
            \sodium_bin2base64($this->preKey, \SODIUM_BASE64_VARIANT_ORIGINAL),
            \sodium_bin2base64($this->preKeySignature, \SODIUM_BASE64_VARIANT_ORIGINAL),
            \sodium_bin2base64($this->oneTimeKey, \SODIUM_BASE64_VARIANT_ORIGINAL),
        ]);
    }

    public static function fromString(string $data): self
    {
        $data = explode('.', $data);
        if ($data[0] !== 'H' || count($data) !== 5) {
            throw new \RuntimeException('Invalid message');
        }

        return new self(
            \sodium_base642bin($data[1], \SODIUM_BASE64_VARIANT_ORIGINAL),
            \sodium_base642bin($data[2], \SODIUM_BASE64_VARIANT_ORIGINAL),
            \sodium_base642bin($data[3], \SODIUM_BASE64_VARIANT_ORIGINAL),
            \sodium_base642bin($data[4], \SODIUM_BASE64_VARIANT_ORIGINAL),
        );
    }
}
