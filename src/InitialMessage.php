<?php

declare(strict_types=1);

namespace NoSignal;

class InitialMessage
{
    public function __construct(
        private string $identityKey,
        private string $ephemeralKey,
        private Message $initialMessage
    ) {}

    public function getIdentityKey(): string
    {
        return $this->identityKey;
    }

    public function getEphemeralKey(): string
    {
        return $this->ephemeralKey;
    }

    public function getInitialMessage(): Message
    {
        return $this->initialMessage;
    }

    public function toString(): string
    {
        return implode('.', [
            'I',
            \sodium_bin2base64($this->identityKey, \SODIUM_BASE64_VARIANT_ORIGINAL),
            \sodium_bin2base64($this->ephemeralKey, \SODIUM_BASE64_VARIANT_ORIGINAL),
            \sodium_bin2base64($this->initialMessage->toString(), \SODIUM_BASE64_VARIANT_ORIGINAL),
        ]);
    }

    public static function fromString(string $data): self
    {
        $data = explode('.', $data);
        if ($data[0] !== 'I' || count($data) !== 4) {
            throw new \RuntimeException('Invalid message');
        }

        return new self(
            \sodium_base642bin($data[1], \SODIUM_BASE64_VARIANT_ORIGINAL),
            \sodium_base642bin($data[2], \SODIUM_BASE64_VARIANT_ORIGINAL),
            Message::fromString(\sodium_base642bin($data[3], \SODIUM_BASE64_VARIANT_ORIGINAL)),
        );
    }
}
