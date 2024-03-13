<?php

namespace Shrd\Laravel\Azure\KeyVault\Tokens;

use Illuminate\Http\Client\RequestException;
use Shrd\EncodingCombinators\Strings\ConstantTime\Base64Url;
use Shrd\Laravel\Azure\Identity\Exceptions\AzureCredentialException;
use Shrd\Laravel\Azure\KeyVault\Clients\Certificate;

readonly class CertificateTokenBuilder
{
    public function __construct(public Certificate $certificate, public string $alg)
    {
    }

    public function getTokenHeader(): array
    {
        return [
            "alg" => $this->alg,
            "typ" => 'JWT',
            "x5t" => $this->certificate->getData()->x5t
        ];
    }

    /**
     * @throws RequestException
     * @throws AzureCredentialException
     */
    public function createTokenWithPayload(array $payload): string
    {
        $header = Base64Url::encodeNoPadding(json_encode($this->getTokenHeader()));
        $payload = Base64Url::encodeNoPadding(json_encode($payload));
        $digest = "$header.$payload";
        $signature = $this->certificate->sign($this->alg, $digest);
        return "$digest.$signature";
    }
}
