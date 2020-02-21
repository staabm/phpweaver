<?php namespace PHPWeaver\Signature;

use PHPWeaver\Exceptions\Exception;
use PHPWeaver\Reflector\ClassCollatorInterface;

class Signatures
{
    /** @var FunctionSignature[] */
    protected $signaturesArray = [];
    /** @var ClassCollatorInterface */
    protected $collator;

    /**
     * @param ClassCollatorInterface $collator
     */
    public function __construct(ClassCollatorInterface $collator)
    {
        $this->collator = $collator;
    }

    /**
     * @param string $func
     * @param string $class
     * @param string $namespace
     *
     * @return bool
     */
    public function has(string $func, string $class = '', string $namespace = ''): bool
    {
        if (!$func) {
            throw new Exception('Illegal identifier: {' . "$func, $class, $namespace" . '}');
        }

        $name = strtolower($namespace . ($class ? $class . '->' : '') . $func);

        return isset($this->signaturesArray[$name]);
    }

    /**
     * @param string $func
     * @param string $class
     * @param string $namespace
     *
     * @return FunctionSignature
     */
    public function get(string $func, string $class = '', string $namespace = ''): FunctionSignature
    {
        if (!$func) {
            throw new Exception('Illegal identifier: {' . "$func, $class, $namespace" . '}');
        }
        $name = strtolower($namespace . ($class ? $class . '->' : '') . $func);
        if (!isset($this->signaturesArray[$name])) {
            $this->signaturesArray[$name] = new FunctionSignature($this->collator);
        }

        return $this->signaturesArray[$name];
    }
}
