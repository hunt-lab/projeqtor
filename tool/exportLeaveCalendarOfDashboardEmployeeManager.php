<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : Salto Consulting 2018
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

// MTY - LEAVE SYSTEM

/** ===========================================================================
 * Create and download the leaves's calendar from dashboardEmployeeManager
 * @param year = Selected Year of leaves. If missing, current year
 * @param month = Selected Month of leaves. If missing, current month 
 * @param idStatus = Selected status of leaves. If missing, all
 * @param idLeaveType = Selected idLeaveType. If missing, all
 * @param idEmployee = Selected idEmployee. If missing, all Employee allowed for the connected user
 */

require_once "../tool/projeqtor.php";
require_once "../tool/formatter.php";

// MTY - EXPORT EXCEL OR ODS
 require_once '../external/PHPOffice/PHPExcel/Classes/PHPExcel.php';
// MTY - EXPORT EXCEL OR ODS

scriptLog('   ->/tool/exportLeaveCalendarOfDashboardEmployeeManager.php');

$year = (new DateTime())->format("Y");
$month = (new DateTime())->format("m");

// THE PARAMETERS
if (isset($_REQUEST['year'])) {
    $year = $_REQUEST['year'];
}
if (isset($_REQUEST['month'])) {
    $month = $_REQUEST['month'];
}
$lastDayOfMonth=lastDayOfMonth((int)$month, $year);
$startDateRequest = $year."-".($month<10?"0":"").$month."-01";
$endDateRequest = $year."-".($month<10?"0":"").$month."-".$lastDayOfMonth;



$idStatus = 0;
if (isset($_REQUEST['idStatus'])) {
    if ($_REQUEST['idStatus']!=0) {
        $idStatus = Security::checkValidId($_REQUEST['idStatus']);
    }
}

$idLeaveType= 0;
if (isset($_REQUEST['idLeaveType'])) {
    if ($_REQUEST['idLeaveType']!=0) {
        $idLeaveType = Security::checkValidId($_REQUEST['idLeaveType']);
    }
}

$idEmployee = 0;
if (isset($_REQUEST['idEmployee'])) {
    if ($_REQUEST['idEmployee']!=0) {
        $idEmployee = Security::checkValidId($_REQUEST['idEmployee']);
    }
}

// NEEDED DATAS

  // EMPLOYEES
  $employeeList = getUserVisibleResourcesList(true, "List",'', false,true,true,true);
  asort($employeeList, SORT_NATURAL | SORT_FLAG_CASE);
  if ($idEmployee==0) {
        $employeeName = "ALL";
  } else {
        $employeeName = $employeeList[$idEmployee];
  }

  // THE LEAVE TYPES
  $leaveTypes = LeaveType::getList();
  if ($idLeaveType==0) {
      $nbTypes = 1;
  } else {
      $nbTypes = count($leaveTypes);
  }
  
  // THE LEAVE WORKFLOW STATUS
  $listStatus = Workflow::getLeaveMngListStatus();    
  $statusListFirstLetter = null;


// THE STYLE BOX
//Box around the lines for each employee
$styleBoxBorderTopBottom = array(
    'borders' => array(
        'top' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN
        ),
        'bottom' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN
        )
    ),
    'border' => array(
        'color' => array('rgb' => '000000')
    ),
    'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
    )        
);

//Box Border Black
$styleBoxBorders = array(
    'borders' => array(
        'top' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '000000')
        ),
        'bottom' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '000000')
        ),
        'right' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '000000')
        ),
        'left' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '000000')
        )
    ),
    'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
    )        
);

// Cells OffDays
$styleBgDayOff = array(
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('rgb' => '000000'),
    ),
    'font' => array(
        'color' => array('argb' => "FFFFFFFF")
    ),
    'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
    )        
);

// Background color for leave type
$leaveTypesStyle = null;
$leaveTypeName="ALL";
foreach ($leaveTypes as $leaveType) {
    $leaveTypesStyle[$leaveType->id] =array(
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => substr($leaveType->color,1))
        ),
        'font' => array(
            'color' => array('rgb' => substr(oppositeColor($leaveType->color),1))
        ),
        'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        )        
    );
    if ($leaveType->id==$idLeaveType) {
        $leaveTypeName = $leaveType->name;
    }
}

// Border color for status
$statusStyle = null;
$statusBGStyle = null;
$statusName="ALL";
foreach ($listStatus as $status) {
    $statusStyle[$status->id] = array(
        'borders' => array(
            'top' => array(
                'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                'color' => array('rgb' => substr($status->color,1))
            ),
            'bottom' => array(
                'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                'color' => array('rgb' => substr($status->color,1))
            ),
            'left' => array(
                'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                'color' => array('rgb' => substr($status->color,1))
            ),
            'right' => array(
                'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                'color' => array('rgb' => substr($status->color,1))
            )
        ),
        'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        )        
    );
    $statusBGStyle[$status->id] =array(
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => substr($status->color,1))
        ),
        'font' => array(
            'color' => array('rgb' => substr(oppositeColor($status->color),1))
        ),
        'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        )        
    );
    if ($status->id==$idStatus) {
        $statusName = $status->name;
    }
}

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setCreator(getSessionUser()->name)
                             ->setLastModifiedBy(getSessionUser()->name)
                             ->setTitle(i18n("leavesCalendar")." ". i18n("of")." ".$year." - ".($month<10?"0":"").$month);

// File name
$fileName = $year."-".($month<10?"0":"").$month."-".i18n("leavesCalendar");
$fileName .= ($idEmployee==0?"":"-".i18n("Employee")." ".$employeeName);
$fileName .= ($idStatus==0?"":"-".i18n("colIdStatus")." ".$statusName);
$fileName .= ($idLeaveType==0?"":"-".i18n("colType")." ".$leaveTypeName);


// ==========================
// CALENDAR
// ==========================
// Set active sheet index to the first sheet
$objPHPExcel->setActiveSheetIndex(0);
$sheet = $objPHPExcel->getActiveSheet();

// Freeze panes
$sheet->freezePane('A12');
// Rows to repeat at top
$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 12);

$sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$sheet->getPageSetup()->setFitToWidth();
$sheet->setShowGridlines(false);

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

$sheet->getColumnDimension("A")->setWidth(20);

// TITLE
// Name of sheet
$sheet->setTitle(str_replace("]","",str_replace("[","",i18n('leavesCalendar'))));
// Line Title
$sheet->mergeCells('A1:BI1');
$sheet->setCellValue('A1', strtoupper(i18n('leavesCalendar')));
// Line Year - Month
$sheet->mergeCells('A2:BI2');
$sheet->setCellValue('A2', strtoupper(getMonthName($month))." - ".$year);

// Line Employee - Type - Status
$sheet->mergeCells('A3:BI3');
$sheet->setCellValue('A3', (i18n("FOR")." ".i18n("Employee")." = ".$employeeName." - ".i18n("colType")." = ".$leaveTypeName." - ".i18n("colIdStatus")." = ".$statusName));
$sheet->getStyle('A1:A3')->getFont()->setSize(20);
$sheet->getStyle('A1:A3')->getFont()->setBold(true);

// LEGEND
$sheet->getStyle('A4:A8')->getFont()->setSize(14);
$sheet->getStyle('A4:A8')->getFont()->setBold(true);
// Line Request Date > Leave Date And Processing Date > Leave Date
$sheet->mergeCells('A4:Q4');
$sheet->setCellValue("A4",i18n("leaveRequestAfterLeaveDate"));
$sheet->setCellValue("R4","R");
$sheet->getStyle("R4")->applyFromArray($styleBoxBorders);

$sheet->getStyle('T4')->getFont()->setSize(14);
$sheet->getStyle('T4')->getFont()->setBold(true);
$sheet->mergeCells("T4:AO4");
$sheet->setCellValue("T4",i18n("leaveProcessingAfterLeaveDate"));
$sheet->setCellValue("AP4","P");
$sheet->getStyle("AP4")->applyFromArray($styleBoxBorders);

// Line Legend Status
$sheet->setCellValue("A6", i18n("colIdStatus"));
$i=3;
foreach( $listStatus as $status) {
    $theRange = sheetCellColumnLetter($i)."6:".sheetCellColumnLetter($i+8)."6";
    $sheet->mergeCells($theRange);
    $sheet->setCellValue(sheetCellColumnLetter($i)."6", $status->name);
    $sheet->getStyle($theRange)->applyFromArray($statusStyle[$status->id]);
    $i += 10;
} 
// Line Legend Leave Type
$sheet->setCellValue("A8", i18n("colType"));
$i=3;
foreach( $leaveTypes as $leaveType) {
    $theRange = sheetCellColumnLetter($i)."8:".sheetCellColumnLetter($i+8)."8";
    $sheet->mergeCells($theRange);
    $sheet->setCellValue(sheetCellColumnLetter($i)."8", $leaveType->name);
    $sheet->getStyle(sheetCellColumnLetter($i)."8")->applyFromArray($leaveTypesStyle[$leaveType->id]);
    $i += 10;
} 

// Calendar
// Line Header
// Employees
$sheet->getStyle('A10:A11')->getFont()->setSize(12);
$sheet->getStyle('A10:A11')->getFont()->setBold(true);
$sheet->mergeCells("A10:A11");
$sheet->setCellValue("A10", strtoupper(i18n("Employee")));
$sheet->getStyle("A10:A11")->applyFromArray($styleBoxBorderTopBottom);

// Num day and days of month
$j=2;
for($i=1;$i<=$lastDayOfMonth;$i++) {
    $sheet->getColumnDimension(sheetCellColumnLetter($j))->setWidth(2);
    $sheet->getColumnDimension(sheetCellColumnLetter($j+1))->setWidth(2);
    $sheet->mergeCells(sheetCellColumnLetter($j)."10:".sheetCellColumnLetter($j+1)."10");
    $sheet->mergeCells(sheetCellColumnLetter($j)."11:".sheetCellColumnLetter($j+1)."11");
    $date = $year.'-'.$month.'-'.($i<10?"0":"").$i;
    $isOffDay=isOffDay($date);
    $dayOfWeek = date('l', strtotime($date));
    $dayOfWeekI18n = i18n($dayOfWeek);
    if ($i>$lastDayOfMonth or $isOffDay) {
        $sheet->getStyle(sheetCellColumnLetter($j)."10:".sheetCellColumnLetter($j+1)."11")->applyFromArray($styleBgDayOff);
    } else {
        $sheet->getStyle(sheetCellColumnLetter($j)."10:".sheetCellColumnLetter($j+1)."11")->applyFromArray($styleBoxBorders);        
    }
    $sheet->setCellValue(sheetCellColumnLetter($j)."10",$dayOfWeekI18n[0]);
    $sheet->setCellValue(sheetCellColumnLetter($j)."11",$i);    
    $j += 2;
}
// Sum
$sheet->mergeCells(sheetCellColumnLetter($j)."10:".sheetCellColumnLetter($j+count($leaveTypes)-1)."10");
$sheet->setCellValue(sheetCellColumnLetter($j)."10", strtoupper(i18n("sum")));
$sheet->getStyle(sheetCellColumnLetter($j)."10:".sheetCellColumnLetter($j+count($leaveTypes)-1)."10")->applyFromArray($styleBoxBorders);
// Sum by leave type
foreach($leaveTypes as $leaveType) {
    $sheet->getColumnDimension(sheetCellColumnLetter($j))->setWidth(14);
    $sheet->setCellValue(sheetCellColumnLetter($j)."11", $leaveType->name);
    $sheet->getStyle(sheetCellColumnLetter($j)."11")->applyFromArray($leaveTypesStyle[$leaveType->id]);
    $j++;
}

$employees= null;
// EMPLOYEE LINES
$line=12;

foreach($employeeList as $key => $name) {
    // Column Employee Name
    $sheet->setCellValue("A".$line,$name);
    $sheet->getStyle("A".$line)->applyFromArray($styleBoxBorderTopBottom);
    $employee = new Employee($key);
    $employees[$employee->id] = $employee;
    
    // Init Sum
    $sumLeaves=null;
    foreach($leaveTypes as $leaveType) {
        $sumLeaves[$leaveType->id]=0;
    }
    // Retrieve leaves of month for the employee
    $leavesDay=getLeavesInArrayDateForAPeriodAndAnEmployee($employee,$startDateRequest,$endDateRequest,$idStatus,$idLeaveType);
    
    // Days of calendar
    $j=2;
    for($i=1;$i<=$lastDayOfMonth;$i++) {
        $date = $year."-".($month<10?"0":"").$month."-".($i<10?"0":"").$i;
        $isOffDay=isOffDay($date, $employee->idCalendarDefinition);
        $idLeave=0;
        $theIdStatus=0;
        $theIdLeaveType=0;
        $requestDateTime=null;
        $processingDateTime=null;
        if ($leavesDay!=null) { 
            if (array_key_exists($date, $leavesDay)) {
                $theIdStatus = ($isOffDay?0:$leavesDay[$date]['idStatus']);
                $theIdLeaveType= $leavesDay[$date]['idType'];
                $idLeave = ($isOffDay?0:$leavesDay[$date]['idLeave']);
                $leaveStartDate = ($isOffDay?0:$leavesDay[$date]['startDate']);
                $leaveEndDate = ($isOffDay?0:$leavesDay[$date]['endDate']);
                $requestDateTime = ($isOffDay?null:$leavesDay[$date]['requestDateTime']);
                $processingDateTime = ($isOffDay?null:$leavesDay[$date]['processingDateTime']);
                $leaveTypeStyle = $leaveTypesStyle[$leavesDay[$date]['idType']];
                $borderStatus = $statusStyle[$leavesDay[$date]['idStatus']];
                if ($isOffDay) {
                    // A day off
                    $sheet->mergeCells(sheetCellColumnLetter($j).$line.":".sheetCellColumnLetter($j+1).$line);
                    $sheet->getStyle(sheetCellColumnLetter($j).$line.":".sheetCellColumnLetter($j+1).$line)->applyFromArray($styleBgDayOff);
                } else {
                    if ($leavesDay[$date]['AM'] and $leavesDay[$date]['PM']) {
                        // A Full day leave
                        $sheet->mergeCells(sheetCellColumnLetter($j).$line.":".sheetCellColumnLetter($j+1).$line);
                        $sheet->getStyle(sheetCellColumnLetter($j).$line.":".sheetCellColumnLetter($j+1).$line)->applyFromArray($leaveTypeStyle);
                        $sheet->getStyle(sheetCellColumnLetter($j).$line.":".sheetCellColumnLetter($j+1).$line)->applyFromArray($borderStatus);
                        if ($theIdStatus!=9) {
                            $sumLeaves[$theIdLeaveType] += 1;
                        }
                    } else {
                        // An Half day leave
                        if ($theIdStatus!=9) {
                            $sumLeaves[$theIdLeaveType] += 0.5;
                        }
                        if ($leavesDay[$date]['AM'] and $leavesDay[$date]['AM']) {
                            // A morning day leave
                            $sheet->getStyle(sheetCellColumnLetter($j).$line)->applyFromArray($leaveTypeStyle);
                            $sheet->getStyle(sheetCellColumnLetter($j).$line)->applyFromArray($borderStatus);
                            $sheet->getStyle(sheetCellColumnLetter($j+1).$line)->applyFromArray($styleBoxBorders);
                        } else {
                            // A afternoon day leave
                            $sheet->getStyle(sheetCellColumnLetter($j).$line)->applyFromArray($styleBoxBorders);
                            $sheet->getStyle(sheetCellColumnLetter($j+1).$line)->applyFromArray($leaveTypeStyle);
                            $sheet->getStyle(sheetCellColumnLetter($j+1).$line)->applyFromArray($borderStatus);                                                        
                        }
                    }
                    $theRequestDate = ($requestDateTime==""?"":substr($requestDateTime, 0,10));
                    if ($theRequestDate>$date) {
                        $sheet->setCellValue(sheetCellColumnLetter($j).$line,"R");
                    } else {
                        $theProcessingDate = ($processingDateTime==""?"":substr($processingDateTime, 0,10));
                        if ($theProcessingDate>$date) {
                            $sheet->setCellValue(sheetCellColumnLetter($j).$line,"P");
                        }
                    }
                }
            } else {
                if ($isOffDay) {
                    // A day off
                    $sheet->mergeCells(sheetCellColumnLetter($j).$line.":".sheetCellColumnLetter($j+1).$line);
                    $sheet->getStyle(sheetCellColumnLetter($j).$line.":".sheetCellColumnLetter($j+1).$line)->applyFromArray($styleBgDayOff);
                } else {
                    $sheet->mergeCells(sheetCellColumnLetter($j).$line.":".sheetCellColumnLetter($j+1).$line);
                    $sheet->getStyle(sheetCellColumnLetter($j).$line.":".sheetCellColumnLetter($j+1).$line)->applyFromArray($styleBoxBorders);
                }                
            }
        } else {
            if ($isOffDay) {
                // A day off
                $sheet->mergeCells(sheetCellColumnLetter($j).$line.":".sheetCellColumnLetter($j+1).$line);
                $sheet->getStyle(sheetCellColumnLetter($j).$line.":".sheetCellColumnLetter($j+1).$line)->applyFromArray($styleBgDayOff);
            } else {
                $sheet->mergeCells(sheetCellColumnLetter($j).$line.":".sheetCellColumnLetter($j+1).$line);
                $sheet->getStyle(sheetCellColumnLetter($j).$line.":".sheetCellColumnLetter($j+1).$line)->applyFromArray($styleBoxBorders);
            }
        }
    $j += 2;
    }    
    
    // Sums
    foreach($leaveTypes as $leaveType) {        
        $sheet->getStyle(sheetCellColumnLetter($j).$line)->applyFromArray($styleBoxBorders);
        $sheet->setCellValue(sheetCellColumnLetter($j).$line,$sumLeaves[$leaveType->id]);
        $j++;
    }
    $line++;
}

// ==========================
// LEAVES LIST OF THE PERIOD
// ==========================
$sheet2 = $objPHPExcel->createSheet();
// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(1);
// Freeze panes
$sheet2->freezePane('A6');
// Rows to repeat at top
$sheet2->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 5);

$sheet2->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
$sheet2->getPageSetup()->setFitToWidth();
$sheet2->setShowGridlines(false);

// Name of sheet
$sheet2->setTitle(str_replace("]","",str_replace("[","",i18n('leavesList'))));
// Line Title
$sheet2->mergeCells('A1:H1');
$sheet2->setCellValue('A1', strtoupper(i18n('leavesList')));
// Line Year - Month
$sheet2->mergeCells('A2:H2');
$sheet2->setCellValue('A2', strtoupper(getMonthName($month))." - ".$year);
// Line Employee - Type - Status
$sheet2->mergeCells('A3:H3');
$sheet2->setCellValue('A3', strtoupper(i18n("FOR")." ".i18n("Employee")." = ".$employeeName." - ".i18n("colType")." = ".$leaveTypeName." - ".i18n("colIdStatus")." = ".$statusName));
$sheet2->getStyle('A1:A3')->getFont()->setSize(20);
$sheet2->getStyle('A1:A3')->getFont()->setBold(true);

// HEADER
// Employees
$sheet2->getStyle('A5')->getFont()->setSize(12);
$sheet2->getStyle('A5')->getFont()->setBold(true);
$sheet2->setCellValue("A5", strtoupper(i18n("Employee")));
$sheet2->getStyle("A5")->applyFromArray($styleBoxBorders);
// Type
$sheet2->getStyle('B5')->getFont()->setSize(12);
$sheet2->getStyle('B5')->getFont()->setBold(true);
$sheet2->setCellValue("B5", strtoupper(i18n("colType")));
$sheet2->getStyle("B5")->applyFromArray($styleBoxBorders);
// Status
$sheet2->getStyle('C5')->getFont()->setSize(12);
$sheet2->getStyle('C5')->getFont()->setBold(true);
$sheet2->setCellValue("C5", strtoupper(i18n("colIdStatus")));
$sheet2->getStyle("C5")->applyFromArray($styleBoxBorders);
// StartDate & AMPM
$sheet2->getStyle('D5')->getFont()->setSize(12);
$sheet2->getStyle('D5')->getFont()->setBold(true);
$sheet2->setCellValue("D5", strtoupper(i18n("startDate")));
$sheet2->getStyle("D5")->applyFromArray($styleBoxBorders);
// EndDate & AMPM
$sheet2->getStyle('E5')->getFont()->setSize(12);
$sheet2->getStyle('E5')->getFont()->setBold(true);
$sheet2->setCellValue("E5", strtoupper(i18n("endDate")));
$sheet2->getStyle("E5")->applyFromArray($styleBoxBorders);
// Nb Days
$sheet2->getStyle('F5')->getFont()->setSize(12);
$sheet2->getStyle('F5')->getFont()->setBold(true);
$sheet2->setCellValue("F5", strtoupper(i18n("nbDays")));
$sheet2->getStyle("F5")->applyFromArray($styleBoxBorders);
// Requested Date
$sheet2->getStyle('G5')->getFont()->setSize(12);
$sheet2->getStyle('G5')->getFont()->setBold(true);
$sheet2->setCellValue("G5", strtoupper(i18n("requestedDate")));
$sheet2->getStyle("G5")->applyFromArray($styleBoxBorders);
// Processing Date
$sheet2->getStyle('H5')->getFont()->setSize(12);
$sheet2->getStyle('H5')->getFont()->setBold(true);
$sheet2->setCellValue("H5", strtoupper(i18n("processingDate")));
$sheet2->getStyle("H5")->applyFromArray($styleBoxBorders);

// Employees
$line = 6;
foreach ($employees as $employee) {
    $lvList=getUserLeaves($startDateRequest,$endDateRequest, $employee->id);
    if (!$lvList) {continue;}
    foreach ($lvList as $leave) {
        // employee Name
        $sheet2->setCellValue("A".$line,$employee->name);
        $sheet2->getStyle("A".$line)->applyFromArray($styleBoxBorders);    
        // Type
        foreach ($leaveTypes as $leaveType) {
            if ($leaveType->id == $leave->idLeaveType) {
                $leaveTypeName = $leaveType->name;
                break;
            }
        }
        $sheet2->setCellValue("B".$line,$leaveTypeName);
        $sheet2->getStyle("B".$line)->applyFromArray($leaveTypesStyle[$leave->idLeaveType]);
        // Status
        foreach ($listStatus as $status) {
            if ($status->id == $leave->idStatus) {
                $statusName = $status->name;
                break;
            }
        }
        $sheet2->setCellValue("C".$line,$statusName);
        $sheet2->getStyle("C".$line)->applyFromArray($statusBGStyle[$leave->idStatus]);
        
        // StartDate & AMPM
        $sheet2->setCellValue("D".$line,$leave->startDate." - ".($leave->startAMPM=="AM"?i18n("morning"):i18n("afternoon")));
        $sheet2->getStyle("D".$line)->applyFromArray($styleBoxBorders);    
        
        // EndtDate & AMPM
        $sheet2->setCellValue("E".$line,$leave->endDate." - ".($leave->endAMPM=="AM"?i18n("morning"):i18n("afternoon")));
        $sheet2->getStyle("E".$line)->applyFromArray($styleBoxBorders);
        
        // NbDays
        $sheet2->setCellValue("F".$line,$leave->nbDays);
        $sheet2->getStyle("F".$line)->applyFromArray($styleBoxBorders);
        
        // Requested Date
        $sheet2->setCellValue("G".$line,$leave->requestDateTime);
        $sheet2->getStyle("G".$line)->applyFromArray($styleBoxBorders);
        
        // Requested Date
        $sheet2->setCellValue("H".$line,$leave->processingDateTime);
        $sheet2->getStyle("H".$line)->applyFromArray($styleBoxBorders);

        $line++;
    }    
}

$sheet2->getColumnDimension('A')->setAutoSize(TRUE);
$sheet2->getColumnDimension('B')->setAutoSize(TRUE);
$sheet2->getColumnDimension('C')->setAutoSize(TRUE);
$sheet2->getColumnDimension('D')->setAutoSize(TRUE);
$sheet2->getColumnDimension('E')->setAutoSize(TRUE);
$sheet2->getColumnDimension('F')->setAutoSize(TRUE);
$sheet2->getColumnDimension('G')->setAutoSize(TRUE);
$sheet2->getColumnDimension('H')->setAutoSize(TRUE);

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Export the Excel
exportSpreadsheet($objPHPExcel, $fileName, true);

exit;
// MTY - EXPORT EXCEL OR ODS
/**
 * Finalize the export to a spreadsheet. Called from an export view.
 * @param $context reference to PHPExcel object
 * @param string $fileName filename of the spreadsheet (xlsx, ods)
 * 
 */

function exportSpreadsheet($context, $fileName, $preCalculateFormulas=false) {
    if (PHP_SAPI == 'cli') die('This example should only be run from a Web Browser');
    if (Parameter::getGlobalParameter("paramDefaultTimezone")) date_default_timezone_set(Parameter::getGlobalParameter("paramDefaultTimezone"));
    else date_default_timezone_set(Parameter::getGlobalParameter("Europe/Paris"));

    $typeOfExport = Parameter::getUserParameter("typeExportXLSorODS");

    $objWriter = NULL;
    if ($typeOfExport == 'Excel') $typeOfExport="Excel2007";
    $format = ($typeOfExport=="Excel2007"?"xlsx":"ods");
    $fileName .= ".".$format;
    $contentType = ($typeOfExport=="Excel2007"?"Content-Type: application/vnd.ms-excel":"Content-Type: application/vnd.oasis.opendocument.spreadsheet");
    
    // Redirect output to a client’s web browser (Excel2007)
    
    header($contentType);
//    header('Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
    header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header('Pragma: public'); // HTTP/1.0
    $objWriter = PHPExcel_IOFactory::createWriter($context, $typeOfExport);
    
    if ($preCalculateFormulas) {
        $objWriter->setPreCalculateFormulas(true);
    }
    $objWriter->save('php://output');
}

/**
 * Convert a sheet cell number to a column sheeet Letters
 * @param integer $c : The column sheet number
 * @return string : The column letters of the column sheet cell number
 */
function sheetCellColumnLetter($c){
    $c = intval($c);
    if ($c <= 0) {return '';}

    $letter = '';
             
    while($c != 0){
       $p = ($c - 1) % 26;
       $c = intval(($c - $p) / 26);
       $letter = chr(65 + $p) . $letter;
    }
    
    return $letter;        
}
// MTY - EXPORT EXCEL OR ODS

?>