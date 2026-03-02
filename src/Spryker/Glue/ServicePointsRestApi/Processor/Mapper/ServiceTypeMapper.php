<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ServicePointsRestApi\Processor\Mapper;

use Generated\Shared\Transfer\GlueResourceTransfer;
use Generated\Shared\Transfer\RestServiceTypesAttributesTransfer;
use Generated\Shared\Transfer\ServiceTypeResourceCollectionTransfer;
use Generated\Shared\Transfer\ServiceTypeStorageCollectionTransfer;
use Generated\Shared\Transfer\ServiceTypeStorageTransfer;
use Spryker\Glue\ServicePointsRestApi\ServicePointsRestApiConfig;

class ServiceTypeMapper implements ServiceTypeMapperInterface
{
    public function mapServiceTypeStorageCollectionToServiceTypeResourceCollection(
        ServiceTypeStorageCollectionTransfer $serviceTypeStorageCollectionTransfer,
        ServiceTypeResourceCollectionTransfer $serviceTypeResourceCollectionTransfer
    ): ServiceTypeResourceCollectionTransfer {
        foreach ($serviceTypeStorageCollectionTransfer->getServiceTypeStorages() as $serviceTypeStorageTransfer) {
            $serviceTypeResourceTransfer = $this->mapServiceTypeStorageTransferToGlueResourceTransfer(
                $serviceTypeStorageTransfer,
                new GlueResourceTransfer(),
            );

            $serviceTypeResourceCollectionTransfer->addServiceTypeResource($serviceTypeResourceTransfer);
        }

        return $serviceTypeResourceCollectionTransfer;
    }

    protected function mapServiceTypeStorageTransferToGlueResourceTransfer(
        ServiceTypeStorageTransfer $serviceTypeStorageTransfer,
        GlueResourceTransfer $serviceTypesGlueResourceTransfer
    ): GlueResourceTransfer {
        $restServiceTypesAttributesTransfer = $this->mapServiceTypeStorageTransferToRestServiceTypesAttributesTransfer(
            $serviceTypeStorageTransfer,
            new RestServiceTypesAttributesTransfer(),
        );

        return $serviceTypesGlueResourceTransfer
            ->setType(ServicePointsRestApiConfig::RESOURCE_SERVICE_TYPES)
            ->setId($serviceTypeStorageTransfer->getUuidOrFail())
            ->setAttributes($restServiceTypesAttributesTransfer);
    }

    protected function mapServiceTypeStorageTransferToRestServiceTypesAttributesTransfer(
        ServiceTypeStorageTransfer $serviceTypeStorageTransfer,
        RestServiceTypesAttributesTransfer $restServiceTypesAttributesTransfer
    ): RestServiceTypesAttributesTransfer {
        return $restServiceTypesAttributesTransfer->fromArray($serviceTypeStorageTransfer->toArray(), true);
    }
}
