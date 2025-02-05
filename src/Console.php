<?php

namespace Devsrealm\TonicsConsole;

use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;

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
        $requiredArgs =  array_filter($commandArgs, function ($key, $value) {
            return str_starts_with($value, "--");
        }, ARRAY_FILTER_USE_BOTH);

        foreach ($registrars as $registrar){
            /**
             * @var $registrar ConsoleCommand
             */
            if ($registrar instanceof ConsoleCommand){
                // if the commandargs conformed with the $registrar required...
                if ($registrar->required() === array_keys($requiredArgs)) {
                    $registrar->run($commandArgs);
                    break;
                }
            }
        }
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