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
}
