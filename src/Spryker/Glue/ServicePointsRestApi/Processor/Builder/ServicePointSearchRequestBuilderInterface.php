<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ServicePointsRestApi\Processor\Builder;

use Generated\Shared\Transfer\ServicePointSearchRequestTransfer;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;

interface ServicePointSearchRequestBuilderInterface
{
    public function createServicePointSearchRequestTransfer(RestRequestInterface $restRequest): ServicePointSearchRequestTransfer;
}
