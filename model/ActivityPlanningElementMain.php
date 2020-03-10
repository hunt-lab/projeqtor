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
 * Planning element is an object included in all objects that can be planned.
 */  
require_once('_securityCheck.php');
class ActivityPlanningElementMain extends PlanningElement {

  public $id;
  public $idProject;
  public $refType;
  public $refId;
  public $refName;
  public $_separator_sectionDateAndDuration;
  public $_tab_5_3_smallLabel = array('validated', 'planned', 'real', '', 'requested', 'startDate', 'endDate', 'duration');
  public $validatedStartDate;
  public $plannedStartDate;
  public $realStartDate;
  public $latestStartDate;
  public $initialStartDate;
  public $validatedEndDate;
  public $plannedEndDate;
  public $realEndDate;
  public $latestEndDate;
  public $initialEndDate;
  public $validatedDuration;
  public $plannedDuration;
  public $realDuration;
  public $_void_4;
  public $initialDuration;
  public $_spe_isOnCriticalPath;
  public $_separator_sectionCostWork_marginTop;
  public $_tab_5_2_smallLabel_1 = array('validated', 'assigned', 'real', 'left', 'reassessed', 'work', 'cost');
  public $validatedWork;
  public $assignedWork;
  public $realWork;
  public $leftWork;
  public $plannedWork;
  public $validatedCost;
  public $assignedCost;
  public $realCost;
  public $leftCost;
  public $plannedCost;
  public $_separator_menuReview_marginTop;
  public $_tab_5_2_smallLabel_3 = array('', '', '', '', '', 'progress','priority');
  public $progress;
  public $_label_expected;
  public $expectedProgress;
  public $_label_wbs;
  public $wbs;
  public $priority;
  public $_label_planning;
  public $idActivityPlanningMode;
  public $_tab_3_1_3 = array('', '', '', 'minimumThreshold');
  public $minimumThreshold;
  public $_label_indivisibility;
  public $indivisibility;
  public $fixPlanning;
  public $_lib_helpFixPlanning;
  public $_tab_5_1_smallLabel = array('workElementCount', 'estimated', 'real', 'left', '', 'ticket');
  public $workElementCount;
  public $workElementEstimatedWork;
  public $workElementRealWork;
  public $workElementLeftWork;
  public $_button_showTickets;
  //public $_label_wbs;
  //public $_label_progress;
  //public $_label_expected;
  public $wbsSortable;
  public $topId;
  public $topRefType;
  public $topRefId;
  public $idle;
  
  private static $_fieldsAttributes=array(
    "plannedStartDate"=>"readonly,noImport",
    "realStartDate"=>"readonly,noImport",
    "plannedEndDate"=>"readonly,noImport",
    "realEndDate"=>"readonly,noImport",
    "plannedDuration"=>"readonly,noImport",
    "realDuration"=>"readonly,noImport",
    "initialWork"=>"hidden",
    "plannedWork"=>"readonly,noImport",
  	"notPlannedWork"=>"hidden",
    "realWork"=>"readonly,noImport",
    "leftWork"=>"readonly,noImport",
    "assignedWork"=>"readonly,noImport",
    "idActivityPlanningMode"=>"required,mediumWidth,colspan3",
    "idPlanningMode"=>"hidden,noImport",
    "indivisibility"=>"colspan3",
  	"workElementEstimatedWork"=>"readonly,noImport",
  	"workElementRealWork"=>"readonly,noImport",
  	"workElementLeftWork"=>"readonly,noImport",
  	"workElementCount"=>"display,noImport",
    "plannedStartFraction"=>"hidden",
    "plannedEndFraction"=>"hidden",
    "validatedStartFraction"=>"hidden",
    "validatedEndFraction"=>"hidden",
    "latestStartDate"=>"hidden",
    "latestEndDate"=>"hidden",
    "isOnCriticalPath"=>"hidden",
    "isManualProgress"=>"hidden",
    "_spe_isOnCriticalPath"=>"",
    "_label_indivisibility"=>"",
    "indivisibility"=>"",
    "minimumThreshold"=>"",
    "fixPlanning"=>"nobr"
  );

  private static $_fieldsTooltip = array(
  		"minimumThreshold"=> "tooltipMinimumThreshold",
  		"indivisibility"=> "tooltipIndivisibility",
      "fixPlanning"=> "tooltipFixPlanningActivity",
  );
  
  private static $_databaseTableName = 'planningelement';
  //private static $_databaseCriteria = array('refType'=>'Activity'); // Bad idea : sets a mess when moving projets and possibly elsewhere.
  
  private static $_databaseColumnName=array(
    "idActivityPlanningMode"=>"idPlanningMode"
  );
  
  private static $_colCaptionTransposition = array('initialStartDate'=>'requestedStartDate',
      'initialEndDate'=> 'requestedEndDate',
      'initialDuration'=>'requestedDuration'
  );
    
  /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
  }
  
  public function setAttributes() {
    
    //if (Parameter::getGlobalParameter('PlanningActivity')=='YES') {
      $act=new Activity($this->refId,true);
      if ( ! $act->isPlanningActivity) {
        self::$_fieldsAttributes['workElementCount']='hidden';
        self::$_fieldsAttributes['workElementEstimatedWork']='hidden';
        self::$_fieldsAttributes['workElementRealWork']='hidden';
        self::$_fieldsAttributes['workElementLeftWork']='hidden';
        self::$_fieldsAttributes['_button_showTickets']='hidden';
      }
    //}
    if ($this->isAttributeSetToField('workElementCount', 'hidden')
    and $this->isAttributeSetToField('workElementEstimatedWork', 'hidden')
    and $this->isAttributeSetToField('workElementRealWork', 'hidden')
    and $this->isAttributeSetToField('workElementLeftWork', 'hidden')) {
      self::$_fieldsAttributes['_button_showTickets']='hidden';
    }
    $showLatest=Parameter::getGlobalParameter('showLatestDates');
    if ($showLatest) {
      self::$_fieldsAttributes['latestStartDate']="readonly";
      self::$_fieldsAttributes['latestEndDate']="readonly";
    }
    
    $user=getSessionUser();
    $priority=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther',array('idProfile'=>$user->getProfile($this->idProject),'scope'=>'changeManualProgress'));
    if(!$this->isManualProgress or $priority and ($priority->rightAccess == 2 or ! $priority->id ) ){
      self::$_fieldsAttributes["progress"]='display';
    }else{
      self::$_fieldsAttributes["progress"]='';
    }
    $planningMode=new PlanningMode($this->idPlanningMode);
    $mode=$planningMode->code;
    if ($mode!='ASAP' and $mode!='ALAP' and $mode!='START' and $mode!='GROUP') {
      $this->indivisibility=0;
      $this->minimumThreshold=0;
      self::$_fieldsAttributes["indivisibility"]='readonly';
      self::$_fieldsAttributes["minimumThreshold"]='readonly';
    } else {
      self::$_fieldsAttributes["indivisibility"]='';
      self::$_fieldsAttributes["minimumThreshold"]='';
    }
    if ($this->indivisibility){
      self::$_fieldsAttributes["minimumThreshold"]='required';
    }
  }
  /** ==========================================================================
   * Destructor
   * @return void
   */ 
  function __destruct() {
    parent::__destruct();
  }

    /** ========================================================================
   * Return the specific databaseTableName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseTableName() {
    $paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
    return $paramDbPrefix . self::$_databaseTableName;
  }
//   /** ========================================================================
//    * Return the specific database criteria
//    * @return the databaseTableName
//    */
//   protected function getStaticDatabaseCriteria() {
//     return self::$_databaseCriteria;
//   }  
  /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return array_merge(parent::getStaticFieldsAttributes(),self::$_fieldsAttributes);
  }
  
  /** ========================================================================
   * Return the generic databaseTableName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseColumnName() {
    return self::$_databaseColumnName;
  }
  
  /** ============================================================================
   * Return the specific colCaptionTransposition
   * @return the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld=null) {
    return self::$_colCaptionTransposition;
  }
  
  protected function getStaticFieldsTooltip() {
  	return self::$_fieldsTooltip;
  }
  /**=========================================================================
   * Overrides SqlElement::save() function to add specific treatments
   * @see persistence/SqlElement#save()
   * @return the return message of persistence/SqlElement#save() method
   */
  public function save() {
    if (! PlanningElement::$_noDispatch) $this->updateWorkElementSummary(true);
    if($this->idActivityPlanningMode){
      $this->idPlanningMode = $this->idActivityPlanningMode;
    }
    
    if($this->minimumThreshold){
      $old = $this->getOld();
      if($old->minimumThreshold != $this->minimumThreshold){
        $this->minimumThreshold = Work::convertWork($this->minimumThreshold);
      }
    }
    return parent::save();
  }
  
/** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */
  public function control(){
    $result="";
    $mode=null;
    if ($this->idActivityPlanningMode) {
      $mode=new ActivityPlanningMode($this->idActivityPlanningMode);
    }   
    if ($mode) {
      if ($mode->mandatoryStartDate and ! $this->validatedStartDate) {
        $result.='<br/>' . i18n('errorMandatoryValidatedStartDate');
      }
      if ($mode->mandatoryEndDate and ! $this->validatedEndDate) {
        $result.='<br/>' . i18n('errorMandatoryValidatedEndDate');
      }
      if ($mode->mandatoryDuration and ! $this->validatedDuration) {
        $result.='<br/>' . i18n('errorMandatoryValidatedDuration');
      }
   
    }
   
    
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }if ($result=="") {
      $result='OK';
    }
    return $result;
    
  }
  
  /** =========================================================================
   * Update the synthesis Data (work) from workElement (tipically Tickets)
   * Called by workElement
   * @return void
   */
  public function updateWorkElementSummary($noSave=false) {
    $we=new WorkElement();  	
  	$weList=$we->getSqlElementsFromCriteria(array('idActivity'=>$this->refId));
  	$this->workElementEstimatedWork=0;
  	$this->workElementRealWork=0;
  	$this->workElementLeftWork=0;
  	$this->workElementCount=0;
  	foreach ($weList as $we) {
  		$this->workElementEstimatedWork+=$we->plannedWork;
  		$this->workElementRealWork+=$we->realWork;
  		$this->workElementLeftWork+=$we->leftWork;
  		$this->workElementCount+=1;
  	}
  	if (! $noSave) {
  	  $this->simpleSave();
  	}
  	$top=new Activity($this->refId);
  	$param=Parameter::getGlobalParameter('limitPlanningActivity');
  	if($param != "YES"){
  	 if ($this->workElementCount==0 and $top->isPlanningActivity) {
  	   $top->isPlanningActivity=0;
  	   $top->saveForced();
  	   } else if ($this->workElementCount>0 and !$top->isPlanningActivity) {
  	       $top->isPlanningActivity=1;
  	       $top->saveForced();
  	   }
  	}
  }
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript ( $colName );
    if ($colName == "fixPlanning") {
      if(Parameter::getUserParameter('paramLayoutObjectDetail')=="tab"){
        $colScript .= '<script type="dojo/connect" event="onChange" >';
        $colScript .= ' dijit.byId("fixPlanning").set("value",dijit.byId("ActivityPlanningElement_fixPlanning").get("value"));';
        $colScript .= '  formChanged();';
        $colScript .= '</script>';
      }
    }
    return $colScript;
  }
  public function drawSpecificItem($item) {
    if ($item=='showTickets') {
      echo '<div id="' . $item . 'Button" ';
      echo ' title="' . i18n('showTickets') . '" style="float:right;margin-right:3px;"';
      echo ' class="roundedButton">';
      echo '<div class="iconView" ';
      $jsFunction="showTickets('Activity',$this->refId);";
      echo ' onclick="' . $jsFunction . '"';
      echo '></div>';
      echo '</div>';
    } else if ($item=='isOnCriticalPath') {
      if ($this->id and $this->isOnCriticalPath and RequestHandler::getValue('criticalPathPlanning')) {
        echo '<div style="position:relative;"><div style="color:#AA0000;margin:0px 10px;text-align:center;position:absolute;top:-55px;height:60px;">'.i18n('colIsOnCriticalPath').'</div></div>';
      }
    }
  }
}
?>