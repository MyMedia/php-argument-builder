<?php

declare(strict_types=1);

namespace Feedo\AbstractArgumentBuilder\Validator;

use Feedo\AbstractArgumentBuilder\Exception;

/**
 * Class ArgumentBuilderTypeValidator.
 *
 * @author Denis Voytyuk <denis.voytyuk@feedo.cz>
 */
class ArgumentBuilderTypeValidator implements TypeValidatorInterface
{
    /**
     * @var
     */
    private $name;

    /**
     * @var string
     */
    private $class;

    /**
     * ArgumentBuilderTypeValidator constructor.
     *
     * @param string $name
     * @param string $class
     */
    public function __construct($name, $class)
    {
        $this->name = $name;
        $this->class = $class;
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\InvalidDefinitionException
     * @throws \ReflectionException
     */
    public function validate($value)
    {
        if (!class_exists($this->class)) {
            throw new Exception\InvalidDefinitionException(sprintf('Class "%s" not found (field: "%s")', $this->class, $this->name));
        }

        if (!is_object($value)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid value type. Expected instance of "%s", got "%s".',
                $this->class,
                is_object($value) ? 'an instance of '.get_class($value) : gettype($value)
            ));
        }

        $refClass = new \ReflectionClass($this->class);
        if (!$refClass->isInstance($value)) {
            throw new Exception\InvalidArgumentException(
                'Value of the field "'.$this->name.'" must an instance of ArgumentBuilderInterface'
            );
        }

        return true;
    }
}
