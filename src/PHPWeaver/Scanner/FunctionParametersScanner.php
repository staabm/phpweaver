<?php namespace PHPWeaver\Scanner;

/** Scans for, collects and parses function signatures */
class FunctionParametersScanner implements ScannerInterface
{
    /** @var Token[] */
    protected $signature = [];
    /** @var int */
    protected $parenCount = 0;
    /** @var int */
    protected $state = 0;

    /**
     * @param Token $token
     *
     * @return void
     */
    public function accept(Token $token): void
    {
        if ($token->isA(T_FUNCTION)) {
            $this->state = 1;
        } elseif (1 === $this->state && '(' === $token->getText()) {
            $this->signature = [];
            $this->signature[] = $token;
            $this->parenCount = 1;
            $this->state = 2;
        } elseif (2 === $this->state) {
            $this->signature[] = $token;
            if ('(' === $token->getText()) {
                ++$this->parenCount;
            } elseif (')' === $token->getText()) {
                --$this->parenCount;
            }
            if (0 === $this->parenCount) {
                $this->state = 0;
            }
        }
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return 0 !== $this->state;
    }

    /**
     * @return string[]
     */
    public function getCurrentSignatureAsTypeMap(): array
    {
        $current = null;
        $map = [];
        foreach ($this->signature as $token) {
            if (T_VARIABLE === $token->getToken()) {
                $map[$token->getText()] = $current ? $current : '???';
            } elseif (',' === $token->getText()) {
                $current = null;
            } elseif (T_STRING === $token->getToken()) {
                $current = $token->getText();
            }
        }

        return $map;
    }
}
