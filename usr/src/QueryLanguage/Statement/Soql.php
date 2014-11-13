<?php
/**
 * @package   Salesforce.com SOAP API module for Able Polecat.
 * @file      AblePolecat-Mod-SalesforceSoapApi/usr/src/QueryLanguage/Statement/Soql.php
 * @brief     Encapsulates SOQL statement.
 *
 * @author    Karl Kuhrman
 * @copyright [BDS II License] (https://github.com/kkuhrman/AblePolecat/blob/master/LICENSE.md)
 * @version   0.7.0
 */

require_once(implode(DIRECTORY_SEPARATOR, array(ABLE_POLECAT_CORE, 'QueryLanguage', 'Statement.php')));

interface SalesforceSoapApi_Soql_StatementInterface 
  extends AblePolecat_DynamicObjectInterface,
          AblePolecat_QueryLanguage_StatementInterface {
  
  /**
   * Extended syntax element constants.
   */
  
  /**
   * Extended DML op constants.
   */
}

/**
 * A shorthand for SalesforceSoapApi_Soql_Statement::create().
 */
function __SOQL() {
  $SoqlStatement = SalesforceSoapApi_Soql_Statement::create();
  return $SoqlStatement;
}

class SalesforceSoapApi_Soql_Statement 
  extends AblePolecat_QueryLanguage_StatementAbstract
  implements SalesforceSoapApi_Soql_StatementInterface {
  
  /********************************************************************************
   * Implementation of AblePolecat_StdObjectInterface.
   ********************************************************************************/
   
  /**
   * Override base class implementation of __set() magic method so as to use syntax checking.
   */
  public function __set($name, $value) {
    switch ($name) {
      default:
        parent::__set($name, $value);
        break;
    }
  }
  
  /********************************************************************************
   * Implementation of AblePolecat_DynamicObjectInterface.
   ********************************************************************************/
   
  /**
   * Creational method.
   *
   * @return AblePolecat_DynamicObjectInterface Concrete instance of class.
   */
  public static function create() {
    $SoqlStatement = new SalesforceSoapApi_Soql_Statement();
    return $SoqlStatement;
  }
  
  /********************************************************************************
   * Implementation of AblePolecat_QueryLanguage_StatementInterface.
   ********************************************************************************/
  
  /**
   * @return query langauge statement as a string.
   */
  public function __toString() {
    $sqlStatement = '';
    $tokens = array($this->getDmlOp());
    switch($this->getDmlOp()) {
      default:
        break;
      case AblePolecat_QueryLanguage_Statement_Sql_Interface::SELECT:
        $tokens[] = $this->getFields();
        if ($this->getQueryObject()) {
          $tokens[] = 'FROM';
          $tokens[] = $this->getQueryObject();
        }
        if ($this->getWhereCondition()) {
          $tokens[] = 'WHERE';
          $tokens[] = $this->getWhereCondition();
        }
        if ($this->getGroupByExpression()) {
          $tokens[] = 'GROUP BY';
          $tokens[] = $this->getGroupByExpression();
        }
        if ($this->getHavingCondition()) {
          $tokens[] = 'HAVING';
          $tokens[] = $this->getHavingCondition();
        }
        if ($this->getOrderByExpression()) {
          $tokens[] = 'ORDER BY';
          $tokens[] = $this->getOrderByExpression();
        }
        $tokens[] = $this->getLimitOffsetSyntax();
        break;
    }
    $sqlStatement = implode(' ', $tokens);
    return trim($sqlStatement);
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