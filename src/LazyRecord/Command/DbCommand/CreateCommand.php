<?php
namespace LazyRecord\Command\DbCommand;
use CLIFramework\Command;
use LazyRecord\Command\BaseCommand;
use LazyRecord\ConfigLoader;
use LazyRecord\Schema;
use LazyRecord\Utils;
use LazyRecord\ConnectionManager;
use Exception;

class CreateCommand extends BaseCommand
{

    public function brief() 
    {
        return 'create database bases on the current config.';
    }

    public function execute()
    {
        $dataSourceId = $this->getCurrentDataSourceId();
        $connectionManager = ConnectionManager::getInstance();
        $dataSource = $connectionManager->getDataSource($dataSourceId);
        var_dump($dataSource); 
    }
}



