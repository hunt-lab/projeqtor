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

scriptLog('dynamicDialogMail.php');
$isIE=false;
if (array_key_exists('isIE',$_REQUEST)) {
	$isIE=$_REQUEST['isIE'];
} 
$objectClass=RequestHandler::getClass('objectClass');
if($objectClass == 'TicketSimple'){
    $objectClass = 'Ticket';
}
$objectId = RequestHandler::getId('objectId');
$obj=new $objectClass($objectId);
$emTp = new EmailTemplate();
$idObjectType = 'id'.$objectClass.'Type';
$idMailable = SqlList::getIdFromTranslatableName('Mailable', $objectClass);
$where = "(idMailable = ".$idMailable." or idMailable IS NULL) and (idType = '".$obj->$idObjectType."' or idType IS NULL)";
$listEmailTemplate = $emTp->getSqlElementsFromCriteria(null,false,$where);
$displayComboButton=false;
$user=getSessionUser();
$profile=$user->getProfile();
$habil=SqlElement::getSingleSqlElementFromCriteria('habilitationOther', array('idProfile'=>$profile, 'scope'=>'combo'));
if ($habil) {
  $list=new ListYesNo($habil->rightAccess);
  if ($list->code=='YES') {
    $displayComboButton=true;
  }
}
?>
<input type="hidden" name="dialogMailObjectClass" id="dialogMailObjectClass" value="<?php echo htmlEncode($objectClass);?>" />
  <table>
    <tr>
      <td>
        <form dojoType="dijit.form.Form" id='mailForm' name='mailForm' onSubmit="return false;">
          <input id="mailRefType" name="mailRefType" type="hidden" value="" />
          <input id="mailRefId" name="mailRefId" type="hidden" value="" />
          <input id="idEmailTemplate" name="idEmailTemplate" type="hidden" value="" />
          <input id="previousEmail" name="previousEmail" type="hidden" value="" />
          <table style="white-space:nowrap">
          <?php if (property_exists($objectClass, 'idContact')) { ?>
            <tr>
              <td class="dialogLabel">
                <label for="dialogMailToContact"><?php echo htmlEncode($obj->getColCaption("idContact"));?>&nbsp;:&nbsp;</label>
              </td>
              <td>
                <div id="dialogMailToContact" name="dialogMailToContact" dojoType="dijit.form.CheckBox" type="checkbox" ></div>
              </td>
            </tr>
          <?php } ?>
          <?php if (property_exists($objectClass, 'idUser') and $objectClass!='Project') {?>   
            <tr>
              <td class="dialogLabel">
                <label for="dialogMailToUser"><?php echo htmlEncode($obj->getColCaption("idUser")); ?>&nbsp;:&nbsp;</label>
              </td>
              <td>
                <div id="dialogMailToUser" name="dialogMailToUser" dojoType="dijit.form.CheckBox" type="checkbox" ></div>
              </td>
            </tr>
          <?php } ?>
          <?php if (property_exists($objectClass, 'idAccountable') and !$obj->isAttributeSetToField('idAccountable','hidden') ) {?>   
            <tr>
              <td class="dialogLabel">
                <label for="dialogMailToAccountable"><?php echo htmlEncode($obj->getColCaption("idAccountable")); ?>&nbsp;:&nbsp;</label>
              </td>
              <td>
                <div id="dialogMailToAccountable" name="dialogMailToAccountable" dojoType="dijit.form.CheckBox" type="checkbox" ></div>
              </td>
            </tr>
          <?php } ?>
          <?php if (property_exists($objectClass, 'idResource') ) {?>   
            <tr>
              <td class="dialogLabel">
                <label for="dialogMailToResource"><?php echo htmlEncode($obj->getColCaption("idResource")); ?>&nbsp;:&nbsp;</label>
              </td>
              <td>
                <div id="dialogMailToResource" name="dialogMailToResource" dojoType="dijit.form.CheckBox" type="checkbox" ></div>
              </td>
            </tr>
          <?php } ?>
          <?php if (property_exists($objectClass, 'idSponsor')) { ?>
            <tr>
              <td class="dialogLabel">
                <label for="dialogMailToSponsor"><?php echo htmlEncode($obj->getColCaption("idSponsor"));?>&nbsp;:&nbsp;</label>
              </td>
              <td>
                <div id="dialogMailToSponsor" name="dialogMailToSponsor" dojoType="dijit.form.CheckBox" type="checkbox" ></div>
              </td>
            </tr>
          <?php } ?>
          <?php if (property_exists($objectClass, 'idProject')) { ?>
            <tr>
              <td class="dialogLabel">
                <label for="dialogMailToProject"><?php echo i18n("colMailToProject") ?>&nbsp;:&nbsp;</label>
              </td>
              <td>
                <div id="dialogMailToProject" name="dialogMailToProject" dojoType="dijit.form.CheckBox" type="checkbox" ></div>
              </td>
            </tr>
            <tr>
              <td class="dialogLabel">
                <label for="dialogMailToProjectIncludingParentProject"><?php echo i18n("colMailToProjectIncludingParentProject") ?>&nbsp;:&nbsp;</label>
              </td>
              <td>
                <div id="dialogMailToProjectIncludingParentProject" name="dialogMailToProjectIncludingParentProject" dojoType="dijit.form.CheckBox" type="checkbox"></div>
                 <?php echo i18n('globalProjectTeam');?>
              </td>
            </tr>
            <tr>
              <td class="dialogLabel">
                <label for="dialogMailToLeader"><?php echo i18n("colMailToLeader") ?>&nbsp;:&nbsp;</label>
              </td>
              <td>
                <div id="dialogMailToLeader" name="dialogMailToLeader" dojoType="dijit.form.CheckBox" type="checkbox" ></div>
              </td>
            </tr>
            <tr>
              <td class="dialogLabel">
                <label for="dialogMailToManager"><?php echo i18n("colMailToManager") ?>&nbsp;:&nbsp;</label>
              </td>
              <td>
                <div id="dialogMailToManager" name="dialogMailToManager" dojoType="dijit.form.CheckBox" type="checkbox" ></div>
              </td>
            </tr>
          <?php } ?>
            <?php if (property_exists($objectClass, '_Assignment') ) {
              $assigedLabel = i18n("colMailToAssigned");
              if($objectClass == 'Meeting'){
                $assigedLabel = i18n("colAttendees");
              }?>  
             <tr>
              <td class="dialogLabel">
                <label for="dialogMailToAssigned"><?php echo $assigedLabel; ?>&nbsp;:&nbsp;</label>
              </td>
              <td>
                <div id="dialogMailToAssigned" name="dialogMailToAssigned" dojoType="dijit.form.CheckBox" type="checkbox" ></div>
              </td>
            </tr>
            <?php }?>
            <tr>
              <td class="dialogLabel">
                <label for="dialogMailToSubscribers"><?php echo i18n("colMailToSubscribers") ?>&nbsp;:&nbsp;</label>
              </td>
              <td>
                <div id="dialogMailToSubscribers" name="dialogMailToSubscribers" dojoType="dijit.form.CheckBox" type="checkbox" ></div>
                <?php echo i18n('colMailToSubscribersDetail');?>
              </td>
            </tr>
            <tr>
              <td class="dialogLabel">
                <label for="dialogMailToOther"><?php echo i18n("colMailToOther") ?>&nbsp;:&nbsp;</label>
              </td>
              <td>
                <div id="dialogMailToOther" name="dialogMailToOther" dojoType="dijit.form.CheckBox" 
                 type="checkbox" onChange="dialogMailToOtherChange();">
                </div> <?php echo i18n('helpOtherEmail');?>
              </td>
            </tr>
            <tr>
              <td class="dialogLabel">
              </td>
              <td>
                <textarea dojoType="dijit.form.Textarea" 
  				          id="dialogOtherMail" name="dialogOtherMail"
  				          style="width: 500px; display:none"
  				          maxlength="4000"
  				          class="input" onblur="findAutoEmail();hideEmailHistorical();" oninput="compareEmailCurrent();" onclick="compareEmailCurrent();"></textarea>
  				      <textarea dojoType="dijit.form.Textarea" 
      					          id="dialogMailObjectIdEmail" name="dialogMailObjectIdEmail"
      					          style="width: 500px; display:none"
      					          class="input" onchange="dialogMailIdEmailChange()"></textarea>
  					    <td style="vertical-align: top">
  					    <?php if ($displayComboButton) {?>
                 <button id="otherMailDetailButton" dojoType="dijit.form.Button" showlabel="false"
                         style="display:none" title="<?php echo i18n('showDetail')?>"iconClass="iconView">
                   <script type="dojo/connect" event="onClick" args="evt">
                      dijit.byId('dialogMailObjectIdEmail').set('value',null);
                      showDetail('dialogMailObjectIdEmail', 0, 'Resource', true);
                   </script>
                 </button>
                 <?php }?>
                </td>
              </td>
            </tr>
            <tr>
              <td class="dialogLabel">
                <label for="dialogOtherMailHistorical"></label>
              </td>
              <td>
                <div id="dialogOtherMailHistorical" name="dialogOtherMailHistorical"
                     style="height:auto; margin-top:-1.9px; margin-left:0.5px; overflow-y:auto; position:relative; z-index: 999999999; 
                     display:none; width: 498px;  background-color:white; border:1px solid grey;">
                </div>
              </td>
            </tr>
            <tr>
              <td class="dialogLabel">
                <label for="dialogMailMessage"><?php echo i18n("colMailMessage") ?>&nbsp;:&nbsp;</label>
              </td>
              <td>
                 <textarea dojoType="dijit.form.Textarea" 
                    id="dialogMailMessage" name="dialogMailMessage"
                    style="width: 500px; "
                    maxlength="4000"
                    class="input" ></textarea>
              </td>
            </tr>
            <?php if (property_exists($objectClass, '_Note') ) {?>    
            <tr>
              <td class="dialogLabel">
                <label for="dialogMailSaveAsNote"><?php echo i18n("colSaveAsNote") ?>&nbsp;:&nbsp;</label>
              </td>
              <td>
                <div id="dialogMailSaveAsNote" name="dialogMailSaveAsNote" dojoType="dijit.form.CheckBox" type="checkbox" ></div>
              </td>
            </tr>
            <?php }?>
            <tr>
              <td class="dialogLabel">
                <label for="dialogMailEmailTemplate" class="generalColClass idEmailTemplateClass"><?php echo htmlEncode($obj->getColCaption("idEmailTemplate")); ?>&nbsp;:&nbsp;</label>
              </td>
              <td>
                <select dojoType="dijit.form.FilteringSelect" 
                id="selectEmailTemplate" name="selectEmailTemplate" class="input"
                <?php echo autoOpenFilteringSelect();?>>
                <option value=""></option>
                <?php foreach ($listEmailTemplate as $key => $value){?>
                <option value="<?php echo $value->id;?>"><span> <?php echo htmlEncode($value->name);?></span></option>
                <?php }?>
                <script type="dojo/connect" event="onChange" args="evt">
                  dojo.byId('idEmailTemplate').value = this.value;
                </script>
                <script type="dojo/connect" event="" args="evt">
                  dojo.byId('idEmailTemplate').value = this.value;
                </script>
               </select>
              </td>
            </tr>
          </table>
       </form>
     </td>
   </tr>
    <tr>
      <td align="center">
        <button dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogMail').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button dojoType="dijit.form.Button" type="submit" id="dialogMailSubmit" onclick="stockEmailCurrent();protectDblClick(this);sendMail();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>    