<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ServicePointsRestApi\Processor\Mapper;

use Generated\Shared\Transfer\RestServicePointAddressesAttributesTransfer;
use Generated\Shared\Transfer\ServicePointAddressStorageTransfer;

class ServicePointAddressMapper implements ServicePointAddressMapperInterface
{
    public function mapServicePointAddressStorageTransferToRestServicePointAddressesAttributesTransfer(
        ServicePointAddressStorageTransfer $servicePointAddressStorageTransfer,
        RestServicePointAddressesAttributesTransfer $restServicePointAddressesAttributesTransfer
    ): RestServicePointAddressesAttributesTransfer {
        return $restServicePointAddressesAttributesTransfer
            ->fromArray($servicePointAddressStorageTransfer->toArray(), true)
            ->setCountryIso2Code($servicePointAddressStorageTransfer->getCountryOrFail()->getIso2CodeOrFail());
    }
}
