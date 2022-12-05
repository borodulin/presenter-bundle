<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\PresenterHandler;

interface CustomExpandInterface
{
    /**
     * Association Names.
     *
     * @example ['customer' => fn ($entity) => $entity->getCustomer(), 'company']
     */
    public function getExpandFields(): array;
}
