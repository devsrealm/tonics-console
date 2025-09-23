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

        foreach ($registrars as $registrar){
            /**
             * @var $registrar ConsoleCommand
             */
            if ($registrar instanceof ConsoleCommand){
                $requiredPatterns = $registrar->required();
                $missing = ArgsHelper::require($commandArgs, $requiredPatterns);
                if (empty($missing)) {
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