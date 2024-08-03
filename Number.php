<?php

class Number
{
    private const INTERNAL_SCALE = 20;
    private const ITERATIONS = 100;
    private const HUMAN_UNITS = [
        3 => 'K', 6 => 'M', 9 => 'G', 12 => 'T', 15 => 'P', 18 => 'E'
    ];

    private string $value;

    public function __construct(string $value = "0")
    {
        bcscale(self::INTERNAL_SCALE);
        $this->value = $this->normalizeFloat($value);
    }

    private function normalizeFloat(string $value): string
    {
        $parts = explode("E", $value);
        return (count($parts) == 2) ? bcmul($parts[0], bcpow(10, $parts[1])) : $value;
    }

    public function clone(): self
    {
        return new self($this->value);
    }

    // Basic arithmetic operations
    public function add(string $delta): self
    {
        return new self(bcadd($this->value, $this->normalizeFloat($delta)));
    }

    public function sub(string $delta): self
    {
        return new self(bcsub($this->value, $this->normalizeFloat($delta)));
    }

    public function mul(string $delta): self
    {
        return new self(bcmul($this->value, $this->normalizeFloat($delta)));
    }

    public function div(string $delta): self
    {
        return new self(bcdiv($this->value, $this->normalizeFloat($delta)));
    }

    public function mod(string $delta): self
    {
        return new self(bcmod($this->value, $this->normalizeFloat($delta)));
    }

    public function pow(string $delta): self
    {
        return new self(bcpow($this->value, $this->normalizeFloat($delta)));
    }

    // Logarithmic functions
    public function ln(): self
    {
        $retval = new self(0);
        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $pow = (new self(2))->mul($i)->add(1);
            $mul = (new self(1))->div($pow);
            $base = $this->sub(1)->div($this->add(1));
            $fraction = $base->pow($pow)->mul($mul);
            $retval = $retval->add($fraction);
        }
        return $retval->mul(2);
    }

    public function log(string $base): self
    {
        $baseLn = (new self($base))->ln();
        return $this->ln()->div($baseLn);
    }

    // Rounding and truncation
    public function floor(): self
    {
        $result = $this->isNegative() ? -1 : 0;
        return new self(bcadd($this->value, $result, 0));
    }

    public function ceil(): self
    {
        $result = $this->isNegative() ? 0 : 1;
        return new self(bcadd($this->value, $result, 0));
    }

    public function abs(): self
    {
        return new self(ltrim($this->value, "-"));
    }

    public function truncate(int $precision = 0): self
    {
        [$integerStr, $decimalStr] = explode(".", $this->toString());
        $valueStr = $integerStr;
        if ($decimalStr && $precision > 0) {
            $decimalStr = substr($decimalStr, 0, $precision);
            if ($decimalStr) {
                $valueStr .= '.' . $decimalStr;
            }
        }
        return new self($valueStr);
    }

    public function round(int $precision = 0): self
    {
        $decimalOffset = '0.' . str_repeat('0', $precision) . '5';
        return $this->isNegative()
            ? $this->sub($decimalOffset)->truncate($precision)
            : $this->add($decimalOffset)->truncate($precision);
    }

    // Comparison methods
    public function isEqual(string $arg): bool
    {
        return bccomp($this->value, $this->normalizeFloat($arg)) == 0;
    }

    public function isSmaller(string $arg): bool
    {
        return bccomp($this->value, $this->normalizeFloat($arg)) == -1;
    }

    public function isSmallerOrEqual(string $arg): bool
    {
        return $this->isSmaller($arg) || $this->isEqual($arg);
    }

    public function isGreater(string $arg): bool
    {
        return bccomp($this->value, $this->normalizeFloat($arg)) == 1;
    }

    public function isGreaterOrEqual(string $arg): bool
    {
        return $this->isGreater($arg) || $this->isEqual($arg);
    }

    public function isNegative(): bool
    {
        return $this->isSmaller(0);
    }

    public function isPositive(): bool
    {
        return $this->isGreater(0);
    }

    // Increment and decrement
    public function inc(): self
    {
        return $this->add(1);
    }

    public function dec(): self
    {
        return $this->sub(1);
    }

    // Formatting methods
    public function format(int $decimals = 0): string
    {
        $str = $this->isNegative() ? "-" : "";
        [$integerStr, $decimalStr] = explode(".", $this->round($decimals)->abs()->toString());
        $integerLength = strlen($integerStr);
        for ($i = 0; $i < $integerLength; $i++) {
            $str .= $integerStr[$i];
            $reverseI = $integerLength - $i - 1;
            if ($reverseI && $reverseI % 3 == 0) {
                $str .= ",";
            }
        }
        if ($decimals) {
            $str .= "." . str_pad($decimalStr, $decimals, "0");
        }
        return $str;
    }

    public function getHumanValue(): self
    {
        $base = (new self(10))->pow($this->getHumanUnitIndex());
        return $this->div($base);
    }

    public function getHumanUnit(): string
    {
        return self::HUMAN_UNITS[$this->getHumanUnitIndex()];
    }

    private function getHumanUnitIndex(): int
    {
        $integerLength = strlen($this->abs()->floor());
        foreach (self::HUMAN_UNITS as $index => $unit) {
            if ($integerLength - 1 <= $index) {
                return $index;
            }
        }
        return array_key_last(self::HUMAN_UNITS);
    }

    public function toString(): string
    {
        $valueSplit = explode(".", $this->value);
        $value = $valueSplit[0];
        if (isset($valueSplit[1])) {
            $value .= "." . rtrim($valueSplit[1], "0");
        }
        return rtrim($value, ".");
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    // Magic methods for basic arithmetic operations
    public function __add($delta) { return $this->add($delta); }
    public function __sub($delta) { return $this->sub($delta); }
    public function __mul($delta) { return $this->mul($delta); }
    public function __div($delta) { return $this->div($delta); }
    public function __mod($delta) { return $this->mod($delta); }
    public function __pow($delta) { return $this->pow($delta); }

    // Magic methods for comparisons
    public function __is_identical($arg) { return $this->isEqual($arg); }
    public function __is_smaller($arg) { return $this->isSmaller($arg); }
    public function __is_smaller_or_equal($arg) { return $this->isSmallerOrEqual($arg); }
    public function __is_greater($arg) { return $this->isGreater($arg); }
    public function __is_greater_or_equal($arg) { return $this->isGreaterOrEqual($arg); }

    // Magic methods for increment and decrement
    public function __pre_inc() { return $this->inc(); }
    public function __pre_dec() { return $this->dec(); }
    public function __post_inc() { $clone = $this->clone(); $this->__assign_add(1); return $clone; }
    public function __post_dec() { $clone = $this->clone(); $this->__assign_sub(1); return $clone; }

    // Magic methods for assignment operations
    public function __assign_add($delta) { $this->value = $this->add($delta)->value; }
    public function __assign_sub($delta) { $this->value = $this->sub($delta)->value; }
    public function __assign_mul($delta) { $this->value = $this->mul($delta)->value; }
    public function __assign_div($delta) { $this->value = $this->div($delta)->value; }
    public function __assign_mod($delta) { $this->value = $this->mod($delta)->value; }
    public function __assign_pow($delta) { $this->value = $this->pow($delta)->value; }
}
