<?php

namespace Feedo\AbstractArgumentBuilder\Tests\Fixtures;

use Feedo\AbstractArgumentBuilder\AbstractArgumentBuilder;

class SubMockArgumentBuilder extends AbstractArgumentBuilder
{
    protected $fields = array(
        'subarg1' => self::ARGUMENT_TYPE_MIXED,
        'subarg2' => self::ARGUMENT_TYPE_MIXED,
        'boolarg' => self::ARGUMENT_TYPE_BOOLEAN,
    );
}
