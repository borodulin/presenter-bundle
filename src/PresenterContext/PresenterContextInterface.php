<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\PresenterContext;

interface PresenterContextInterface
{
    public const EXPAND_REQUEST = 'expand_request';
    public const EXPAND = 'expand';
    public const FILTER_REQUEST = 'filter_request';
    public const SORT_REQUEST = 'sort_request';
    public const PAGINATION_REQUEST = 'pagination_request';
    public const NAME_CONVERTER = 'name_converter';
    public const GROUP = 'group';
    public const DEFAULT_GROUP = 'default';

    public function toArray(): array;
}
