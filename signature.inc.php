<?php
class Signatures
{
    protected $signatures_array = [];
    protected $collator;

    public function __construct(ClassCollator $collator)
    {
        $this->collator = $collator;
    }

    public function has($func, $class = '')
    {
        $name = strtolower($class ? ($class . '->' . $func) : $func);

        return isset($this->signatures_array[$name]);
    }

    public function get($func, $class = '')
    {
        if (!$func) {
            throw new Exception('Illegal identifier: {' . "$func, $class" . '}');
        }
        $name = strtolower($class ? ($class . '->' . $func) : $func);
        if (!isset($this->signatures_array[$name])) {
            $this->signatures_array[$name] = new FunctionSignature($this->collator);
        }

        return $this->signatures_array[$name];
    }

    public function export()
    {
        $out = [];
        foreach ($this->signatures_array as $name => $function_signature) {
            $out[$name] = $function_signature->export();
        }

        return $out;
    }
}

class FunctionSignature
{
    protected $arguments = [];
    /** @var FunctionArgument */
    protected $returnType;
    protected $collator;

    public function __construct(ClassCollator $collator)
    {
        $this->collator = $collator;
        $this->returnType = new FunctionArgument(0);
    }

    public function blend(array $arguments, string $returnType)
    {
        foreach ($arguments as $id => $type) {
            $arg = $this->getArgumentById($id);
            $arg->collateWith($type);
            if (!$arg->getName()) {
                $arg->setName($id);
            }
        }

        if ($returnType) {
            $this->returnType->collateWith($returnType);
        }
    }

    public function getReturnType(): string
    {
        return $this->returnType->getType();
    }

    public function getArgumentById($id)
    {
        if (!isset($this->arguments[$id])) {
            $this->arguments[$id] = new FunctionArgument($id);
        }

        return $this->arguments[$id];
    }

    public function getArgumentByName($name)
    {
        foreach ($this->arguments as $argument) {
            if ($argument->getName() === $name) {
                return $argument;
            }
        }
    }

    public function getArguments()
    {
        $args = $this->arguments;
        ksort($args);

        return $args;
    }

    public function export()
    {
        $out = [];
        foreach ($this->arguments as $argument) {
            $out[] = $argument->export();
        }

        return $out;
    }
}

class FunctionArgument
{
    protected $id;
    protected $name;
    protected $type;

    public function __construct($id, $name = null, $type = '???')
    {
        $this->id = $id;
        $this->name = $name;
        if ('null' === $type) {
            $this->type = '???';
        } else {
            $this->type = $type;
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function isUndefined()
    {
        return '???' === $this->type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function collateWith(string $type)
    {
        if ('???' === $this->type) {
            $this->type = $type;
        }

        if ($type === $this->type || '???' === $type || '' === $type) {
            return;
        }

        $tmp = explode('|', $this->type);
        $tmp = array_filter($tmp);
        $tmp = array_flip($tmp);
        $tmp[$type] = 0;

        ksort($tmp);
        if (isset($tmp['null'])) {
            unset($tmp['null']);
            $tmp['null'] = 0; // Always have null as the last option
        }

        $tmp = array_keys($tmp);

        $this->type = implode('|', $tmp);
    }

    public function export()
    {
        return $this->getName() . ' (' . ($this->isUndefined() ? 'mixed' : $this->getType()) . ')';
    }
}
