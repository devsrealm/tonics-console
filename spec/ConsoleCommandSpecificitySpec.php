<?php

use Devsrealm\TonicsConsole\CommandRegistrar;
use Devsrealm\TonicsConsole\Console;
use Devsrealm\TonicsConsole\ProcessCommandLineArgs;
use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;

describe("Console Command Specificity", function() {

    beforeEach(function() {
        $this->executedCommand = null;
    });

    it("should match the most specific command when multiple commands have overlapping requirements", function() {

        // Create a generic command
        $genericCommand = new class($this) implements ConsoleCommand {
            private $testContext;

            public function __construct($context) {
                $this->testContext = $context;
            }

            public function required(): array {
                return ['--migrate:all'];
            }

            public function run(array $commandOptions): void {
                $this->testContext->executedCommand = 'MigrateAll';
            }
        };

        // Create a more specific command
        $specificCommand = new class($this) implements ConsoleCommand {
            private $testContext;

            public function __construct($context) {
                $this->testContext = $context;
            }

            public function required(): array {
                return ['--migrate:all', '--fresh'];
            }

            public function run(array $commandOptions): void {
                $this->testContext->executedCommand = 'MigrateAllFresh';
            }
        };

        // Register generic command first (order should not matter)
        $registrar = new CommandRegistrar([$genericCommand, $specificCommand]);

        $args = ['--migrate:all', '--fresh'];
        $processedArgs = (new ProcessCommandLineArgs($args))->getProcessArgs();

        $console = new Console($registrar, $processedArgs, null);
        $console->bootConsole();

        // Should execute the more specific command
        expect($this->executedCommand)->toBe('MigrateAllFresh');
    });

    it("should match the generic command when only generic arguments are provided", function() {

        // Create a generic command
        $genericCommand = new class($this) implements ConsoleCommand {
            private $testContext;

            public function __construct($context) {
                $this->testContext = $context;
            }

            public function required(): array {
                return ['--migrate:all'];
            }

            public function run(array $commandOptions): void {
                $this->testContext->executedCommand = 'MigrateAll';
            }
        };

        // Create a more specific command
        $specificCommand = new class($this) implements ConsoleCommand {
            private $testContext;

            public function __construct($context) {
                $this->testContext = $context;
            }

            public function required(): array {
                return ['--migrate:all', '--fresh'];
            }

            public function run(array $commandOptions): void {
                $this->testContext->executedCommand = 'MigrateAllFresh';
            }
        };

        $registrar = new CommandRegistrar([$genericCommand, $specificCommand]);

        $args = ['--migrate:all'];
        $processedArgs = (new ProcessCommandLineArgs($args))->getProcessArgs();

        $console = new Console($registrar, $processedArgs, null);
        $console->bootConsole();

        // Should execute the generic command
        expect($this->executedCommand)->toBe('MigrateAll');
    });

    it("should prioritize specificity regardless of registration order", function() {

        // Create a generic command
        $genericCommand = new class($this) implements ConsoleCommand {
            private $testContext;

            public function __construct($context) {
                $this->testContext = $context;
            }

            public function required(): array {
                return ['--env*'];
            }

            public function run(array $commandOptions): void {
                $this->testContext->executedCommand = 'EnvGeneric';
            }
        };

        // Create a more specific command with wildcard
        $specificCommand = new class($this) implements ConsoleCommand {
            private $testContext;

            public function __construct($context) {
                $this->testContext = $context;
            }

            public function required(): array {
                return ['--env*', '--file'];
            }

            public function run(array $commandOptions): void {
                $this->testContext->executedCommand = 'EnvWithFile';
            }
        };

        // Register in reverse order (specific first, then generic)
        $registrar = new CommandRegistrar([$specificCommand, $genericCommand]);

        $args = ['--env:manage', '--file=.env.local'];
        $processedArgs = (new ProcessCommandLineArgs($args))->getProcessArgs();

        $console = new Console($registrar, $processedArgs, null);
        $console->bootConsole();

        // Should still execute the more specific command
        expect($this->executedCommand)->toBe('EnvWithFile');
    });

    it("should handle three-tier specificity correctly", function() {

        // Most generic
        $command1 = new class($this) implements ConsoleCommand {
            private $testContext;

            public function __construct($context) {
                $this->testContext = $context;
            }

            public function required(): array {
                return ['--migrate:all'];
            }

            public function run(array $commandOptions): void {
                $this->testContext->executedCommand = 'Tier1';
            }
        };

        // Medium specificity
        $command2 = new class($this) implements ConsoleCommand {
            private $testContext;

            public function __construct($context) {
                $this->testContext = $context;
            }

            public function required(): array {
                return ['--migrate:all', '--fresh'];
            }

            public function run(array $commandOptions): void {
                $this->testContext->executedCommand = 'Tier2';
            }
        };

        // Most specific
        $command3 = new class($this) implements ConsoleCommand {
            private $testContext;

            public function __construct($context) {
                $this->testContext = $context;
            }

            public function required(): array {
                return ['--migrate:all', '--fresh', '--seed'];
            }

            public function run(array $commandOptions): void {
                $this->testContext->executedCommand = 'Tier3';
            }
        };

        // Register in random order
        $registrar = new CommandRegistrar([$command2, $command1, $command3]);

        $args = ['--migrate:all', '--fresh', '--seed'];
        $processedArgs = (new ProcessCommandLineArgs($args))->getProcessArgs();

        $console = new Console($registrar, $processedArgs, null);
        $console->bootConsole();

        // Should execute the most specific command
        expect($this->executedCommand)->toBe('Tier3');
    });

    it("should match correct command when same count but different requirements (--fresh variant)", function() {

        // Generic command
        $genericCommand = new class($this) implements ConsoleCommand {
            private $testContext;

            public function __construct($context) {
                $this->testContext = $context;
            }

            public function required(): array {
                return ['--migrate:all'];
            }

            public function run(array $commandOptions): void {
                $this->testContext->executedCommand = 'MigrateAll';
            }
        };

        // Specific command with --fresh
        $freshCommand = new class($this) implements ConsoleCommand {
            private $testContext;

            public function __construct($context) {
                $this->testContext = $context;
            }

            public function required(): array {
                return ['--migrate:all', '--fresh'];
            }

            public function run(array $commandOptions): void {
                $this->testContext->executedCommand = 'MigrateAllFresh';
            }
        };

        // Specific command with --rest (same count as fresh)
        $restCommand = new class($this) implements ConsoleCommand {
            private $testContext;

            public function __construct($context) {
                $this->testContext = $context;
            }

            public function required(): array {
                return ['--migrate:all', '--rest'];
            }

            public function run(array $commandOptions): void {
                $this->testContext->executedCommand = 'MigrateAllRest';
            }
        };

        $registrar = new CommandRegistrar([$genericCommand, $freshCommand, $restCommand]);

        $args = ['--migrate:all', '--fresh'];
        $processedArgs = (new ProcessCommandLineArgs($args))->getProcessArgs();

        $console = new Console($registrar, $processedArgs, null);
        $console->bootConsole();

        // Should execute the fresh variant
        expect($this->executedCommand)->toBe('MigrateAllFresh');
    });

    it("should match correct command when same count but different requirements (--rest variant)", function() {

        // Generic command
        $genericCommand = new class($this) implements ConsoleCommand {
            private $testContext;

            public function __construct($context) {
                $this->testContext = $context;
            }

            public function required(): array {
                return ['--migrate:all'];
            }

            public function run(array $commandOptions): void {
                $this->testContext->executedCommand = 'MigrateAll';
            }
        };

        // Specific command with --fresh
        $freshCommand = new class($this) implements ConsoleCommand {
            private $testContext;

            public function __construct($context) {
                $this->testContext = $context;
            }

            public function required(): array {
                return ['--migrate:all', '--fresh'];
            }

            public function run(array $commandOptions): void {
                $this->testContext->executedCommand = 'MigrateAllFresh';
            }
        };

        // Specific command with --rest (same count as fresh)
        $restCommand = new class($this) implements ConsoleCommand {
            private $testContext;

            public function __construct($context) {
                $this->testContext = $context;
            }

            public function required(): array {
                return ['--migrate:all', '--rest'];
            }

            public function run(array $commandOptions): void {
                $this->testContext->executedCommand = 'MigrateAllRest';
            }
        };

        $registrar = new CommandRegistrar([$genericCommand, $freshCommand, $restCommand]);

        $args = ['--migrate:all', '--rest'];
        $processedArgs = (new ProcessCommandLineArgs($args))->getProcessArgs();

        $console = new Console($registrar, $processedArgs, null);
        $console->bootConsole();

        // Should execute the rest variant
        expect($this->executedCommand)->toBe('MigrateAllRest');
    });

    it("should not match when required arguments are missing (same count different requirements)", function() {

        // Generic command
        $genericCommand = new class($this) implements ConsoleCommand {
            private $testContext;

            public function __construct($context) {
                $this->testContext = $context;
            }

            public function required(): array {
                return ['--migrate:all'];
            }

            public function run(array $commandOptions): void {
                $this->testContext->executedCommand = 'MigrateAll';
            }
        };

        // Specific command with --fresh
        $freshCommand = new class($this) implements ConsoleCommand {
            private $testContext;

            public function __construct($context) {
                $this->testContext = $context;
            }

            public function required(): array {
                return ['--migrate:all', '--fresh'];
            }

            public function run(array $commandOptions): void {
                $this->testContext->executedCommand = 'MigrateAllFresh';
            }
        };

        // Specific command with --rest (same count as fresh)
        $restCommand = new class($this) implements ConsoleCommand {
            private $testContext;

            public function __construct($context) {
                $this->testContext = $context;
            }

            public function required(): array {
                return ['--migrate:all', '--rest'];
            }

            public function run(array $commandOptions): void {
                $this->testContext->executedCommand = 'MigrateAllRest';
            }
        };

        $registrar = new CommandRegistrar([$genericCommand, $freshCommand, $restCommand]);

        // Providing an argument that doesn't match any of the specific commands
        $args = ['--migrate:all', '--unknown'];
        $processedArgs = (new ProcessCommandLineArgs($args))->getProcessArgs();

        $console = new Console($registrar, $processedArgs, null);
        $console->bootConsole();

        // Should fall back to generic command
        expect($this->executedCommand)->toBe('MigrateAll');
    });

    it("should handle multiple same-count commands with wildcards", function() {

        // Command with wildcard and --file
        $envFileCommand = new class($this) implements ConsoleCommand {
            private $testContext;

            public function __construct($context) {
                $this->testContext = $context;
            }

            public function required(): array {
                return ['--env*', '--file'];
            }

            public function run(array $commandOptions): void {
                $this->testContext->executedCommand = 'EnvWithFile';
            }
        };

        // Command with wildcard and --set (same count as --file)
        $envSetCommand = new class($this) implements ConsoleCommand {
            private $testContext;

            public function __construct($context) {
                $this->testContext = $context;
            }

            public function required(): array {
                return ['--env*', '--set'];
            }

            public function run(array $commandOptions): void {
                $this->testContext->executedCommand = 'EnvWithSet';
            }
        };

        $registrar = new CommandRegistrar([$envFileCommand, $envSetCommand]);

        $args = ['--env:manage', '--set=DB_HOST=localhost'];
        $processedArgs = (new ProcessCommandLineArgs($args))->getProcessArgs();

        $console = new Console($registrar, $processedArgs, null);
        $console->bootConsole();

        // Should execute the set variant
        expect($this->executedCommand)->toBe('EnvWithSet');
    });

    it("should have deterministic sorting when multiple commands have same count", function() {

        // Create multiple commands with same count but different requirements
        // We'll register them in various orders and ensure consistent results
        $command1 = new class($this) implements ConsoleCommand {
            private $testContext;

            public function __construct($context) {
                $this->testContext = $context;
            }

            public function required(): array {
                return ['--migrate:all', '--alpha'];
            }

            public function run(array $commandOptions): void {
                $this->testContext->executedCommand = 'Alpha';
            }
        };

        $command2 = new class($this) implements ConsoleCommand {
            private $testContext;

            public function __construct($context) {
                $this->testContext = $context;
            }

            public function required(): array {
                return ['--migrate:all', '--beta'];
            }

            public function run(array $commandOptions): void {
                $this->testContext->executedCommand = 'Beta';
            }
        };

        $command3 = new class($this) implements ConsoleCommand {
            private $testContext;

            public function __construct($context) {
                $this->testContext = $context;
            }

            public function required(): array {
                return ['--migrate:all', '--gamma'];
            }

            public function run(array $commandOptions): void {
                $this->testContext->executedCommand = 'Gamma';
            }
        };

        // Test 1: Register in order [1, 2, 3] and provide --beta
        $registrar1 = new CommandRegistrar([$command1, $command2, $command3]);
        $args1 = ['--migrate:all', '--beta'];
        $processedArgs1 = (new ProcessCommandLineArgs($args1))->getProcessArgs();
        $console1 = new Console($registrar1, $processedArgs1, null);
        $console1->bootConsole();
        $result1 = $this->executedCommand;

        // Reset
        $this->executedCommand = null;

        // Test 2: Register in different order [3, 1, 2] and provide same --beta
        $registrar2 = new CommandRegistrar([$command3, $command1, $command2]);
        $args2 = ['--migrate:all', '--beta'];
        $processedArgs2 = (new ProcessCommandLineArgs($args2))->getProcessArgs();
        $console2 = new Console($registrar2, $processedArgs2, null);
        $console2->bootConsole();
        $result2 = $this->executedCommand;

        // Both should produce the same result (Beta)
        expect($result1)->toBe('Beta');
        expect($result2)->toBe('Beta');
        expect($result1)->toBe($result2);
    });

    it("should consistently match the correct command across different registration orders", function() {

        // Create commands with same count
        $freshCommand = new class($this) implements ConsoleCommand {
            private $testContext;

            public function __construct($context) {
                $this->testContext = $context;
            }

            public function required(): array {
                return ['--migrate:all', '--fresh'];
            }

            public function run(array $commandOptions): void {
                $this->testContext->executedCommand = 'Fresh';
            }
        };

        $restCommand = new class($this) implements ConsoleCommand {
            private $testContext;

            public function __construct($context) {
                $this->testContext = $context;
            }

            public function required(): array {
                return ['--migrate:all', '--rest'];
            }

            public function run(array $commandOptions): void {
                $this->testContext->executedCommand = 'Rest';
            }
        };

        $seedCommand = new class($this) implements ConsoleCommand {
            private $testContext;

            public function __construct($context) {
                $this->testContext = $context;
            }

            public function required(): array {
                return ['--migrate:all', '--seed'];
            }

            public function run(array $commandOptions): void {
                $this->testContext->executedCommand = 'Seed';
            }
        };

        // Test multiple permutations of registration order
        $orders = [
            [$freshCommand, $restCommand, $seedCommand],
            [$seedCommand, $freshCommand, $restCommand],
            [$restCommand, $seedCommand, $freshCommand],
        ];

        foreach ($orders as $order) {
            // Reset
            $this->executedCommand = null;

            $registrar = new CommandRegistrar($order);
            $args = ['--migrate:all', '--fresh'];
            $processedArgs = (new ProcessCommandLineArgs($args))->getProcessArgs();
            $console = new Console($registrar, $processedArgs, null);
            $console->bootConsole();

            // Should always execute Fresh, regardless of registration order
            expect($this->executedCommand)->toBe('Fresh');
        }
    });

});

