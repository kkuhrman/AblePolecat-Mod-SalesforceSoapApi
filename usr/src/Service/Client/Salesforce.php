<?php
/**
 * @package   Salesforce.com SOAP API module for Able Polecat.
 * @file      AblePolecat-Mod-SalesforceSoapApi/usr/src/Service/Client/Salesforce.php
 * @brief     Encapsulates Salesforce.com SOAP client.
 *
 * @author    Karl Kuhrman
 * @copyright [BDS II License] (https://github.com/kkuhrman/AblePolecat/blob/master/LICENSE.md)
 * @version   0.7.0
 */

if (!defined('FORCE_DOT_COM_TOOLKIT_FOR_PHP_PATH')) {
  $FORCE_DOT_COM_TOOLKIT_FOR_PHP_PATH = AblePolecat_Server_Paths::getFullPath('7178a704-b5fd-11e4-a12d-0050569e00a2');
  define('FORCE_DOT_COM_TOOLKIT_FOR_PHP_PATH', $FORCE_DOT_COM_TOOLKIT_FOR_PHP_PATH);
}
if (!defined('ABLEPOLECAT_MOD_SALESFORCESOAPAPI_SRC_PATH')) {
  define('ABLEPOLECAT_MOD_SALESFORCESOAPAPI_SRC_PATH', dirname(dirname(__DIR__)));
}
require_once(implode(DIRECTORY_SEPARATOR, array(FORCE_DOT_COM_TOOLKIT_FOR_PHP_PATH, 'soapclient', 'SforceEnterpriseClient.php')));
require_once(implode(DIRECTORY_SEPARATOR, array(ABLEPOLECAT_MOD_SALESFORCESOAPAPI_SRC_PATH, 'QueryLanguage', 'Statement', 'Soql.php')));
require_once(implode(DIRECTORY_SEPARATOR, array(ABLE_POLECAT_CORE, 'Service', 'Client.php')));

class SalesforceSoapApi_Client extends AblePolecat_Service_ClientAbstract {
  
  /**
   * Registry entry article constants.
   */
  const UUID = 'f6967c4c-c7ec-11e4-a12d-0050569e00a2';
  const NAME = 'SalesforceSoapApi_Client';
  
  /**
   * @var SalesforceSoapApi_Client Instance of singleton.
   */
  private static $Singleton;
  
  /**
   * @var SforceEnterpriseClient.
   */
  private $Client;
   
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
   */
  public function open(AblePolecat_AccessControl_AgentInterface $Agent, AblePolecat_AccessControl_Resource_LocaterInterface $Url = NULL) {
    
    $connected = FALSE;
    
    try {
      $this->Client = new SforceEnterpriseClient();
      $this->Client->createConnection($Url->__toString());
      $this->Client->login(
        $Url->getUsername(),
        $Url->getPassword() . $Url->getSecurityToken()
      );
      $connected = TRUE;
    }
    catch (Exception $Exception) {
      $this->Client = NULL;
      throw new AblePolecat_Resource_Exception($Exception->getMessage());
    }
    return $connected;
  }
  
  /********************************************************************************
   * Implementation of AblePolecat_CacheObjectInterface.
   ********************************************************************************/
  
  /**
   * Serialize object to cache.
   *
   * @param AblePolecat_AccessControl_SubjectInterface $Subject.
   */
  public function sleep(AblePolecat_AccessControl_SubjectInterface $Subject = NULL) {
  }
  
  /**
   * Create a new instance of object or restore cached object to previous state.
   *
   * @param AblePolecat_AccessControl_SubjectInterface Session status helps determine if connection is new or established.
   *
   * @return AblePolecat_CacheObjectInterface Initialized server resource ready for business or NULL.
   */
  public static function wakeup(AblePolecat_AccessControl_SubjectInterface $Subject = NULL) {
    if (!isset(self::$Singleton)) {
      self::$Singleton = new SalesforceSoapApi_Client($Subject);
    }
    return self::$Singleton; 
  }
  
  /********************************************************************************
   * Encapsulation of SOAP client API.
   ********************************************************************************/
   
  /**
   * Execute SOQL SELECT statement and return result.
   *
   * @param SalesforceSoapApi_Soql_StatementInterface $soql
   *
   * @return object or NULL.
   */
  public function query(SalesforceSoapApi_Soql_StatementInterface $soql) {
    
    $Response = NULL;
    if(isset($this->Client)) {
      $Response = $this->Client->query($soql);
    }
    return $Response;
  }
  
  /********************************************************************************
   * Helper functions.
   ********************************************************************************/
   
  /**
   * Extends __construct().
   */
  protected function initialize() {
    parent::initialize();
    $this->Client = NULL;
    $this->setId('Salesforce.com');
    $this->setName('soapClient');
  }
}