<?php
/**
 * @package   Salesforce.com SOAP API module for Able Polecat.
 * @file      AblePolecat-Mod-SalesforceSoapApi/usr/src/Resource/Sobject.php
 * @brief     Encapsulates single record from SOQL result (Sobject).
 *
 * @author    Karl Kuhrman
 * @copyright [BDS II License] (https://github.com/kkuhrman/AblePolecat/blob/master/LICENSE.md)
 * @version   0.7.0
 */

if (!defined('ABLEPOLECAT_MOD_SALESFORCESOAPAPI_SRC_PATH')) {
  define('ABLEPOLECAT_MOD_SALESFORCESOAPAPI_SRC_PATH', dirname(__DIR__));
}
require_once(implode(DIRECTORY_SEPARATOR, array(ABLEPOLECAT_MOD_SALESFORCESOAPAPI_SRC_PATH, 'Resource.php')));

interface SalesforceSoapApi_Resource_SobjectInterface extends SalesforceSoapApi_ResourceInterface {
}

abstract class SalesforceSoapApi_Resource_SobjectAbstract
  extends SalesforceSoapApi_ResourceAbstract
  implements SalesforceSoapApi_Resource_SobjectInterface {
  
  /********************************************************************************
   * Implementation of AblePolecat_Data_PrimitiveInterface.
   ********************************************************************************/
  
  /**
   * @param DOMDocument $Document.
   *
   * @return DOMElement Encapsulated data expressed as DOM node.
   */
  public function getDomNode(DOMDocument $Document = NULL) {
    //
    // Create parent element.
    //
    !isset($Document) ? $Document = new DOMDocument() : NULL;
    $Element = $Document->createElement(AblePolecat_Data::getDataTypeName($this));
    
    //
    // Iterate through properties and create child elements.
    //
    $Property = $this->getFirstProperty();
    while ($Property) {
      $propertyName = $this->getPropertyKey();
      $Child = $Property->getDomNode($Document);
      $Element->appendChild($Child);
      $Property = $this->getNextProperty();
    }
    return $Element;
  }
  
  /********************************************************************************
   * Implementation of SalesforceSoapApi_ResourceInterface.
   ********************************************************************************/
   
  /**
   * Populate class members with data from SOAP response.
   */
  public function postprocessSoapResponse() {
    //
    // SOQL for detail objects should return one and only one record.
    //
    if ($this->getResponseRecordCount() != 1) {
      $message = sprintf("SOQL for detail objects should return one and only one record. %d returned for %s.",
        $this->getResponseRecordCount(),
        AblePolecat_Data::getDataTypeName($this)
      );
      AblePolecat_Command_Log::invoke(
        $this->getDefaultCommandInvoker(), 
        $message,
        AblePolecat_LogInterface::WARNING
      );
    }
    $Records =$this->getResponseRecords();
    if (isset($Records[0])) {
      $Properties = get_object_vars($Records[0]);
      foreach($Properties as $propertyName => $propertyValue) {
        $this->__set($propertyName, $propertyValue);
      }
    }
  }
}