<?php
/**
 * @package   Salesforce.com SOAP API module for Able Polecat.
 * @file      Resource.php
 * @brief     Encapsulates result of request to Salesforce.com SOAP API.
 *
 * @author    Karl Kuhrman
 * @copyright [BDS II License] (https://github.com/kkuhrman/AblePolecat/blob/master/LICENSE.md)
 * @version   0.7.0
 */

require_once(implode(DIRECTORY_SEPARATOR, array(ABLE_POLECAT_CORE, 'Resource.php')));

interface SalesforceSoapApi_ResourceInterface extends AblePolecat_ResourceInterface {
}

class SalesforceSoapApi_Resource 
  extends AblePolecat_ResourceAbstract
  implements SalesforceSoapApi_ResourceInterface {
  
  /**
   * Constants.
   */
  const UUID = '8eb53998-5f6f-11e4-8bc7-0050569e00a2';
  const NAME = 'Generic Sobject resource';
  
  /**
   * @var AblePolecat_AccessControl_Agent_User Instance of singleton.
   */
  private static $Resource;
  
  /********************************************************************************
   * Implementation of AblePolecat_CacheObjectInterface
   ********************************************************************************/
  
  /**
   * Create a new instance of object or restore cached object to previous state.
   *
   * @param AblePolecat_AccessControl_SubjectInterface $Subject
   *
   * @return Instance of SalesforceSoapApi_Resource
   */
  public static function wakeup(AblePolecat_AccessControl_SubjectInterface $Subject = NULL) {
    
    if (!isset(self::$Resource)) {
      self::$Resource = new SalesforceSoapApi_Resource($Subject);
    }
    return self::$Resource;
  }
  
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
    //
    // @todo: 
    // - SOQL is defined in sub-class initialization.
    // - Execute SOQL here using given agent/locater
    // - Process SOQL result in sub-class implementation of abstract method TBD.
    //
    return TRUE;
  }
  
  /********************************************************************************
   * Helper functions.
   ********************************************************************************/
  
  /**
   * Validates request URI path to ensure resource request can be fulfilled.
   *
   * @throw AblePolecat_Resource_Exception If request URI path is not validated.
   */
  protected function validateRequestPath() {
  }
  
  /**
   * Extends __construct().
   */
  protected function initialize() {
    parent::initialize();
  }
}