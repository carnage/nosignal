<?php

declare(strict_types=1);

class RatchetState
{
    private const MAX_SKIP = 1000;

    private string $contact;

    private string $DHs;
    private string $DHr;
    private string $rootKey;
    private string $chainKeySending;
    private string $chainKeyReceiving;

    private int $sendingSequenceNumber = 0;
    private int $receivingSequenceNumber = 0;
    private int $previousSendingChainMessages = 0;

    private array $skippedKeys;

    private function __construct()
    {
    }

    public static function fromPublicKey(string $contact, string $secretKey, string $contactPublicKey): self
    {
        $instance = new self();
        $instance->contact = $contact;
        $instance->DHs = \sodium_crypto_kx_keypair();
        $instance->DHr = $contactPublicKey;
        list($instance->rootKey, $instance->chainKeySending) =
            $instance->KDF_RK($secretKey, $instance->DH($instance->DHs, $instance->DHr));

        return $instance;
    }

    public static function fromKeyPair(string $contact, string $secretKey, string $contactPublicKey, string $keyPair): self
    {
        $instance = new self();
        $instance->contact = $contact;
        $instance->DHs = $keyPair;
        $instance->DHr = $contactPublicKey;
        list($instance->rootKey, $instance->chainKeySending) =
            $instance->KDF_RK($secretKey, $instance->DH($instance->DHs, $instance->DHr));

        return $instance;
    }

    public function getContact(): string
    {
        return $this->contact;
    }

    public function sendNew(string $plainText): Message
    {
        list($this->chainKeySending, $messageKey) = $this->KDF_CK($this->chainKeySending);
        $header = [
            'sequenceNumber' => $this->sendingSequenceNumber,
            'previousSendingChainMessages' => $this->previousSendingChainMessages,
            'pubKey' => sodium_crypto_kx_publickey($this->DHs),
        ];

        $nonce = \random_bytes(\SODIUM_CRYPTO_AEAD_AES256GCM_NPUBBYTES);

        $message = new Message(
            \sodium_crypto_aead_aes256gcm_encrypt(
                $plainText,
                implode('', $header),
                $nonce,
                $messageKey
            ),
            $nonce,
            $header['sequenceNumber'],
            $header['previousSendingChainMessages'],
            $header['pubKey'],
        );

        \sodium_memzero($messageKey);

        $this->sendingSequenceNumber++;
        return $message;
    }

    public function receiveNew(Message $message): string
    {
        $header = [
            'sequenceNumber' => $message->getSequenceNumber(),
            'previousSendingChainMessages' => $message->getPreviousSendingChainLength(),
            'pubKey' => $message->getRatchetPublicKey(),
        ];

        if ($this->hasSkippedKey($message->getRatchetPublicKey(), $message->getSequenceNumber())) {
            $messageKey = $this->skippedKeys[$message->getRatchetPublicKey()][$message->getSequenceNumber()];
            unset($this->skippedKeys[$message->getRatchetPublicKey()][$message->getSequenceNumber()]);

            return \sodium_crypto_aead_aes256gcm_decrypt(
                $message->getCipherText(),
                implode('', $header),
                $message->getNonce(),
                $messageKey
            );
        }
        if ($message->getRatchetPublicKey() !== $this->DHr) {
            //handle out of order message
            $this->skipMessageKeys($message->getPreviousSendingChainLength());
            $this->dhRatchet($message->getRatchetPublicKey());
        }

        $this->skipMessageKeys($message->getSequenceNumber());
        list($this->chainKeyReceiving, $messageKey) = $this->KDF_CK($this->chainKeyReceiving);
        $this->receivingSequenceNumber++;

        return \sodium_crypto_aead_aes256gcm_decrypt(
            $message->getCipherText(),
            implode('', $header),
            $message->getNonce(),
            $messageKey
        );
    }

    private function DH(string $x, string $y): string
    {
        return \sodium_crypto_scalarmult(\sodium_crypto_kx_secretkey($x), $y);
    }

    private function KDF_RK(string $rootKey, $dhOut): array
    {
        $priKey = \sodium_crypto_generichash($rootKey, $dhOut);
        $t1 = \sodium_crypto_generichash( 'no-signal' . 0x01, $priKey);
        $t2 = \sodium_crypto_generichash( 'no-signal' . 0x02, $priKey);
        $t3 = \sodium_crypto_generichash( 'no-signal' . 0x03, $priKey);

        return [$t1, $t2, $t3];
    }

    private function KDF_CK(string $key): array
    {
        $priKey = \sodium_crypto_generichash('no-signal-ratchet' . 0x00, $key);
        $t1 = \sodium_crypto_generichash('no-signal-ratchet' . 0x01, $priKey);
        $t2 = \sodium_crypto_generichash('no-signal-ratchet' . 0x02, $priKey);
        $t3 = \sodium_crypto_generichash( 'no-signal-ratchet' . 0x03, $priKey);

        return [$t1, $t2, $t3];
    }

    private function skipMessageKeys(int $n): void
    {
        if ($this->receivingSequenceNumber + self::MAX_SKIP < $n) {
            throw new \RuntimeException('cannot skip that many');
        }

        if (isset($this->chainKeyReceiving)) {
            while ($this->receivingSequenceNumber < $n) {
                list($this->chainKeyReceiving, $messageKey) = $this->KDF_CK($this->chainKeyReceiving);
                $this->skippedKeys[$this->DHr][$this->receivingSequenceNumber] = $messageKey;
                $this->receivingSequenceNumber++;
            }
        }
    }

    private function hasSkippedKey(string $ratchetPublicKey, int $sequenceNumber): bool
    {
        return isset($this->skippedKeys[$ratchetPublicKey][$sequenceNumber]);
    }

    private function dhRatchet(string $ratchetPublicKey): void
    {
        $this->previousSendingChainMessages = $this->sendingSequenceNumber;
        $this->receivingSequenceNumber = 0;
        $this->sendingSequenceNumber = 0;
        $this->DHr = $ratchetPublicKey;
        list($this->rootKey, $this->chainKeyReceiving) = $this->KDF_RK($this->rootKey, $this->DH($this->DHs, $this->DHr));
        $this->DHs = \sodium_crypto_kx_keypair();
        list($this->rootKey, $this->chainKeySending) = $this->KDF_RK($this->rootKey, $this->DH($this->DHs, $this->DHr));
    }
}
