<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\ServicePointsRestApi\Api\Storefront\Provider;

use Generated\Api\Storefront\ServiceTypesStorefrontResource;
use Generated\Shared\Transfer\ServiceTypeStorageConditionsTransfer;
use Generated\Shared\Transfer\ServiceTypeStorageCriteriaTransfer;
use Generated\Shared\Transfer\ServiceTypeStorageTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractStorefrontProvider;
use Spryker\Client\ServicePointStorage\ServicePointStorageClientInterface;

class ServiceTypesStorefrontProvider extends AbstractStorefrontProvider
{
    protected const string URI_VAR_UUID = 'uuid';

    public function __construct(
        protected ServicePointStorageClientInterface $servicePointStorageClient,
    ) {
    }

    /**
     * @return \Generated\Api\Storefront\ServiceTypesStorefrontResource|null
     */
    protected function provideItem(): object|null
    {
        $uuid = (string)$this->getUriVariable(static::URI_VAR_UUID);

        $conditionsTransfer = (new ServiceTypeStorageConditionsTransfer())->addUuid($uuid);
        $criteriaTransfer = (new ServiceTypeStorageCriteriaTransfer())
            ->setServiceTypeStorageConditions($conditionsTransfer);

        $collectionTransfer = $this->servicePointStorageClient->getServiceTypeStorageCollection($criteriaTransfer);

        if ($collectionTransfer->getServiceTypeStorages()->count() === 0) {
            return null;
        }

        return $this->mapTransferToResource($collectionTransfer->getServiceTypeStorages()->offsetGet(0));
    }

    protected function mapTransferToResource(ServiceTypeStorageTransfer $storageTransfer): ServiceTypesStorefrontResource
    {
        $resource = new ServiceTypesStorefrontResource();
        $resource->uuid = $storageTransfer->getUuid();
        $resource->name = $storageTransfer->getName();
        $resource->key = $storageTransfer->getKey();

        return $resource;
    }
}
