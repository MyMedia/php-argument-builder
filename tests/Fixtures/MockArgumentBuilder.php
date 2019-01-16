<?php

namespace Feedo\AbstractArgumentBuilder\Tests\Fixtures;

use Feedo\AbstractArgumentBuilder\AbstractArgumentBuilder;

/**
 * @method $this getArg1()
 * @method       setArg1($value)
 * @method $this unsetArg1($value)
 * @method       getArg2()
 * @method $this setArg2($value)
 * @method       unsetArg2($value)
 * @method $this getSub1()
 * @method       setSub1($value)
 * @method       unsetSub1($value)
 * @method $this getSub2()
 * @method       setSub2($value, $_ = null)
 * @method       unsetSub2($value)
 * @method $this getEnum()
 * @method       setEnum($value)
 * @method       unsetEnum($value)
 */
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
