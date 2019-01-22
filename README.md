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

AbstractArgumentBuilder class is used to build a query string from pre-defined validatable parameters. It can also be used to the reverse so that the application uses only the desired validated parameters.
Generates its own property getters and setters and unset via automated magic function.

# Installation

First, install the dependency:

```bash
$ composer require mymedia/php-argument-builder
```

# Usage examples

## Basic usage

### AbstractArgumentBuilder

AbstractArgumentBuilder implements ArgumentBuilderInterface which provides only one method: build(). Magic function __call() provides access to getters and setters, and unset, without the need to generate them manually. It also provides us with __toString() function that returns http query string.

```php
$builder
    ->setSearch('foobar')
    ->setFilter('color', 'iridescent')
    ->setFilter('size', 'height', 2)
    ->setFilter('size', 'width', 10);
```

Using the provided functionality, the presented code will generate query arguments like: `http://example.com/?search=foobar&filter[color]=iridescent&filter[size][height]=2&filter[size][width]=10.` However this requires additional classes to extend the `AbstractArgumentBuilder` that we will define in the next section.

### Extending AbstractArgumentBuilder

#### Argument Types

`AbstractArgumentBuilder` defines following constants, that are used in field validation:
 
```php
    const ARGUMENT_TYPE_MIXED = 0;
    const ARGUMENT_TYPE_ARGUMENT_BUILDER = 1;
    const ARGUMENT_TYPE_NUMERIC = 2;
    const ARGUMENT_TYPE_ENUM = 3;
    const ARGUMENT_TYPE_BOOLEAN = 4;
```

#### Classes

```php
class SearchArgumentBuilder extends AbstractArgumentBuilder
{
    protected $fields = [
        'search' => self::ARGUMENT_TYPE_MIXED,
        'filter' => SearchFilterArgumentBuilder::class,
    ];
}
```

```php
class SearchFilterArgumentBuilder extends AbstractArgumentBuilder
{
    protected $fields = [
        'color' => self::ARGUMENT_TYPE_MIXED,
        'size' => SearchFilterSizeArgumentConverter::class, 
    ];
}
```

```php
class SearchFilterSizeArgumentBuilder extends AbstractArgumentBuilder
{
    protected $fields = [
        'height' => self::ARGUMENT_TYPE_MIXED,
        'width' => self::ARGUMENT_TYPE_MIXED,
    ];
}
```

### Field Validation

A simple way to provide field validation, it will fail if the defined condition is not met:

```php
class SearchFilterPriceArgumentBuilder extends AbstractArgumentBuilder
{
    protected function load()
    {
        $this->fields = array(
            'min' => array(
                'type' => self::ARGUMENT_TYPE_MIXED,
                'validator' => function ($value) {
                    return $value >= 0 && $value <= 1000;
                }
            ),
            'max' => array(
                'type' => self::ARGUMENT_TYPE_MIXED,
                'validator' => function ($value) {
                    return $value >= 0 && $value <= 1000;
                }
            ),
        );
    }
}
```

# Code license

You are free to use the code in this repository under the terms of the MIT license. LICENSE contains a copy of this license.