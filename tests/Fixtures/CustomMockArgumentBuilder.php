<?php

namespace Feedo\AbstractArgumentBuilder\Tests\Fixtures;

use Feedo\AbstractArgumentBuilder\AbstractArgumentBuilder;

class CustomMockArgumentBuilder extends AbstractArgumentBuilder
{
    public function __construct(array $fields)
    {
        $this->fields = $fields;
        parent::__construct();
    }
}
