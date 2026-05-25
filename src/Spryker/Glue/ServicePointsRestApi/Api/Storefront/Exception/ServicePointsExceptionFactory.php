<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\ServicePointsRestApi\Api\Storefront\Exception;

use Generated\Shared\Transfer\RestErrorMessageTransfer;
use Spryker\ApiPlatform\Exception\GlueApiException;
use Spryker\Client\GlossaryStorage\GlossaryStorageClientInterface;
use Spryker\Glue\ServicePointsRestApi\ServicePointsRestApiConfig;
use Symfony\Component\HttpFoundation\Response;

class ServicePointsExceptionFactory
{
    public function __construct(
        protected ServicePointsRestApiConfig $servicePointsRestApiConfig,
        protected GlossaryStorageClientInterface $glossaryStorageClient,
    ) {
    }

    public function createServicePointNotFoundException(string $localeName): GlueApiException
    {
        return $this->createExceptionForGlossaryKey(
            ServicePointsRestApiConfig::GLOSSARY_KEY_VALIDATION_SERVICE_POINT_ENTITY_NOT_FOUND,
            $localeName,
        );
    }

    public function createServicePointAddressNotFoundException(string $localeName): GlueApiException
    {
        return $this->createExceptionForGlossaryKey(
            ServicePointsRestApiConfig::GLOSSARY_KEY_VALIDATION_SERVICE_POINT_ADDRESS_ENTITY_NOT_FOUND,
            $localeName,
        );
    }

    public function createEndpointNotFoundException(string $localeName): GlueApiException
    {
        return $this->createExceptionForGlossaryKey(
            ServicePointsRestApiConfig::GLOSSARY_KEY_ERROR_ENDPOINT_NOT_FOUND,
            $localeName,
        );
    }

    protected function createExceptionForGlossaryKey(string $glossaryKey, string $localeName): GlueApiException
    {
        $errorData = $this->servicePointsRestApiConfig->getGlossaryKeyToErrorDataMapping()[$glossaryKey] ?? [];
        $detail = $this->glossaryStorageClient->translate($glossaryKey, $localeName);

        return new GlueApiException(
            (int)($errorData[RestErrorMessageTransfer::STATUS] ?? Response::HTTP_BAD_REQUEST),
            (string)($errorData[RestErrorMessageTransfer::CODE] ?? ServicePointsRestApiConfig::RESPONSE_CODE_UNKNOWN_ERROR),
            $detail !== '' ? $detail : $glossaryKey,
        );
    }
}
