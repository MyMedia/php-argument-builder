<?php

declare(strict_types=1);

namespace Feedo\AbstractArgumentBuilder;

/**
 * Class SortableArgumentBuilderTrait.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
trait SortableArgumentBuilderTrait
{
    public static function buildSortDirection($value, $direction = self::SORT_ASC)
    {
        return $direction.$value;
    }

    private function validateSort($value, array $values)
    {
        if (0 === mb_strpos($value, self::SORT_DESC)) {
            $value = mb_substr($value, 1);
        }

        return in_array($value, $values, true);
    }
}
