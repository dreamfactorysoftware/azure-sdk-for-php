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
 * @author    Azure PHP SDK <azurephpsdk@microsoft.com>
 * @copyright 2012 Microsoft Corporation
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link      https://github.com/windowsazure/azure-sdk-for-php
 */

namespace Client;
use WindowsAzure\Table\Models\Entity;
use WindowsAzure\Common\Internal\Utilities;

/**
 * The cloud table class.
 *
 * @category  Microsoft
 * @package   Client
 * @author    Azure PHP SDK <azurephpsdk@microsoft.com>
 * @copyright 2012 Microsoft Corporation
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @version   Release: 0.4.0_2014-01
 * @link      https://github.com/windowsazure/azure-sdk-for-php
 */
class CloudTable
{
    /**
     * @var TableRestProxy
     */
    private $_proxy;

    /**
     * @var string
     */
    private $_name;

    /**
     * @var string
     */
    private $_defaultParitionKey;

    /**
     * Initializes new CloudTable object using the provided parameters.
	 *
	 * Sets default partition key by default using uniqid() function.
     *
     * @param string                             $name  The table name.
     * @param \WindowsAzure\Table\Internal\ITable $proxy The table REST proxy.
     */
    public function __construct($name, $proxy)
    {
        $this->_name  			   = $name;
        $this->_proxy 			   = $proxy;
        $this->_defaultParitionKey = uniqid();
    }

    /**
     * Sets the default partition key for this table entities.
     *
     * @param string $paritionKey The default partition key.
     *
     * @return void
     */
    public function setDefaultPartitionKey($paritionKey)
    {
        $this->_defaultParitionKey = $paritionKey;
    }

    /**
     * Gets the default partition key.
     *
     * @return string
     */
    public function getDefaultPartitionKey()
    {
        return $this->_defaultParitionKey;
    }

    /**
     * Quries entities for the given table name.
     *
     * @param string               $table   The name of the table.
     * @param QueryEntitiesOptions $options The optional parameters.
     *
     * @return QueryEntitiesResult
     *
     * @see http://msdn.microsoft.com/en-us/library/windowsazure/dd179421.aspx
     */
    public function queryEntities($options = null)
    {
        return $this->_proxy->queryEntities($this->_name, $options);
    }

    /**
     * Inserts new entity to the table.
     *
     * @param string                     $entries The entries values.
     * @param Models\TableServiceOptions $options The optional parameters.
     *
     * @return void
     *
     * @see http://msdn.microsoft.com/en-us/library/windowsazure/dd179433.aspx
     */
    public function insertTypelessEntity($entries, $options = null)
    {
        $entity = new Entity();
        $entity->setPartitionKey($this->_defaultParitionKey);
        $entity->setRowKey(uniqid());

        foreach ($entries as $columnName => $value) {
            $entity->addProperty($columnName, null, $value);
        }

        $this->_proxy->insertEntity($this->_name, $entity, $options);
    }

    /**
     * Deletes an entity using the default partition key and provided row key.
     *
     * @param string $rowKey The entity row key.
     *
     * @return void
     */
    public function deleteEntity($rowKey)
    {
        $this->_proxy->deleteEntity(
            $this->_name,
            $this->_defaultParitionKey,
            $rowKey
        );
    }
}


