<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ServicePointsRestApi\Processor\Reader;

use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;

interface ServicePointAddressReaderInterface
{
    /**
     * @param list<string> $servicePointUuids
     *
     * @return array<string, \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface>
     */
    public function getServicePointAddressRestResourcesIndexedByServicePointUuid(array $servicePointUuids): array;

    public function getServicePointAddress(RestRequestInterface $restRequest): RestResponseInterface;
}
