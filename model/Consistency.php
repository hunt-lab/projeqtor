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
 * Technical class to implement consistency checks
 */ 
require_once('_securityCheck.php');

class Consistency {

   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {

  }

   /** ==========================================================================
   * Destructor
   * @return void
   */ 
  function __destruct() {
    
  }
  
  // =================================================================================================================
  // WBS Ordering
  // =================================================================================================================
  
  /**
   * Check consistency of WBS ordering
   * @param string $display
   * @param string $correct
   */
  public static function checkWbs($correct=false,$trace=false) {
    $pe=new PlanningElement();
    $peList=$pe->getSqlElementsFromCriteria(null,null,"1=1",'wbsSortable asc');
    $lastWbs='';
    $lastPe=$pe;
    $errors=0;
    $arrayWbs=array();
    foreach ($peList as $idx=>$pe) {
      $currentWbs=$pe->wbsSortable;
      if ($trace) echo "$pe->wbsSortable - $pe->refType #$pe->refId - $pe->refName<br/>";
      if (!$pe->refType or !$pe->refId) {
        displayError(i18n("checkPlanningElementEmpty",array(i18n($pe->refType),$pe->refId,$pe->id)));
        $errors++;
        if ($correct) {
          $resultDeletePE=$pe->delete();
          if (getLastOperationStatus($resultDeletePE=="OK")) {
            displayOK(i18n("checkFixed"),true);
          } else {
            displayMsg(i18n("checkNotFixed"),true);
          }
        }
        continue;
      }
      // check for duplicate WBS
      if ($pe->wbsSortable==$lastWbs) {
        displayError(i18n("checkWbsDuplicate",array($lastWbs,i18n($lastPe->refType),$lastPe->refId,i18n($pe->refType),$pe->refId)));
        $errors++;
        if ($correct) {
          self::fixOrder($pe,"duplicate",$peList,$idx);
        }
      }
      // Check Parent
      $parentWbs='';
      if ($pe->topRefType and $pe->topRefId) {
        $key=$pe->topRefType.'#'.$pe->topRefId;
        $parentWbs=(isset($arrayWbs[$key]))?$arrayWbs[$key]:'';
        if (!$parentWbs) { // Possibly just incorrect order
          $parentPe=SqlElement::getSingleSqlElementFromCriteria('PlanningElement', array('refType'=>$pe->topRefType, 'refId'=>$pe->topRefId));
          if ($parentPe and $parentPe->id) {
            $parentWbs=$parentPe->wbsSortable;
          }
        }
        if ($parentWbs=='') {
          displayError(i18n("checkWbsParentNotFound",array($pe->topRefType,$pe->topRefId, i18n($pe->refType), $pe->refId)));
          $errors++;
          if ($correct) {
            displayError(i18n("checkCannotFix"),true);
          }
        }
      }
      if ($parentWbs and $parentWbs!=substr($pe->wbsSortable,0,strlen($parentWbs))) {
        displayError(i18n("checkWbsParentIssue",array($pe->wbsSortable,i18n($pe->refType),$pe->refId,$parentWbs,$pe->topRefType,$pe->topRefId)));
        $errors++;
        if ($correct) {
          self::fixOrder($pe,"parent",$peList,$idx);
        }
      } else if ($parentWbs and strlen($pe->wbsSortable)!=strlen($parentWbs)+6) {
        displayError(i18n("checkWbsParentIssue",array($pe->wbsSortable,i18n($pe->refType),$pe->refId,$parentWbs,$pe->topRefType,$pe->topRefId)));
        $errors++;
        if ($correct) {
          self::fixOrder($pe,"parent",$peList,$idx);
        }
      }
      // Check Order
      $order=substr($pe->wbsSortable,-5);
      if ($lastWbs==$parentWbs) { // Previous is parent, so must be 001
        if (intval($order)!=1) {
          displayError(i18n("checkWbsFirst", array($pe->wbsSortable,i18n($pe->refType),$pe->refId)));
          $errors++;
          if ($correct) {
            self::fixOrder($pe,"first",$peList,$idx);
          }
        }
      } else if (substr($lastWbs,0,-6)==$parentWbs) { // Previous has same root (same parent), number must be is sequence
        if (intval($order)!=intval(substr($lastWbs,-5))+1) {
          displayError(i18n("checkWbsOrder",array($pe->wbsSortable, i18n($pe->refType), $pe->refId, $lastWbs)));
          $errors++;
          if ($correct) {
            self::fixOrder($pe,"order",$peList,$idx);
          }
        }
      } else { // Change root, current numbering must be is sequence
        $rootPrev=substr($lastWbs,0,strlen($pe->wbsSortable));
        if (intval($order)!=intval(substr($rootPrev,-5))+1) {
          displayError(i18n("checkWbsOrder",array($pe->wbsSortable, i18n($pe->refType), $pe->refId,$lastWbs)));
          $errors++;
          if ($correct) {
            self::fixOrder($pe,"order",$peList,$idx);
          }
        }
      }
      // Check displayed wbs compared to wbsSortable
  
      // Check project order
      if ($pe->refType=='Project') {
        $prj=new Project($pe->refId);
        $pe=$prj->ProjectPlanningElement;
        if ($prj->sortOrder!=$pe->wbsSortable) {
          displayError(i18n("checkWbsSortOrderProject",array($prj->id,$prj->sortOrder,$pe->wbsSortable)));
          $errors++;
          if ($correct) {
            $prj->sortOrder=$pe->wbsSortable;
            $res=$prj->save();
            if (getLastOperationStatus($res)=='OK'  or getLastOperationStatus($res)=='NO_CHANGE') displayOK(i18n("checkFixed"),true);
            else displayError($res,true);
          }
        }
  
      }
      // Continue
      $key=$pe->refType.'#'.$pe->refId;
      $arrayWbs[$key]=$currentWbs;
      $lastWbs=$currentWbs;
      $lastPe=$pe;
    }
    if (!$errors) {
      displayOK(i18n("checkNoError"));
    }
  }

  private static function fixOrder($pe, $issue,$peList,$idx) {
    $actual=new PlanningElement($pe->id);
    if ($pe->wbsSortable!=$actual->wbsSortable) {
      displayOK(i18n("checkFixed"),true);
      return;
    }
    $action="unknown";
    $peNext=null;
    $pePrec=null;
    if ($issue=="duplicate" or $issue=="parent") { // Duplicate or inconsistent with Parent => just get a good one (order is sure incorrect)
      $action="recalculate";
    } else if ($issue=="first") {
      $action="recalculateLevel";
    } else if ($issue=="order") {
      $action="recalculate";
      $cur=$idx-1;
      while ($action=="recalculate" and $cur>=0) {
        $pePrec=$peList[$cur];
        if (substr($pe->wbsSortable,0,strlen($pe->wbsSortable)-5)!=substr($pePrec->wbsSortable,0,strlen($pe->wbsSortable)-5)) {
          $cur=-1;
          break;
        }
        if (strlen($pe->wbsSortable)==strlen($pePrec->wbsSortable)) {
          $action="moveFromPrec";
        }
        $cur--;
      }
    } else {
      displayError(i18n("checkCannotFix"),true);
    }
    if ($action=="recalculate") {
      $pe->wbs=null;
      $pe->wbsSortable=null;
      $res=$pe->save();
      if (getLastOperationStatus($res)=='OK' or getLastOperationStatus($res)=='NO_CHANGE') displayOK(i18n("checkFixed"),true);
      else displayError($res,true);
    } else if ($action=='moveFromPrec' and $pePrec) {
      $res=$pe->moveTo($pePrec->id,'after');
      if (getLastOperationStatus($res)=='OK' or getLastOperationStatus($res)=='NO_CHANGE') displayOK(i18n("checkFixed"),true);
      else displayError($res,true);
    } else if ($action=="recalculateLevel" and $pe->topId) {
      $where="topId=$pe->topId";
      $levelList=$pe->getSqlElementsFromCriteria(null,null,$where,'wbsSortable');
      if (count($levelList)==1) {
        $pe->wbs=null;
        $pe->wbsSortable=null;
        $res=$pe->save();
        if (getLastOperationStatus($res)=='OK' or getLastOperationStatus($res)=='NO_CHANGE') displayOK(i18n("checkFixed"),true);
        else displayError($res,true);
      } else if (count($levelList)>1) {
        $first=$levelList[0];
        $second=$levelList[1];
        $res=$second->moveTo($first->id,'before');
        $first=new PlanningElement($first->id);
        $second=new PlanningElement($second->id);
        $res=$first->moveTo($second->id,'before');
        if (getLastOperationStatus($res)=='OK' or getLastOperationStatus($res)=='NO_CHANGE') displayOK(i18n("checkFixed"),true);
        else displayError($res,true);
      }
    } else {
      if ($issue!='') {
        displayError(i18n("checkCannotFix"),true);
      }
    }
  }
  
  // =================================================================================================================
  // Work Duplicate
  // =================================================================================================================
  
  public static function checkDuplicateWork($correct=false,$trace=false) {
    $errors=0;
    // Direct Query : valid here for technical needs on grouping
    $work=new Work();
    $workTable=$work->getDatabaseTableName();
    $query="SELECT idAssignment as idassignment, idResource as idResource, refType as reftype, refId as refid, idWorkElement as idworkelement, day as day, count(*) as cpt from $workTable group by idAssignment, idResource, refType, refId, idWorkElement, day having count(*)>1";
    $result=Sql::query($query);
    while ($line = Sql::fetchLine($result)) {
      $idAss=$line['idassignment'];
      $refType=$line['reftype'];
      $refId=$line['refid'];
      $idWork=$line['idworkelement'];
      $day=$line['day'];
      $cpt=$line['cpt'];
      $lstWork=$work->getSqlElementsFromCriteria(array('idAssignment'=>$idAss,'day'=>$day, 'refType'=>$refType, 'refId'=>$refId, 'idWorkElement'=>$idWork),null,'id asc');
      $wk=reset($lstWork);
      $resName=SqlList::getNameFromId('Affectable', $wk->idResource);
      displayError(i18n("checkDuplicateWorkFound",array($resName,htmlFormatDate($wk->workDate),i18n($wk->refType),$wk->refId)));
      $errors++;
      if ($correct) {
        $nb=0;
        $res='';
        foreach ($lstWork as $work) {
          if ($nb==0 and $work->work!=0) {
            $nb++;
            // Do not delete first not null
          } else {            
            $res=$work->delete();
          }
          
        }
        if (getLastOperationStatus($res)=='OK'  or getLastOperationStatus($res)=='NO_CHANGE') displayOK(i18n("checkFixed"),true);
        else displayError($res,true);
      }
    }
    if (!$errors) {
      displayOK(i18n("checkNoError"));
      
    }
  }
  
  // =================================================================================================================
  // Work On Ticket
  // =================================================================================================================
  
  public static function checkWorkOnTicket($correct=false,$trace=false) {
    $errors=0;
    // Direct Query : valid here for technical needs on grouping
    $work=new Work();
    $workTable=$work->getDatabaseTableName();
    $we=new workElement();
    $weTable=$we->getDatabaseTableName();
    $query="SELECT we.refType as reftype, we.refId as refid, we.realWork as realwork, (select sum(work) from $workTable w where w.idWorkElement=we.id) as sumwork from $weTable we where realwork!=(select sum(work) from $workTable w where w.idWorkElement=we.id) ";
    $result=Sql::query($query);
    while ($line = Sql::fetchLine($result)) {
      $refType=$line['reftype'];
      $refId=$line['refid'];
      $realWork=$line['realwork'];
      $sumWork=$line['sumwork'];
      if (Work::displayWorkWithUnit($realWork)==Work::displayWorkWithUnit($sumWork)) continue; // It is just a rounding issue
      displayError(i18n("checkIncorrectWork",array(i18n($refType),$refId,Work::displayWorkWithUnit($realWork),Work::displayWorkWithUnit($sumWork))));
      $errors++;
      if ($correct) {
        $elt=new $refType($refId);
        $res=$elt->save();
        $we=SqlElement::getSingleSqlElementFromCriteria('WorkElement', array('refType'=>$refType, 'refId'=>$refId));
        if ($we->id) {
          $we->realWork=$sumWork;
          $resWe=$we->simpleSave();
        }
        if (getLastOperationStatus($res)=='OK' or (isset($resWe) and getLastOperationStatus($resWe)=='OK') ) {
          displayOK(i18n("checkFixed"),true);
        } else {
          displayMsg(i18n("checkNotFixed"),true);
          $query="SELECT idResource as idres, sum(work) as sumwork from $workTable w where w.refType='$refType' and w.refId=$refId group by idResource";
          $resultRes=Sql::query($query);
          while ($lineRes = Sql::fetchLine($resultRes)) {
            $idRes=$lineRes['idres'];
            $sumWork=$lineRes['sumwork'];
            displayMsg('&nbsp;-&nbsp;'.SqlList::getNameFromId('Affectable', $idRes).' : '.Work::displayWorkWithUnit($sumWork),true);
          }
        
          
        }
        
      }
    }
    if (!$errors) {
      displayOK(i18n("checkNoError"));
  
    }
  }
  
  // =================================================================================================================
  // Work On Activity
  // =================================================================================================================
  
  public static function checkWorkOnActivity($correct=false,$trace=false) {
    $errors=0;
    // Direct Query : valid here for technical needs on grouping
    $work=new Work();
    $workTable=$work->getDatabaseTableName();
    $pe=new PlanningElement();
    $peTable=$pe->getDatabaseTableName();
    $we=new WorkElement();
    $weTable=$we->getDatabaseTableName();
    $query="SELECT pe.refType as reftype, pe.refId as refid, pe.realWork as realwork, pe.leftWork as leftwork, pe.plannedWork as plannedwork,"
          ."  coalesce((select sum(work) from $workTable w where w.refType=pe.refType and w.refId=pe.refId),0)"
          ."+coalesce((select sum(pesum.realWork) from $peTable pesum where pesum.topId=pe.id),0)"
          ."+coalesce((select sum(coalesce(wesum.realWork,0)) from $weTable wesum where pe.refType='Project' and wesum.idProject=pe.refId and wesum.idActivity is null),0)"
          ." as sumwork "
          ."FROM $peTable pe "
          ."WHERE pe.isManualProgress=0 and ( (pe.realWork+pe.leftWork)!=pe.plannedWork or pe.realwork!="
           ."coalesce((select sum(work) from $workTable w where w.refType=pe.refType and w.refId=pe.refId),0)"
           ."+coalesce((select sum(pesum.realWork) from $peTable pesum where pesum.topId=pe.id),0)"
           ."+coalesce((select sum(coalesce(wesum.realWork,0)) from $weTable wesum where pe.refType='Project' and wesum.idProject=pe.refId and wesum.idActivity is null),0)"
           ." )";
    $result=Sql::query($query);
    while ($line = Sql::fetchLine($result)) {
      $refType=$line['reftype'];
      $refId=$line['refid'];
      $realWork=$line['realwork'];
      $leftWork=$line['leftwork'];
      $plannedWork=$line['plannedwork'];
      $sumWork=$line['sumwork'];      
      if(!$sumWork){
        $sumWork = 0;
      }
      if (Work::displayWorkWithUnit($realWork)==Work::displayWorkWithUnit($sumWork) and Work::displayWorkWithUnit($realWork+$leftWork)==Work::displayWorkWithUnit($plannedWork)) {
        continue; // It is just a rounding issue
      } else if (abs($realWork+$leftWork-$plannedWork)<0.01) {
        continue; // It is just a rounding issue
      }
      $errorDisplayed=false;
      if (round($realWork,2)!=round($sumWork,2)) {
        displayError(i18n("checkIncorrectWork",array(i18n($refType),$refId,Work::displayWorkWithUnit($realWork),Work::displayWorkWithUnit($sumWork))));
        $errorDisplayed=true;
      }
      if (round($realWork+$leftWork,2)!=round($plannedWork,2)) {
        displayError(i18n("checkIncorrectSumWork",array(i18n($refType),$refId,Work::displayWorkWithUnit($realWork),Work::displayWorkWithUnit($leftWork),Work::displayWorkWithUnit($plannedWork))));
        $errorDisplayed=true;
      }
      if (!$errorDisplayed) {
        $msg=(substr(i18n("checkUnknownError"),0,1)=='[')?"Unknown error for $refType #$refId":i18n("checkUnknownError",array(i18n($refType),$refId));
        displayError($msg);
        traceLog($msg. " at Consistency::checkWorkOnActivity() | realWork=$realWork | leftWork=$leftWork | plannedWork=$plannedWork | sumWork=$sumWork");
      }
      $errors++;
      if ($correct) {
        $res=PlanningElement::updateSynthesis($refType,$refId);
        if (getLastOperationStatus($res)!='OK') {
          displayMsg(i18n("checkNotFixed"),true);
          $query="SELECT idResource as idres, sum(work) as sumwork from $workTable w where w.refType='$refType' and w.refId=$refId group by idResource";
          $resultRes=Sql::query($query);
          while ($lineRes = Sql::fetchLine($resultRes)) {
            $idRes=$lineRes['idres'];
            $sumWork=$lineRes['sumwork'];
            displayMsg('&nbsp;-&nbsp;'.SqlList::getNameFromId('Affectable', $idRes).' : '.Work::displayWorkWithUnit($sumWork),true);
          }
        } else {
          displayOK(i18n("checkFixed"),true);
        }
      }
    }
    if (!$errors) {
      displayOK(i18n("checkNoError"));
  
    }
  }
  
  // =================================================================================================================
  // Work On Assignment
  // =================================================================================================================
  
  public static function checkWorkOnAssignment($correct=false,$trace=false) {
    $errors=0;
    // Direct Query : valid here for technical needs on grouping
    $work=new Work();
    $workTable=$work->getDatabaseTableName();
    $ass=new Assignment();
    $assTable=$ass->getDatabaseTableName();
    $query="SELECT ass.id as id, ass.refType as reftype, ass.refId as refid, ass.realWork as realwork, ass.leftWork as leftwork, ass.plannedWork as plannedwork,"
        ."  (select sum(work) from $workTable w where w.idAssignment=ass.id) as sumwork, ass.idResource as idresource "
        ."FROM $assTable ass "
        ."WHERE realwork!=(select sum(work) from $workTable w where w.idAssignment=ass.id) "
        ."   OR (coalesce(ass.realWork,0)+coalesce(ass.leftWork,0))!=coalesce(ass.plannedWork,0) ";
    $result=Sql::query($query);
    while ($line = Sql::fetchLine($result)) {
      $id=$line['id'];
      $refType=$line['reftype'];
      $refId=$line['refid'];
      $realWork=$line['realwork'];
      $leftWork=$line['leftwork'];
      $plannedWork=$line['plannedwork'];
      $idResource=$line['idresource'];
      $sumWork=$line['sumwork'];
      if (Work::displayWorkWithUnit($realWork)==Work::displayWorkWithUnit($sumWork) and Work::displayWorkWithUnit($realWork+$leftWork)==Work::displayWorkWithUnit($plannedWork)) continue; // It is just a rounding issue
      if ($realWork!=$sumWork) displayError(i18n("checkIncorrectWork",array(i18n($refType),$refId,Work::displayWorkWithUnit($realWork),Work::displayWorkWithUnit($sumWork))));
      if ($realWork+$leftWork!=$plannedWork) displayError(i18n("checkIncorrectSumWork",array(i18n($refType),$refId.' ['.i18n('Resource').' #'.$idResource.']',Work::displayWorkWithUnit($realWork),Work::displayWorkWithUnit($leftWork),Work::displayWorkWithUnit($plannedWork))));
      $errors++;
      if ($correct) {
        $ass=new Assignment($id);
        $res=$ass->saveWithRefresh();
        if (getLastOperationStatus($res)!='OK') {
          displayMsg(i18n("checkNotFixed"),true);
        } else {
          displayOK(i18n("checkFixed"),true);
        }
      }
    }
    // Check Resource 
    $res=new Resource();
    $resTable=$res->getDatabaseTableName();
    $query="SELECT a.id as assid, a.idResource as assress, w.id as workid, w.idResource as workres, w.workDate as workdate, w.refType as reftype, w.refid as refid "
        ." FROM $workTable w, $assTable a, $resTable r "
        ." where w.idAssignment=a.id and a.idResource=r.id and r.isResourceTeam=0 and a.idResource!=w.idResource";
    $result=Sql::query($query);
    while ($line = Sql::fetchLine($result)) {
      $wRes=SqlList::getNameFromId('Affectable', $line['workres']);
      $aRes=SqlList::getNameFromId('Affectable', $line['assress']);
      $date=htmlFormatDate($line['workdate']);
      displayError(i18n('errorWorkResource',array($wRes,$aRes,$date,$line['workid'],$line['assid'],i18n($line['reftype']),$line['refid'])));
      $errors++;
      if ($correct) {
        $w=new Work($line['workid']);
        $w->idResource=$line['assress'];
        $res=$w->save();
        if (getLastOperationStatus($res)!='OK') {
          displayMsg(i18n("checkNotFixed"),true);
          debugTraceLog($res);
        } else {
          displayOK(i18n("checkFixed"),true);
        }
      }
    }
    // Check work with no project
    $query="SELECT w.id as id, w.idAssignment as assid, w.refType as reftype, w.refId as refid"
        ." FROM $workTable w "
        ." WHERE w.idProject is null or w.idProject=0 ";
    $result=Sql::query($query);
    while ($line = Sql::fetchLine($result)) {
      $id=$line['id'];
      $refType=$line['reftype'];
      $refId=$line['refid'];
      $assid=$line['assid'];
      displayError(i18n('checkWorkWithoutProject',array($id,i18n($refType),$refId)));
      $errors++;
      if ($correct) {
        $w=new Work($id);
        $proj=null;
        $ass=new Assignment($assid);
        if ($ass->idProject) {
          $proj=$ass->idProject;
        } else if ($refType and $refId) {
          $obj=new $refType($refId);
          if (property_exists($obj, 'idProject') and $obj->idProject) {
            $proj=$obj->idProject;
          }
        }
        if ($proj) {
          $w->idProject=$proj;
          $res=$w->save();
        } else {
          $res="No Project found for Assignment #$assid and for $refType #$refId";
        }
        if (getLastOperationStatus($res)!='OK') {
          displayMsg(i18n("checkNotFixed"),true);
          debugTraceLog($res);
        } else {
          displayOK(i18n("checkFixed"),true);
        }
      }
      }  
     
    if (!$errors) {
      displayOK(i18n("checkNoError"));
  
    }
  }
  
  // =================================================================================================================
  // Idle consistency from Activity / PlanningElement / Assignment
  // =================================================================================================================
  
  public static function checkIdlePropagation($correct=false,$trace=false) {
    $errors=0;
    // Direct Query : valid here for technical needs on grouping
    $actArray=array('Activity','Meeting','TestSession');
    foreach ($actArray as $type) {
      $ass=new Assignment();
      $assTable=$ass->getDatabaseTableName();
      $act=new $type();
      $actTable=$act->getDatabaseTableName();
      $pe=new PlanningElement();
      $peTable=$pe->getDatabaseTableName();
      $query="SELECT act.id as actid, pe.id as peid, ass.id as assid, act.idle as actidle, pe.idle as peidle, ass.idle as assidle "
          ." FROM $actTable as act left join $peTable as pe on (pe.refType='$type' and pe.refId=act.id) left join $assTable ass on (ass.refType='$type' and ass.refId=act.id)"
          ." WHERE act.idle!=pe.idle or (act.idle!=ass.idle and ass.idle is not null)";
      $result=Sql::query($query);
      while ($line = Sql::fetchLine($result)) {
        $actId=$line['actid'];
        $peId=$line['peid'];
        $assId=$line['assid'];
        $actIdle=$line['actidle'];
        $peIdle=$line['peidle'];
        $assIdle=$line['assidle'];
        displayError(i18n("checkIncorrectIdle",array(i18n($type),$actId,$actIdle,$peIdle,$assIdle)));
        $errors++;
        if ($correct) {
          if ($assId) {
            $ass=new Assignment($assId);
            $ass->idle=$actIdle;
            if ($ass->idle==1) $ass->leftWork=0;
            $resAss=$ass->save();
          }
          if ($peId) {
            $pe=new PlanningElement($peId);
            $pe->idle=$actIdle;
            if ($pe->idle==1) $pe->leftWork=0;
            $resPe=$pe->save();
          }
          $act=new $type($actId);
          $resAct=$act->save();
          if (getLastOperationStatus($resAct)=='OK' or (isset($resPe) and getLastOperationStatus($resPe)=='OK') or (isset($resAss) and getLastOperationStatus($resAss)=='OK') ) {
            displayOK(i18n("checkFixed"),true);
          } else {
            displayMsg(i18n("checkNotFixed"),true);
          }
        }
      }
    }
    if (!$errors) {
      displayOK(i18n("checkNoError"));
  
    }
  }
  
  // =================================================================================================================
  // Missing (or Extra) Planning Elements
  // =================================================================================================================
  
  public static function checkMissingPlanningElement($correct=false,$trace=false) {
    $errors=0;
    // Direct Query : valid here for technical needs on grouping
    $work=new Work();
    $workTable=$work->getDatabaseTableName();
    $pe=new PlanningElement();
    $peTable=$pe->getDatabaseTableName();
    $ass=new Assignment();
    $assTable=$ass->getDatabaseTableName();
    
    $query="SELECT ass.refType as reftype, ass.refId as refid, ass.idResource as idresource, ass.id as id from $assTable ass"
         . "  WHERE (select count(*) from $peTable pe where pe.refType=ass.refType and pe.refId=ass.refId )!=1";
    $result=Sql::query($query);
    $stockRefType='';
    $stockRefId='';
    while ($line = Sql::fetchLine($result)) {
      $refType=$line['reftype'];
      $refId=$line['refid'];
      $assId=$line['id'];
      if ($refType==$stockRefType and $refId==$stockRefId) continue;
      $stockRefType=$refType;
      $stockRefId=$refId;
      $listPe=$pe->getSqlElementsFromCriteria(array('refType'=>$refType,"refId"=>$refId));
      if (count($listPe)==0) {
        displayError(i18n("checkPlanningElementMissing",array(i18n($refType),$refId)));
        $errors++;
        if ($correct) {
          $peNameForRefObj=$refType."PlanningElement";
          $pmNameForRefObj="id".$refType."PlanningMode";
          $refObjFromPlan=new $refType($refId);
          if ($refObjFromPlan->id) { // Assignment refers to existing item
            if (property_exists($refObjFromPlan,$peNameForRefObj) and is_object($refObjFromPlan->$peNameForRefObj)
            and property_exists($refObjFromPlan->$peNameForRefObj, $pmNameForRefObj) and !$refObjFromPlan->$peNameForRefObj->$pmNameForRefObj) {
              $planningModeList=SqlList::getList('PlanningMode','applyTo');
              foreach ($planningModeList as $pmId=>$pmApplyTo) {
                if ($pmApplyTo==$refType) {
                  $refObjFromPlan->$peNameForRefObj->$pmNameForRefObj=$pmId;
                  break;
                }
              }
            }
            $resultSaveObjFromPlan=$refObjFromPlan->save();
            if (getLastOperationStatus($resultSaveObjFromPlan=="OK")) {
              displayOK(i18n("checkFixed"),true);
            } else {
              displayMsg(i18n("checkNotFixed"),true);
            }
          } else { // Assignment refers to no existing item : delete
            $ass=new Assignment($assId);
            if ($ass->id) {
            	$resultDeleteInvalidAssignement=$ass->delete();
              if (getLastOperationStatus($resultDeleteInvalidAssignement=="OK")) {
                displayOK(i18n("checkFixed"),true);
              } else {
                displayMsg(i18n("checkNotFixed"),true);
              }
            } else {
            	displayMsg(i18n("checkNotFixed"),true);
            }
          }
        }
      } else {
        displayError(i18n("checkPlanningElementExtra",array(i18n($refType),$refId,count($listPe))));
        $errors++;
        if ($correct) {
          displayMsg(i18n("checkNotFixed"),true);
        }
      }
    }
    if (!$errors) {
      displayOK(i18n("checkNoError"));
  
    }
  }
  
  // =================================================================================================================
  // Idle consistency from Activity / PlanningElement / Assignment
  // =================================================================================================================
  
  public static function checkInvalidFilters($correct=false,$trace=false) {
    $errors=0;
    // Direct Query : valid here for technical needs on grouping
    $crit=new FilterCriteria();
    $critTable=$crit->getDatabaseTableName();
    $filter=new Filter();
    $filterTable=$filter->getDatabaseTableName();
    $query="SELECT crit.id as idcrit, crit.dispAttribute as attribute, filter.name as name, filter.refType as reftype, filter.idUser as user "
        ." FROM $critTable crit, $filterTable filter"
        ." WHERE crit.idFilter=filter.id and crit.sqlOperator='IN' and crit.sqlValue='0' and crit.isDynamic=0";
    $result=Sql::query($query);
    while ($line = Sql::fetchLine($result)) {
      $type=$line['reftype'];
      $userName=SqlList::getNameFromId('User', $line['user']);
      $attribute=$line['attribute'];
      $filterName=$line['name'];
      $id=$line['idcrit'];
      displayError(i18n("checkIncorrectFilterCriteria",array($filterName,i18n($type),$userName,$attribute)));
      $errors++;
      if ($correct) {
        $fc=new FilterCriteria($id);
        $res=$fc->delete();
        if (getLastOperationStatus($res)=='OK') {
          displayOK(i18n("checkFixed"),true);
        } else {
          displayMsg(i18n("checkNotFixed"),true);
        }
      }
    }

    if (!$errors) {
      displayOK(i18n("checkNoError"));
  
    }
  }
}?>