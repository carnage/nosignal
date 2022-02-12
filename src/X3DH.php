<?php

declare(strict_types=1);

namespace NoSignal;

class X3DH
{
    private string $identityKey;
    private string $identityKeyX25519;

    private string $preKey;
    private string $preKeySignature;

    private array $oneTimeKeys = [];
    private array $ratchets = [];

    public function __construct()
    {
        $this->identityKey = \sodium_crypto_sign_keypair();
        $this->identityKeyX25519 = \sodium_crypto_sign_ed25519_sk_to_curve25519(
            \sodium_crypto_sign_secretkey($this->identityKey)
        ) . \sodium_crypto_sign_ed25519_pk_to_curve25519(
            \sodium_crypto_sign_publickey($this->identityKey)
        );

        $this->preKey = \sodium_crypto_kx_keypair();
        $this->preKeySignature = \sodium_crypto_sign_detached(
            \sodium_crypto_generichash(\sodium_crypto_kx_publickey($this->preKey)),
            \sodium_crypto_sign_secretkey($this->identityKey)
        );
    }

    // this deviates slightly from signal protocol as we only intend the handshake to be used once
    public function createHandshakeMessage(string $contact): PreKeyHandshakeMessage
    {
        $oneTimeKey = \sodium_crypto_kx_keypair();
        $this->oneTimeKeys[$contact] = $oneTimeKey;

        return new PreKeyHandshakeMessage(
            \sodium_crypto_sign_publickey($this->identityKey),
            \sodium_crypto_kx_publickey($this->preKey),
            $this->preKeySignature,
            \sodium_crypto_kx_publickey($oneTimeKey)
        );
    }

    public function createInitialMessage(string $contact, PreKeyHandshakeMessage $message): InitialMessage
    {
        if (!\sodium_crypto_sign_verify_detached(
            $message->getPreKeySignature(),
            \sodium_crypto_generichash($message->getPreKey()),
            $message->getIdentityKey()
        )) {
            throw new \RuntimeException('invalid prekey sig');
        }

        $ephemeralKey = \sodium_crypto_kx_keypair();

        $dh1 = $this->DH($this->identityKeyX25519, $message->getPreKey());
        $dh2 = $this->DH($ephemeralKey, \sodium_crypto_sign_ed25519_pk_to_curve25519($message->getIdentityKey()));
        $dh3 = $this->DH($ephemeralKey, $message->getPreKey());
        $dh4 = $this->DH($ephemeralKey, $message->getOneTimeKey());

        $secretKey = $this->KDF_SK($dh1, $dh2, $dh3, $dh4);

        $this->ratchets[$contact] = RatchetState::fromPublicKey($contact, $secretKey, $message->getOneTimeKey());

        return new InitialMessage(
            \sodium_crypto_sign_publickey($this->identityKey),
            \sodium_crypto_kx_publickey($ephemeralKey),
            $this->ratchets[$contact]->sendNew('hello world')
        );
    }

    public function receiveInitialMessage(string $contact, InitialMessage $message): string
    {
        $dh1 = $this->DH($this->preKey, \sodium_crypto_sign_ed25519_pk_to_curve25519($message->getIdentityKey()));
        $dh2 = $this->DH($this->identityKeyX25519, $message->getEphemeralKey());
        $dh3 = $this->DH($this->preKey, $message->getEphemeralKey());
        $dh4 = $this->DH($this->oneTimeKeys[$contact], $message->getEphemeralKey());

        $secretKey = $this->KDF_SK($dh1, $dh2, $dh3, $dh4);

        $this->ratchets[$contact] = RatchetState::fromKeyPair(
            $contact,
            $secretKey,
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
        $key = \sodium_crypto_generichash($dh1 . $dh2 . $dh3 . $dh4);
        $priKey = \sodium_crypto_generichash('no-signal-handshake' . 0x00, $key);
        $secretKey = \sodium_crypto_generichash('no-signal-handshake' . 0x01, $priKey);
        return $secretKey;
    }
}