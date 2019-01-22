<?php

namespace Feedo\ArgumentBuilder\Tests\Fixtures;

use Feedo\ArgumentBuilder\AbstractArgumentBuilder;

class CustomMockArgumentBuilder extends AbstractArgumentBuilder
{
    public function __construct(array $fields)
    {
        $this->fields = $fields;
        parent::__construct();
    }
}
