<?php
    include_once("../module/global.inc.php");
    check_login();
    include_once("../module/global2_inc.php");
    
    set_time_limit(0);
    $debug=0;
    if ($opnm=='charles'){
        $debug=1;
    }
    $bresult=pg_exec($db,"begin transaction");
    $cresult=pg_exec($db,"set datestyle to 'POSTGRES,EUROPEAN'");

function createTrcd(){
    global $opnm;
    
    if(strlen($opnm)>20) $trcd= strtolower(substr($opnm,1,20));
    else $trcd= strtolower($opnm);
    
    $trcd.= "_".date("ymdHis");
    return $trcd;        
}

    if ($cmd=="upme") {

        if ($nx==3 && $xtype==1) {
            $xresult1=pg_exec($db,"select code,flag from msmemb where code='$memcode';");
            if (pg_numrows($xresult1)>0) {
                list($xcode,$xflag)=pg_fetch_row($xresult1,0);
                /*
                0 : reinstate
                1 : suspend/inactive
                2 : terminate
                3 : resign
                4 : expired
                5 : permanent expired
                6 : transferred
                7 : rejoin
                8 : deceased
                */
                $adjstat = "[unkown]";
                $xxdate = "$date1-$month1-$year1";
                $uFlag = $actx;
                if ($actx==4 || $actx==5) { 
                    $uFlag = $actx;
                    $adjstat = "Expired";
                    if ($ex_name==1) {
                        $xresultEXP_a=pg_exec($db,"select trim(last_name) from msmemb_extra where code='$xcode';");
                        $xresultEXP_b=pg_exec($db,"select trim(mid_name) from msmemb_extra where code='$xcode';");
                        $xresultEXP_c=pg_exec($db,"select trim(name) from msmemb where code='$xcode';");
                        if(pg_numrows($xresultEXP_a)>0) list($lastname)=pg_fetch_row($xresultEXP_a,0);
                        else if(pg_numrows($xresultEXP_b)>0) list($midname)=pg_fetch_row($xresultEXP_b,0);
                        else if(pg_numrows($xresultEXP_c)>0) list($firstname)=pg_fetch_row($xresultEXP_c,0);
                        if($lastname!='') $lname = $lastname;
                        else if($midname!='') $lname = $lastname;
                        else $lname = $firstname;

                        $totSTR = strlen(trim($lname));
                        $expSTR = explode("[EXP",trim($lname));
                        // loop the count
                        if(count($expSTR)>1) {
                            $lname = $expSTR[0];
                        }
                        /*for ($i = 0; $i < count($expSTR); $i++) {
                                $cutEXP = substr(trim($lname),-9);
                                if ($debug) print "hasil CUT $i : $cutEXP<br>";
                                if ($cutEXP=="[EXPIRED]") $remEXP = substr_replace(trim($lname),'',-9);
                                if ($debug) print "hasil DEL $i : $remEXP<br>";
                                $lname .= $remEXP;
                        }*/
                        // end loop
                        if($ex_date!='') {
                            $ex_date = str_replace("'","",$ex_date);
                            $exp_date = explode("/",$ex_date);
                            $expword = "$exp_date[0]/$exp_date[1]/".substr($exp_date[2],2,2);
                        } else $expword = "$date1/$month1/".substr($year1,2,2);
                        $lname = "$lname [EXP$expword]";
                        //$lname = $lname." [EXPIRED]";
                        $xresultEXP=pg_exec($db,"update msmemb_extra set last_name='$lname' where code='$memcode';");
                    }
                
                } else {
                    $xresultEXP_a=pg_exec($db,"select trim(last_name) from msmemb_extra where code='$xcode';");
                    $xresultEXP_b=pg_exec($db,"select trim(mid_name) from msmemb_extra where code='$xcode';");
                    $xresultEXP_c=pg_exec($db,"select trim(name) from msmemb where code='$xcode';");
                    if(pg_numrows($xresultEXP_a)>0) list($lname)=pg_fetch_row($xresultEXP_a,0);
                    else if(pg_numrows($xresultEXP_b)>0) list($lname)=pg_fetch_row($xresultEXP_b,0);
                    else if(pg_numrows($xresultEXP_c)>0) list($lname)=pg_fetch_row($xresultEXP_c,0);

                    $totSTR = strlen(trim($lname));
                    $expSTR = explode("[EXP",trim($lname));
                    // loop the count
                    if(count($expSTR)>1) {
                        $lname = $expSTR[0];
                    }
                    /*	for ($i = 0; $i < count($expSTR); $i++) {
                                    $cutEXP = substr(trim($lname),-9);
                                    if ($debug) print "hasil CUT $i : $cutEXP<br>";
                                    if ($cutEXP=='[EXPIRED]') $remEXP = substr_replace(trim($lname),'',-9);
                                    if ($debug) print "hasil DEL $i : $remEXP<br>";
                                    if (trim($remEXP)<>'') $lname = $remEXP;
                        }*/
                    // end loop	
                    //if (trim($lname)<>"") {
                    $msql = "update msmemb_extra set last_name='$lname' where code='$memcode';";
                    $xresultEXP=pg_exec($db,$msql);
                    //}
                }

                $adjstat = flag_desc($actx);	
                $txtnote = "Membership $adjstat on $xxdate";			
                //$msql = "update msmemb set tgg1='$txtnote',flagdt='$txtUdate',flag='$actx' where code='$memcode';";
                $msql = "update msmemb set tgg1='$txtnote',flag='$actx' where code='$memcode';";
                $xresult1=pg_exec($db,$msql);
                $msg = "Membership ($memcode) flag updated";
                // update member adjustment report
                if (($actx==4 || $actx==5) && $ex_date<>'')  {
                    $txtUdate = $ex_date;
                    $msql = "update msmemb set expired_date='$txtUdate' where code='$memcode';";
                    $xresultADJ=pg_exec($db,$msql);
                }
                $xresultmax=pg_exec($db,"select max(id) from msmemb_adj;");
                if (pg_numrows($xresultmax)>0) list($idmax)=pg_fetch_row($xresultmax,0);
                else $idmax=0;
                $idno = $idmax + 1;
                $msql = "insert into msmemb_adj(code,opnm,adjdate,adj_text,status) values('$memcode','$opnm',now(),'$txtnote','3');";
                $xresultADJ=pg_exec($db,$msql);

                $xresultmax=pg_exec($db,"select max(id) from msmemb_adj;");
                list($idmax)=pg_fetch_row($xresultmax,0);
                $msql = "insert into log_adj(id,key,old_value,new_value,code) values('$idmax','flag','$xflag','$actx','$memcode');";
                $xresultADJ=pg_exec($db,$msql);
            
                // end update
                $nx=0;
                $xtype='';
            }
        }
        if ($opnm=='charles'){
            var_dump($sess);
        }
        if ($sess=="a") {
            //$uploaddir = '/tmp/';
            $uploaddir= '../download_file/';
            $period = ID_CNT.$year1.$month1.$date1;
            $filename = strtoupper($period.basename($_FILES['userfile']['name']));
            $uploadfile = $uploaddir.$filename;				
            $chkext = explode('.',$filename);
            $flext = "DBF";
            //if ($cbo_report=="3") $flext = "TXT";
            if ($chkext[1]<>$flext) {
                $msg = "File is not DBF file";
                if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
                    if (file_exists($uploadfile)) {
                            $msg.= ", Deleted";
                            unlink($uploadfile);
                    }
                }
                    //unlink($uploadfile);
            } else {
                if (file_exists($uploadfile)) {
                    //print "File exist, deleting old file. 1";
                    unlink($uploadfile);
                }
                if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {		
                    $msg = "Moved";

                    $dbfx = dbase_open($uploadfile, 0);			
                    if ($dbfx) {
                        // read some data ..
                        $record_numbers = dbase_numrecords($dbfx);
                        $nf_num = dbase_numfields($dbfx);
                        $rec = dbase_get_record($dbfx,$recno);
                        //$column_info = dbase_get_header_info($dbfx); // PHP 5 only
                        $msg = "Found $record_numbers record(s)";
                        $zz = 0;
                        $ww = 0;
                    // 
                        $updateres = "<b><u>Data successfully uploaded</u></b><br>";
                        if($record_numbers>0) {
                            $tabh = "tmp_mflag";

                            for ($i = 1; $i <= $record_numbers; $i++) {
                                $row = dbase_get_record_with_names($dbfx, $i);
                                
                                $result=pg_exec($db,"select code,flag from msmemb where code='".trim($row['CODE'])."' and flag not in ('2');");
                                if ($opnm=='charles'){
                                    var_dump("select code from msmemb where code='".trim($row['CODE'])."' and flag not in ('2');".pg_numrows($result));
                                    echo('<br>');
                                }
                                if (pg_numrows($result)<1) {
                                    if ($ww==0) {
                                        $updateres .= "<font color='red'>Member(s) code not exist in msmemb : </font>";
                                        $updateres .= trim($row['CODE']);
                                    } else {
                                        $updateres .= ", ".trim($row['CODE'])."";
                                    }
                                    $ww++;
                                } else {
                                    $flag_de=pg_fetch_row($result,0);
                                    if ($debug) print "<br>FLAG:$flag_de[1]";
                                    /*
                                    if ($actx==0) {	$uFlag = 0;	$adjstat = "Normal"; }
                                    if ($actx==1) { $uFlag = 1; $adjstat = "Suspended/inactive"; }
                                    if ($actx==2) { $uFlag = 2; $adjstat = "Terminated"; }
                                    if ($actx==3) { $uFlag = 3; $adjstat = "Resigned"; }
                                    if ($actx==4) { $uFlag = 4; $adjstat = "Expired"; }
                                    if ($actx==5) { $uFlag = 5; $adjstat = "Rermanent expired"; }
                                    if ($actx==6) { $uFlag = 6; $adjstat = "Transferred"; }
                                    if ($actx==7) { $uFlag = 7; $adjstat = "Rejoin"; }
                                    if ($actx==8) { $uFlag = 8; $adjstat = "Deceased"; } 
                                    */

                                    if ($actx=="4") $fldname = $row['EXP_DATE'];
                                    if ($actx=="7") $fldname = $row['ACT_DATE'];
                                    if ($actx=="1") $fldname = $row['SUS_DATE'];
                                    if ($actx=="3") $fldname = $row['RES_DATE'];
                                    if ($actx=="2") $fldname = $row['TER_DATE'];
                                    if ($debug) print "this : $fldname<br>";
                                    $d1 = substr($fldname,2,2);
                                    $d11 = substr($fldname,0,4);
                                    $d2 = substr($fldname,4,2);
                                    $d3 = substr($fldname,6,2);
                                    $bdate = $d2.$d1;
                                    $xdate = "'$d11-$d2-$d3'";

                                    if (trim($fldname)=="") { 
                                            $bdate = "0000";
                                            $xdate = "null";
                                    }

                                    $xmname = trim($row['NAME']);
                                    $session_name= createTrcd();
                                    /*
                                    $result=pg_exec($db,"select code from $tabh where code='".trim($row['CODE'])."' and act_type='$actx';");
                                    if (pg_numrows($result)<1) {
                                            $commdeltest = "insert into $tabh values ('$actx','".trim($row['CODE'])."','".addslashes($xmname)."',$xdate);";
                                            $resultSTATD=pg_exec($db,$commdeltest);
                                    }
                                     */
                                    if ($opnm=='charles' && $row['CODE']=='141110431') {
                                        var_dump("insert into $tabh values ('$actx','".trim($row['CODE'])."','".addslashes($xmname)."',$xdate,'$session_name')");
                                        echo('<br>');
                                    }
                                    $commdeltest = "insert into $tabh values ('$actx','".trim($row['CODE'])."','".addslashes($xmname)."',$xdate,'$session_name');";
                                    $resultSTATD= pg_exec($db,$commdeltest);
                                    
                                    
                                    $zz++;
                                }
                            }
                            $count_0 = pg_query($db,"select count(*) as co from $tabh where session_id='$session_name'");
                            $count_0 = pg_fetch_row($count_0,0);
                            if ($debug) print "$count_0[0]:AWAL<br>";

                            $msg = "$zz record(s) uploaded";
                            $updateres	.= "<br><b>$msg, $ww record(s) not exist</b>";
                            dbase_close($dbfx);
                                
                        } else {
                            $msg = "Open DBF file failed !";
                        }
                    }

                }
            }
        }
        if ($opnm=='charles'){
            echo('<br>');
            var_dump($cbo_report);
        }
                // NEW MEMBERS
        if ($cbo_report=="1") { 
            $zz=0;  $yy=0;
            $ww=0;
            $tabh = "tmp_mflag";

            if ($updateres!=""){
                $updateres .= "<br><br><b><u>Data successfully updated</u></b><br>";
            } else {
                $updateres = "<b><u>Data successfully updated</u></b><br>";
            }
            if ($opnm=='charles'){
                var_dump($session_name); 
            }
            //$result= pg_query($db,"select * from $tabh where act_type='$actx' and code in (select code from msmemb);");
            $count_1 = pg_query($db,"select count(*) as co from $tabh where session_id='$session_name'");
            $count_2 = pg_query($db,"select count(*) as co from $tabh where session_id='$session_name' and code in (select code from msmemb)");
            $count_1 = pg_fetch_row($count_1,0);
            $count_2 = pg_fetch_row($count_2,0);
            if ($debug) print "$count_1[0]----$count_2[0]<br>";
            $sql= "select * from $tabh where session_id='$session_name' and code in (select code from msmemb);";
            $result= pg_query($db,$sql);
            for ($l = 0; $l < pg_numrows($result); $l++) {
                $rowmemb=pg_fetch_row($result,$l);
                //$xsql2 = "select * from $tabh where act_type='$actx' and code='$rowmemb[1]' and code in (select code from msmemb);";
                $xsql2 = "select * from $tabh where session_id='$session_name' and code='$rowmemb[1]' and code in (select code from msmemb);";
                
                $result2=pg_query($db, $xsql2);
            if (pg_numrows($result2)<1) {
                if ($ww==0) {
                    $updateres .= "Member(s) code not exist in msmemb : ";
                    $updateres .= $rowmemb[1];
                } else {
                    $updateres .= ", $rowmemb[1]";
                }
                $ww++;
            } else {
                $rowmem=pg_fetch_row($result2,0);
                // flag: 0 normal, 1 suspended, 2 canceled/terminated, 3 disabled, 4 expired, job->Race

                //$zz++;
                //if ($actx=="0") {
                $zdate = explode('-',$rowmem[3]);
                $d1 = substr($zdate[0],2,2);				
                $d2 = $zdate[1];								
                $bdate = $d2.$d1;
                $xdate = "'$rowmem[3]'";
                $xxdate = "$date1-$month1-$year1";
                if (trim($rowmem[2])=="") { 
                        $bdate = "0000";
                        $xdate = "NULL";
                }
                //expired
                $uFlag = 0;
                $uFlag = $actx;
                $adjstat = "[unkown]";
                $adjstat = flag_desc($actx);
                $xresultmax=pg_exec($db,"select flag from msmemb where code='$rowmem[1]';");
                list($xflag)=pg_fetch_row($xresultmax,0);
                // print $xdate;
                        
                if($xflag!='2'){ 
                    $zsql = "select a_edit from mflag_setup where mflag='$xflag';";
                    $zres = pg_query($db,$zsql);
                    if(pg_numrows($zres)>0){
                        list($allow) = pg_fetch_row($zres,0);
                        
                        if($allow=='f') {
                            $yy++;
                            if ($debug) print("jing");
                        }
                            
                        else {

                            $zz++;
                            if ($debug) print("jing$zz-$xflag<br>");
                            if ($xdate<>'' && $xdate<>"''") $commdeltest = "update msmemb set flag='$uFlag' where code='$rowmem[1]';"; 
                            else $commdeltest = "update msmemb set flag='$uFlag' where code='$rowmem[1]';";
                            
                            error_log($_SERVER['PHP_SELF']." [".date('F j, Y, g:i a')."] ".__LINE__."\n sql=".$commdeltest." \n", 3, "/var/log/mlmtest.log");
                            pg_send_query($db,$commdeltest);
                            $rec = pg_get_result($db);
                            //error_log($_SERVER['PHP_SELF']." [".date('F j, Y, g:i a')."] \n sql=".$sql." \n", 3, "/var/log/mlmtest.log");
                            if(pg_result_error($rec)){
                                error_log($_SERVER['PHP_SELF']." [".date('F j, Y, g:i a')."] ".__LINE__."\n Error=".pg_result_error($rec)."\n sql=".$commdeltest." \n", 3, "/var/log/mlmtest.log");
                            }                                
                            //$res = pg_exec($db,$commdeltest);
                            // update member adjustment report
                            /*$xresultmax=pg_exec($db,"select max(id) from msmemb_adj;");
                            if (pg_numrows($xresultmax)>0) list($idmax)=pg_fetch_row($xresultmax,0);
                            else $idmax=0;
                            $idno = $idmax + 1;*/
                            //$xresultADJ= pg_exec($db,"insert into msmemb_adj(code,opnm,adjdate,adj_text,status) values('$rowmem[1]','$opnm',now(),'Membership $adjstat on $xxdate','3');");
                            $sql= "insert into msmemb_adj(code,opnm,adjdate,adj_text,status) 
                                values('$rowmem[1]','$opnm',now(),'Membership $adjstat on $xxdate','3');";
                            pg_send_query($db,$sql);
                            $rec = pg_get_result($db);
                            //error_log($_SERVER['PHP_SELF']." [".date('F j, Y, g:i a')."] \n sql=".$sql." \n", 3, "/var/log/mlmtest.log");
                            if(pg_result_error($rec)){
                                error_log($_SERVER['PHP_SELF']." [".date('F j, Y, g:i a')."] ".__LINE__."\n Error=".pg_result_error($rec)."\n sql=".$sql." \n", 3, "/var/log/mlmtest.log");
                            }                                

                            $xresultmax=pg_exec($db,"select max(id) from msmemb_adj;");
                            list($idmax)=pg_fetch_row($xresultmax,0);
                            $sql = "insert into log_adj(id,key,old_value,new_value,code) values('$idmax','flag','$xflag','$actx','$rowmem[1]');";
                            //$xresultADJ=pg_exec($db,$msql);
                            pg_send_query($db,$sql);
                            $rec = pg_get_result($db);
                            //error_log($_SERVER['PHP_SELF']." [".date('F j, Y, g:i a')."] \n sql=".$sql." \n", 3, "/var/log/mlmtest.log");
                            if(pg_result_error($rec)){
                                error_log($_SERVER['PHP_SELF']." [".date('F j, Y, g:i a')."] ".__LINE__."\n Error=".pg_result_error($rec)."\n sql=".$sql." \n", 3, "/var/log/mlmtest.log");
                            }                                
                                
                            // end update
                            if ($actx=="4") {
                                $xsq1="select expired_date from msmemb where code='$rowmem[1]';";
                                $xrow1=pg_exec($db,$xsq1);

                                if ($xdate=='NULL' || $xdate==''){
                                    $sql_expdate = "expired_date=null";
                                    $xdate='null';
                                }else{
                                    $sql_expdate = "expired_date=$xdate";
                                }

                                //$commdeltest = "update msmemb set $sql_expdate where code='$rowmem[1]';";
                                //$res = pg_exec($db,$commdeltest);
                                $sql= "update msmemb set $sql_expdate where code='$rowmem[1]';";
                                pg_send_query($db,$sql);
                                $rec = pg_get_result($db);
                                //error_log($_SERVER['PHP_SELF']." [".date('F j, Y, g:i a')."] \n sql=".$sql." \n", 3, "/var/log/mlmtest.log");
                                if(pg_result_error($rec)){
                                    error_log($_SERVER['PHP_SELF']." [".date('F j, Y, g:i a')."] ".__LINE__."\n Error=".pg_result_error($rec)."\n sql=".$sql." \n", 3, "/var/log/mlmtest.log");
                                }
                                
                                // update name
                                if ($ex_name==1) {
                                    $xresultEXP_a=pg_exec($db,"select trim(last_name) from msmemb_extra where code='$rowmem[1]';");
                                    $xresultEXP_b=pg_exec($db,"select trim(mid_name) from msmemb_extra where code='$rowmem[1]';");
                                    $xresultEXP_c=pg_exec($db,"select trim(name) from msmemb where code='$rowmem[1]';");
                                    if(pg_numrows($xresultEXP_a)>0) list($lastname)=pg_fetch_row($xresultEXP_a,0);
                                    else if(pg_numrows($xresultEXP_b)>0) list($midname)=pg_fetch_row($xresultEXP_b,0);
                                    else if(pg_numrows($xresultEXP_c)>0) list($firstname)=pg_fetch_row($xresultEXP_c,0);
                                    
                                    if($lastname!='') $lname = $lastname;
                                    else if($midname!='') $lname = $lastname;
                                    else $lname = $firstname;
                                    if ($debug) print "awalnya gini : $lname *<br>";
                                    $totSTR = strlen(trim($lname));
                                    $expSTR = explode("[EXP",trim($lname));

                                    // loop the count
                                    if(count($expSTR)>1) {
                                        $lname = $expSTR[0];
                                    }
                                    /*for ($ie = 0; $ie < count($expSTR); $ie++) {
                                            $cutEXP = substr(trim($lname),-9);
                                            if ($debug) print "hasil CUT $ie : $cutEXP<br>";
                                            if ($cutEXP=="[EXPIRED]") $remEXP = substr_replace(trim($lname),'',-9);						
                                            if ($debug) print "hasil DEL $ie : $remEXP<br>";
                                            $lname = $remEXP;
                                    }*/
                                    // end loop
                                    if($xdate!='') {
                                        $xdate = str_replace("'","",$xdate);
                                        $exp_date = explode("-",$xdate);
                                        $expword = "$exp_date[0]/$exp_date[1]/".substr($exp_date[2],2,2);
                                    }
                                    $lname = "$lname [EXP$expword]";
                                    //$lname = $lname." [EXPIRED]";
                                    //$xresultEXP=pg_exec($db,"update msmemb_extra set last_name='$lname' where code='$rowmem[1]';");
                                    $sql= "update msmemb_extra set last_name='$lname' where code='$rowmem[1]';";
                                    pg_send_query($db,$sql);
                                    $rec = pg_get_result($db);
                                    //error_log($_SERVER['PHP_SELF']." [".date('F j, Y, g:i a')."] \n sql=".$sql." \n", 3, "/var/log/mlmtest.log");
                                    if(pg_result_error($rec)){
                                        error_log($_SERVER['PHP_SELF']." [".date('F j, Y, g:i a')."] ".__LINE__."\n Error=".pg_result_error($rec)."\n sql=".$sql." \n", 3, "/var/log/mlmtest.log");
                                    }
                                    
                                }
                                
                            } else {									
                                $xresultEXP_a=pg_exec($db,"select trim(last_name) from msmemb_extra where code='$rowmem[1]';");
                                $xresultEXP_b=pg_exec($db,"select trim(mid_name) from msmemb_extra where code='$rowmem[1]';");
                                $xresultEXP_c=pg_exec($db,"select trim(name) from msmemb where code='$rowmem[1]';");
                                if(pg_numrows($xresultEXP_a)>0) list($lname)=pg_fetch_row($xresultEXP_a,0);
                                else if(pg_numrows($xresultEXP_b)>0) list($lname)=pg_fetch_row($xresultEXP_b,0);
                                else if(pg_numrows($xresultEXP_c)>0) list($lname)=pg_fetch_row($xresultEXP_c,0);
                                $totSTR = strlen(trim($lname));
                                $expSTR = explode("[EXP",trim($lname));
                                
                                // loop the count
                                if(count($expSTR)>1) {
                                        $lname = $expSTR[0];
                                }
                                /*	for ($ie = 0; $ie < count($expSTR); $ie++) {
                                                $cutEXP = substr(trim($lname),-9);
                                                if ($debug) print "hasil CUT $ie : $cutEXP<br>";
                                                if ($cutEXP=="[EXPIRED]") $remEXP = substr_replace(trim($lname),'',-9);
                                                if ($debug) print "hasil DEL $ie : $remEXP<br>";
                                                if (trim($remEXP)<>'') $lname = $remEXP;
                                                if (trim($lname)=="[EXPIRED]") $lname = '';
                                        }*/
                                // end loop	
                                //if (trim($lname)<>"") {
                                $sql = "update msmemb_extra set last_name='$lname' where code='$rowmem[1]'; update msmemb set expired_date=null where code='$rowmem[1]';";
                                //$xresultEXP=pg_exec($db,$sql);
                                pg_send_query($db,$sql);
                                $rec = pg_get_result($db);
                                //error_log($_SERVER['PHP_SELF']." [".date('F j, Y, g:i a')."] \n sql=".$sql." \n", 3, "/var/log/mlmtest.log");
                                if(pg_result_error($rec)){
                                    error_log($_SERVER['PHP_SELF']." [".date('F j, Y, g:i a')."] ".__LINE__."\n Error=".pg_result_error($rec)."\n sql=".$sql." \n", 3, "/var/log/mlmtest.log");
                                }
                                    
                                    //}							
                            }
                        }
                    }
                } else $yy++;
                    //}

                } 
            } //end for
            $msg = "$zz record(s) updated";
            if ($opnm=='charles'){
                var_dump($msg);
            }
            $updateres	.= "<br><b>$msg, $ww record(s) not exist</b>";
            if($yy>0) $updateres	.= "<br><b> $yy record(s) not allow to update</b>";
        }
    }
	
    if ($nx==2 && $xtype==1) {

        $xsql = "select code,flag from msmemb where code='$memcode';";
        $xres = pg_exec($db,$xsql);
        if(pg_numrows($xres)>0) {
            list($memid,$memflag) = pg_fetch_row($xres,0);
            //if($memflag=='2' || $memflag=='5') {		
            if($memflag=='2') {
                $msg = "This member can not be update ($memcode).";
                header("location: upd_membership.php?msg=$msg");
                exit;	
            } else {
                $asql = "select a_edit from mflag_setup where mflag='$memflag';";
                $ares = pg_exec($db,$asql);
                if(pg_numrows($ares)>0) {
                        list($allow) = pg_fetch_row($ares,0);
                        if($allow=='f'){
                                $msg = "This member can not be update ($memcode). Please Check membership flag setup.";
                                header("location: upd_membership.php?msg=$msg");
                                exit;
                        }
                }
            }
	}

        if (get_memname($memcode)=="-") {
            $msg = "<font color='red'>Member ($memcode) not exist !</font>"; 
            $dis = " disabled";
            header("location: upd_membership.php?msg=$msg");
            exit;	
        }
    }
	
    include("../module/header.inc");
?> 
<script language="javascript">
function validcek(){	
	if (document.frm_level.actx[0].checked==false && document.frm_level.actx[1].checked==false && document.frm_level.actx[2].checked==false && document.frm_level.actx[3].checked==false && document.frm_level.actx[4].checked==false && document.frm_level.actx[5].checked==false && document.frm_level.actx[6].checked==false && document.frm_level.actx[7].checked==false && document.frm_level.actx[8].checked==false) {
		alert('Please select action type !');		
		return false;
	}
	if (document.frm_level.userfile.value=="") {
		alert('Please select file to upload !');
		document.frm_level.userfile.focus();
		return false;
	}
    document.frm_level.cbo_report.value='1';
	document.frm_level.sess.value='a';
	document.frm_level.cmd.value='upme';
	document.frm_level.submit();
}

function updatecek(){	
	if (document.frm_level.actx[0].checked==false && document.frm_level.actx[1].checked==false && document.frm_level.actx[2].checked==false && document.frm_level.actx[3].checked==false && document.frm_level.actx[4].checked==false && document.frm_level.actx[5].checked==false && document.frm_level.actx[6].checked==false && document.frm_level.actx[7].checked==false && document.frm_level.actx[8].checked==false) {
		alert('Please select action type !');
		// document.frm_level.userfile.focus();
		return false;
	}
	
	document.frm_level.sess.value='b';
	document.frm_level.cbo_report.value='1';
	document.frm_level.cmd.value='upme';
	document.frm_level.submit();
}

function purgecek(){	
	if (document.frm_level.actx[0].checked==false && document.frm_level.actx[1].checked==false && document.frm_level.actx[2].checked==false && document.frm_level.actx[3].checked==false && document.frm_level.actx[4].checked==false && document.frm_level.actx[5].checked==false && document.frm_level.actx[6].checked==false && document.frm_level.actx[7].checked==false && document.frm_level.actx[8].checked==false) {
		alert('Please select action type !');
		// document.frm_level.userfile.focus();
		return false;
	}
	if (document.frm_level.purgx[0].checked==false && document.frm_level.purgx[1].checked==false) {
		alert('Check to purge !');
		// document.frm_level.userfile.focus();
		return false;
	}	
		document.frm_level.sess.value='c';
		document.frm_level.cbo_report.value='0';
		document.frm_level.cmd.value='upme';		
		document.frm_level.action="upd_membership.php#purged";
		document.frm_level.submit();	
}

function purgedel(){	
	if (document.frm_level.actx[0].checked==false && document.frm_level.actx[1].checked==false && document.frm_level.actx[2].checked==false && document.frm_level.actx[3].checked==false && document.frm_level.actx[4].checked==false && document.frm_level.actx[5].checked==false && document.frm_level.actx[6].checked==false && document.frm_level.actx[7].checked==false && document.frm_level.actx[8].checked==false) {
		alert('Please select action type !');
		// document.frm_level.userfile.focus();
		return false;
	}
	if (document.frm_level.purgx[0].checked==false && document.frm_level.purgx[1].checked==false) {
		alert('Check to purge !');
		// document.frm_level.userfile.focus();
		return false;
	}
	valid=confirm("Are you sure to perform this action?");
	if (valid) {
	document.frm_level.sess.value='c';
	document.frm_level.cbo_report.value='0';
	document.frm_level.cmd.value='upme';
	document.frm_level.deltemp.value='1';
	document.frm_level.action="upd_membership.php#purged";
	document.frm_level.submit();
	}
}

function xrem(xxx) {
xnote = ' on '+document.frm_level.txtUdate.value;
if (xxx=='0') xnote = 'Membership <?=flag_desc(0)?>'+xnote;
if (xxx=='1') xnote = '<?=flag_desc(1)?> membership'+xnote;
if (xxx=='2') xnote = '<?=flag_desc(2)?> membership'+xnote;
if (xxx=='3') xnote = 'Membership <?=flag_desc(3)?>'+xnote;
if (xxx=='4') xnote = 'Membership <?=flag_desc(4)?>'+xnote;
if (xxx=='5') xnote = 'Membership <?=flag_desc(5)?>'+xnote;
if (xxx=='4' || xxx=='5') {
	document.frm_level.ex_cdate.checked=true;
} else document.frm_level.ex_cdate.checked=false;
if (xxx=='6') xnote = 'Membership <?=flag_desc(6)?>'+xnote;
if (xxx=='7') xnote = 'Membership <?=flag_desc(7)?>'+xnote;
if (xxx=='8') xnote = 'Membership <?=flag_desc(8)?>'+xnote;
document.frm_level.txtnote.value = xnote;
}

function upval(){	
	if (document.frm_level.actx[0].checked==false && document.frm_level.actx[1].checked==false && document.frm_level.actx[2].checked==false && document.frm_level.actx[3].checked==false && document.frm_level.actx[4].checked==false && document.frm_level.actx[5].checked==false && document.frm_level.actx[6].checked==false && document.frm_level.actx[7].checked==false && document.frm_level.actx[8].checked==false) {
		alert('Please select action type !');
		// document.frm_level.userfile.focus();
		return false;
	}
	if ((document.frm_level.actx[4].checked==true || document.frm_level.actx[5].checked==true) && document.frm_level.ex_date.value=='') {
		alert('Please enter expired date !');
		document.frm_level.ex_date.focus();
		return false;
	}
	if (document.frm_level.txtpass.value=='') {
		alert('Please enter password !');
		document.frm_level.txtpass.focus();
		return false;
	}
	valid=confirm("Are you sure to perform this action?");
	if (valid) {
	document.frm_level.cmd.value='upme';
	document.frm_level.submit();
	}
}

function mval() {
	if (document.frm_level.memcode.value=='') {
		alert('Information is incomplete !');
		document.frm_level.memcode.focus();
		return false;
	}
	document.frm_level.submit();
}
  
</script>
<table width="790" border="0" align="center" cellspacing="0" cellpadding="0">
  <tr>
    <td width="50%"><b><img src="../images/members.gif" width="18" height="18" hspace="2" vspace="2" align="absmiddle" > Update Membership Flag</b></td>
    <td align="right" class="red">
	<? if ($msg<>"") echo $msg;?>
	</td>
  </tr>
</table>
<form action="upd_membership.php" method="post" name="frm_level" enctype="multipart/form-data">
<table border="0" bgcolor="#CCCCCC" align="center" cellpadding="2" cellspacing="1" width="790">
<?php if ($nx=='') { ?>
	  <tr bgcolor="#e6e6e6">
          <td colspan="2">&nbsp;</td>
        </tr>
        <tr bgcolor="#FFFFFF" valign="top">
          <td width="15%">&nbsp;Update Type</td>
          <td ><input type="radio" name="xtype" value="0" <?php if ($xtype==0) print "checked"; ?>> By DBF File<br><input type="radio" name="xtype" value="1" <?php if ($xtype==1) print "checked"; ?>> By Member Code
          </td>
        </tr>
		<tr bgcolor="#e6e6e6">
          <td colspan="2">&nbsp;<input type="submit" name="btnNext" value="Next"><input type="hidden" name="nx" value="1"></td>
        </tr>
<?php 
    } 
    if ($nx==1 && $xtype==1) {  
?>
	<tr bgcolor="#e6e6e6">
          <td colspan="2">&nbsp;</td>
        </tr>
        <tr bgcolor="#FFFFFF" valign="top">
          <td width="15%">&nbsp;Member Code</td>
          <td ><input type="text" name="memcode" value="" size="15" maxlength="9"></td>
        </tr>
	<tr bgcolor="#e6e6e6">
          <td colspan="2">&nbsp;<input type="button" name="btnNext" value="Next" onclick="mval()"><input type="hidden" name="nx" value="2"><input type="hidden" name="xtype" value="<?=$xtype?>"></td>
        </tr>
<?php 
    } 
    if ($nx==2 && $xtype==1) {
        $dis = ''; $dis2 = '';
        $querr="select trim(flag),code from msmemb where code='$memcode';";
        $result=pg_exec($db,$querr);
        if (pg_numrows($result)>0) $rowF = pg_fetch_row($result,0);
        // if ($rowF[0]=="3" || $rowF[0]=="2" || $rowF[0]=="0") $dis2 =  " disabled";
        if ($rowF[0]=="2") {
            // $msg = "This member ($memcode) already Suspended/Terminated";
            // header("location: upd_membership.php?msg=$msg");	
            // exit;
            $dis = " disabled";
        }
	if (get_memname($memcode)=="-") {
            $msg = "<font color='red'>Member ($memcode) not exist !</font>"; 
            $dis = " disabled";
            header("location: upd_membership.php?msg=$msg");
            exit;	
        }
?>
	<tr bgcolor="#e6e6e6">
          <td colspan="2">&nbsp;</td>
        </tr>
        <tr bgcolor="#FFFFFF" valign="top">
          <td width="15%">&nbsp;Member Code</td>
          <td ><?=$memcode?> (<?=get_memname($memcode)?>)<input type="hidden" name="memcode" value="<?=$memcode?>"> <?=$msg?></td>
        </tr>
		<tr bgcolor="#FFFFFF" valign="top">
          <td width="15%">&nbsp;Current Status</td>
          <td ><?php if (strtoupper(flag_desc($rowF[0]))=="REINSTATED") print "NORMAL"; else print strtoupper(flag_desc($rowF[0])); ?></td>
        </tr>
		  <tr bgcolor="#FFFFFF" valign="top">
          <td width="15%">&nbsp;Flag type</td>
          <td><input type="radio" name="actx" onclick="xrem(this.value)" value="0" <? if ($actx==0) print "checked"; ?><?=$dis?>> Reinstated
            <br><input type="radio" name="actx" onclick="xrem(this.value)" value="1" <? if ($actx==1) print "checked"; ?><?=$dis?>> Suspend/Inactive
            <br><input type="radio" name="actx" onclick="xrem(this.value)" value="2" <? if ($actx==2) print "checked"; ?><?=$dis?>> Terminate
            <br><input type="radio" name="actx" onclick="xrem(this.value)" value="3" <? if ($actx==3) print "checked"; ?><?=$dis?>> Resign
            <br><input type="radio" name="actx" onclick="xrem(this.value)" value="4" <? if ($actx==4) print "checked"; ?><?=$dis?>> Expired
            <br><input type="radio" name="actx" onclick="xrem(this.value)" value="5" <? if ($actx==5) print "checked"; ?><?=$dis?>> Permanent expired
            <br><input type="radio" name="actx" onclick="xrem(this.value)" value="6" <? if ($actx==6) print "checked"; ?><?=$dis?>> Transferred
            <br><input type="radio" name="actx" onclick="xrem(this.value)" value="7" <? if ($actx==7) print "checked"; ?><?=$dis?>> Rejoin
            <br><input type="radio" name="actx" onclick="xrem(this.value)" value="8" <? if ($actx==8) print "checked"; ?><?=$dis?>> Deceased
          </td>
        </tr>
	<tr bgcolor="#FFFFFF" valign="top">
          <td width="15%">&nbsp;Update for<br>&nbsp;(for Expired Status)</td>
          <td ><input type="checkbox" name="ex_name" value="1"> Name<br><input type="checkbox" name="ex_cdate" value="1"> Expired Date <input type="text" name="ex_date" value="<?=$ex_date?>" size="12" maxlength="10" <?=$dis?>>
                <a href="javascript:void(0)" onClick="gfPop.fPopCalendar(frm_level.ex_date);return false;" HIDEFOCUS>
                        <img src="../images/cal_show.gif" border="0">
                </a></td>
        </tr>
	<tr bgcolor="#FFFFFF" valign="top">
          <td width="15%">&nbsp;Update Date</td>
          <td ><input type="text" name="txtUdate" value="<?if($txtUdate=="") echo date("d/m/Y"); else echo $txtUdate;?>" size="12" maxlength="10" <?=$dis?>>
                <a href="javascript:void(0)" onClick="gfPop.fPopCalendar(frm_level.txtUdate);return false;" HIDEFOCUS>
                        <img src="../images/cal_show.gif" border="0">
                </a></td>
        </tr>
        <tr bgcolor="#FFFFFF" valign="top">
          <td width="15%">&nbsp;Remarks</td>
          <td ><input type="text" name="txtnote" value="" size="55" <?=$dis?>></td>
        </tr>
		<tr bgcolor="#FFFFFF" valign="top">
          <td width="15%">&nbsp;Confirm Password</td>
          <td ><input type="password" name="txtpass" value="" size="15" <?=$dis?>></td>
        </tr>
		<tr bgcolor="#e6e6e6">
          <td colspan="2">&nbsp;<input type="button" name="btnNext" value="Submit" onclick="upval()" <?=$dis?>> <input type="button" name="btnCancel" value="Cancel" onclick="location.href='upd_membership.php'" ><input type="hidden" name="cmd"><input type="hidden" name="nx" value="3"><input type="hidden" name="xtype" value="<?=$xtype?>"></td>
        </tr>
		 
<?php } ?>
		<!-- DBF type border -->
<?php if ($nx==1 && $xtype==0) {  ?>
	<tr bgcolor="#e6e6e6">
          <td colspan="2">&nbsp;<b></b></td>
        </tr>
        <tr bgcolor="#FFFFFF" valign="top">
          <td width="15%">&nbsp;Flag type</td>
          <td >
              <label><input type="radio" name="actx" value="0" <?php if ($actx==0) print "checked"; ?>> Reinstated</label>
            <br><label><input type="radio" name="actx" value="1" <?php if ($actx==1) print "checked"; ?>> Suspend/Inactive</label>
            <br><label><input type="radio" name="actx" value="2" <?php if ($actx==2) print "checked"; ?>> Terminate</label>
            <br><label><input type="radio" name="actx" value="3" <?php if ($actx==3) print "checked"; ?>> Resign</label>
            <br><label><input type="radio" name="actx" value="4" <?php if ($actx==4) print "checked"; ?>> Expired</label>
            <br><label><input type="radio" name="actx" value="5" <?php if ($actx==5) print "checked"; ?>> Permanent expired</label>
            <br><label><input type="radio" name="actx" value="6" <?php if ($actx==6) print "checked"; ?>> Transferred</label>
            <br><label><input type="radio" name="actx" value="7" <?php if ($actx==7) print "checked"; ?>> Rejoin</label>
            <br><label><input type="radio" name="actx" value="8" <?php if ($actx==8) print "checked"; ?>> Deceased</label>
          </td>
        </tr>
	<tr bgcolor="#FFFFFF">
          <td width="15%">&nbsp;Upload file</td>
          <td ><input type="file" name="userfile" size="30">
          </td>
        </tr>
       <tr bgcolor="#FFFFFF" valign="top">
          <td width="15%">&nbsp;Update for<br>&nbsp;(for Expired Status)</td>
          <td ><input type="checkbox" name="ex_name" value="1"> Name</td>
        </tr>
        <tr bgcolor="#e6e6e6">
          <td colspan="2">&nbsp;<input type="button" name="btnOK" value="Upload file" onclick="validcek()" > <input type="button" name="btnOK" value="Update" onclick="updatecek()" ><input type="hidden" name="cmd"><input type="hidden" name="sess"><input type="hidden" name="cbo_report"><input type="hidden" name="nx" value="<?=$nx?>"><input type="hidden" name="xtype" value="<?=$xtype?>"></td>
        </tr>

		<tr bgcolor="#ffffff">
          <td colspan="2">&nbsp;</td>
        </tr>
        <tr bgcolor="#e6e6e6">
          <td colspan="2">&nbsp;<b>List/Purge Member Data in Temporary Table</b></td>
        </tr>
        <tr bgcolor="#FFFFFF" valign="top">
          <td width="15%">&nbsp;Select Date</td>
          <td ><input type="radio" name="purgx" value="0" <?php if ($purgx==0) print "checked"; ?>> All<br><input type="radio" name="purgx" value="1" <? if ($purgx==1) print "checked"; ?>> Date from <input type="text" name="txtTgl1" value="<?if($txtTgl1=="") echo date("d/m/Y"); else echo $txtTgl1;?>" size="12" maxlength="10">
                <a href="javascript:void(0)" onClick="gfPop.fPopCalendar(frm_level.txtTgl1);return false;" HIDEFOCUS>
                        <img src="../images/cal_show.gif" border="0">
                </a> <b>-</b> Date to <input type="text" name="txtTgl2" value="<?if($txtTgl2=="") echo date("d/m/Y"); else echo $txtTgl2;?>" size="12" maxlength="10">
                <a href="javascript:void(0)" onClick="gfPop.fPopCalendar(frm_level.txtTgl2);return false;" HIDEFOCUS>
                        <img src="../images/cal_show.gif" border="0">
                </a>
          </td>
        </tr>
       
        <tr bgcolor="#e6e6e6">
<?php if ($sess=="c") {
        
        $tabh = "tmp_mflag";	   
        pg_query($db,"set datestyle to 'POSTGRES,EUROPEAN'");
	   
        if ($deltemp=="1") {
            $commdel = "delete from $tabh where act_type='$actx';";
            if ($purgx==1 && $actx==0) $commdel = "delete from $tabh where act_type='$actx' and xdate>='$txtTgl1' and xdate<='$txtTgl2';";
            if ($purgx==1 && $actx==1) $commdel = "delete from $tabh where act_type='$actx';";
            $resultDEL=pg_exec($db,$commdel);
            //print $commdel;
        }     
       
      if ($purgx==0) $commdeltest = "select * from $tabh where act_type='$actx' order by code;";
      //if ($purgx==1 && $actx==0) 
      else $commdeltest = "select * from $tabh where act_type='$actx' and xdate>='$txtTgl1' and xdate<='$txtTgl2' order by code;";			
      //if ($purgx==1 && $actx==1) $commdeltest = "select * from $tabh where act_type='$actx' order by code;";
      $resultSTATD=pg_exec($db,$commdeltest);
    } 
?>
          <td colspan="2">&nbsp;<input type="button" name="btnOK" value="List" onclick="purgecek()" > <input type="button" name="btnOK" value="Purge" onclick="purgedel()" ><input type="hidden" name="deltemp" value="0">
              
            </td>
        </tr>
<?php 
    if ($sess=="c" && $deltemp=="0") {
?>
       <tr bgcolor="#ffffff">
          <td colspan="2"><a name="purged">
          <table border="0" bgcolor="#CCCCCC" align="center" cellpadding="1" cellspacing="1" width="100%">
          <tr bgcolor="#e6e6e6"><td width="15%">Member ID</td><td>Member Name</td><td width="12%"><? if ($actx==4) { ?>Expired Date<? } else print "&nbsp;"; ?></td></tr>
<?php
        if (pg_numrows($resultSTATD)<1) { 
?>
          <tr><td colspan="3" bgcolor="#ffffff" align="center">Data not exists</td></tr>
<?php          
        } else {
            for ($k = 0; $k < pg_numrows($resultSTATD); $k++) { 
            $rowmemb=pg_fetch_row($resultSTATD,$k);         		
            //if ($actx=="1") $rowmemb[2] = get_memname($rowmemb[0]);
?>
              <tr bgcolor="#ffffff"><td width="15%"><?=$rowmemb[1]?></td><td><?=stripslashes($rowmemb[2])?></td><td width="12%"><?=$rowmemb[3]?></td></tr>
<?php 
            }
        } 
?>
          <tr bgcolor="#e6e6e6"><td colspan="3">&nbsp;<input type="button" name="btnCancel" value="Cancel" onclick="location.href='../member/index.php'" ></td></tr>
          </table>
          </td>
        </tr>
<?php 
    }
?>

<?php if ($zzz==1) { ?>
	  
       <tr bgcolor="#ffffff">
          <td colspan="2">&nbsp;</td>
        </tr>
        
	  <tr bgcolor="#e6e6e6">
          <td colspan="2">&nbsp;<b>Update Expired/Reactive Member Data</b></td>
        </tr>
        <tr bgcolor="#FFFFFF" valign="top">
          <td width="15%">&nbsp;Update for</td>
          <td ><input type="checkbox" name="mname" value="1"> Name<br><input type="checkbox" name="mstat" value="1"> Status<br><input type="checkbox" name="mexp" value="1" > Expired Date
          </td>
        </tr>
       
        <tr bgcolor="#e6e6e6">
          <td colspan="2">&nbsp;</td>
        </tr>
        
<?php
    } 
} 
?>
  <tr bgcolor="#FFFFFF"><td align="center" colspan="2"><?=$updateres?></td></tr> 
</table>
    <input type="hidden" id="session_name" name="session_name" value="<?=$session_name?>"/>
</form>

<iframe width="174" height="189" name="gToday:normal:../jsfile/agenda.js" id="gToday:normal:../jsfile/agenda.js" src="../jsfile/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; left:-500px; top:0px;"></iframe>

<?php
    if ($debug){
        $bresult=pg_exec($db,"rollback");
    } else {
        $bresult=pg_exec($db,"commit");
    }
    
    
include("../module/footer.inc");
?>
