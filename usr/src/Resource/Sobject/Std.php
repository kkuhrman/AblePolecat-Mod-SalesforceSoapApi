<?php
/**
 * @package   Salesforce.com SOAP API module for Able Polecat.
 * @file      AblePolecat-Mod-SalesforceSoapApi/usr/src/Resource/Sobject/Generic.php
 * @brief     Encapsulates a generic SOQL result record.
 *
 * @author    Karl Kuhrman
 * @copyright [BDS II License] (https://github.com/kkuhrman/AblePolecat/blob/master/LICENSE.md)
 * @version   0.7.0
 */

if (!defined('ABLEPOLECAT_MOD_SALESFORCESOAPAPI_SRC_PATH')) {
  define('ABLEPOLECAT_MOD_SALESFORCESOAPAPI_SRC_PATH', dirname(dirname(__DIR__)));
}
require_once(implode(DIRECTORY_SEPARATOR, array(ABLEPOLECAT_MOD_SALESFORCESOAPAPI_SRC_PATH, 'Resource', 'Sobject.php')));

interface SalesforceSoapApi_Resource_Sobject_StdInterface extends SalesforceSoapApi_ResourceInterface {
}

class SalesforceSoapApi_Resource_Sobject_Std
  extends SalesforceSoapApi_ResourceAbstract
  implements SalesforceSoapApi_Resource_SobjectInterface {
  
  /********************************************************************************
   * Implementation of AblePolecat_CacheObjectInterface
   ********************************************************************************/
  
  /**
   * Create a new instance of object or restore cached object to previous state.
   *
   * @param AblePolecat_AccessControl_SubjectInterface $Subject
   *
   * @return Instance of SalesforceSoapApi_Resource_Sobject_Std
   */
  public static function wakeup(AblePolecat_AccessControl_SubjectInterface $Subject = NULL) {
    $Resource = new SalesforceSoapApi_Resource_Sobject_Std($Subject);
    return $Resource;
  }
  
  /********************************************************************************
   * Implementation of SalesforceSoapApi_ResourceInterface.
   ********************************************************************************/
   
  /**
   * Interpret request path/query string as SOQL statement.
   *
   * @return SalesforceSoapApi_Soql_StatementInterface SOQL SELECT statement or NULL.
   */
  public function interpretRequest() {
    return NULL;
  }
  
  /**
   * Populate class members with data from SOAP response.
   */
  public function postprocessSoapResponse() {
    //
    // @see interpretRequest()
    //
    parent::postprocessSoapResponse();
  }
  
  /********************************************************************************
   * Helper functions.
   ********************************************************************************/
  
  /**
   * Validates request URI path to ensure resource request can be fulfilled.
   *
   * @throw AbleTabby_Exception If request URI path is not validated.
   */
  public function validateRequestPath() {
    //
    // Do nothing for generic object.
    //
  }
  
  /**
   * Extends __construct().
   */
  protected function initialize() {
    parent::initialize();
  }
}