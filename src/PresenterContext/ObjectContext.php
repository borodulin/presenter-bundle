<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\PresenterContext;

use Borodulin\PresenterBundle\Request\Expand\ExpandRequestInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class ObjectContext implements PresenterContextInterface
{
    public function __construct(
        public ?ExpandRequestInterface $expandRequest = null,
        public ?NameConverterInterface $nameConverter = null,
        public string $group = self::DEFAULT_GROUP,
    ) {
    }

    public function toArray(): array
    {
        $context = [];
        if (null !== $this->expandRequest) {
            $context[self::EXPAND_REQUEST] = $this->expandRequest;
        }
        if (null !== $this->nameConverter) {
            $context[self::NAME_CONVERTER] = $this->nameConverter;
        }
        $context[self::GROUP] = $this->group;

        return $context;
    }
}
