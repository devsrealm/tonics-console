<?php

namespace Devsrealm\TonicsConsole;

class CommandRegistrar
{

    private array $List;

    public function __construct(array $list){
        if ($list){
            $this->setList($list);
        }
    }

    /**
     * @return array
     */
    public function getList(): array
    {
        return $this->List;
    }

    /**
     * @param array $List
     */
    public function setList(array $List): void
    {
        $this->List = $List;
    }

    /**
     * @param array $List
     * Should be in the format:
     * <code>
     * [
     * 'Command::class',
     * ]
     * </code>
     */
    public function register(array $List){
        $mimes = array_merge($this->getList(), $List);
        $this->setList($mimes);
    }

}