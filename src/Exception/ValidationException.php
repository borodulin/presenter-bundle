<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ValidationException extends HttpException
{
    public function __construct(
        private readonly array $errors
    ) {
        parent::__construct(422, 'Validation error');
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
