<?php

declare(strict_types=1);

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
}
