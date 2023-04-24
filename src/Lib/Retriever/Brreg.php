<?php

namespace BisonLab\ContextBundle\Lib\Retriever;

class Brreg implements RetrieverInterface
{
    public function getSupports(): array
    {
        return [
            "brreg" => ['enhet']
        ];
    }

    public function getExternalDataFromContext($context): mixed
    {
        if ($context->getObjectName() == "enhet") {
            $client = new \GuzzleHttp\Client();
            $res = $client->request('GET', 'https://data.brreg.no/enhetsregisteret/api/enheter/' . $context->getExternalId());
            if ($res->getStatusCode() == 200)
                return json_decode($res->getBody(), true);
        }
        return null;
    }
}
