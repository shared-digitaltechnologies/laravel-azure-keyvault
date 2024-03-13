<?php

namespace Shrd\Laravel\Azure\KeyVault\References;

use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

readonly class KeyVaultKeyReference extends KeyVaultReference
{
    public static function fromUri(string|UriInterface $uri, ?UriFactoryInterface $uriFactory = null): self
    {
        if(is_string($uri)) {
            $uriFactory ??= Psr17FactoryDiscovery::findUriFactory();
            $uri = $uriFactory->createUri($uri);
        }
        return new self($uri);
    }

    public static function fromName(string $vaultName,
                                    string $keyName,
                                    ?string $keyVersion = null,
                                    ?UriFactoryInterface $uriFactory = null): self
    {
        return self::fromUri("https://$vaultName.vault.azure.net/keys/$keyName/$keyVersion", $uriFactory);
    }

    public static function fromProperties(array $properties, ?UriFactoryInterface $uriFactory = null): self
    {
        if(array_key_exists('KeyUri', $properties)) {
            return self::fromUri($properties['KeyUri'], $uriFactory);
        }

        return self::fromName(
            vaultName: $properties['VaultName'],
            keyName: $properties['KeyName'],
            keyVersion: $properties['KeyVersion'] ?? null,
            uriFactory: $uriFactory
        );
    }

    public static function from(string|UriInterface|array|KeyVaultReference $value,
                                ?UriFactoryInterface $uriFactory = null): self
    {
        if($value instanceof self) return $value;
        if($value instanceof KeyVaultReference) {
            return self::fromName(
                vaultName: $value->getVaultName(),
                keyName: $value->getName(),
                keyVersion: $value->getVersion(),
                uriFactory: $uriFactory
            );
        }
        if($value instanceof UriInterface) return new self($value);
        if(is_array($value)) return self::fromProperties($value, $uriFactory);
        return self::fromString($value, $uriFactory);
    }

    public static function fromString(string $value, ?UriFactoryInterface $uriFactory = null): self
    {
        $values = self::parseKeyVaultReferenceString($value);
        if($values === null) return self::fromUri($value, $uriFactory);
        return self::fromProperties($values, $uriFactory);
    }

    public function getSignUri(): UriInterface
    {
        return $this->uri->withPath($this->uri->getPath().'/sign');
    }

    public function getVerifyUri(): UriInterface
    {
        return $this->uri->withPath($this->uri->getPath().'/verify');
    }

    public function toReferenceString(): string
    {
        return "@Microsoft.KeyVault(KeyUri=$this->uri)";
    }
}
