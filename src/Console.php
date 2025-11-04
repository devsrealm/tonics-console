<?php

namespace Devsrealm\TonicsConsole;

use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;
use Devsrealm\TonicsConsole\Helpers\ArgsHelper;

class Console
{

    private CommandRegistrar $commandRegistrar;
    private array $processedArgs;
    private $container;

    public function __construct(CommandRegistrar $commandRegistrar, array $processedArgs, $container){
        $this->commandRegistrar = $commandRegistrar;
        $this->processedArgs = $processedArgs;
        $this->container = $container;
    }

    public function bootConsole()
    {
        $registrars = array_values($this->getCommandRegistrar()->getList());
        $commandArgs = $this->getProcessedArgs();

        // Find all matching commands
        $matches = [];
        foreach ($registrars as $registrar) {
            if ($registrar instanceof ConsoleCommand) {
                $requiredPatterns = $registrar->required();
                $missing = ArgsHelper::require($commandArgs, $requiredPatterns);
                if (empty($missing)) {
                    $matches[] = [
                        'command' => $registrar,
                        'required' => $requiredPatterns,
                        'count' => count($requiredPatterns)
                    ];
                }
            }
        }

        if (empty($matches)) {
            // No matching commands found, silently exit
            return;
        }

        // Sort matches by specificity (most required arguments first)
        // For commands with the same count, we use a deterministic secondary sort
        // by comparing JSON-encoded requirements to ensure stable sorting behavior
        usort($matches, function($a, $b) {
            $countCompare = $b['count'] <=> $a['count'];
            if ($countCompare !== 0) {
                return $countCompare;
            }
            // Secondary sort: use JSON encoding for deterministic ordering
            return json_encode($a['required']) <=> json_encode($b['required']);
        });

        // Run the most specific matching command
        $matches[0]['command']->run($commandArgs);
    }

    /**
     * @return array
     */
    public function getProcessedArgs(): array
    {
        return $this->processedArgs;
    }

    /**
     * @return CommandRegistrar
     */
    public function getCommandRegistrar(): CommandRegistrar
    {
        return $this->commandRegistrar;
    }

    /**
     * @param CommandRegistrar $commandRegistrar
     */
    public function setCommandRegistrar(CommandRegistrar $commandRegistrar): void
    {
        $this->commandRegistrar = $commandRegistrar;
    }


    public function getContainer()
    {
        return $this->container;
    }

}