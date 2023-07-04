<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\Serializer;

use Borodulin\PresenterBundle\DataProvider\DataProviderInterface;
use Borodulin\PresenterBundle\DataProvider\PaginatedDataProviderInterface;
use Borodulin\PresenterBundle\DataProvider\QueryBuilder\QueryBuilderInterface;
use Borodulin\PresenterBundle\PresenterContext\DataProviderContextFactory;
use Borodulin\PresenterBundle\PresenterHandler\PresenterHandlerRegistry;
use Borodulin\PresenterBundle\Request\Filter\CustomFilterInterface;
use Borodulin\PresenterBundle\Request\Filter\FilterBuilder;
use Borodulin\PresenterBundle\Request\Filter\FilterRequest;
use Borodulin\PresenterBundle\Request\Pagination\PaginationBuilder;
use Borodulin\PresenterBundle\Request\Pagination\PaginationRequestInterface;
use Borodulin\PresenterBundle\Request\Pagination\PaginationResponseFactoryInterface;
use Borodulin\PresenterBundle\Request\Sort\CustomSortInterface;
use Borodulin\PresenterBundle\Request\Sort\SortBuilder;
use Borodulin\PresenterBundle\Request\Sort\SortRequestInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

class DataProviderNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    private NormalizerInterface $normalizer;

    public function __construct(
        private readonly PresenterHandlerRegistry $presenterHandlerRegistry,
        private readonly DataProviderContextFactory $dataProviderContextFactory,
    ) {
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        if (\is_object($data)) {
            $class = $data::class;
            $reflection = new \ReflectionClass($class);

            return $reflection->implementsInterface(DataProviderInterface::class);
        }

        return false;
    }

    public function normalize($object, string $format = null, array $context = []): mixed
    {
        $presenterContext = $this->dataProviderContextFactory->createFromArrayContext($context);

        $queryBuilder = $this->prepareQueryBuilder($object, $presenterContext->sortRequest, $presenterContext->filterRequest);

        if ($object instanceof PaginatedDataProviderInterface) {
            $paginationRequest = $presenterContext->paginationRequest;
        } else {
            $paginationRequest = null;
        }

        [$presenterHandler, $method] = $this->presenterHandlerRegistry->getPresenterHandlerForClass($object::class, $presenterContext->group);

        if ($paginationRequest instanceof PaginationRequestInterface) {
            if ($presenterHandler instanceof PaginationResponseFactoryInterface) {
                $responseFactory = $presenterHandler;
            } elseif ($object instanceof PaginationResponseFactoryInterface) {
                $responseFactory = $object;
            } else {
                $responseFactory = null;
            }

            $response = (new PaginationBuilder())
                ->paginate(
                    $paginationRequest,
                    $queryBuilder,
                    fn ($entity) => $this->normalizer->normalize($entity, null, $context),
                    $responseFactory
                );
        } else {
            $response = array_map(
                fn ($entity) => $this->normalizer->normalize($entity, null, $context),
                $queryBuilder->fetchAll()
            );
        }
        if (\is_callable([$presenterHandler, $method])) {
            $response = \call_user_func([$presenterHandler, $method], $object, $response, $context, $queryBuilder);
        }

        return $this->normalizer->normalize($response, null, $context);
    }

    public function setSerializer(SerializerInterface $serializer): void
    {
        $this->normalizer = $serializer;
    }

    private function prepareQueryBuilder(
        DataProviderInterface $dataProvider,
        ?SortRequestInterface $sortRequest,
        ?FilterRequest $filterRequest
    ): QueryBuilderInterface {
        $queryBuilder = clone $dataProvider->getQueryBuilder();
        if (null !== $sortRequest) {
            (new SortBuilder())->sort(
                $sortRequest,
                $queryBuilder,
                $dataProvider instanceof CustomSortInterface ? $dataProvider : null
            );
        }
        if (null !== $filterRequest) {
            (new FilterBuilder())->filter(
                $filterRequest,
                $queryBuilder,
                $dataProvider instanceof CustomFilterInterface ? $dataProvider : null
            );
        }

        return $queryBuilder;
    }
}
