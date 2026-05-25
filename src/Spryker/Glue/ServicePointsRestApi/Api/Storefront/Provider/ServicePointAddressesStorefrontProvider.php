<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\ServicePointsRestApi\Api\Storefront\Provider;

use ApiPlatform\Metadata\Operation;
use Generated\Api\Storefront\ServicePointAddressesStorefrontResource;
use Generated\Shared\Transfer\ServicePointAddressStorageTransfer;
use Generated\Shared\Transfer\ServicePointStorageConditionsTransfer;
use Generated\Shared\Transfer\ServicePointStorageCriteriaTransfer;
use Spryker\ApiPlatform\Provider\BatchLoadableProviderInterface;
use Spryker\ApiPlatform\State\Provider\AbstractStorefrontProvider;
use Spryker\Client\ServicePointStorage\ServicePointStorageClientInterface;
use Spryker\Client\Store\StoreClientInterface;
use Spryker\Glue\ServicePointsRestApi\Api\Storefront\Exception\ServicePointsExceptionFactory;
use Spryker\Service\Serializer\SerializerServiceInterface;

/**
 * @implements \Spryker\ApiPlatform\Provider\BatchLoadableProviderInterface<\Generated\Api\Storefront\ServicePointAddressesStorefrontResource>
 */
class ServicePointAddressesStorefrontProvider extends AbstractStorefrontProvider implements BatchLoadableProviderInterface
{
    protected const string URI_VAR_SERVICE_POINT_UUID = 'servicePointUuid';

    protected const string URI_VAR_UUID = 'uuid';

    public function __construct(
        protected ServicePointStorageClientInterface $servicePointStorageClient,
        protected StoreClientInterface $storeClient,
        protected ServicePointsExceptionFactory $exceptionFactory,
        protected SerializerServiceInterface $serializer,
    ) {
    }

    /**
     * Intercepts the batch invocation made by {@see \Spryker\ApiPlatform\Relationship\ApiPlatformRelationshipResolver}
     * for `?include=service-point-addresses` against a `/service-points` collection. A single
     * Redis read covers all parents instead of one read per service point.
     *
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (isset($uriVariables[static::BATCH_DATA_KEY]) && is_array($uriVariables[static::BATCH_DATA_KEY])) {
            return $this->provideBatch($uriVariables[static::BATCH_DATA_KEY]);
        }

        return parent::provide($operation, $uriVariables, $context);
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     *
     * @return array<object>
     */
    protected function provideCollection(): array
    {
        throw $this->exceptionFactory->createEndpointNotFoundException($this->getLocale()->getLocaleNameOrFail());
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function provideItem(): ?object
    {
        $servicePointUuid = (string)$this->findUriVariable(static::URI_VAR_SERVICE_POINT_UUID);
        $uuid = (string)$this->findUriVariable(static::URI_VAR_UUID);

        if ($servicePointUuid === '' || $uuid === '') {
            throw $this->exceptionFactory->createServicePointAddressNotFoundException($this->getLocale()->getLocaleNameOrFail());
        }

        $servicePointStorageTransfer = $this->loadServicePointStorages([$servicePointUuid])[0] ?? null;

        if ($servicePointStorageTransfer === null) {
            throw $this->exceptionFactory->createServicePointAddressNotFoundException($this->getLocale()->getLocaleNameOrFail());
        }

        $servicePointAddressStorageTransfer = $servicePointStorageTransfer->getAddress();

        if ($servicePointAddressStorageTransfer === null || $servicePointAddressStorageTransfer->getUuid() !== $uuid) {
            throw $this->exceptionFactory->createServicePointAddressNotFoundException($this->getLocale()->getLocaleNameOrFail());
        }

        return $this->buildResource($servicePointStorageTransfer->getUuidOrFail(), $servicePointAddressStorageTransfer);
    }

    /**
     * @param array<int, array<string, mixed>> $batchUriVariables
     *
     * @return array<object>
     */
    protected function provideBatch(array $batchUriVariables): array
    {
        $servicePointUuids = [];
        $expectedAddressUuidByServicePointUuid = [];

        foreach ($batchUriVariables as $itemUriVariables) {
            $servicePointUuid = isset($itemUriVariables[static::URI_VAR_SERVICE_POINT_UUID])
                ? (string)$itemUriVariables[static::URI_VAR_SERVICE_POINT_UUID]
                : '';
            $addressUuid = isset($itemUriVariables[static::URI_VAR_UUID])
                ? (string)$itemUriVariables[static::URI_VAR_UUID]
                : '';

            if ($servicePointUuid === '' || $addressUuid === '') {
                continue;
            }

            $servicePointUuids[$servicePointUuid] = $servicePointUuid;
            $expectedAddressUuidByServicePointUuid[$servicePointUuid] = $addressUuid;
        }

        if ($servicePointUuids === []) {
            return [];
        }

        $resources = [];

        foreach ($this->loadServicePointStorages(array_values($servicePointUuids)) as $servicePointStorageTransfer) {
            $servicePointUuid = $servicePointStorageTransfer->getUuid();
            $servicePointAddressStorageTransfer = $servicePointStorageTransfer->getAddress();

            if (
                $servicePointUuid === null
                || $servicePointAddressStorageTransfer === null
                || $servicePointAddressStorageTransfer->getUuid() !== ($expectedAddressUuidByServicePointUuid[$servicePointUuid] ?? null)
            ) {
                continue;
            }

            $resources[] = $this->buildResource($servicePointUuid, $servicePointAddressStorageTransfer);
        }

        return $resources;
    }

    /**
     * @param list<string> $servicePointUuids
     *
     * @return list<\Generated\Shared\Transfer\ServicePointStorageTransfer>
     */
    protected function loadServicePointStorages(array $servicePointUuids): array
    {
        if ($servicePointUuids === []) {
            return [];
        }

        $conditionsTransfer = (new ServicePointStorageConditionsTransfer())
            ->setUuids($servicePointUuids)
            ->setStoreName($this->storeClient->getCurrentStore()->getNameOrFail());

        $criteriaTransfer = (new ServicePointStorageCriteriaTransfer())
            ->setServicePointStorageConditions($conditionsTransfer);

        $storages = [];
        foreach ($this->servicePointStorageClient->getServicePointStorageCollection($criteriaTransfer)->getServicePointStorages() as $servicePointStorageTransfer) {
            $storages[] = $servicePointStorageTransfer;
        }

        return $storages;
    }

    protected function buildResource(
        string $servicePointUuid,
        ServicePointAddressStorageTransfer $servicePointAddressStorageTransfer
    ): ServicePointAddressesStorefrontResource {
        $resourceData = [
            'servicePointUuid' => $servicePointUuid,
            'uuid' => $servicePointAddressStorageTransfer->getUuid(),
            'countryIso2Code' => $servicePointAddressStorageTransfer->getCountry()?->getIso2Code(),
            'address1' => $servicePointAddressStorageTransfer->getAddress1(),
            'address2' => $servicePointAddressStorageTransfer->getAddress2(),
            'address3' => $servicePointAddressStorageTransfer->getAddress3(),
            'zipCode' => $servicePointAddressStorageTransfer->getZipCode(),
            'city' => $servicePointAddressStorageTransfer->getCity(),
        ];

        return $this->serializer->denormalize($resourceData, ServicePointAddressesStorefrontResource::class);
    }
}
