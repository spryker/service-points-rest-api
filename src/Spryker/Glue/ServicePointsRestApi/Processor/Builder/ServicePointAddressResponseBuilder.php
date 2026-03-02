<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ServicePointsRestApi\Processor\Builder;

use Generated\Shared\Transfer\RestServicePointAddressesAttributesTransfer;
use Generated\Shared\Transfer\ServicePointStorageCollectionTransfer;
use Generated\Shared\Transfer\ServicePointStorageTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestLinkInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\ServicePointsRestApi\Processor\Mapper\ServicePointAddressMapperInterface;
use Spryker\Glue\ServicePointsRestApi\ServicePointsRestApiConfig;

class ServicePointAddressResponseBuilder implements ServicePointAddressResponseBuilderInterface
{
    /**
     * @var \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface
     */
    protected RestResourceBuilderInterface $restResourceBuilder;

    /**
     * @var \Spryker\Glue\ServicePointsRestApi\Processor\Builder\ErrorResponseBuilderInterface
     */
    protected ErrorResponseBuilderInterface $errorResponseBuilder;

    /**
     * @var \Spryker\Glue\ServicePointsRestApi\Processor\Mapper\ServicePointAddressMapperInterface
     */
    protected ServicePointAddressMapperInterface $servicePointAddressMapper;

    public function __construct(
        RestResourceBuilderInterface $restResourceBuilder,
        ErrorResponseBuilderInterface $errorResponseBuilder,
        ServicePointAddressMapperInterface $servicePointAddressMapper
    ) {
        $this->restResourceBuilder = $restResourceBuilder;
        $this->errorResponseBuilder = $errorResponseBuilder;
        $this->servicePointAddressMapper = $servicePointAddressMapper;
    }

    public function createServicePointAddressRestResponse(
        ServicePointStorageTransfer $servicePointStorageTransfer
    ): RestResponseInterface {
        $servicePointAddressResource = $this->createServicePointAddressRestResource($servicePointStorageTransfer);

        return $this->restResourceBuilder
            ->createRestResponse()
            ->addResource($servicePointAddressResource);
    }

    /**
     * @param \Generated\Shared\Transfer\ServicePointStorageCollectionTransfer $servicePointStorageCollectionTransfer
     *
     * @return array<string, \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface>
     */
    public function createServicePointAddressRestResourcesIndexedByServicePointUuid(
        ServicePointStorageCollectionTransfer $servicePointStorageCollectionTransfer
    ): array {
        $servicePointAddressRestResources = [];
        foreach ($servicePointStorageCollectionTransfer->getServicePointStorages() as $servicePointStorageTransfer) {
            if (!$servicePointStorageTransfer->getAddress()) {
                continue;
            }

            $servicePointAddressRestResources[$servicePointStorageTransfer->getUuidOrFail()]
                = $this->createServicePointAddressRestResource($servicePointStorageTransfer);
        }

        return $servicePointAddressRestResources;
    }

    public function createServicePointAddressNotFoundErrorResponse(string $localeName): RestResponseInterface
    {
        return $this->errorResponseBuilder->createErrorResponse(
            ServicePointsRestApiConfig::GLOSSARY_KEY_VALIDATION_SERVICE_POINT_ADDRESS_ENTITY_NOT_FOUND,
            $localeName,
        );
    }

    public function createServicePointAddressServicePointIsNotSpecifiedErrorResponse(string $localeName): RestResponseInterface
    {
        return $this->errorResponseBuilder->createErrorResponse(
            ServicePointsRestApiConfig::GLOSSARY_KEY_ERROR_SERVICE_POINT_IDENTIFIER_IS_NOT_SPECIFIED,
            $localeName,
        );
    }

    protected function createServicePointAddressRestResource(
        ServicePointStorageTransfer $servicePointStorageTransfer
    ): RestResourceInterface {
        $restServicePointAddressesAttributesTransfer = $this->servicePointAddressMapper
            ->mapServicePointAddressStorageTransferToRestServicePointAddressesAttributesTransfer(
                $servicePointStorageTransfer->getAddressOrFail(),
                new RestServicePointAddressesAttributesTransfer(),
            );

        $servicePointAddressRestResource = $this->restResourceBuilder->createRestResource(
            ServicePointsRestApiConfig::RESOURCE_SERVICE_POINT_ADDRESSES,
            $servicePointStorageTransfer->getAddressOrFail()->getUuidOrFail(),
            $restServicePointAddressesAttributesTransfer,
        );

        return $servicePointAddressRestResource->addLink(
            RestLinkInterface::LINK_SELF,
            $this->getServicePointAddressResourceSelfLink($servicePointStorageTransfer),
        );
    }

    protected function getServicePointAddressResourceSelfLink(
        ServicePointStorageTransfer $servicePointStorageTransfer
    ): string {
        return sprintf(
            '%s/%s/%s/%s',
            ServicePointsRestApiConfig::RESOURCE_SERVICE_POINTS,
            $servicePointStorageTransfer->getUuidOrFail(),
            ServicePointsRestApiConfig::RESOURCE_SERVICE_POINT_ADDRESSES,
            $servicePointStorageTransfer->getAddressOrFail()->getUuidOrFail(),
        );
    }
}
