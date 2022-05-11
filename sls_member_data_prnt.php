<?php
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");
	include("../module/global.inc");
	//include_once("../interfaces/Database.interface.php");
	//include_once("../classes/database/PostgreSQL.class.php");
	//include_once("../classes/Logger.class.php");
	//include_once("../commons/database.inc");		
	pg_query($db,"set datestyle to 'POSTGRES,EUROPEAN'");
	check_login();
	$cmb_report_type=$_POST['cmb_report_type'];
       // $cbo_country=$_POST['cbo_country'];
        $cmb_sts=$_POST['cmb_sts'];
        $txtTgl1=$_POST['txtTgl1'];
        $txtTgl2=$_POST['txtTgl2'];
/*	$access = $db2->doQuery("select allbr as all_branch from users_extra where uname='$opnm'")->getFirstRow();
	if ($access["all_branch"]=='t') 
		$mssql = "select br_code from msms_new where br_status and not br_deleted and br_region='$cn_id'";
	else 
		$mssql = "select b.br_code from msms_new b, users_braccess a 
			where a.brcode=b.br_code and b.br_status and not b.br_deleted and a.uname='$opnm' and br_region='$cn_id'";
	
	$asql = "select a.cn_id,a.code,a.dspbal,d.prdcd from discpack_balance a
		join newmstrh b on a.code=b.code and b.trtype='14'
		join newmstrd c on b.trcd=c.trcd 
		join msprd_conditions d on c.prdcd=d.prdcd and a.cn_id=d.cn_id and d.dsp 
		join ($mssql) as m on b.loccd=m.br_code
		where a.cn_id='$cn_id' and a.dspbal>0 order by a.code";
       */
	     
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head><title><?=$judul?></title>
	<?echo $M_CSS; ?>
	<style type="text/css" media="Screen">
		/* <![CDATA[ */
		#main{ margin:auto;}
		#tomato{
			margin: auto;
			visibility: visible
		}
		/* ]]> */
	</style>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

	<SCRIPT language="JavaScript">
		top.document.title = '<?=$judul?>';
		window.status = '';
		window.status = '<?=mxlang("12",'')?>';
	
		function setVisible(obj, bool){
			if(typeof obj == "string") obj = document.getElementById(obj);
			
			if(bool == false){
				if(obj.style.visibility != 'hidden');
					obj.style.visibility = 'hidden';
				} else {
				if(obj.style.visibility != 'visible');
				obj.style.visibility = 'visible';
			}
		}
	
		function goprint(){
			setVisible('tomato',false);
			setTimeout("window.print()", 1000);
			setTimeout("setVisible('tomato',true)", <?=$print_delay?>);
			//window.print();
		}
	
	</SCRIPT>
</head>
<body bgcolor="#FFFFFF" text="#000000" leftmargin="5" topmargin="5" marginwidth="0" marginheight="0">   
      <div align='center'><h3>Sales and Member Data Report</h3></div><br>
	<table width="790" align="center" border="1" cellpadding="2" cellspacing="0" bgcolor="#000" id="theTable" border-color="#000">
        <?
              $txtTgl1ymd=implode("-", array_reverse(explode("/", $txtTgl1)));
                        $txtTgl2ymd=implode("-", array_reverse(explode("/", $txtTgl2)));
                         $vSQL = "select * from mlm.trx_type_setup where cn_id='PH'";
		$vRes = pg_query($db,$vSQL);
		$vRowSetup = pg_fetch_assoc($vRes,0);
            if ($cmb_report_type == 'summ') {
                ?>
            <TR bgcolor="#FFFFFF" style="font-weight:bold"> 
                        <td colspan="4" ><b>Country :Philippines</b></div></td>
                       <td colspan="4" align="right" >&nbsp;Period: <?=$txtTgl1?> - <?= $txtTgl2?></td>
                    </tr>
                    <TR bgcolor="#dee7e5"> 
                        <TD colspan="8" valign="top" >
                            <b>1. SIMP Sales Data</b>
                        </td>
                    </tr> 
                    <tr bgcolor="#dee7e5" style="font-weight:bold">
                      <td width="13%" align="center">Type</td>
                        <td width="13%" align="center">Amount (PHP)</td>
                        <td width="13%" align="center">PV</td>
                        <td width="13%" align="center">SV (PHP)</td>
                        <td width="13%" align="center">MPV</td>
                        <td width="13%" align="center">Total Record(Header)</td>
                        <td width="13%" align="center">Total Record(Detail)</td>
                      <td align="center">&nbsp;</td>


                    </tr>
             <?  if($vRowSetup['cashbill'] =='t') {	?>
                    <tr bgcolor="#dee7e5">
                        <td>Cash Bill</td>
                        <?
                      
                        $SqlcbBramt="select sum(ndp) as amt, sum(npv) as pv , sum(nbv)as sv,count(trcd) as total from mlm.newmstrh where trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and  trtype='1'";
                        $SqlcbSCamt="select sum(ndp) as amt, sum(npv) as pv , sum(nbv)as sv,count(trcd) as total from mlm.newsctrh where trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and  trtype='1'";
                        if($cmb_sts=='1'){
                            $SqlcbBramt .=" and post='$cmb_sts' "    ;
                            $SqlcbSCamt .=" and post='$cmb_sts' "    ;
                        }else if($cmb_sts=='-1'){
                            $SqlcbBramt .=" and post='0'  "    ;
                            $SqlcbSCamt .=" and post='0'   "    ;
                        }
                        $SqlcbBramt .="  and trcd not like '%****%' and bc_id='PH' "    ;
                        $SqlcbSCamt .=" and trcd not like '%****%' and bc_id='PH'  "    ;
                  // echo "$SqlcbBramt<br>";
                      //    echo "$SqlcbSCamt<br>";
                        $resbrcbamt= pg_exec($db,$SqlcbBramt);
                        if(pg_num_rows($resbrcbamt)>0){
                         //   echo "$SqlcbBramt<br>";
                            $resbr = pg_fetch_assoc($resbrcbamt, 0);
                            //print_r($resbr);
                            $DPCBamt=$resbr["amt"];
                            $PVCBamt= $resbr["pv"];     
                            $SVCBamt= $resbr["sv"];  
                            $TotalBrHeader=$resbr["total"];  
                        }else {$DPCBamt=0;$PVCBamt=0;$SVCBamt=0;$TotalBrHeader=0;}
                        $ressccbamt= pg_exec($db,$SqlcbSCamt);
                        if(pg_num_rows($ressccbamt)>0){
                        //     echo "$SqlcbSCamt<br>";
                            $resbr = pg_fetch_assoc($ressccbamt, 0);
                            // print_r($resbr);
                            $DPCBamt +=$resbr["amt"];
                            $PVCBamt += $resbr["pv"];     
                            $SVCBamt += $resbr["sv"];   
                            $TotalBrHeader +=$resbr["total"]; 
                        }
                         $SqlSCDetail = "select count(prdcd) as qty
                                  from mlm.newsctrh a join mlm.newsctrd b on a.trcd=b.trcd   where  trtype='1'
                                  and trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and a.trcd not like '%****%'   ";
                        $SqlBRDetail = "select count(prdcd) as qty
                                  from mlm.newmstrh a join mlm.newmstrd b on a.trcd=b.trcd     where trtype='1'
                                  and trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and a.trcd not like '%****%'   ";
                        if($cmb_sts=='1'){
                            $SqlSCDetail .=" and post='$cmb_sts' "    ;
                            $SqlBRDetail .=" and post='$cmb_sts' "    ;
                        }else if($cmb_sts=='-1'){
                            $SqlSCDetail .=" and post='0'  "    ;
                            $SqlBRDetail .=" and post='0'   "    ;
                        }
                        $resbrdetail= pg_exec($db,$SqlBRDetail);
                        if(pg_num_rows($resbrdetail)>0){
            // echo "$SqlKITBRamt<br>";
                            $resbr = pg_fetch_assoc($resbrdetail, 0);
                          //  print_r($resbr);
                           
                            $TotalBRDetail= $resbr["qty"];  
                        }else {$TotalBRDetail=0;}
                        $resscdetail= pg_exec($db,$SqlSCDetail);
                        if(pg_num_rows($resscdetail)>0){
                          // echo "$SqlKITSCamt<br>";
                           //echo "xxx=$PVKITamt";
                            $resbr = pg_fetch_assoc($resscdetail, 0);
                           //  print_r($resbr);
                           
                            $TotalBRDetail+= $resbr["qty"];   
                        }
                       
                        
                        
                        ?>
                        <td  align="right"><?=number_format(($DPCBamt),2)?></td>
                        <td  align="right"> <?=number_format(($PVCBamt),2)?></td>
                        <td  align="right"><?=number_format(($SVCBamt),2)?></td>
                       <td align="right">-</td>
                        <td  align="right"><?=$TotalBrHeader?></td>
                        <td  align="right"><?=$TotalBRDetail?></td>
                        <td align="center"><input type="checkbox"></td>
                    </tr>
             <?}?>
             <?  if($vRowSetup['ioc'] =='t') {	?>
                      <tr bgcolor="#dee7e5">
                        <td>IOC Promotion PV</td>
                        <?
                       $SqliocBrpromoamt="select sum(tdp) as amt, sum(tpv) as pv , sum(tbv)as sv,count(trcd) as total from mlm.ioc_newmscrtrh where trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and  trtype='14'";
                        $Sqliocscpromoamt="select sum(tdp) as amt, sum(tpv) as pv , sum(tbv)as sv,count(trcd) as total from mlm.ioc_newsctrh where trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and  trtype='14'";
                        if($cmb_sts=='1'){
                            $SqliocBrpromoamt .=" and post='1' "    ;
                            $Sqliocscpromoamt .=" and post='1' "    ;
                        }else if($cmb_sts=='-1'){
                            $SqliocBrpromoamt .=" and post='0'  "    ;
                            $Sqliocscpromoamt .=" and post='0'   "    ;
                        }
                        $SqliocBrpromoamt .="  and trcd not  like '%****%'"    ;
                        $Sqliocscpromoamt .=" and trcd not  like '%****%' "    ;
                    //     echo "$SqliocBrpromoamt<br>";
                      //   echo "$Sqliocscpromoamt<br>";
                        $resbrcbamt= pg_exec($db,$SqliocBrpromoamt);
                        if(pg_num_rows($resbrcbamt)>0){
                        // echo "$SqliocBrpromoamt<br>";
                            $resbr = pg_fetch_assoc($resbrcbamt, 0);
                      //      print_r($resbr);
                            $DPPromoamt=$resbr["amt"];
                            $PVPromoamt= $resbr["pv"];     
                            $SVPromoamt= $resbr["sv"];  
                            $TotalPromoHeader=$resbr["total"];  
                        }else {$DPPromoamt=0;$PVPromoamt=0;$SVPromoamt=0;$TotalPromoHeader=0;}
                        $ressccbamt= pg_exec($db,$Sqliocscpromoamt);
                        if(pg_num_rows($ressccbamt)>0){
                        //     echo "$Sqliocscpromoamt<br>";
                            $resbr = pg_fetch_assoc($ressccbamt, 0);
                     //    print_r($resbr);
                            $DPPromoamt +=$resbr["amt"];
                            $PVPromoamt += $resbr["pv"];     
                            $SVPromoamt += $resbr["sv"];   
                            $TotalPromoHeader +=$resbr["total"]; 
                        }
                         $SqlSCDetail = "select count(b.prdcd) as qty
                                  from mlm.ioc_newmscrtrh a join mlm.ioc_newmscrtrd b on a.trcd=b.trcd   where trtype='14'
                                  and trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and a.trcd not  like '%****%'   ";
                        $SqlBRDetail = "select count(b.prdcd) as qty
                                  from mlm.ioc_newsctrh a join mlm.ioc_newsctrd b on a.trcd=b.trcd     where trtype='14'
                                  and trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and a.trcd not  like '%****%'  ";
                        if($cmb_sts=='1'){
                            $SqlSCDetail .=" and post='1' "    ;
                            $SqlBRDetail .=" and post='1' "    ;
                        }else if($cmb_sts=='-1'){
                            $SqlSCDetail .=" and post='0'  "    ;
                            $SqlBRDetail .=" and post='0'   "    ;
                        }
                       
                        $resbrdetail= pg_exec($db,$SqlBRDetail);
                        if(pg_num_rows($resbrdetail)>0){
            // echo "$SqlKITBRamt<br>";
                            $resbr = pg_fetch_assoc($resbrdetail, 0);
                          //  print_r($resbr);
                           
                            $TotalPromoDetail= $resbr["qty"];  
                        }else {$TotalBRDetail=0;}
                        $resscdetail= pg_exec($db,$SqlSCDetail);
                        if(pg_num_rows($resscdetail)>0){
                          // echo "$SqlKITSCamt<br>";
                           //echo "xxx=$PVKITamt";
                            $resbr = pg_fetch_assoc($resscdetail, 0);
                           //  print_r($resbr);
                           
                            $TotalPromoDetail+= $resbr["qty"];   
                        }
                       
                       $MVPromoamt=$PVPromoamt;
                        ?>
                        <td align="right"><?=number_format(($DPPromoamt),2)?></td>
                        <td align="right">-</td>
                        <td align="right"><?=number_format(($SVPromoamt),2)?></td>
                       <td  align="right"><?=number_format(($MVPromoamt),2)?></td>
                        <td align="right"><?=$TotalPromoHeader?></td>
                        <td align="right"><?=$TotalPromoDetail?></td>
                        <td align="center"><input type="checkbox"></td>
                    </tr>
             <?}?>
             <?  if($vRowSetup['kittrx'] =='t') {	?>
                     <tr bgcolor="#dee7e5">
                     <?
                       $SqlcbBramt="select sum(ndp) as amt, sum(npv) as pv , sum(nbv)as sv,count(trcd) as total from mlm.newmstrh where trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and  trtype='5'";
                        $SqlcbSCamt="select sum(ndp) as amt, sum(npv) as pv , sum(nbv)as sv,count(trcd) as total from mlm.newsctrh where trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and  trtype='5'";
                        $SqlcbTRHamt="select sum(ndp) as amt, sum(npv) as pv , sum(nbv)as sv,count(trcd) as total from mlm.newtrh where trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and  trtype='5' and trcd ilike '%KT%'";
                        if($cmb_sts=='1'){
                            $SqlcbBramt .=" and post='1' "    ;
                            $SqlcbSCamt .=" and post='1' "    ;
                         //   $SqlcbTRHamt.=" and post='1' "    ;
                        }else if($cmb_sts=='-1'){
                            $SqlcbBramt .=" and post='0'  "    ;
                            $SqlcbSCamt .=" and post='0'   "    ;
                            $SqlcbTRHamt.=" and post='0' "    ;
                        }
                        $SqlcbBramt .="  and trcd not like '%****%'"    ;
                        $SqlcbSCamt .=" and trcd not like '%****%' "    ;
                        $SqlcbTRHamt.="and trcd not like '%****%' "    ;
                      
                      
                        $resbrcbamt= pg_exec($db,$SqlcbBramt);
                        if(pg_num_rows($resbrcbamt)>0){
                        
                            $resbr = pg_fetch_assoc($resbrcbamt, 0);
                           //print_r($resbr);
                            $DPKITamt=$resbr["amt"];
                            $PVKITamt= $resbr["pv"];     
                            $SVKITamt= $resbr["sv"];  
                            $KITTotalHead=$resbr["total"];  
                        }else {$DPKITamt=0;$PVCBamt=0;$SVCBamt=0;$TotalBrHeader=0;}
                      $ressccbamt= pg_exec($db,$SqlcbSCamt);
                        if(pg_num_rows($ressccbamt)>0){
                        //     echo "$SqlcbSCamt<br>";
                            $resbr = pg_fetch_assoc($ressccbamt, 0);
                           // print_r($resbr);
                            $DPKITamt +=$resbr["amt"];
                            $PVKITamt += $resbr["pv"];     
                            $SVKITamt += $resbr["sv"];   
                            $KITTotalHead +=$resbr["total"]; 
                        }
                     //     echo "$SqlcbTRHamt<br>";
                       $rescbtrhamt= pg_exec($db,$SqlcbTRHamt);
                        if(pg_num_rows($rescbtrhamt)>0){
                        //     echo "$SqlcbSCamt<br>";
                            $resbr = pg_fetch_assoc($rescbtrhamt, 0);
                        //  print_r($resbr);
                            $DPKITamt +=$resbr["amt"];
                            $PVKITamt += $resbr["pv"];     
                            $SVKITamt += $resbr["sv"];   
                            $KITTotalHead +=$resbr["total"]; 
                        }
                         $SqlSCDetail = "select count(prdcd) as qty
                                  from mlm.newsctrh a join mlm.newsctrd b on a.trcd=b.trcd   where  trtype='5'
                                  and trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and a.trcd not like '%****%'   ";
                        $SqlBRDetail = "select count(prdcd) as qty
                                  from mlm.newmstrh a join mlm.newmstrd b on a.trcd=b.trcd     where trtype='5'
                                  and trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and a.trcd not like '%****%'   ";
                        $SqlBRtrhDetail = "select count(prdcd) as qty
                                  from mlm.newtrh a join mlm.newtrd b on a.trcd=b.trcd     where trtype='5'
                                  and trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and a.trcd not like '%****%'   and a.trcd ilike '%KT%'  ";
                        if($cmb_sts=='1'){
                            $SqlSCDetail .=" and post='1' "    ;
                            $SqlBRDetail .=" and post='1' "    ;
                           // $SqlBRtrhDetail.=" and post='1' "    ;
                        }else if($cmb_sts=='-1'){
                            $SqlSCDetail .=" and post='0'  "    ;
                            $SqlBRDetail .=" and post='0'   "    ;
                            $SqlBRtrhDetail.=" and post='0' "    ;
                        }
                        $resbrdetail= pg_exec($db,$SqlBRDetail);
                     
                        if(pg_num_rows($resbrdetail)>0){
            // echo "$SqlKITBRamt<br>";
                            $resbr = pg_fetch_assoc($resbrdetail, 0);
                          //  print_r($resbr);
                           
                            $TotalKITDetail= $resbr["qty"];  
                        }else {$TotalKITDetail=0;}
                        $resscdetail= pg_exec($db,$SqlSCDetail);
                        if(pg_num_rows($resscdetail)>0){
                          // echo "$SqlKITSCamt<br>";
                           //echo "xxx=$PVKITamt";
                            $resbr = pg_fetch_assoc($resscdetail, 0);
                           //  print_r($resbr);
                           
                            $TotalKITDetail+= $resbr["qty"];   
                        }
                      //  echo "$SqlBRtrhDetail<br>";
                    $restrhdetail= pg_exec($db,$SqlBRtrhDetail);
                        if(pg_num_rows($restrhdetail)>0){
                          // echo "$SqlKITSCamt<br>";
                           //echo "xxx=$PVKITamt";
                            $resbr = pg_fetch_assoc($restrhdetail, 0);
                         //print_r($resbr);
                           
                            $TotalKITDetail+= $resbr["qty"];   
                        }
                        $sqlKITMPV="select sum(ipv)::numeric as tot_ipv from mlm.newtrh 
                             where trdt >= '$txtTgl1ymd' and trdt <= '$txtTgl2ymd' 
                             and wv > 0 and ipv > 0";
                    if($cmb_sts=='1'|| $cmb_sts==''){
                        $resKitMpv= pg_exec($db,$sqlKITMPV);
                         if(pg_num_rows($resKitMpv)>0){
                              $resbr = pg_fetch_assoc($resKitMpv, 0);
                             
                              $TotalKITMPv= $resbr["tot_ipv"]; 
                         }
                    }
                     ?>   
                        <td>KIT Transaction</td>
                        <td align="right"><?=number_format($DPKITamt,2)?></td>
                        <td align="right"><?=number_format($PVKITamt,2)?></td>
                        <td align="right"><?=number_format($SVKITamt,2)?></td>
                       <td align="right"><?=number_format($TotalKITMPv,2)?></td>
                        <td align="right"><?=$KITTotalHead?></td>
                        <td align="right"><?=$TotalKITDetail?></td>
                        <td align="center"><input type="checkbox"></td>
                    </tr>
             <?}?>
                      <tr bgcolor="#dee7e5" style="font-weight:bold">
   
                        <td align="right"><div align="center"><strong> Total </strong></div></td>
                         <td align="right"><?=number_format(($DPCBamt+$DPKITamt+$DPPromoamt),2)?></td>
                        <td align="right"><?=number_format(($PVCBamt+$PVKITamt),2)?></td>
                        <td align="right"><?=number_format(($SVCBamt+$SVKITamt),2)?></td>
                       
                       <td  align="right"><?=number_format(($TotalKITMPv+$MVPromoamt),2)?></td>
                        <td align="right"><?=$TotalBrHeader+$KITTotalHead+$TotalPromoHeader?></td>
                        <td align="right"><?=($TotalBRDetail+$TotalKITDetail+$TotalPromoDetail)?></td>
                        <td align="center"><input type="checkbox"></td>
                    </tr>
               <tr bgcolor="#fff">
    <td colspan="8">&nbsp;</td>
  </tr>
   <TR bgcolor="#ffd7d7"> 
                        <TD colspan="8" valign="top" >
                            <b>2. IOC Sales Data</b>
                        </td>
                    </tr> 
                     <tr bgcolor="#ffd7d7" style="font-weight:bold">
                         <td width="13%" align="center">Type</td>
                        <td width="13%" align="center">Amount (PHP)</td>
                        <td width="13%" align="center">PV</td>
                        <td width="13%" align="center">SV (PHP)</td>
                        <td width="13%" align="center">MPV</td>
                        <td width="13%" align="center">Total Record(Header)</td>
                        <td width="13%" align="center">Total Record(Detail)</td>
<td ></td>

                    </tr>
                    <?
                     
                      $SqlIOCBramt="select sum(tdp) as amt, sum(tpv) as pv , sum(tbv)as sv,count(trcd) as total from mlm.ioc_newmscrtrh a where trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and  trtype='14'";
                        $SqlIOCSCamt="select sum(tdp) as amt, sum(tpv) as pv , sum(tbv)as sv,count(trcd) as total from mlm.ioc_newsctrh a where trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and  trtype='14'";
                        if($cmb_sts=='1'){
                            $SqlIOCBramt .=" and post='1' "    ;
                            $SqlIOCSCamt .=" and post='1' "    ;
                        }else if($cmb_sts=='-1'){
                            $SqlIOCBramt .=" and post='0'  "    ;
                            $SqlIOCSCamt .=" and post='0'   "    ;
                        }
                        $SqlIOCBramt .="  and a.trcd not like '%****%'"    ;
                        $SqlIOCSCamt .=" and a.trcd not like '%****%' "    ;
                  // echo "$SqlIOCBramt<br>";
                    //  echo "$SqlIOCSCamt<br>";
                        $resioccbamt= pg_exec($db,$SqlIOCBramt);
                        if(pg_num_rows($resioccbamt)>0){
                         //   echo "$SqlcbBramt<br>";
                            $resbr = pg_fetch_assoc($resioccbamt, 0);
                           // print_r($resbr);
                            $DPIOCamt=$resbr["amt"];
                            $PVIOCamt= $resbr["pv"];     
                            $SVIOCamt= $resbr["sv"];  
                            $TotalIOCHeader=$resbr["total"];  
                        }else {$DPIOCamt=0;$PVIOCamt=0;$SVIOCamt=0;$TotalIOCHeader=0;}
                        $ressciocamt= pg_exec($db,$SqlIOCSCamt);
                        if(pg_num_rows($ressciocamt)>0){
                        //     echo "$SqlcbSCamt<br>";
                            $resbr = pg_fetch_assoc($ressciocamt, 0);
                          //print_r($resbr);
                            $DPIOCamt +=$resbr["amt"];
                            $PVIOCamt += $resbr["pv"];     
                            $SVIOCamt += $resbr["sv"];   
                            $TotalIOCHeader +=$resbr["total"]; 
                        }
                         $SqlSCIOCDetail = "select count(prdcd) as qty
                                  from mlm.ioc_newmscrtrh a join ioc_newmscrtrd b on a.trcd=b.trcd   where trtype='14'
                                  and trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and a.trcd not like '%****%'   ";
                         
                        $SqlBRIOCDetail = "select count(prdcd) as qty
                                  from mlm.ioc_newsctrh a join ioc_newsctrd b on a.trcd=b.trcd     where trtype='14'
                                  and trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and a.trcd not like '%****%'   ";
                        if($cmb_sts=='1'){
                            $SqlSCIOCDetail .=" and post='1' "    ;
                            $SqlBRIOCDetail .=" and post='1' "    ;
                        }else if($cmb_sts=='-1'){
                            $SqlSCIOCDetail .=" and post='0'  "    ;
                            $SqlBRIOCDetail .=" and post='0'   "    ;
                        }
                  //   echo "$SqlSCIOCDetail";
                        //     echo "$SqlBRIOCDetail";
                        $resbriocdetail= pg_exec($db,$SqlBRIOCDetail);
                        if(pg_num_rows($resbriocdetail)>0){
            // echo "$SqlKITBRamt<br>";
                            $resbr = pg_fetch_assoc($resbriocdetail, 0);
                          //print_r($resbr);
                           
                            $TotalBRIOCDetail= $resbr["qty"];  
                        }else {$TotalBRIOCDetail=0;}
                        $ressciocdetail= pg_exec($db,$SqlSCIOCDetail);
                        if(pg_num_rows($ressciocdetail)>0){
                          // echo "$SqlKITSCamt<br>";
                           //echo "xxx=$PVKITamt";
                            $resbr = pg_fetch_assoc($ressciocdetail, 0);
                          //   print_r($resbr);
                            $TotalBRIOCDetail +=$resbr["qty"];
                        }
                       
                     
                        ?>
                    <tr bgcolor="#ffd7d7">
                        <td>IOC Sales</td>
                        
                        <td align="right"><?=number_format($DPIOCamt,2)?></td>
                        <td align="right"><?=number_format($PVIOCamt,2)?></td>
                        <td align="right"><?=number_format($SVIOCamt,2)?></td>
                        <td align="right">-</td>
                        <td align="right"><?=$TotalIOCHeader?></td>
                        <td align="right"><?=$TotalBRIOCDetail?></td>
                        <td ><input type="checkbox"></td>
                    </tr>
                    <tr bgcolor="#ffd7d7" style="font-weight:bold">
                        <td align="center"><strong>Total</strong></td>
                        <td  align="right"><?=number_format($DPIOCamt,2)?></td>
                        <td align="right"><?=number_format($PVIOCamt,2)?></td>
                        <td  align="right"><?=number_format($SVIOCamt,2)?></td>
                        <td align="right">-</td>
                        <td  align="right"><?=$TotalIOCHeader?></td>
                        <td align="right"><?=$TotalBRIOCDetail?></td>
                         <td ><input type="checkbox"></td>
                    </tr>

               
   <tr bgcolor="#fff">
    <td colspan="8">&nbsp;</td>
                    <tr bgcolor="#e8f2a1" style="font-weight:bold">
                        <TD colspan="2" bgcolor="#e8f2a1" valign="top" >
                            <strong>3. Member Data</strong>
                        </td>
                        <td colspan="6" align="center" bgcolor="#e8f2a1"></td>
                    </tr> 
                    <tr  bgcolor="#e8f2a1" style="font-weight:bold">
                        <td width="20%" align="center" bgcolor="#e8f2a1">Type</td>
                       
                        <td width="10%" align="center" bgcolor="#e8f2a1">Total </td>
                        <td colspan="6">&nbsp;</td>

                    </tr>
                    
                    <tr bgcolor="#e8f2a1">
                        <td width="20%">New Joined Member</td>
                       <?$sqljoinmember="select count(*) as total from mlm.msmemb where code ilike '06%' and  joindt>='$txtTgl1ymd' and joindt<='$txtTgl2ymd'  ;";
                        $resjoinmember= pg_exec($db,$sqljoinmember);
                        if(pg_num_rows($resjoinmember)>0){
                             $resjoinmemberassoc = pg_fetch_assoc($resjoinmember, 0);
                             $joinmember=$resjoinmemberassoc["total"];
                        }else $joinmember="0";
                       
                       ?>
                        <td width="10%" align="right"><?=$joinmember?></td>
                        <td colspan="6"><input type="checkbox"></td> 
                    </tr>
                       <?$sqljoinioc="select count(*) as total from mlm.ioc_msmemb where joindt>='$txtTgl1ymd' and joindt<='$txtTgl2ymd' ;";
                        $resjoinioc= pg_exec($db,$sqljoinioc);
                        if(pg_num_rows($resjoinioc)>0){
                             $resjoiniocassoc = pg_fetch_assoc($resjoinioc, 0);
                             $joinioc=$resjoiniocassoc["total"];
                        }else $joinioc="0";
                       
                       ?>
                    <tr bgcolor="#e8f2a1">
                        <td width="20%">New Joined IOC</td>
                       
                        <td width="10%" align="right"><?=$joinioc?></td>
                          <td colspan="6"><input type="checkbox"></td> 

                    </tr>
                 <tr bgcolor="#e8f2a1" style="font-weight:bold">
                          <td><div align="center"><strong> Total </strong></div></td>
                        
                        <td width="10%"  align="right" ><?=($joinioc+$joinmember)?></td>
                        <td colspan="6"><input type="checkbox"></td> 

                    </tr>

 
        <? }else{
            $SqlloccdBR="select a.loccd,b.name from mlm.newmstrh a join mlm.msms b on a.loccd=b.code where trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and (a.trtype='1' or a.trtype='5')  ";
                        if($cmb_sts=='1'){
                            $SqlloccdBR .=" and post='1' "    ;
                           
                        }else if($cmb_sts=='-1'){
                            $SqlloccdBR .=" and post='0'  "    ;
                            
                        }
                        $SqlloccdBR .="  and a.trcd not like '%****%' group by a.loccd,b.name ";
                        
              $SqlloccdBR1="select a.loccd,b.name from mlm.ioc_newmscrtrh a join mlm.msms b on a.loccd=b.code where trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and (a.trtype='14')  ";
                        if($cmb_sts=='1'){
                            $SqlloccdBR1 .=" and post='1' "    ;
                           
                        }else if($cmb_sts=='-1'){
                            $SqlloccdBR1 .=" and post='0'  "    ;
                            
                        }
                        $SqlloccdBR1 .="  and a.trcd not like '%****%' group by a.loccd,b.name ";           
                $SqlloccdBR3 ="select a.loccd ,c.name
                                      from mlm.ioc_newmscrtrh a join mlm.ioc_msmemb b on a.icode=b.icode join mlm.msms c on a.loccd=c.code
                                      where icountry = 'PH' and joindt>='$txtTgl1ymd'  and joindt<'$txtTgl2ymd' group by a.loccd,c.name";
                  $SqlloccdBR4="select b.loccd,b.name from mlm.newtrh a join mlm.msms b on a.loccd=b.bc_id where trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and (a.trtype='5')  and a.trcd ilike '%KT%'   ";
                         if($cmb_sts=='1'){
                            $SqlloccdBR4 .=" and a.status='1' "    ;
                           
                        }else if($cmb_sts=='-1'){
                            $SqlloccdBR4 .=" and a.status='-'  "    ;
                            
                        }          
                $SqlloccdBRx="select * from ($SqlloccdBR1 union $SqlloccdBR union $SqlloccdBR3 union $SqlloccdBR4 )x order by loccd "    ;
                 //  echo "$SqlloccdBRx";
                      $resloccdBR= pg_exec($db,$SqlloccdBRx);
                        if(pg_num_rows($resloccdBR)>0){
                           for ($ls = 0; $ls < pg_num_rows($resloccdBR); $ls++) {
                                $row1 = pg_fetch_assoc($resloccdBR, $ls);
                                $rowcode=$row1["loccd"];

                         
            ?>
                    <TR bgcolor="#FFFFFF" style="font-weight:bold;"> 
                        <TD colspan="8" valign="top" >
                            <div style="float:left;width:50%;"><b>Branch : <?=$row1["loccd"]?> - <?=$row1["name"]?></b></div>
                            <div style="float: right; text-align: right; width: 50%;"><b>Period : <?=$txtTgl1?> - <?= $txtTgl2?></b></div>
                        </td>
                    </tr>
                    
                    <TR bgcolor="#dee7e5"> 
                        <TD colspan="8" valign="top" >
                            <b>1. SIMP Sales Data</b>
                        </td>
                    </tr> 
                    <tr bgcolor="#dee7e5" style="font-weight:bold">
                        <td width="20%"  align="center">Type</td>
                        <td width="13%" align="center">Amount (PHP)</td>
                        <td width="13%" align="center">PV</td>
                        <td width="13%" align="center">SV (PHP)</td>
                        <td width="13%" align="center">MPV</td>
                        <td width="13%" align="center">Total Record(Header)</td>
                        <td width="13%" align="center">Total Record(Detail)</td>
                        <td>&nbsp;</td>
                    </tr>
             <?  if($vRowSetup['cashbill'] =='t') {	?>
                    <tr bgcolor="#dee7e5">
                        <td>Cash Bill</td>
                        <?
                        $txtTgl1ymd=implode("-", array_reverse(explode("/", $txtTgl1)));
                        $txtTgl2ymd=implode("-", array_reverse(explode("/", $txtTgl2)));
                        $SqlcbBramt="select sum(ndp) as amt, sum(npv) as pv , sum(nbv)as sv,count(trcd) as total from mlm.newmstrh where trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and loccd='$rowcode' and  trtype='1'";
                        if($cmb_sts=='1'){
                            $SqlcbBramt .=" and post='1' "    ;
                           
                        }else if($cmb_sts=='-1'){
                            $SqlcbBramt .=" and post='0'  "    ;
                            
                        }
                        $SqlcbBramt .="  and trcd not like '%****%'"    ;
                      
                        $resbrcbamt= pg_exec($db,$SqlcbBramt);
                        if(pg_num_rows($resbrcbamt)>0){
                         //   echo "$SqlcbBramt<br>";
                            $resbr = pg_fetch_assoc($resbrcbamt, 0);
                            //print_r($resbr);
                            $DPCBamt=$resbr["amt"];
                            $PVCBamt= $resbr["pv"];     
                            $SVCBamt= $resbr["sv"];  
                            $TotalBrHeader=$resbr["total"];  
                        }else {$DPCBamt=0;$PVCBamt=0;$SVCBamt=0;$TotalBrHeader=0;}
                        
                         $SqlBRDetail = "select count(prdcd) as qty
                                  from newmstrh a join newmstrd b on a.trcd=b.trcd     where trtype='1'
                                  and trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and a.trcd not like '%****%'  and loccd='$rowcode' ";
                        if($cmb_sts=='1'){
                            $SqlBRDetail .=" and post='1' "    ;
                        }else if($cmb_sts=='-1'){
                            $SqlBRDetail .=" and post='0'   "    ;
                        }
                   //     echo "$SqlBRDetail<br>";
                        $resbrdetail= pg_exec($db,$SqlBRDetail);
                        if(pg_num_rows($resbrdetail)>0){
        // echo "$SqlKITBRamt<br>";
                            $resbr = pg_fetch_assoc($resbrdetail, 0);
                          //  print_r($resbr);
                           
                            $TotalBRDetail= $resbr["qty"];  
                          //  echo "$TotalBRDetail<br>";
                        }else {$TotalBRDetail=0;}
                       
                       
                        
                        
                        ?>
                        <td align="right"><?=number_format(($DPCBamt),2)?></td>
                        <td align="right"><?=number_format(($PVCBamt),2)?></td>
                        <td align="right"><?=number_format(($SVCBamt),2)?></td>
                        <td align="right">-</td>
                        <td align="right"><?=$TotalBrHeader?></td>
                        <td align="right"><?=$TotalBRDetail?></td>
                        <td><input type="checkbox"></td>
                    </tr>
             <?}?>
             <?  if($vRowSetup['ioc'] =='t') {	?>
                    <tr bgcolor="#dee7e5">
                        <td>IOC Promotion PV</td>
                        <?
                        $SqliocBrpromoamt="select sum(tdp) as amt, sum(tpv) as pv , sum(tbv)as sv,count(trcd) as total from mlm.ioc_newmscrtrh where trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and  trtype='14'  and loccd='$rowcode'   ";
                        $Sqliocscpromoamt="select sum(tdp) as amt, sum(tpv) as pv , sum(tbv)as sv,count(trcd) as total from mlm.ioc_newsctrh where trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and  trtype='14'  and loccd='$rowcode'  ";
                        if($cmb_sts=='1'){
                            $SqliocBrpromoamt .=" and post='1' "    ;
                            $Sqliocscpromoamt .=" and post='1' "    ;
                        }else if($cmb_sts=='-1'){
                            $SqliocBrpromoamt .=" and post='0'  "    ;
                            $Sqliocscpromoamt .=" and post='0'   "    ;
                        }
                        $SqliocBrpromoamt .="  and trcd not  like '%****%'"    ;
                        $Sqliocscpromoamt .=" and trcd not  like '%****%' "    ;
                         
                        $resbrcbamt= pg_exec($db,$SqliocBrpromoamt);
                        if(pg_num_rows($resbrcbamt)>0){
                        // echo "$SqliocBrpromoamt<br>";
                            $resbr = pg_fetch_assoc($resbrcbamt, 0);
                            // print_r($resbr);
                            $DPPromoamt=$resbr["amt"];
                            $PVPromoamt= $resbr["pv"];     
                            $SVPromoamt= $resbr["sv"];  
                            $TotalPromoHeader=$resbr["total"];  
                        }else {$DPPromoamt=0;$PVPromoamt=0;$SVPromoamt=0;$TotalPromoHeader=0;}
                        /*$ressccbamt= pg_exec($db,$Sqliocscpromoamt);
                        if(pg_num_rows($ressccbamt)>0){
                        //     echo "$Sqliocscpromoamt<br>";
                            $resbr = pg_fetch_assoc($ressccbamt, 0);
                            print_r($resbr);
                            $DPPromoamt +=$resbr["amt"];
                            $PVPromoamt += $resbr["pv"];     
                            $SVPromoamt += $resbr["sv"];   
                            $TotalPromoHeader +=$resbr["total"]; 
                        }*/
                         $SqlSCDetail = "select count(b.prdcd) as qty
                                  from mlm.ioc_newmscrtrh a join mlm.ioc_newmscrtrd b on a.trcd=b.trcd   where trtype='14'
                                  and trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and a.trcd not  like '%****%'   ";
                        $SqlBRDetail = "select count(b.prdcd) as qty
                                  from mlm.ioc_newmscrtrh a join mlm.ioc_newmscrtrd b on a.trcd=b.trcd     where trtype='14'
                                  and trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and a.trcd not  like '%****%'  and loccd='$rowcode'";
                        if($cmb_sts=='1'){
                            $SqlSCDetail .=" and post='1' "    ;
                            $SqlBRDetail .=" and post='1' "    ;
                        }else if($cmb_sts=='-1'){
                            $SqlSCDetail .=" and post='0'  "    ;
                            $SqlBRDetail .=" and post='0'   "    ;
                        }
                        //echo "$SqlBRDetail<br>";
                        $resbrdetail= pg_exec($db,$SqlBRDetail);
                        if(pg_num_rows($resbrdetail)>0){
                        // echo "$SqlKITBRamt<br>";
                            $resbr = pg_fetch_assoc($resbrdetail, 0);
                          //  print_r($resbr);
                           
                            $TotalPromoDetail= $resbr["qty"];  
                        }else {$TotalBRDetail=0;}
                        /* $resscdetail= pg_exec($db,$SqlSCDetail);
                        if(pg_num_rows($resscdetail)>0){
                          // echo "$SqlKITSCamt<br>";
                           //echo "xxx=$PVKITamt";
                            $resbr = pg_fetch_assoc($resscdetail, 0);
                           //  print_r($resbr);
                           
                            $TotalPromoDetail+= $resbr["qty"];   
                        }*/
                       
                        $MVPromoamt=$PVPromoamt;
                        ?>
                        <td  align="right"><?=number_format(($DPPromoamt),2)?></td>
                       <td align="right">-</td>
                       <td align="right">-</td>
                       <td   align="right"><?=number_format(($MVPromoamt),2)?></td>
                       <td   align="right"><?=$TotalPromoHeader?></td>
                        <td   align="right"><?=$TotalPromoDetail?></td>
                        <td><input type="checkbox"></td>
                    </tr>
             <?}?>
             <?  if($vRowSetup['kittrx'] =='t') {	?>
                      <tr bgcolor="#dee7e5">
                   <?
                     $SqlcbBramt="select sum(ndp) as amt, sum(npv) as pv , sum(nbv)as sv,count(trcd) as total from mlm.newmstrh where trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and  trtype='5' and loccd='$rowcode' ";
                        $SqlcbSCamt="select sum(ndp) as amt, sum(npv) as pv , sum(nbv)as sv,count(trcd) as total from mlm.newsctrh where trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and  trtype='5' and loccd='$rowcode' ";
                        $SqlcbTRHamt="select sum(ndp) as amt, sum(npv) as pv , sum(nbv)as sv,count(trcd) as total from mlm.newtrh where trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and  trtype='5'  and loccd='".substr($rowcode, -3)."' and  trcd ilike '%KT%' ";
                        if($cmb_sts=='1'){
                            $SqlcbBramt .=" and post='1' "    ;
                            $SqlcbSCamt .=" and post='1' "    ;
                           //$SqlcbTRHamt.=" and status='1' "    ;
                        }else if($cmb_sts=='-1'){
                            $SqlcbBramt .=" and post='0'  "    ;
                            $SqlcbSCamt .=" and post='0'   "    ;
                            $SqlcbTRHamt.=" and status='-' "    ;
                        }
                        $SqlcbBramt .="  and trcd not like '%****%'"    ;
                        $SqlcbSCamt .=" and trcd not like '%****%' "    ;
                        $SqlcbTRHamt.="and trcd not like '%****%' "    ;
                        $resbrcbamt= pg_exec($db,$SqlcbBramt);
                        if(pg_num_rows($resbrcbamt)>0){
                         //   echo "$SqlcbBramt<br>";
                            $resbr = pg_fetch_assoc($resbrcbamt, 0);
                            //print_r($resbr);
                            $DPKITamt=$resbr["amt"];
                            $PVKITamt= $resbr["pv"];     
                            $SVKITamt= $resbr["sv"];  
                            $KITTotalHead=$resbr["total"];  
                        }else {$DPKITamt=0;$PVCBamt=0;$SVCBamt=0;$TotalBrHeader=0;}
                       /* $ressccbamt= pg_exec($db,$SqlcbSCamt);
                        if(pg_num_rows($ressccbamt)>0){
                        //     echo "$SqlcbSCamt<br>";
                            $resbr = pg_fetch_assoc($ressccbamt, 0);
                            // print_r($resbr);
                            $DPKITamt +=$resbr["amt"];
                            $PVKITamt += $resbr["pv"];     
                            $SVKITamt += $resbr["sv"];   
                            $KITTotalHead +=$resbr["total"]; 
                        }*/
                      
                     if($cmb_sts=='1'|| $cmb_sts==''){
                        // echo "$SqlcbTRHamt<br>";
                            $rescbtrhamt= pg_exec($db,$SqlcbTRHamt);
                            if(pg_num_rows($rescbtrhamt)>0){
                            //     echo "$SqlcbSCamt<br>";
                                $resbr = pg_fetch_assoc($rescbtrhamt, 0);
                                // print_r($resbr);
                                $DPKITamt +=$resbr["amt"];
                                $PVKITamt += $resbr["pv"];     
                                $SVKITamt += $resbr["sv"];   
                                $KITTotalHead +=$resbr["total"]; 
                            }
                              
                          }
                         $SqlSCDetail = "select count(prdcd) as qty
                                  from mlm.newsctrh a join mlm.newsctrd b on a.trcd=b.trcd   where  trtype='5'
                                  and trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and a.trcd not like '%****%' and loccd='$rowcode'   ";
                        $SqlKITBRamt = "select count(prdcd) as qty
                                  from mlm.newmstrh a join mlm.newmstrd b on a.trcd=b.trcd     where trtype='5'
                                  and trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and a.trcd not like '%****%' and loccd='$rowcode'   ";
                        $SqlBRtrhDetail = "select count(prdcd) as qty
                                  from mlm.newtrh a join mlm.newtrd b on a.trcd=b.trcd     where trtype='5'
                                  and trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and a.trcd not like '%****%' and loccd='".substr($rowcode, -3)."'  and  a. trcd ilike '%KT%' ";
                        if($cmb_sts=='1'){
                            $SqlSCDetail .=" and post='1' "    ;
                            $SqlKITBRamt .=" and post='1' "    ;
                          //  $SqlBRtrhDetail.=" and post='1' "    ;
                        }else if($cmb_sts=='-1'){
                            $SqlSCDetail .=" and post='0'  "    ;
                            $SqlKITBRamt .=" and post='0'   "    ;
                          //  $SqlBRtrhDetail.=" and post='0' "    ;
                        }
                        $resbrdetail1= pg_exec($db,$SqlKITBRamt);
                     if(pg_num_rows($resbrdetail)>0){
         //echo "$SqlKITBRamt<br>";
                            $resbr = pg_fetch_assoc($resbrdetail1, 0);
                          //  print_r($resbr);
                           
                            $TotalKITDetail= $resbr["qty"];  
                        }else {$TotalKITDetail=0;}
                      /*  $resscdetail= pg_exec($db,$SqlSCDetail);
                        if(pg_num_rows($resscdetail)>0){
                          // echo "$SqlKITSCamt<br>";
                           //echo "xxx=$PVKITamt";
                            $resbr = pg_fetch_assoc($resscdetail, 0);
                           //  print_r($resbr);
                           
                            $TotalKITDetail+= $resbr["qty"];   
                        }*/
                       if($cmb_sts=='1'|| $cmb_sts==''){
                            $restrhdetail= pg_exec($db,$SqlBRtrhDetail);
                            if(pg_num_rows($restrhdetail)>0){
                              // echo "$SqlKITSCamt<br>";
                               //echo "xxx=$PVKITamt";
                                $resbr = pg_fetch_assoc($restrhdetail, 0);
                               //  print_r($resbr);

                                $TotalKITDetail+= $resbr["qty"];   
                            }
                        }
                         $sqlKITMPV="select sum(ipv)::numeric as tot_ipv from mlm.newtrh 
                             where trdt >= '$txtTgl1ymd' and trdt <= '$txtTgl2ymd' and (loccd='$rowcode' or loccd='".substr($rowcode, -3)."' )
                              and wv > 0 and ipv > 0";
                 //  echo "$sqlKITMPV";
                           if($cmb_sts=='1'|| $cmb_sts==''){
                        $resKitMpv= pg_exec($db,$sqlKITMPV);
                         if(pg_num_rows($resKitMpv)>0){
                              $resbr = pg_fetch_assoc($resKitMpv, 0);
                             
                              $TotalKITMPv= $resbr["tot_ipv"]; 
                         }else $TotalKITMPv=0;
                           }
                     ?>   
                      <td>KIT Transaction</td>
                        <td  align="right"><?=number_format($DPKITamt,2)?></td>
                        <td  align="right"><?=number_format($PVKITamt,2)?></td>
                        <td  align="right"><?=number_format($SVKITamt,2)?></td>
                        <td  align="right"><?=number_format($TotalKITMPv,2)?></td>
                        <td  align="right"><?=$KITTotalHead?></td>
                        <td  align="right"><?=$TotalKITDetail?></td>
                        <td><input type="checkbox"></td>
                    </tr>
             <?}?>
                     <tr  bgcolor="#dee7e5" style="font-weight:bold">
                       <td><div align="center"><strong> Total </strong></div></td>
                         <td  align="right"><?=number_format(($DPCBamt+$DPKITamt+$DPPromoamt),2)?></td>
                        <td align="right"><?=number_format(($PVCBamt+$PVKITamt),2)?></td>
                        <td  align="right"><?=number_format(($SVCBamt+$SVKITamt),2)?></td>
                       
                         <td  align="right"><?=number_format(($TotalKITMPv+$MVPromoamt),2)?></td>
                        <td  align="right"><?=$TotalBrHeader+$KITTotalHead+$TotalPromoHeader?></td>
                        <td  align="right"><?=($TotalBRDetail+$TotalKITDetail+$TotalPromoDetail)?></td>
                        <td><input type="checkbox"></td>
                    </tr>
               <tr bgcolor="#fff">
                   <td colspan="8">&nbsp;</td></tr>
     <TR  bgcolor="#ffd7d7" style="font-weight:bold;">
                        <TD colspan="8" valign="top" >
                            <b>2. IOC Sales Data</b>
                        </td>
                    </tr> 
                    <tr bgcolor="#ffd7d7" style="font-weight:bold">
                       <td width="20%" align="center">Type</td>
                        <td width="13%" align="center">Amount (PHP)</td>
                        <td width="13%" align="center">PV</td>
                        <td width="13%" align="center">SV (PHP)</td>
                        <td width="13%" align="center">MPV</td>
                        <td width="13%" align="center">Total Record(Header)</td>
                        <td width="13%" align="center">Total Record(Detail)</td>
<td  ></td>

                    </tr>
                    <?
                        $txtTgl1ymd=implode("-", array_reverse(explode("/", $txtTgl1)));
                        $txtTgl2ymd=implode("-", array_reverse(explode("/", $txtTgl2)));
                        $SqlIOCBramt="select sum(tdp) as amt, sum(tpv) as pv , sum(tbv)as sv,count(trcd) as total from mlm.ioc_newmscrtrh a where trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and  trtype='14' and loccd='$rowcode'";
                        if($cmb_sts=='1'){
                            $SqlIOCBramt .=" and post='1' "    ;
                         
                        }else if($cmb_sts=='-1'){
                            $SqlIOCBramt .=" and post='0'  "    ;
                       
                        }
                        // echo "$SqlIOCBramt<br>";
                        $SqlIOCBramt .="  and a.trcd not like '%****%'"    ;
                      
                        $resioccbamt= pg_exec($db,$SqlIOCBramt);
                        if(pg_num_rows($resioccbamt)>0){
                         //   echo "$SqlcbBramt<br>";
                            $resbr = pg_fetch_assoc($resioccbamt, 0);
                            //print_r($resbr);
                            $DPIOCamt=$resbr["amt"];
                            $PVIOCamt= $resbr["pv"];     
                            $SVIOCamt= $resbr["sv"];  
                            $TotalIOCHeader=$resbr["total"];  
                        }else {$DPIOCamt=0;$PVIOCamt=0;$SVIOCamt=0;$TotalIOCHeader=0;}
                        
                         
                        $SqlBRIOCDetail = "select count(prdcd) as qty
                                  from mlm.ioc_newmscrtrh a join mlm.ioc_newmscrtrd b on a.trcd=b.trcd     where trtype='14'
                                  and trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and a.trcd not like '%****%' and loccd='$rowcode'  ";
                        if($cmb_sts=='1'){
                            $SqlBRIOCDetail .=" and post='1' "    ;
                            
                        }else if($cmb_sts=='-1'){
                            $SqlBRIOCDetail .=" and post='0'  "    ;
                            
                        }
                        $resbriocdetail= pg_exec($db,$SqlBRIOCDetail);
                        if(pg_num_rows($resbriocdetail)>0){
            // echo "$SqlKITBRamt<br>";
                            $resbr = pg_fetch_assoc($resbriocdetail, 0);
                          //  print_r($resbr);
                           
                            $TotalBRIOCDetail= $resbr["qty"];  
                        }else {$TotalBRIOCDetail=0;}
                        
                       
                      
                        ?>
              <tr bgcolor="#ffd7d7">
                        <td>IOC Sales</td>
                        
                        <td align="right"><?=number_format($DPIOCamt,2)?></td>
                        <td align="right"><?=number_format($PVIOCamt,2)?></td>
                        <td align="right"><?=number_format($SVIOCamt,2)?></td>
                        <td align="right">-</td>
                        <td  align="right"><?=$TotalIOCHeader?></td>
                        <td  align="right"><?=$TotalBRIOCDetail?></td>
                        <td  ><input type="checkbox"></td>
                    </tr>
                    <tr bgcolor="#ffd7d7" style="font-weight:bold">
                         <td><div align="center"><strong> Total </strong></div></td>
                        <td align="right"><?=number_format($DPIOCamt,2)?></td>
                        <td  align="right"><?=number_format($PVIOCamt,2)?></td>
                        <td align="right"><?=number_format($SVIOCamt,2)?></td>
                        <td align="right">-</td>
                        <td  align="right"><?=$TotalIOCHeader?></td>
                        <td  align="right"><?=$TotalBRIOCDetail?></td>
                         <td  ><input type="checkbox"></td>
                    </tr>

                <tr bgcolor="#fff">
    <td colspan="8">&nbsp;</td>
  </tr>
  
                     <tr bgcolor="#e8f2a1" style="font-weight:bold">
                        <TD colspan="2" bgcolor="#e8f2a1" valign="top" >
                            <strong>3. Member Data</strong>
                        </td>
                        <td colspan="6" align="center" bgcolor="#e8f2a1"></td>
                    </tr> 
                    <tr  bgcolor="#e8f2a1" style="font-weight:bold">
                        <td width="20%" align="center" bgcolor="#e8f2a1">Type</td>
                       
                        <td width="10%" align="center" bgcolor="#e8f2a1">Total </td>
                        <td colspan="6">&nbsp;</td>

                    </tr>
                    
                    <tr bgcolor="#e8f2a1">
                        <td width="20%">New Joined Member</td>
                       <?$sqljoinmember="select count(*) as total from mlm.msmemb where code ilike '06%' and joindt>='$txtTgl1ymd' and joindt<='$txtTgl2ymd' and loccd='$rowcode'  ;";
                        $resjoinmember= pg_exec($db,$sqljoinmember);
                        if(pg_num_rows($resjoinmember)>0){
                             $resjoinmemberassoc = pg_fetch_assoc($resjoinmember, 0);
                             $joinmember=$resjoinmemberassoc["total"];
                        }else $joinmember="0";
                       
                       ?>
                        <td width="10%" align="right"><?=$joinmember?></td>
                         <td  colspan="6"><input type="checkbox"></td>
                    </tr>
                       <?$sqljoinioc="select count(a.icode) as total  
                               from mlm.ioc_newmscrtrh a join mlm.ioc_msmemb b on a.icode=b.icode 
                               where icountry = 'PH' and joindt>='$txtTgl1ymd' and joindt<='$txtTgl2ymd' and loccd='$rowcode' ;";
                     //  echo "$sqljoinioc<br>";
                       $resjoinioc= pg_exec($db,$sqljoinioc);
                        if(pg_num_rows($resjoinioc)>0){
                             $resjoiniocassoc = pg_fetch_assoc($resjoinioc, 0);
                             $joinioc=$resjoiniocassoc["total"];
                        }else $joinioc="0";
                       
                       ?>
                    <tr bgcolor="#e8f2a1">
                        <td width="20%">New Joined IOC</td>
                       
                        <td width="10%" align="right"><?=$joinioc?></td>
                        <td colspan="6"><input type="checkbox"></td>
                    </tr>
                    <tr bgcolor="#e8f2a1" style="font-weight:bold">
                         <td><div align="right"><strong> Total </strong></div></td>
                        
                        <td width="10%"align="right"><?=($joinioc+$joinmember)?></td>
                        <td colspan="6"><input type="checkbox"></td>
                    </tr>
  <tr bgcolor="#fff">
    <td colspan="8">&nbsp;</td>
  </tr>
                
        <?                  }
        
                        }
        
                   $SqlloccdBR="select loccd from mlm.newsctrh a  where trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' ";
                   $SqlloccdBR2="select loccd from mlm.newtrh a  where trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' ";
            $SqlloccdBR1="select sccode as loccd from mlm.ioc_newsctrh a  where trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and  trtype='12' ";
                        if($cmb_sts=='1'){
                            $SqlloccdBR .=" and post='1' "    ;
                            $SqlloccdBR1 .=" and post='1' "    ;
                           
                        }else if($cmb_sts=='-1'){
                            $SqlloccdBR .=" and post='0'  "    ;
                            $SqlloccdBR1 .=" and post='1' "    ;
                            
                        }
                        $SqlloccdBR .="  and trcd not like '%****%' and loccd not like 'S%' group by loccd  "    ;
                        $SqlloccdBR1 .="  and trcd not like '%****%' and sccode not like 'S%' group by loccd  "    ;
                        $SqlloccdBR2="";
                          $SqlloccdBR3 ="select a.sccode as loccd 
                                      from mlm.ioc_newsctrh a join mlm.ioc_msmemb b on a.icode=b.icode join mlm.msms c on a.sccode=c.code
                                      where icountry = 'PH' and joindt>='$txtTgl1ymd'  and joindt<'$txtTgl2ymd' group by a.sccode ";
                        $SqlloccdBRx="select loccd from ($SqlloccdBR union $SqlloccdBR1 union $SqlloccdBR3 )x group by  loccd order by loccd";
              // echo  "$SqlloccdBRx";
                      $resloccdBR= pg_exec($db,$SqlloccdBRx);
                        if(pg_num_rows($resloccdBR)>0){
                           for ($ls = 0; $ls < pg_num_rows($resloccdBR); $ls++) {
                                $row1 = pg_fetch_assoc($resloccdBR, $ls);
                                $rowcode=$row1["loccd"];

                         
            ?>
                    <TR bgcolor="#FFFFFF" style="font-weight:bold;"> 
                        <TD colspan="8" valign="top" >
                            <div style="float:left;width:50%;"><b>Service Center : <?=$row1["loccd"]?> - <?=getMemberName($row1["loccd"])?></b></div>
                            <div style="float: right; text-align: right; width: 50%;"><b>Period : <?=$txtTgl1?> - <?= $txtTgl2?></b></div>
                        </td>
                    </tr>
                    
                    <TR bgcolor="#dee7e5"> 
                        <TD colspan="8" valign="top" >
                            <b>1. SIMP Sales Data</b>
                        </td>
                    </tr> 
                    <tr bgcolor="#dee7e5" style="font-weight:bold">
                        <td width="20%"  align="center">Type</td>
                        <td width="13%" align="center">Amount (PHP)</td>
                        <td width="13%" align="center">PV</td>
                        <td width="13%" align="center">SV (PHP)</td>
                        <td width="13%" align="center">MPV</td>
                        <td width="13%" align="center">Total Record(Header)</td>
                        <td width="13%" align="center">Total Record(Detail)</td>
                        <td colspan="6">&nbsp;</td>
                    </tr>
     <?  if($vRowSetup['cashbill'] =='t') {	?>
                    <tr bgcolor="#dee7e5">          
                    
                        <td>Cash Bill</td>
                        <?
                        $txtTgl1ymd=implode("-", array_reverse(explode("/", $txtTgl1)));
                        $txtTgl2ymd=implode("-", array_reverse(explode("/", $txtTgl2)));
                        $SqlcbBramt="select sum(ndp) as amt, sum(npv) as pv , sum(nbv)as sv,count(trcd) as total from mlm.newsctrh where trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and loccd='$rowcode' and  trtype='1'";
                        if($cmb_sts=='1'){
                            $SqlcbBramt .=" and post='1' "    ;
                           
                        }else if($cmb_sts=='-1'){
                            $SqlcbBramt .=" and post='0'  "    ;
                            
                        }
                        $SqlcbBramt .="  and trcd not like '%****%'"    ;
                      
                        $resbrcbamt= pg_exec($db,$SqlcbBramt);
                        if(pg_num_rows($resbrcbamt)>0){
                         //   echo "$SqlcbBramt<br>";
                            $resbr = pg_fetch_assoc($resbrcbamt, 0);
                            //print_r($resbr);
                            $DPCBamt=$resbr["amt"];
                            $PVCBamt= $resbr["pv"];     
                            $SVCBamt= $resbr["sv"];  
                            $TotalBrHeader=$resbr["total"];  
                        }else {$DPCBamt=0;$PVCBamt=0;$SVCBamt=0;$TotalBrHeader=0;}
                        
                         $SqlBRDetail = "select count(prdcd) as qty
                                  from mlm.newsctrh a join mlm.newsctrd b on a.trcd=b.trcd     where trtype='1'
                                  and trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and a.trcd not like '%****%'   and loccd='$rowcode' ";
                        if($cmb_sts=='1'){
                            $SqlBRDetail .=" and post='1' "    ;
                        }else if($cmb_sts=='-1'){
                            $SqlBRDetail .=" and post='0'   "    ;
                        }
                        $resbrdetail= pg_exec($db,$SqlBRDetail);
                        if(pg_num_rows($resbrdetail)>0){
        // echo "$SqlKITBRamt<br>";
                            $resbr = pg_fetch_assoc($resbrdetail, 0);
                          //  print_r($resbr);
                           
                            $TotalBRDetail= $resbr["qty"];  
                          //  echo "$TotalBRDetail<br>";
                        }else {$TotalBRDetail=0;}
                       
                       
                        
                        ?>
                        <td align="right"><?=number_format(($DPCBamt),2)?></td>
                        <td align="right"><?=number_format(($PVCBamt),2)?></td>
                        <td align="right"><?=number_format(($SVCBamt),2)?></td>
                        <td align="right">-</td>
                        <td align="right"><?=$TotalBrHeader?></td>
                        <td  align="right"><?=$TotalBRDetail?></td>
                        <td ><input type="checkbox"></td>
                    </tr>
     <?}?>
     <?  if($vRowSetup['ioc'] =='t') {	?>
                     <tr bgcolor="#dee7e5">
                        <td>IOC Promotion PV</td>
                        <?
                     $SqliocBrpromoamt="select sum(tdp) as amt, sum(tpv) as pv , sum(tbv)as sv,count(trcd) as total from mlm. ioc_newsctrh  where trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and  trtype='14'  and loccd='$rowcode'   ";
                        $Sqliocscpromoamt="select sum(tdp) as amt, sum(tpv) as pv , sum(tbv)as sv,count(trcd) as total from mlm.ioc_newsctrh where trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and  trtype='14'  and sccode='$rowcode'  ";
                        if($cmb_sts=='1'){
                            $SqliocBrpromoamt .=" and post='1' "    ;
                            $Sqliocscpromoamt .=" and post='1' "    ;
                        }else if($cmb_sts=='-1'){
                            $SqliocBrpromoamt .=" and post='0'  "    ;
                            $Sqliocscpromoamt .=" and post='0'   "    ;
                        }
                        $SqliocBrpromoamt .="  and trcd not  like '%****%'"    ;
                        $Sqliocscpromoamt .=" and trcd not  like '%****%' "    ;
                         
                        
                        $DPPromoamt=0;$PVPromoamt=0;$SVPromoamt=0;$TotalPromoHeader=0;
                      //   echo "$Sqliocscpromoamt<br>";
                       $ressccbamt= pg_exec($db,$Sqliocscpromoamt);
                        if(pg_num_rows($ressccbamt)>0){
                        //     echo "$Sqliocscpromoamt<br>";
                            $resbr = pg_fetch_assoc($ressccbamt, 0);
                     //    print_r($resbr);
                            $DPPromoamt +=$resbr["amt"];
                            $PVPromoamt += $resbr["pv"];     
                            $SVPromoamt += $resbr["sv"];   
                            $TotalPromoHeader +=$resbr["total"]; 
                        }
                         $SqlSCDetail = "select count(b.prdcd) as qty
                                  from mlm.ioc_newmscrtrh a join mlm.ioc_newmscrtrd b on a.trcd=b.trcd   where trtype='14'
                                  and trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and a.trcd not  like '%****%'  and sccode='$rowcode'  ";
                        $SqlBRDetail = "select count(b.prdcd) as qty
                                  from mlm.ioc_newsctrh a join mlm.ioc_newsctrd b on a.trcd=b.trcd     where trtype='14'
                                  and trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and a.trcd not  like '%****%'  and sccode='$rowcode'   ";
                        if($cmb_sts=='1'){
                            $SqlSCDetail .=" and post='1' "    ;
                            $SqlBRDetail .=" and post='1' "    ;
                        }else if($cmb_sts=='-1'){
                            $SqlSCDetail .=" and post='0'  "    ;
                            $SqlBRDetail .=" and post='0'   "    ;
                        }
                       
                       $TotalPromoDetail=0;
                       // echo "$SqlSCDetail<br>";
                        $resscdetail= pg_exec($db,$SqlBRDetail);
                        if(pg_num_rows($resscdetail)>0){
                          // echo "$SqlKITSCamt<br>";
                           //echo "xxx=$PVKITamt";
                            $resbr = pg_fetch_assoc($resscdetail, 0);
                           //  print_r($resbr);
                           
                            $TotalPromoDetail+= $resbr["qty"];   
                        }
                       
                       $MVPromoamt=$PVPromoamt;
                        ?>
                        <td  align="right"><?=number_format(($DPPromoamt),2)?></td>
                        <td align="right">-</td>
                        <td align="right">-</td>
                       <td  align="right"><?=number_format(($MVPromoamt),2)?></td>
                       <td align="right"><?=$TotalPromoHeader?></td>
                        <td align="right"><?=$TotalPromoDetail?></td>
                          <td ><input type="checkbox"></td>
                    </tr>
     <?}?>
     <?  if($vRowSetup['kittrx'] =='t') {	?>
                      <tr bgcolor="#dee7e5">
                    <?
                      $SqlcbBramt="select sum(ndp) as amt, sum(npv) as pv , sum(nbv)as sv,count(trcd) as total from mlm.newmstrh where trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and  trtype='5' and loccd='$rowcode' ";
                        $SqlcbSCamt="select sum(ndp) as amt, sum(npv) as pv , sum(nbv)as sv,count(trcd) as total from mlm.newsctrh where trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and  trtype='5' and loccd='$rowcode' ";
                        $SqlcbTRHamt="select sum(ndp) as amt, sum(npv) as pv , sum(nbv)as sv,count(trcd) as total from mlm.newtrh where trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and  trtype='5'  and loccd='".substr($rowcode, -3)."'  and trcd ilike '%KT%'  ";
                        if($cmb_sts=='1'){
                            $SqlcbBramt .=" and post='1' "    ;
                            $SqlcbSCamt .=" and post='1' "    ;
                          //  $SqlcbTRHamt.=" and post='1' "    ;
                        }else if($cmb_sts=='-1'){
                            $SqlcbBramt .=" and post='0'  "    ;
                            $SqlcbSCamt .=" and post='0'   "    ;
                          //  $SqlcbTRHamt.=" and post='1' "    ;
                        }
                        $SqlcbBramt .="  and trcd not like '%****%'"    ;
                        $SqlcbSCamt .=" and trcd not like '%****%' "    ;
                        $SqlcbTRHamt.="and trcd not like '%****%' "    ;
                       /* $resbrcbamt= pg_exec($db,$SqlcbBramt);
                        if(pg_num_rows($resbrcbamt)>0){
                         //   echo "$SqlcbBramt<br>";
                            $resbr = pg_fetch_assoc($resbrcbamt, 0);
                            //print_r($resbr);
                            $DPKITamt=$resbr["amt"];
                            $PVKITamt= $resbr["pv"];     
                            $SVKITamt= $resbr["sv"];  
                            $KITTotalHead=$resbr["total"];  
                        }else {*/$DPKITamt=0;$PVKITamt=0;$SVKITamt=0;$KITTotalHead=0;//}
                        $ressccbamt= pg_exec($db,$SqlcbSCamt);
                        if(pg_num_rows($ressccbamt)>0){
                        //     echo "$SqlcbSCamt<br>";
                            $resbr = pg_fetch_assoc($ressccbamt, 0);
                            // print_r($resbr);
                            $DPKITamt +=$resbr["amt"];
                            $PVKITamt += $resbr["pv"];     
                            $SVKITamt += $resbr["sv"];   
                            $KITTotalHead +=$resbr["total"]; 
                        }
                          if($cmb_sts=='1'|| $cmb_sts==''){
                            $rescbtrhamt= pg_exec($db,$SqlcbTRHamt);
                            if(pg_num_rows($rescbtrhamt)>0){
                            //     echo "$SqlcbSCamt<br>";
                                $resbr = pg_fetch_assoc($rescbtrhamt, 0);
                                // print_r($resbr);
                                $DPKITamt +=$resbr["amt"];
                                $PVKITamt += $resbr["pv"];     
                                $SVKITamt += $resbr["sv"];   
                                $KITTotalHead +=$resbr["total"]; 
                            }
                              
                          }
                         $SqlSCDetail = "select count(prdcd) as qty
                                  from mlm.newsctrh a join mlm.newsctrd b on a.trcd=b.trcd   where  trtype='5'
                                  and trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and a.trcd not like '%****%' and loccd='$rowcode'   ";
                        $SqlBRDetail = "select count(prdcd) as qty
                                  from mlm.newmstrh a join mlm.newmstrd b on a.trcd=b.trcd     where trtype='5'
                                  and trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and a.trcd not like '%****%' and loccd='$rowcode'   ";
                        $SqlBRtrhDetail = "select count(prdcd) as qty
                                  from mlm.newtrh a join mlm.newtrd b on a.trcd=b.trcd     where trtype='5'
                                  and trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and a.trcd not like '%****%' and loccd='".substr($rowcode, -3)."' and a.trcd ilike '%KT%' ";
                        if($cmb_sts=='1'){
                            $SqlSCDetail .=" and post='1' "    ;
                            $SqlBRDetail .=" and post='1' "    ;
                          //  $SqlBRtrhDetail.=" and post='1' "    ;
                        }else if($cmb_sts=='-1'){
                            $SqlSCDetail .=" and post='0'  "    ;
                            $SqlBRDetail .=" and post='0'   "    ;
                          //  $SqlBRtrhDetail.=" and post='0' "    ;
                        }
                      //  $resbrdetail= pg_exec($db,$SqlBRDetail);
                        /*if(pg_num_rows($resbrdetail)>0){
            // echo "$SqlKITBRamt<br>";
                            $resbr = pg_fetch_assoc($resbrdetail, 0);
                          //  print_r($resbr);
                           
                            $TotalKITDetail= $resbr["qty"];  
                        }else {$TotalKITDetail=0;}*/
                       // $TotalKITDetail=0;
                       // echo "$SqlSCDetail<br>";
                        $resscdetail1= pg_exec($db,$SqlSCDetail);
                        if(pg_num_rows($resscdetail)>0){
                          // echo "$SqlKITSCamt<br>";
                           //echo "xxx=$PVKITamt";
                            $resbr = pg_fetch_assoc($resscdetail1, 0);
                        // print_r($resbr);
                           
                            $TotalKITDetail= $resbr["qty"];   
                        }else $TotalKITDetail=0;
                    if($cmb_sts=='1'|| $cmb_sts==''){
                            $restrhdetail= pg_exec($db,$SqlBRtrhDetail);
                            if(pg_num_rows($restrhdetail)>0){
                              // echo "$SqlKITSCamt<br>";
                               //echo "xxx=$PVKITamt";
                                $resbr = pg_fetch_assoc($restrhdetail, 0);
                               //  print_r($resbr);

                                $TotalKITDetail+= $resbr["qty"];   
                            }
                        }
                       $sqlKITMPV="select sum(ipv)::numeric as tot_ipv from mlm.newtrh 
                             where trdt >= '$txtTgl1ymd' and trdt <= '$txtTgl2ymd' and (loccd='$rowcode' or loccd='".substr($rowcode, -3)."' )
                              and wv > 0 and ipv > 0";
                    if($cmb_sts=='1'|| $cmb_sts==''){
                        $resKitMpv= pg_exec($db,$sqlKITMPV);
                         if(pg_num_rows($resKitMpv)>0){
                              $resbr = pg_fetch_assoc($resKitMpv, 0);
                             
                              $TotalKITMPv= $resbr["tot_ipv"]; 
                         }else $TotalKITMPv=0;
                    }
                     ?>   
                      <td>KIT Transaction</td>
                        <td  align="right"><?=number_format($DPKITamt,2)?></td>
                        <td  align="right"><?=number_format($PVKITamt,2)?></td>
                        <td  align="right"><?=number_format($SVKITamt,2)?></td>
                        <td  align="right"><?=number_format($TotalKITMPv,2)?></td>
                        <td  align="right"><?=$KITTotalHead?></td>
                        <td  align="right"><?=$TotalKITDetail?></td>
                          <td ><input type="checkbox"></td>
                    </tr>
     <?}?>
                     <tr  bgcolor="#dee7e5" style="font-weight:bold">
                        <td><div align="center"><strong> Total </strong></div></td>
                         <td align="right"><?=number_format(($DPCBamt+$DPKITamt+$DPPromoamt),2)?></td>
                        <td align="right"><?=number_format(($PVCBamt+$PVKITamt),2)?></td>
                        <td align="right"><?=number_format(($SVCBamt+$SVKITamt),2)?></td>
                       
                        <td  align="right"><?=number_format(($TotalKITMPv+$MVPromoamt),2)?></td>
                         <td align="right"><?=$TotalBrHeader+$KITTotalHead+$TotalPromoHeader?></td>
                        <td align="right"><?=($TotalBRDetail+$TotalKITDetail+$TotalPromoDetail)?></td>
                          <td ><input type="checkbox"></td>
                    </tr>
              <tr bgcolor="#fff">
    <td colspan="8">&nbsp;</td>
              </tr>
     <TR  bgcolor="#ffd7d7" style="font-weight:bold;"> 
                        <TD colspan="8" valign="top" >
                            <b>2. IOC Sales Data</b>
                        </td>
                    </tr> 
                    <tr  bgcolor="#ffd7d7" style="font-weight:bold">
                        <td align="center">Type</td>
                        <td align="center">Amount (PHP)</td>
                        <td align="center">PV</td>
                        <td align="center">SV (PHP)</td>
                        <td align="center">MPV</td>
                        <td align="center">Total Record(Header)</td>
                        <td align="center">Total Record(Detail)</td>
                        <td ></td>


                    </tr>
                    <?
                        $txtTgl1ymd=implode("-", array_reverse(explode("/", $txtTgl1)));
                        $txtTgl2ymd=implode("-", array_reverse(explode("/", $txtTgl2)));
                        $SqlIOCBramt="select sum(tdp) as amt, sum(tpv) as pv , sum(tbv)as sv,count(trcd) as total from mlm.ioc_newsctrh a where trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and  trtype='14' and sccode='$rowcode'";
                        if($cmb_sts=='1'){
                            $SqlIOCBramt .=" and post='1' "    ;
                         
                        }else if($cmb_sts=='-1'){
                            $SqlIOCBramt .=" and post='0'  "    ;
                       
                        }
                        // echo "$SqlIOCBramt<br>";
                        $SqlIOCBramt .="  and trcd not like '%****%'"    ;
                      
                        $resioccbamt= pg_exec($db,$SqlIOCBramt);
                        if(pg_num_rows($resioccbamt)>0){
                         //   echo "$SqlcbBramt<br>";
                            $resbr = pg_fetch_assoc($resioccbamt, 0);
                            //print_r($resbr);
                            $DPIOCamt=$resbr["amt"];
                            $PVIOCamt= $resbr["pv"];     
                            $SVIOCamt= $resbr["sv"];  
                            $TotalIOCHeader=$resbr["total"];  
                        }else {$DPIOCamt=0;$PVIOCamt=0;$SVIOCamt=0;$TotalIOCHeader=0;}
                        
                         
                        $SqlBRIOCDetail = "select count(prdcd) as qty
                                  from mlm.ioc_newsctrh a join  mlm.ioc_newsctrd b on a.trcd=b.trcd     where trtype='14'
                                  and trdt>='$txtTgl1ymd' and  trdt<='$txtTgl2ymd' and a.trcd not like '%****%' and sccode='$rowcode'  ";
                        if($cmb_sts=='1'){
                            $SqlBRIOCDetail .=" and post='1' "    ;
                            
                        }else if($cmb_sts=='-1'){
                            $SqlBRIOCDetail .=" and post='0'  "    ;
                            
                        }
                        $resbriocdetail= pg_exec($db,$SqlBRIOCDetail);
                        if(pg_num_rows($resbriocdetail)>0){
            // echo "$SqlKITBRamt<br>";
                            $resbr = pg_fetch_assoc($resbriocdetail, 0);
                          //  print_r($resbr);
                           
                            $TotalBRIOCDetail= $resbr["qty"];  
                        }else {$TotalBRIOCDetail=0;}
                        
                       
                     
                        ?>
                  <tr bgcolor="#ffd7d7">
                        <td>IOC Sales</td>
                        
                        <td align="right"><?=number_format($DPIOCamt,2)?></td>
                        <td align="right"><?=number_format($PVIOCamt,2)?></td>
                        <td align="right"><?=number_format($SVIOCamt,2)?></td>
                        <td align="right">-</td>
                        <td align="right"><?=$TotalIOCHeader?></td>
                        <td align="right"><?=$TotalBRIOCDetail?></td>
                          <td ><input type="checkbox"></td>
                    </tr>
                      <tr bgcolor="#ffd7d7" style="font-weight:bold">
                        <td align="center"><strong>Total</strong></td>
                        <td align="right"><?=number_format($DPIOCamt,2)?></td>
                        <td align="right"><?=number_format($PVIOCamt,2)?></td>
                        <td align="right"><?=number_format($SVIOCamt,2)?></td>
                        <td align="right">-</td>
                        <td  align="right"><?=$TotalIOCHeader?></td>
                        <td align="right"><?=$TotalBRIOCDetail?></td>
                         <td ><input type="checkbox"></td>
                    </tr>

                <tr bgcolor="#fff">
    <td colspan="8">&nbsp;</td>
  </tr>
                     <tr bgcolor="#e8f2a1" style="font-weight:bold">
                        <TD colspan="2" bgcolor="#e8f2a1" valign="top" >
                            <strong>3. Member Data</strong>
                        </td>
                        <td colspan="6" align="center" bgcolor="#e8f2a1"></td>
                    </tr> 
                    <tr  bgcolor="#e8f2a1" style="font-weight:bold">
                        <td width="20%" align="center" bgcolor="#e8f2a1">Type</td>
                       
                        <td width="10%" align="center" bgcolor="#e8f2a1">Total </td>
                        <td colspan="6">&nbsp;</td>

                    </tr>
                    
                    <tr bgcolor="#e8f2a1">
                        <td width="20%">New Joined Member</td>
               <?$sqljoinmember="select count(*) as total from mlm.msmemb where code ilike '06%' and joindt>='$txtTgl1ymd' and joindt<='$txtTgl2ymd' and loccd='$rowcode'   ;";
                      // echo "$sqljoinmember <br>";
                        $resjoinmember= pg_exec($db,$sqljoinmember);
                        if(pg_num_rows($resjoinmember)>0){
                             $resjoinmemberassoc = pg_fetch_assoc($resjoinmember, 0);
                             $joinmember=$resjoinmemberassoc["total"];
                        }else $joinmember="0";
                       
                       ?>
                        <td width="10%" align="right"><?=$joinmember?></td>
                        <td  colspan="6"><input type="checkbox"></td>
                    </tr>
                       <?$sqljoinioc="select count(a.icode) as total  
                               from mlm.ioc_newsctrh a join mlm.ioc_msmemb b on a.icode=b.icode 
                               where icountry = 'PH' and joindt>='$txtTgl1ymd' and joindt<='$txtTgl2ymd' and sccode='$rowcode' ;";
                      // echo "$sqljoinioc <br>";
                       $resjoinioc= pg_exec($db,$sqljoinioc);
                        if(pg_num_rows($resjoinioc)>0){
                             $resjoiniocassoc = pg_fetch_assoc($resjoinioc, 0);
                             $joinioc=$resjoiniocassoc["total"];
                        }else $joinioc="0";
                       
                       ?>
                     <tr bgcolor="#e8f2a1">
                        <td width="20%">New Joined IOC</td>
                       
                        <td width="10%" align="right"><?=$joinioc?></td>
                        <td  colspan="6"><input type="checkbox"></td>
                    </tr>
                    <tr bgcolor="#e8f2a1" style="font-weight:bold">
                         <td><div align="center"><strong> Total </strong></div></td>
                        
                        <td width="10%"align="right"><?=($joinioc+$joinmember)?></td>
                        <td  colspan="6"><input type="checkbox"></td>
                        
                    </tr>
                <tr bgcolor="#fff">
    <td colspan="8">&nbsp;</td>
  </tr>
        <?                  }
        
                        }      
                        
                        
                        
                }
        ?>  
          </TABLE> 
               <!-- <TR bgcolor="#E6E6E6" style="<?//= (pg_numrows($aresa) == 0) ? "display:none" : "" ?>">
                    <TD colspan="8" align="left">
                        <input type="button" name="btnEXP3" value="Export to CSV" onclick="exportME()" >
                        <INPUT type="button" name="Button" value="<?//= mxlang(406,'') ?>" onclick="goprint()">
                    </TD>
                </TR>-->

    
     <div align="center" style="margin-top:1em">Report Generated Date: <?=date("d/m/Y")?></div>
        <TABLE width="790" align="center" border="0" cellspacing="1" cellpadding="6" bgcolor="#fff">
         <tr bgcolor="#FFFFFF">
			<td height="20"  align="right" colspan="8">
				<div id="main"><div id="tomato">
				<input type="button" name="btnOK" value="<?=mxlang(110,'')?>" onClick="goprint()">
				<input type="button" name="Button" value="<?=mxlang(294,'')?>" onclick="location.href='sls_member_data_report.php'">
				</div></div>
			</td>
		</tr>
	</TABLE>
</body>
</html>

