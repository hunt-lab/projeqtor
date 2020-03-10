<?php 
/*** COPYRIGHT NOTICE *********************************************************
 *
******************************************************************************
*** WARNING *** T H I S    F I L E    I S    N O T    O P E N    S O U R C E *
******************************************************************************
*
* Copyright 2015 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
*
* This file is an add-on to ProjeQtOr, packaged as a plug-in module.
* It is NOT distributed under an open source license.
* It is distributed in a proprietary mode, only to the customer who bought
* corresponding licence.
* The company ProjeQtOr remains owner of all add-ons it delivers.
* Any change to an add-ons without the explicit agreement of the company
* ProjeQtOr is prohibited.
* The diffusion (or any kind if distribution) of an add-on is prohibited.
* Violators will be prosecuted.
*
*** DO NOT REMOVE THIS NOTICE ************************************************/

require_once "../tool/projeqtor.php";
require_once "../plugin/backupDatabase/backup.php";
$needRestartCron=false;
if ( array_key_exists('needRestartCron',$_REQUEST)) {
  $needRestartCron=true;
}

function startPlgBackupCron(){
	  global $startInclude,$paramDbHost,$paramDbUser,$paramDbPassword,$paramDbName,$paramDbPrefix,$paramDbPort;
  
  //plgBackupDayOfWeek plgBackupMonth plgBackupDayOfMonth plgBackupHours plgBackupMinutes
  if(array_key_exists('plgBackupDayOfWeek', $_REQUEST) 
  && array_key_exists('plgBackupMonth', $_REQUEST) 
  && array_key_exists('plgBackupDayOfMonth', $_REQUEST) 
  && array_key_exists('plgBackupHours', $_REQUEST)
  && array_key_exists('plgBackupMinutes', $_REQUEST)){
    
    $minutes=$_REQUEST['plgBackupMinutes'];
    $hours=$_REQUEST['plgBackupHours'];
    $dayOfMonth=$_REQUEST['plgBackupDayOfMonth'];
    $month=$_REQUEST['plgBackupMonth'];
    $dayOfWeek=$_REQUEST['plgBackupDayOfWeek'];
    
    $cronStr=$minutes.' '.$hours.' '.$dayOfMonth.' '.$month.' '.$dayOfWeek;
    $cronExecution=new CronExecution();
    if(Parameter::getGlobalParameter("plgBackupCron")!=null){
      $cronExecution=new CronExecution(Parameter::getGlobalParameter("plgBackupCron"));
    }else{
      $cronExecution->idle=0;
    }

    $cronExecution->fileExecuted="../tool/plgBackupCron.php";
    $cronExecution->fonctionName="startPlgBackupCron";
    $cronExecution->cron=$cronStr;
    $cronExecution->nextTime=null;
    traceLog($cronExecution->save());
  
    if(Parameter::getGlobalParameter("plgBackupCron")==null){
      Parameter::storeGlobalParameter("plgBackupCron",$cronExecution->id);
    }
  }else{
    
    Parameter::clearGlobalParameters();
    $nbFiles=5;
    if(Parameter::getGlobalParameter("plgBackupNbFiles")!=null){
      $nbFiles=Parameter::getGlobalParameter("plgBackupNbFiles");
    }
    $bk = new BackupMySQL(array(
      'host' => $paramDbHost,
    	'username' => $paramDbUser,
    	'passwd' => $paramDbPassword,
    	'dbname' => $paramDbName,
    	'dbprefix' => $paramDbPrefix,
    	'port' => $paramDbPort,
    	'dossier' => '../plugin/backupDatabase/backupDatabase/',
    	'anonymize' => false,
      'nbr_fichiers' => $nbFiles
    	));
      traceLog("BackupDatabase has been done by the Cron");
  }
}
if(!isset($inCronBlockFonctionCustom) || $inCronBlockFonctionCustom==null || isset($_REQUEST['forcePlgBackupCronStart'])){
  startPlgBackupCron();
}
?>