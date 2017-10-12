
class Number
{
	protected $internalScale = 20;
    protected $iterations = 100;
	
	protected $value = 0;
	protected $humanUnits = [
		3 => 'K',
		6 => 'M', 
		9 => 'G', 
		12 => 'T', 
		15 => 'P', 
	'E'
	];
	
	public function __construct($value)
	{
		bcscale($this->internalScale);
		
		$value = (string) $value;
		$this->value = $this->normalizeFloat($value);
	}
	
	private function normalizeFloat($value)
	{
		$part = explode("E", $value);
		if (count($part) == 2) {
			$value = bcmul($part[0], bcpow(10, $part[1]));
		}
		return $value;
	}
	
	public function clone()
	{
		return new self($this->value);
	}
	
	public function add($delta)
	{
		$delta = $this->normalizeFloat($delta);
		$newValue = bcadd($this->value, $delta);
		return new self($newValue);
	}
	
	public function sub($delta)
	{
		$delta = $this->normalizeFloat($delta);
		$newValue = bcsub($this->value, $delta);
		return new self($newValue);
	}
	
	public function mul($delta)
	{
		$delta = $this->normalizeFloat($delta);
		$newValue = bcmul($this->value, $delta);
		return new self($newValue);
	}
	
	public function div($delta)
	{
		$delta = $this->normalizeFloat($delta);
		$newValue = bcdiv($this->value, $delta);
		return new self($newValue);
	}	
	
	public function mod($delta)
	{
		$delta = $this->normalizeFloat($delta);
		$newValue = bcmod($this->value, $delta);
		return new self($newValue);
	}	
	
	public function pow($delta)
	{
		$delta = $this->normalizeFloat($delta);
		$newValue = bcpow($this->value, $delta);
		return new self($newValue);
	}
	
	public function ln()
	{
		$retval = new self(0);
        for ($i = 0; $i < $this->iterations; $i++) {
			$pow = (new self(2))->mul($i)->add(1);
			$mul = (new self(1))->div($pow);
			$base = $this->sub(1)->div($this->add(1));
			$fraction = $base->pow($pow)->mul($mul);
			$retval = $retval->add($fraction);
        }
        return $retval->mul(2);
	}
		
    /**
     *  Gives the base 10 logarithm of the argument (uses ln $val/ln 10).
     *
     * @return string|integer|float
     */
	public function log($base)
	{
		$baseLn = (new self($base))->ln();
		return $this->ln()->div($baseLn);
	}
	
	
	public function floor()
	{
		$result = 0;
		if ($this->isNegative()) {
			$result = -1;
		}
		$newValue = bcadd($this->value, $result, 0);
		return new self($newValue);
	}
	
	public function ceil()
	{
		$result = 1;
		if ($this->isNegative()) {
			$result = 0;
		}
		$newValue = bcadd($this->value, $result, 0);
		return new self($newValue);
	}
	
	public function abs()
	{
		return new self(ltrim($this->value, "-"));
	}
	
	public function isEqual($arg)
	{
		$arg = $this->normalizeFloat($arg);
		return bccomp($this->value, $arg) == 0;
	}
	
	public function isSmaller($arg)
	{
		$arg = $this->normalizeFloat($arg);
		return bccomp($this->value, $arg) == -1;
	}
	
	public function isSmallerOrEqual($arg)
	{
		return $this->isSmaller($arg) || $this->isEqual($arg);
	}
	
	public function isGreater($arg)
	{
		$arg = $this->normalizeFloat($arg);
		return bccomp($this->value, $arg) == 1;
	}
	
	public function isGreaterOrEqual($arg)
	{
		return $this->isGreater($arg) || $this->isEqual($arg);
	}
	
	public function isNegative()
	{
		return $this->isSmaller(0);
	}
	
	public function isPositive()
	{
		return $this->isGreater(0);
	}
	
	public function inc()
	{
		return $this->add(1);
	}
	
	public function dec()
	{
		return $this->sub(1);
	}
	
	public function format($decimals = 0)
	{
		$str = "";
		if ($this->isNegative()) {
			$str = "-";
		}
		$valueSplit = explode(".", $this->abs()->toString());
		$integerLength = strlen($valueSplit[0]);
		for ($i=0; $i < $integerLength; $i++) {
			$str .= $valueSplit[0][$i];
			$reverseI = $integerLength - $i - 1;
			if ($reverseI && $reverseI % 3 == 0) {
				$str .= ",";
			}
		}
		if ($valueSplit[1]) {
			$str .= ".".$valueSplit[1];
		}
		return $str;
	}
	
	protected function getHumanUnitIndex()
	{
		$unitIndex = 0;
		$integerLength = strlen($this->abs()->floor());
		foreach ($this->humanUnits as $index => $unit) {
			if ($integerLength - 1 <= $index) {
				break;
			}
			$unitIndex = $index;
		}
		return $unitIndex;
	}
	
	public function getHumanValue()
	{
		$base = (new self(10))->pow($this->getHumanUnitIndex());
		return $this->div($base);
	}
	
	public function getHumanUnit()
	{
		return $this->humanUnits[$this->getHumanUnitIndex()];
	}
	
	public function toString()
	{
		$valueSplit = explode(".", $this->value);
		$value = $valueSplit[0];
		if ($valueSplit[1]) {
			$value .= ".".rtrim($valueSplit[1], "0");
		}
		return $value;
	}
	
	/* Overload Basic Operators */
	public function __add($delta)
	{
		return $this->add($delta);
	}
	
	public function __sub($delta)
	{
		return $this->sub($delta);
	}
	
	public function __mul($delta)
	{
		return $this->mul($delta);
	}
	
	public function __div($delta)
	{
		return $this->div($delta);
	}
	
	public function __mod($delta)
	{
		return $this->mod($delta);
	}
	
	public function __pow($delta)
	{
		return $this->pow($delta);
	}
	
	/* Overload Boolean Operators */
	public function __is_identical($arg)
	{
		return $this->isEqual($arg);
	}
	
	public function __is_smaller($arg)
	{
		return $this->isSmaller($arg);
	}
	
	public function __is_smaller_or_equal($arg)
	{
		return $this->isSmallerOrEqual($arg);
	}
	
	public function __is_greater($arg)
	{
		return $this->isGreater($arg);
	}
	
	public function __is_greater_or_equal($arg)
	{
		return $this->isGreaterOrEqual($arg);
	}
	
	/* Overload Inc && Dec */
	public function __pre_inc()
	{
		return $this->inc();
	}
	
	public function __pre_dec()
	{
		return $this->dec();
	}
	
	/* TODO: Don't know how to overload post_inc */
	public function __post_inc()
	{
		return $this->inc();
	}
	
	/* TODO: Don't know how to overload __post_dec */
	public function __post_dec()
	{
		return $this->dec();
	}
	
	/* Overloaed Assign Operators */
	public function __assign_add($delta)
	{
		$delta = $this->normalizeFloat($delta);
		$this->value = bcadd($this->value, $delta);
	}
	
	public function __assign_sub($delta)
	{
		$delta = $this->normalizeFloat($delta);
		$this->value = bcsub($this->value, $delta);
	}
	
	public function __assign_mul($delta)
	{
		$delta = $this->normalizeFloat($delta);
		$this->value = bcmul($this->value, $delta);
	}
	
	public function __assign_div($delta)
	{
		$delta = $this->normalizeFloat($delta);
		$this->value = bcdiv($this->value, $delta);
	}
	
	public function __assign_mod($delta)
	{
		$delta = $this->normalizeFloat($delta);
		$this->value = bcmod($this->value, $delta);
	}
	
	public function __assign_pow($delta)
	{
		$delta = $this->normalizeFloat($delta);
		$this->value = bcpow($this->value, $delta);
	}
	
	public function __toString()
	{
		return $this->toString();
	}
	
}
