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

require_once "../tool/projeqtor.php";
require_once "../tool/formatter.php";
scriptLog('   ->/tool/refreshDataCloningCountDiv.php');

$userSelected = RequestHandler::getValue('userSelected');
$dataCloning = new DataCloning();
$date = date('Y-m-d');
$addDate =  addDaysToDate(date('Y-m-d'), 1);
$wherePerDay = "requestedDate > '$date' and requestedDate < '$addDate'";
$dataCloningCountPerDay = $dataCloning->countSqlElementsFromCriteria(null, $wherePerDay);
$dataCloningCountTotal = $dataCloning->countSqlElementsFromCriteria(array("idle"=>"0", "idResource"=>$userSelected));
$dataCloningPerDay = Parameter::getGlobalParameter('dataCloningPerDay');
$res = new Affectable($userSelected);
$dataCloningTotalObj=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', array("scope"=>"dataCloningTotal", "idProfile"=>$res->idProfile));
$dataCloningTotal = $dataCloningTotalObj->rightAccess;
?>
<table align="center">
  <tr>
    <td style="text-align:center;" class="dialogLabel">
      <?php echo i18n('colDataCloningCount', array($dataCloningPerDay-$dataCloningCountPerDay, $dataCloningPerDay));?>
    </td>
  </tr>
  <tr>
    <td style="text-align:center;" class="dialogLabel">
      <?php echo i18n('colDataCloningCountTotal', array($dataCloningTotal-$dataCloningCountTotal, $dataCloningTotal));?>
    </td>
  </tr>
</table>