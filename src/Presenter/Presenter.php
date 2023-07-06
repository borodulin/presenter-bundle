<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\Presenter;

use Borodulin\PresenterBundle\PresenterContext\DataProviderContext;
use Borodulin\PresenterBundle\PresenterContext\ObjectContext;

/**
 * @internal
 */
class Presenter implements PresenterInterface
{
    private ?object $object = null;

    public function __construct(
        public readonly ObjectContext $objectContext,
        public readonly DataProviderContext $dataProviderContext,
    ) {
    }

    public function show(object $object): PresenterInterface
    {
        $clone = clone $this;
        $clone->object = $object;

        return $clone;
    }

    public function getObject(): ?object
    {
        return $this->object;
    }
}
