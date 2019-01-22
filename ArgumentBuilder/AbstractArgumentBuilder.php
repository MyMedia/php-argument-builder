<?php

declare(strict_types=1);

namespace Feedo\ArgumentBuilder;

use Feedo\ArgumentBuilder\Validator\ArgumentBuilderTypeValidator;
use Feedo\ArgumentBuilder\Validator\TypeValidatorInterface;

/**
 * Class AbstractArgumentBuilder.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
abstract class AbstractArgumentBuilder implements ArgumentBuilderInterface
{
    public const ARGUMENT_TYPE_MIXED = 0;
    public const ARGUMENT_TYPE_ARGUMENT_BUILDER = 1;
    public const ARGUMENT_TYPE_NUMERIC = 2;
    public const ARGUMENT_TYPE_ENUM = 3;
    public const ARGUMENT_TYPE_BOOLEAN = 4;

    protected $args = array();

    protected $fields = array(
        //'arg1' => self::ARGUMENT_TYPE_MIXED,
        //'arg2' => SomeArgumentBuilder::class,
    );

    public function __construct()
    {
        $this->load();
        $this->normalizeFields();
    }

    protected function load()
    {
    }

    /**
     * Translates a camel case string into a string with underscores (e.g. firstName -&gt; first_name).
     *
     * @param string $str String in camel case format
     *
     * @return string $str Translated into underscore format
     */
    private function camelCaseToSnakeCase($str)
    {
        $str[0] = strtolower($str[0]);

        return preg_replace_callback('/([A-Z])/', function ($c) {
            return '_'.strtolower($c[1]);
        }, $str);
    }

    /**
     * Translates a snake case string into a camel-cased string (e.g. first_name -&gt; FirstName).
     *
     * @param string $str
     *
     * @return string
     */
    private function snakeCaseToCamelCase($str)
    {
        return implode(array_map('ucfirst', explode('_', $str)));
    }

    /**
     * Normalizes field definitions so that they have the same format (expands shortcuts to array).
     *
     * @throws Exception\InvalidDefinitionException
     */
    private function normalizeFields()
    {
        foreach ($this->fields as $name => $field) {
            // Consider string value as a class name
            if (is_string($field)) {
                $this->fields[$name] = array(
                    'type' => self::ARGUMENT_TYPE_ARGUMENT_BUILDER,
                    'class' => $field,
                    'validator' => new ArgumentBuilderTypeValidator($name, $field),
                );

            // consider numeric values as type name
            } elseif (is_int($field)) {
                $this->fields[$name] = array(
                    'type' => $field,
                    'validator' => null,
                );
            }

            if (!is_array($this->fields[$name])) {
                throw new Exception\InvalidDefinitionException(
                    'Field description must be either string (shortcut for class name), or int (shortcut for field type) or array (full form)'
                );
            }

            if (!array_key_exists('type', $this->fields[$name])) {
                throw new Exception\InvalidDefinitionException(
                    'Field type is not defined'
                );
            }

            if (
                self::ARGUMENT_TYPE_ARGUMENT_BUILDER === $this->fields[$name]['type']
                && empty($this->fields[$name]['class'])
            ) {
                throw new Exception\InvalidDefinitionException(
                    'Field of type ARGUMENT_TYPE_ARGUMENT_BUILDER must have class defined'
                );
            }
        }
    }

    /**
     * Validates non-null fields according to the field type and validator (if given).
     *
     * @param string $field
     * @param mixed  $value
     *
     * @return bool
     *
     * @throws Exception\InvalidDefinitionException
     */
    private function validateFieldValue($field, $value)
    {
        if (null === $value) {
            return true;
        }

        if (
            !array_key_exists('validator', $this->fields[$field])
            || null === $this->fields[$field]['validator']
            || false === $this->fields[$field]['validator']
        ) {
            return true;
        }

        $validator = $this->fields[$field]['validator'];

        if ($validator instanceof TypeValidatorInterface) {
            return $validator->validate($value);
        }

        if (!is_callable($validator)) {
            throw new Exception\InvalidDefinitionException(
                sprintf('Validator for the field "%s" is defined but is not callable', $field)
            );
        }

        return call_user_func($this->fields[$field]['validator'], $value);
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     *
     * @throws Exception\UndefinedMethodException
     */
    public function __call($name, $arguments)
    {
        foreach (array('callGet', 'callSet', 'callUnset') as $method) {
            try {
                return $this->{$method}($name, $arguments);
            } catch (Exception\UnmatchedCallTypeException $e) {
                // continue cycle
            }
        }

        throw new Exception\UndefinedMethodException($name);
    }

    /**
     * Provides $builder->getName($arguments...).
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed|null
     *
     * @throws Exception\UnmatchedCallTypeException
     * @throws Exception\InvalidArgumentException
     * @throws Exception\UndefinedMethodException
     */
    private function callGet($name, $arguments)
    {
        if (!preg_match('/^get([A-Z\-][A-Za-z0-9]+)$/', $name, $matches)) {
            throw new Exception\UnmatchedCallTypeException();
        }

        $field = lcfirst($matches[1]);
        $field = $this->camelCaseToSnakeCase($field);

        if (!array_key_exists($field, $this->fields)) {
            throw new Exception\UndefinedMethodException($name);
        }

        if (self::ARGUMENT_TYPE_ARGUMENT_BUILDER !== $this->fields[$field]['type'] && 0 !== count($arguments)) {
            throw new Exception\InvalidArgumentException('Method '.__CLASS__.'::'.$name.'() must take exactly 0 arguments');
        }

        if (self::ARGUMENT_TYPE_ARGUMENT_BUILDER === $this->fields[$field]['type'] && count($arguments) > 0) {
            if (!is_string($arguments[0])) {
                throw new Exception\InvalidArgumentException(
                    'Method '.__CLASS__.'::'.$name.'() expects the first parameter to be string if you want to get sub-value'
                );
            }

            if (!empty($this->args[$field])) {
                return call_user_func_array(array($this->args[$field], 'get'.$this->snakeCaseToCamelCase($arguments[0])), array_slice($arguments, 1));
            }
        }

        return isset($this->args[$field]) ? $this->args[$field] : null;
    }

    /**
     * Provides $builder->setName($value).
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return $this
     *
     * @throws Exception\InvalidDefinitionException
     * @throws Exception\UndefinedMethodException
     * @throws Exception\UnmatchedCallTypeException
     */
    private function callSet($name, $arguments)
    {
        if (!preg_match('/^set([A-Z\-][A-Za-z0-9]+)$/', $name, $matches)) {
            throw new Exception\UnmatchedCallTypeException();
        }

        $field = $matches[1];
        if ('-' !== $field[0]) {
            $field = lcfirst($matches[1]);
        }
        $field = $this->camelCaseToSnakeCase($field);

        if (!array_key_exists($field, $this->fields)) {
            throw new Exception\UndefinedMethodException($name);
        }

        if (self::ARGUMENT_TYPE_ARGUMENT_BUILDER !== $this->fields[$field]['type'] && 1 !== count($arguments)) {
            throw new Exception\InvalidArgumentException('Method '.__CLASS__.'::'.$name.'() must take exactly 1 argument');
        }

        $value = $arguments[0];

        if (self::ARGUMENT_TYPE_ARGUMENT_BUILDER === $this->fields[$field]['type'] && count($arguments) > 1) {
            if (!is_string($arguments[0])) {
                throw new Exception\InvalidArgumentException(
                    'Method '.__CLASS__.'::'.$name.'() expects the first parameter to be string'
                );
            }

            $class = $this->fields[$field]['class'];
            $value = isset($this->args[$field]) ? $this->args[$field] : new $class();

            call_user_func_array(array($value, 'set'.$this->snakeCaseToCamelCase($arguments[0])), array_slice($arguments, 1));
        } elseif (!$this->validateFieldValue($field, $value)) {
            throw new Exception\InvalidArgumentException(sprintf('Invalid value "%s" for field "%s"', $value, $field));
        }

        $this->args[$field] = $value;

        return $this;
    }

    /**
     * Provides $builder->unsetName().
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return $this
     *
     * @throws Exception\UnmatchedCallTypeException
     * @throws Exception\UndefinedMethodException
     */
    private function callUnset($name, $arguments)
    {
        if (!preg_match('/^unset([A-Z\-][A-Za-z0-9]+)$/', $name, $matches)) {
            throw new Exception\UnmatchedCallTypeException();
        }

        $field = $matches[1];
        if ('-' !== $field[0]) {
            $field = lcfirst($matches[1]);
        }
        $field = $this->camelCaseToSnakeCase($field);

        // Check if field is defined for this ArgumentBuilder
        if (!array_key_exists($field, $this->fields)) {
            throw new Exception\UndefinedMethodException($name);
        }

        // Check argument count
        if (self::ARGUMENT_TYPE_ARGUMENT_BUILDER !== $this->fields[$field]['type'] && 0 !== count($arguments)) {
            throw new Exception\InvalidArgumentException('Method '.__CLASS__.'::'.$name.'() must take exactly 0 arguments');
        }

        // no parameters means plain unsetName() call, so no syntatic sugar logic involved
        if (0 === count($arguments)) {
            unset($this->args[$field]);

            return $this;
        }

        // At this point we already filtered out zero arguments and non-ARGUMENT_BUILDER types,
        // so we expect that the first argument is string because it's a name for a field.
        // Let's check that!
        if (!is_string($arguments[0])) {
            throw new Exception\InvalidArgumentException(
                'Method '.__CLASS__.'::'.$name.'() expects the first parameter to be string if you want to unset sub-value'
            );
        }

        // To call unset on a sub-ArgumentBuilder, we need to make sure it exists (otherwise there is nothing to unset)
        if (!empty($this->args[$field])) {
            // Allow syntactic sugar (pass unset call recursively)
            call_user_func_array(array($this->args[$field], 'unset'.$this->snakeCaseToCamelCase($arguments[0])), array_slice($arguments, 1));
        }

        return $this;
    }

    private function transformValue($field, $value)
    {
        if (self::ARGUMENT_TYPE_BOOLEAN === $this->fields[$field]['type']) {
            if ('any' === $value) {
                return $value;
            }

            return $value ? 'true' : 'false';
        }

        return $value;
    }

    /**
     * Returns array of arguments.
     *
     * @return array
     */
    public function build()
    {
        $result = array();

        foreach ($this->args as $key => $arg) {
            if ($arg instanceof ArgumentBuilderInterface) {
                $result[$key] = $arg->build(); //@todo: missing Circular Reference check
            } else {
                $result[$key] = $this->transformValue($key, $arg);
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return http_build_query($this->build());
    }
}
