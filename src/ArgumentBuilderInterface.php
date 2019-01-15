<?php

declare(strict_types=1);

namespace Feedo\AbstractArgumentBuilder;

/**
 * Interface ArgumentBuilderInterface.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
interface ArgumentBuilderInterface
{
    /**
     * Returns array of arguments.
     *
     * @return array
     */
    public function build();
}
