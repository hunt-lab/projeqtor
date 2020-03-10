<?php
$class=RequestHandler::getClass('objectClass',true);
$id=RequestHandler::getValue('objectId');
$scope=RequestHandler::getValue('scope',true,'detail');
$obj=new $class($id);

$idx=0;
ob_start();
echo "<div style='width:450px;max-height:200px;overflow-y:auto;overflow-x:hidden'>";
echo "<table style='width:400px'>";

if ($scope=='detail') $page='objectDetail.php';
else $page='../tool/jsonQuery.php';
if ($scope=='detail') $context='template';
else $context='list';

echo "<tr>";
echo "<td style='width:300px'>".i18n("templateReportDefaultFormat")."</td>";
echo "<td>";
  drawButton('Print',$page,'html',$context);
echo "</td>";
echo "<td>";
  drawButton('Pdf',$page,'pdf',$context);
echo "</td>";
echo "<td>";
  drawButton('Download',$page,'pdf',($scope=='list')?'downloadList':'download');
echo "</td>";
echo "<td></td>";
echo "</tr>";

$context='template';
$tmpList=TemplateReport::getTemplateList($scope, $class, $obj);
usort($tmpList,"TemplateReport::sortByName");

if ($scope=='detail') $page='../report/object/'.$class.'.php';
else $page='../report/object/'.$class.'_List.php';
$extDownload=($scope=='list')?'List':'';
$extDownload="";

foreach ($tmpList as $tmpObj) {
  $idx++;
  if ($scope=='detail') $page='../report/object/'.$class.'.php?idTemplate='.$tmpObj->id;
  else $page='../report/object/'.$class.'_List.php?idTemplate='.$tmpObj->id;
  echo "<tr>";
  echo "<td style='width:300px'>".$tmpObj->name."</td>";
  if ($tmpObj->ext=='txt') {
    echo "<td>";
      drawButton('Print',$page,'txt',$context,true);
    echo "</td>";
    echo "<td>";
      drawButton('Pdf',$page,'pdf',$context,true);
    echo "</td>";
    echo "<td>";
      drawButton('Download',$page,'txt','download'.$extDownload);
    echo "</td>";
  } else if ($tmpObj->ext=='html') {
    echo "<td>";
      drawButton('Print',$page,'txt',$context,true);
    echo "</td>";
    echo "<td>";
      drawButton('Pdf',$page,'pdf','noheader'.$extDownload,true);
    echo "</td>";
    echo "<td>";
      drawButton('Download',$page,'html','download'.$extDownload,true);
    echo "</td>";
  } else {
    $icon='Download';
    if ($tmpObj->ext=='docx' or $tmpObj->ext=='odt') {
      $icon='Word';
      $mode='word';
    } else if ($tmpObj->ext=='xlsx' or $tmpObj->ext=='ods') {
      $icon='Excel';
      $mode='excel';
    } else if ($tmpObj->ext=='pptx' or $tmpObj->ext=='odp') {
      $icon='Powerpoint';
      $mode='powerpoint';
    }
    echo "<td></td>";
    echo "<td></td>";
    echo "<td>";
      drawButton($icon,$page,$mode,'download'.$extDownload);
    echo "</td>";
  }
  echo "<td>";
    showImageOfTemplate($tmpObj->templateFile);
  echo "</td>";
  echo "</tr>";
}

echo '</table>';
echo "</div>";

function drawButton($icon,$page,$mode,$context="template",$outputHtml=false) {
  global $idx,$scope;
  echo '<button id="templateButtonPrint'.$icon.$idx.'" dojoType="dijit.form.Button" showlabel="false"';
  $title=ucfirst($mode).ucfirst($context);
  switch (ucfirst($mode).ucfirst($context)) {
    case 'HtmlTemplate': case 'TxtTemplate': case 'HtmlList':
      $title='Show';
      break;
    case 'PdfTemplate': case 'PdfNoheader': case 'PdfList':
      $title='ShowPdf';
      break;
    case 'WordDownload': case "ExcelDownload": case "HtmlDownload": case "TxtDownload":  case "PowerpointDownload": case "DownloadDownload": case "PdfDownloadList":
      $title='Download';
      break;
  }
  if ($outputHtml) $page.="&outputHtml=true";
  echo '  title="'. i18n('titleReport'.$title).'"';
  echo '  iconClass="dijitButtonIcon dijitButtonIcon'.$icon.'" class="detailButton">';
  echo '  <script type="dojo/connect" event="onClick" args="evt">';
  echo '    showPrint("'.$page.'", "'.$context.'", null, "'.$mode.'", "'.(($scope=='detail')?'P':'L').'");';
  echo '    dijit.byId("dialogTemplateReportSelectTemplate").hide();';
  if ($mode=='pdf' and ($context=='download' or $context=='downloadList')) {
  echo '    if (dojo.byId("temporaryMessageText")) {';  
  echo '      dojo.byId("temporaryMessageText").innerHTML="<div class=\'messageOK\'>'.i18n('downloadWaitMessage').'</div>";';
  echo '      dojo.byId("temporaryMessage").style.display="block";';
  echo '      setTimeout("dojo.byId(\'temporaryMessage\').style.display=\'none\';",5000);';
  echo '    }';
  }
  echo '  </script>';
  echo '</button>';
}

function showImageOfTemplate($templateFile) {
  $maxWidth=64;
  $maxHeight=64;
  foreach (TemplateReport::$_allowedImageExtensions as $ext) {
    $imgFile='../plugin/templateReport/images/'.$templateFile.'.'.$ext;
    if (file_exists($imgFile)) {
      $imgSize=getimagesize($imgFile);
      $width=$imgSize[0];
      $height=$imgSize[1];
      $resize='';
      if ($width>$maxWidth) {
        $width=$maxWidth;
        $resize="width:".$maxWidth."px;";
      }
      $imgFile.='?random='.time();
      echo '<img src="'.$imgFile.'" style="'.$resize.'cursor:pointer;" onClick="zoomTemplateImage(\''.$imgFile.'\',\''.$templateFile.'.'.$ext.'\');" />';
      break;
    }
  }
}