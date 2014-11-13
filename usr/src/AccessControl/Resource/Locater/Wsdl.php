<?php
/**
 * @package   Salesforce.com SOAP API module for Able Polecat.
 * @file      AblePolecat-Mod-SalesforceSoapApi/usr/src/AccessControl/Resource/Locater/Wsdl.php
 * @brief     Encapsulates resource locater for Salesforce.com SOAP service.
 *
 * @author    Karl Kuhrman
 * @copyright [BDS II License] (https://github.com/kkuhrman/AblePolecat/blob/master/LICENSE.md)
 * @version   0.7.0
 */

require_once(implode(DIRECTORY_SEPARATOR, array(ABLE_POLECAT_CORE, 'AccessControl', 'Resource', 'Locater.php')));

interface SalesforceSoapApi_AccessControl_Resource_Locater_WsdlInterface extends AblePolecat_AccessControl_Resource_LocaterInterface {
  /**
   * @return string Force.com user security token.
   */
  public function getSecurityToken();
  
  /**
   * @param string $securityToken Force.com user security token.
   */
  public function setSecurityToken($securityToken);
}

class SalesforceSoapApi_AccessControl_Resource_Locater_Wsdl 
  extends AblePolecat_AccessControl_Resource_Locater
  implements SalesforceSoapApi_AccessControl_Resource_Locater_WsdlInterface {
  
  /**
   * @var string Force.com user security token.
   */
  private $securityToken;
  
  /********************************************************************************
   * Implementation of AblePolecat_AccessControl_Resource_LocaterInterface.
   ********************************************************************************/
  
  /**
   * Create URL.
   * 
   * @param DOMString $url Relative or absolute path.
   * @param optional DOMString $baseURL.
   *
   * @return object Instance of class implementing AblePolecat_AccessControl_Resource_LocaterInterface or NULL.
   */
  public static function create($url, $baseURL = NULL) {
    isset($baseURL) ? $url = $baseURL . self::URI_SLASH . $url : NULL;
    $Locater = new SalesforceSoapApi_AccessControl_Resource_Locater_Wsdl($url);
    return $Locater;
  }
  
  /********************************************************************************
   * Implementation of SalesforceSoapApi_AccessControl_Resource_Locater_WsdlInterface.
   ********************************************************************************/
  
  /**
   * @return string Force.com user security token.
   */
  public function getSecurityToken() {
    return $this->securityToken;
  }
  
  /**
   * @param string $securityToken Force.com user security token.
   */
  public function setSecurityToken($securityToken) {
    $this->securityToken = $securityToken;
  }
  
  /********************************************************************************
   * Helper functions.
   ********************************************************************************/
   
  /**
   * Extends __construct().
   */
  protected function initialize() {
    parent::initialize();
    $this->securityToken = NULL;
  }
}