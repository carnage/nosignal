<?php

declare(strict_types=1);

class X3DH
{
    private string $identityKey;
    private string $preKey;
    private string $preKeySignature;

    private array $oneTimeKeys = [];
    private array $ratchets = [];

    public function __construct()
    {
        $this->identityKey = \sodium_crypto_kx_keypair();
        $this->preKey = \sodium_crypto_kx_keypair();
        $this->preKeySignature = \sodium_crypto_sign_detached(
            \sodium_crypto_generichash(\sodium_crypto_kx_publickey($this->preKey)),
            $this->identityKey
        );
    }

    // this deviates slightly from signal protocol as we only intend the handshake to be used once
    public function createHandshakeMessage(string $contact): PreKeyHandshakeMessage
    {
        $oneTimeKey = \sodium_crypto_kx_keypair();
        $this->oneTimeKeys[$contact] = $oneTimeKey;

        return new PreKeyHandshakeMessage(
            \sodium_crypto_kx_publickey($this->identityKey),
            $this->preKey,
            $this->preKeySignature,
            \sodium_crypto_kx_publickey($oneTimeKey)
        );
    }

    public function createInitialMessage(string $contact, PreKeyHandshakeMessage $message): InitialMessage
    {
        if (!\sodium_crypto_sign_verify_detached(
            $message->getPreKeySignature(),
            $message->getPreKey(),
            $message->getIdentityKey()
        )) {
            throw new \RuntimeException('invalid prekey sig');
        }

        $ephemeralKey = \sodium_crypto_kx_keypair();

        $dh1 = $this->DH($this->identityKey, $message->getPreKey());
        $dh2 = $this->DH($ephemeralKey, $message->getIdentityKey());
        $dh3 = $this->DH($ephemeralKey, $message->getPreKey());
        $dh4 = $this->DH($ephemeralKey, $message->getOneTimeKey());

        $secretKey = $this->KDF_SK($dh1, $dh2, $dh3, $dh4);

        $this->ratchets[$contact] = RatchetState::fromPublicKey($contact, $secretKey, $message->getOneTimeKey());

        return new InitialMessage(
            \sodium_crypto_kx_publickey($this->identityKey),
            \sodium_crypto_kx_publickey($ephemeralKey),
            $this->ratchets[$contact]->sendNew('hello world')
        );
    }

    public function receiveInitialMessage(string $contact, InitialMessage $message): string
    {
        $dh1 = $this->DH($this->preKey, $message->getIdentityKey());
        $dh2 = $this->DH($this->identityKey, $message->getEphemeralKey());
        $dh3 = $this->DH($this->preKey, $message->getEphemeralKey());
        $dh4 = $this->DH($this->oneTimeKeys[$contact], $message->getEphemeralKey());

        $secretKey = $this->KDF_SK($dh1, $dh2, $dh3, $dh4);

        $this->ratchets[$contact] = RatchetState::fromKeyPair(
            $contact,
            $secretKey,
            $message->getInitialMessage()->getRatchetPublicKey(),
            $this->oneTimeKeys[$contact]
        );

        return $this->ratchets[$contact]->receiveNew($message->getInitialMessage());
    }

    private function DH(string $x, string $y): string
    {
        return \sodium_crypto_scalarmult(\sodium_crypto_kx_secretkey($x), $y);
    }

    private function KDF_SK(string $dh1, string $dh2, string $dh3, string $dh4): string
    {
        $priKey = \sodium_crypto_generichash('no-signal-handshake' . 0x00, $dh1 . $dh2 . $dh3 . $dh4);
        $secretKey = \sodium_crypto_generichash('no-signal-handshake' . 0x01, $priKey);
        return $secretKey;
    }
}