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

/** ===========================================================================
 * Delete the current object : call corresponding method in SqlElement Class
 */

require_once "../tool/projeqtor.php";

$versionProjectId=null;
if (array_key_exists('idVersionProject',$_REQUEST)) {
  $versionProjectId=$_REQUEST['idVersionProject']; // validated to be numeric value in SqlElement base constructor.
}
$versionProjectId=trim($versionProjectId);
if ($versionProjectId=='') {
  $versionProjectId=null;
} 
if ($versionProjectId==null) {
  throwError('versionProjectId parameter not found in REQUEST');
}

Sql::beginTransaction();
$obj=new VersionProject($versionProjectId);

global $doNotUpdateAllVersionProject; // for Perfs improvment
$doNotUpdateAllVersionProject=true;

$result=$obj->delete();

$obj->propagateDeletionToComponentVersions();
$doNotUpdateAllVersionProject=false; // Finish perfs improvment

// Message of correct saving
displayLastOperationStatus($result);
?>