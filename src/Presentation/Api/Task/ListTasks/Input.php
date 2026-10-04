<?php

declare(strict_types=1);

namespace App\Presentation\Api\Task\ListTasks;

use Yiisoft\Hydrator\Attribute\Parameter\Trim;
use Yiisoft\Input\Http\AbstractInput;
use Yiisoft\Input\Http\Attribute\Data\FromQuery;
use Yiisoft\Validator\Rule\In;
use Yiisoft\Validator\Rule\Integer;
use Yiisoft\Validator\Rule\Length;

#[FromQuery]
final class Input extends AbstractInput
{
    /** @var positive-int */
    #[Integer(min: 1)]
    public int $page = 1;

    /** @var positive-int */
    #[Integer(min: 1, max: 100)]
    public int $limit = 20;

    #[Trim]
    #[Length(max: 100, skipOnEmpty: true)]
    public string $search = '';

    #[In(['desc', 'asc'], message: 'Sort must be "desc" or "asc".')]
    public string $sort = 'desc';
}
