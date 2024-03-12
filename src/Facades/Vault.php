<?php

namespace Shrd\Laravel\Azure\KeyVault\Facades;

use Illuminate\Support\Facades\Facade;
use Shrd\Laravel\Azure\KeyVault\Clients\Key;
use Shrd\Laravel\Azure\KeyVault\Clients\Certificate;
use Shrd\Laravel\Azure\KeyVault\Clients\Secret;
use Shrd\Laravel\Azure\KeyVault\Clients\KeyVaultClient;
use Shrd\Laravel\Azure\KeyVault\KeyVaultService;

/**
 * Access the Azure KeyVault.
 *
 * @method static KeyVaultClient client(?string $credential = null)
 * @method static mixed resolve($value, ?string $credential = null)
 * @method static array resolveKeys(array &$values, array $keys, ?string $credential = null)
 * @method static Key|Certificate|Secret get($reference, ?string $credential = null)
 * @method static Key key($reference, ?string $credential = null)
 * @method static Certificate certificate($reference, ?string $credential = null)
 * @method static Secret secret($reference, ?string $credential = null)
 */
class Vault extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return KeyVaultService::class;
    }
}
