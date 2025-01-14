# Tonics Console

Tonics Console is a PHP-based command-line argument processing library. It allows you to easily parse and handle command-line arguments in your PHP applications.

## Installation

To install Tonics Console, you can use Composer:

```sh
composer require devsrealm/tonics-console
```

## Usage

### Basic Example

Here's a basic example of how to use the `ProcessCommandLineArgs` class to parse command-line arguments:

```php
<?php

require 'vendor/autoload.php';

use Devsrealm\TonicsConsole\ProcessCommandLineArgs;

$args = ['--name=John', '--age=30', '-v'];

$processArgs = new ProcessCommandLineArgs($args);

if ($processArgs->passes()) {
    $parsedArgs = $processArgs->getProcessArgs();
    print_r($parsedArgs);
} else {
    echo "No valid arguments passed.";
}
```

### Output

The above code will output:

```
Array
(
    [--name] => John
    [--age] => 30
    [-v] => 
)
```

Here is an additional example demonstrating how to use the `ProcessCommandLineArgs` class to handle different types of command-line arguments:

### Advanced Example

This example shows how to handle both required and optional arguments, as well as how to check if the arguments were passed correctly.

```php
<?php

require 'vendor/autoload.php';

use Devsrealm\TonicsConsole\ProcessCommandLineArgs;

$args = ['--name=John', '--age=30', '-v', '--email=john.doe@example.com'];

$processArgs = new ProcessCommandLineArgs($args);

if ($processArgs->passes()) {
    $parsedArgs = $processArgs->getProcessArgs();
    
    echo "Parsed Arguments:\n";
    print_r($parsedArgs);
    
    // Access individual arguments
    $name = $parsedArgs['--name'] ?? 'Unknown';
    $age = $parsedArgs['--age'] ?? 'Unknown';
    $email = $parsedArgs['--email'] ?? 'Unknown';
    $verbose = isset($parsedArgs['-v']);
    
    echo "\nDetails:\n";
    echo "Name: $name\n";
    echo "Age: $age\n";
    echo "Email: $email\n";
    echo "Verbose Mode: " . ($verbose ? 'Enabled' : 'Disabled') . "\n";
} else {
    echo "No valid arguments passed.";
}
```

### Output

The above code will output:

```
Parsed Arguments:
Array
(
    [--name] => John
    [--age] => 30
    [-v] =>
    [--email] => john.doe@example.com
)

Details:
Name: John
Age: 30
Email: john.doe@example.com
Verbose Mode: Enabled
```

This example demonstrates how to parse and access individual command-line arguments, including handling optional flags like `-v`.

## Class Details

### `ProcessCommandLineArgs`

This class is responsible for processing command-line arguments.

#### Constructor

```php
public function __construct(array $args)
```

- **$args**: An array of command-line arguments.

#### Methods

- **processArgs($args): array**

  Filters and processes the command-line arguments.

- **passes(): bool**

  Checks if there are any valid arguments passed.

- **getProcessArgs(): array**

  Returns the processed arguments.

## License

This project is licensed under the MIT License. See the `LICENSE` file for details.