<?php
/*
 * @author: qCazelles
 */

require_once "../tool/projeqtor.php";
scriptLog('   ->/view/versionsPlanningList.php');

$startDate=date('Y-m-d');
$endDate=null;
$user=getSessionUser();
$saveDates=false;
$saveShowResource=Parameter::getUserParameter('planningShowResource');
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

$saveShowWbsObj=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningShowWbs'));
$saveShowWbs=$saveShowWbsObj->parameterValue;
// $saveShowResourceObj=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningShowResource'));
// $saveShowResource=$saveShowResourceObj->parameterValue;
$saveShowWorkObj=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningShowWork'));
$saveShowWork=$saveShowWorkObj->parameterValue;
$saveShowClosedObj=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningShowClosed'));
$saveShowClosed=$saveShowClosedObj->parameterValue;
$saveShowMilestoneObj=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningShowMilestone'));
$saveShowMilestone=$saveShowMilestoneObj->parameterValue;

$displayProductVersionActivity = Parameter::getUserParameter('planningVersionDisplayProductVersionActivity');
$displayComponentVersionActivity = Parameter::getUserParameter('planningVersionDisplayComponentVersionActivity');
$showClosedPlanningVersion = Parameter::getUserParameter('planningVersionShowClosed');
$showOnlyActivesVersions=Parameter::getUserParameter('showOnlyActivesVersions');

if ($saveShowClosed) {
	$_REQUEST['idle']=true;
}

$proj=null;
if (sessionValueExists('project')) {
	$proj=getSessionValue('project');
	if(strpos($proj, ",")){
		$proj="*";
	}
}
if ($proj=='*' or !$proj) {
	$proj=null;
}
?>

<div id="mainPlanningDivContainer" dojoType="dijit.layout.BorderContainer">
	<div dojoType="dijit.layout.ContentPane" region="top" id="listHeaderDiv" height="27px"
	style="z-index: 3; position: relative; overflow: visible !important;">
		<table width="100%" height="27px" class="listTitle" >
		  <tr height="27px">
		  	<td style="vertical-align:top; min-width:100px; width:15%">
		      <table >
    		    <tr height="32px">
        		    <td width="50px"  style="min-width:50px"  align="center">
                    <?php echo formatIcon('VersionsPlanning', 32, null, true);?>
                  </td>
                  <td width="200px" ><span class="title" style="max-width:200px;white-space:normal"><?php echo i18n('menuVersionsPlanning');?></span></td>
      		    </tr>
                <tr height="32px">
        		    <td width="50px"  style="min-width:50px"  align="center">
        		    </td>
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
		              $objectId=(RequestHandler::isCodeSet('objectId'))?RequestHandler::getId('objectId'):'';
		              $productVersionsListId=(RequestHandler::isCodeSet('productVersionsListId'))?RequestHandler::getValue('productVersionsListId'):'';
		              $objectVersion=(RequestHandler::isCodeSet('objectVersion'))?RequestHandler::getValue('objectVersion'):'';?>
		              <input type="hidden" id="objectClass" name="objectClass" value="<?php echo $objectClass;?>" /> 
		              <input type="hidden" id="objectId" name="objectId" value="<?php echo $objectId;?>" />
		              <input type="hidden" id="productVersionsListId" name="productVersionsListId" value="<?php echo $productVersionsListId;?>" />
		              <input type="hidden" id="objectVersion" name="objectVersion" value="<?php echo $objectVersion;?>" />
                  	  <input type="hidden" id="versions" name="versions" value="true" />
		              &nbsp;&nbsp;&nbsp;
<?php
//CHANGE qCazelles - Correction GANTT - Ticket #100
//Old
// $tabProductVersions = $_REQUEST['productVersionsListId'];
//New
//END CHANGE qCazelles - Correction GANTT - Ticket #100
$tabProductVersions=array();
if (isset($_REQUEST['productVersionsListId'])) {
  if ( strpos($_REQUEST['productVersionsListId'], '_')!==false) {
    $tabProductVersions=explode('_', $_REQUEST['productVersionsListId']);
  } else {
    $tabProductVersions[]=$_REQUEST['productVersionsListId'];
  }
} else { // PBE : will retreive last access if use of previous navigation button
  if (sessionValueExists('tabProductVersions')) {
    $tabProductVersions=getSessionValue('tabProductVersions');
  }
}
setSessionValue('tabProductVersions', $tabProductVersions);
// PBE - end
$nbPvs = 0;
foreach ($tabProductVersions as $idProductVersion) {
	echo '<input type="hidden" id="pvNo'.$nbPvs.'" name="idsProductVersion[]" value="'.$idProductVersion.'" />';
	$nbPvs += 1;
}
echo '<input type="hidden" id="nbPvs" name="nbPvs" value="'.$nbPvs.'" />';

?>                  
		            </td>
		            <td style="white-space:nowrap;width:240px">
		              <table>
                    <tr>
                      <td align="right">&nbsp;&nbsp;&nbsp;<?php echo i18n("displayStartDate");?>&nbsp;&nbsp;</td><td>
                        <div dojoType="dijit.form.DateTextBox"
	                        <?php if (sessionValueExists('browserLocaleDateFormatJs')) {
														echo ' constraints="{datePattern:\''.getSessionValue('browserLocaleDateFormatJs').'\'}" ';
													}?>
                           id="startDatePlanView" name="startDatePlanView"
                           invalidMessage="<?php echo i18n('messageInvalidDate')?>"
                           type="text" maxlength="10"
                           style="width:100px; text-align: center;" class="input roundedLeft"
                           hasDownArrow="true"
                           value="<?php if(sessionValueExists('startDatePlanView')){ echo getSessionValue('startDatePlanView'); }else{ echo $startDate; } ?>" >
                           <script type="dojo/method" event="onChange" >
                            saveDataToSession('startDatePlanView',formatDate(dijit.byId('startDatePlanView').get("value")), false);
                            refreshJsonPlanning();
                           </script>
                         </div>
                      </td>
                    </tr>
                    <tr>
                      <td align="right">&nbsp;&nbsp;&nbsp;<?php echo i18n("displayEndDate");?>&nbsp;&nbsp;</td>
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
                           value="<?php if(sessionValueExists('endDatePlanView')){ echo getSessionValue('endDatePlanView'); }else{ echo $endDate; }  ?>" >
                           <script type="dojo/method" event="onChange" >
                            saveDataToSession('endDatePlanView',formatDate(dijit.byId('endDatePlanView').get("value")), false);
                            refreshJsonPlanning();
                           </script>
                        </div>
                      </td>
                    </tr>
                  </table>
		            </td>
                <td>
                  <table >
                    <tr>
                      <td width="32px">
                        <button title="<?php echo i18n('printPlanning')?>"
                         dojoType="dijit.form.Button"
                         id="listPrint" name="listPrint"
                         iconClass="dijitButtonIcon dijitButtonIconPrint" class="detailButton" 
                         showLabel="false">
                          <script type="dojo/connect" event="onClick" args="evt">
                            <?php $ganttPlanningPrintOldStyle=Parameter::getGlobalParameter('ganttPlanningPrintOldStyle');
                                  if (!$ganttPlanningPrintOldStyle) {$ganttPlanningPrintOldStyle="NO";}
                                  if ($ganttPlanningPrintOldStyle=='YES') {?>
                                   showPrint("../tool/jsonPlanning.php?portfolio=true", 'planning');
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
                         iconClass="dijitButtonIcon dijitButtonIconPdf" class="detailButton"  showLabel="false">
                          <script type="dojo/connect" event="onClick" args="evt">
                          var paramPdf='<?php echo Parameter::getGlobalParameter("pdfPlanningBeta");?>';
                          if(paramPdf!='false') planningPDFBox();
                          else showPrint("../tool/jsonPlanning_pdf.php?portfolio=true", 'planning', null, 'pdf');
                          </script>
                        </button>
                      </td>
                      <?php $activeFilter=false;
                       if (is_array(getSessionUser()->_arrayFilters)) {
                         if (array_key_exists('VersionsPlanning', getSessionUser()->_arrayFilters)) {
                           if (count(getSessionUser()->_arrayFilters['VersionsPlanning'])>0) {
                           	foreach (getSessionUser()->_arrayFilters['VersionsPlanning'] as $filter) {
                           		if (!isset($filter['isDynamic']) or $filter['isDynamic']=="0") {
                           			$activeFilter=true;
                           		}
                           	}
                           }
                         }
                       }
                       $showListFilter='false';
                       if($displayComponentVersionActivity=='1' or $displayProductVersionActivity=='1'){
                          $showListFilter='true';
                       }
                       
                       ?>
                      <td width="50px">
                        <div id="listFilterAdvanced" style="visibility:<?php echo ($showListFilter=='true')?'visible':'hidden';?>;">
                          <button 
                           title="<?php echo i18n('advancedFilter')?>"  
                           class="comboButton" 
                           dojoType="dijit.form.DropDownButton" 
                           id="listFilterFilter" name="listFilterFilter"
                           iconClass="icon<?php echo($activeFilter)?'Active':'';?>Filter" showLabel="false">
                            <script type="dojo/connect" event="onClick" args="evt">
                              showFilterDialog();
                            </script>
                            <script type="dojo/method" event="onMouseEnter" args="evt">
                              clearTimeout(closeFilterListTimeout);
                              clearTimeout(openFilterListTimeout);
                              openFilterListTimeout=setTimeout("dijit.byId('listFilterFilter').openDropDown();",popupOpenDelay);
                            </script>
                            <script type="dojo/method" event="onMouseLeave" args="evt">
                              clearTimeout(openFilterListTimeout);
                              closeFilterListTimeout=setTimeout("dijit.byId('listFilterFilter').closeDropDown();",2000);
                            </script>
                            <div dojoType="dijit.TooltipDialog" id="directFilterList" style="z-index: 999999;<!-- display:none; --> position: absolute;">
                            <?php $objectClass='VersionsPlanning';
                                  include "../tool/displayFilterList.php";
                            ?>
                              <script type="dojo/method" event="onMouseEnter" args="evt">
                                clearTimeout(closeFilterListTimeout);
                                clearTimeout(openFilterListTimeout);
                              </script>
                              <script type="dojo/method" event="onMouseLeave" args="evt">
                                dijit.byId('listFilterFilter').closeDropDown();
                              </script>
                            </div> 
                          </button>
                        </div>
                      </td>
                      <td style="padding-right:5px;padding-left:20px;text-align: right;">
                        <?php echo i18n('displayComponentVersionActivity');?>
                      </td>
                      <td>
                        <div title="<?php echo i18n('displayComponentVersionActivity')?>" dojoType="dijit.form.CheckBox" 
                          class="whiteCheck" type="checkbox" id="listDisplayComponentVersionActivity" name="listDisplayComponentVersionActivity"
                          <?php if ($displayComponentVersionActivity=='1') { echo ' checked="checked" '; }?> >
                          <script type="dojo/method" event="onChange" >
                            saveUserParameter('planningVersionDisplayComponentVersionActivity',((this.checked)?'1':'0'));
                            showListFilter('planningVersionDisplayComponentVersionActivity',((this.checked)?'1':'0'));
                            refreshJsonPlanning();
                          </script>
                        </div>
               		  </td>
                 		<td style="padding-right:5px;padding-left:20px;text-align: right;" >
                 		  <div id="displayRessource" style="visibility:<?php echo ($showListFilter=='true')?'visible':'hidden';?>;">
                            <?php echo i18n('labelShowResource');?>
                          </div>
                        </td>
               		    <td>
                 		  <div id="displayRessourceCheck" style="visibility:<?php echo ($showListFilter=='true')?'visible':'hidden';?>!important;">
                            <div title="<?php echo i18n('showRessourceComponentVersion')?>" dojoType="dijit.form.CheckBox" 
                              class="whiteCheck" type="checkbox" id="showRessourceComponentVersion" name="showRessourceComponentVersion"
                              <?php if ($saveShowResource=='1') { echo ' checked="checked" '; }?> >
                              <script type="dojo/method" event="onChange" >
                                saveUserParameter('planningShowResource',((this.checked)?'1':'0'));
                                //displayResourceComponentVersion('showRessourceComponentVersion',((this.checked)?'1':'0'));
                                refreshJsonPlanning();
                              </script>
                            </div>
                          </div>
               		    </td>
               		    <td style="padding-right:5px;padding-left:20px;text-align: right;" >
                 		  <div id="versionsWithoutActivity" style="visibility:<?php  echo ($showListFilter=='true')?'visible':'hidden';?>;">
                            <?php echo i18n('versionsWithoutActivity');?>
                          </div>
                        </td>
               		    <td>
                 		  <div id="hideVersionsWithoutActivityCheck" style="visibility:<?php  echo ($showListFilter=='true')?'visible':'hidden';?>!important;">
                            <div title="<?php echo i18n('versionsWithoutActivityCheck')?>" dojoType="dijit.form.CheckBox" 
                              class="whiteCheck" type="checkbox" id="versionsWithoutActivityCheck" name="versionsWithoutActivityCheck"
                              <?php if ($hideversionsWithoutActivity=Parameter::getUserParameter('versionsWithoutActivity')=='1') { echo ' checked="checked" '; }?> >
                              <script type="dojo/method" event="onChange" >
                                saveUserParameter('versionsWithoutActivity',((this.checked)?'1':'0'));
                                refreshJsonPlanning();
                              </script>
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
                       <td style="padding-right:5px;padding-left:20px;text-align: right;">
                      <?php echo i18n('displayProductVersionActivity');?>
                      </td><td>
                      <div title="<?php echo i18n('displayProductVersionActivity')?>" dojoType="dijit.form.CheckBox" 
                        class="whiteCheck" type="checkbox" id="listDisplayProductVersionActivity" name="listDisplayProductVersionActivity"
                        <?php if ($displayProductVersionActivity=='1') { echo ' checked="checked" '; }?> >
                        <script type="dojo/method" event="onChange" >
                          saveUserParameter('planningVersionDisplayProductVersionActivity',((this.checked)?'1':'0'));
                          showListFilter('planningVersionDisplayProductVersionActivity',((this.checked)?'1':'0'));
                          refreshJsonPlanning();
                        </script>
                      </div>
                      </td>
                       <td  style="padding-right:5px;padding-left:20px;text-align: right;" >
                      <?php echo i18n('labelShowIdle');?>
                      </td><td>
                      <div title="<?php echo i18n('labelShowIdle')?>" dojoType="dijit.form.CheckBox" 
                        class="whiteCheck" type="checkbox" id="showClosedPlanningVersion" name="showClosedPlanningVersion"
                        <?php if ($showClosedPlanningVersion=='1') { echo ' checked="checked" '; }?> >
                        <script type="dojo/method" event="onChange" >
                          saveUserParameter('planningVersionShowClosed',((this.checked)?'1':'0'));
                          refreshJsonPlanning();
                        </script>
                      </div>
                      </td>
                      <td  style="padding-right:5px;padding-left:20px;text-align: right;" >
                      <?php echo i18n('showOnlyActivesVersions');?>
                      </td><td>
                      <div title="<?php echo i18n('showOnlyActivesVersions')?>" dojoType="dijit.form.CheckBox" 
                        class="whiteCheck" type="checkbox" id="showOnlyActivesVersions" name="showOnlyActivesVersions"
                        <?php if ($showOnlyActivesVersions=='1') { echo ' checked="checked" '; }?> >
                        <script type="dojo/method" event="onChange" >
                          saveUserParameter('showOnlyActivesVersions',((this.checked)?'1':'0'));
                          refreshJsonPlanning();
                        </script>
                      </div>
                      </td>
                    </tr>
                  </table>
                </td>
		        </table>    
		      </form>
		    </td>
		  </tr>
		</table>
		<div id="listBarShow" class="dijitAccordionTitle"  onMouseover="showList('mouse')" onClick="showList('click');">
		  <div id="listBarIcon" align="center"></div>
		</div>
	
		<div dojoType="dijit.layout.ContentPane" id="planningJsonData" jsId="planningJsonData" 
     style="display: none">
		  <?php
            include '../tool/jsonVersionsPlanning.php';
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