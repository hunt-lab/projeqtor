<?php
use Spipu\Html2Pdf\Html2Pdf;
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
 * Print page of application.
 */
/* Possible outMode
 *  - html     : printing of detail or list or report (it is default value)
 *  - pdf      : pdf printing
 *  - csv      : CSV export of list
 *  - mpp      : export of planning to project xml format
 *  - word     : export to word (TBS)
 *  - excel    : export to excel (TBS)
 *  - download : direct download, whatever the file
 *  - downloadPdf : transform to pdf (like "pdf") and direct download file
 */
   require_once "../tool/projeqtor.php";
   scriptLog('   ->/view/print.php'); 
   projeqtor_set_time_limit(300);
   ob_start();
   $outMode='html';
   $printInNewPage=getPrintInNewWindow();
   if (array_key_exists('outMode', $_REQUEST)) {
     if ($_REQUEST['outMode']) {
       $outMode=$_REQUEST['outMode'];
     }
   }
   $orientation='L';
   if (array_key_exists('orientation', $_REQUEST)) {
   	$orientation=$_REQUEST['orientation'];
   }
   $download=(RequestHandler::getValue('context')=='download' or RequestHandler::getValue('context')=='downloadList')?true:false; // Attention, download without extend is not like other download outmode
   $noHeader=(RequestHandler::getValue('context')=='noheader' or RequestHandler::getValue('context')=='noheaderList')?true:false;
   if ($outMode=='downloadPdf') $outMode='pdf';
   if ($outMode=='pdf') {
     $printInNewPage=getPrintInNewWindow('pdf');
     $memoryLimitForPDF=Parameter::getGlobalParameter('paramMemoryLimitForPDF');
     if (isset($memoryLimitForPDF)) {
       $limit=$memoryLimitForPDF;	
     } else {
     	 $limit='';
     }
     if ($limit===0) {
     	 header ('Content-Type: text/html; charset=UTF-8');
     	 echo "<html><head></head><body>";
     	 echo i18n("msgPdfDisabled");
     	 echo "</body></html>";
     	 return;
     } else if ($limit=='') {
       // Keep existing
     } else {
      projeqtor_set_memory_limit($limit.'M');
     }
   } 
   if ($outMode=='csv')  {
     $contentType="application/force-download";
     $nameReport=RequestHandler::getValue('reportCodeName');
     $objectClass=RequestHandler::getValue('objectClass');
     $name="export_" . (($nameReport)?$nameReport:$objectClass) . "_" . date('Ymd_His') . ".csv";
     header("Content-Type: " . $contentType . "; name=\"" . $name . "\""); 
	   header("Content-Transfer-Encoding: binary"); 
	   //header("Content-Length: $size"); 
	   header("Content-Disposition: attachment; filename=\"" .$name . "\""); 
	   header("Expires: 0"); 
	   header("Cache-Control: no-cache, must-revalidate");
	   header("Pragma: no-cache");
   } else if ($outMode=='mpp')  {
     $contentType="application/force-download";
     $name="export_planning_" . date('Ymd_His') . ".xml";
     header("Content-Type: " . $contentType . "; name=\"" . $name . "\""); 
     header("Content-Transfer-Encoding: binary"); 
     //header("Content-Length: $size"); 
     header("Content-Disposition: attachment; filename=\"" .$name . "\""); 
     header("Expires: 0"); 
     header("Cache-Control: no-cache, must-revalidate");
     header("Pragma: no-cache");
   } else if ($outMode=='word' or $outMode=='excel' or $outMode == "download")  {
     
   } else if ($download)  { // all downloadXxx except download
     $printInNewPage=true;
     $contentType="application/force-download";
     $nameReport=RequestHandler::getValue('reportCodeName');
     $objectClass=RequestHandler::getValue('objectClass');
     $objectId=RequestHandler::getValue('objectId');
     $ext=strtolower(str_replace('download','',$outMode));
     if ($objectClass) {
       if ($objectId) {
         $name=i18n($objectClass).'_'.intval($objectId);
       } else {
         $name=i18n('menu'.$objectClass);
       }
     } else {
       $name="export";
     }
     $name.="_".date('Ymd_His').'.'.$ext;
     header("Content-Type: " . $contentType . "; name=\"" . $name . "\""); 
	   header("Content-Transfer-Encoding: binary"); 
	   //header("Content-Length: $size"); 
	   header("Content-Disposition: attachment; filename=\"" .$name . "\""); 
	   header("Expires: 0"); 
	   header("Cache-Control: no-cache, must-revalidate");
	   header("Pragma: no-cache");
   } else {
     header ('Content-Type: text/html; charset=UTF-8');
   }
   $detail=false;
   if (array_key_exists('detail', $_REQUEST)) {
   	$detail=true;
   }
  if ($outMode!='pdf' and $outMode!='csv' and $outMode!='mpp' and $outMode!='word' and $outMode!='excel' and $outMode!='txt' and !$download and !$noHeader) {?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" 
  "http://www.w3.org/TR/html4/strict.dtd">
<?php }
   if ($outMode!='csv' and $outMode!='mpp' and $outMode!='word' and $outMode!='excel' and $outMode!='txt' and (!$download or $outMode=='pdf') and !$noHeader) {?>
<html>
<head>   
  <title><?php echo getPrintTitle();?></title>
  <link rel="stylesheet" type="text/css" href="css/jsgantt.css" />
  <link rel="stylesheet" type="text/css" href="css/projeqtorIcons.css" />
  <link rel="stylesheet" type="text/css" href="css/projeqtorPrint.css" />
  <link rel="stylesheet" type="text/css" href="css/projeqtorFlat.css" />
  <link rel="shortcut icon" href="img/logo.ico" type="image/x-icon" />
  <link rel="icon" href="img/logo.ico" type="image/x-icon" />
<?php if (! isset($debugIEcompatibility) or $debugIEcompatibility==false) {?>  
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
<?php }?> 
  <script type="text/javascript" src="../external/dojo/dojo.js"
    djConfig='modulePaths: {"i18n":"../../tool/i18n",
                            "i18nCustom":"../../plugin"},
              parseOnLoad: true,
              isDebug: <?php echo getBooleanValueAsString(Parameter::getGlobalParameter('paramDebugMode'));?>'></script>
  <script type="text/javascript" src="../external/dojo/projeqtorDojo.js"></script>
  <script type="text/javascript" src="../view/js/jsgantt.js"></script>
  <script type="text/javascript"> 
    var customMessageExists=<?php echo(file_exists(Plugin::getDir()."/nls/$currentLocale/lang.js"))?'true':'false';?>;
    dojo.require("dojo.dnd.Container");
    dojo.require("dijit.layout.BorderContainer");
    dojo.require("dijit.layout.ContentPane");
    dojo.require("dojo.parser");
    dojo.require("dojo.i18n");
    //dojo.require("dojo.date.locale");
    dojo.addOnLoad(function(){
      <?php 
        if (array_key_exists('directPrint', $_REQUEST)) {
          echo "window.print();";
          //echo "window.close();";
        }
      ?>
      var printInNewWindow=<?php echo (getPrintInNewWindow())?'true':'false';?>;
      if (printInNewWindow) {
        var objTop=window.opener;
      } else {
    	  var objTop=top;
    	  objTop.window.document.title="<?php echo getPrintTitle();?>";
      }
      objTop.hideWait();
      //objTop.window.document.title="<?php echo getPrintTitle();?>";
      //window.document.title=applicationName;
      <?php if ($_REQUEST['page']=='planningPrint.php') {?>
        
        dojo.byId("leftGanttChartDIV_print").innerHTML=objTop.dojo.byId("leftGanttChartDIV").innerHTML;
        dojo.byId("leftGanttChartDIV_print").style.width=objTop.dojo.byId("leftGanttChartDIV").style.width;
        dojo.byId("leftside").style.top=0;
        dojo.byId("ganttScale").innerHTML="";
        dojo.byId("GanttChartDIV_print").style.left=dojo.byId("leftGanttChartDIV_print").offsetWidth+"px";
        dojo.byId("rightGanttChartDIV_print").innerHTML=objTop.dojo.byId("rightGanttChartDIV").innerHTML;
        dojo.byId("topGanttChartDIV_print").innerHTML=objTop.dojo.byId("topGanttChartDIV").innerHTML;
        dojo.byId("rightside").style.left='-1px';
        var height=dojo.byId("leftside").offsetHeight;
        height+=43;
        dojo.byId("GanttChartDIV_print").style.height=height+'px';
        //var g = new JSGantt.GanttChart('g',dojo.byId('ganttDiv'), window.top.g.getFormat()); 
      <?php }?>
    }); 
    
  </script>
</head>
<page backtop="100px" backbottom="20px" footer="page">
<<?php echo ($printInNewPage or $outMode=='pdf') ?'body':'div';?> style="-webkit-print-color-adjust: exact;<?php echo ($outMode=='pdf')?'font-size:90%;':''; ?>" id="bodyPrint" class="tundra ProjeQtOrFlatGrey" onload="window.top.hideWait();">
  <?php 
  }
  $page=$_REQUEST['page'];
  securityCheckPage($page);
  $includeFile=$page;
  if (! substr($page,0,3)=='../') {
    $includeFile.='../view/';
  }
  securityCheckPage($includeFile);
  $pos = strpos($includeFile, '?');
  if ($pos !== FALSE) {
    $params=substr($includeFile, $pos + 1);
    $includeFile=substr($includeFile, 0, $pos);
    $paramArray=explode('&',$params);
    foreach ($paramArray as $param)
    {
      $par=explode('=',$param);
      $_REQUEST[$par[0]]=$par[1];
    }
  }
  include $includeFile;
  if ($outMode!='csv' and $outMode!='mpp' and $outMode!='word' and $outMode!='excel' and $outMode!='txt' and (!$download or $outMode=='pdf') and !$noHeader) {?>
</<?php echo ($printInNewPage or $outMode=='pdf')?'body':'div';?>>
</page>
</html>
<?php
  } 
  finalizePrint();
?>
<?php function finalizePrint() {
  global $outMode, $download, $includeFile, $orientation;
  $pdfLib='html2pdf';
  //$pdfLib='dompdf';
  $outputFileName=null;
  if (isset($_REQUEST['objectClass']) and isset($_REQUEST['objectId']) and isset($_REQUEST['page']) 
  and ($_REQUEST['page']=='objectDetail.php' or $_REQUEST['page']=='../report/object/'.$_REQUEST['objectClass'].'.php' )) {
    $objectClass=$_REQUEST['objectClass'];
    $objectId=$_REQUEST['objectId'];
    $obj=new $objectClass($objectId);
    $outputFileName=i18n($objectClass).' ';
    if (property_exists($obj, 'reference') and $obj->reference) {
      $outputFileName.=$obj->reference;
    } else {
      $outputFileName.=$obj->id;
      if (property_exists($obj, 'name')) {
        //$outputFileName.=' ('.$obj->name.')';
      }
    }
    $outputFileName.=".pdf";
    $outputFileName=Security::checkValidFileName($outputFileName,false);
  } else if (isset($_REQUEST['page']) and $_REQUEST['page']=='../tool/jsonQuery.php' and isset($_REQUEST['objectClass']) ) {
    $objectClass=$_REQUEST['objectClass'];
    $outputFileName=i18n('menu'.$objectClass).'_'.date('Ymd_His');
    $outputFileName.=".pdf";
    $outputFileName=Security::checkValidFileName($outputFileName,false);
  } else if (isset($_REQUEST['reportName'])) {
    $outputFileName=$_REQUEST['reportName'].'_'.date('Ymd_His');
    $outputFileName.=".pdf";
  }
  if (isset($pdfNamePrefix)) $outputFileName=$pdfNamePrefix.$outputFileName;
  if ($outMode=='pdf') {
    $content = ob_get_clean();   
    if ($pdfLib=='html2pdf') {
      /* HTML2PDF way */
      require_once '../external/html2pdf/vendor/autoload.php';
      
      $html2pdf = new Html2Pdf($orientation,'A4','en');
      //$html2pdf = new HTML2PDF($orientation,'A4','en');
      
      //$html2pdf->setModeDebug();
      $html2pdf->pdf->SetDisplayMode('fullpage');
      $html2pdf->pdf->SetMargins(10,10,10,10);
      $fontForPDF=Parameter::getGlobalParameter('fontForPDF');
      if (!$fontForPDF) $fontForPDF='freesans';
      $html2pdf->setDefaultFont($fontForPDF);
      $html2pdf->setTestTdInOnePage(false);
      //$html2pdf->setModeDebug(); 
      //$content=str_replace("à","&agrave;",$content);
//traceExecutionTime($includeFile,true);

     $html2pdf->writeHTML($content);
     // $html2pdf->writeHTML($html2pdf->getHtmlFromPage($content)); 
     
      if (ob_get_length()) {
        ob_end_clean();
      }
      if (!$outputFileName) $outputFileName='document.pdf';
      if ($download) {
        $html2pdf->Output($outputFileName,'D');
      } else {
        $html2pdf->Output($outputFileName);
      }
//traceExecutionTime($includeFile);
    } else if ($pdfLib=='dompdf') {
    /* DOMPDF way */
      require_once("../external/dompdf/dompdf_config.inc.php");
      $dompdf = new DOMPDF();
      $dompdf->load_html($content);
      $dompdf->render();
      $dompdf->stream("sample.pdf");
    }
  }
}
?>