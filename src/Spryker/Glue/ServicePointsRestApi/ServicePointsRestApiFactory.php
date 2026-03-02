<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ServicePointsRestApi;

use Spryker\Glue\Kernel\AbstractFactory;
use Spryker\Glue\ServicePointsRestApi\Dependency\Client\ServicePointsRestApiToGlossaryStorageClientInterface;
use Spryker\Glue\ServicePointsRestApi\Dependency\Client\ServicePointsRestApiToServicePointSearchClientInterface;
use Spryker\Glue\ServicePointsRestApi\Dependency\Client\ServicePointsRestApiToServicePointStorageClientInterface;
use Spryker\Glue\ServicePointsRestApi\Dependency\Client\ServicePointsRestApiToStoreClientInterface;
use Spryker\Glue\ServicePointsRestApi\Processor\Builder\ErrorResponseBuilder;
use Spryker\Glue\ServicePointsRestApi\Processor\Builder\ErrorResponseBuilderInterface;
use Spryker\Glue\ServicePointsRestApi\Processor\Builder\ServicePointAddressResponseBuilder;
use Spryker\Glue\ServicePointsRestApi\Processor\Builder\ServicePointAddressResponseBuilderInterface;
use Spryker\Glue\ServicePointsRestApi\Processor\Builder\ServicePointResponseBuilder;
use Spryker\Glue\ServicePointsRestApi\Processor\Builder\ServicePointResponseBuilderInterface;
use Spryker\Glue\ServicePointsRestApi\Processor\Builder\ServicePointSearchRequestBuilder;
use Spryker\Glue\ServicePointsRestApi\Processor\Builder\ServicePointSearchRequestBuilderInterface;
use Spryker\Glue\ServicePointsRestApi\Processor\Expander\CheckoutDataResponseAttributesExpander;
use Spryker\Glue\ServicePointsRestApi\Processor\Expander\CheckoutDataResponseAttributesExpanderInterface;
use Spryker\Glue\ServicePointsRestApi\Processor\Expander\ServicePointAddressRelationshipExpander;
use Spryker\Glue\ServicePointsRestApi\Processor\Expander\ServicePointAddressRelationshipExpanderInterface;
use Spryker\Glue\ServicePointsRestApi\Processor\Expander\ServicePointByCheckoutDataResourceRelationshipExpander;
use Spryker\Glue\ServicePointsRestApi\Processor\Expander\ServicePointByCheckoutDataResourceRelationshipExpanderInterface;
use Spryker\Glue\ServicePointsRestApi\Processor\Mapper\ServicePointAddressMapper;
use Spryker\Glue\ServicePointsRestApi\Processor\Mapper\ServicePointAddressMapperInterface;
use Spryker\Glue\ServicePointsRestApi\Processor\Mapper\ServicePointMapper;
use Spryker\Glue\ServicePointsRestApi\Processor\Mapper\ServicePointMapperInterface;
use Spryker\Glue\ServicePointsRestApi\Processor\Mapper\ServiceTypeMapper;
use Spryker\Glue\ServicePointsRestApi\Processor\Mapper\ServiceTypeMapperInterface;
use Spryker\Glue\ServicePointsRestApi\Processor\Reader\ServicePointAddressReader;
use Spryker\Glue\ServicePointsRestApi\Processor\Reader\ServicePointAddressReaderInterface;
use Spryker\Glue\ServicePointsRestApi\Processor\Reader\ServicePointReader;
use Spryker\Glue\ServicePointsRestApi\Processor\Reader\ServicePointReaderInterface;
use Spryker\Glue\ServicePointsRestApi\Processor\Reader\ServicePointStorageReader;
use Spryker\Glue\ServicePointsRestApi\Processor\Reader\ServicePointStorageReaderInterface;
use Spryker\Glue\ServicePointsRestApi\Processor\Reader\ServiceTypeResourceReader;
use Spryker\Glue\ServicePointsRestApi\Processor\Reader\ServiceTypeResourceReaderInterface;
use Spryker\Glue\ServicePointsRestApi\Processor\Validator\ServicePointCheckoutRequestAttributesValidator;
use Spryker\Glue\ServicePointsRestApi\Processor\Validator\ServicePointCheckoutRequestAttributesValidatorInterface;

/**
 * @method \Spryker\Glue\ServicePointsRestApi\ServicePointsRestApiConfig getConfig()
 */
class ServicePointsRestApiFactory extends AbstractFactory
{
    public function createServicePointReader(): ServicePointReaderInterface
    {
        return new ServicePointReader(
            $this->getServicePointSearchClient(),
            $this->createServicePointStorageReader(),
            $this->createServicePointRequestBuilder(),
            $this->createServicePointResponseBuilder(),
        );
    }

    public function createServicePointAddressReader(): ServicePointAddressReaderInterface
    {
        return new ServicePointAddressReader(
            $this->createServicePointStorageReader(),
            $this->createServicePointAddressResponseBuilder(),
        );
    }

    public function createServicePointStorageReader(): ServicePointStorageReaderInterface
    {
        return new ServicePointStorageReader(
            $this->getServicePointStorageClient(),
            $this->getStoreClient(),
        );
    }

    public function createServiceTypeResourceReader(): ServiceTypeResourceReaderInterface
    {
        return new ServiceTypeResourceReader(
            $this->createServiceTypeMapper(),
            $this->getServicePointStorageClient(),
        );
    }

    public function createServicePointByCheckoutDataResourceRelationshipExpander(): ServicePointByCheckoutDataResourceRelationshipExpanderInterface
    {
        return new ServicePointByCheckoutDataResourceRelationshipExpander(
            $this->createServicePointMapper(),
            $this->getResourceBuilder(),
        );
    }

    public function createServicePointAddressRelationshipExpander(): ServicePointAddressRelationshipExpanderInterface
    {
        return new ServicePointAddressRelationshipExpander(
            $this->createServicePointAddressReader(),
        );
    }

    public function createServicePointRequestBuilder(): ServicePointSearchRequestBuilderInterface
    {
        return new ServicePointSearchRequestBuilder(
            $this->getConfig(),
            $this->getStoreClient(),
        );
    }

    public function createServicePointResponseBuilder(): ServicePointResponseBuilderInterface
    {
        return new ServicePointResponseBuilder(
            $this->getResourceBuilder(),
            $this->createErrorResponseBuilder(),
            $this->createServicePointMapper(),
        );
    }

    public function createServicePointAddressResponseBuilder(): ServicePointAddressResponseBuilderInterface
    {
        return new ServicePointAddressResponseBuilder(
            $this->getResourceBuilder(),
            $this->createErrorResponseBuilder(),
            $this->createServicePointAddressMapper(),
        );
    }

    public function createErrorResponseBuilder(): ErrorResponseBuilderInterface
    {
        return new ErrorResponseBuilder(
            $this->getConfig(),
            $this->getResourceBuilder(),
            $this->getGlossaryStorageClient(),
        );
    }

    public function createServicePointMapper(): ServicePointMapperInterface
    {
        return new ServicePointMapper();
    }

    public function createServicePointAddressMapper(): ServicePointAddressMapperInterface
    {
        return new ServicePointAddressMapper();
    }

    public function createServiceTypeMapper(): ServiceTypeMapperInterface
    {
        return new ServiceTypeMapper();
    }

    public function createCheckoutDataResponseAttributesExpander(): CheckoutDataResponseAttributesExpanderInterface
    {
        return new CheckoutDataResponseAttributesExpander();
    }

    public function createServicePointCheckoutRequestAttributesValidator(): ServicePointCheckoutRequestAttributesValidatorInterface
    {
        return new ServicePointCheckoutRequestAttributesValidator(
            $this->getServicePointStorageClient(),
            $this->getStoreClient(),
        );
    }

    public function getServicePointStorageClient(): ServicePointsRestApiToServicePointStorageClientInterface
    {
        return $this->getProvidedDependency(ServicePointsRestApiDependencyProvider::CLIENT_SERVICE_POINT_STORAGE);
    }

    public function getServicePointSearchClient(): ServicePointsRestApiToServicePointSearchClientInterface
    {
        return $this->getProvidedDependency(ServicePointsRestApiDependencyProvider::CLIENT_SERVICE_POINT_SEARCH);
    }

    public function getStoreClient(): ServicePointsRestApiToStoreClientInterface
    {
        return $this->getProvidedDependency(ServicePointsRestApiDependencyProvider::CLIENT_STORE);
    }

    public function getGlossaryStorageClient(): ServicePointsRestApiToGlossaryStorageClientInterface
    {
        return $this->getProvidedDependency(ServicePointsRestApiDependencyProvider::CLIENT_GLOSSARY_STORAGE);
    }
}
