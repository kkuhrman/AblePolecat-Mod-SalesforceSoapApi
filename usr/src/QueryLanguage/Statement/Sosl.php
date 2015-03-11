<?php
/**
 * @package   Salesforce.com SOAP API module for Able Polecat.
 * @file      AblePolecat-Mod-SalesforceSoapApi/usr/src/QueryLanguage/Statement/Sosl.php
 * @brief     Encapsulates SOSL statement.
 *
 * @author    Karl Kuhrman
 * @copyright [BDS II License] (https://github.com/kkuhrman/AblePolecat/blob/master/LICENSE.md)
 * @version   0.7.0
 */

require_once(implode(DIRECTORY_SEPARATOR, array(ABLE_POLECAT_CORE, 'QueryLanguage', 'Statement.php')));

interface SalesforceSoapApi_Sosl_StatementInterface 
  extends AblePolecat_DynamicObjectInterface,
          AblePolecat_QueryLanguage_StatementInterface {
  
  /**
   * Extended syntax element constants.
   */
  const FIND      = 'FIND';
  const IN        = 'IN';
  const RETURNING = 'RETURNING';
  
  /**
   * Statement elements.
   */
  const SEARCH_QUERY          = 'SEARCH_QUERY';
  const SEARCH_GROUP          = 'SEARCH_GROUP';
  const SEARCH_GROUP_ALL      = 'ALL FIELDS';
  const SEARCH_GROUP_NAME     = 'NAME FIELDS';
  const SEARCH_GROUP_EMAIL    = 'EMAIL FIELDS';
  const SEARCH_GROUP_PHONE    = 'PHONE FIELDS';
  const SEARCH_GROUP_SIDEBAR  = 'SIDEBAR FIELDS';
}

/**
 * A shorthand for SalesforceSoapApi_Sosl_Statement::create().
 */
function __SOSL() {
  $SoslStatement = SalesforceSoapApi_Sosl_Statement::create();
  return $SoslStatement;
}

class SalesforceSoapApi_Sosl_Statement 
  extends AblePolecat_DynamicObjectAbstract
  implements SalesforceSoapApi_Sosl_StatementInterface {
  
  /**
   * Registry entry article constants.
   */
  const UUID = '3cb95032-c7ed-11e4-a12d-0050569e00a2';
  const NAME = 'SalesforceSoapApi_Sosl_Statement';
  
  /**
   * @var supported sql syntax.
   */
  private static $supportedSosl = NULL;
  
  /********************************************************************************
   * Implementation of AblePolecat_StdObjectInterface.
   ********************************************************************************/
   
  /**
   * Override base class implementation of __set() magic method so as to use syntax checking.
   */
  public function __set($name, $value) {
    switch ($name) {
      default:
        //
        // @todo: do we allow this?
        //
        parent::__set($name, $value);
        break;
      case AblePolecat_QueryLanguage_StatementInterface::DML:
        $this->setDmlOp($value);
        break;
      case AblePolecat_QueryLanguage_StatementInterface::FIELDS:
        $this->setFields($value);
        break;
      case AblePolecat_QueryLanguage_StatementInterface::WHERE:
        $this->setWhereCondition($value);
        break;
      case AblePolecat_QueryLanguage_StatementInterface::ORDERBY:
        $this->setOrderByExpression($value);
        break;
      case AblePolecat_QueryLanguage_StatementInterface::LIMIT:
        $this->setLimit($value);
        break;
      case AblePolecat_QueryLanguage_Statement_Sql_Interface::OFFSET:
        $this->setOffset($value);
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
    $SoqlStatement = new SalesforceSoapApi_Sosl_Statement();
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
    //
    // @todo implement SOSL __toString().
    //
    return $sqlStatement;
  }
     
  /**
   * @return string DML operation.
   */
  public function getDmlOp() {
    return SalesforceSoapApi_Sosl_StatementInterface::FIND;
  }
  
  /**
   * Used to express literal values in SOSL (for example quotes around strings etc).
   *
   * @param mixed $literal The value of the literal being expressed.
   * @param string $type Data type to override default evaluation.
   *
   * @return string Value of literal expressed in encapsulated SQL DML syntax.
   */
  public static function getLiteralExpression($literal, $type = NULL) {
    //
    // @todo: handle NULL values
    //
    $expression = '';
    
    //
    // @todo: handle non-scalar types.
    //
    if (isset($literal) && is_scalar($literal)) {
      switch (gettype($literal)) {
        default:
          //
          // @todo NULL?
          //
          break;
         case 'boolean':
          $literal ? $expression = 'TRUE' : $expression = 'FALSE';
          break;
        case 'integer':
          $expression = intval($literal);
          break;
        case 'double':
          //
          // NOTE: gettype() returns "double" in case of a float, and not simply "float"
          // @todo: obviously must be formatted scale, precision etc.
          //
          $expression = strval($literal);
          break;
        case 'string':
          //
          // NOTE: call to values() handles quotes
          // @see values().
          //
          $expression = strval($literal);
          break;
        case 'NULL':
          //
          // @todo
          //
          break;
      }
    }
    return $expression;
  }
  
  /**
   * Set DML operation.
   * 
   * @param string $DmlOp DML operation.
   *
   * @throw AblePolecat_QueryLanguage_Exception if syntax is not supported.
   */
  public function setDmlOp($DmlOp) {    
    if ($DmlOp != SalesforceSoapApi_Sosl_StatementInterface::FIND) {
      throw new AblePolecat_QueryLanguage_Exception("Invalid SOSL syntax [$DmlOp].",
        AblePolecat_Error::INVALID_SYNTAX);
    }
  }
  
  /**
   * Verifies if given syntax element is supported.
   *
   * @param string $dml DML operation (e.g. SELECT, INSERT, etc.)
   * @param string $element One of the predefined SOSL syntax element constants.
   *
   * @return bool TRUE if syntax is supported by concrete class, otherwise FALSE.
   */
  public static function supportsSyntax($dml, $element = NULL) {
    
    $Supported = FALSE;
    
    if (isset($element)) {
      isset(self::$supportedSosl[$dml][$element]) ? $Supported = self::$supportedSosl[$dml][$element] : $Supported = FALSE;
    }
    else {
      $Supported = isset(self::$supportedSosl[$dml]);
    }
    return $Supported;
  }
  
  /********************************************************************************
   * Query building helper functions.
   *
   * Simple SOSL statement syntax is:
   * FIND {[search string]} IN [search group] RETURNING [SObject] ([field list] 
   *  [WHERE clause] [ORDER BY clause] [LIMIT/OFFSET expression])
   *
   * The RETURNING clause can specify multiple SObjects and corresponding field
   * lists etc separated by comma. Accordingly, the query building functions for 
   * where(), order_by() and limit() require the first parameter to be the 
   * corresponding SObject name.
   ********************************************************************************/
  
  /**
   * @param string $searchQuery The text to search for.
   *
   * @return SalesforceSoapApi_Sosl_Statement.
   */
  public function find() {
    $args = func_get_args();
    if (isset($args[0])) {
      $this->setSearchQuery($args[0]);
    }
    return $this;
  }
  
  /**
   * Set SOSL search group.
   *
   * @param string $searchGroup
   *
   * @return SalesforceSoapApi_Sosl_Statement.
   */
  public function in() {
    $args = func_get_args();
    if (isset($args[0])) {
      $this->setSearchGroup($args[0]);
    }
    return $this;
  }
  
  /**
   * Associate a LIMIT/OFFSET expression with SObject in SOSL RETURNING clause.
   *
   * @param string $sobjectName
   * @param int $limit
   * @param int $offset
   *
   * @return SalesforceSoapApi_Sosl_Statement.
   */
  public function limit() {
    $args = func_get_args();
    isset($args[0]) ? $this->setLimit($args[0]) : NULL;
    isset($args[1]) ? $this->setOffset($args[1]) : NULL;
    return $this;
  }
  
  /**
   * Associate an ORDER BY clause with SObject in SOSL RETURNING clause.
   *
   * Example: order_by('Custom_Object__c', 'Name', 'Custom_Field__c DESC')
   *
   * @param string $sobjectName
   * @param string $fieldList
   *
   * @return SalesforceSoapApi_Sosl_Statement.
   */
  public function order_by() {
    $OrderByExpression = NULL;
    foreach(func_get_args() as $key => $arg) {
      $OrderByExpression .= $arg;
    }
    $this->setOrderByExpression($OrderByExpression);
    return $this;
  }
  
  /**
   * Associate field list with SObject in SOSL RETURNING clause.
   *
   * Example: returning('Custom_Object__c', 'Id', 'Name', 'Custom_Field__c', ...)
   *
   * @param string $sobjectName
   * @param string $fieldName
   *
   * @return SalesforceSoapApi_Sosl_Statement.
   */
  public function returning() {
    $args = func_get_args();
    $sobjectName = array_shift($args);
    $this->setReturningClause($sobjectName, $args);
    return $this;
  }
  
  /**
   * Associate a WHERE clause with SObject in SOSL RETURNING clause.
   *
   * Do not include WHERE keyword. Example: where("FirstName = 'John' AND LastName = 'Doe'")
   *
   * @param string $sobjectName
   * @param string $whereClasue
   *
   * @return SalesforceSoapApi_Sosl_Statement.
   */
  public function where() {
    
    $WhereCondition = NULL;
    
    foreach(func_get_args() as $key => $arg) {
      try{
        $strvalue = AblePolecat_Data_Primitive_Scalar_String::typeCast($arg);
        !isset($WhereCondition) ? $WhereCondition = array() : NULL;
        $WhereCondition[] = $strvalue;
      }
      catch (AblePolecat_Data_Exception $Exception) {
        throw new AblePolecat_QueryLanguage_Exception(
          sprintf("%s WHERE parameter must be scalar or implement __toString(). %s passed.", 
            get_class($this), 
            AblePolecat_Data::getDataTypeName($arg)
          ), 
          AblePolecat_Error::INVALID_TYPE_CAST
        );
      }
    }
    $this->setWhereCondition(implode(' ', $WhereCondition));
    return $this;
  }
  
  /********************************************************************************
   * Helper functions.
   ********************************************************************************/
  
  /**
   * @return string QueryObject field list.
   */
  public function getFields() {
    return $this->getPropertyValue(AblePolecat_QueryLanguage_StatementInterface::FIELDS, NULL);
  }
  
  /**
   * @return string LIMIT.
   */
  public function getLimit() {
    return $this->getPropertyValue(AblePolecat_QueryLanguage_StatementInterface::LIMIT, NULL);
  }
  
  /**
   * @return string Proper syntax for LIMIT/OFFSET.
   */
  public function getLimitOffsetSyntax() {
    
    $Syntax = NULL;
    $Limit = $this->getLimit();
    $Offset = $this->getOffset();
    if (isset($Limit)) {
      $Syntax = "LIMIT $Limit";
      isset($Offset) ? $Syntax .= " OFFSET $Offset" : NULL;
    }
    return $Syntax;
  }
  
  /**
   * @return string OFFSET.
   */
  public function getOffset() {
    return $this->getPropertyValue(AblePolecat_QueryLanguage_StatementInterface::OFFSET, NULL);
  }
  
  /**
   * @return string ORDER BY expression.
   */
  public function getOrderByExpression() {
    return $this->getPropertyValue(AblePolecat_QueryLanguage_StatementInterface::ORDERBY, NULL);
  }
  
  /**
   * @return string WHERE condition.
   */
  public function getWhereCondition() {
    return $this->getPropertyValue(AblePolecat_QueryLanguage_StatementInterface::WHERE, NULL);
  }
  
  /**
   * Set LIMIT.
   *
   * @param string $Limit LIMIT.
   *
   * @throw AblePolecat_QueryLanguage_Exception if syntax is not supported.
   */
  public function setLimit($Limit) {
    
    $DmlOp = $this->getDmlOp();
    $Element = AblePolecat_QueryLanguage_StatementInterface::LIMIT;
    if (self::supportsSyntax($DmlOp, $Element)) {
      parent::__set($Element, intval($Limit));
    }
    else {
      throw new AblePolecat_QueryLanguage_Exception("Invalid SOSL Syntax [$DmlOp | $Element].",
        AblePolecat_Error::INVALID_SYNTAX);
    }
  }
  
  /**
   * Set OFFSET.
   *
   * @param string $Offset OFFSET.
   *
   * @throw AblePolecat_QueryLanguage_Exception if syntax is not supported.
   */
  public function setOffset($Offset) {
    
    $DmlOp = $this->getDmlOp();
    $Element = AblePolecat_QueryLanguage_Statement_Sql_Interface::OFFSET;
    if (self::supportsSyntax($DmlOp, $Element)) {
      parent::__set($Element, intval($Offset));
    }
    else {
      throw new AblePolecat_QueryLanguage_Exception("Invalid SOSL Syntax [$DmlOp | $Element].",
        AblePolecat_Error::INVALID_SYNTAX);
    }
  }
  
  /**
   * Associate ORDER BY expression with SObject in SOSL RETURNING clause.
   *
   * Example: returning('Custom_Object__c', array('Id', 'Name', 'Custom_Field__c DESC',))
   *
   * @param string $sobjectName
   * @param array $OrderByExpression
   *
   * @throw AblePolecat_QueryLanguage_Exception if syntax is not supported.
   */
  public function setOrderByExpression($sobjectName, $OrderByExpression) {
    
    if (isset($sobjectName) && is_string($sobjectName)) {
      $returnObjects = $this->getPropertyValue(AblePolecat_QueryLanguage_StatementInterface::QUERYOBJECT, NULL);
      if (!isset($returnObjects)) {
        $returnObjects = array();
      }
      if (!isset($returnObjects[$sobjectName])) {
        $returnObjects[$sobjectName] = array();
      }
      AblePolecat_QueryLanguage_StatementInterface::ORDERBY;
      // parent::__set(AblePolecat_QueryLanguage_StatementInterface::QUERYOBJECT, $returnObjects);
    }
    if (self::supportsSyntax($DmlOp, $Element)) {
      parent::__set($Element, strval($OrderByExpression));
    }
    else {
      throw new AblePolecat_QueryLanguage_Exception(sprintf("ORDER BY clause associated SObject name must be string. %s given",
        AblePolecat_Data::getDataTypeName($sobjectName)),
        AblePolecat_Error::INVALID_SYNTAX
      );
    }
  }
  
  /**
   * Associate field list with SObject in SOSL RETURNING clause.
   *
   * Example: returning('Custom_Object__c', array('Id', 'Name', 'Custom_Field__c',))
   *
   * @param string $sobjectName
   * @param array $fieldList
   *
   * @throw AblePolecat_QueryLanguage_Exception if syntax is not supported.
   */
  public function setReturningClause($sobjectName, $fieldList = NULL) {
    
    if (isset($sobjectName) && is_string($sobjectName)) {
      $returnObjects = $this->getPropertyValue(AblePolecat_QueryLanguage_StatementInterface::QUERYOBJECT, NULL);
      if (!isset($returnObjects)) {
        $returnObjects = array();
      }
      if (!isset($returnObjects[$sobjectName])) {
        $returnObjects[$sobjectName] = array();
      }      
      // parent::__set(AblePolecat_QueryLanguage_StatementInterface::QUERYOBJECT, $returnObjects);
      $this->appendToReturningClauseForObject($sobjectName, AblePolecat_QueryLanguage_StatementInterface::FIELDS, $fieldList);
    }
    else {
      throw new AblePolecat_QueryLanguage_Exception(sprintf("SObject name in RETURNING clause must be string. %s given",
        AblePolecat_Data::getDataTypeName($sobjectName)),
        AblePolecat_Error::INVALID_SYNTAX
      );
    }
  }
  
  /**
   * Set SOSL search group.
   *
   * @param string $searchGroup
   *
   * @throw AblePolecat_QueryLanguage_Exception if syntax is not supported.
   */
  public function setSearchGroup($searchGroup) {
    
    if (is_string($searchGroup)) {
      switch($searchGroup) {
        case self::SEARCH_GROUP_ALL:
        case self::SEARCH_GROUP_NAME:
        case self::SEARCH_GROUP_EMAIL:
        case self::SEARCH_GROUP_PHONE:
        case self::SEARCH_GROUP_SIDEBAR:
          parent::__set(self::SEARCH_GROUP, $searchGroup);
          break;
      }
    }
    else {
      throw new AblePolecat_QueryLanguage_Exception("Search group must be \
        ALL FIELDS | NAME FIELDS | EMAIL FIELDS | PHONE FIELDS | SIDEBAR FIELDS.",
        AblePolecat_Error::INVALID_SYNTAX);
    }
  }
  
  /**
   * Set SOSL search query.
   *
   * @param string $searchQuery
   *
   * @throw AblePolecat_QueryLanguage_Exception if syntax is not supported.
   */
  public function setSearchQuery($searchQuery) {
    
    if (is_scalar($searchQuery)) {
      parent::__set(self::SEARCH_QUERY, strval($searchQuery));
    }
    else {
      throw new AblePolecat_QueryLanguage_Exception(sprintf("Search query must be scalar. %s given.",
        AblePolecat_Data::getDataTypeName($searchQuery)),
        AblePolecat_Error::INVALID_SYNTAX
      );
    }
  }
  
  /**
   * Set WHERE condition.
   *
   * @param string $WhereCondition WHERE condition.
   *
   * @throw AblePolecat_QueryLanguage_Exception if syntax is not supported.
   */
  public function setWhereCondition($WhereCondition) {
    
    $DmlOp = $this->getDmlOp();
    $Element = AblePolecat_QueryLanguage_StatementInterface::WHERE;
    if (self::supportsSyntax($DmlOp, $Element)) {
      parent::__set($Element, strval($WhereCondition));
    }
    else {
      throw new AblePolecat_QueryLanguage_Exception("Invalid SOSL Syntax [$DmlOp | $Element].",
        AblePolecat_Error::INVALID_SYNTAX);
    }
  }
  
  /**
   * Add given scalar element(s) to sub-query associated with given SObject.
   * 
   * @param string $sobjectName
   * @param string $subQueryElementName self::FIELDS ~ SELF::WHERE ~ SELF::ORDERBY ~ SELF::LIMIT
   * @param mixed $subQueryTerms Array or string.
   */
  protected function appendToReturningClauseForObject($sobjectName, $subQueryElementName, $subQueryTerms) {
    $returnObjects = $this->getPropertyValue(AblePolecat_QueryLanguage_StatementInterface::QUERYOBJECT, NULL);
    if (isset($returnObjects) && isset($returnObjects[$sobjectName])) {
      switch($subQueryElementName) {
        default:
          throw new AblePolecat_QueryLanguage_Exception(sprintf("%s is not valid in SOSL RETURNING clause.", $subQueryElementName),
            AblePolecat_Error::INVALID_SYNTAX);
          break;
        case self::FIELDS:
        case self::WHERE:
        case self::ORDERBY:
        case self::LIMIT:          
          if (!isset($returnObjects[$sobjectName][$subQueryElementName])) {
            $returnObjects[$sobjectName][$subQueryElementName] = array();
            if (isset($subQueryTerms)) {
              if(is_array($subQueryTerms)) {
                foreach($subQueryTerms as $key => $term) {
                  if (is_scalar($term)) {
                    $returnObjects[$sobjectName][$subQueryElementName][] = $term;
                  }
                  else {
                    throw new AblePolecat_QueryLanguage_Exception(sprintf("Sub query term in RETURNING clause must be string. %s given",
                      AblePolecat_Data::getDataTypeName($term)),
                      AblePolecat_Error::INVALID_SYNTAX
                    );
                  }
                }
              }
              else {
                if (is_scalar($subQueryTerms)) {
                  $returnObjects[$sobjectName][$subQueryElementName][] = $subQueryTerms;
                }
                else {
                  throw new AblePolecat_QueryLanguage_Exception(sprintf("Sub query term in RETURNING clause must be string. %s given",
                    AblePolecat_Data::getDataTypeName($subQueryTerms)),
                    AblePolecat_Error::INVALID_SYNTAX
                  );
                }
              }
            }
          }
          default:
      }
      // parent::__set(AblePolecat_QueryLanguage_StatementInterface::QUERYOBJECT, $returnObjects);
    }
    else {
      throw new AblePolecat_QueryLanguage_Exception(sprintf("%s not defined in RETURNING clause", $sobjectName),
      AblePolecat_Error::INVALID_SYNTAX);
    }
  }
  
  /**
   * Extends __construct().
   *
   * Sub-classes should override to initialize arguments.
   */
  protected function initialize() {    
    //
    // Initialize SOSL support settings (for static method calls).
    //
    self::$supportedSosl = array(
      self::FIND => array(
        self::IN => TRUE,
        self::RETURNING => TRUE,
        self::WHERE => TRUE,
        self::ORDERBY => TRUE,
        self::LIMIT => TRUE,
        self::OFFSET => TRUE,
      ),
    );
  }
}