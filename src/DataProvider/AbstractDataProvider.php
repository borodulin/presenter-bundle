<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\DataProvider;

use Borodulin\PresenterBundle\PresenterContext\PresenterContextAwareInterface;
use Borodulin\PresenterBundle\PresenterContext\PresenterContextInterface;

abstract class AbstractDataProvider implements DataProviderInterface, PresenterContextAwareInterface
{
    protected ?PresenterContextInterface $context = null;

    public function getContext(): ?PresenterContextInterface
    {
        return $this->context;
    }

    public function setContext(?PresenterContextInterface $context): void
    {
        $this->context = $context;
    }
}
