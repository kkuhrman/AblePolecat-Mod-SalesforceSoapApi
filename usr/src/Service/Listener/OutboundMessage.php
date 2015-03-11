<?php
/**
 * @package   Salesforce.com SOAP API module for Able Polecat.
 * @file      AblePolecat-Mod-SalesforceSoapApi/usr/src/Service/Listener/OutboundMessage.php
 * @brief     Processes outbound SOAP message sent by Salesforce.com.
 *
 * @author    Karl Kuhrman
 * @copyright [BDS II License] (https://github.com/kkuhrman/AblePolecat/blob/master/LICENSE.md)
 * @version   0.7.0
 */

require_once(implode(DIRECTORY_SEPARATOR, array(ABLE_POLECAT_CORE, 'Service', 'Soap', 'Listener.php')));

interface SalesforceSoapApi_Service_Listener_OutboundMessageInterface 
  extends AblePolecat_Service_Soap_ListenerInterface {
  
  /**
   * Process Salesforce.com outbound message data.
   *
   * @param stdClass $request Outbound message sent by Salesforce.com.
   *
   * @return boolean.
   */
  public function notifications($request);
}

/**
 * Base class for outbound message listener.
 */
abstract class SalesforceSoapApi_Service_Listener_OutboundMessageAbstract 
  extends AblePolecat_Service_Soap_ListenerAbstract 
  implements SalesforceSoapApi_Service_Listener_OutboundMessageInterface {
  
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