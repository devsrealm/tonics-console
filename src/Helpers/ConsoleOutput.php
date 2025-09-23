<?php

namespace Devsrealm\TonicsConsole\Helpers;

/**
 * ConsoleOutput provides ANSI-colored console output with timestamps and context prefixes.
 * Supports both instance methods and static methods for convenience.
 */
class ConsoleOutput
{
    /** @var resource */
    private $out;
    /** @var resource */
    private $err;

    private static bool $initialized = false;
    private static array $fgColors = [];
    private static array $bgColors = [];
    private static string $appName = 'TonicsConsole';

    public function __construct($out = null, $err = null)
    {
        $this->out = $out ?? \STDOUT;
        $this->err = $err ?? \STDERR;
        self::initShellColors();
    }

    // ===================== Instance Methods =====================

    /**
     * @param string $fgColor
     * @param string $bgColor
     * @param string $message
     * @return string
     */
    public function coloredText(string $fgColor = 'black', string $bgColor = 'yellow', string $message = ''): string
    {
        self::initShellColors();
        $shortClassName = '[' . static::getAppName() . '] ' . '[' . $this->getObjectShortClassName($this) . ']:';
        if (key_exists($fgColor, self::$fgColors) && key_exists($bgColor, self::$bgColors)) {
            $fgColor = self::$fgColors[$fgColor];
            $bgColor = self::$bgColors[$bgColor];
            $date = date("Y-m-d H:i:s");
            $message = "$date $shortClassName \e[{$fgColor};{$bgColor}m{$message}\e[0m\n";
        }
        return $message;
    }

    /**
     * @param $message
     */
    public function successMessage($message): void
    {
        if (self::isCLI()) {
            fwrite($this->out, $this->coloredText("black", "green", "$message ✔"));
        }
    }

    /**
     * @param $message
     */
    public function errorMessage($message): void
    {
        if (self::isCLI()) {
            fwrite($this->err, $this->coloredText("white", "red", "$message ❌"));
        }
    }

    /**
     * @param $message
     */
    public function infoMessage($message): void
    {
        if (self::isCLI()) {
            fwrite($this->out, $this->coloredText("black", "light_gray", "$message !"));
        }
    }

    /**
     * @param $message
     */
    public function delayMessage($message): void
    {
        if (self::isCLI()) {
            fwrite($this->out, $this->coloredText("black", "yellow", "$message..."));
        }
    }

    // Backward-compatible aliases
    public function info(string $message): void { $this->infoMessage($message); }
    public function success(string $message): void { $this->successMessage($message); }
    public function warning(string $message): void {
        if (self::isCLI()) {
            fwrite($this->out, $this->coloredText("black", "yellow", $message));
        }
    }
    public function error(string $message): void { $this->errorMessage($message); }
    public function title(string $message): void {
        if (self::isCLI()) {
            fwrite($this->out, $this->coloredText("white", "black", $message));
        }
    }
    public function section(string $message): void {
        if (self::isCLI()) {
            fwrite($this->out, $this->coloredText("black", "light_gray", $message));
        }
    }
    public function comment(string $message): void {
        if (self::isCLI()) {
            fwrite($this->out, $this->coloredText("dark_gray", "black", $message));
        }
    }

    // ===================== Plain Writers =====================
    public function line(string $message = ''): void
    {
        fwrite($this->out, $message . PHP_EOL);
    }

    public function write(string $message): void
    {
        fwrite($this->out, $message);
    }

    /**
     * Render a beautiful table with borders and proper spacing, wrapping text to fit terminal width.
     */
    public function table(array $rows, array $headers = []): void
    {
        $allRows = $headers ? [$headers, ...$rows] : $rows;
        if (!$allRows) {
            return;
        }

        $terminalWidth = self::getTerminalWidth();
        $numColumns = count($allRows[0]);
        $borderWidth = $numColumns + 1;

        // Calculate initial widths
        $widths = array_fill(0, $numColumns, 0);
        foreach ($allRows as $row) {
            foreach (array_values($row) as $i => $cell) {
                $widths[$i] = max($widths[$i], mb_strlen((string)$cell));
            }
        }

        // Adjust widths to fit terminal
        $totalWidth = array_sum($widths) + ($numColumns * 3) + 1; // padding + borders
        $availableWidth = $terminalWidth - $borderWidth - ($numColumns * 2); // padding

        if ($totalWidth > $terminalWidth) {
            $widths = $this->adjustWidths($widths, $availableWidth);
        }

        $this->drawTableLine($widths, 'top');

        if ($headers) {
            $this->drawTableRow($headers, $widths);
            $this->drawTableLine($widths, 'middle');
        }

        foreach ($rows as $i => $row) {
            $this->drawTableRow($row, $widths);
            if ($i < count($rows) - 1) {
                $this->drawTableLine($widths, 'middle');
            }
        }

        $this->drawTableLine($widths, 'bottom');
    }

    private function adjustWidths(array $widths, int $availableWidth): array
    {
        $totalWidth = array_sum($widths);
        $newWidths = [];
        foreach ($widths as $width) {
            $newWidths[] = floor($width / $totalWidth * $availableWidth);
        }
        // Distribute remainder
        $remainder = $availableWidth - array_sum($newWidths);
        for ($i = 0; $i < $remainder; $i++) {
            $newWidths[$i % count($newWidths)]++;
        }
        return $newWidths;
    }

    private function drawTableRow(array $rowData, array $widths): void
    {
        $wrappedRows = [];
        $maxLines = 0;
        foreach (array_values($rowData) as $i => $cell) {
            $wrapped = wordwrap((string)$cell, $widths[$i], "\n", true);
            $lines = explode("\n", $wrapped);
            $wrappedRows[] = $lines;
            $maxLines = max($maxLines, count($lines));
        }

        for ($l = 0; $l < $maxLines; $l++) {
            $line = '│';
            foreach ($wrappedRows as $i => $lines) {
                $cellLine = $lines[$l] ?? '';
                $line .= ' ' . str_pad($cellLine, $widths[$i]) . ' │';
            }
            $this->line($line);
        }
    }

    private function drawTableLine(array $widths, string $position): void
    {
        $left = '├';
        $middle = '┼';
        $right = '┤';
        if ($position === 'top') {
            $left = '┌';
            $middle = '┬';
            $right = '┐';
        } elseif ($position === 'bottom') {
            $left = '└';
            $middle = '┴';
            $right = '┘';
        }

        $line = $left;
        foreach ($widths as $i => $width) {
            $line .= str_repeat('─', $width + 2);
            if ($i < count($widths) - 1) {
                $line .= $middle;
            }
        }
        $line .= $right;
        $this->line($line);
    }

    /**
     * Render bullet list with better formatting
     */
    public function bulletList(array $items, string $bullet = '•'): void
    {
        foreach ($items as $item) {
            $this->line("  {$bullet} " . (string)$item);
        }
    }

    // ===================== Static Methods =====================

    /**
     * @param string $fgColor
     * @param string $bgColor
     * @param string $message
     * @param string|null $contextClass
     * @return string
     */
    public static function coloredTextStatic(string $fgColor = 'black', string $bgColor = 'yellow', string $message = '', ?string $contextClass = null): string
    {
        self::initShellColors();
        $context = $contextClass ?: 'ConsoleOutput';
        $shortClassName = '[' . self::getAppName() . '] ' . '[' . $context . ']:';
        if (key_exists($fgColor, self::$fgColors) && key_exists($bgColor, self::$bgColors)) {
            $fgColor = self::$fgColors[$fgColor];
            $bgColor = self::$bgColors[$bgColor];
            $date = date("Y-m-d H:i:s");
            $message = "$date $shortClassName \e[{$fgColor};{$bgColor}m{$message}\e[0m\n";
        }
        return $message;
    }

    /**
     * @param string $message
     * @param string|null $contextClass
     */
    public static function successMessageStatic(string $message, ?string $contextClass = null): void
    {
        if (self::isCLI()) {
            echo self::coloredTextStatic("black", "green", "$message ✔", $contextClass);
        }
    }

    /**
     * @param string $message
     * @param string|null $contextClass
     */
    public static function errorMessageStatic(string $message, ?string $contextClass = null): void
    {
        if (self::isCLI()) {
            fwrite(\STDERR, self::coloredTextStatic("white", "red", "$message ❌", $contextClass));
        }
    }

    /**
     * @param string $message
     * @param string|null $contextClass
     */
    public static function infoMessageStatic(string $message, ?string $contextClass = null): void
    {
        if (self::isCLI()) {
            echo self::coloredTextStatic("black", "light_gray", "$message !", $contextClass);
        }
    }

    /**
     * @param string $message
     * @param string|null $contextClass
     */
    public static function delayMessageStatic(string $message, ?string $contextClass = null): void
    {
        if (self::isCLI()) {
            echo self::coloredTextStatic("black", "yellow", "$message...", $contextClass);
        }
    }

    // ===================== Utility Methods =====================

    public static function setAppName(string $name): void
    {
        self::$appName = $name;
    }

    public static function getAppName(): string
    {
        return self::$appName;
    }

    public static function isCLI(): bool
    {
        return PHP_SAPI === 'cli' || defined('STDOUT');
    }

    private static function getTerminalWidth(): int
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $output = [];
            @exec('mode con', $output);
            foreach ($output as $line) {
                if (str_contains($line, 'Columns')) {
                    return (int)trim(substr($line, strpos($line, ':') + 1));
                }
            }
        } else {
            $width = (int)@shell_exec('tput cols');
            if ($width > 0) {
                return $width;
            }
        }
        return 80; // Default width
    }

    private function getObjectShortClassName(object $o): string
    {
        $fq = get_class($o);
        $pos = strrpos($fq, '\\');
        return $pos !== false ? substr($fq, $pos + 1) : $fq;
    }

    private static function initShellColors(): void
    {
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;

        // Foreground colors (30-37 standard, 90-97 bright)
        self::$fgColors = [
            'black' => 30,
            'red' => 31,
            'green' => 32,
            'yellow' => 33,
            'blue' => 34,
            'magenta' => 35,
            'cyan' => 36,
            'light_gray' => 37,
            'dark_gray' => 90,
            'light_red' => 91,
            'light_green' => 92,
            'light_yellow' => 93,
            'light_blue' => 94,
            'light_magenta' => 95,
            'light_cyan' => 96,
            'white' => 97,
        ];

        // Background colors (40-47 standard, 100-107 bright)
        self::$bgColors = [
            'black' => 40,
            'red' => 41,
            'green' => 42,
            'yellow' => 43,
            'blue' => 44,
            'magenta' => 45,
            'cyan' => 46,
            'light_gray' => 47,
            'dark_gray' => 100,
            'light_red' => 101,
            'light_green' => 102,
            'light_yellow' => 103,
            'light_blue' => 104,
            'light_magenta' => 105,
            'light_cyan' => 106,
            'white' => 107,
        ];
    }
}
