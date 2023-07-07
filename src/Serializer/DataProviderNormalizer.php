<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\Serializer;

use Borodulin\PresenterBundle\DataProvider\DataProviderInterface;
use Borodulin\PresenterBundle\DataProvider\QueryBuilder\QueryBuilderInterface;
use Borodulin\PresenterBundle\Presenter\Presenter;
use Borodulin\PresenterBundle\PresenterContext\DataProviderContext;
use Borodulin\PresenterBundle\PresenterContext\DataProviderContextFactory;
use Borodulin\PresenterBundle\PresenterContext\ObjectContext;
use Borodulin\PresenterBundle\PresenterContext\ObjectContextFactory;
use Borodulin\PresenterBundle\PresenterHandler\PresenterHandlerRegistry;
use Borodulin\PresenterBundle\Request\Filter\CustomFilterInterface;
use Borodulin\PresenterBundle\Request\Filter\FilterBuilder;
use Borodulin\PresenterBundle\Request\Pagination\PaginationBuilder;
use Borodulin\PresenterBundle\Request\Pagination\PaginationResponseFactoryInterface;
use Borodulin\PresenterBundle\Request\Sort\CustomSortInterface;
use Borodulin\PresenterBundle\Request\Sort\SortBuilder;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

class DataProviderNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    private NormalizerInterface $normalizer;

    public function __construct(
        private readonly PresenterHandlerRegistry $presenterHandlerRegistry,
        private readonly DataProviderContextFactory $dataProviderContextFactory,
        private readonly ObjectContextFactory $objectContextFactory,
    ) {
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        if ($data instanceof DataProviderInterface) {
            return true;
        }
        if ($data instanceof Presenter) {
            return $data->getObject() instanceof DataProviderInterface;
        }

        return false;
    }

    public function normalize($object, string $format = null, array $context = []): mixed
    {
        $dataProvider = $object instanceof Presenter ? $object->getObject() : $object;
        if (!$dataProvider instanceof DataProviderInterface) {
            throw new \InvalidArgumentException();
        }

        $dataProviderContext = $object instanceof Presenter ? $object->dataProviderContext
            : $this->dataProviderContextFactory->createFromArrayContext($context);

        $queryBuilder = $this->prepareQueryBuilder($dataProvider, $dataProviderContext);

        $objectContext = $object instanceof Presenter ? $object->objectContext : $this->objectContextFactory->createFromArrayContext($context);

        [$presenterHandler, $method] = $this->presenterHandlerRegistry
            ->getPresenterHandlerForClass($dataProvider::class, $objectContext?->group ?? ObjectContext::DEFAULT_GROUP);

        if ($dataProviderContext->isPaginationEnabled()) {
            if ($presenterHandler instanceof PaginationResponseFactoryInterface) {
                $responseFactory = $presenterHandler;
            } elseif ($dataProvider instanceof PaginationResponseFactoryInterface) {
                $responseFactory = $dataProvider;
            } else {
                $responseFactory = null;
            }

            $response = (new PaginationBuilder())
                ->paginate(
                    $dataProviderContext->paginationRequest,
                    $queryBuilder,
                    fn ($entity) => $this->normalizer->normalize($entity, $format, $objectContext->toArray()),
                    $responseFactory
                );
        } else {
            $response = array_map(
                fn ($entity) => $this->normalizer->normalize($entity, $format, $objectContext->toArray()),
                $queryBuilder->fetchAll()
            );
        }
        if (\is_callable([$presenterHandler, $method])) {
            $response = \call_user_func([$presenterHandler, $method], $dataProvider, $response, $context, $queryBuilder);
        }

        return $this->normalizer->normalize($response, $format, $context);
    }

    public function setSerializer(SerializerInterface $serializer): void
    {
        $this->normalizer = $serializer;
    }

    private function prepareQueryBuilder(
        DataProviderInterface $dataProvider,
        DataProviderContext $dataProviderContext,
    ): QueryBuilderInterface {
        $queryBuilder = clone $dataProvider->getQueryBuilder();
        if ($dataProviderContext->isSortEnabled() && null !== $dataProviderContext->sortRequest) {
            (new SortBuilder())->sort(
                $dataProviderContext->sortRequest,
                $queryBuilder,
                $dataProvider instanceof CustomSortInterface ? $dataProvider : null
            );
        }
        if ($dataProviderContext->isFilterEnabled() && null !== $dataProviderContext->filterRequest) {
            (new FilterBuilder())->filter(
                $dataProviderContext->filterRequest,
                $queryBuilder,
                $dataProvider instanceof CustomFilterInterface ? $dataProvider : null
            );
        }

        return $queryBuilder;
    }
}
