<?php

namespace Feedo\ArgumentBuilder\Tests\Unit;

use Feedo\ArgumentBuilder\Exception\InvalidArgumentException;
use Feedo\ArgumentBuilder\Exception\InvalidDefinitionException;
use Feedo\ArgumentBuilder\Exception\UndefinedMethodException;
use Feedo\ArgumentBuilder\AbstractArgumentBuilder;
use Feedo\ArgumentBuilder\Tests\Fixtures\CustomMockArgumentBuilder;
use Feedo\ArgumentBuilder\Tests\Fixtures\MockArgumentBuilder;
use Feedo\ArgumentBuilder\Tests\Fixtures\SubMockArgumentBuilder;
use PHPUnit\Framework\TestCase;

class AbstractArgumentBuilderTest extends TestCase
{
    private $sampleData;
    private $sampleDataEncoded;

    protected function setUp()
    {
        $this->sampleData = array(
            'arg1' => 'xxx',
            'arg2' => 'yyy',
            'sub1' => array(
                'subarg1' => 'zzz',
                'subarg2' => 'aaa',
                'boolarg' => 'false',
            ),
            'sub2' => array(
                'subarg1' => 'nnn',
                'subarg2' => 'mmm',
                'boolarg' => 'true',
            ),
            'enum' => 'val1',
        );

        $this->sampleDataEncoded = http_build_query($this->sampleData);
    }

    private function getBuilderMock()
    {
        $builder = new MockArgumentBuilder();
        $builder->setArg1('xxx');
        $builder->setArg2('yyy');
        $builder->setSub1('subarg1', 'zzz');
        $builder->setSub1('subarg2', 'aaa');
        $builder->setSub1('boolarg', false);
        $builder->setSub2('subarg1', 'nnn');
        $builder->setSub2('subarg2', 'mmm');
        $builder->setSub2('boolarg', true);
        $builder->setEnum('val1');

        return $builder;
    }

    public function testBuild()
    {
        $builder = $this->getBuilderMock();

        $this->assertEquals($this->sampleData, $builder->build());
    }

    public function testToString()
    {
        $builder = $this->getBuilderMock();

        $this->assertEquals($this->sampleDataEncoded, (string) $builder);
    }

    public function testGetFields()
    {
        $builder = $this->getBuilderMock();

        $this->assertEquals('xxx', $builder->getArg1());
        $this->assertInstanceOf(SubMockArgumentBuilder::class, $builder->getSub1());
        $this->assertEquals('zzz', $builder->getSub1('subarg1'));
        $builder->unsetSub1();
        $this->assertEquals(null, $builder->getSub1('subarg1'));
    }

    public function testSetNullFields()
    {
        $data = $this->sampleData;
        $data['arg1'] = null;
        $data['sub1'] = null;
        $data['sub2']['subarg1'] = null;

        $builder = $this->getBuilderMock();
        $builder->setArg1(null);
        $builder->setSub1(null);
        $builder->setSub2('subarg1', null);

        $this->assertEquals($data, $builder->build());
    }

    public function testSetSubAsObject()
    {
        $builder = $this->getBuilderMock();
        $builder->setSub1(new SubMockArgumentBuilder());
    }

    public function testUnsetFields()
    {
        $data = $this->sampleData;
        unset($data['arg1'], $data['sub1'], $data['sub2']['subarg1']);

        $builder = $this->getBuilderMock();
        $builder->unsetArg1();
        $builder->unsetSub1();
        $builder->unsetSub2('subarg1');

        $this->assertEquals($data, $builder->build());
    }

    public function testSetFields()
    {
        $data = $this->sampleData;
        $data['arg1'] = 'aaa';
        $data['sub1']['subarg1'] = 'new';
        $data['sub1']['boolarg'] = 'true';
        $data['sub2'] = array('subarg1' => 'good');

        $builder = $this->getBuilderMock();
        $builder->setArg1('aaa');
        $builder->setSub1('subarg1', 'new');
        $builder->setSub2((new SubMockArgumentBuilder())->setSubarg1('good'));
        $builder->setSub1('boolarg', true);

        $this->assertEquals($data, $builder->build());
    }

    public function testInvalidDefinitionNotArray()
    {
        $this->expectException(InvalidDefinitionException::class);
        $this->expectExceptionMessage('Field description must be either string (shortcut for class name), or int (shortcut for field type) or array (full form)');

        $builder = new CustomMockArgumentBuilder(array(
            'arg1' => new \stdClass(),
        ));
        $builder->setArg1('aaa');
    }

    public function testInvalidDefinitionMissingType()
    {
        $this->expectException(InvalidDefinitionException::class);
        $this->expectExceptionMessage('Field type is not defined');

        $builder = new CustomMockArgumentBuilder(array(
            'arg1' => array(
            ),
        ));
        $builder->setArg1('aaa');
    }

    public function testInvalidDefinitionMissingClass()
    {
        $this->expectException(InvalidDefinitionException::class);
        $this->expectExceptionMessage('Field of type ARGUMENT_TYPE_ARGUMENT_BUILDER must have class defined');

        $builder = new CustomMockArgumentBuilder(array(
            'arg1' => array(
                'type' => AbstractArgumentBuilder::ARGUMENT_TYPE_ARGUMENT_BUILDER,
            ),
        ));
        $builder->setArg1('aaa');
    }

    public function testCallUndefinedMethod()
    {
        $this->expectException(UndefinedMethodException::class);
        $this->expectExceptionMessageRegExp('/^Call to undefined method/');

        $builder = $this->getBuilderMock();
        $builder->dummyCall('foo');
    }

    public function testSetNonexistingParameter()
    {
        $this->expectException(UndefinedMethodException::class);
        $this->expectExceptionMessageRegExp('/^Call to undefined method/');

        $builder = $this->getBuilderMock();
        $builder->setNonexistant('blah');
    }

    public function testUnsetNonexistingParameter()
    {
        $this->expectException(UndefinedMethodException::class);
        $this->expectExceptionMessageRegExp('/^Call to undefined method/');

        $builder = $this->getBuilderMock();
        $builder->unsetNonexistant();
    }

    /**
     * @dataProvider provideNonstringData
     */
    public function testInvalidSugarUsage($value)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/^Method .+ expects the first parameter to be string if you want to get sub-value$/');

        $builder = $this->getBuilderMock();
        $builder->getSub1($value);
    }

    public function testInvalidGetCall()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/Method .+ must take exactly 0 arguments/');

        $builder = $this->getBuilderMock();
        $builder->getArg1('yyy');
    }

    public function testGetNonexistingParameter()
    {
        $this->expectException(UndefinedMethodException::class);
        $this->expectExceptionMessageRegExp('/^Call to undefined method/');

        $builder = $this->getBuilderMock();
        $builder->getDummy();
    }

    public function testInvalidSubValue()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/^Value of the field ".+" must an instance of ArgumentBuilderInterface$/');

        $builder = $this->getBuilderMock();
        $builder->setSub1(new \stdClass());
    }

    public function testInvalidSubValueNonObject()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/^Invalid value type. Expected instance of ".+", got ".+".$/');

        $builder = $this->getBuilderMock();
        $builder->setSub1(array('xxx'));
    }

    public function testSetValueInvalidArgCount()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/^Method .+ must take exactly 1 argument$/');

        $builder = $this->getBuilderMock();
        $builder->setArg1('aaa', 'bbb');
    }

    /**
     * @dataProvider provideNonstringData
     */
    public function testSetValueInvalidFirstParamSyntaxSugar($value)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/^Method .+ expects the first parameter to be string$/');

        $builder = $this->getBuilderMock();
        $builder->setSub1($value, 'bbb');
    }

    public function testUnsetWrongArgumentCount()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/^Method .+ must take exactly 0 arguments$/');

        $builder = $this->getBuilderMock();
        $builder->unsetArg1('bbb');
    }

    /**
     * @dataProvider provideNonstringData
     */
    public function testUnsetInvalidArgument($value)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/^Method .+ expects the first parameter to be string if you want to unset sub-value$/');

        $builder = $this->getBuilderMock();
        $builder->unsetSub1($value);
    }

    public function testArgumentBuilderTypeClassDoesNotExist()
    {
        $this->expectException(InvalidDefinitionException::class);
        $this->expectExceptionMessageRegExp('/^Class ".+" not found \(field\: ".+"\)$/');

        $builder = new CustomMockArgumentBuilder(array(
            'arg1' => 'DummyNonExistantClass',
        ));
        $builder->setArg1('something');
    }

    /**
     * @dataProvider provideInvalidValidatorData
     */
    public function testArgumentBuilderInvalidValidator($validator)
    {
        $this->expectException(InvalidDefinitionException::class);
        $this->expectExceptionMessageRegExp('/^Validator for the field ".+" is defined but is not callable$/');

        $builder = new CustomMockArgumentBuilder(array(
            'arg1' => array(
                'type' => AbstractArgumentBuilder::ARGUMENT_TYPE_MIXED,
                'validator' => $validator,
            ),
        ));
        $builder->setArg1('something');
    }

    /**
     * @dataProvider provideEmptyValidatorData
     */
    public function testArgumentBuilderEmptyValidator($validator)
    {
        $builder = new CustomMockArgumentBuilder(array(
            'arg1' => array(
                'type' => AbstractArgumentBuilder::ARGUMENT_TYPE_MIXED,
                'validator' => $validator,
            ),
        ));
        $builder->setArg1('something');
    }

    public function testArgumentBuilderValidatorResultFalse()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/^Invalid value ".+" for field ".+"$/');

        $builder = new CustomMockArgumentBuilder(array(
            'arg1' => array(
                'type' => AbstractArgumentBuilder::ARGUMENT_TYPE_MIXED,
                'validator' => function () {
                    return false;
                },
            ),
        ));
        $builder->setArg1('something');
    }

    /**
     * @return array
     */
    public function provideInvalidValidatorData()
    {
        return [
            [0],
            [1],
            ['nonexistantfunction'],
            [new \stdClass()],
            [array()],
            [array(new \stdClass(), 'some')],
        ];
    }

    /**
     * @return array
     */
    public function provideNonstringData()
    {
        return [
            [0],
            [1],
            [new \stdClass()],
            [array()],
        ];
    }

    /**
     * @return array
     */
    public function provideEmptyValidatorData()
    {
        return [
            [null],
            [false],
        ];
    }
}
