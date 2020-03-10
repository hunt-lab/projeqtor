<?php 
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : -
 *
 * This file is part of ProjeQtOr.
 * 
 * ProjeQtOr is free software: you can redistribute it and/or modify it under 
 * the terms of the GNU Affero General Public License as published by the Free 
 * Software Foundation, either version 3 of the License, or (at your option) 
 * any later version.
 * 
 * ProjeQtOr is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for 
 * more details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * ProjeQtOr. If not, see <http://www.gnu.org/licenses/>.
 *
 * You can get complete code of ProjeQtOr, other resource, help and information
 * about contributors at http://www.projeqtor.org 
 *     
 *** DO NOT REMOVE THIS NOTICE ************************************************/

/* ============================================================================
 * Menu defines list of items to present to users.
 */ 
require_once('_securityCheck.php');
class Module extends SqlElement {

  // extends SqlElement, so has $id
  public $id;    // redefine $id to specify its visible place 
  public $name;
  public $idModule;
  public $sortOrder=0;
  public $active;
  public $idle;
  
  public $_isNameTranslatable = true;
  
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
  }

  
   /** ==========================================================================
   * Destructor
   * @return void
   */ 
  function __destruct() {
    parent::__destruct();
  }
    
  public function save() {
    $old=$this->getOld();
    $result=parent::save();
    $moduleMenu=new ModuleMenu();
    $moduleMenuList=$moduleMenu->getSqlElementsFromCriteria(array('idModule'=>$this->id));
    foreach ($moduleMenuList as $moduleMenu) {
      $moduleMenu->active=$this->active;
      $moduleMenu->save();
    }
    $moduleReport=new ModuleReport();
    $moduleReportList=$moduleReport->getSqlElementsFromCriteria(array('idModule'=>$this->id));
    foreach ($moduleReportList as $moduleReport) {
      $moduleReport->active=$this->active;
      $moduleReport->save();
    }
    // MTY - LEAVE SYSTEM
    if ($this->id==12 and $old->active!=$this->active) { // HR Module
      include_once '../tool/projeqtor-hr.php';
      $result=initPurgeLeaveSystemElements(($this->active)?'YES':'NO');
      $status = getLastOperationStatus($result);
      if ($status=="OK") {
        unsetSessionValue("visibleProjectsList");
      } elseif ($status=="NO_CHANGE") {
        $status="OK";
      }
      traceLog("Change LeaveSystemActiv to ".(($this->active)?'YES':'NO').' :'.$status);
      if ($status!='OK') traceLog($result); 
    }
      // MTY - LEAVE SYSTEM
    unsetSessionValue('menuInactiveList',true);
    unsetSessionValue('reportInactiveList',true);
    unsetSessionValue('moduleList',true);
    return $result;
  }
  public static function isMenuActive($menu) {
    if (! sessionValueExists('menuInactiveList',true)) {
      self::initializeMenuInactiveList();
    }
    $list=getSessionValue('menuInactiveList',null,true);
    if (isset($list[$menu])) return false;
    else return true;
  }
  public static function isModuleActive($module) {
    if (! sessionValueExists('moduleList',true)) {
      self::initializeModuleList();
    }
    $list=getSessionValue('moduleList',null,true);
    if (isset($list[$module]) and $list[$module]==1) return true;
    else return false;
  }
  public static function moduleExists($module) {
    if (! sessionValueExists('moduleList',true)) {
      self::initializeModuleList();
    }
    $list=getSessionValue('moduleList',null,true);
    if (isset($list[$module])) return true;
    else return false;
  }
  private static function initializeMenuInactiveList() {
    $moduleMenu=new ModuleMenu();
    $list=$moduleMenu->countGroupedSqlElementsFromCriteria(null, array('idMenu','active'),'1=1');
    $arrayCpt=array();
    foreach ($list as $key=>$cpt) {
      $split=explode('|',$key);
      $menu=SqlList::getNameFromId('Menu', $split[0],false);
      $active=$split[1];
      if (!isset($arrayCpt[$menu])) $arrayCpt[$menu]=array('0'=>0,'1'=>0);
      $arrayCpt[$menu][$active]=$cpt;
    }
   
    $result=array();
    foreach($arrayCpt as $menu=>$tab) {
      if ($tab['1']>0) {
        // At least one active
      } else {
        $result[$menu]=$menu;
      }
    }
    setSessionValue('menuInactiveList', $result,true);
  }
  
  public static function isReportActive($report) {
    if (! sessionValueExists('reportInactiveList',true)) {
      self::initializeReportInactiveList();
    }
    $list=getSessionValue('reportInactiveList',null,true);
    if (isset($list[$report])) return false;
    else return true;
  }
  private static function initializeReportInactiveList() {
    $moduleReport=new ModuleReport();
    $list=$moduleReport->countGroupedSqlElementsFromCriteria(null, array('idReport','active'),'1=1');
    $arrayCpt=array();
    foreach ($list as $key=>$cpt) {
      $split=explode('|',$key);
      $report=SqlList::getNameFromId('Report', $split[0],false);
      $active=$split[1];
      if (!isset($arrayCpt[$report])) $arrayCpt[$report]=array('0'=>0,'1'=>0);
      $arrayCpt[$report][$active]=$cpt;
    }
    $result=array();
    foreach($arrayCpt as $report=>$tab) {
      if ($tab['1']>0) {
        // At least one active
      } else {
        $result[$report]=$report;
      }
    }
    setSessionValue('reportInactiveList', $result,true);
  }
  
  private static function initializeModuleList() {
    $result=array();
    $module=new Module();
    $list=$module->getSqlElementsFromCriteria(null,null,"1=1");
    foreach ($list as $module) {
      $result[$module->name]=$module->active;
    }
    setSessionValue('moduleList', $result,true);
  }
  
  public static function applyModuleRestrictionsOnParametersList(&$list) {
    if (! Module::isModuleActive('moduleConfiguration')) {
      unset($list['tabConfiguration']);
      unset($list['columnConfigurationLeft']);
      unset($list['sectionProductAndComponent']);
      unset($list['displayBusinessFeature']);
      unset($list['displayMilestonesStartDelivery']);
      unset($list['displayLanguage']);
      unset($list['displayContext']);
      unset($list['showTendersOnVersions']);
      unset($list['includeProductInProductStructure']);
      unset($list['columnConfigurationRight']);
      unset($list['sectionVersion']);
      unset($list['displayMilestonesStartDelivery']);
      unset($list['displayListOfActivity']);
      unset($list['directAccessToComponentList']);
      unset($list['versionNameAutoformat']);
      unset($list['versionNameAutoformatSeparator']);
      unset($list['subscriptionAuto']);
      unset($list['typeOfCopyComponentVersion']);
      unset($list['versionCompatibility']);
      unset($list['productVersionOnDelivery']);
      unset($list['sortVersionComboboxNameDesc']);
      unset($list['sortCompositionStructure']);
      unset($list['manageComponentOnRequirement']);
      unset($list['dontAddClosedDeliveredVersionToProject']);
      unset($list['authorizeActivityOnDeliveredProduct']);
      unset($list['autoSetUniqueComponentVersion']);
    }
    if (! Module::isModuleActive('moduleFinancial')) {
      unset($list['tabFinancial']);
      unset($list['newColumnbFinancialLeft']);
      unset($list['newColumnbFinancialRight']);
    }
    if (! Module::isModuleActive('moduleExpenses')) {
      unset($list['sectionFinancialProvider']);
      unset($list['ImputOfAmountProvider']);
      unset($list['ImputOfBillLineProvider']);
    }  
    if (! Module::isModuleActive('moduleIncomes')) {
      unset($list['sectionFinancialClient']);
      unset($list['ImputOfAmountClient']);
      unset($list['ImputOfBillLineClient']);
    }
    if (! Module::isModuleActive('moduleHumanResource')) {
      unset($list['sectionLeaves']);
      unset($list['leavesSystemActiv']);
      unset($list['leavesSystemAdmin']);
      unset($list['typeExportXLSorODS']);
    }
    if (! Module::isModuleActive('moduleNotification')) {
      unset($list['cronCheckNotifications']);
    }
    if (! Module::isModuleActive('moduleOrganization')) {
      unset($list['useOrganizationBudgetElement']);
      unset($list['sectionOrganization']);
    }
  }
  
  public static function getListOfFieldsToHide($class) {
    $list=array();
    if (! Module::isModuleActive('moduleConfiguration')) {
      $tstFld=array( '_sec_productComponent', 'idProduct', 'idComponent', 'idProductVersion','idOriginalProductVersion','idTargetProductVersion', 
                     'idComponentVersion', 'idOriginalComponentVersion', 'idTargetComponentVersion',
                      '_sec_ProductprojectProducts','_ProductProject','_sec_VersionprojectVersions','_VersionProject'
      );
      foreach ($tstFld as $fld) {
        if (property_exists($class,$fld)) {
          $list[$fld]=$fld;
        }
      }
    }
    if ($class=="Delivery" and ! Module::isModuleActive('modulePlanning')) {
      $list['_sec_Link_Activity']='_sec_Link_Activity' ;
      $list['_Link_Activity']='_Link_Activity';
    }
    return $list;
  }
}
?>