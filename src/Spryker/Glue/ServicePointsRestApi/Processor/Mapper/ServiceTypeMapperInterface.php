<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ServicePointsRestApi\Processor\Mapper;

use Generated\Shared\Transfer\ServiceTypeResourceCollectionTransfer;
use Generated\Shared\Transfer\ServiceTypeStorageCollectionTransfer;

interface ServiceTypeMapperInterface
{
    public function mapServiceTypeStorageCollectionToServiceTypeResourceCollection(
        ServiceTypeStorageCollectionTransfer $serviceTypeStorageCollectionTransfer,
        ServiceTypeResourceCollectionTransfer $serviceTypeResourceCollectionTransfer
    ): ServiceTypeResourceCollectionTransfer;
}
