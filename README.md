| [Master][Master] |
|:----------------:|
| [![Build Status][Master image]][Master] |
| [![Coverage Status][Master coverage image]][Master coverage] |
| [![Quality Status][Master quality image]][Master quality] |

[Master]: https://travis-ci.org/MyMedia/php-argument-builder
[Master image]: https://travis-ci.org/MyMedia/php-argument-builder.svg?branch=master
[Master coverage]: https://scrutinizer-ci.com/g/MyMedia/php-argument-builder/?branch=master
[Master coverage image]: https://scrutinizer-ci.com/g/MyMedia/php-argument-builder/badges/coverage.png?b=master
[Master quality]: https://scrutinizer-ci.com/g/MyMedia/php-argument-builder/?branch=master
[Master quality image]: https://scrutinizer-ci.com/g/MyMedia/php-argument-builder/badges/quality-score.png?b=master

Argument Builder Library
===================

AbstractArgumentBuilder class is used in php applications to create custom ArgumentBuilders. 
Generates its own property getters and setters and unset via automated magic function.

# Installation

First, install the dependency:

```bash
$ composer require mymedia/php-argument-builder
```

# Usage examples

## Basic example

* Argument Builder:

```php
<?php

declare(strict_types=1);

namespace App\CustomArgumentBuilder;

use Feedo\ArgumentBuilder\AbstractArgumentBuilder;

/**
 * Class CustomArgumentBuilder.
 *
 * @author Author <author@example.com>
 *
 * @method       getArg1()
 * @method $this setArg1($value)
 * @method       getArg2()
 * @method $this setArg2($value)
 * @method       getArg3()
 * @method $this setArg3(string $value, $_ = null)
 */
class CustomArgumentBuilder extends AbstractArgumentBuilder
{
    protected function load()
    {
        $this->fields = array(
            'arg1' => self::ARGUMENT_TYPE_NUMERIC,
            'arg2' => self::ARGUMENT_TYPE_MIXED,
            'arg3' => array(
                'subArg1' => self::ARGUMENT_TYPE_ENUM,
                'subArg2' => self::ARGUMENT_TYPE_BOOLEAN,
            ),
        );
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\CustomArgumentBuilder;

class ArgumentBuilderHelper
{
    public function setCustomBuilderData(array $data): CustomArgumentBuilder
    {
        $builder = new CustomArgumentBuilder();
        
        $builder
            ->setArg1($data['arg1'])
            ->setArg2($data['arg2'])
            ->setArg3('subArg1', $data['arg3']->getSubArg1())
            ->setArg3('subArg2', $data['arg3']->getSubarg2());
        
        return $builder;
    }
    
    public function unsetCustomBuilderArguments(CustomArgumentBuilder $builder): CustomArgumentBuilder
    {
        $builder->unsetArg1();
        $builder->unsetArg2();
        $builder->unsetArg3('subArg1');
        
        return $builder;
    }
}
```

# Code license

You are free to use the code in this repository under the terms of the MIT license. LICENSE contains a copy of this license.