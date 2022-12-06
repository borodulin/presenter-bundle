<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\Serializer;

use Borodulin\PresenterBundle\DataProvider\CustomFilterInterface;
use Borodulin\PresenterBundle\DataProvider\CustomSortInterface;
use Borodulin\PresenterBundle\DataProvider\DataProviderInterface;
use Borodulin\PresenterBundle\DataProvider\PaginatedDataProviderInterface;
use Borodulin\PresenterBundle\DataProvider\QueryBuilder\QueryBuilderInterface;
use Borodulin\PresenterBundle\Request\Expand\ExpandRequestInterface;
use Borodulin\PresenterBundle\Request\Filter\FilterBuilder;
use Borodulin\PresenterBundle\Request\Filter\FilterRequest;
use Borodulin\PresenterBundle\Request\Filter\FilterRequestInterface;
use Borodulin\PresenterBundle\Request\Pagination\PaginationBuilder;
use Borodulin\PresenterBundle\Request\Pagination\PaginationRequestFactory;
use Borodulin\PresenterBundle\Request\Pagination\PaginationRequestInterface;
use Borodulin\PresenterBundle\Request\Sort\SortBuilder;
use Borodulin\PresenterBundle\Request\Sort\SortRequestInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

class DataProviderNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    private NormalizerInterface $normalizer;
    private PaginationRequestFactory $paginationRequestFactory;

    public function __construct(
        PaginationRequestFactory $paginationRequestFactory
    ) {
        $this->paginationRequestFactory = $paginationRequestFactory;
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        if (\is_object($data)) {
            $class = \get_class($data);
            $reflection = new \ReflectionClass($class);

            return $reflection->implementsInterface(DataProviderInterface::class);
        }

        return false;
    }

    /**
     * @return array|array[]|\ArrayObject|\ArrayObject[]|bool|bool[]|float|float[]|int|int[]|null[]|string|string[]|null
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $expandRequest = $context['expand_request'] ?? null;
        unset($context['expand_request']);
        $context['expand'] = $expandRequest instanceof ExpandRequestInterface ? $expandRequest->getExpand() : ($context['expand'] ?? []);
        $filterRequest = $context['filter_request'] ?? null;
        unset($context['filter_request']);
        $sortRequest = $context['sort_request'] ?? null;
        unset($context['sort_request']);
        $paginationRequest = $context['pagination_request'] ?? null;
        unset($context['pagination_request']);

        $sortRequest = $sortRequest instanceof SortRequestInterface ? $sortRequest : null;
        $filterRequest = $filterRequest instanceof FilterRequestInterface ? $filterRequest : null;

        $queryBuilder = $this->prepareQueryBuilder($object, $sortRequest, $filterRequest);

        if ($object instanceof PaginatedDataProviderInterface) {
            $paginationRequest = $paginationRequest instanceof PaginationRequestInterface ?
                $paginationRequest : $this->paginationRequestFactory->createDefault();
        }

        if ($paginationRequest instanceof PaginationRequestInterface) {
            $response = (new PaginationBuilder())
                ->paginate(
                    $paginationRequest,
                    $queryBuilder,
                    fn ($entity) => $this->normalizer->normalize($entity, null, $context)
                );

            return $this->normalizer->normalize($response, null, $context);
        } else {
            return array_map(
                fn ($entity) => $this->normalizer->normalize($entity, null, $context),
                $queryBuilder->fetchAll()
            );
        }
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
