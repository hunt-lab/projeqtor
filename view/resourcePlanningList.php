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
 * Presents the list of objects of a given class.
 *
 */
require_once "../tool/projeqtor.php";
scriptLog('   ->/view/resourcePlanningList.php');

$canPlan=false;
$right=SqlElement::getSingleSqlElementFromCriteria('habilitationOther', array('idProfile'=>$user->idProfile, 'scope'=>'planning'));
if ($right) {
  $list=new ListYesNo($right->rightAccess);
  if ($list->code=='YES') {
    $canPlan=true;
  }
}
$plannableProjectsList=getSessionUser()->getListOfPlannableProjects();
if (! $canPlan) {
  $canPlan=(count($plannableProjectsList)>0)?true:false;
}

$startDate=date('Y-m-d');
$endDate=null;
$user=getSessionUser();
$saveDates=false;
$paramStart=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningStartDate'));
if ($paramStart->id) {
  $startDate=$paramStart->parameterValue;
  $saveDates=true;
}
$paramEnd=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningEndDate'));
if ($paramEnd->id) {
  $endDate=$paramEnd->parameterValue;
  $saveDates=true;
}
//$saveShowWbsObj=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningShowWbs'));
//$saveShowWbs=$saveShowWbsObj->parameterValue;
$saveShowWbs=Parameter::getUserParameter('planningShowWbs');
//$saveShowResourceObj=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningShowResource'));
//$saveShowResource=$saveShowResourceObj->parameterValue;
$saveShowResource=Parameter::getUserParameter('planningShowResource');
//$saveShowWorkObj=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningShowWork'));
//$saveShowWork=$saveShowWorkObj->parameterValue;
$saveShowWork=Parameter::getUserParameter('planningShowWork');
//$saveShowClosedObj=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningShowClosed'));
//$saveShowClosed=$saveShowClosedObj->parameterValue;
$saveShowClosed=Parameter::getUserParameter('planningShowClosed');
//$saveShowProjectObj=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningShowProject'));
//$saveShowProject=$saveShowProjectObj->parameterValue;
$saveShowProject=Parameter::getUserParameter('planningShowProject');
$saveShowNullAssignment=Parameter::getUserParameter('listShowNullAssignment');
if ($saveShowClosed) {
  $_REQUEST['idle']=true;
}
//$objectClass='Task';
//$obj=new $objectClass;

$displayWidthPlan="9999";
if (RequestHandler::isCodeSet('destinationWidth')) {
  $displayWidthPlan=RequestHandler::getNumeric('destinationWidth');
}

?>
   
<div id="mainPlanningDivContainer" dojoType="dijit.layout.BorderContainer">
	<div dojoType="dijit.layout.ContentPane" region="top" id="listHeaderDiv" height="27px"
	style="z-index: 3; position: relative; overflow: visible !important;">
		<table width="100%" height="27px" class="listTitle" >
		  <tr height="32px">
		    <td style="vertical-align:top; min-width:100px; width:15%;">
		      <table >
    		    <tr height="32px">
      		    <td width="50px" style="min-width:50px" align="center">
                <?php echo formatIcon('ResourcePlanning', 32, null, true);?>
              </td>
              <td style="min-width:100px"><span class="title" style="max-width:250px;white-space:normal"><?php echo i18n('menuResourcePlanning');?></span></td>
      		  </tr>
    		  </table>
		    </td>
		    <td>   
		      <form dojoType="dijit.form.Form" id="listForm" action="" method="" >
		        <table style="width: 100%;">
		          <tr>
		            <td style="width:70px">
		              <?php 
		              $objectClass=(RequestHandler::isCodeSet('objectClass'))?RequestHandler::getClass('objectClass'):'';
		              $objectId=(RequestHandler::isCodeSet('objectId'))?RequestHandler::getId('objectId'):'';?>
		              <input type="hidden" id="objectClass" name="objectClass" value="<?php echo $objectClass;?>" /> 
		              <input type="hidden" id="objectId" name="objectId" value="<?php echo $objectId;?>" />
		              &nbsp;&nbsp;&nbsp;
<?php if ($canPlan) { ?>
		              <button id="planButton" dojoType="dijit.form.Button" showlabel="false"
		                title="<?php echo i18n('buttonPlan');?>"
		                iconClass="iconPlanStopped" >
		                <script type="dojo/connect" event="onClick" args="evt">
                     showPlanParam();
                     return false;
                    </script>
		              </button>
<?php }?>             
		            </td>
		            <td style="white-space:nowrap;width:<?php echo ($displayWidthPlan>1030)?240:150;?>px">
		              <table>
                    <tr>
                      <td align="right">&nbsp;&nbsp;&nbsp;<?php echo ($displayWidthPlan>1030)?i18n("displayStartDate"):i18n("from");?>&nbsp;&nbsp;</td><td>
                        <div dojoType="dijit.form.DateTextBox"
	                        <?php if (sessionValueExists('browserLocaleDateFormatJs')) {
														echo ' constraints="{datePattern:\''.getSessionValue('browserLocaleDateFormatJs').'\'}" ';
													}?>
                           id="startDatePlanView" name="startDatePlanView"
                           invalidMessage="<?php echo i18n('messageInvalidDate')?>"
                           type="text" maxlength="10"
                           style="width:100px; text-align: center;" class="input roundedLeft"
                           hasDownArrow="true"
                           value="<?php if(sessionValueExists('startDatePlanView')){ echo getSessionValue('startDatePlanView'); }else{ echo $startDate; }?>" >
                           <script type="dojo/method" event="onChange" >
                            saveDataToSession('startDatePlanView',formatDate(dijit.byId('startDatePlanView').get("value")), false);
                            refreshJsonPlanning();
                           </script>
                         </div>
                      </td>
                    </tr>
                    <tr>
                      <td align="right">&nbsp;&nbsp;&nbsp;<?php echo ($displayWidthPlan>1030)?i18n("displayEndDate"):i18n("to");?>&nbsp;&nbsp;</td>
                      <td>
                        <div dojoType="dijit.form.DateTextBox"
	                        <?php if (sessionValueExists('browserLocaleDateFormatJs')) {
														echo ' constraints="{datePattern:\''.getSessionValue('browserLocaleDateFormatJs').'\'}" ';
													}?>
                           id="endDatePlanView" name="endDatePlanView"
                           invalidMessage="<?php echo i18n('messageInvalidDate')?>"
                           type="text" maxlength="10"
                           style="width:100px; text-align: center;" class="input roundedLeft"
                           hasDownArrow="true"
                           value="<?php if(sessionValueExists('endDatePlanView')){ echo getSessionValue('endDatePlanView'); }else{ echo $endDate; } ?>" >
                           <script type="dojo/method" event="onChange" >
                            saveDataToSession('endDatePlanView',formatDate(dijit.byId('endDatePlanView').get("value")), false);
                            refreshJsonPlanning();
                           </script>
                        </div>
                      </td>
                    </tr>
                  </table>
		            </td>
                <td style="width:<?php echo ($displayWidthPlan>1030)?'150':'120';?>px">
                  <table>
                    <tr>
                      <td width="32px">
                        <button title="<?php echo i18n('printPlanning')?>"
                         dojoType="dijit.form.Button"
                         id="listPrint" name="listPrint"
                         iconClass="dijitButtonIcon dijitButtonIconPrint" class="detailButton" showLabel="false">
                          <script type="dojo/connect" event="onClick" args="evt">
<?php $ganttPlanningPrintOldStyle=Parameter::getGlobalParameter('ganttPlanningPrintOldStyle');
      if (!$ganttPlanningPrintOldStyle) {$ganttPlanningPrintOldStyle="NO";}
      if ($ganttPlanningPrintOldStyle=='YES') {?>
                          showPrint("../tool/jsonResourcePlanning.php", 'planning');
<?php } else { ?>
                          showPrint("planningPrint.php", 'planning');
<?php }?>            
                          </script>
                        </button>
                      </td>
                      <td width="32px">
                        <button title="<?php echo i18n('reportPrintPdf')?>"
                         dojoType="dijit.form.Button"
                         id="listPrintPdf" name="listPrintPdf"
                         iconClass="dijitButtonIcon dijitButtonIconPdf" class="detailButton" showLabel="false">
                          <script type="dojo/connect" event="onClick" args="evt">
                          var paramPdf='<?php echo Parameter::getGlobalParameter("pdfPlanningBeta");?>';
                          if(paramPdf!='false') planningPDFBox();
                          else showPrint("../tool/jsonResourcePlanning_pdf.php", 'planning', null, 'pdf');
                          </script>
                        </button>
                      </td>
                      <td width="32px">
                        <input type="hidden" id="outMode" name="outMode" value="" />
                        <div dojoType="dijit.form.DropDownButton"
                             id="planningColumnSelector" jsId="planningColumnSelector" name="planningColumnSelector" 
                             showlabel="false" class="comboButton" iconClass="dijitButtonIcon dijitButtonIconColumn" 
                             title="<?php echo i18n('columnSelector');?>">
                          <span>title</span>
                          <div dojoType="dijit.TooltipDialog" class="white" style="width:250px;">
                            <script type="dojo/connect" event="onHide" args="evt">
                              if (dndMoveInProgress) { setTimeout('dijit.byId("planningColumnSelector").openDropDown();',1); }
                            </script>   
									          <div id="dndPlanningColumnSelector" jsId="dndPlanningColumnSelector" 
                             dndType="column" dojotype="dojo.dnd.Source" 
                             withhandles="true" class="container">    
									             <?php 
									               $resourcePlanning=true; 
									               include('../tool/planningColumnSelector.php')?>
									          </div>
                            <div style="height:5px;"></div>    
                            <div style="text-align: center;"> 
                              <button title="" dojoType="dijit.form.Button" 
                                id="" name="" showLabel="true"><?php echo i18n('buttonOK');?>
                                <script type="dojo/connect" event="onClick" args="evt">
                                  validatePlanningColumn();
                                </script>
                              </button>
                            </div>                 
									        </div>
									      </div>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="3" style="white-space:nowrap;">
                        <span title="<?php echo i18n('saveDates')?>" dojoType="dijit.form.CheckBox"
                           type="checkbox" id="listSaveDates" name="listSaveDates" class="whiteCheck"
                           <?php if ( $saveDates) {echo 'checked="checked"'; } ?>  >

                          <script type="dojo/method" event="onChange" >
                            refreshJsonPlanning();
                          </script>
                        </span>
                        <span for="listSaveDates"><?php echo i18n("saveDates");?></span>
                      </td>
                    </tr>
                  </table>
                </td>
		            <td style="width:5px">
                  <table>
                    <tr>
                      <td style="text-align:right;"><?php echo i18n('colIdResource');?>&nbsp;&nbsp;</td>
                      <td>
                      <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft" 
                        style="width: <?php echo ($displayWidthPlan>1030)?150:100;?>px;"
                        name="selectResourceName" id="selectResourceName" value="<?php if(sessionValueExists('selectResourceName')){ echo getSessionValue('selectResourceName'); }?>"
                        <?php echo autoOpenFilteringSelect();?>  
                      >
                        <script type="dojo/method" event="onChange" >
                           saveDataToSession('selectResourceName', dijit.byId('selectResourceName').get("value"), false);
                           refreshJsonPlanning();
                        </script>
                         <option value=""></option>
                        <?php 
                         $specific='imputation';
                         $includePool=true;
                         $specificDoNotInitialize=true;
                         
                         include '../tool/drawResourceListForSpecificAccess.php';?>  
                      </select>
                      </td>
                    </tr>
                    <tr>
                      <td style="text-align:right;"><?php echo i18n('colIdTeam');?>&nbsp;&nbsp;</td>
                      <td>
                      <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft" 
                        style="width:  <?php echo ($displayWidthPlan>1030)?150:100;?>px;"
                        name="teamName" id="teamName" value="<?php if(sessionValueExists('teamName')){ echo getSessionValue('teamName'); }?>"
                        <?php echo autoOpenFilteringSelect();?>
                        >
                        <script type="dojo/method" event="onChange" > 
                           saveDataToSession('teamName', dijit.byId('teamName').get("value"), false);                          
                           refreshJsonPlanning();
                        </script>
                        <?php 
                         htmlDrawOptionForReference('idTeam', null)?>  
                      </select>
                      </td>
                    </tr>
                  </table>
                </td>
		            <td style="text-align: right; align: right;">
		              <table width="100%"><tr>
		              <td>&nbsp;</td>
		              <td width="35px">&nbsp;</td>
		              <td width="200px">
                  <?php echo i18n("labelShowWbsShort");?>
                  </td><td width="35px">
		              <div title="<?php echo i18n('showWbs')?>" dojoType="dijit.form.CheckBox" 
                    type="checkbox" id="showWBS" name="showWBS" class="whiteCheck"
                    <?php if ($saveShowWbs=='1') { echo ' checked="checked" '; }?> >
		                <script type="dojo/method" event="onChange" >
                      saveUserParameter('planningShowWbs',((this.checked)?'1':'0'));
                      refreshJsonPlanning();
                    </script>
		              </div>&nbsp;
		              </td></tr>
		              <tr>
		              <td style="min-width:80px;"><?php echo i18n("labelShowAssignmentWithoutWork");?></td>
		              <td>
                  <div title="<?php echo i18n('titleShowAssignmentWithoutWork')?>" dojoType="dijit.form.CheckBox" 
                    type="checkbox" id="listShowNullAssignment" name="listShowNullAssignment" class="whiteCheck"
                    <?php if ($saveShowNullAssignment=='1') { echo ' checked="checked" '; }?> >
                    <script type="dojo/method" event="onChange" >
                      saveUserParameter('listShowNullAssignment',((this.checked)?'1':'0'));
                      refreshJsonPlanning();
                    </script>
                  </div>&nbsp;
                  </td>
		              <td>
		              <?php echo i18n("labelShowIdleShort");?>
                  </td><td>
		              <div title="<?php echo i18n('showIdleElements')?>" dojoType="dijit.form.CheckBox" 
                    type="checkbox" id="listShowIdle" name="listShowIdle" class="whiteCheck"
                    <?php if ($saveShowClosed=='1') { echo ' checked="checked" '; }?> >
		                <script type="dojo/method" event="onChange" >
                      saveUserParameter('planningShowClosed',((this.checked)?'1':'0'));
                      refreshJsonPlanning();
                    </script>
		              </div>&nbsp;
                  </td></tr>
                  <tr>
                  <td style="min-width:80px;"><?php echo i18n("labelShowProjectLevel");?></td>
		              <td>
                  <div title="<?php echo i18n('showProjectLevel')?>" dojoType="dijit.form.CheckBox" 
                    type="checkbox" id="listShowProject" name="listShowProject" class="whiteCheck"
                    <?php if ($saveShowProject=='1') { echo ' checked="checked" '; }?> >
                    <script type="dojo/method" event="onChange" >
                      saveUserParameter('planningShowProject',((this.checked)?'1':'0'));
                      refreshJsonPlanning();
                    </script>
                  </div>&nbsp;
                  </td>
                  <td>
                  <?php echo i18n("labelShowLeftWork");?>
                  </td><td>
                  <div title="<?php echo i18n('showLeftWork')?>" dojoType="dijit.form.CheckBox" 
                    type="checkbox" id="listShowLeftWork" name="listShowLeftWork" class="whiteCheck"
                    <?php if ($saveShowWork=='1') { echo ' checked="checked" '; }?> >
                    <script type="dojo/method" event="onChange" >
                      saveUserParameter('planningShowWork',((this.checked)?'1':'0'));
                      refreshJsonPlanning();
                    </script>
                  </div>&nbsp;
                  </td></tr>
                  </table>
		            </td>
		          </tr>
		        </table>    
		      </form>
		    </td>
		  </tr>
		</table>
	
		<div dojoType="dijit.layout.ContentPane" id="planningJsonData" jsId="planningJsonData" 
     style="display: none">
		  <?php
            include '../tool/jsonResourcePlanning.php';
          ?>
		</div>
	</div>
	<div dojoType="dijit.layout.ContentPane" region="center" id="gridContainerDiv">
   <div id="submainPlanningDivContainer" dojoType="dijit.layout.BorderContainer"
    style="border-top:1px solid #ffffff;">
        <?php $leftPartSize=Parameter::getUserParameter('planningLeftSize');
          if (! $leftPartSize) {$leftPartSize='325px';} ?>
	   <div dojoType="dijit.layout.ContentPane" region="left" splitter="true" 
      style="width:<?php echo $leftPartSize;?>; height:100%; overflow-x:scroll; overflow-y:hidden;" class="ganttDiv" 
      id="leftGanttChartDIV" name="leftGanttChartDIV"
      onScroll="dojo.byId('ganttScale').style.left=(this.scrollLeft)+'px'; this.scrollTop=0;"
      onWheel="leftMouseWheel(event);">
      <script type="dojo/method" event="onUnload" >
         var width=this.domNode.style.width;
         setTimeout("saveUserParameter('planningLeftSize','"+width+"');",1);
         return true;
      </script>
     </div>
     <div dojoType="dijit.layout.ContentPane" region="center" 
      style="height:100%; overflow:hidden;" class="ganttDiv" 
      id="GanttChartDIV" name="GanttChartDIV" >
       <div id="mainRightPlanningDivContainer" dojoType="dijit.layout.BorderContainer">
         <div dojoType="dijit.layout.ContentPane" region="top" 
          style="width:100%; height:45px; overflow:hidden;" class="ganttDiv"
          id="topGanttChartDIV" name="topGanttChartDIV">
         </div>
         <div dojoType="dijit.layout.ContentPane" region="center" 
          style="width:100%; overflow-x:scroll; overflow-y:scroll; position: relative; top:-10px;" class="ganttDiv"
          id="rightGanttChartDIV" name="rightGanttChartDIV"
          onScroll="dojo.byId('rightside').style.left='-'+(this.scrollLeft+1)+'px';
                    dojo.byId('leftside').style.top='-'+(this.scrollTop)+'px';"
         >
         </div>
       </div>
     </div>
   </div>
	</div>
</div>
