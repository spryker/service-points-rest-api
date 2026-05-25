<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\ServicePointsRestApi\Api\Storefront\Provider;

use Generated\Api\Storefront\ServicePointsStorefrontResource;
use Generated\Shared\Transfer\ServicePointSearchCollectionTransfer;
use Generated\Shared\Transfer\ServicePointSearchRequestTransfer;
use Generated\Shared\Transfer\ServicePointSearchTransfer;
use Generated\Shared\Transfer\ServicePointStorageConditionsTransfer;
use Generated\Shared\Transfer\ServicePointStorageCriteriaTransfer;
use Generated\Shared\Transfer\ServicePointStorageTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractStorefrontProvider;
use Spryker\Client\ServicePointSearch\ServicePointSearchClientInterface;
use Spryker\Client\ServicePointStorage\ServicePointStorageClientInterface;
use Spryker\Client\Store\StoreClientInterface;
use Spryker\Glue\ServicePointsRestApi\Api\Storefront\Exception\ServicePointsExceptionFactory;
use Spryker\Glue\ServicePointsRestApi\ServicePointsRestApiConfig;
use Spryker\Service\Serializer\SerializerServiceInterface;

class ServicePointsStorefrontProvider extends AbstractStorefrontProvider
{
    protected const string URI_VAR_UUID = 'uuid';

    protected const string QUERY_PARAM_SEARCH = 'q';

    protected const string QUERY_PARAM_FILTER = 'filter';

    protected const string QUERY_PARAM_SORT = 'sort';

    protected const string FILTER_RESOURCE_PREFIX = 'service-points.';

    protected const string FILTER_FIELD_SERVICE_TYPE_KEY = 'serviceTypeKey';

    /**
     * @uses \Spryker\Client\ServicePointSearch\Plugin\Elasticsearch\Query\ServiceTypesServicePointSearchQueryExpanderPlugin::PARAMETER_SERVICE_TYPES
     */
    protected const string PARAMETER_SERVICE_TYPES = 'serviceTypes';

    /**
     * @uses \Spryker\Client\ServicePointSearch\Builder\ServicePointSearchSortConfigBuilder::DEFAULT_SORT_PARAM_KEY
     */
    protected const string PARAMETER_SORT = 'sort';

    /**
     * @uses \Spryker\Client\ServicePointSearch\Plugin\Elasticsearch\Query\PaginatedServicePointSearchQueryExpanderPlugin::PARAMETER_OFFSET
     */
    protected const string PARAMETER_OFFSET = 'offset';

    /**
     * @uses \Spryker\Client\ServicePointSearch\Plugin\Elasticsearch\Query\PaginatedServicePointSearchQueryExpanderPlugin::PARAMETER_LIMIT
     */
    protected const string PARAMETER_LIMIT = 'limit';

    /**
     * @uses \Spryker\Client\ServicePointSearch\Plugin\Elasticsearch\Query\ServicePointAddressRelationExcludeServicePointQueryExpanderPlugin::PARAMETER_EXCLUDE_ADDRESS_RELATION
     */
    protected const string PARAMETER_EXCLUDE_ADDRESS_RELATION = 'excludeAddressRelation';

    /**
     * @uses \Spryker\Client\ServicePointSearch\Plugin\Elasticsearch\ResultFormatter\ServicePointSearchResultFormatterPlugin::NAME
     */
    protected const string KEY_SERVICE_POINT_SEARCH_COLLECTION = 'ServicePointSearchCollection';

    public function __construct(
        protected ServicePointStorageClientInterface $servicePointStorageClient,
        protected ServicePointSearchClientInterface $servicePointSearchClient,
        protected StoreClientInterface $storeClient,
        protected ServicePointsExceptionFactory $exceptionFactory,
        protected SerializerServiceInterface $serializer,
        protected ServicePointsRestApiConfig $servicePointsRestApiConfig,
    ) {
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function provideItem(): ?object
    {
        $uuid = (string)$this->findUriVariable(static::URI_VAR_UUID);

        if ($uuid === '') {
            throw $this->exceptionFactory->createServicePointNotFoundException($this->getLocale()->getLocaleNameOrFail());
        }

        $servicePointStorageTransfer = $this->findServicePointStorageByUuid($uuid);

        if ($servicePointStorageTransfer === null) {
            throw $this->exceptionFactory->createServicePointNotFoundException($this->getLocale()->getLocaleNameOrFail());
        }

        return $this->buildResourceFromStorage($servicePointStorageTransfer);
    }

    /**
     * @return array<\Generated\Api\Storefront\ServicePointsStorefrontResource>
     */
    protected function provideCollection(): array
    {
        $servicePointSearchRequestTransfer = $this->buildSearchRequest();
        $searchResults = $this->servicePointSearchClient->searchServicePoints($servicePointSearchRequestTransfer);

        $collectionTransfer = $searchResults[static::KEY_SERVICE_POINT_SEARCH_COLLECTION] ?? new ServicePointSearchCollectionTransfer();

        $addressUuidsByServicePointUuid = $this->loadAddressUuidsByServicePointUuid($collectionTransfer);

        $resources = [];
        foreach ($collectionTransfer->getServicePoints() as $servicePointSearchTransfer) {
            $resources[] = $this->buildResourceFromSearch($servicePointSearchTransfer, $addressUuidsByServicePointUuid);
        }

        if ($resources !== []) {
            $nbResults = $collectionTransfer->getNbResults() ?? 0;
            $offset = $this->getPaginationOffset();
            $limit = $this->getPaginationLimit();
            $resources[0]->pagination = $this->calculatePagination($offset, $limit, $nbResults);
        }

        return $resources;
    }

    protected function findServicePointStorageByUuid(string $uuid): ?ServicePointStorageTransfer
    {
        $servicePointStorages = $this->getServicePointStoragesByUuids([$uuid]);

        if ($servicePointStorages === []) {
            return null;
        }

        return $servicePointStorages[0];
    }

    /**
     * @param list<string> $uuids
     *
     * @return list<\Generated\Shared\Transfer\ServicePointStorageTransfer>
     */
    protected function getServicePointStoragesByUuids(array $uuids): array
    {
        if ($uuids === []) {
            return [];
        }

        $conditionsTransfer = (new ServicePointStorageConditionsTransfer())
            ->setUuids($uuids)
            ->setStoreName($this->storeClient->getCurrentStore()->getNameOrFail());

        $criteriaTransfer = (new ServicePointStorageCriteriaTransfer())
            ->setServicePointStorageConditions($conditionsTransfer);

        $collection = $this->servicePointStorageClient->getServicePointStorageCollection($criteriaTransfer);
        $storages = [];

        foreach ($collection->getServicePointStorages() as $storageTransfer) {
            $storages[] = $storageTransfer;
        }

        return $storages;
    }

    protected function buildSearchRequest(): ServicePointSearchRequestTransfer
    {
        $requestParameters = [
            static::PARAMETER_EXCLUDE_ADDRESS_RELATION => true,
        ];

        $requestParameters = $this->applyFilters($requestParameters);
        $requestParameters = $this->applySort($requestParameters);
        $requestParameters = $this->applyPagination($requestParameters);

        return (new ServicePointSearchRequestTransfer())
            ->setRequestParameters($requestParameters)
            ->setSearchString((string)$this->getRequest()->query->get(static::QUERY_PARAM_SEARCH, '') ?: null);
    }

    /**
     * @param array<string, mixed> $requestParameters
     *
     * @return array<string, mixed>
     */
    protected function applyFilters(array $requestParameters): array
    {
        $filters = $this->getRequest()->query->all()[static::QUERY_PARAM_FILTER] ?? [];

        if (!is_array($filters)) {
            return $requestParameters;
        }

        foreach ($filters as $key => $value) {
            if (!is_string($key) || !str_starts_with($key, static::FILTER_RESOURCE_PREFIX)) {
                continue;
            }

            $field = substr($key, strlen(static::FILTER_RESOURCE_PREFIX));

            if ($field === static::FILTER_FIELD_SERVICE_TYPE_KEY) {
                $requestParameters[static::PARAMETER_SERVICE_TYPES] = [$value];

                continue;
            }

            $requestParameters[$field] = $value;
        }

        return $requestParameters;
    }

    /**
     * @param array<string, mixed> $requestParameters
     *
     * @return array<string, mixed>
     */
    protected function applySort(array $requestParameters): array
    {
        $sort = (string)$this->getRequest()->query->get(static::QUERY_PARAM_SORT, '');

        if ($sort === '') {
            return $requestParameters;
        }

        $direction = 'asc';
        if (str_starts_with($sort, '-')) {
            $direction = 'desc';
            $sort = substr($sort, 1);
        }

        if (!in_array($sort, $this->servicePointsRestApiConfig->getAllowedSortFields(), true)) {
            return $requestParameters;
        }

        $requestParameters[static::PARAMETER_SORT] = sprintf('%s_%s', $sort, $direction);

        return $requestParameters;
    }

    /**
     * @param array<string, mixed> $requestParameters
     *
     * @return array<string, mixed>
     */
    protected function applyPagination(array $requestParameters): array
    {
        $pageParam = $this->getRequest()->query->all()[static::QUERY_PARAM_PAGE] ?? null;

        if (!is_array($pageParam)) {
            return $requestParameters;
        }

        if (isset($pageParam[static::QUERY_PARAMETER_OFFSET])) {
            $requestParameters[static::PARAMETER_OFFSET] = (int)$pageParam[static::QUERY_PARAMETER_OFFSET];
        }

        if (isset($pageParam[static::QUERY_PARAMETER_LIMIT])) {
            $requestParameters[static::PARAMETER_LIMIT] = (int)$pageParam[static::QUERY_PARAMETER_LIMIT];
        }

        return $requestParameters;
    }

    /**
     * @return array<string, string>
     */
    protected function loadAddressUuidsByServicePointUuid(ServicePointSearchCollectionTransfer $collectionTransfer): array
    {
        $servicePointUuids = [];
        foreach ($collectionTransfer->getServicePoints() as $servicePointSearchTransfer) {
            $uuid = $servicePointSearchTransfer->getUuid();
            if ($uuid !== null) {
                $servicePointUuids[] = $uuid;
            }
        }

        if ($servicePointUuids === []) {
            return [];
        }

        $addressUuidsByServicePointUuid = [];
        foreach ($this->getServicePointStoragesByUuids($servicePointUuids) as $storageTransfer) {
            $addressUuid = $storageTransfer->getAddress()?->getUuid();

            if ($addressUuid !== null) {
                $addressUuidsByServicePointUuid[$storageTransfer->getUuidOrFail()] = $addressUuid;
            }
        }

        return $addressUuidsByServicePointUuid;
    }

    protected function buildResourceFromStorage(ServicePointStorageTransfer $servicePointStorageTransfer): ServicePointsStorefrontResource
    {
        return $this->serializer->denormalize(
            [
                'uuid' => $servicePointStorageTransfer->getUuid(),
                'name' => $servicePointStorageTransfer->getName(),
                'key' => $servicePointStorageTransfer->getKey(),
                'servicePointAddressUuid' => $servicePointStorageTransfer->getAddress()?->getUuid(),
            ],
            ServicePointsStorefrontResource::class,
        );
    }

    /**
     * @param array<string, string> $addressUuidsByServicePointUuid
     */
    protected function buildResourceFromSearch(
        ServicePointSearchTransfer $servicePointSearchTransfer,
        array $addressUuidsByServicePointUuid
    ): ServicePointsStorefrontResource {
        return $this->serializer->denormalize(
            [
                'uuid' => $servicePointSearchTransfer->getUuid(),
                'name' => $servicePointSearchTransfer->getName(),
                'key' => $servicePointSearchTransfer->getKey(),
                'servicePointAddressUuid' => $addressUuidsByServicePointUuid[$servicePointSearchTransfer->getUuid()] ?? null,
            ],
            ServicePointsStorefrontResource::class,
        );
    }
}
