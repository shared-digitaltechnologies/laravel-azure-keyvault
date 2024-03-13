<?php

namespace Shrd\Laravel\Azure\KeyVault\References;

use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

readonly class KeyVaultSecretReference extends KeyVaultReference
{
    public static function fromProperties(array $properties, ?UriFactoryInterface $uriFactory = null): self
    {
        if(array_key_exists('SecretUri', $properties)) {
            return self::fromUri($properties['SecretUri'], $uriFactory);
        }

        return self::fromName(
            vaultName: $properties['VaultName'],
            secretName: $properties['SecretName'],
            secretVersion: $properties['SecretVersion'] ?? null,
            uriFactory: $uriFactory
        );
    }

    public static function fromUri(string|UriInterface $uri, ?UriFactoryInterface $uriFactory = null): self
    {
        if(is_string($uri)) {
            $uriFactory ??= Psr17FactoryDiscovery::findUriFactory();
            $uri = $uriFactory->createUri($uri);
        }
        return new self($uri);
    }

    public static function fromName(string $vaultName,
                                    string $secretName,
                                    string|null $secretVersion = null,
                                    UriFactoryInterface $uriFactory = null): self
    {
        return self::fromUri("https://$vaultName.vault.azure.net/secrets/$secretName/$secretVersion", $uriFactory);
    }

    public static function from(string|UriInterface|array|KeyVaultReference $value,
                                ?UriFactoryInterface $uriFactory = null): self
    {
        if($value instanceof self) return $value;
        if($value instanceof KeyVaultReference) {
            return self::fromName($value->getVaultName(), $value->getName(), $value->getVersion(), $uriFactory);
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

    public function toReferenceString(): string
    {
        return "@Microsoft.KeyVault(SecretUri=$this->uri)";
    }
}
