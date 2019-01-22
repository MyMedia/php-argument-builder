<?php

namespace Feedo\ArgumentBuilder\Tests\Fixtures;

use Feedo\ArgumentBuilder\AbstractArgumentBuilder;

class SubMockArgumentBuilder extends AbstractArgumentBuilder
{
    protected $fields = array(
        'subarg1' => self::ARGUMENT_TYPE_MIXED,
        'subarg2' => self::ARGUMENT_TYPE_MIXED,
        'boolarg' => self::ARGUMENT_TYPE_BOOLEAN,
    );
}
