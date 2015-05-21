<?php
/**
 * @package   Salesforce.com SOAP API module for Able Polecat.
 * @file      AblePolecat-Mod-SalesforceSoapApi/usr/src/Transaction/Restricted.php
 * @brief     Base class for all transactions using the Force.com SOAP API.
 *
 * @author    Karl Kuhrman
 * @copyright [BDS II License] (https://github.com/kkuhrman/AblePolecat/blob/master/LICENSE.md)
 * @version   0.7.0
 */

if (!defined('ABLEPOLECAT_MOD_SALESFORCESOAPAPI_SRC_PATH')) {
  define('ABLEPOLECAT_MOD_SALESFORCESOAPAPI_SRC_PATH', dirname(dirname(__DIR__)));
}
require_once(implode(DIRECTORY_SEPARATOR, array(ABLEPOLECAT_MOD_SALESFORCESOAPAPI_SRC_PATH, 'AccessControl', 'Resource', 'Locater', 'Wsdl.php')));
require_once(implode(DIRECTORY_SEPARATOR, array(ABLEPOLECAT_MOD_SALESFORCESOAPAPI_SRC_PATH, 'Resource.php')));
require_once(implode(DIRECTORY_SEPARATOR, array(ABLE_POLECAT_CORE, 'Transaction', 'Restricted.php')));

interface SalesforceSoapApi_Transaction_RestrictedInterface extends AblePolecat_Transaction_RestrictedInterface {
}

abstract class SalesforceSoapApi_Transaction_RestrictedAbstract 
  extends AblePolecat_TransactionAbstract 
  implements SalesforceSoapApi_Transaction_RestrictedInterface {
  
  const CONF_FILENAME_MOD = 'module.xml';
  
  /**
   * @var SalesforceSoapApi_AccessControl_Resource_Locater_Wsdl.
   */
  private $WsdlLocater;
  
  /**
   * @var mixed Security token from Force.com SOAP service.
   */
  private $SecurityToken;
  
  /********************************************************************************
   * Implementation of AblePolecat_TransactionInterface.
   ********************************************************************************/
   
  /**
   * Commit
   */
  public function commit() {
    //
    // Parent updates transaction in database.
    //
    parent::commit();
  }
  
  /**
   * Rollback
   */
  public function rollback() {
    //
    // @todo
    //
  }
  
  /**
   * Begin or resume the transaction.
   *
   * @return AblePolecat_ResourceInterface The result of the work, partial or completed.
   * @throw AblePolecat_Transaction_Exception If cannot be brought to a satisfactory state.
   */
  public function start() {
    
    //
    // Check request method.
    //
    $method = $this->getRequest()->getMethod();
    switch ($method) {
      default:
        break;
      case 'GET':
        //
        // Check for valid security token.
        //
        $SecurityToken = $this->getSecurityToken();
        if (isset($SecurityToken)) {
          //
          // Database is active. Allow parent to handle from here.
          //
          return parent::start();
        }
        else {
          //
          // User is not authenticated. Save transaction in $_SESSION global variable.
          //
          $transactionId = $this->getTransactionId();
          AblePolecat_Mode_Session::setSessionVariable($this->getAgent(), AblePolecat_Host::POLECAT_INSTALL_TRX, $transactionId);
          AblePolecat_Mode_Session::setSessionVariable($this->getAgent(), AblePolecat_Host::POLECAT_INSTALL_SAVEPT, 'start');
        }
        break;
      case 'POST':
        $transactionId = AblePolecat_Mode_Session::getSessionVariable($this->getAgent(), AblePolecat_Host::POLECAT_INSTALL_TRX);
        $savePointId = AblePolecat_Mode_Session::getSessionVariable($this->getAgent(), AblePolecat_Host::POLECAT_INSTALL_SAVEPT);
        break;
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
   * Implementation of AblePolecat_Transaction_RestrictedInterface.
   ********************************************************************************/
  
  /**
   * @return boolean TRUE if internal authentication is valid, otherwise FALSE.
   */
  public function authenticate() {
    
  }
  
  /**
   * @return UUID Id of redirect resource on authentication.
   */
  public function getRedirectResourceId() {
    
  }
  
  /**
   * @return mixed Whatever was used to authenticate access.
   */
  public function getSecurityToken() {
    return $this->SecurityToken;
  }
  
  /********************************************************************************
   * Helper functions.
   ********************************************************************************/
  
  /**
   * Extends __construct().
   */
  protected function initialize() {
    parent::initialize();
    $this->WsdlLocater = NULL;
    $this->SecurityToken = NULL;
  }
}