<?php

namespace Feedo\AbstractArgumentBuilder\Tests\Unit;

use Feedo\AbstractArgumentBuilder\AbstractArgumentBuilder;
use PHPUnit\Framework\TestCase;

class AbstractArgumentBuilderTest extends TestCase
{
    public $concreteClass;

    public function setUp()
    {
        $this->concreteClass = new class extends AbstractArgumentBuilder {
            public $fields = [
                'corn' => 0,
                'wheat' => 0,
                'potatoes' => 0,
            ];
        };
    }

    /**
     * @dataProvider correctGetDataProvider
     *
     * @param string $name
     * @param array $arguments
     */
    public function testCallGet($name, $arguments): void
    {
        $this->setUp();
        $result = $this->concreteClass->{'get'.$name}();

        $this->assertEquals('', $result);
    }

    public function testCallException()
    {
        $this->setUp();

        $name = 'Cannabis';

        $this->expectException('Feedo\AbstractArgumentBuilder\Exception\UndefinedMethodException');
        $this->concreteClass->{'get'.$name}();

    }

    /**
     * @dataProvider correctSetDataProvider
     *
     * @param $name
     * @param $arguments
     */
    public function testCallSet($name, $arguments): void
    {
        $this->setUp();

        $result = $this->concreteClass->{'set'.$name}($arguments)->build();

        $args = [
            strtolower($name) => $arguments,
        ];

        $this->assertEquals($args, $result);
    }

    /**
     * @return array
     */
    public function correctGetDataProvider(): array
    {
        return [
            ['Corn', []],
            ['Wheat', []],
            ['Potatoes', []],
        ];
    }

    /**
     * @return array
     */
    public function correctSetDataProvider(): array
    {
        return [
            ['Corn', 'Pop,pop,pop'],
            ['Wheat', 'Gluten'],
            ['Potatoes', 'Po-ta-toes'],
        ];
    }
}