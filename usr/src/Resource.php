<?php
/**
 * @package   Salesforce.com SOAP API module for Able Polecat.
 * @file      AblePolecat-Mod-SalesforceSoapApi/usr/src/Resource.php
 * @brief     Encapsulates result of request to Salesforce.com SOAP API.
 *
 * @author    Karl Kuhrman
 * @copyright [BDS II License] (https://github.com/kkuhrman/AblePolecat/blob/master/LICENSE.md)
 * @version   0.7.0
 */

if (!defined('SALESFORCE_SOAP_API_MOD_SRC_PATH')) {
  define('SALESFORCE_SOAP_API_MOD_SRC_PATH', __DIR__);
}
require_once(implode(DIRECTORY_SEPARATOR, array(SALESFORCE_SOAP_API_MOD_SRC_PATH, 'Service', 'Client', 'Salesforce.php')));
require_once(implode(DIRECTORY_SEPARATOR, array(ABLE_POLECAT_CORE, 'Resource.php')));

interface SalesforceSoapApi_ResourceInterface extends AblePolecat_ResourceInterface {
  
  /**
   * Interpret request path/query string as SOQL statement.
   *
   * @return SalesforceSoapApi_Soql_StatementInterface SOQL SELECT statement or NULL.
   */
  public function interpretRequest();
  
  /**
   * Populate class members with data from SOAP response.
   */
  public function postprocessSoapResponse();
}

abstract class SalesforceSoapApi_ResourceAbstract
  extends AblePolecat_ResourceAbstract
  implements SalesforceSoapApi_ResourceInterface {
  
  /**
   * @var SalesforceSoapApi_Soql_StatementInterface SOQL SELECT statement.
   */
  private $soql;
  
  /**
   * @var Object Result of SOQL query.
   */
  private $soapResponse;
  
  /********************************************************************************
   * Implementation of AblePolecat_AccessControl_ResourceInterface.
   ********************************************************************************/
   
  /**
   * Opens an existing resource or makes an empty one accessible depending on permissions.
   * 
   * @param AblePolecat_AccessControl_AgentInterface $agent Agent seeking access.
   * @param AblePolecat_AccessControl_Resource_LocaterInterface $Url Existing or new resource.
   * @param string $name Optional common name for new resources.
   *
   * @return bool TRUE if access to resource is granted, otherwise FALSE.
   * @throw AblePolecat_Resource_Exception if resource cannot be opened.
   */
  public function open(AblePolecat_AccessControl_AgentInterface $Agent, AblePolecat_AccessControl_Resource_LocaterInterface $Url = NULL) {
    
    //
    // Validate SOQL.
    //
    if (!isset($this->soql)) {
      throw new AblePolecat_Resource_Exception(sprintf("%s class did not produce a valid SOQL statement for resource given by %s",
        AblePolecat_Data::getDataTypeName($this),
        $this->resourceName
      ));
    }
    else if (!is_a($this->soql, 'SalesforceSoapApi_Soql_StatementInterface')) {
      throw new AblePolecat_Resource_Exception(sprintf("SalesforceSoapApi_ResourceInterface::interpretRequest() must return object which implements SalesforceSoapApi_Soql_StatementInterface or NULL. %s::interpretRequest() returned %s.",
        AblePolecat_Data::getDataTypeName($this),
        AblePolecat_Data::getDataTypeName($this->soql)
      ));
    }
    
    //
    // Establish connection to Salesforce.com SOAP client.
    //
    $SalesforceClient = SalesforceSoapApi_Client::wakeup($Agent);
    $SalesforceClient->open($Agent, $Url);
    $this->soapResponse = $SalesforceClient->query($this->soql);
    $this->postprocessSoapResponse();
    return TRUE;
  }
  
  /********************************************************************************
   * Helper functions.
   ********************************************************************************/
  
  /**
   * @return bool TRUE if SOAP response returned all results of query, otherwise FALSE.
   */
  public function getQueryDone() {
    $done = FALSE;
    if (isset($this->soapResponse)) {
      $done = $this->soapResponse->done;
    }
    return $done;
  }
  
  /**
   * @return string ID use in queryMore() or NULL.
   */
  public function getQueryLocater() {
    $queryLocator = NULL;
    if (isset($this->soapResponse)) {
      $queryLocator = $this->soapResponse->queryLocator;
    }
    return $queryLocator;
  }
  
  /**
   * @return int Number of records returned by query.
   */
  public function getResponseRecordCount() {
    $size = 0;
    if (isset($this->soapResponse)) {
      $size = $this->soapResponse->size;
    }
    return $size;
  }
  
  /**
   * @return Array Records returned by query.
   */
  public function getResponseRecords() {
    $records = NULL;
    if (isset($this->soapResponse)) {
      $records = $this->soapResponse->records;
    }
    else {
      $records = array();
    }
    return $records;
  }
  
  /**
   * Extends __construct().
   */
  protected function initialize() {
    //
    // Override AblePolecat_ResourceAbstract::initialize();
    // (Leave $this->resourceId and $this->resourceName undefined).
    //
    $this->validateRequestPath();
    $this->setUri(AblePolecat_Host::getRequest()->getBaseUrl() . AblePolecat_Host::getRequest()->getRequestPath(TRUE));
    $this->soql = $this->interpretRequest();
    $this->soapResponse = NULL;
  }
}