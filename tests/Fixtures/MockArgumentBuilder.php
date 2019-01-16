<?php

namespace Feedo\AbstractArgumentBuilder\Tests\Fixtures;

use Feedo\AbstractArgumentBuilder\AbstractArgumentBuilder;

/**
 * @method $this getArg1()
 * @method       setArg1($value, $_ = null)
 * @method $this unsetArg1($_ = null)
 * @method       getArg2()
 * @method $this setArg2($value, $_ = null)
 * @method       unsetArg2($_ = null)
 * @method $this getSub1()
 * @method       setSub1($value, $_ = null)
 * @method       unsetSub1($_ = null)
 * @method $this getSub2()
 * @method       setSub2($value, $_ = null)
 * @method       unsetSub2($_ = null)
 * @method $this getEnum()
 * @method       setEnum($value, $_ = null)
 * @method       unsetEnum($_ = null)
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
