<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ServicePointsRestApi\Processor\Mapper;

use Generated\Shared\Transfer\RestServicePointsAttributesTransfer;
use Generated\Shared\Transfer\ServicePointSearchTransfer;
use Generated\Shared\Transfer\ServicePointStorageTransfer;
use Generated\Shared\Transfer\ServicePointTransfer;

class ServicePointMapper implements ServicePointMapperInterface
{
    public function mapServicePointSearchTransferToRestServicePointsAttributesTransfer(
        ServicePointSearchTransfer $servicePointSearchTransfer,
        RestServicePointsAttributesTransfer $restServicePointsAttributesTransfer
    ): RestServicePointsAttributesTransfer {
        return $restServicePointsAttributesTransfer->fromArray($servicePointSearchTransfer->toArray(), true);
    }

    public function mapServicePointStorageTransferToRestServicePointsAttributesTransfer(
        ServicePointStorageTransfer $servicePointStorageTransfer,
        RestServicePointsAttributesTransfer $restServicePointsAttributesTransfer
    ): RestServicePointsAttributesTransfer {
        return $restServicePointsAttributesTransfer->fromArray($servicePointStorageTransfer->toArray(), true);
    }

    public function mapServicePointTransferToRestServicePointsAttributesTransfer(
        ServicePointTransfer $servicePointTransfer,
        RestServicePointsAttributesTransfer $restServicePointsAttributesTransfer
    ): RestServicePointsAttributesTransfer {
        return $restServicePointsAttributesTransfer->fromArray($servicePointTransfer->toArray(), true);
    }
}
