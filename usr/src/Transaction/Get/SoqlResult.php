<?php
/**
 * @package   Salesforce.com SOAP API module for Able Polecat.
 * @file      AblePolecat-Mod-SalesforceSoapApi/usr/src/Transaction/Get/SoqlResult.php
 * @brief     Able Polecat transaction processes SOAP request and returns resource.
 *
 * @author    Karl Kuhrman
 * @copyright [BDS II License] (https://github.com/kkuhrman/AblePolecat/blob/master/LICENSE.md)
 * @version   0.7.0
 */

if (!defined('SALESFORCE_SOAP_API_MOD_PATH')) {
  $SALESFORCE_SOAP_API_MOD_PATH = AblePolecat_Server_Paths::getFullPath('d69bd6b3-5ef1-11e4-8bc7-0050569e00a2');
  define('SALESFORCE_SOAP_API_MOD_PATH', $SALESFORCE_SOAP_API_MOD_PATH);
}
if (!defined('ABLEPOLECAT_MOD_SALESFORCESOAPAPI_SRC_PATH')) {
  define('ABLEPOLECAT_MOD_SALESFORCESOAPAPI_SRC_PATH', dirname(dirname(__DIR__)));
}
require_once(implode(DIRECTORY_SEPARATOR, array(ABLEPOLECAT_MOD_SALESFORCESOAPAPI_SRC_PATH, 'AccessControl', 'Resource', 'Locater', 'Wsdl.php')));
require_once(implode(DIRECTORY_SEPARATOR, array(ABLEPOLECAT_MOD_SALESFORCESOAPAPI_SRC_PATH, 'Resource.php')));
require_once(implode(DIRECTORY_SEPARATOR, array(ABLE_POLECAT_CORE, 'Transaction', 'Get', 'Resource.php')));

class SalesforceSoapApi_Transaction_Get_SoqlResult extends AblePolecat_Transaction_Get_Resource {
  
  /**
   * Registry entry article constants.
   */
  const UUID = 'fa8b8693-4401-11e4-b353-0050569e00a2';
  const NAME = 'SalesforceSoapApi_Transaction_Get_SoqlResult';
  
  const CONF_FILENAME_MOD = 'module.xml';
  
  /**
   * @var AblePolecat_AccessControl_Agent_User Instance of singleton.
   */
  private static $Transaction;
  
  /********************************************************************************
   * Implementation of AblePolecat_AccessControl_ArticleInterface.
   ********************************************************************************/
  
  /**
   * Return unique, system-wide identifier for agent.
   *
   * @return string Transaction identifier.
   */
  public static function getId() {
    return self::UUID;
  }
  
  /**
   * Return common name for agent.
   *
   * @return string Transaction name.
   */
  public static function getName() {
    return self::NAME;
  }
  
  /********************************************************************************
   * Implementation of AblePolecat_CacheObjectInterface.
   ********************************************************************************/
  
  /**
   * Create a new instance of object or restore cached object to previous state.
   *
   * @param AblePolecat_AccessControl_SubjectInterface Session status helps determine if connection is new or established.
   *
   * @return AblePolecat_CacheObjectInterface Initialized server resource ready for business or NULL.
   */
  public static function wakeup(AblePolecat_AccessControl_SubjectInterface $Subject = NULL) {
    
    if (!isset(self::$Transaction)) {
      //
      // Unmarshall (from numeric keyed index to named properties) variable args list.
      //
      $ArgsList = self::unmarshallArgsList(__FUNCTION__, func_get_args());
      self::$Transaction = new SalesforceSoapApi_Transaction_Get_SoqlResult($ArgsList->getArgumentValue(self::TX_ARG_SUBJECT));
      self::prepare(self::$Transaction, $ArgsList, __FUNCTION__);
    }
    return self::$Transaction;
  }
  
  /********************************************************************************
   * Implementation of AblePolecat_TransactionInterface.
   ********************************************************************************/
  
  /**
   * Rollback
   */
  public function rollback() {
    //
    // @todo
    //
  }
  
  /**
   * Return the data model (resource) corresponding to a web request URI/path.
   *
   * @return AblePolecat_ResourceInterface
   * @throw AblePolecat_Transaction_Exception If cannot be brought to a satisfactory state.
   */
  public function run() {
        
    //
    // Give parent first shot at resolving request.
    //
    $Resource = parent::run();
    if (isset($Resource)) {
      if (!is_a($Resource, 'SalesforceSoapApi_ResourceInterface')) {
        //
        // unexpected resource type.
        //
        $message = sprintf("%s must return object which implements SalesforceSoapApi_ResourceInterface or NULL. %s returned.",
          __METHOD__,
          AblePolecat_Data::getDataTypeName($Resource)
        );
        throw new AblePolecat_Resource_Exception($message);
      }
      else {
        //
        // Get module registry entry.
        //
        $RegistryEntry = AblePolecat_Registry_Entry_ClassLibrary::fetch(ABLEPOLECAT_MOD_SALESFORCESOAPAPI_LIB_ID);
        
        //
        // Get module local project configuration file.
        //
        $localProjectConfFile = NULL;
        if (isset($RegistryEntry)) {
          $localProjectConfFile = AblePolecat_Mode_Config::getModuleConfFile($RegistryEntry);
        }
        
        //
        // Get SOAP client locater settings.
        //
        $wsdlPath = '';
        $userName = '';
        $passWord = '';
        $securityToken = '';
        if (isset($localProjectConfFile)) {
          //
          // WSDL path.
          //
          $Node = AblePolecat_Dom::getElementById($localProjectConfFile, ABLEPOLECAT_MOD_SALESFORCESOAPAPI_WSDL_ID);
          if (isset($Node)) {
            if (isset($Node->childNodes)) {
              foreach($Node->childNodes as $key => $childNode) {
                if($childNode->nodeName == 'polecat:path') {
                  $wsdlPath = $childNode->nodeValue;
                  if (!file_exists($wsdlPath)) {
                    //
                    // User did not set valid WSDL path in local project configuration file.
                    //
                    throw new AblePolecat_Resource_Exception(sprintf("Salesforce.com SOAP client WSDL path is not valid. Tried %s",
                      addslashes($wsdlPath)
                    ));
                  }
                  break;
                }
              }
            }
          }
          
          //
          // Salesforce.com user login.
          //
          $Node = AblePolecat_Dom::getElementById($localProjectConfFile, ABLEPOLECAT_MOD_SALESFORCESOAPAPI_USER_ID);
          if (isset($Node)) {
            if (isset($Node->childNodes)) {
              foreach($Node->childNodes as $key => $childNode) {
                switch ($childNode->nodeName) {
                  default:
                    break;
                  case 'polecat:username':
                    $userName = $childNode->nodeValue;
                    break;
                  case 'polecat:password':
                    $passWord = $childNode->nodeValue;
                    break;
                  case 'polecat:securityToken':
                    $securityToken = $childNode->nodeValue;
                    break;
                }
              }
            }
          }
        }
        
        //
        // Create locater from conf file
        //
        $Locater = SalesforceSoapApi_AccessControl_Resource_Locater_Wsdl::create($wsdlPath);
        $Locater->setUsername($userName);
        $Locater->setPassword($passWord);
        $Locater->setSecurityToken($securityToken);
        
        //
        // Use transaction agent and locater to open resource
        //
        $Agent = $this->getAgent();
        $Resource->open($Agent, $Locater);
      }
    }
    return $Resource;
  }
  
  /********************************************************************************
   * Helper functions.
   ********************************************************************************/
  
  /**
   * Extends __construct().
   */
  protected function initialize() {
    parent::initialize();
  }
}