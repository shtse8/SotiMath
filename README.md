# PHP-Number

PHP-Number is a powerful library for arbitrary precision mathematics in PHP. It provides a simple and intuitive way to perform high-precision calculations, leveraging the BCMath extension and optionally the PECL Operator extension for enhanced performance.

## Features

- Arbitrary precision arithmetic operations
- Support for scientific notation
- Seamless integration with PHP's native mathematical functions
- Optional operator overloading for intuitive syntax (requires PECL Operator)
- Comprehensive set of mathematical operations (addition, subtraction, multiplication, division, power, modulus, etc.)
- Formatting options for human-readable output

## Requirements

- PHP 7.2 or higher
- BCMath extension

### Optional (for operator overloading)
- [PECL Operator extension](https://github.com/php/pecl-php-operator)

## Installation

### Via Composer

```bash
composer require shtse8/Number
```

### Installing BCMath

On Debian/Ubuntu systems:

```bash
sudo apt install php7.2-bcmath
```

For other systems, please refer to the [PHP documentation](https://www.php.net/manual/en/book.bc.php) for installation instructions.

### Installing PECL Operator (Optional)

If you want to use operator overloading, you'll need to install the PECL Operator extension:

```bash
git clone https://github.com/php/pecl-php-operator
cd pecl-php-operator
phpize
./configure
make && sudo make install
```

Then, enable the extension:

```bash
echo "extension=operator.so" | sudo tee /etc/php/7.2/mods-available/operator.ini
sudo ln -s /etc/php/7.2/mods-available/operator.ini /etc/php/7.2/cli/conf.d/20-operator.ini
sudo ln -s /etc/php/7.2/mods-available/operator.ini /etc/php/7.2/fpm/conf.d/20-operator.ini
sudo service php7.2-fpm reload
```

Note: Adjust the PHP version in the paths if you're using a different version.

## Usage

### Basic Usage

```php
use shtse8\Number;

$num1 = new Number("1.8573958822565E+17");
$num2 = new Number("111");

$result = $num1->add($num2)->pow($num2);
echo $result->toString();
```

### With Operator Overloading (requires PECL Operator)

```php
use shtse8\Number;

$num1 = new Number("1.8573958822565E+17");
$num2 = new Number("111");

$result = $num1 + $num2;
$result **= $num2;
echo $result;
```

## Available Methods

- `add($number)`: Addition
- `sub($number)`: Subtraction
- `mul($number)`: Multiplication
- `div($number)`: Division
- `mod($number)`: Modulus
- `pow($number)`: Power
- `abs()`: Absolute value
- `floor()`: Round down
- `ceil()`: Round up
- `round($precision)`: Round to specified precision
- `isEqual($number)`: Equality comparison
- `isSmaller($number)`: Less than comparison
- `isGreater($number)`: Greater than comparison
- `format($decimals)`: Format number with thousands separators and specified decimal places

For a complete list of methods and their usage, please refer to the [API documentation](link-to-api-docs).

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Acknowledgments

- Thanks to the PHP team for the BCMath extension
- Thanks to the PECL Operator team for enabling operator overloading in PHP
