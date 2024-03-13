<?php

namespace Shrd\Laravel\Azure\KeyVault;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Psr\Http\Message\UriFactoryInterface;
use Shrd\Laravel\Azure\Identity\AzureCredentialService;
use Shrd\Laravel\Azure\KeyVault\Clients\Certificate;
use Shrd\Laravel\Azure\KeyVault\Clients\Key;
use Shrd\Laravel\Azure\KeyVault\Clients\KeyVaultClient;
use Shrd\Laravel\Azure\KeyVault\Clients\Secret;
use Shrd\Laravel\Azure\KeyVault\References\KeyVaultCertificateReference;
use Shrd\Laravel\Azure\KeyVault\References\KeyVaultKeyReference;
use Shrd\Laravel\Azure\KeyVault\References\KeyVaultReference;
use Shrd\Laravel\Azure\KeyVault\References\KeyVaultSecretReference;

class KeyVaultService
{
    /**
     * @var array<string, KeyVaultClient>
     */
    protected array $clients = [];

    protected string $defaultCredential;

    protected bool $cacheEnabled;
    protected ?string $cacheStore;
    protected mixed $cacheTtl;
    protected string $cachePrefix;

    public function __construct(protected AzureCredentialService $credential,
                                protected UriFactoryInterface $uriFactory,
                                protected ?ConfigRepository $config = null,
                                protected ?CacheFactory $cacheFactory = null)
    {
        $this->defaultCredential = $config?->get('azure-keyvault.credential')
            ?? $this->credential->getDefaultCredential();

        $this->cacheEnabled = $config?->get('azure-keyvault.cache.enabled') ?? true;
        $this->cacheStore = $config?->get('azure-keyvault.cache.store');
        $this->cacheTtl = $config?->get('azure-keyvault.cache.ttl') ?? '1 hour';
        $this->cachePrefix = $config?->get('azure-keyvault.cache.prefix') ?? 'azure_keyvault:';
    }

    protected function getCacheRepository(): ?CacheRepository
    {
        if(!$this->cacheEnabled) return null;
        return $this->cacheFactory?->store($this->cacheStore);
    }

    public function client(?string $credential = null): KeyVaultClient
    {
        $credential ??= $this->defaultCredential;
        if(array_key_exists($credential, $this->clients)) {
            return $this->clients[$credential];
        }

        $client = new KeyVaultClient(
            credential: $this->credential->credential($credential),
            cache: $this->getCacheRepository(),
            cacheTTL: $this->cacheTtl,
            cachePrefix: $this->cachePrefix,
        );
        $this->clients[$credential] = $client;
        return $client;
    }

    public function resolve($value, ?string $credential = null): mixed
    {
        if(KeyVaultReference::isKeyVaultReferenceString($value)) {
            $value = KeyVaultReference::from($value, $this->uriFactory);
        }

        if($value instanceof KeyVaultReference) return $this->get($value, $credential)->getResolvedValue();
        return $value;
    }

    public function resolveKeys(array &$values, array $keys, ?string $credential = null): array
    {
        foreach ($keys as $key) {
            if(Arr::has($values, $key)) {
                Arr::set($values, $key, $this->resolve(Arr::get($values, $key), $credential));
            }
        }
        return $values;
    }

    public function reference($reference): KeyVaultReference
    {
        return KeyVaultReference::from($reference, $this->uriFactory);
    }

    public function get($reference, ?string $credential = null): Certificate|Secret|Key
    {
        $reference = KeyVaultReference::from($reference, $this->uriFactory);
        if($reference instanceof KeyVaultSecretReference) {
            return new Secret($this->client($credential), $reference);
        } elseif ($reference instanceof KeyVaultKeyReference) {
            return new Key($this->client($credential), $reference);
        } elseif ($reference instanceof KeyVaultCertificateReference) {
            return new Certificate($this->client($credential), $reference);
        } else {
            throw new InvalidArgumentException(self::class."::get(".get_debug_type($reference).") not implemented.");
        }
    }

    public function certificateReference($reference): KeyVaultCertificateReference
    {
        return KeyVaultCertificateReference::from($reference, $this->uriFactory);
    }

    public function certificate($reference, ?string $credential = null): Certificate
    {
        $reference = KeyVaultCertificateReference::from($reference, $this->uriFactory);
        return new Certificate($this->client($credential), $reference);
    }

    public function secretReference($reference): KeyVaultSecretReference
    {
        return KeyVaultSecretReference::from($reference, $this->uriFactory);
    }

    public function secret($reference, ?string $credential = null): Secret
    {
        $reference = KeyVaultReference::from($reference, $this->uriFactory);
        if($reference instanceof KeyVaultSecretReference) {
            return new Secret($this->client($credential), $reference);
        } elseif ($reference instanceof KeyVaultCertificateReference) {
            return $this->certificate($reference, $credential)->getSecret();
        } else {
            throw new InvalidArgumentException("Could not create a Secret-client from ".$reference->toReferenceString());
        }
    }

    public function keyReference($reference): KeyVaultKeyReference
    {
        return KeyVaultKeyReference::from($reference, $this->uriFactory);
    }

    public function key($reference, ?string $credential = null): Key
    {
        $reference = KeyVaultReference::from($reference, $this->uriFactory);
        if($reference instanceof KeyVaultKeyReference) {
            return new Key($this->client($credential), $reference);
        } elseif ($reference instanceof KeyVaultSecretReference) {
            return $this->secret($reference, $credential)->getKey();
        } elseif ($reference instanceof KeyVaultCertificateReference) {
            return $this->certificate($reference, $credential)->getKey();
        } else {
            throw new InvalidArgumentException("Could not create a Key-client from ". $reference->toReferenceString());
        }
    }
}
