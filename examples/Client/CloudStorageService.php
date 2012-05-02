<?php

/**
 * LICENSE: Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * PHP version 5
 *
 * @category  Microsoft
 * @package   Client
 * @author    Abdelrahman Elogeel <Abdelrahman.Elogeel@microsoft.com>
 * @copyright 2012 Microsoft Corporation
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link      http://pear.php.net/package/azure-sdk-for-php
 */
 
namespace Client;
use WindowsAzure\Core\Configuration;
use WindowsAzure\Services\Table\TableService;
use WindowsAzure\Services\Table\TableSettings;
use WindowsAzure\Services\Blob\BlobService;
use WindowsAzure\Services\Blob\BlobSettings;
use WindowsAzure\Services\Queue\QueueService;
use WindowsAzure\Services\Queue\QueueSettings;
use WindowsAzure\Services\Table\Models\QueryTablesOptions;

/**
 * Encapsulates Windows Azure storage service operations.
 *
 * @category  Microsoft
 * @package   Client
 * @author    Abdelrahman Elogeel <Abdelrahman.Elogeel@microsoft.com>
 * @copyright 2012 Microsoft Corporation
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/azure-sdk-for-php
 */
class CloudStorageService
{
    /**
     * @var IQueue
     */
    private $_queueProxy;
    
    /**
     * @var IBlob
     */
    private $_blobProxy;
    
    /**
     * @var ITable
     */
    private $_tableProxy;
    
    /**
     * Constructs CloudStorageService using the provided parameters.
     * 
     * @param string $name      The storage service name.
     * @param string $key       The storage service access key.
     * @param array  $endpoints The storage service endpoints.
     * @throws \InvalidArgumentException 
     */
    public function __construct($name, $key, $endpoints)
    {
        $queueUri = null;
        $blobUri  = null;
        $tableUri = null;
        
        foreach ($endpoints as $value) {
            if (substr_count($value, 'queue.core')) {
                $queueUri = $value;
            } else if (substr_count($value, 'table.core')) {
                $tableUri = $value;
            } else if (substr_count($value, 'blob.core')) {
                $blobUri = $value;
            } else {
                throw new \InvalidArgumentException(ErrorMessages::INVALID_ENDPOINT);
            }
        }
        
        $config = new Configuration();
        $config->setProperty(TableSettings::ACCOUNT_NAME, $name);
        $config->setProperty(TableSettings::ACCOUNT_KEY, $key);
        $config->setProperty(TableSettings::URI, $tableUri);
        $config->setProperty(BlobSettings::ACCOUNT_NAME, $name);
        $config->setProperty(BlobSettings::ACCOUNT_KEY, $key);
        $config->setProperty(BlobSettings::URI, $tableUri);
        $config->setProperty(QueueSettings::ACCOUNT_NAME, $name);
        $config->setProperty(QueueSettings::ACCOUNT_KEY, $key);
        $config->setProperty(QueueSettings::URI, $tableUri);
        
        $this->_tableProxy = TableService::create($config);
        $this->_blobProxy  = BlobService::create($config);
        $this->_queueProxy = QueueService::create($config);
    }
    
    /**
     * Checks if a given table name exists or not.
     * 
     * @param string $name The table name.
     * 
     * @return boolean 
     */
    public function tableExists($name)
    {
        $tables = $this->listTables();
        foreach ($tables as $tableName) {
            if ($tableName == $name) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Lists all tables in this storage service.
     * 
     * @return array
     */
    public function listTables()
    {
        $nextTableName = null;
        $tables        = array();
        
        do {
            $options = new QueryTablesOptions();
            $options->setNextTableName($nextTableName);
            $result        = $this->_tableProxy->queryTables();
            $nextTableName = $result->getNextTableName();
            $tables = array_merge($tables, $result->getTables());
        }while(!is_null($nextTableName));
        
        return $tables;
    }
    
    /**
     * Creates table if it does not exist.
     * 
     * @param string $name The table name.
     * 
     * @return CloudTable
     */
    public function createTable($name)
    {
        if (!$this->tableExists($name)) {
            $this->_tableProxy->createTable($name);
        }
        
        return new CloudTable($name, $this->_tableProxy);
    }
    
    /**
     * Deletes given table.
     * 
     * @param string $name The table name.
     * 
     * @return none
     */
    public function deleteTable($name)
    {
        if ($this->tableExists($name)) {
            $this->_tableProxy->deleteTable($name);
        }
    }
}

?>
