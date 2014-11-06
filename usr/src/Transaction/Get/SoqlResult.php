<?php
/**
 * @package   Salesforce.com SOAP API module for Able Polecat.
 * @file      Transaction/Get/SoqlResult.php
 * @brief     Able Polecat transaction processes SOAP request and returns resource.
 *
 * @author    Karl Kuhrman
 * @copyright [BDS II License] (https://github.com/kkuhrman/AblePolecat/blob/master/LICENSE.md)
 * @version   0.7.0
 */

require_once(implode(DIRECTORY_SEPARATOR, array(ABLE_POLECAT_CORE, 'Transaction', 'Get', 'Resource.php')));

class SalesforceSoapApi_Transaction_Get_SoqlResult extends AblePolecat_Transaction_Get_Resource {
  
  /**
   * Constants.
   */
  const UUID = 'fa8b8693-4401-11e4-b353-0050569e00a2';
  const NAME = 'GET SOQL Result';
  
  /**
   * @var AblePolecat_AccessControl_Agent_User Instance of singleton.
   */
  private static $Transaction;
  
  /********************************************************************************
   * Implementation of AblePolecat_AccessControl_ArticleInterface.
   ********************************************************************************/
  
  /**
   * Return unique, system-wide identifier for agent.
   *
   * @return string Transaction identifier.
   */
  public static function getId() {
    return self::UUID;
  }
  
  /**
   * Return common name for agent.
   *
   * @return string Transaction name.
   */
  public static function getName() {
    return self::NAME;
  }
  
  /********************************************************************************
   * Implementation of AblePolecat_CacheObjectInterface.
   ********************************************************************************/
  
  /**
   * Create a new instance of object or restore cached object to previous state.
   *
   * @param AblePolecat_AccessControl_SubjectInterface Session status helps determine if connection is new or established.
   *
   * @return AblePolecat_CacheObjectInterface Initialized server resource ready for business or NULL.
   */
  public static function wakeup(AblePolecat_AccessControl_SubjectInterface $Subject = NULL) {
    if (!isset(self::$Transaction)) {
      //
      // Unmarshall (from numeric keyed index to named properties) variable args list.
      //
      $ArgsList = self::unmarshallArgsList(__FUNCTION__, func_get_args());
      self::$Transaction = new SalesforceSoapApi_Transaction_Get_SoqlResult($ArgsList->getArgumentValue(self::TX_ARG_SUBJECT));
      self::prepare(self::$Transaction, $ArgsList, __FUNCTION__);
    }
    return self::$Transaction;
  }
  
  /********************************************************************************
   * Implementation of AblePolecat_TransactionInterface.
   ********************************************************************************/
  
  /**
   * Rollback
   */
  public function rollback() {
    //
    // @todo
    //
  }
  
  /**
   * Return the data model (resource) corresponding to a web request URI/path.
   *
   * @return AblePolecat_ResourceInterface
   * @throw AblePolecat_Transaction_Exception If cannot be brought to a satisfactory state.
   */
  public function run() {
    
    //
    // Give parent first shot at resolving request.
    //
    $Resource = parent::run();
    
    if (!is_a($Resource, 'SalesforceSoapApi_Resource')) {
      //
      // @todo: unexpected resource type.
      //
    }
    
    //
    // @todo: static method for getting full path, checking syntax, including file...
    //
    return $Resource;
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