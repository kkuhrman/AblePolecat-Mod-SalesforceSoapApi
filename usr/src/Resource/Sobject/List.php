<?php
/**
 * @package   Salesforce.com SOAP API module for Able Polecat.
 * @file      AblePolecat-Mod-SalesforceSoapApi/usr/src/Resource/Sobject/List.php
 * @brief     Encapsulates multiple records from SOQL result (Sobject).
 *
 * @author    Karl Kuhrman
 * @copyright [BDS II License] (https://github.com/kkuhrman/AblePolecat/blob/master/LICENSE.md)
 * @version   0.7.0
 */

if (!defined('ABLEPOLECAT_MOD_SALESFORCESOAPAPI_SRC_PATH')) {
  define('ABLEPOLECAT_MOD_SALESFORCESOAPAPI_SRC_PATH', dirname(dirname(__DIR__)));
}
require_once(implode(DIRECTORY_SEPARATOR, array(ABLEPOLECAT_MOD_SALESFORCESOAPAPI_SRC_PATH, 'Resource', 'Sobject', 'Std.php')));

interface SalesforceSoapApi_Resource_Sobject_ListInterface extends SalesforceSoapApi_ResourceInterface {
}

abstract class SalesforceSoapApi_Resource_Sobject_ListAbstract
  extends SalesforceSoapApi_ResourceAbstract
  implements SalesforceSoapApi_Resource_Sobject_ListInterface {
  
  /********************************************************************************
   * Implementation of SalesforceSoapApi_ResourceInterface.
   ********************************************************************************/
   
  /**
   * Populate class members with data from SOAP response.
   */
  public function postprocessSoapResponse() {
    
    //
    // Create list container.
    //
    $List = new AblePolecat_Data_Primitive_Array();
    
    //
    // Add list items.
    //
    $Records = $this->getResponseRecords();
    foreach($Records as $offset => $Record) {
      $ListItem = new SalesforceSoapApi_Resource_Sobject_Std();
      $Properties = get_object_vars($Record);
      foreach($Properties as $propertyName => $propertyValue) {
        $ListItem->__set($propertyName, $propertyValue);
      }
      $List->__set($offset, $ListItem);
    }
    $this->List__r = $List;
  }
}