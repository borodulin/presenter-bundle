<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\Presenter;

use Borodulin\PresenterBundle\Request\Expand\ExpandRequestInterface;
use Borodulin\PresenterBundle\Request\Filter\FilterRequestInterface;
use Borodulin\PresenterBundle\Request\Pagination\PaginationRequestInterface;
use Borodulin\PresenterBundle\Request\Sort\SortRequestInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
class Presenter implements PresenterInterface
{
    public function __construct(
        private readonly NormalizerInterface $normalizer,
        private readonly ?FilterRequestInterface $filterRequest,
        private readonly ?SortRequestInterface $sortRequest,
        private readonly ?PaginationRequestInterface $paginationRequest,
        private readonly ?ExpandRequestInterface $expandRequest,
        private readonly ?NameConverterInterface $nameConverter = null
    ) {

    }

    public function show(object $object, array $context = [])
    {
        if (!\array_key_exists('expand_request', $context)) {
            $context['expand_request'] = $this->expandRequest;
        }
        if (!\array_key_exists('filter_request', $context)) {
            $context['filter_request'] = $this->filterRequest;
        }
        if (!\array_key_exists('sort_request', $context)) {
            $context['sort_request'] = $this->sortRequest;
        }
        if (!\array_key_exists('pagination_request', $context)) {
            $context['pagination_request'] = $this->paginationRequest;
        }
        if (!\array_key_exists('name_converter', $context)) {
            $context['name_converter'] = $this->nameConverter;
        }

        return $this->normalizer->normalize($object, null, $context);
    }
}
