<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\NameConverter;

use Borodulin\PresenterBundle\Serializer\DummyNameConverter;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class NameConverterRegistry
{
    private DummyNameConverter $dummyNameConverter;

    public function __construct(
        private readonly array $nameConverters = []
    ) {
        $this->dummyNameConverter = new DummyNameConverter();
    }

    public function getNameConverter(string $group): NameConverterInterface
    {
        return $this->nameConverters[$group] ?? $this->dummyNameConverter;
    }
}
