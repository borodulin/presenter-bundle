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
        $nameConverter = $context[PresenterContextInterface::NAME_CONVERTER] ?? null;
        $nameConverter = $nameConverter instanceof NameConverterInterface ? $nameConverter : null;
        $expandRequest = $context[PresenterContextInterface::EXPAND_REQUEST] ?? null;
        if (!$expandRequest instanceof ExpandRequestInterface && isset($context[PresenterContextInterface::EXPAND])) {
            $expandRequest = $this->expandRequestFactory->createFromArray($context[PresenterContextInterface::EXPAND]);
        }

        return new ObjectContext(
            expandRequest: $expandRequest instanceof ExpandRequestInterface ? $expandRequest : null,
            nameConverter: $nameConverter instanceof NameConverterInterface ? $nameConverter : null,
            group: $context[PresenterContextInterface::GROUP] ?? PresenterContextInterface::DEFAULT_GROUP,
        );
    }
}
