<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ServicePointsRestApi\Processor\Builder;

use Generated\Shared\Transfer\ServicePointStorageCollectionTransfer;
use Generated\Shared\Transfer\ServicePointStorageTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;

interface ServicePointAddressResponseBuilderInterface
{
    public function createServicePointAddressRestResponse(
        ServicePointStorageTransfer $servicePointStorageTransfer
    ): RestResponseInterface;

    /**
     * @param \Generated\Shared\Transfer\ServicePointStorageCollectionTransfer $servicePointStorageCollectionTransfer
     *
     * @return array<string, \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface>
     */
    public function createServicePointAddressRestResourcesIndexedByServicePointUuid(
        ServicePointStorageCollectionTransfer $servicePointStorageCollectionTransfer
    ): array;

    public function createServicePointAddressNotFoundErrorResponse(string $localeName): RestResponseInterface;

    public function createServicePointAddressServicePointIsNotSpecifiedErrorResponse(string $localeName): RestResponseInterface;
}
