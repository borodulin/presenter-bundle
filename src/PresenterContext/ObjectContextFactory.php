<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\PresenterContext;

use Borodulin\PresenterBundle\Request\Expand\ExpandFactory;
use Borodulin\PresenterBundle\Request\Expand\ExpandRequestInterface;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class ObjectContextFactory
{
    public function __construct(
        private readonly ExpandFactory $expandRequestFactory,
    ) {
    }

    public function createFromInputBug(InputBag $inputBag): ObjectContext
    {
        $expand = $this->expandRequestFactory->tryCreateFromInputBug($inputBag);

        return new ObjectContext(
            expandRequest: $expand,
        );
    }

    public function createFromArrayContext(array $context): ObjectContext
    {
        $nameConverter = $context[ObjectContext::NAME_CONVERTER] ?? null;
        $nameConverter = $nameConverter instanceof NameConverterInterface ? $nameConverter : null;
        $expandRequest = $context[ObjectContext::EXPAND_REQUEST] ?? null;
        if (!$expandRequest instanceof ExpandRequestInterface && isset($context[ObjectContext::EXPAND])) {
            $expandRequest = $this->expandRequestFactory->createFromArray($context[ObjectContext::EXPAND]);
        }

        return new ObjectContext(
            expandRequest: $expandRequest instanceof ExpandRequestInterface ? $expandRequest : null,
            nameConverter: $nameConverter instanceof NameConverterInterface ? $nameConverter : null,
            group: $context[ObjectContext::GROUP] ?? ObjectContext::DEFAULT_GROUP,
        );
    }
}
