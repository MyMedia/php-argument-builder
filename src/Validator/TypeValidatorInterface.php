<?php

declare(strict_types=1);

namespace Feedo\AbstractArgumentBuilder\Validator;

/**
 * Class TypeValidatorInterface.
 *
 * @author Denis Voytyuk <denis.voytyuk@feedo.cz>
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
