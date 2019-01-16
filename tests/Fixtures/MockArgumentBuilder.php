<?php

namespace Feedo\AbstractArgumentBuilder\Tests\Fixtures;

use Feedo\AbstractArgumentBuilder\AbstractArgumentBuilder;

class MockArgumentBuilder extends AbstractArgumentBuilder
{
    protected function load()
    {
        $this->fields = array(
            'arg1' => self::ARGUMENT_TYPE_MIXED,
            'arg2' => self::ARGUMENT_TYPE_MIXED,
            'sub1' => SubMockArgumentBuilder::class,
            'sub2' => array(
                'type' => self::ARGUMENT_TYPE_ARGUMENT_BUILDER,
                'class' => SubMockArgumentBuilder::class,
            ),
            'enum' => array(
                'type' => self::ARGUMENT_TYPE_ENUM,
                'validator' => function ($value) {
                    return in_array($value, array('val1', 'val2'));
                },
            ),
        );
    }
}
