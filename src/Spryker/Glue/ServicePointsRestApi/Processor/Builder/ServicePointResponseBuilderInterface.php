<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ServicePointsRestApi\Processor\Builder;

use Generated\Shared\Transfer\ServicePointSearchCollectionTransfer;
use Generated\Shared\Transfer\ServicePointStorageTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;

interface ServicePointResponseBuilderInterface
{
    public function createServicePointRestResponse(
        ServicePointStorageTransfer $servicePointStorageTransfer
    ): RestResponseInterface;

    public function createServicePointCollectionRestResponse(
        ServicePointSearchCollectionTransfer $servicePointSearchCollectionTransfer
    ): RestResponseInterface;

    public function createServicePointNotFoundErrorResponse(string $localeName): RestResponseInterface;
}
