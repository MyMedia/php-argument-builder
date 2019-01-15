<?php

declare(strict_types=1);

namespace Feedo\AbstractArgumentBuilder\Exception;

/**
 * Class UndefinedMethodException.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
class UndefinedMethodException extends \RuntimeException
{
    public function __construct($name)
    {
        parent::__construct('Call to undefined method '.__CLASS__.'::'.$name.'()');
    }
}
