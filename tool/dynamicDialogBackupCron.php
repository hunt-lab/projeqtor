<?php 
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2015 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : -
 *
 * This file is part of ProjeQtOr.
 * 
 * ProjeQtOr is free software: you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free 
 * Software Foundation, either version 3 of the License, or (at your option) 
 * any later version.
 * 
 * ProjeQtOr is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for 
 * more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * ProjeQtOr. If not, see <http://www.gnu.org/licenses/>.
 *
 * You can get complete code of ProjeQtOr, other resource, help and information
 * about contributors at http://www.projeqtor.org 
 *     
 *** DO NOT REMOVE THIS NOTICE ************************************************/

/* ============================================================================
 * Habilitation defines right to the application for a menu and a profile.
 */ 
require_once "../tool/projeqtor.php";
//cron minute/hour/day of month/month/day of week
$minutes='*';
$hours='*';
$dayOfMonth='*';
$month='*';
$dayOfWeek='*';
if(Parameter::getGlobalParameter("plgBackupCron")!=null){
  $cronExecution=new CronExecution(Parameter::getGlobalParameter("plgBackupCron"));
  if($cronExecution->id == null){
    Parameter::storeGlobalParameter("plgBackupCron",null);
  }else{
    $cron=explode(" ",$cronExecution->cron);
    $minutes=$cron[0];
    $hours=$cron[1];
    $dayOfMonth=$cron[2];
    $month=$cron[3];
    $dayOfWeek=$cron[4];
  }
}
?>
<form id='plgBackupForm' name='plgBackupForm' onSubmit="return false;" >
<table style="width:100%;">
<tr>
  <td>
    <?php echo i18n("plgBackupMinutes");?>
  </td>
</tr>
<tr>
  <td>
    <select dojoType="dijit.form.FilteringSelect" class="input required" required="true"
    <?php echo autoOpenFilteringSelect ();?>
    style="width: 98%;" name="plgBackupMinutes" id="plgBackupMinutes">
    <?php echo htmlReturnOptionForMinutesHoursCron($minutes);?>
    </select>
  </td>
</tr>
<tr>
  <td>
    <?php echo i18n("plgBackupHours");?>
  </td>
</tr>
<tr>
  <td>
    <select dojoType="dijit.form.FilteringSelect" class="input required" required="true"
    <?php echo autoOpenFilteringSelect ();?>
    style="width: 98%;" name="plgBackupHours" id="plgBackupHours">
    <?php echo htmlReturnOptionForMinutesHoursCron($hours,true);?>
    </select>
  </td>
</tr>
<tr>
  <td>
    <?php echo i18n("plgBackupDayOfMonth");?>
  </td>
</tr>
<tr>
  <td>
    <select dojoType="dijit.form.FilteringSelect" class="input required" required="true"
    <?php echo autoOpenFilteringSelect ();?>
    style="width: 98%;" name="plgBackupDayOfMonth" id="plgBackupDayOfMonth">
    <?php echo htmlReturnOptionForMinutesHoursCron($dayOfMonth,false,true);?>
    </select>
  </td>
</tr>
<tr>
  <td>
    <?php echo i18n("plgBackupMonth");?>
  </td>
</tr>
<tr>
  <td>
    <select dojoType="dijit.form.FilteringSelect" class="input required" required="true"
    <?php echo autoOpenFilteringSelect ();?>
    style="width: 98%;" name="plgBackupMonth" id="plgBackupMonth">
    <?php echo htmlReturnOptionForMonthsCron($month);?>
    </select>
  </td>
</tr>
<tr>
  <td>
    <?php echo i18n("plgBackupDayOfWeek");?>
  </td>
</tr>
<tr>
  <td>
    <select dojoType="dijit.form.FilteringSelect" class="input required" required="true"
    <?php echo autoOpenFilteringSelect ();?>
    style="width: 98%;" name="plgBackupDayOfWeek" id="plgBackupDayOfWeek">
    <?php echo htmlReturnOptionForWeekdaysCron($dayOfWeek);?>
    </select>
  </td>
</tr>
      <tr>
      <td align="center">
        <input type="hidden" id="dialogKanbanResultAction">
        <button class="mediumTextButton"  dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogBackupCron').hide();formChangeInProgress=false;">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button class="mediumTextButton"  id="dialogKanbanResultSubmit" dojoType="dijit.form.Button" type="submit" onclick="protectDblClick(this);plgBackupSendCronUpdate();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
</table>
</form>
<?php 
function htmlReturnOptionForMinutesHoursCron($selection, $isHours=false, $isDayOfMonth=false, $required=false) {
  $arrayWeekDay=array();
  $nbTot=60;
  $start=0;
  if($isHours){
    $nbTot=24;
    $start=0;
  }
  if($isDayOfMonth){
    $nbTot=32;
    $start=1;
  }
  for($i=$start;$i<$nbTot;$i++){
    $key=$i;
    if($key<10)$key='0'.$key;
    $arrayWeekDay[$key]=$key;
  }
  $result="";
  if (! $required) {
    $result.='<option value="*" >'.i18n('all').'</option>';
  }
  foreach($arrayWeekDay as $key=>$line) {
    $result.= '<option value="' . $key . '"';
    if ( $selection and $key==$selection ) { $result.= ' SELECTED '; }
    $result.= '>'.$line. '</option>';
  }
  return $result;
}
function htmlReturnOptionForWeekdaysCron($selection, $required=false) {
  $arrayWeekDay=array('1'=>'Monday', '2'=>'Tuesday', '3'=>'Wednesday', '4'=>'Thursday',
      '5'=>'Friday', '6'=>'Saturday', '7'=>'Sunday');
  $result="";
  if (! $required) {
    $result.='<option value="*" >'.i18n('all').'</option>';
  }
  for ($key=1; $key<=7; $key++) {
    $result.= '<option value="' . $key . '"';
    if ( $selection and $key==$selection ) { $result.= ' SELECTED '; }
    $result.= '>'. i18n($arrayWeekDay[$key]) . '</option>';
  }
  return $result;
}

function htmlReturnOptionForMonthsCron($selection, $required=false) {
  $arrayMonth=array('01'=>'January', '02'=>'February', '03'=>'March', '04'=>'April',
      '05'=>'May', '06'=>'June', '07'=>'July','08'=>'August',
      '09'=>'September', '10'=>'October', '11'=>'November','12'=>'December');
  $result="";
  if (! $required) {
    $result.='<option value="*" >'.i18n('all').'</option>';
  }
  foreach($arrayMonth as $key=>$line) {
    $result.= '<option value="' . $key . '"';
    if ( $selection and $key==$selection ) { $result.= ' SELECTED '; }
    $result.= '>'. i18n($line) . '</option>';
  }
  return $result;
}
?>