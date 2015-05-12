<?php
/**
 * @file      AblePolecat-Mod-SalesforceSoapApi/usr/share/test/QueryLanguage/Statement/Sosl.php
 * @brief     Unit tests for SalesforceSoapApi_Sosl_Statement.
 * 
 * @author    Karl Kuhrman
 * @copyright [BDS II License] (https://github.com/kkuhrman/AblePolecat/blob/master/LICENSE.md)
 * @version   0.7.0
 */

require_once(implode(DIRECTORY_SEPARATOR, array(ABLE_POLECAT_CORE, 'UnitTest.php')));
require_once(implode(DIRECTORY_SEPARATOR, array(ABLEPOLECAT_MOD_SALESFORCESOAPAPI_SRC_PATH, 'QueryLanguage', 'Statement', 'Soql.php')));

class SalesforceSoapApiTest_QueryLanguage_Statement_Sosl extends AblePolecat_UnitTest implements AblePolecat_UnitTestInterface {
  
  const TEST_SUBJECT = 'SalesforceSoapApi_Sosl_Statement';
  const TEST_SEARCH_QUERY = 'find this phrase';
  const TEST_APPEND_PHRASE = 'find another phrase';
  
  /**
   * Run all the tests in the class.
   *
   * @throw AblePolecat_UnitTest_Exception if any test fails.
   */
  public static function runTests() {
    self::test__SOSL();
    self::test_find();
    self::test_and();
    self::test_and_not();
    self::test_or();
    self::test_in();
    self::test_returning();
    self::test_where();
    self::test_order_by();
    self::test_limit();
    self::test__toString();
  }
  
  public static function test__SOSL() {
    $sosl = __SOSL();
    if (is_a($sosl, 'SalesforceSoapApi_Sosl_Statement')) {
      self::setTestResult(__METHOD__, TRUE);
    }
    else {
      $Exception = self::createMismatchException(self::TEST_SUBJECT, '__SOSL', 'SalesforceSoapApi_Sosl_Statement', AblePolecat_Data::getDataTypeName($sosl));
      self::setTestResult(__METHOD__, FALSE, $Exception);
    }
  }
  
  public static function test_find() {
    
    $sosl = __SOSL()->find(self::TEST_SEARCH_QUERY);
    $searchQuery = $sosl->getSearchQuery();
    if ($searchQuery == self::TEST_SEARCH_QUERY) {
      self::setTestResult(__METHOD__, TRUE);
    }
    else {
      $Exception = self::createMismatchException(self::TEST_SUBJECT, 'find', self::TEST_SEARCH_QUERY, $searchQuery);
      self::setTestResult(__METHOD__, FALSE, $Exception);
    }
  }
  
  public static function test_and() {
    
    //
    // Test incorrect syntax.
    //
    $endTest = FALSE;
    try {
      $sosl = __SOSL()->_and(self::TEST_APPEND_PHRASE)->find(self::TEST_SEARCH_QUERY);
      $Exception = new AblePolecat_UnitTest_Exception('_and() called before find() should throw exception.');
      self::setTestResult(__METHOD__, FALSE, $Exception);
      $endTest = TRUE;
    }
    catch(AblePolecat_QueryLanguage_Exception $Exception) {
    }
    
    //
    // Verify expected result.
    //
    if (!$endTest) {
      $sosl = __SOSL()->find(self::TEST_SEARCH_QUERY)->_and(self::TEST_APPEND_PHRASE);
      $searchQuery = $sosl->getSearchQuery();
      $expectedResult = implode(' ', array(self::TEST_SEARCH_QUERY, 'AND', self::TEST_APPEND_PHRASE));
      if ($searchQuery == $expectedResult) {
        self::setTestResult(__METHOD__, TRUE);
      }
      else {
        $Exception = self::createMismatchException(self::TEST_SUBJECT, '_and', $expectedResult, $searchQuery);
        self::setTestResult(__METHOD__, FALSE, $Exception);
      }
    }
  }
  
  public static function test_and_not() {
    
    //
    // Test incorrect syntax.
    //
    $endTest = FALSE;
    try {
      $sosl = __SOSL()->_and_not(self::TEST_APPEND_PHRASE)->find(self::TEST_SEARCH_QUERY);
      $Exception = new AblePolecat_UnitTest_Exception('_and_not() called before find() should throw exception.');
      self::setTestResult(__METHOD__, FALSE, $Exception);
      $endTest = TRUE;
    }
    catch(AblePolecat_QueryLanguage_Exception $Exception) {
    }
    
    //
    // Verify expected result.
    //
    if (!$endTest) {
      $sosl = __SOSL()->find(self::TEST_SEARCH_QUERY)->_and_not(self::TEST_APPEND_PHRASE);
      $searchQuery = $sosl->getSearchQuery();
      $expectedResult = implode(' ', array(self::TEST_SEARCH_QUERY, 'AND NOT', self::TEST_APPEND_PHRASE));
      if ($searchQuery == $expectedResult) {
        self::setTestResult(__METHOD__, TRUE);
      }
      else {
        $Exception = self::createMismatchException(self::TEST_SUBJECT, '_and_not', $expectedResult, $searchQuery);
        self::setTestResult(__METHOD__, FALSE, $Exception);
      }
    }
  }
  
  public static function test_or() {
    
    //
    // Test incorrect syntax.
    //
    $endTest = FALSE;
    try {
      $sosl = __SOSL()->_or(self::TEST_APPEND_PHRASE)->find(self::TEST_SEARCH_QUERY);
      $Exception = new AblePolecat_UnitTest_Exception('_or() called before find() should throw exception.');
      self::setTestResult(__METHOD__, FALSE, $Exception);
      $endTest = TRUE;
    }
    catch(AblePolecat_QueryLanguage_Exception $Exception) {
    }
    
    //
    // Verify expected result.
    //
    if (!$endTest) {
      $sosl = __SOSL()->find(self::TEST_SEARCH_QUERY)->_or(self::TEST_APPEND_PHRASE);
      $searchQuery = $sosl->getSearchQuery();
      $expectedResult = implode(' ', array(self::TEST_SEARCH_QUERY, 'OR', self::TEST_APPEND_PHRASE));
      if ($searchQuery == $expectedResult) {
        self::setTestResult(__METHOD__, TRUE);
      }
      else {
        $Exception = self::createMismatchException(self::TEST_SUBJECT, '_or', $expectedResult, $searchQuery);
        self::setTestResult(__METHOD__, FALSE, $Exception);
      }
    }
  }
  
  public static function test_in() {
    
    //
    // Test incorrect syntax.
    //
    $endTest = FALSE;
    try {
      $sosl = __SOSL()->in(SalesforceSoapApi_Sosl_StatementInterface::SEARCH_GROUP_ALL)->find(self::TEST_SEARCH_QUERY);
      $Exception = new AblePolecat_UnitTest_Exception('in() called before find() should throw exception.');
      self::setTestResult(__METHOD__, FALSE, $Exception);
      $endTest = TRUE;
    }
    catch(AblePolecat_QueryLanguage_Exception $Exception) {
    }
    try {
      $sosl = __SOSL()->find(self::TEST_SEARCH_QUERY)->in('incorrect syntax');
      $Exception = new AblePolecat_UnitTest_Exception('in() called before find() should throw exception.');
      self::setTestResult(__METHOD__, FALSE, $Exception);
      $endTest = TRUE;
    }
    catch(AblePolecat_QueryLanguage_Exception $Exception) {
    }
    
    //
    // Verify expected results.
    //
    if (!$endTest) {
      $sosl = __SOSL()->find(self::TEST_SEARCH_QUERY)->in(SalesforceSoapApi_Sosl_StatementInterface::SEARCH_GROUP_ALL);
      $searchGroup = $sosl->getSearchGroup();
      if ($searchGroup == SalesforceSoapApi_Sosl_StatementInterface::SEARCH_GROUP_ALL) {
        self::setTestResult(__METHOD__, TRUE);
      }
      else {
        $Exception = self::createMismatchException(self::TEST_SUBJECT, 'in', SalesforceSoapApi_Sosl_StatementInterface::SEARCH_GROUP_ALL, $searchGroup);
        self::setTestResult(__METHOD__, FALSE, $Exception);
      }
    }
  }
  
  public static function test_returning() {
    
    //
    // Test incorrect syntax.
    //
    $endTest = FALSE;
    try {
      $sosl = __SOSL()->returning(SalesforceSoapApi_Sosl_StatementInterface::SEARCH_GROUP_ALL)->find(self::TEST_SEARCH_QUERY);
      $Exception = new AblePolecat_UnitTest_Exception('returning() called before find() should throw exception.');
      self::setTestResult(__METHOD__, FALSE, $Exception);
      $endTest = TRUE;
    }
    catch(AblePolecat_QueryLanguage_Exception $Exception) {
    }
    try {
      $sosl = __SOSL()->find(self::TEST_SEARCH_QUERY)->returning(array('Account'));
      $Exception = new AblePolecat_UnitTest_Exception("Syntax other than returning('Custom_Object__c', 'Id', 'Name', 'Custom_Field__c', ...) should throw exception.");
      self::setTestResult(__METHOD__, FALSE, $Exception);
      $endTest = TRUE;
    }
    catch(AblePolecat_QueryLanguage_Exception $Exception) {
    }
    
    //
    // Verify expected results.
    //
    if (!$endTest) {
      $sosl = __SOSL()->find(self::TEST_SEARCH_QUERY)->returning('Account', 'Id', 'Name')->returning('Contact', 'FirstName', 'LastName');
      
      $returningClause = $sosl->getReturningClause();
      $ExpectedResult = 'Account(Id, Name), Contact(FirstName, LastName)';
      if ($returningClause == $ExpectedResult) {
        self::setTestResult(__METHOD__, TRUE);
      }
      else {
        $Exception = self::createMismatchException(self::TEST_SUBJECT, 'returning', $ExpectedResult, $returningClause);
        self::setTestResult(__METHOD__, FALSE, $Exception);
      }
    }
  }
  
  public static function test_where() {
    
    $testWhereClause = 'Account LIKE \'%Heat%\'';
    
    //
    // Test incorrect syntax.
    //
    $endTest = FALSE;
    try {
      $sosl = __SOSL()->find(self::TEST_SEARCH_QUERY)->where($testWhereClause);
      $Exception = new AblePolecat_UnitTest_Exception('where() called before returning() should throw exception.');
      self::setTestResult(__METHOD__, FALSE, $Exception);
      $endTest = TRUE;
    }
    catch(AblePolecat_QueryLanguage_Exception $Exception) {
    }
    
    //
    // Verify expected results.
    //
    if (!$endTest) {
      $sosl = __SOSL()->find(self::TEST_SEARCH_QUERY)->
        returning('Account', 'Id', 'Name')->where($testWhereClause)->limit(100);
      $returningClause = $sosl->getReturningClause();
      $ExpectedResult = "Account(Id, Name WHERE $testWhereClause LIMIT 100)";
      if ($returningClause == $ExpectedResult) {
        self::setTestResult(__METHOD__, TRUE);
      }
      else {
        $Exception = self::createMismatchException(self::TEST_SUBJECT, 'returning', $ExpectedResult, $returningClause);
        self::setTestResult(__METHOD__, FALSE, $Exception);
      }
    }
  }
  
  public static function test_order_by() {
    
    //
    // Test incorrect syntax.
    //
    $endTest = FALSE;
    try {
      $sosl = __SOSL()->find(self::TEST_SEARCH_QUERY)->order_by('Name', 'DESC');
      $Exception = new AblePolecat_UnitTest_Exception('order_by() called before returning() should throw exception.');
      self::setTestResult(__METHOD__, FALSE, $Exception);
      $endTest = TRUE;
    }
    catch(AblePolecat_QueryLanguage_Exception $Exception) {
    }
    
    //
    // Verify expected results.
    //
    if (!$endTest) {
      $sosl = __SOSL()->find(self::TEST_SEARCH_QUERY)->
        returning('Account', 'Id', 'Name')->order_by('Name', 'DESC', 'Id')->limit(100);
      $returningClause = $sosl->getReturningClause();
      $ExpectedResult = "Account(Id, Name ORDER BY Name DESC, Id LIMIT 100)";
      if ($returningClause == $ExpectedResult) {
        self::setTestResult(__METHOD__, TRUE);
      }
      else {
        $Exception = self::createMismatchException(self::TEST_SUBJECT, 'returning', $ExpectedResult, $returningClause);
        self::setTestResult(__METHOD__, FALSE, $Exception);
      }
    }
  }
  
  public static function test_limit() {
    
    //
    // Test incorrect syntax.
    //
    $endTest = FALSE;
    try {
      $sosl = __SOSL()->find(self::TEST_SEARCH_QUERY)->limit(100,0, SalesforceSoapApi_Sosl_StatementInterface::SCOPE_ENTIRE_QUERY);
      $Exception = new AblePolecat_UnitTest_Exception('limit() called before returning() should throw exception.');
      self::setTestResult(__METHOD__, FALSE, $Exception);
      $endTest = TRUE;
    }
    catch(AblePolecat_QueryLanguage_Exception $Exception) {
    }
    try {
      $sosl = __SOSL()->find(self::TEST_SEARCH_QUERY)->
        returning('Account', 'Id', 'Name')->limit(100, 100)->
        limit(300, -67, SalesforceSoapApi_Sosl_StatementInterface::SCOPE_ENTIRE_QUERY);
      $Exception = new AblePolecat_UnitTest_Exception('Setting OFFSET in LIMIT clause for entire SOSL query should throw exception.');
      self::setTestResult(__METHOD__, FALSE, $Exception);
      $endTest = TRUE;
    }
    catch(AblePolecat_QueryLanguage_Exception $Exception) {
    }
    
    //
    // Verify expected results.
    //
    if (!$endTest) {
      $sosl = __SOSL()->find(self::TEST_SEARCH_QUERY)->
        returning('Account', 'Id', 'Name')->limit(100, 100)->
        returning('Contact', 'FirstName', 'LastName')->limit(200, 200)->
        limit(300, NULL, SalesforceSoapApi_Sosl_StatementInterface::SCOPE_ENTIRE_QUERY);
      $returningClause = $sosl->getReturningClause();
      $ExpectedResult = 'Account(Id, Name LIMIT 100 OFFSET 100), Contact(FirstName, LastName LIMIT 200 OFFSET 200)';
      if (($returningClause == $ExpectedResult) && (300 == $sosl->getLimit())) {
        self::setTestResult(__METHOD__, TRUE);
      }
      else {
        $Exception = self::createMismatchException(self::TEST_SUBJECT, 'returning', $ExpectedResult, $returningClause);
        self::setTestResult(__METHOD__, FALSE, $Exception);
      }
    }
  }
  
  public static function test__toString() {
    
    $sosl = __SOSL()->find('Apple AND Banana AND NOT Orange OR Grape')->
      in(SalesforceSoapApi_Sosl_StatementInterface::SEARCH_GROUP_NAME)->
      returning('Account', 'Id', 'Name')->where('Name NOT LIKE \'%Tomato%\'')->order_by('Name', 'DESC')->limit(250)->
      returning('Contact', 'FirstName', 'LastName')->limit(200,1000)->
      limit(300, NULL, SalesforceSoapApi_Sosl_StatementInterface::SCOPE_ENTIRE_QUERY);
    $expectedQueryResult = 'FIND {Apple AND Banana AND NOT Orange OR Grape} IN NAME FIELDS RETURNING Account(Id, Name WHERE Name NOT LIKE \'%Tomato%\' ORDER BY Name DESC LIMIT 250), Contact(FirstName, LastName LIMIT 200 OFFSET 1000) LIMIT 300';
    $query = $sosl->__toString();
    // AblePolecat_Debug::kill($sosl);
    if ($query == $expectedQueryResult) {
        self::setTestResult(__METHOD__, TRUE);
      }
      else {
        $Exception = self::createMismatchException(self::TEST_SUBJECT, 'returning', $expectedQueryResult, $query);
        self::setTestResult(__METHOD__, FALSE, $Exception);
      }
  }
}