<?php

namespace Shrd\Laravel\Azure\KeyVault\Tests\Unit\Data;

use Shrd\Laravel\Azure\KeyVault\References\KeyVaultSecretReference;
use Shrd\Laravel\Azure\Storage\Tests\TestCase;

class KeyVaultSecretReferenceTest extends TestCase
{
    public function test_fromKeyVaultReferenceStringWithUri()
    {
        $value = '@Microsoft.KeyVault(SecretUri=https://precon-dev-development.vault.azure.net/secrets/someSecret/)';
        $ref = KeyVaultSecretReference::from($value);

        $this->assertEquals("https://precon-dev-development.vault.azure.net/secrets/someSecret/", (string)$ref->uri);
    }

    public function test_fromKeyVaultReferenceStringWithParts()
    {
        $value = '@Microsoft.KeyVault(VaultName=precon-dev-development;SecretName=someSecret)';
        $ref = KeyVaultSecretReference::from($value);

        $this->assertEquals("https://precon-dev-development.vault.azure.net/secrets/someSecret/", (string)$ref->uri);
    }

    public function test_fromUriString()
    {
        $value = 'https://precon-dev-development.vault.azure.net/secrets/someSecret/';
        $ref = KeyVaultSecretReference::from($value);

        $this->assertEquals("https://precon-dev-development.vault.azure.net/secrets/someSecret/", (string)$ref->uri);
    }

    public function test_scope()
    {
        $value = 'https://precon-dev-development.vault.azure.net/secrets/someSecret/';
        $ref = KeyVaultSecretReference::from($value);

        $this->assertEquals("https://precon-dev-development.vault.azure.net/.default", (string)$ref->getScope());
    }
}
