<?php

namespace Shrd\Laravel\Azure\KeyVault\Tests\Feature;

use Shrd\Laravel\Azure\KeyVault\Clients\Certificate;
use Shrd\Laravel\Azure\KeyVault\KeyVaultService;
use Shrd\Laravel\Azure\KeyVault\References\KeyVaultCertificateReference;
use Shrd\Laravel\Azure\KeyVault\Tests\TestCase;

class KeyVaultServiceTest extends TestCase
{
    public function test_init_certificate()
    {
        /** @var KeyVaultService $service */
        $service = $this->app->make(KeyVaultService::class);

        $certificate = $service->certificate(KeyVaultCertificateReference::fromName(
            vaultName: 'testVault',
            certificateName: 'testCertificate'
        ));

        $this->assertInstanceOf(Certificate::class, $certificate);
    }
}
