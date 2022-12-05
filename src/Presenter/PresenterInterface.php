<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\Presenter;

interface PresenterInterface
{
    /**
     * @return mixed
     */
    public function show(object $object, array $context = []);
}
