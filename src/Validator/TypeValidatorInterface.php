<?php

declare(strict_types=1);

namespace Feedo\ArgumentBuilder\Validator;

/**
 * Class TypeValidatorInterface.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
interface TypeValidatorInterface
{
    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function validate($value);
}
