<?php
/**
 * @package   Salesforce.com SOAP API module for Able Polecat.
 * @file      AblePolecat-Mod-SalesforceSoapApi/usr/src/QueryLanguage/Statement/Sosl.php
 * @brief     Encapsulates SOSL statement.
 *
 * @todo: SOSL [ WITH DivisionFilter ]
 * @todo: SOSL [ WITH DATA CATEGORY DataCategorySpec ]
 * @todo: SOSL [ WITH SNIPPET[(target_length=n)] ]
 * @todo: SOSL [ WITH NETWORK NetworkIdSpec ]
 * @todo: SOSL [ UPDATE [TRACKING], [VIEWSTAT] ]
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
  const SCOPE_ENTIRE_QUERY    = 'ENTIRE QUERY';
  
  const SOSL_SYNTAX = 'FIND {SearchQuery} \n \
    [ IN SearchGroup [ convertCurrency(Amount)] ] \n \
    [ RETURNING FieldSpec [ toLabel(fields)] ] \n \
    [ WITH DivisionFilter ] \n \
    [ WITH DATA CATEGORY DataCategorySpec ] \n \
    [ WITH SNIPPET[(target_length=n)] ] \n \
    [ WITH NETWORK NetworkIdSpec ] \n \
    [ LIMIT n ] \n \
    [ OFFSET n ] \n \
    [ UPDATE [TRACKING], [VIEWSTAT] ]';
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
  
  /**
   * @var Array Internal pointer to most recently defined RETURNING clause.
   */
  private $currentReturningClause;
  
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
    
    $tokens = array();
    
    //
    // FIND
    //
    $tokens[] = sprintf("FIND {%s}",
      $this->getSearchQuery()
    );
    
    //
    // IN
    //
    $searchGroup = $this->getSearchGroup();
    isset($searchGroup) ? $tokens[] = sprintf("IN %s", $searchGroup) : NULL;
    
    //
    // RETURNING
    //
    $returningClause = $this->getReturningClause();
    isset($returningClause) ? $tokens[] = sprintf("RETURNING %s", $returningClause) : NULL;
    
    //
    // Entire query limit
    //
    $entireQueryLimit = $this->getLimitOffsetSyntax();
    isset($entireQueryLimit) ? $tokens[] = $entireQueryLimit : NULL;
    
    return implode(' ', $tokens);
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
   * @param string $searchQuery The text to search for.
   *
   * @return SalesforceSoapApi_Sosl_Statement.
   */
  public function _and() {
    $args = func_get_args();
    if (isset($args[0])) {
      $this->appendSearchQuery($args[0], 'AND');
    }
    return $this;
  }
  
  /**
   * @param string $searchQuery The text to search for.
   *
   * @return SalesforceSoapApi_Sosl_Statement.
   */
  public function _and_not() {
    $args = func_get_args();
    if (isset($args[0])) {
      $this->appendSearchQuery($args[0], 'AND NOT');
    }
    return $this;
  }
  
  /**
   * @param string $searchQuery The text to search for.
   *
   * @return SalesforceSoapApi_Sosl_Statement.
   */
  public function _or() {
    $args = func_get_args();
    if (isset($args[0])) {
      $this->appendSearchQuery($args[0], 'OR');
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
    isset($args[2]) ? $scope = $args[2] : $scope = $this->currentReturningClause;
    isset($args[0]) ? $this->setLimit($args[0], $scope) : NULL;
    isset($args[1]) ? $this->setOffset($args[1], $scope) : NULL;
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
    
    $orderByTokens = array();
    
    foreach(func_get_args() as $key => $arg) {
      switch (strtoupper($arg)) {
        default:
          $orderByTokens[] = $arg;
          break;
        case 'ASC':
        case 'DESC':
          if (isset($orderByTokens[$key - 1])) {
            $token = $orderByTokens[$key - 1] . ' ' . $arg;
            $orderByTokens[$key - 1] = $token;
          }
          break;
      }
    }
    $OrderByExpression = implode(', ', $orderByTokens);
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
    $args = func_get_args();
    isset($args[0]) ? $Limit = $args[0] : $Limit = $this->getLimit();
    isset($args[1]) ? $Offset = $args[1] : $Offset = NULL;
    
    if (isset($Limit)) {
      $Syntax = "LIMIT $Limit";
      isset($Offset) ? $Syntax .= " OFFSET $Offset" : NULL;
    }
    return $Syntax;
  }
  
  /**
   * @return string Search query (text following FIND keyword).
   */
  public function getSearchQuery() {
    return $this->getPropertyValue(SalesforceSoapApi_Sosl_StatementInterface::SEARCH_QUERY);
  }
  
  /**
   * @param string ALL FIELDS | NAME FIELDS | EMAIL FIELDS | PHONE FIELDS | SIDEBAR FIELDS
   */
  public function getSearchGroup() {
    return $this->getPropertyValue(SalesforceSoapApi_Sosl_StatementInterface::SEARCH_GROUP);
  }
  
  /**
   * Return the tokens in the RETURNING clause(es) OR build SOSL RETURNING clause.
   *
   * @param bool $asStr IF TRUE, build SOSL RETURNING clause and return string, otherwise return tokens.
   *
   * @return mixed
   */
  public function getReturningClause($asStr = TRUE) {
    $returningClause = '';
    $returnObjects = $this->getPropertyValue(AblePolecat_QueryLanguage_StatementInterface::QUERYOBJECT);
    if ($asStr && isset($returnObjects)) {
      $returningClauseSubqueries = array();
      foreach($returnObjects as $objectName => $objectTokens) {
        $returningClauseSubquery = $objectName;
        $fieldsExpression = '';
        $whereExpression = '';
        $orderByExpression = '';
        $limitExpression = '';
        if (isset($objectTokens[self::FIELDS])) {
          $fieldsExpression = implode(', ', $objectTokens[self::FIELDS]);
        }
        if (isset($objectTokens[self::WHERE]) && isset($objectTokens[self::WHERE][0])) {
          $whereExpression = sprintf(" WHERE %s", $objectTokens[self::WHERE][0]);
        }
        if (isset($objectTokens[self::ORDERBY]) && isset($objectTokens[self::ORDERBY][0])) {
          $orderByExpression = sprintf(" ORDER BY %s", $objectTokens[self::ORDERBY][0]);
        }
        if (isset($objectTokens[self::LIMIT]) && isset($objectTokens[self::LIMIT][0])) {
          $limit = $objectTokens[self::LIMIT][0];
          $offset = NULL;
          if (isset($objectTokens[self::OFFSET]) && isset($objectTokens[self::OFFSET][0])) {
            $offset = $objectTokens[self::OFFSET][0];
          }
          $limitExpression = ' ' . $this->getLimitOffsetSyntax($limit, $offset);
        }
        $returningClauseSubquery .= sprintf("(%s%s%s%s)", 
          $fieldsExpression, 
          $whereExpression,
          $orderByExpression,
          $limitExpression
        );
        $returningClauseSubqueries[] = $returningClauseSubquery;
      }
      $returningClause = implode(', ', $returningClauseSubqueries);
    }
    else {
      $returningClause = $returnObjects;
    }
    return $returningClause;
  }
    
  /**
   * Set LIMIT.
   *
   * @param string $Limit LIMIT.
   *
   * @throw AblePolecat_QueryLanguage_Exception if syntax is not supported.
   */
  public function setLimit($Limit) {
    
    //
    // throw exception if syntax is out of order.
    //
    $returnObjects = $this->getPropertyValue(AblePolecat_QueryLanguage_StatementInterface::QUERYOBJECT);
    if (!isset($returnObjects)) {
      throw new AblePolecat_QueryLanguage_Exception(sprintf("SOSL syntax error. %s. SOSL syntax is %s.",
        'LIMIT, OFFSET clause must be preceded by RETURNING',
        SalesforceSoapApi_Sosl_StatementInterface::SOSL_SYNTAX
      ));
    }
    
    $args = func_get_args();
    isset($args[1]) ? $scope = $args[1] : $scope = self::SCOPE_ENTIRE_QUERY;
    if ($this->currentReturningClause == $scope) {
      $this->appendToReturningClauseForObject($scope, AblePolecat_QueryLanguage_StatementInterface::LIMIT, $Limit);
    }
    else {
      parent::__set(AblePolecat_QueryLanguage_StatementInterface::LIMIT, $Limit);
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
    
    //
    // throw exception if syntax is out of order.
    //
    $returnObjects = $this->getPropertyValue(AblePolecat_QueryLanguage_StatementInterface::QUERYOBJECT);
    if (!isset($returnObjects)) {
      throw new AblePolecat_QueryLanguage_Exception(sprintf("SOSL syntax error. %s. SOSL syntax is %s.",
        'LIMIT, OFFSET clause must be preceded by RETURNING',
        SalesforceSoapApi_Sosl_StatementInterface::SOSL_SYNTAX
      ));
    }
    
    $args = func_get_args();
    isset($args[1]) ? $scope = $args[1] : $scope = self::SCOPE_ENTIRE_QUERY;
    if ($this->currentReturningClause == $scope) {
      if (!isset($returnObjects[$scope][AblePolecat_QueryLanguage_StatementInterface::LIMIT])) {
        throw new AblePolecat_QueryLanguage_Exception(sprintf("SOSL syntax error. %s. SOSL syntax is %s.",
          'Cannot declare OFFSET without LIMIT',
          SalesforceSoapApi_Sosl_StatementInterface::SOSL_SYNTAX
        ));
      }
      $this->appendToReturningClauseForObject($scope, AblePolecat_QueryLanguage_StatementInterface::OFFSET, $Offset);
    }
    else {
      throw new AblePolecat_QueryLanguage_Exception(sprintf("SOSL syntax error. %s. SOSL syntax is %s.",
        'Cannot declare OFFSET in LIMIT clause for entire SOSL query',
        SalesforceSoapApi_Sosl_StatementInterface::SOSL_SYNTAX
      ));
    }
  }
  
  /**
   * Associate ORDER BY expression with SObject in SOSL RETURNING clause.
   *
   * Example: returning('Custom_Object__c', array('Id', 'Name', 'Custom_Field__c DESC',))
   *
   * @param array $OrderByExpression
   *
   * @throw AblePolecat_QueryLanguage_Exception if syntax is not supported.
   */
  public function setOrderByExpression($OrderByExpression) {
    //
    // ORDER BY clause must be associated with a RETURNING clause field spec.
    // @see https://developer.salesforce.com/docs/atlas.en-us.soql_sosl.meta/soql_sosl/sforce_api_calls_sosl_returning.htm#topic-title
    //
    if (!isset($this->currentReturningClause)) {
      throw new AblePolecat_QueryLanguage_Exception(sprintf("SOSL syntax error. %s. SOSL syntax is %s.",
        'ORDER BY clause must be associated with a RETURNING clause field spec',
        SalesforceSoapApi_Sosl_StatementInterface::SOSL_SYNTAX
      ));
    }
    $this->appendToReturningClauseForObject($this->currentReturningClause, AblePolecat_QueryLanguage_StatementInterface::ORDERBY, $OrderByExpression);
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
    
    //
    // throw exception if syntax is out of order.
    //
    $searchQuery = $this->getPropertyValue(SalesforceSoapApi_Sosl_StatementInterface::SEARCH_QUERY);
    if (!isset($searchQuery)) {
      throw new AblePolecat_QueryLanguage_Exception(sprintf("SOSL syntax error. %s. SOSL syntax is %s.",
        'RETURNING clause must be preceded by FIND',
        SalesforceSoapApi_Sosl_StatementInterface::SOSL_SYNTAX
      ));
    }
    
    if (isset($sobjectName) && is_string($sobjectName)) {
      $returnObjects = $this->getPropertyValue(AblePolecat_QueryLanguage_StatementInterface::QUERYOBJECT, NULL);
      if (!isset($returnObjects)) {
        parent::__set(AblePolecat_QueryLanguage_StatementInterface::QUERYOBJECT, array($sobjectName => array()));
      }
      if (!isset($returnObjects[$sobjectName])) {
        $returnObjects[$sobjectName] = array();
        parent::__set(AblePolecat_QueryLanguage_StatementInterface::QUERYOBJECT, $returnObjects);
      }
      $this->appendToReturningClauseForObject($sobjectName, AblePolecat_QueryLanguage_StatementInterface::FIELDS, $fieldList);
      
      //
      // Internal pointer saves name of object associated with most recently
      // defined RETURNING clause in case LIMIT, WHERE, etc expressions follow.
      //
      $this->currentReturningClause = $sobjectName;
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
    
    //
    // throw exception if syntax is out of order.
    //
    $searchQuery = $this->getPropertyValue(SalesforceSoapApi_Sosl_StatementInterface::SEARCH_QUERY);
    if (!isset($searchQuery)) {
      throw new AblePolecat_QueryLanguage_Exception(sprintf("SOSL syntax error. %s. SOSL syntax is %s.",
        'IN clause must be preceded by FIND',
        SalesforceSoapApi_Sosl_StatementInterface::SOSL_SYNTAX
      ));
    }
    
    if (is_string($searchGroup)) {
      $searchGroupErrorMessage = "Search group must be \
        ALL FIELDS | NAME FIELDS | EMAIL FIELDS | PHONE FIELDS | SIDEBAR FIELDS.";
      switch($searchGroup) {
        default:
          throw new AblePolecat_QueryLanguage_Exception($searchGroupErrorMessage, AblePolecat_Error::INVALID_SYNTAX);
          break;
        case SalesforceSoapApi_Sosl_StatementInterface::SEARCH_GROUP_ALL:
        case SalesforceSoapApi_Sosl_StatementInterface::SEARCH_GROUP_NAME:
        case SalesforceSoapApi_Sosl_StatementInterface::SEARCH_GROUP_EMAIL:
        case SalesforceSoapApi_Sosl_StatementInterface::SEARCH_GROUP_PHONE:
        case SalesforceSoapApi_Sosl_StatementInterface::SEARCH_GROUP_SIDEBAR:
          parent::__set(SalesforceSoapApi_Sosl_StatementInterface::SEARCH_GROUP, $searchGroup);
          break;
      }
    }
    else {
      throw new AblePolecat_QueryLanguage_Exception($searchGroupErrorMessage, AblePolecat_Error::INVALID_SYNTAX);
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
      parent::__set(SalesforceSoapApi_Sosl_StatementInterface::SEARCH_QUERY, strval($searchQuery));
    }
    else {
      throw new AblePolecat_QueryLanguage_Exception(sprintf("Search query must be scalar. %s given.",
        AblePolecat_Data::getDataTypeName($searchQuery)),
        AblePolecat_Error::INVALID_SYNTAX
      );
    }
  }
  
  /**
   * Append term(s) to the SOSL search query.
   *
   * @param string $searchQuery
   * @param string $conjunction AND | OR | AND NOT | NULL
   *
   * @throw AblePolecat_QueryLanguage_Exception if syntax is not supported.
   */
  public function appendSearchQuery($searchQuery, $conjunction) {
    
    $conjunction = strtoupper($conjunction);
    if (is_scalar($searchQuery)) {
      //
      // throw exception if syntax is out of order.
      //
      $appendQuery = $this->getPropertyValue(SalesforceSoapApi_Sosl_StatementInterface::SEARCH_QUERY);
      switch ($conjunction) {
        default:
          $appendQuery .= ' ' . $searchQuery;
          break;
        case 'AND':
        case 'AND NOT':
        case 'OR':
          if (!isset($appendQuery)) {
            throw new AblePolecat_QueryLanguage_Exception(sprintf("SOSL syntax error. %s. SOSL syntax is %s.",
              sprintf("%s clause must be preceded by FIND", $conjunction),
              SalesforceSoapApi_Sosl_StatementInterface::SOSL_SYNTAX
            ));
          }
          $appendQuery .= ' ' . implode(' ', array($conjunction, $searchQuery));
          break;
      }
      parent::__set(SalesforceSoapApi_Sosl_StatementInterface::SEARCH_QUERY, strval($appendQuery));
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
    
    //
    // WHERE clause must be associated with a RETURNING clause field spec.
    // @see https://developer.salesforce.com/docs/atlas.en-us.soql_sosl.meta/soql_sosl/sforce_api_calls_sosl_returning.htm#topic-title
    //
    if (!isset($this->currentReturningClause)) {
      throw new AblePolecat_QueryLanguage_Exception(sprintf("SOSL syntax error. %s. SOSL syntax is %s.",
        'WHERE clause must be associated with a RETURNING clause field spec',
        SalesforceSoapApi_Sosl_StatementInterface::SOSL_SYNTAX
      ));
    }
    $this->appendToReturningClauseForObject($this->currentReturningClause, AblePolecat_QueryLanguage_StatementInterface::WHERE, $WhereCondition);
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
        case self::OFFSET:
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
      parent::__set(AblePolecat_QueryLanguage_StatementInterface::QUERYOBJECT, $returnObjects);
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
    $this->currentReturningClause = NULL;
  }
}