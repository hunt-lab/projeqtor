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

/*
 * ============================================================================
 * List of parameter specific to a user.
 * Every user may change these parameters (for his own user only !).
 */
require_once "../tool/projeqtor.php";
scriptLog('   ->/view/module.php');
$user=getSessionUser();
?>
<input type="hidden" name="objectClassManual" id="objectClassManual" value="Module" />
<div class="container" dojoType="dijit.layout.BorderContainer">
  <div style="height:48px" id="listDiv" dojoType="dijit.layout.ContentPane" region="top" 
    style="text-align:center;width:100%;font-size:250%;font-weight:bold; " class="listTitle">
      <div style="text-align:center;width:100%;padding-top:5px;font-size:250%;font-weight:bold; ">
        <?php echo i18n("menuModule")?>
      </div>
      <button id="saveParameterButton" dojoType="dijit.form.Button" showlabel="true" 
        title="<?php echo i18n("applyChanges");?>"
        style="position:absolute;top:10px;right:20px;color:#707070;font-weight:bold" class="" enabled="true"
        iconClass="dijitButtonIcon dijitButtonIconSave" class="detailButton">
        <script type="dojo/connect" event="onClick" args="evt">
		        var url="../tool/resetModuleTablesInSession.php";
            dojo.xhrPost({
            url : url,
            load : function(data, args) {
              dojo.byId("saveParameterButton").blur();
              disableWidget("applyButton");
              showWait();
              noDisconnect=true;
              quitConfirmed=true;
<?php         if (getSessionValue('showModule')) {
                $firstPage=getSessionValue('showModule');
                unsetSessionValue('showModule');?>
                dojo.byId("directAccessPage").value="<?php echo $firstPage;?>";
<?php         } else { ?> 
                dojo.byId("directAccessPage").value="moduleView.php";
<?php         } ?>
              dojo.byId("menuActualStatus").value=menuActualStatus;
              dojo.byId("p1name").value="type";
              dojo.byId("p1value").value=forceRefreshMenu;
              forceRefreshMenu="";
              dojo.byId("directAccessForm").submit();
            },
            error : function () {
             consoleTraceLog("error resetting module tables in session");
            }
          });
        </script>
       <?php echo i18n("applyChanges");?>
      </button>
    </div> 
    <div style="overflow-y:auto;overflow-x:hidden;padding:10px" id="detailDiv" dojoType="dijit.layout.ContentPane" region="center" >
    <table style="margin:20px; width:100%">

    <?php 
    $mod=new Module();
    $modList=$mod->getSqlElementsFromCriteria(null,null,null,'sortOrder asc');
    $simuIndex=Parameter::getGlobalParameter('simuIndex');
    global $hosted;
    if ($hosted===true) $simuIndex=1; // Will hide Simulation Module for hosted
    foreach ($modList as $mod) {
      if($simuIndex and $mod->name == 'moduleDataCloning'){
        continue;
      }?>
      <tr style="height:30px" >
        <td style="width:25%;padding:10px;vertical-align:top" class="simpleText">
          <?php if ($mod->idModule) echo "<div style='width:40px;float:left'>&nbsp;</div>";?> 
          <?php $moduleClass=substr($mod->name,6);?>
          <div style='float:left;position:relative;top:-5px;margin-right:15px;' class='icon<?php echo $moduleClass?> iconSize32 icon<?php echo $moduleClass?>32'></div>
          <div id="module_<?php echo $mod->id;?>" style="float:left;zoom:125%" dojoType="dijit.form.CheckBox" 
           parent="<?php if ($mod->idModule) {echo $mod->idModule;}?>" type="checkbox" <?php echo ($mod->active)?'checked="checked"':'';?> 
           class="moduleClass <?php if ($mod->idModule) {echo 'parentModule'.$mod->idModule;}?>" 
           onclick="saveModuleStatus(<?php echo $mod->id?>,this.checked);"
           data-dojo-props="" ></div>&nbsp;
          <label for="module_<?php echo $mod->id;?>" style="float:none;" class="simpleText bold big"><?php echo i18n($mod->name);?></label>
        </td>
        
        <td style="width:25%;padding:10px 10px;vertical-align:top">
        <?php
        $modMenu=new ModuleMenu();
        $modMenuList=$modMenu->getSqlElementsFromCriteria(array('idModule'=>$mod->id),null,null,'id asc');
        foreach ($modMenuList as $modMenu) {
          if ($modMenu->hidden) continue;
          $menuName=SqlList::getNameFromId('Menu', $modMenu->idMenu,false);
          $class=substr($menuName,4);
          echo "<div style='float:left;clear:left;padding-top:10px' class='icon$class iconSize22 icon".$class."22'></div>";
          echo "<div style='float:left;padding-left:10px;padding-top:3px;' class='simpleText bold'>".i18n($menuName)."</div>"; 
        }
        ?>
        </td>
        <td class="simpleText" style="width:50%;padding:10px 50px;vertical-align:top; text-align:justify;"><?php echo i18n($mod->name.'Comment');?></td>
      </tr>
    <?php }?>
    </table>
  </div>
</div>