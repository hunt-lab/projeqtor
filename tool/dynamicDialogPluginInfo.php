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

// TODO (SECURITY) : should be disabled until thoroughly fixed from security vulnerabilities (i.e. directory traversal)
include_once("../tool/projeqtor.php");
$filename=$_REQUEST['filename'];
$arrayData=Plugin::getMetadata($filename);
echo "<br/>";
echo "<table>";
foreach ($arrayData as $prop=>$val) {
  $label=i18n("col".substr($prop,6));
  echo '<tr><td class="input label" style="width:200px">'.$label.'&nbsp;:&nbsp;</td><td class="input">'.$val.'</td></tr>';
  echo '<tr><td colspan="2">&nbsp;</td></tr>';
}
echo "</table>"; 
echo "<br/>";
echo "<br/>";
