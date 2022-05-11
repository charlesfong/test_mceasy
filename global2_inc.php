<?php
include_once("negative_val_prnx.php");
$blns_00=array('01'=>"Jan",'02'=>"Feb",'03'=>"Mar",'04'=>"Apr",'05'=>"May",'06'=>"Jun",'07'=>"Jul",'08'=>"Aug",'09'=>"Sep",'10'=>"Oct",'11'=>"Nov",'12'=>"Dec");
$arr_download = array('New Member', 'New Sales', 'Maintain Sales', 'Change Upcode', 'Invoice','Edited Member','Warehouse Invoice','Foreign Member','Staff Receipt','Product Master');
//$arr_race = array ("M" => "MALAY", "C" => "CHINESE", "I" => "INDIAN", "B"=>"BUMIPUTRA", "O"=>"OTHERS", "T"=>"THAILAND");
$arr_race = array ("M" => "MALAY", "C" => "CHINESE", "I" => "INDIAN", "O"=>"OTHERS", "T"=>"THAILAND");
$xstatus_contra = array ("N" => mxlang("191"), "S" => mxlang("1781"), "C" => mxlang("970"), "X" => mxlang("950"), "B" => mxlang("1783"), "V" => mxlang("1782"), "D" => mxlang("954"), "E" => ucwords(strtolower(mxlang("948"))), "R" =>mxlang("1140"));
$xcontra_type =array("C" => "Contra Cheque", "V"=>"Contra Voucher");
//$xstatus_contra1 = array ("normal"=>"N", "stop payment"=>"S","contra"=>"C", "contra replacement"=>"X", "cleared at bank"=>"B", "reverse stop payment"=>"V", "deleted"=>"D", "exchange"=>"E", "replacement"=>"R");
$x01last_time = mktime(0,0,0, date('m')-1, 1, date('Y'));
$x30last_time = mktime(0,0,0, date('m'), 0, date('Y'));
$x01last=date('d/m/Y', $x01last_time);
$x30last=date('d/m/Y', $x30last_time);
$xtoday	=date('d/m/Y');
$x01this_time = mktime(0,0,0, date('m'), 1, date('Y'));
$x01this=date('d/m/Y', $x01this_time);
$daysInMonth=array('31','28','31','30','31','30','31','31','30','31','30','31');
$xarr_type_grn=array("Re-packing", "Damage Goods", "Product Recall - Return", "Product Recall - Scrap");
$xarr_to_grn	=array("Shipping Department", "External Supplier");
$xarr_to_grn_lang	=array(mxlang("743"),mxlang("744"));
//cek kabisat
$def_usccuser = "8eVN4z542";
$def_uscctrx = "4974xQsW9WacE6vB";
if (date("L")==1) {
	$daysInMonth[1]='29';
}
$def_newtrh =
	array(
		array("trcd","C", 50),
		array("trtype","C", 1),
		array("code","C", 15),
		array("bc_id","C", 15),
		array("upcode","C", 15),
		array("trdt", "D"),
		array("etdt", "D"),
		array("loccd","C", 15),
		array("tdp","N", 10, 2),
		array("tpv","N", 10, 2),
		array("tbv","N", 10, 2),
		array("pdisc","N", 10, 2),
		array("ptax","N", 10, 2),
		array("ndp","N", 10, 2),
		array("npv","N", 10, 2),
		array("nbv","N", 10, 2),
		array("totpay","N", 10, 2),
		array("note1","C", 200),
		array("note2","C", 200),
		array("note3","C", 200),
		array("createnm","C", 10),
		array("lupddt", "D"),
		array("lupdtm", "D"),
		array("opnm","C", 20),
		array("send","C", 25),
		array("flag","C", 1),
		array("status","C", 10),
		array("cbno","C", 50)
	);


function fdisc_item($xprdcd, $xqty, $xdp) {
	$xamount=0;
	global $db;
	$sql_disc = "select print,discount from msprd where trim(prdcd)='$xprdcd'";
	$res_disc = pg_exec($db, $sql_disc);
	if (pg_num_rows($res_disc)>0) {
		$xitem_min 	= pg_fetch_result($res_disc, 0, 0);
		$xitem_disc = pg_fetch_result($res_disc, 0, 1);
		if ($xqty>=$xitem_min && $xitem_min>0) {
			$xamount = (floor($xqty/$xitem_min))*$xitem_disc;
			//$xamount = $xitem_disc;
		}
	}
	pg_free_result($res_disc);
	return $xamount;
}

function fget_state($xst_id, $xst_other='') {
	global $db;
	$xst_name='-';
	$xsql="select st_name from state where st_id=$xst_id";
	//echo($xsql."<br>\n");
	if ($xst_id!='') {
		$xres=pg_exec($db, $xsql);
		if (pg_num_rows($xres)>0) {
			$xst_name=pg_fetch_result($xres, 0, 0);
			if (strtoupper($xst_name)=='OTHERS' && $xst_other!='') {
				$xst_name=$xst_other;
			}
		}
		pg_free_result($xres);
	}
	return $xst_name;
}

function fget_country($xcn_id) {
	global $db;
	$xcn_name='-';
	//$xsql="select cn_name from country where cn_id='$xcn_id'";
	$xsql = "select name from country_list where iso='$xcn_id'";
	//echo($xsql."<br>\n");
	$xres=pg_exec($db, $xsql);
	if (pg_num_rows($xres)>0) $xcn_name=pg_fetch_result($xres, 0, 0);
	pg_free_result($xres);
	//if ($xcn_name=='MALAYSIA') $xcn_name='MY';
	return stripslashes($xcn_name);
}

function getBalanceAmount($mCode) {
	global $debug;
	$cur_bal=0;
	$q1 = "SELECT a.balamt FROM ewallet.ewallettrx a WHERE a.memcode='$mCode' ORDER BY date(a.date) desc, a.no desc limit 1 offset 0";
	if($GLOBALS["db5"]->doQuery($q1)->isFound()) {
		$row = $GLOBALS["db5"]->getFirstRow();
		$cur_bal = $row["balamt"];
	} 
	return $cur_bal;
}

function fdo_eppay($ewno,$cbdate,$cbno,$memid) {
	global $debug;
	$msg = "";
	$arcredstatdep = array('1','4','5','6','8','11','12','15','17','18','19','21','23','24','25','26','27','28','29','32');
	$ardebstatdep = array('14','2','3','7','9','10','13','16','20');
	
	$thesql = "select memcode,date,desctrx,amt,balamt,remark,statdeposit,opnm,ewtrxno,ewtrxstate,curr from ewallet.ewallettrx_temp where ewtrxno='$ewno'";
	if($GLOBALS["db5"]->doQuery($thesql)->isFound()) {
		$ewdata = $GLOBALS["db2"]->toArray();
		foreach($ewdata as &$a) {
			if(in_array($a["statdeposit"],$arcredstatdep))
				$realBalance=getBalanceAmount($a["memcode"]) + $a["amt"];
			else if(in_array($a["statdeposit"],$ardebstatdep))  
				$realBalance=getBalanceAmount($a["memcode"]) - $a["amt"];   
			else  $realBalance=0;
			$realBalance=round($realBalance,2);
			if ($cbdate=="") $cbdate=$a["date"];
			$asql = "select * from ewallet.ewallettrx where ewtrxno='$ewno' and memcode='{$a["memcode"]}' and statdeposit='{$a["statdeposit"]}'";
			if(!$GLOBALS["db5"]->doQuery($asql)->isFound()) 
				$GLOBALS["db5"]->doInsert("ewallet.ewallettrx",
					array(
						"memcode"=>$a["memcode"],
						"date"=>$cbdate,
						"desctrx"=>$a["desctrx"],
						"amt"=>$a["amt"],
						"balamt"=>$realBalance,
						"remark"=>$cbno,
						"statdeposit"=>$a["statdeposit"],
						"opnm"=>$a["opnm"],
						"ewtrxno"=>$a["ewtrxno"],
						"ewtrxstate"=>$a["ewtrxstate"],
						"curr"=>$a["curr"]
					)
				);
			else 
				$GLOBALS["db5"]->doUpdate("ewallet.ewallettrx",array("remark"=>$cbno),"ewtrxno='$ewno' and memcode='{$a["memcode"]}'");
			
			if ($stdep=="14" || $stdep=="2") {
				$asql = "select * from ewallet.ewpayment where ewtrxno='$ewno'";
				if($GLOBALS["db5"]->doQuery($asql)->isFound()) 
					$GLOBALS["db5"]->doUpdate("ewallet.ewpayment",array("pono"=>$cbno),"ewtrxno='$ewno'");
				else
					$GLOBALS["db5"]->doInsert("ewallet.ewpayment",
						array(
							"ewtrxno"=>$a["ewtrxno"],
							"amt"=>$a["amt"],
							"pono"=>$cbno,
							"curr"=>$a["curr"]
						)
					);
			}
		}
	}
}

function count_prd_items2($invcode,$theMSID,$datecode,$mode) {
	global $db,$xprd;
	$ccresult=pg_exec($db,"set datestyle to 'POSTGRES,EUROPEAN';");
	$spoons = 0;
	$invcode = trim($invcode);
	if ($mode==0) {
		$querrvfx=
			"select a.prdcd,sum(b.qty)
			from msprd_items a,newmstrd b,newmstrh c
			where trim(a.prdcd)='$invcode' and trim(b.prdcd)=trim(a.prdcd)
			and b.trcd=c.trcd and c.loccd='$theMSID' and c.trdt<='$datecode'
			GROUP BY a.prdcd
			order by prdcd;";
	}
	if ($mode==1) {
		$querrvfx=
			"select a.prdcd,sum(b.qty)
			from msprd a,newmsivtrd b,newmsivtrh c
			where trim(a.prdcd)='$invcode' and trim(b.prdcd)=trim(a.prdcd)
			and b.trivcd=c.trivcd and
			c.loccd='$theMSID' and c.trdt<='$datecode'
			GROUP by a.prdcd
			order by prdcd;";
	}
	if ($mode==2) {
		$querrvfx=
			"select a.prdcd,sum(b.qty)
			from msprd a,newmsivtrd b,newmsivtrh c
			where trim(a.prdcd)='$invcode' and trim(b.prdcd)=trim(a.prdcd)
			and b.trivcd=c.trivcd and
			c.code='$theMSID' and c.trdt<='$datecode'
			GROUP by a.prdcd
			order by prdcd;";
	}
	if ($mode==3) {
		$querrvfx=
			"select a.prdcd,sum(b.qty)
			from msprd a,newsctrd b,newsctrh c
			where b.trcd=c.trcd and  trim(a.prdcd)='$invcode' and trim(b.prdcd)=trim(a.prdcd)
			and trim(c.note3)='$theMSID' and c.trdt<='$datecode'
			GROUP by a.prdcd
			order by prdcd;";
	}
	if ($mode==4) {
	$querrvfx=
		"select a.prdcd,sum(b.qty)
		from msprd_items a,focmstrd b,focmstrh c
		where trim(a.prdcd)='$invcode' and trim(b.prdcd)=trim(a.prdcd) and b.trcd=c.trcd and
		trim(c.loccd)='$theMSID' and c.trdt<='$datecode'
		GROUP by a.prdcd
		order by prdcd;";
	}
	//if ($invcode=="FB002" && $mode==2) print "$querrvfx<br>";
	$spoons=0;
	$resultvfx=pg_exec($db,$querrvfx);
	if (pg_num_rows($resultvfx)>0) {
/*		for($mx=0;$mx<pg_num_rows($resultvfx);$mx++) {
			$rowinvqty=pg_fetch_row($resultvfx,$mx);
			$spoons = $spoons+($rowinvqty[2]*$rowinvqty[3]);
			//print "$mx. $spoons<br>";
		}*/
		$spoons=pg_fetch_result($resultvfx, 0, 1);
	}
	return $spoons;
}

function selBNSC2($idx,$fldname) {
	global $db,$priv,$opnm,$txt_code,$txt_msid,$txtTo;

	if ($priv==2)
	{
		$svcquerr=
			"select code from msmemb a
			where a.svsldr='$idx' and a.code='$opnm'
			order by code ASC;";
		$ressvc=pg_exec($db,$svcquerr);
		//print $svcquerr;
		if (pg_numrows($ressvc)<0) {
			print "<INPUT type=\"text\" name=\"$fldname\" size=\"10\" 	maxlength=\"9\">\n";
		} else {
			print "<select name=\"$fldname\">\n";
			for($ls=0;$ls<pg_numrows($ressvc);$ls++) {
				$rowls=pg_fetch_row($ressvc,$ls);
				//$test = $fldname;
				$selc = "";
				if ($txt_code==$rowls[0] || $txt_msid==$rowls[0] || $txtTo==$rowls[0]) $selc = " selected";
				print "<option value=\"$rowls[0]\" $selc>$rowls[0] (".trim(get_memname($rowls[0]))."$fldname_result)</option>\n";
			}
			print "</select>\n";
		}
	}
	else
	{
	//print $txtTo;
		$qry_usrbranch="SELECT whcode FROM users_extra WHERE uname='$opnm'";
		$res_usrbranch=pg_exec($db,$qry_usrbranch);
		$code_usr=pg_fetch_row($res_usrbranch);
		if($code_usr[0]==1)
		{
			print "<select name=\"$fldname\">\n";

			$qry_usrbranch2=
				"SELECT b.br_code,b.br_name
			FROM msms_new b, users_braccess a
			WHERE a.brcode=b.br_code and b.br_status=true and b.br_deleted=false
			and a.uname='$opnm'
			order by b.br_code ASC ";
			$res_usrbranch2=pg_exec($db,$qry_usrbranch2);
			if (pg_numrows($res_usrbranch2)>1)
			print "<option value=\"DDEPT\" $selc>".mxlang("524")."</option>\n";
			while($row_usr2=pg_fetch_row($res_usrbranch2))
			{
				//$test = $$fldname;
				echo $row_usr2[0];
				$selc = "";
				if ($txt_code==$row_usr2[0] || $txt_msid==$row_usr2[0]  || $txtTo==$row_usr2[0]) $selc = " selected";
				print "<option value=\"$row_usr2[0]\" $selc>$row_usr2[0] (".trim(get_memname($row_usr2[0])).") </option>\n";
			}
			print "</select>\n";
		}
		else
		{
			print "<INPUT type=\"text\" name=\"$fldname\" size=\"10\" 	maxlength=\"9\">\n";
		}

	}

}

function selBNSC_FREPORT($idx,$fldname) {
	// $idx=0 display 'Warehouse'
	// $idx=1 display 'All'
	global $db, $opnm;
	$qry_usrbranch2="
	SELECT b.br_code,b.br_name
	FROM msms_new b, users_braccess a
	WHERE a.brcode=b.br_code and b.br_status=true and b.br_deleted=false
	and a.uname='$opnm'
	order by b.br_code ASC ";

	if ($_POST[$fldname]=='') $seltxt='selected';
	print "<select name=\"$fldname\">\n";
	if ($idx==0) echo "<option value='DDEPT' $seltxt>--".mxlang("524")."--</option>\n";
	if ($idx==1) echo "<option value='0' $seltxt>--".mxlang("934")."--</option>\n";
	$res_usrbranch2=pg_exec($db,$qry_usrbranch2);
	while($row_usr2=pg_fetch_row($res_usrbranch2))
	{
		//$test = $$fldname;
		//echo $row_usr2[0];
		$seltxt=($_POST[$fldname]==$row_usr2[0])?" selected ":" ";
		print "<option value=\"$row_usr2[0]\" $seltxt>$row_usr2[0] (".$row_usr2[1].") </option>\n";
	}
	print "</select>\n";
	pg_free_result($res_usrbranch2);
}


function check_stockist_ex($st_id){
	global $db;
	$xsql =
		"select stockist from sub_mssc_extra
		where scname='$st_id';";
	$hresult='-1';
	$xresult=pg_exec($db, $xsql);
	if (pg_num_rows($xresult)>0) {
		$hresult=pg_fetch_result($xresult, 0, 0);
		if ($hresult=='') $hresult='1';
	}
	pg_free_result($xresult);
	return $hresult;
}

function check_branch_ex($br_id){
	global $db;
	//$xsql = "select loccd from msms where loccd='$br_id';";
	$xsql = "select br_code from msms_new where br_code='$br_id';";
	$hresult=-1;
	$xresult=pg_exec($db, $xsql);
	if (pg_num_rows($xresult)>0) {
		$hresult=1;
	}
	pg_free_result($xresult);
	return $hresult;
}

function check_level_ex($the_id){
	//result WH, BR, MS, SC
	global $db;
	$hresult='-';
	if ($the_id=='DDEPT'){
		$hresult='WH';
	}else{
		$xsql = "select br_code from msms_new where br_code='$the_id';";
		$xres = pg_exec($db, $xsql);
		if (pg_num_rows($xres)>0) {
			$hresult='BR';
		}else{
			$xsql = "select stockist from sub_mssc_extra where scname='$the_id';";
			$xres=pg_exec($db, $xsql);
			if (pg_num_rows($xres)>0) {
				$xrow=pg_fetch_row($xres, 0);
				if ($xrow[0]=='0') {
					$hresult='MS';
				}else{
					$hresult='SC';
				}
			}
		}
		pg_free_result($xres);
	}
	return $hresult;
}

function is_autoconvert($xprdcd) {
	// check seq==1 in msprd tables
	global $db;
	$xresult=false;
	$sql1 = "select seq,type from msprd where trim(prdcd)='$xprdcd';";
	$res1 = pg_exec($db, $sql1);
	if (pg_numrows($res1)>0) {
		$h_row = pg_fetch_row($res1, 0);
		//if autoconvert && type package
		if ($h_row[0]=='1') $xresult=true;
	}
	pg_free_result($res1);
  return $xresult;
}

function is_package($xprdcd,$xloccd) {
  $flag = FALSE;
	
	$autoconvert = $GLOBALS["db2"]->doQuery("select type,seq,scdp2 from msprd where trim(prdcd)='$xprdcd'")->getFirstRow();
	
  if (!empty($autoconvert)) {
		
		if ($autoconvert["type"]=='2') {
			if ($xloccd=="DDEPT")
				$flag = ($autoconvert["seq"]=='1');
			else {
				if ($autoconvert["scdp2"]=='1')
					$flag = $GLOBALS["db2"]->doQuery("select br_code from msms_new where br_code='$xloccd'")->isFound();
			}
		}
  }
  
  return $flag;
}

function product_is_exist($xprdcd) {
	global $db;
	$xresult=false;
	$sql1 ="select prdcd from msprd where trim(prdcd)='$xprdcd';";
	$res1 = pg_exec($db, $sql1);
	if (pg_numrows($res1)>0) {
		$xresult=true;
	}
	pg_free_result($res1);
  return $xresult;
}

function hitung_maxqoh($prdcode,$whid) {

	global $db,$region,$debug;
	$spoons = 0;
	$bigx = 0;
	$prdcode = trim($prdcode);

	$querrp="select invcd,inv_qty from msprd_items where prdcd='$prdcode' order by invcd;";
	$resultp=pg_exec($db,$querrp);
	if (pg_numrows($resultp)>0) {
		$is_round=1;
		for($mx=0;$mx<pg_numrows($resultp);$mx++) {
			$rowp=pg_fetch_row($resultp, $mx);
			$xinv_qty=$rowp[1];
			$querrpx="select sum(qty_bal) from fifo.batch_inloc where prdcd='$rowp[0]' and loccd='$whid'";
			//if ($_SESSION['opnm']=='didit') echo "<br>GGGGGGGGG:$prdcode::".$querrpx;
			$resultpx=pg_exec($db,$querrpx);
			if (pg_numrows($resultpx)>0) {
				$rowpx=pg_fetch_row($resultpx, 0);
				$xinloc	= $rowpx[0]/$xinv_qty;
			} else {
				$xinloc	= 0;
			}
			if ($spoons==0 && $is_round==1) {
				$spoons=$xinloc;
			}else{
				if ($xinloc<$spoons) $spoons=$xinloc;
			}
			$is_round=$is_round+1;
			Logger::debug("spoons:",$spoons);
		}
	}
	$spoons = round_down($spoons);
	
	return $spoons;
}

function fpart_date(){
    global $db;
    $result=pg_exec($db,"set datestyle to 'ISO,EUROPEAN'");
    $dbdate=pg_exec($db,"select current_date");
    $dbrow=pg_fetch_row($dbdate,0);
    $cmbdate=substr($dbrow[0],2,2).substr($dbrow[0],5,2).substr($dbrow[0],8,2);
    return $cmbdate;

}

function auto_convert($xprdcd, $xqty, $xloccd, $xnote, $xcancel='') {
	global $db, $debug, $opnm, $year1, $month1, $date1;

	$digitcb="DO-AD";
	$digitcb=$digitcb.fpart_date();
	$thismonth=$year1;
	$bresult=pg_exec($db,"begin transaction");
	$squery = "select nvalue from newdoadmonth where nstart='$thismonth' and loccd='$xloccd';";
	$dresult=pg_exec($db,$squery);
	if ($debug==1) print $squery."<br>";

		if (pg_numrows($dresult)>0)	{
			list($newTransId)=pg_fetch_row($dresult,0);
			$newTransId=$newTransId+1;
			$dresult=pg_exec($db,"update newdoadmonth set nvalue=nvalue+1 where nstart='$thismonth' and loccd='$xloccd';");
			if ($debug==1) print "update newdoadmonth set nvalue=nvalue+1 where nstart='$thismonth' and loccd='$xloccd';"."<br>";
		} else {
			$dresult=pg_exec($db,"insert into newdoadmonth values('$thismonth',1,'$xloccd')");
			if ($debug==1) print "insert into newdoadmonth values('$thismonth',1,'$xloccd')<br>";
			$newTransId=1;
		}
		for ($i=0;strlen($newTransId)<=5;$i++)
			$newTransId="0" . $newTransId;
		$newTransId=$digitcb.$newTransId;
		$tdate=array_values(getdate());

		if ($xcancel=='cancel')	{
			$querr="insert into adjustment_description
			values
			('$newTransId',
			'Cancel Auto convert ".-$xqty." pcs $xprdcd at $date1/$month1/$year1 $xnote');";
		}else{
			$querr="insert into adjustment_description
			values
			('$newTransId',
			'Auto convert $xqty pcs $xprdcd at $date1/$month1/$year1 $xnote');";
		}
		if ($debug==1) print $querr."<br><br>";
		$result=pg_exec($db,$querr);

		$querr=
			"insert into addotrh
			(trcd,trtype,code,bc_id,upcode,loccd,trdt,etdt,createnm,opnm,status)
				values
			('$newTransId','1','$xloccd','".ID_CNT."','$upcd','$xloccd',date(now()),date(now()),'$opnm','$opnm','1')";
		if ($debug==1) print $querr."<br>";
		$result=pg_exec($db,$querr);

		$sql_item=
			"select trim(inv_prdcd),inv_qty
			from msprd_items where prdcd='$xprdcd' ";
		if ($debug) echo("$sql_item<br>\n");
		$res_item=pg_exec($db, $sql_item);
		if (pg_num_rows($res_item)>0)	 {
			for ($r=0;$r<pg_num_rows($res_item);$r++) {
				$row_item=pg_fetch_row($res_item, $r);
				$x_qoh=hitungqoh_basic($row_item[0], $xloccd);
				$xqty_item=-($row_item[1]*$xqty);
				$query2trd =
					"insert into addotrd
					(trcd,prdcd,qty,dp,cp,sp,pv,bv) values
				('$newTransId','".$row_item[0]."',$xqty_item,0,0,0,0,$x_qoh);";
				if ($debug==1) print $query2trd."<br>";
				else $result2trd = pg_exec($db,$query2trd);
				update_inloc_basic($row_item[0],$row_item[1]*$xqty,$debug,$xloccd,0,'-');

			}
		}
		pg_free_result($res_item);

		//update inloc package +
		$x_qoh=hitungqoh_basic($xprdcd, $xloccd);
		$query2trd =
			"insert into addotrd
			(trcd,prdcd,qty,dp,cp,sp,pv,bv) values
		('$newTransId','".$xprdcd."',".$xqty.",0,0,0,0,$x_qoh);";
		if ($debug==1) print $query2trd."<br>";
		else $result2trd = pg_exec($db,$query2trd);
		update_inloc_basic($xprdcd,$xqty,$debug,$xloccd,0,'+');

		if ($debug==0) {
			$bresult=pg_exec($db,"commit transaction");
		}
}

function check_input_prd_transaction($xtmp_table, $xtrcd, $xfieldtrcd, $xloccd) {
	global $db, $xopnm, $msg;//, $debug;

	//from inloc save to array
	$xsql="
		select a.prdcd
		from $xtmp_table a, msprd b
		where a.prdcd=b.prdcd and a.$xfieldtrcd='".strtoupper($xtrcd)."'
		and a.opnm='$xopnm'
		union
		select c.inv_prdcd
		from $xtmp_table a, msprd b, msprd_items c
		where a.prdcd=b.prdcd and b.prdcd=c.prdcd and (b.type='2' or b.type='6' or kit='1')
		and a.$xfieldtrcd='".strtoupper($xtrcd)."' and a.opnm='$xopnm'; ";

	if ($debug) echo($xsql)."<br>";
	$xres=pg_exec($db, $xsql);
	if (pg_num_rows($xres)>0) {
		for ($x=0;$x<pg_num_rows($xres);$x++) {
			$xxprdcd = pg_fetch_result($xres, $x, 0);
			$xxinloc = hitungqoh_basic($xxprdcd, $xloccd);
			$arr_inlocbefore[$xxprdcd]=$xxinloc;
		}
	}
	pg_free_result($xres);

	if ($debug) echo("Inloc before <br>\n");
	for ($kk=0; $kk<count($arr_inlocbefore); $kk++) {
		list ($xxprdcd, $xxqoh_sisa) = each($arr_inlocbefore);
		if ($debug) echo(" $xxprdcd $xxqoh_sisa <br>\n");
	}

	//echo("<br>");
	$xsql="
		select a.prdcd, a.qty, '0' as xtype,b.seq
		from $xtmp_table a, msprd b
		where a.prdcd=b.prdcd and (b.type='4' or b.seq='1')
		and a.$xfieldtrcd='".strtoupper($xtrcd)."' and a.opnm='$xopnm'
		union
		select a.prdcd, a.qty, '1' as xtype,b.seq
		from $xtmp_table a, msprd b
		where a.prdcd=b.prdcd and (b.type='2' or b.type='6' or kit='1')
		and a.$xfieldtrcd='".strtoupper($xtrcd)."' and a.opnm='$xopnm'
	; ";
	if ($debug) echo($xsql)."<br>";
	$xres=pg_exec($db, $xsql);
	if (pg_num_rows($xres)>0) {
		for ($x=0;$x<pg_num_rows($xres);$x++) {
			$xxrow = pg_fetch_row($xres, $x);
			$xxprdcd=$xxrow[0];
			$xxqty	=$xxrow[1];
			$xxtype	=$xxrow[2];
			$xxauto	=$xxrow[3];
			if ($debug) echo("*$xxprdcd $xxqty $xxtype<br>\n");
			$arr_inlocafter[$xxprdcd]=$arr_inlocbefore[$xxprdcd] - $xxqty;
			$arr_inloctype[$xxprdcd]=$xxtype;
			$arr_inlocauto[$xxprdcd]=$xxauto;
		}
	}
	pg_free_result($xres);

	if (count($arr_inlocbefore)>0) {
		reset($arr_inlocbefore);
		reset($arr_inlocafter);
		reset($arr_inloctype);
		reset($arr_inlocauto);
	}
	if ($debug) echo("<br>Inloc after <br>\n");
	for ($kk=0; $kk<count($arr_inlocafter); $kk++) {
		list ($xxprdcd, $xxqoh_sisa2) = each($arr_inlocafter);
		list ($xxprdcd, $xxtype2) = each($arr_inloctype);
		list ($xxprdcd, $xxauto2) = each($arr_inlocauto);

		if ($debug) echo("$xxprdcd $xxqoh_sisa2 $xxtype2 <br>");
		if ($xxqoh_sisa2<0) { // if has been found minus qoh after input
			if ($xxauto2==0) {
				$cek_minus=true;
				$msg=mxlang("1970",$xxprdcd);
				//break;
			}else{	//autoconvert
				$querrp=
					"select trim(inv_prdcd),inv_qty from msprd_items
					where trim(prdcd)='$xxprdcd' order by inv_prdcd;";
				//echo("$querrp<br>");
				$resultp=pg_exec($db,$querrp);
				$spoons=0;
				if (pg_numrows($resultp)>0) {
					for($mx=0;$mx<pg_numrows($resultp);$mx++) {
						$rowp=pg_fetch_row($resultp, $mx);
						$xinv_qty=$rowp[1];
						if ($arr_inlocafter[$rowp[0]]=='' && $arr_inlocbefore[$rowp[0]]!='') {
							$arr_inlocafter[$rowp[0]]=$arr_inlocbefore[$rowp[0]];
						}
						$xinloc	= $arr_inlocafter[$rowp[0]]/$xinv_qty;
						if ($mx==0) {
							$spoons=$xinloc;
						}else{
							if ($xinloc<$spoons) $spoons=$xinloc;
						}
					}
				}
				$xqoh2=$spoons;
				$xqoh2_need=$xqoh2+$xxqoh_sisa2;

				//echo("xqoh2=$xqoh2 xqoh2_need=$xqoh2_need  <br>\n");
				if ($xqoh2_need<0) {
					$cek_minus=true;
					$msg=mxlang("1970",$xxprdcd);
				}
				break;
			}
		}
		//echo("$xxprdcd $xxqoh_sisa2 $xxtype2 <br>\n");
	}
	return $cek_minus;
}

function get_upcode($memidx) {
	global $db;
	$querr="select upcode from msmemb where code='$memidx';";
	//echo($querr);
	$result=pg_exec($db,$querr);
	if (pg_num_rows($result)>0) $hresult=pg_fetch_result($result, 0, 0);
	pg_free_result($result);
	return $hresult;
}

function prd_items3($prdcode) {
	global $db,$region;
	//if (is_package($prdcode)) {
		$querrvfx="select trim(b.prdnm),trim(b.prdcd),a.inv_qty from msprd_items a,msprd b
			where trim(a.prdcd)='$prdcode' and a.cn_id='$region' and b.prdcd=a.inv_prdcd;";
		$resultvfx=pg_exec($db,$querrvfx);
		for($mx=0;$mx<pg_numrows($resultvfx);$mx++) {
			$rowkititem=pg_fetch_row($resultvfx,$mx);
			print "<br><small><font color='blue'>&nbsp;- $rowkititem[2] QTY, $rowkititem[1] ".stripslashes($rowkititem[0])."</font></small>";
		}
		pg_free_result($resultvfx);
	//}
}

function js_input_prd() {
	global $xinput_prdcd_type, $xloccd;
	if ($xinput_prdcd_type=='' || $xinput_prdcd_type==1) {
?>
		function fadd_item(xnilai) {
			var shortcut 	 = document.frm_invcnt_fltr;
			var the_submit = false;
			shortcut.item_action.value='add';
			if (shortcut.prd_txt_input.value!='' && shortcut.prd_list_input.value=='--list--') {
				shortcut.prd_hid_input.value=shortcut.prd_txt_input.value;
				the_submit = true;
			}
			if (shortcut.prd_txt_input.value=='' && shortcut.prd_list_input.value!='--list--') {
				shortcut.prd_hid_input.value=shortcut.prd_list_input.value;
				the_submit = true;
			}
			//if (shortcut.prd_txt_input.value!='' && shortcut.prd_list_input.value!='--list--'
			//	&& shortcut.prd_txt_input.value!=shortcut.prd_list_input.value) {
			if (shortcut.prd_txt_input.value!='' && shortcut.prd_list_input.value!='--list--') {
				alert(shortcut.xalert_1543.value);
				//alert("Please, select input just one !!");
				shortcut.prd_txt_input.value='';
				shortcut.prd_txt_input.focus();
				the_submit = false;
			}
			shortcut.prd_hid_input.value=shortcut.prd_hid_input.value.toUpperCase();
			if (the_submit) {
				shortcut.next.value=1;
				shortcut.submit();
			}
		}
<?
	}elseif ($xinput_prdcd_type==2) {
?>
		function fadd_item(xnilai) {
			var shortcut 	 = document.frm_invcnt_fltr;
			var the_submit = false;
			shortcut.item_action.value='add';
			if (xnilai==0) {
				if (shortcut.prd_txt_input.value!='') {
					shortcut.prd_hid_input.value=shortcut.prd_txt_input.value;
					shortcut.qty.value=shortcut.qty1.value;
					the_submit = true;
				}else{
					alert('Please input product code ! ');
					shortcut.prd_txt_input.focus();
					the_submit = false;
				}
			}
			if (xnilai==1) {
				shortcut.prd_hid_input.value=shortcut.prd_list_input.value;
				shortcut.qty.value=shortcut.qty2.value;
				the_submit = true;
			}
			if (the_submit) {
				shortcut.prd_hid_input.value=shortcut.prd_hid_input.value.toUpperCase();
				shortcut.next.value=1;
				shortcut.submit();
			}
		}
<?
	}elseif ($xinput_prdcd_type==3) {
	}
}

function frm_input_prd($xloccd) {
	global $db, $cbo_filter, $qty, $qty1,$qty2,$prd_txt_input,$prd_list_input,$PPV,
		$page_doin, $page_dist_sp, $page_inv, $page_sp, $xtypeprd, $priv,
		$page_sc;
	//print $page_doin;
	if ($xloccd=='DDEPT' && $cbo_filter=="" && $page_doin==1) {
			if ($PPV==0) $add_filter1 = "and pv<=0 ";
			if ($PPV==1) $add_filter1 = "and pv>0 ";
	} else {
		if ($cbo_filter=="") $cbo_filter=1;
		switch ($cbo_filter) {
			case 1:
				$add_filter1 = "and pv>0 ";
				break;
			case 2:
				$add_filter1 = "and pv<=0 ";
				break;
		};
	}

	if ($xtypeprd=='normal')	{ //only normal product
		$add_fltr = " and a.type='4' ";
	}else{
		$add_fltr = " and (a.type='4' or (b.exp_date>now() and a.type='6') or a.type='2') ";
	}

	$querr=
		"select trim(a.prdcd) as prdcd,a.prdnm,a.type,a.seq,0 as type_item
		from msprd a,msprd_extra b
		where trim(b.prdcd)=trim(a.prdcd) and b.viewable='1'
		and b.discontinue='0' and a.pdcid is null
		$add_filter1 $add_fltr ".
/*		union
		select prid as prdcd,pname as prdnm,1 as type_item
		from promo_product
		where available='t' and testing='f' and expdate>=now()
		$add_filter1 ".*/
		" order by type_item,prdcd;";

	//print $querr;
	global $xinput_prdcd_type;
	if ($xinput_prdcd_type=='' || $xinput_prdcd_type==1) {
		$result = pg_exec($db,$querr);
?>
		<table width="100%" border="0" cellspacing="1" cellpadding="1" bgcolor="#CCCCCC">
			<tr bgcolor="#FFFFFF">
				<input type='hidden' name='xalert_1543' value='<?=mxlang('1543')?>'>
				<td><?=mxlang("590")?>: &nbsp;<input type='text' id="prd_txt_input" name='prd_txt_input' value='<?=$prd_txt_input?>' size="8" maxlength="10" onKeypress="return enterMove(event)">&nbsp;<input type='hidden' name='prd_hid_input'>&nbsp;
						<select name='cbo_filter' onchange='document.frm_invcnt_fltr.next.value=1;document.frm_invcnt_fltr.submit();'>
							<option value='0' <?=($cbo_filter==0)?'selected':''?>><?=mxlang("934")?></option>
							<option value='1' <?=($cbo_filter==1||($PPV==1&&$cbo_filter==''&&$page_doin==1))?'selected':''?>><?=mxlang("2893")?></option>
							<option value='2' <?=($cbo_filter==2||($PPV==0&&$cbo_filter==''&&$page_doin==1))?'selected':''?>><?=mxlang("1622")?></option>
						</select>
					<select name='prd_list_input'>
	<?
						if (pg_numrows($result)>0) {
							echo("<option value='--".strtolower(mxlang("1336"))."--' ".(("--".strtolower(mxlang("1336"))."--"==$prd_list_input)?'selected':'').">".mxlang("591")."</option>\n");
							for ($i=0;$i<pg_numrows($result);$i++) {
								$row_item = pg_fetch_row($result, $i);
/*								if ($row_item[4]==0) {
									$x_qoh 	= hitungqoh($row_item[0], $xloccd);
								}else{
									$x_qoh 	= hitung_pr_qoh($row_item[0], 'DDEPT');
								}	*/
								//if ($xloccd=='DDEPT' && $row_item[2]==2 && $row_item[3]==1) {
								//	$x_qoh += hitung_maxqoh_packet($row_item[0], $xloccd);
								//}
								//$x_qoh=0;
								$x_qoh 	= hitungqoh_basic(trim(strtoupper($row_item[0])), $xloccd);
								if ($x_qoh<=0)	{
									if (
										($xloccd=='DDEPT' && ($page_dist_sp==1 || $page_inv==1 || $page_sp==1)
										&& is_autoconvert($row_item[0]))
										||
										($xloccd=='DDEPT' && $page_doin)
										||
										(($priv=='2' || $priv=='0') && $page_sc==1)
										||
										(($priv=='2' || $priv=='0') && is_autoconvert($row_item[0]))/*nanang (29-01-2008)*/
										)
									{
										echo("<option value='$row_item[0]' ".(($row_item[0]==$prd_list_input)?'selected':'').">$row_item[0] - ".stripslashes($row_item[1])." - $x_qoh</option>\n");
									}
								}else{
									echo("<option value='$row_item[0]' ".(($row_item[0]==$prd_list_input)?'selected':'').">$row_item[0] - ".stripslashes($row_item[1])." - $x_qoh</option>\n");
								}

/*								if ($x_qoh>0 && $page_doin==0 && $page_dist_sp==0 && $page_inv==0 && $page_sp==0) {
									echo("<option value='$row_item[0]' ".(($row_item[0]==$prd_list_input)?'selected':'').">$row_item[0] - ".stripslashes($row_item[1])." - $x_qoh</option>\n");
								} else if ($xloccd=='DDEPT' &&
									($page_doin==1 || $page_dist_sp==1 || $page_inv==1 || $page_sp==1)
									) {
									echo("<option value='$row_item[0]' ".(($row_item[0]==$prd_list_input)?'selected':'').">$row_item[0] - ".stripslashes($row_item[1])." - $x_qoh</option>\n");
								}*/
							}
						}
	?>
					</select>&nbsp;
					<?=mxlang("227")?> : <input type='text' name='qty' value='<?=$qty?>' size='5' maxlength='6'>&nbsp;
					<input type='button' name='btn_add' value='<?=mxlang("465")?>' onClick='fadd_item(0);'>
					<input type='hidden' name='item_action' value=''>
					<input type='hidden' name='tmp_trivcd' value='<?=$tmp_trivcd?>'>
				</td>
			</tr>
		<table>
<?
		pg_free_result($result);
	}elseif ($xinput_prdcd_type==2) {
		$result = pg_exec($db,$querr);
?>
		<table width="100%" border="0" cellspacing="1" cellpadding="1" bgcolor="#CCCCCC">
			<tr bgcolor="#FFFFFF">
				<td><br>
					&nbsp;<?=mxlang("590")?> :&nbsp;<input type='text' id='prd_txt_input' name='prd_txt_input' size='8' maxlength='10' value='<?=$prd_txt_input?>' onKeypress="return enterMove(event)">
					&nbsp;<?=mxlang("227")?> : <input type='text' name='qty1' value='<?=$qty1?>' size='8' maxlength='10'>&nbsp;
					<input name="prdcd" type="hidden" id="prdcd" value="<?=$prdcd?>" >
					<input type='button' name='btn_add1' value='<?=mxlang("465")?>' onClick='fadd_item(0);'><br><br>
					&nbsp;<?=mxlang("590")?> :&nbsp;<select name='cbo_filter' onchange='document.frm_invcnt_fltr.next.value=1;document.frm_invcnt_fltr.submit();'>
							<option value='0' <?=($cbo_filter==0)?'selected':''?>><?=mxlang("934")?></option>
							<option value='1' <?=($cbo_filter==1||($PPV==1&&$cbo_filter==''&&$page_doin==1))?'selected':''?>><?=mxlang("2893")?></option>
							<option value='2' <?=($cbo_filter==2||($PPV==0&&$cbo_filter==''&&$page_doin==1))?'selected':''?>><?=mxlang("1622")?></option>
						</select>&nbsp;
					<select name='prd_list_input'>
	<?
						if (pg_numrows($result)>0) {
							//echo("<option value='--list--' ".(('--list--'==$prd_list_input)?'selected':'').">Select Product</option>\n");
							for ($i=0;$i<pg_numrows($result);$i++) {
								$row_item = pg_fetch_row($result, $i);
								//$x_qoh 	= hitungqoh($row_item[0], $txt_msid);
								if ($row_item[4]==0) {
									$x_qoh 	= hitungqoh($row_item[0], $xloccd);
								}else{
									$x_qoh 	= hitung_pr_qoh($row_item[0], $xloccd);
								}
								if ($x_qoh>0 && $page_doin==0) {
									echo("<option value='$row_item[0]' ".(($row_item[0]==$prd_list_input)?'selected':'').">$row_item[0] - ".stripslashes($row_item[1])." - $x_qoh</option>\n");
								} else if ($xloccd=='DDEPT' && $page_doin==1) {
									echo("<option value='$row_item[0]' ".(($row_item[0]==$prd_list_input)?'selected':'').">$row_item[0] - ".stripslashes($row_item[1])." - $x_qoh</option>\n");
								}
							}
						}
	?>
					</select>
					&nbsp;<?=mxlang("227")?> : <input type='text' name='qty2' value='<?=$qty2?>' size='5' maxlength='6'>&nbsp;
					<input type='button' name='btn_add2' value='<?=mxlang("465")?>' onClick='fadd_item(1);'><br><br>
					<input type='hidden' name='item_action' value=''>
					<input type='hidden' name='prd_hid_input'>
					<input type='hidden' name='qty' value='1'>
					<input type='hidden' name='tmp_trivcd' value='<?=$tmp_trivcd?>'>
				</td>
			</tr>
		<table>
<?
		pg_free_result($result);
	}elseif ($xinput_prdcd_type==3) {
	} //else if $xinput_prdcd_type
}

function is_warehouse($id) {
        $query = "select warehouse_id from distibution_setup where warehouse_id='$id' ";
        if ($GLOBALS["db2"]->doQuery($query)->isFound()) {
          return true;
        }
        return false;
}

function js_input_prd4() {
	global $xloccd, $xreg, $db, $xtype_pv, $xtype_kit, $region, $default_cnt, $xtrx_type,$seller_ty,$debug,$price_sc,$txt_key,
		$txt_ship, $xsnh_use_man,$opnm, $deduct_prod_item,$dotype,$xmodule_name,$cursign,$kitMustCheck,$txt_br,$xdo, $state;
	if ($xtype_pv=='') $xtype_pv='all';
?>
	var sc_trx = 0;
<?
	$sc = check_stockist_ex($xloccd);
	if($sc==1) { ?> sc_trx=1; <? } ?>
	
	var win_items;
	var items_str=new Array();
	var items_prdcd=new Array();
	var items_qty=new Array();
	var itemsqty=new Array();
	var items_dp=new Array();
	var items_pv=new Array();
	var items_bv=new Array();
	var items_qoh=new Array();
	var items_kit=new Array();
	var items_bts=new Array();
	var items_weight=new Array();
	var items_remark=new Array();
	var items_newprd= new Array();
	var dotype='<?=$dotype?>';
	var xmodule_name='<?=$xmodule_name?>';
	
	var items_pack=new Array();
	var items_prdcdpack=new Array();
	var items_packqty=new Array();
	var items_prdcdpack2=new Array();
	var items_packqty2=new Array();
	var win_para = '';

	function setVisible(obj, bool){
		if(typeof obj == "string")
			obj = document.getElementById(obj);
		if(bool == false){
			if(obj.style.visibility != 'hidden');
				obj.style.visibility = 'hidden';
			} else {
			if(obj.style.visibility != 'visible');
			obj.style.visibility = 'visible';
		}
	}

	function fview_item(xreg,choice) {
		var shortcut 	 = document.frm_invcnt_fltr;
		var dimensi="height=500,width=600,left=300,top=25,resizable=yes,scrollbars=yes";
		var location_id = "<?=$xloccd?>";

		<? if (strpos($_SERVER['SCRIPT_NAME'],"registration_cart.php")!==FALSE) { ?>
			if ($("cboSeller")===null) {
				if ($("cboBranch")===null)
					location_id = $F("cboSCenter");
				else
					location_id = $F("cboBranch");
			}
			else {
				if ($F("cboSeller")=="ms")
					location_id = $F("cboBranch");
				else
					location_id = $F("cboSCenter");
			}
		<? }
		$trxtp="";
		if (func_num_args()==2)
			$trxtp=func_get_arg(1); 
		if($trxtp=="SR")
		{
		?>
			var url="../product_ordering/msprd_lst.php?staffname="+$('txt_key').value+"&xloccd="+location_id+"&xreg="+xreg+"&pil="+choice+"&xtype_pv=<?=$xtype_pv?>&xtype_kit=<?=$xtype_kit?>&cn_id=<?=$region?>";
		<?
		}
		else
		{
		?>
			var url="../product_ordering/msprd_lst.php?xloccd="+location_id+"&xreg="+xreg+"&pil="+choice+"&xtype_pv=<?=$xtype_pv?>&xtype_kit=<?=$xtype_kit?>&membercode="+$F('membercode');
		<?
		}
		?>

		if (arguments.length>2)
		  url=url+"&trxtype="+arguments[2];
		
		if (arguments.length==4)
		  url=url+"&buyer="+arguments[3];

		if (win_items!=null) {
			if (!win_items.closed) {
				$('open_win').value=1;
				win_items.location.replace (url);
				win_items.focus();
			}else{
				if($('open_win').value!=1) 
					$('open_win').value=1;
				win_items = window.open(url,"msprd_list",dimensi);
			}
		}else{
			if($('open_win').value=='' || ($('open_win').value==0 && win_items==null)) {
				$('open_win').value=1;
				win_items = window.open(url,"msprd_list",dimensi);
			} 
		}
	}
	
	function fwin_close() {
		if (win_items!=null) {
			if (!win_items.closed) {
				win_items.close();
			}
		}
	}
	var numberOfKIT_prdcd = 0;
	function fdel_item4(xprdcd) {
		var count_item=items_prdcd.length;
		var xrow=0;
		var xtmp_array='';
		var xtemp='';
		if (xmodule_name=='doc') {
				xtemp=document.frm_invcnt_fltr.tmp_array.value;
				if(xtemp!='1')
				xtmp_array=document.frm_invcnt_fltr.tmp_array.value.split('|');
		}
		if (items_prdcd.length>0) {
			for (i=0;i < count_item ; i++) {
				
				if (items_prdcd[i]==xprdcd) {
					<? if ($kitMustCheck) { ?>
							if (count_item==1) {
								$('cboSeller').enable();
								if ($('cboSCenter')!==null)
									$('cboSCenter').enable();
								if ($('cboBranch')!==null)
									$('cboBranch').enable();
							}
							if (items_kit[i]==1)
								numberOfKit -= items_qty[i];

					<? } ?>
					xtmp_index=i;
					//alert("number of kit before delete :"+numberOfKIT_prdcd);
					if (items_kit[i]==1){
						numberOfKIT_prdcd -= items_qty[i];
					}

					items_qty[i]=0;
					//alert("number of kit after delete :"+numberOfKIT_prdcd);
				}

				if (xmodule_name=='doc' && items_prdcd[i]==xprdcd) {
					xtmp_array[i]='f';
				}
				if (xmodule_name=='wrgrn' || xmodule_name=='brgrn') {
					if (items_qty[i]!=0) {
						items_remark[i]=$('txt_remark_'+items_prdcd[i]).value;
					}
				}



				
			}
			if (xmodule_name=='doc') {
					var baris = '';
					if(xtemp!='1'){
						for (a=0;a < xtmp_array.length;a++){
							if(baris=="")
								baris = xtmp_array[a];
							else
								baris += "|"+xtmp_array[a];
						}
						document.frm_invcnt_fltr.tmp_array.value = baris;
					}
			}

		}

		if(sc_trx==1) {
			if(items_prdcdpack2.length>0) {
				for (i=0;i < items_prdcdpack2.length;i++) {
					if (items_prdcdpack2[i]==xprdcd) {
						items_packqty2[i] = 0;
					}
				}
			}
		}

		fdisplay_items();
		if(xmodule_name=='doc') fill_qty();
		if (xmodule_name=='wrgrn' || xmodule_name=='brgrn') {
			if (items_prdcd.length>0) {
				for (i=0;i < items_prdcd.length;i++) {
					if (items_qty[i]!=0) {
						//document.getElementById("item_list2").innerHTML+=i+' '+items_prdcd[i]+' '+items_remark[i]+'<br>';
						$('txt_remark_'+items_prdcd[i]).value=items_remark[i];
					}
				}
			}
		}
		//alert("numberOfKIT_prdcd currently:"+numberOfKIT_prdcd);
		<?
		if($xtrx_type == 'cb'){
		?>


			$('no_Of_KITprd').value = numberOfKIT_prdcd;

			if ((numberOfKIT_prdcd<1) && $('isShow_chk_KITredem').value=="1"){
				$('Show_chk_KITredem').hide();
				$('chk_KIT_self_redeem').checked = false;
			}

		<?
		}
		?>
	}

	function fadding_item(xprdcd,xprdnm,xqty,xpv,xbv,xdp,xcp,xqoh,xkit,xbts,xpack,xweight,sum,stfflmt) {
	<?
	if($kitMustCheck) {
	?>
		$('cboSeller').disable();
		if ($('cboSCenter')!==null)
			$('cboSCenter').disable();
		if ($('cboBranch')!==null)
			$('cboBranch').disable();
			
		if (xkit==1)
			numberOfKit += parseInt(xqty);
	<?
	}
	?>
	
	var shortcut = document.frm_invcnt_fltr;
	var xmode='';
	var xtmp_index=0;
	var xtmp_qty=0;
	var xtmp_qty2=0;
	var com_qty=0;
	var count_item=items_prdcd.length;
	var count_itempack=items_prdcdpack.length;
	var count_itempack2=items_prdcdpack2.length;
	xmode='insert';

	var listed_prdcd=new Array();
	var listed_prdcdqty=new Array();
	var abc=0;

	var newprdfl = 0;
	for (ab=0;ab < xarr_items.length; ab++) {
		var arr_split=xarr_items[ab].split("|");
		if (arr_split[0]==xprdcd) 
			newprdfl = arr_split[14];
	}
	items_newprd.push(newprdfl);
			
	if (xkit==1){
		numberOfKIT_prdcd += parseInt(xqty);
		<?
		if($xtrx_type == 'cb'){
		?>
			$('no_Of_KITprd').value = numberOfKIT_prdcd;
		<?
		}
		?>
	}
	if (sc_trx==1) {
		if (items_prdcdpack2.length>0) {
			for (i=0;i < items_prdcdpack2.length;i++) {
				<? if($opnm=="nanang") { ?>alert('checking ['+i+']:' + items_prdcdpack2[i] +'=='+ xprdcd);<? } ?>
				if (items_prdcdpack2[i]==xprdcd) {
					xtmp_qty = parseInt(items_packqty2[i]) + parseInt(xqty);
					if(parseInt(xtmp_qty)>parseInt(xqoh)){
						alert("Fail to add "+xqty+" unit(s) of "+xprdcd+"\n\n Total QTY currently in the list : "+items_packqty2[i]+"\n QOH Available :"+xqoh);
						return false;
					}
				}
			}
		}
	}

	if (items_prdcd.length>0) {
		for (i=0;i < items_prdcd.length;i++) {
			abc++;
			if(items_pack[i]==1 && items_qty[i]==0) {
				for (z=0;z < items_prdcdpack.length;z++) {
					if(items_qty[i]!=items_packqty[z]) {
						items_packqty[z] = parseInt(items_qty[i]);
					}
				}
			}
			
			if (items_prdcd[i]==xprdcd) {
				xtmp_index=i;
				xtmp_qty	= items_qty[i];
				xmode='update';
			}
			
			if(items_qty[i]>0) {
			if(items_pack[i]==1) {
				for (ie=0;ie < xarr_items.length; ie++) {
					var arr_split=xarr_items[ie].split("|");
					if (arr_split[0]==items_prdcd[i]) {
						var arr_pack=xpack_prditems[ie].split("|");
						var qty_pack=xpack_prdqty[ie].split("|");
						for (k=0;k < arr_pack.length; k++) {
							if(listed_prdcd.length>0) {
								var add_flag = 0;
								for (l=0;l < listed_prdcd.length; l++) {
									if(arr_pack[k]==listed_prdcd[l]){
										listed_prdcdqty[l] = parseInt(listed_prdcdqty[l]) + parseInt(qty_pack[k]*items_qty[i]);
										add_flag = 1;
									}
								}
								if(add_flag==0) {
									listed_prdcd[l] = arr_pack[k];
									listed_prdcdqty[l] = parseInt(qty_pack[k]*items_qty[i]);
								}
							} else {
								listed_prdcd[abc-1] = arr_pack[k];
								listed_prdcdqty[abc-1] = parseInt(qty_pack[k]*items_qty[i]);
							}
						}
					}
				}
				
			} else {
				if(listed_prdcd.length>0) {
					var add_sign = 0;
					for (l=0;l < listed_prdcd.length; l++) {
						if(items_prdcd[i]==listed_prdcd[l]){
							listed_prdcdqty[l] = parseInt(listed_prdcdqty[l]) + parseInt(items_qty[i]);
							add_sign = 1;
						}
					}
					if(add_sign==0) {
						listed_prdcd[l+1] = items_prdcd[i];
						listed_prdcdqty[l+1] = items_qty[i];
					}
				} else {
					listed_prdcd[abc-1] = items_prdcd[i];
					listed_prdcdqty[abc-1] = items_qty[i];
				}
			}
			}
		}
	}

	<? if($opnm=="nanang") {?>
		if(listed_prdcd.length>0) {
			for (c=0;c < listed_prdcd.length; c++) {
				alert('listed prdcd ' + c + ' : ' + listed_prdcd[c] + ' , QTY = ' + listed_prdcdqty[c]);
			}
		}
		alert('prdcd in  = '+ xprdcd + ', Qoh : ' + xqoh);
		alert('mode = '+ xmode);
	<? } ?>

	if(xpack==1){
		for (ix=0;ix < xarr_items.length; ix++) {
			var arr_split=xarr_items[ix].split("|");
			
			if (arr_split[0]==xprdcd) {
				if(xpack_prditems[ix] != undefined) {
				<? if($opnm=="nanang") { ?>alert('product items ' + xpack_prditems[ix]); <? } ?>
				var arr_pack=xpack_prditems[ix].split("|");
				var qty_pack=xpack_prdqty[ix].split("|");

				for (z=0;z < arr_pack.length; z++) {
					count_itempack++;
					<? if($opnm=="nanang") { ?>alert('Check List Prdcd :' + listed_prdcd.length); <? } ?>
					for (d=0;d < listed_prdcd.length; d++) {
						if(listed_prdcd[d]==arr_pack[z]) {

							var prdcdqoh = 0;
							if (items_prdcd.length>0) {
								for (i=0;i < items_prdcd.length;i++) {
									if(arr_pack[z]==items_prdcd[i]){
										prdcdqoh = items_qoh[i];
									}
								}
							}

							if(prdcdqoh==0) {
								for (cb=0;cb < xarr_items.length; cb++) {
									var main_prd=xarr_items[cb].split("|");
									if (main_prd[0]==arr_pack[z]) {
										prdcdqoh = main_prd[6];
									}
								}
							}

							<? if($opnm=="nanang") { ?>alert('Checking tot qty :' + listed_prdcdqty[d]+' + ('+qty_pack[z] + '*'+xqty+')>'+prdcdqoh);<? } ?>
							if (parseInt(listed_prdcdqty[d])+parseInt(qty_pack[z]*xqty)>parseInt(prdcdqoh)) {
								alert("Fail to add "+xqty+" unit(s) of "+xprdcd+" containing "+listed_prdcd[d]+"\n\n Total QTY currently in the list : "+listed_prdcdqty[d]+"\n QOH Available :"+prdcdqoh);
								return false;
							} else {
								items_prdcdpack[count_itempack-1] = arr_pack[z];
								items_packqty[count_itempack-1] = parseInt(qty_pack[z]*xqty); 
							}
						}
					}
					if (listed_prdcd.length<1) {
						var prdcdqoh = 0;
						<? if($opnm=="nanang") { ?>alert('tot items_prdcd :' + items_prdcd.length); <? } ?>
						if (items_prdcd.length>0) {
							for (i=0;i < items_prdcd.length;i++) {
								if(arr_pack[z]==items_prdcd[i]){
									prdcdqoh = items_qoh[i];
								}
							}
						} 

						if(prdcdqoh==0) {
							for (ib=0;ib < xarr_items.length; ib++) {
								var main_prd=xarr_items[ib].split("|");
								if (main_prd[0]==arr_pack[z]) {
									prdcdqoh = main_prd[6];
								}
							}
						}
						if (parseInt(qty_pack[z]*xqty)>parseInt(prdcdqoh)) {
							alert("Fail to add "+xqty+" unit(s) of "+xprdcd+" containing "+arr_pack[z]+"\n\n QOH Available :"+prdcdqoh);
							return false;
						}
					} else {
						var main_prdcdqoh = 0;
						for (ic=0;ic < xarr_items.length; ic++) {
							var main_prdcd=xarr_items[ic].split("|");
							if (main_prdcd[0]==arr_pack[z]) {
								main_prdcdqoh = main_prdcd[6];
							}
						}
						if (parseInt(qty_pack[z]*xqty)>parseInt(main_prdcdqoh)) {
							alert("Fail to add "+xqty+" unit(s) of "+xprdcd+" containing "+arr_pack[z]+"\n\n QOH Available :"+main_prdcdqoh);
							return false;
						}
					}
				}
									
				} else { 
					alert('This product does not have items. Please contact administrator !');
					return false;
				}
			}
		}
	}
		
	if (xmode=='insert') {
		var count_item=items_str.length;

		var addqtypack = 0;
		for (b=0;b < listed_prdcd.length;b++) {
			if(listed_prdcd[b]==xprdcd) {
				addqtypack = parseInt(listed_prdcdqty[b]);
			}
		}
		<?if($opnm=="nanang") { ?>alert('check : ' + addqtypack + '+' + xqty +'>' + xqoh);<? } ?>
		if (parseInt(addqtypack)+parseInt(xqty)>parseInt(xqoh)) {	
		//if (parseInt(xqty)>parseInt(xqoh)) {
			alert("Fail to add "+xqty+" unit(s) of "+xprdcd+" \n\n Total QTY currently in the list :0\n QOH Available :"+xqoh);
			return false;
		}

		if(dotype!='doc') {
			<?
			$trxtp="";
			if (func_num_args()==2)
				$trxtp=func_get_arg(1); 
			if($trxtp=="SR") {
			?>
				if(!isNaN(sum)) {
					var sum2=parseInt(stfflmt-sum);
				}
				else {
					var sum2=parseInt(stfflmt);
				}
				if(parseInt(xqty)>parseInt(sum2)) {
					if(parseInt(stfflmt)>0) {
						alert('Can not continue process, staff limit is '+stfflmt+' for product '+xprdcd+'');
						return false;
					}
					else {
						var xstring ="<table width='100%' border='0' cellspacing='0' cellpadding='1' bgcolor='#cccccc'>";
						if(newprdfl == 1){
							xstring += "<tr bgcolor='yellow' valign='top'>";
							if(flag_newprd==0)
								flag_newprd = 2;
							else if(flag_newprd!=0 && flag_newprd!=2)
								flag_newprd = 3;
						} else {
							xstring += "<tr bgcolor='#FFFFFF' valign='top'>";
							if(flag_newprd==0)
								flag_newprd = 1;
							else if(flag_newprd!=0 && flag_newprd!=1)
								flag_newprd = 3;
						}
						document.frm_invcnt_fltr.newprd_flag.value=flag_newprd;
						xstring +="<td width='10%' align='left'>"+xprdcd+" </td>"+
							"<td align='left'>"+xprdnm;
							if (xmodule_name=='wrgrn' || xmodule_name=='brgrn') {
								xstring+="<br><br><?=mxlang("228")?>: <br><TEXTAREA ID='txt_remark_"+xprdcd+"' NAME='txt_remark_"+xprdcd+"' ROWS='2' COLS='70'></TEXTAREA>";
							}
						xstring+="</td>";
						if (xmodule_name!='wrgrn' && xmodule_name!='brgrn') {
							xstring +=
								"<td width='11%' align='right'>"+"<?//=$cursign?> "+digit_format(xpv,2)+"</td>"+
								"<td width='11%' align='right'>"+"<?//=$cursign?> "+digit_format(xbv,2)+"</td>";
						}
						xstring +=
							"<td width='11%' align='right'>"+"<?//=$cursign?> "+digit_format(xdp,2)+"</td>"+
							"<td width='6%' align='right'>"+xqoh+"</td>"+
							"<td width='7%' align='right'>"+xqty+"</td>"+
							"<td width='5%' align='center'><a href=\"javascript:;\" onclick=\"fdel_item4('"+xprdcd+"');\"><?=ucfirst(strtolower(mxlang("1195")))?></a></td>"+
							"</tr></table>";
					}
				}
				else {
					var xstring =
						"<table width='100%' border='0' cellspacing='0' cellpadding='1' bgcolor='#cccccc'>";
					if(newprdfl == 1){
						xstring += "<tr bgcolor='yellow' valign='top'>";
						if(flag_newprd==0)
							flag_newprd = 2;
						else if(flag_newprd!=0 && flag_newprd!=2)
							flag_newprd = 3;
					} else {
						xstring += "<tr bgcolor='#FFFFFF' valign='top'>";
						if(flag_newprd==0)
							flag_newprd = 1;
						else if(flag_newprd!=0 && flag_newprd!=1)
							flag_newprd = 3;
					}
					document.frm_invcnt_fltr.newprd_flag.value=flag_newprd;
					xstring+="<td width='10%' align='left'>"+xprdcd+" </td>"+
						"<td align='left'>"+xprdnm;
					if (xmodule_name=='wrgrn' || xmodule_name=='brgrn') {
						xstring+="<br><br><?=mxlang("228")?>: <br><TEXTAREA ID='txt_remark_"+xprdcd+"' NAME='txt_remark_"+xprdcd+"' ROWS='2' COLS='70'></TEXTAREA>";
					}
					xstring+="</td>";
					if (xmodule_name!='wrgrn' && xmodule_name!='brgrn') {
						xstring +=
							"<td width='11%' align='right'>"+"<?//=$cursign?> "+digit_format(xpv,2)+"</td>"+
							"<td width='11%' align='right'>"+"<?//=$cursign?> "+digit_format(xbv,2)+"</td>";
					}
					xstring +=
						"<td width='11%' align='right'>"+"<?//=$cursign?> "+digit_format(xdp,2)+"</td>"+
						"<td width='6%' align='right'>"+xqoh+"</td>"+
						"<td width='7%' align='right'>"+xqty+"</td>"+
						"<td width='5%' align='center'><a href=\"javascript:;\" onclick=\"fdel_item4('"+xprdcd+"');\"><?=ucfirst(strtolower(mxlang("1195")))?></a></td>"+
						"</tr></table>";
				}
			<?
			}
			else {
			?>
				var xstring = "<table width='100%' border='0' cellspacing='0' cellpadding='1' bgcolor='#cccccc'>";
				if(newprdfl == 1){
					xstring += "<tr bgcolor='yellow' valign='top'>";
					if(flag_newprd==0)
						flag_newprd = 2;
					else if(flag_newprd!=0 && flag_newprd!=2)
						flag_newprd = 3;
				} else {
					xstring += "<tr bgcolor='#FFFFFF' valign='top'>";
					if(flag_newprd==0)
						flag_newprd = 1;
					else if(flag_newprd!=0 && flag_newprd!=1)
						flag_newprd = 3;
				}
				document.frm_invcnt_fltr.newprd_flag.value=flag_newprd;
				xstring += "<td width='10%' align='left'>"+xprdcd+" </td>"+"<td align='left'>"+xprdnm;	
				if (xmodule_name=='wrgrn' || xmodule_name=='brgrn') {
					xstring+="<br><br><?=mxlang("228")?>: <br><TEXTAREA ID='txt_remark_"+xprdcd+"' NAME='txt_remark_"+xprdcd+"' ROWS='2' COLS='70'></TEXTAREA>";
				}
				xstring+="</td>";
				if (xmodule_name!='wrgrn' && xmodule_name!='brgrn') {
					xstring +=
						"<td width='11%' align='right'>"+"<?//=$cursign?> "+digit_format(xpv,2)+"</td>"+
						"<td width='11%' align='right'>"+"<?//=$cursign?> "+digit_format(xbv,2)+"</td>";
				}
				xstring +=
					"<td width='11%' align='right'>"+"<?//=$cursign?> "+digit_format(xdp,2)+"</td>"+
					"<td width='6%' align='right'>"+xqoh+"</td>"+
					"<td width='7%' align='right'>"+xqty+"</td>"+
					"<td width='5%' align='center'><a href=\"javascript:;\" onclick=\"fdel_item4('"+xprdcd+"');\"><?=ucfirst(strtolower(mxlang("1195")))?></a></td>"+
					"</tr></table>";
			<?
			}
			?>
		}
		else {
			var xstring = "<table width='100%' border='0' cellspacing='0' cellpadding='1' bgcolor='#cccccc'>";
			if(newprdfl == 1) {
				xstring += "<tr bgcolor='yellow' valign='top'>";
				if(flag_newprd==0)
					flag_newprd = 2;
				else if(flag_newprd!=0 && flag_newprd!=2)
					flag_newprd = 3;
			} else {
				xstring += "<tr bgcolor='#FFFFFF' valign='top'>";
				if(flag_newprd==0)
					flag_newprd = 1;
				else if(flag_newprd!=0 && flag_newprd!=1)
					flag_newprd = 3;
			}
			document.frm_invcnt_fltr.newprd_flag.value=flag_newprd;
			xstring += "<td width='10%' align='left'>"+xprdcd+"</td>";			
			xstring += "<td align='left'>"+xprdnm;
			if (xbts=='1') {
				xstring+="<br><br>"+frm_bts4(count_item,xprdcd,xqty);
			}
			xstring+="<td width='6%' align='right'>"+xqoh+"</td>";
			xstring+="</td>"+"<td width='7%' align='right'>"+xqty+"</td>"+
				"<td width='5%' align='center'><a href=\"javascript:;\" onclick=\"fdel_item4('"+xprdcd+"');\"><?=ucfirst(strtolower(mxlang("1195")))?></a></td>"+
				"</tr>";
			xstring+="</table>";
			//document.frm_invcnt_fltr.tot_item.value=count_item+1;
		}
		
		count_item++;
		items_str[count_item-1]=xstring;
		items_prdcd[count_item-1]=xprdcd;
		items_qty[count_item-1]=xqty;
		itemsqty[count_item-1]=xqty;
		items_dp[count_item-1]=xdp;
		items_pv[count_item-1]=xpv;
		items_bv[count_item-1]=xbv;
		items_qoh[count_item-1]=xqoh;
		items_kit[count_item-1]=xkit;
		items_bts[count_item-1]=xbts;
		items_weight[count_item-1]=xweight; 
		items_remark[count_item-1]='';
		items_pack[count_item-1]=xpack;

		if(sc_trx==1) {
			if(xpack==1){
				for (ix=0;ix < xarr_items.length; ix++) {
					var arr_split=xarr_items[ix].split("|");
					if (arr_split[0]==xprdcd) {
						count_itempack2++;
						items_prdcdpack2[count_itempack2-1] = xprdcd;
						items_packqty2[count_itempack2-1] = parseInt(xqty);  
					}
				}
			}
		}

	}
	else { //update
		var addqtypack = 0;
		for (b=0;b < listed_prdcd.length;b++) {
			if(listed_prdcd[b]==xprdcd) {
				addqtypack = parseInt(listed_prdcdqty[b]);
			}
		}
		<?if($opnm=="nanang") { ?>alert('check : ' + addqtypack + '+' + xqty +'>' + xqoh);<? } ?>
		if (parseInt(addqtypack)+parseInt(xqty)>parseInt(xqoh)) {
			alert("Fail to add "+xqty+" unit(s) of "+xprdcd+" \n\n Total QTY currently in the list : "+addqtypack+"\n QOH Available :"+xqoh);
			return false;
		}

		xtmp_qty = parseInt(xtmp_qty) - parseInt(xtmp_qty2) + parseInt(xqty);
		items_qty[xtmp_index]=xtmp_qty;
		itemsqty[xtmp_index]=xtmp_qty;
		items_dp[xtmp_index]=xdp;
		items_pv[xtmp_index]=xpv;
		items_bv[xtmp_index]=xbv;
		items_qoh[xtmp_index]=xqoh;
		items_kit[xtmp_index]=xkit;
		items_bts[xtmp_index]=xbts;
		items_pack[xtmp_index]=xpack;
		items_weight[xtmp_index]=xweight;

		if(sc_trx==1) {
			if(xpack==1){
				for (i=0;i < items_prdcdpack2.length;i++) {
					if (items_prdcdpack2[i]==xprdcd) {
						items_packqty2[i] = parseInt(items_packqty2[i]) + parseInt(xqty);
					}
				}
			}
		}
		
		if(dotype!='doc'){
			items_str[xtmp_index]= "<table width='100%' border='0' cellspacing='0' cellpadding='1' bgcolor='#cccccc'>";
			if(newprdfl == 1) {
				items_str[xtmp_index] += "<tr bgcolor='yellow' valign='top'>";
				if(flag_newprd==0)
					flag_newprd = 2;
				else if(flag_newprd!=0 && flag_newprd!=2)
					flag_newprd = 3;
			} else {
				items_str[xtmp_index] += "<tr bgcolor='#FFFFFF' valign='top'>";
				if(flag_newprd==0)
					flag_newprd = 1;
				else if(flag_newprd!=0 && flag_newprd!=1)
					flag_newprd = 3;
			}
			document.frm_invcnt_fltr.newprd_flag.value=flag_newprd;
			items_str[xtmp_index] += "<td width='10%' align='left'>"+xprdcd+"</td>"+ "<td align='left'>"+xprdnm;
			if (xmodule_name=='wrgrn' || xmodule_name=='brgrn') {
				items_str[xtmp_index]+="<br><br><?=mxlang("228")?>: <br><TEXTAREA ID='txt_remark_"+xprdcd+"' NAME='txt_remark_"+xprdcd+"' ROWS='2' COLS='70'>"+items_remark[xtmp_index]+"</TEXTAREA>";
			}
			items_str[xtmp_index]+="</td>";
			if (xmodule_name!='wrgrn' && xmodule_name!='brgrn') {
				items_str[xtmp_index]+=
					"<td width='11%' align='right'>"+"<?//=$cursign?> "+digit_format(xpv,2)+"</td>"+
					"<td width='11%' align='right'>"+"<?//=$cursign?> "+digit_format(xbv,2)+"</td>";
			}
			
			<?
			$trxtp="";
			if (func_num_args()==2)
				$trxtp=func_get_arg(1); 
			if($trxtp=="SR") {
			?>
				if(!isNaN(sum)) {
					var sum2=parseInt(stfflmt-sum);
				}
				else {
					var sum2=parseInt(stfflmt);
				}
				if(parseInt(xtmp_qty)>parseInt(sum2))
				{
					if(parseInt(stfflmt)>0) {
						alert('Can not continue process, staff limit is '+stfflmt+' for product '+xprdcd+'');
						xtmp_qty=xtmp_qty-xqty;
						items_qty[xtmp_index]=xtmp_qty;
						items_str[xtmp_index]+=
							"<td width='11%' align='right'>"+"<?//=$cursign?> "+digit_format(xdp,2)+"</td>"+
							"<td width='6%' align='right'>"+xqoh+"</td>"+
							"<td width='7%' align='right'>"+xtmp_qty+"</td>"+
							"<td width='5%' align='center'><a href=\"javascript:;\" onclick=\"fdel_item4('"+xprdcd+"');\"><?=ucfirst(strtolower(mxlang("1195")))?></a></td>"+
							"</tr></table>";
					}
					else {
						items_str[xtmp_index]+=
							"<td width='11%' align='right'>"+"<?//=$cursign?> "+digit_format(xdp,2)+"</td>"+
							"<td width='6%' align='right'>"+xqoh+"</td>"+
							"<td width='7%' align='right'>"+xtmp_qty+"</td>"+
							"<td width='5%' align='center'><a href=\"javascript:;\" onclick=\"fdel_item4('"+xprdcd+"');\"><?=ucfirst(strtolower(mxlang("1195")))?></a></td>"+
							"</tr></table>";
					}
				}
				else {
					items_str[xtmp_index]+=
						"<td width='11%' align='right'>"+"<?//=$cursign?> "+digit_format(xdp,2)+"</td>"+
						"<td width='6%' align='right'>"+xqoh+"</td>"+
						"<td width='7%' align='right'>"+xtmp_qty+"</td>"+
						"<td width='5%' align='center'><a href=\"javascript:;\" onclick=\"fdel_item4('"+xprdcd+"');\"><?=ucfirst(strtolower(mxlang("1195")))?></a></td>"+
						"</tr></table>";
				}
			<?
			}
			else {
			?>
				items_str[xtmp_index]+=
					"<td width='11%' align='right'>"+"<?//=$cursign?> "+digit_format(xdp,2)+"</td>"+
					"<td width='6%' align='right'>"+xqoh+"</td>"+
					"<td width='7%' align='right'>"+xtmp_qty+"</td>"+
					"<td width='5%' align='center'><a href=\"javascript:;\" onclick=\"fdel_item4('"+xprdcd+"');\"><?=ucfirst(strtolower(mxlang("1195")))?></a></td>"+
					"</tr></table>";
			<?
			}
			?>
		}
		else {
			items_str[xtmp_index]=
				"<table width='100%' border='0' cellspacing='0' cellpadding='1' bgcolor='#cccccc'>"+
				"<tr bgcolor='#FFFFFF' valign='top'>"+
				"<td width='10%' align='left'>"+xprdcd+"</td>";

			items_str[xtmp_index]+="<td align='left'>"+xprdnm;
			if (xbts=='1') {
				items_str[xtmp_index]+="<br><br>"+frm_bts4(xtmp_index,xprdcd,xtmp_qty);
			}
			items_str[xtmp_index]+="</td>";
			items_str[xtmp_index]+="<td width='6%' align='right'>"+xqoh+"</td>";
			items_str[xtmp_index]+="<td width='7%' align='right'>"+xtmp_qty+"</td>"+
				"<td width='5%' align='center'><a href=\"javascript:;\" onclick=\"fdel_item4('"+xprdcd+"');\"><?=ucfirst(strtolower(mxlang("1195")))?></a></td>"+
				"</tr></table>";
		}
	}

	fdisplay_items();

	if (xmodule_name=='wrgrn' || xmodule_name=='brgrn') {
		if (items_prdcd.length>0) {
			for (i=0;i < items_prdcd.length;i++) {
				if (items_qty[i]!=0) {
					//document.getElementById("item_list2").innerHTML+=i+' '+items_prdcd[i]+' '+items_remark[i]+'<br>';
					$('txt_remark_'+items_prdcd[i]).value=items_remark[i];
				}
			}
		}
	}

	<?
	if($xtrx_type == 'cb'){
	?>
		if ((xkit==1) && $('isShow_chk_KITredem').value=="1")
			$('Show_chk_KITredem').show();

	<?
	}
	?>

	}

	function fdisplay_items() {
		var shortcut = document.frm_invcnt_fltr;
		var dotype='<?=$dotype?>';
		var xtr = "";
		var xsumDP = 0;
		var xsumPV = 0;
		var xsumBV = 0;
		var xsumWeight = 0;
		var tmp_sumweight = 0;
		var icount=0;
		//var items_prdcd2= items_prdcd.sort();
		var xsnh_value=0; 
		
		var numberToFixed = 
		(function() {
			return toFixedString;
			
			function toFixedString(n, digits) {
				var unsigned = toUnsignedString(Math.abs(n), digits);
				return (n < 0 ? "-" : "") + unsigned;
			}
			
			function toUnsignedString(m, digits) {
				var t, s = Math.round(m * Math.pow(10, digits)) + "",
						start, end;
				if (/\D/.test(s)) { 
					return "" + m;
				}
				s = padLeft(s, 1 + digits, "0");
				start = s.substring(0, t = (s.length - digits));
				end = s.substring(t);
				if(end) {
					end = "." + end;
				}
				return start + end; // avoid "0."
			}
			/** 
			* @param {string} input: input value converted to string.
			* @param {number} size: desired length of output.
			* @param {string} ch: single character to prefix to s.
			*/
			function padLeft(input, size, ch) {
				var s = input + "";
				while(s.length < size) {
					s = ch + s;
				}
				return s;
			}
		})();

		
		var xprdcd_input = shortcut.prd_txt_input.value.replace(/^\s+|\s+$/, '');
		
		xprdcd_input = xprdcd_input.toUpperCase();
		var xfound=false;

		for (ix=0;ix < xarr_items.length; ix++) {
			var arr_split=xarr_items[ix].split("|");

			if (arr_split[0]==xprdcd_input) {
				xsfflmt =parseInt(arr_split[12]);
			}
		}

		flag_newprd = 0;
		for (i=0;i < items_str.length;i++) {
		//for (i=0;i < items_prdcd2.length;i++) {
			if (items_qty[i]!=0) {
				var qty=parseInt(items_qty[i]);
				icount++;
				xtr+=items_str[i];
				xsumDP+=parseFloat(items_qty[i])*parseFloat(items_dp[i]); 
				xsumPV+=parseFloat(items_qty[i])*parseFloat(items_pv[i]);
				xsumBV+=parseFloat(items_qty[i])*parseFloat(items_bv[i]);
				tmp_sumweight+=parseFloat(items_qty[i])*parseFloat(items_weight[i]);
				xsumWeight =numberToFixed(tmp_sumweight,2);
				
				if(items_newprd[i]==1) {
					if(flag_newprd==0)
						flag_newprd = 2;
					else if(flag_newprd!=0 && flag_newprd!=2)
						flag_newprd = 3;
				} else {
					if(flag_newprd==0)
						flag_newprd = 1;
					else if(flag_newprd!=0 && flag_newprd!=1)
						flag_newprd = 3;
				}
			}
		}
		document.frm_invcnt_fltr.newprd_flag.value=flag_newprd;
		
		//shortcut.txt_temp.value=items_prdcd2;
		if (icount==0) {
			xtr+="<table>";
			xtr+="<tr bgcolor='#FFFFFF'>";
			xtr+="<td align='center' colspan='8'><font color='#FF0000'><b><?=mxlang("1569")?></b></font></td>";
			xtr+="</tr>";
			xtr+="</table>";
			document.frm_invcnt_fltr.btnAdd.disabled=true;
			
		}
		
		document.getElementById("item_list").innerHTML=xtr;
		if(dotype!='doc'){
			if (xmodule_name!='wrgrn' && xmodule_name!='brgrn') {
				document.getElementById("jTotPV").innerHTML="<?//=$cursign?> "+digit_format(xsumPV, 2);
				document.getElementById("jTotBV").innerHTML="<?//=$cursign?> "+' '+digit_format(xsumBV, 2);
			}
			document.frm_invcnt_fltr.tot_item.value=items_str.length;
			document.getElementById("jTotDP").innerHTML="<?//=$cursign?> "+digit_format(xsumDP, 2);
		}

		
		//shortcut.txt_temp.value=items_prdcd2;
<?
		if ($txt_ship==1 && $xsnh_use_man==0) {
?>
		
		xsnh_value=fget_snhcost(xsumDP, shortcut.txt_shcost.value);
		shortcut.txt_shcost.value=digit_format(xsnh_value,2);
		$('spn_shcost').update(digit_format(xsnh_value,2));
		$('weight').update(xsumWeight);
		
<?
		} else {
?>
		xsnh_value=fget_pickup_snhcost(xsumDP);
		$('txt_shcost_pickup').value = digit_format(xsnh_value,2);
		$('pickup_fee').update(digit_format(xsnh_value,2));
<?
		}
?>
		if (icount>0) document.frm_invcnt_fltr.btnAdd.disabled=false;

	}

	var flag_newprd = 0;
	var xarr_items=new Array();
	var xpack_prditems=new Array();
	var xpack_prdqty=new Array();
<?
	
	$region = (empty($region))?"US":$region;
	
	$sqlku = "select def_currency from mlm_setup where id_cnt='$region'";
	$resku = pg_exec($db,$sqlku);
	if(pg_numrows($resku)>0) {
		list($cursign) = pg_fetch_row($resku,0);
		if($cursign=='') $cursign="USD";
	} else $cursign="USD";

	$trxtp="";
	if (func_num_args()==2)
	  $trxtp=func_get_arg(1); 
	if ($xdo=='CR') $trxtp = $xdo;
	
	switch($trxtp) {
	  case "CR"	:
				  $price = "0,0,d.cp,d.cp";
				  break;
	  case "SR"	:
				  $price = "0,0,d.sp,d.sp";
				  break;
	  case "MR"	:
				  $price = "d.pv,d.bv,d.dp,d.dp";
				  break;
	  case "SCR":
				  //$price = "d.pv,d.bv,d.scp,d.scp";
				  $price_sc = (empty($price_sc))?"d.scp":$price_sc;
				  $price = "d.pv,d.bv,$price_sc,$price_sc";
				  break;
	  default	:
				  $price = "d.pv,d.bv,d.dp,d.cp";
				  break;
	}
	
	$xkit_sql='';
	if ($xtype_pv=='pv') $xpv_sql='and d.pv>0 ';
	else if ($xtype_pv=='nonpv') $xpv_sql='and d.pv<=0 ';

	$xkit_sql='';
	if ($xtype_kit=='nonkit') {
		$xkit_sql=" and a.kit <> '1' ";
	}
	
	$prd_sql="select a.prdcd,a.prdnm,b.prdgroup_id,b.dp,coalesce(b.cp,0) as cp,coalesce(b.sp,0) as sp,coalesce(b.pv,0) as pv,
		coalesce(b.bv,0) as bv, coalesce(c.qoh,0) as qoh from msprd a left join msprd_price b on a.prdcd=b.prdcd 
		left join inloc c on a.prdcd=c.prdcd and c.loccd='$xloccd'
		where b.prdgroup_id in (select prdgroup_id from msprd_stgroup_info where (st_id='$state' or st_id='all')) and 
		b.dp is not null and c.qoh>0 order by a.prdcd;";
	  //and c.qoh>0
 	if ($opnm=="nanang") echo "/*\n",$prd_sql,"; -- $trxtp */\n";
	$prd_res = pg_exec($db, $prd_sql);
	for ($i=0;$i<pg_num_rows($prd_res);$i++) {
		$prd_row = pg_fetch_row($prd_res, $i);
		$xqoh = $prd_row[9];
		
		$flnew ="";
		$ares = pg_exec($db,"select new_prd from msprd_condition where prdcd='{$prd_row[0]}' and prdgroup_id in (select prdgroup_id from msprd_stgroup_info where st_id='$state' or st_id='all')");
		if(pg_numrows($ares)>0) list($flnew) = pg_fetch_row($ares,0);
		$newprdflag = ($flnew=='t')?1:0;

		echo("    xarr_items[$i]=\"$prd_row[0]|".addslashes($prd_row[1])."|$prd_row[4]|$prd_row[5]|$prd_row[6]|$prd_row[7]|$xqoh|$x_kit|$x_bts|$x_pack|$prd_row[10]|$prd_row[11]|$prd_row[12]|$prd_row[13]|$newprdflag\";\n");

		if($x_pack==1) {
		$querrvfx="select b.prdcd,a.inv_qty from msprd_items a,msprd b where a.prdcd='".trim($prd_row[0])."' and b.prdcd=a.inv_prdcd";
		$resultvfx=pg_exec($db,$querrvfx);
		$brg_pack=""; $qty_pack="";
		for($mx=0;$mx<pg_numrows($resultvfx);$mx++) { 
			$rowkititem=pg_fetch_row($resultvfx,$mx);
			if($mx==0){ $brg_pack.=""; $qty_pack.=""; } else { $brg_pack.="|"; $qty_pack.="|"; }
			$brg_pack .= "$rowkititem[0]";
			$qty_pack .= "$rowkititem[1]";
		}
		if($brg_pack!="") echo (" xpack_prditems[$i]=\"$brg_pack\";\n");
		if($qty_pack!="") echo (" xpack_prdqty[$i]=\"$qty_pack\";\n");
		}	
			
	}
	pg_free_result($prd_res);
?>

	function fadd_item4() {
		//alert("<?="{$date1}";?>");
		var shortcut = document.frm_invcnt_fltr;
		if(shortcut.qty.value=='' || shortcut.qty.value==0 || shortcut.qty.value<0) {
			alert('Please enter QTY !');
			shortcut.qty.focus();
			return false;
		}
		if (isNaN(shortcut.qty.value)) {
			alert('Invalid qty !');
			shortcut.qty.value=0;
			return false;
		}
		
		var xprdcd_input = shortcut.prd_txt_input.value.replace(/^\s+|\s+$/, '');
		xprdcd_input = xprdcd_input.toUpperCase();
		var xfound=false; 

		for (ix=0;ix < xarr_items.length; ix++) {
			var arr_split=xarr_items[ix].split("|");
			
			var product_code=arr_split[0].toUpperCase();

			if (product_code==xprdcd_input) { 	
				xprdcd	=arr_split[0];
				xprdnm	=arr_split[1];
				xpv	=arr_split[2];
				xbv	=arr_split[3];
				xdp	=arr_split[4];
				xcp	=arr_split[5];
				xqoh	=arr_split[6];
				xkit	=arr_split[7];
				xbts	=arr_split[8];
				xpack	=arr_split[9];
				xsfflmt =parseInt(arr_split[12]);
				sum =parseInt(arr_split[13]);
				xqty = shortcut.qty.value;
				
				if(arr_split[11]=="")	xweight=0;
				else xweight = arr_split[11];

				if (xqoh=='') xqoh=0;
				<?
				if($trxtp=="SR")
				{
				?>
					xqty = shortcut.qty.value;
					if(xsfflmt>0)
					{
						var url = "../module/get_stafflmt.php";
						var params="staff_name="+$('txt_key').value+"&prdcd="+xprdcd_input+"&loccd="+"<?=$xloccd?>"+"&cn_id="+"<?=$region?>";

						new Ajax.Request(url, {
							parameters:params,
							onSuccess: function(transport) {
							if (transport.responseText!='null'){
								var dt=transport.responseText.evalJSON();
									var sum=dt["sum"];
									var prdcd=dt["prdcd"];
									var stafflimit=dt["staff_limit"];

									if(isNaN(sum)) {
										sum = 0;
									}
									
									if(parseInt(sum)+ parseInt(xqty)> parseInt(stafflimit)) {
										alert('Can not continue process, staff limit is '+stafflimit+' for product '+xprdcd_input+'');
										return false;
									}
									else {	fadding_item(xprdcd,xprdnm,xqty,xpv,xbv,xdp,xcp,xqoh,xkit,xbts,xpack,xweight,sum,xsfflmt);
									}
							}
							}
						});
					}
					else {	fadding_item(xprdcd,xprdnm,xqty,xpv,xbv,xdp,xcp,xqoh,xkit,xbts,xpack,xweight,sum, xsfflmt);
					}
					xfound=true;
				<?
				}
				else {
				?>
					if(!$F('membercode').blank()) {
						var url = "../module/get_member_product_purchase.php";
						var params = "code="+$('membercode').value+"&prdcd="+xprdcd_input;

						new Ajax.Request(url, {
						parameters:params,
						onSuccess: function(transport) {
							if (transport.responseText!='null') {
								var dt = transport.responseText.evalJSON();
								var code = dt["code"];
								var prdcd = dt["prdcd"];
								var product_val = dt["product_val"];
								fadding_item(xprdcd,xprdnm,xqty,xpv,xbv,xdp,xcp,xqoh,xkit,xbts,xpack,xweight,0,0);
							}
							else {
								if(xqoh<=0) {
									fadding_item(xprdcd,xprdnm,xqty,xpv,xbv,xdp,xcp,xqoh,xkit,xbts,xpack,xweight,0,0);
								}
								else {
									alert('Sorry, this member is not allowed to purchase product '+xprdcd_input+'');
									return false;
								}
							}
						}
						});
					}
					else {
						fadding_item(xprdcd,xprdnm,xqty,xpv,xbv,xdp,xcp,xqoh,xkit,xbts,xpack,xweight,0,0);
					}
					xfound=true;
				<?
				}
				?>
			}
		}
		
		if(xfound==false){
			alert('<?=mxlang("1582")?> '+xprdcd_input+' <?=mxlang("1583")?> <? if($xloccd=="DDEPT") echo "Warehouse"; else echo "$xloccd";?>');
		}
	}
	
	function fget_snhcost(xsrc_total, xsnh_charge) {
		var xresult = 0;
		if(parseFloat(xsrc_total)>0) {
<?
		$xship_sql = "select * from new_snh_setup ship_setup left join v_ms_sc_region seller_reg on ship_setup.cn_id=seller_reg.region 
		where seller_reg.code='$xloccd' and location in ('ALL','$seller_ty') and flag";
		$xship_res = $GLOBALS["db2"]->doQuery($xship_sql)->getFirstRow();
		
		if ($GLOBALS["db2"]->getNumRows()>0) {
			if($xship_res["use_man"]=="0") {
				if($xship_res["{$xtrx_type}cond"]=="=") {
					$xship_res["{$xtrx_type}cond"] = "==";
				}
?>				
				var condition = '<?=$xship_res["{$xtrx_type}cond"]?>';
				var amount = '<?=$xship_res["{$xtrx_type}amount"]?>';
				var comprison = digit_format(xsrc_total,2)+condition+digit_format(amount,2);
				//alert(xsrc_total+"<?=$xship_res["{$xtrx_type}cond"]?>"+amount);
				if (xsrc_total<?=$xship_res["{$xtrx_type}cond"]?>amount) {
					xresult = '<?=$xship_res["{$xtrx_type}charge"]?>';
				}
<?
			} else if($xship_res["use_man"]=="2") {
				$asql = "select sccode from sub_mssc_ext where sccode='$txt_key' and gsc";
				$gsc_flag = ($GLOBALS["db2"]->doQuery($asql)->isFound())?1:0;
				$snhtype = ($xtrx_type=='inv')?($gsc_flag)?"GSN":"SNO":"MNO";
				$snh = "select * from new_snh_condition where cn_id='$region' and category_bycond='$snhtype' and location in ('ALL','$seller_ty') order by postcd_bycode desc";
				$snh_con = $GLOBALS["db2"]->doQuery($snh)->toArray();
				$no = 0;
				foreach($snh_con as &$s) {
					$no++;
					if($s["postcd_bycode"]=='All') {
						$s["cond_bycond"] = ($s["cond_bycond"]=="=")?"==":$s["cond_bycond"];
						$cond_amt = $s["amount_bycond"];
						$cond_charge = $s["charge_bycond"];
						?>
						var amount = <?=$s["amount_bycond"]?>;
						if (xsrc_total<?=$s["cond_bycond"]?>amount) {
							xresult = '<?=$s["charge_bycond"]?>';
						}
						<?
					} else {
						if($no>1) echo "else ";
						$output = explode( "=", $s["postcd_bycode"] );
						if(count($output)==1) {
							$output = explode( "<>", $s["postcd_bycode"] );
							$apost = explode( "#", $output[1] );
							echo "if(";
							for ($i=0;$i<count($apost);$i++) {
								$bpost = explode("-", $apost[$i]);
								if(count($bpost)>1) {
									for ($j=$bpost[0];$j<=$bpost[1];$j++) {
										echo "$('delivery_zip').value != $j";
										if($j!=$bpost[1]) echo " && ";
									}
								} else
									echo "$('delivery_zip').value != $apost[$i]";
								if($i!=(count($apost)-1)) echo " && ";
							}
							echo ") {";
						} else {
							$newcond = "== '{$output[1]}'";
							$apost = explode( "#", $output[1] );
							echo "if(";
							for ($i=0;$i<count($apost);$i++) {
								$bpost = explode("-", $apost[$i]);
								if(count($bpost)>1) {
									for ($j=$bpost[0];$j<=$bpost[1];$j++) {
										echo "$('delivery_zip').value == $j";
										if($j!=$bpost[1]) echo " || ";
									}
								} else 
									echo "$('delivery_zip').value == $apost[$i]";
								if($i!=(count($apost)-1)) echo " || ";
							}
							echo ") {";
						}
						Logger::debug("check setup zip {$s["postcd_bycode"]} : ",$output);
						Logger::debug("new condition : $newcond ");
						$s["cond_bycond"] = ($s["cond_bycond"]=="=")?"==":$s["cond_bycond"];
						$s["postcd_bycode"] = str_replace("<>","!=",$s["postcd_bycode"]);
					?>
						var condition = '<?=($s["cond_bycond"]=="=")?"==":$s["cond_bycond"]?>';
						var amount = <?=$s["amount_bycond"]?>;
						if (xsrc_total<?=$s["cond_bycond"]?>amount) {
							xresult = '<?=$s["charge_bycond"]?>';
						}
					}
					<?
					} 
				}
			}
		}
		else {
?>
			xresult = xsnh_charge;
<?
		}
?>
		}
		return xresult;
	}

	function fget_pickup_snhcost(xsrc_total) {
		var xresult = 0;
<?
		$xship_sql = "select * from new_snh_setup ship_setup left join v_ms_sc_region seller_reg on ship_setup.cn_id=seller_reg.region where seller_reg.code='$xloccd' 
			and location in ('ALL','$seller_ty') and flag";
		$xship_res = $GLOBALS["db2"]->doQuery($xship_sql)->getFirstRow();
		
		if ($GLOBALS["db2"]->getNumRows()>0) {
			if($xship_res["pickup_handling"]=="t") {
				$asql = "select sccode from sub_mssc_ext where sccode='$txt_key' and gsc";
				$gsc_flag = ($GLOBALS["db2"]->doQuery($asql)->isFound())?1:0;
				if($xtrx_type=='inv') {
					$trxcond = ($gsc_flag)?$xship_res["gsinvcond_pickup"]:$xship_res["invcond_pickup"];
					$trxamount = ($gsc_flag)?$xship_res["gsinvamount_pickup"]:$xship_res["invamount_pickup"];
					$shcharge = ($gsc_flag)?$xship_res["gsinvcharge_pickup"]:$xship_res["invcharge_pickup"];
				} else {
					$trxcond = $xship_res["cbcond_pickup"];
					$trxamount = $xship_res["cbamount_pickup"];
					$shcharge = $xship_res["cbcharge_pickup"];
				}
				$trxcond = ($trxcond=="=")?"==":$trxcond;
				if(!empty($trxcond)) {
?>				
				var condition = '<?=$trxcond?>';
				var amount = '<?=$trxamount?>';
				var comprison = digit_format(xsrc_total,2)+condition+digit_format(amount,2);
				if (xsrc_total<?=$trxcond?>amount) {
					xresult = '<?=$shcharge?>';
				}
<?
				}
			}
		}
?>
		return xresult;
	}
<?
}

function frm_input_prd4($xloccd, $xtreg,$trxType=NULL,$buyer=NULL,$membercode=NULL) {
	/**
		$treg = 0 is SC
		$treg = 1 is BR
	*/
	
	global $qty;
	
?>
		<table width="100%" border="0" cellspacing="1" cellpadding="1" bgcolor="#CCCCCC">
			

			<tr bgcolor="#FFFFFF">
				<td><?=mxlang("590")?>: &nbsp;
					<input type='text' name='prd_txt_input' value='<?=$prd_txt_input?>' size="8" maxlength="10" onKeypress="return enterMove(event)">&nbsp;
					<input type='hidden' name='prd_hid_input' value="<?=$prd_row[0]?>">&nbsp;
					<?=mxlang("227")?> : <input type='text' name='qty' value='<?=$qty?>' size='5' maxlength='6'>&nbsp;
					<input type='button' name='btn_add' value='<?=mxlang("465")?>' onClick='fadd_item4();'>
					<input type='hidden' name='hid_item_prdcd'>
					<input type='hidden' name='hid_item_qty'>
					<span id="prd_view" stryle="display: none;">
					<input type='button' name='btn_view' value='<?=mxlang("1539")?> By Type' onClick="fview_item(<?=$xtreg?>,1,'<?=$trxType?>','<?=$buyer?>');">
					<input type='button' name='btn_view' value='<?=mxlang("1539")?> By Category' onClick="fview_item(<?=$xtreg?>,2,'<?=$trxType?>','<?=$buyer?>');">
					</div>
					<input type='hidden' name='item_action' value=''>
					<input type='hidden' name='tmp_trivcd' value='<?=$tmp_trivcd?>'>
					<input type='hidden' id='sum' name='sum' value='<?=$sum?>'>
					<input type='hidden' id='xprdcd' name='xprdcd' value='<?=$xprdcd?>'>
					<input type='hidden' id='xqty' name='xqty' value='<?=$xqty?>'>	
					<input type='hidden' id='aqty' name='aqty' value='<?=$aqty?>'>
					<input type='hidden' id='stfflmt' name='stfflmt' value='<?=$stfflmt?>'>
					<input type='hidden' id='membercode' name='membercode' value='<?=$membercode?>'>
					<input type='hidden' id='open_win' name='open_win' value=''>
					<div id='xtesting'></div>
				</td>
			</tr>
		<table>
<?php
}

function frm_payment_transaction() {
	global $db,$presult1,$vou_bool,$chk_bool,$vou_text,$vou_curr,$vou_rate,$chk_text,$chk_curr,$chk_rate,$cursign,$vou_input,$chk_input, $priv, $show_ccd, $show_btr, 
	$new_contra_db, $txt_scid, $txt_key, $xtxt_key, $return_total, $tmp_file_ref, $xmodule_name, $xloccd, $date1, $month1, $year1,$show_arlist_trx, $transaction_localdate,
	$txt_grandtotal,$credit_flag,$region,$db2,$db3,$autoship_flag,$sc_info,$scadd_default;
	if ($return_total=='') $return_total = 0 - $txt_grandtotal;
	$transaction_timestamp = get_timestamp(get_timezone(TZ_COUNTRY,$region));
	if (empty($transaction_localdate)) $transaction_localdate = date("d/m/Y",$transaction_timestamp);
    //$exptransaction_localdate=date("m/y",$transaction_timestamp);
?>
        
	<input type="hidden" name="tmp_batch_vou_txt">
	<input type="hidden" name="tmp_batch_che_txt">
	<input type="hidden" name="tmp_exp_vou_txt">
	<input type="hidden" name="tmp_exp_che_txt">
	<input type='hidden' name='is_stockist' value='<?=$txt_scid?>'>
	<input type='hidden' name='xtxt_key' value='<?=(($xtxt_key!='')?$xtxt_key:$txt_key);?>'>
	<input type="hidden" name="tmp_che_contraloc">
	<input type="hidden" name="tmp_che_memcode">
	<input type="hidden" name="tmp_che_status">
	<input type="hidden" name="tmp_vou_contraloc">
	<input type="hidden" name="tmp_vou_memcode">
	<input type="hidden" name="tmp_vou_status">
	<input type="hidden" name="xloccd" value='<?=$xloccd?>'>
	<input type="hidden" name="tmp_file_ref" value='<?=(($tmp_file_ref!='')?$tmp_file_ref:$xmodule_name);?>'>
	<table width="100%" border="0" cellspacing="1" cellpadding="1" bgcolor="#CCCCCC">
		<tr bgcolor="#E6E6E6">
			<td width=20% align="center"><?=ucwords(strtolower(mxlang("157")))?></td>
			<td width=10% align="center"><?=ucwords(strtolower(mxlang("152")))?></td>
			<td align="center" ><?=ucwords(strtolower(mxlang("1708")))?></td>
		</tr>
		<?php
			$ar_flag = 0;
			$beside_return = 0;
			$paytotal = 0;
			$ccd_flag = 0;
			if (pg_numrows($presult1)>0) {
				for ($ip=0;$ip<pg_num_rows($presult1);$ip++) {
					$prow_ = pg_fetch_row($presult1, $ip);
					if ($prow_[0]=='AR' && $prow_[2]=='t') { 
						$ar_flag = 1;
						$beside_return = ($prow_[2]=='t')?1:0;
						echo "<input type='hidden' id='cbo_paytype_$ip' name='cbo_paytype_$ip' value='AR'>";
						echo "<input type='hidden' id='cbo_payinput_ar' name='cbo_payinput_$ip' value='0'>";
					} else {
		?>
			<!--tr bgcolor="#FFFFFF" <?=(($prow_[0]=='OTH' AND $creditflag==1)?"":(($prow_[0]!='OTH')?"":" hidden "))?> -->
			<tr bgcolor="#FFFFFF" class="<?=(($prow_[0]=='OTH')?"trOTH":"")?>" style="<?=($credit_flag!=1 && $prow_[0]=='OTH')?"display:none":""?>">
				<td valign='top'><?=mxlangtxt($prow_[1])?> <?=($prow_[0]=='CCD')?"1":""?></td>
				<td align='right' valign='top'  >
					<?php if ($prow_[0]=='EW') { ?>
						<!--<span id="ew_payment">0</span>-->
						<input class='input1 payments' type='text' id='cbo_payinput_ew' name='cbo_payinput_<?=$ip?>' value='0' onChange='calc_tot_pay();' size='15'>
					<?php } else if ($prow_[0]=='ARR') { ?>
						<span id="arr_payment">0</span>
						<input class='input1 payments' type='hidden' id='cbo_payinput_arr' name='cbo_payinput_<?=$ip?>' value='0'>
					<?php } else if ($prow_[0]=='CSH') { 
						//$csh_value = ($prow_[3]=='t')?$txt_grandtotal:0;
						$csh_value = 0;
						if($prow_[3]=='t') {
			                            $csh_value = $txt_grandtotal;
                        			    if($credit_flag==1) { 
							$credit = pg_exec($db,"select credit_bal from credit_history where code='$txt_key'");
							if(pg_numrows($credit)>0) {
								$crow = pg_fetch_row($credit, 0);
								$credit_balance = $crow[0];
								Logger::debug("credit data : ",$crow);
								if($credit_balance > $txt_grandtotal)
                                        				$csh_value = 0;
				                                else
                                				        $csh_value = $txt_grandtotal - $credit_balance;
								}
							}
						}
						$return_total += $csh_value;
						$paytotal += $csh_value;
					?>
						<input class='input1 payments' type='text' id='cbo_payinput_<?=$ip?>' name='cbo_payinput_<?=$ip?>' value='<?=$csh_value?>'
						onChange='calc_tot_pay();' size='15'>
					
					<?php 
                                        
                       	} else if ($prow_[0]=='OTH') { 
                       		$credit_balance = 0;
                       		if($credit_flag==1) { 
								Logger::debug("Prow:$prow_[0], creditflag:$credit_flag");
								$credit = pg_exec($db,"select credit_bal from credit_history where code='$txt_key'");
								Logger::debug("select credit_bal from credit_history where code='$txt_key'");
								if(pg_numrows($credit)>0) {
									$crow = pg_fetch_row($credit, 0);
									$credit_balance = $crow[0];
									Logger::debug("credit data : ",$crow);
									if($credit_balance > $txt_grandtotal)
										 $credit_balance = $txt_grandtotal;
								}
								$return_total += $credit_balance;
								$paytotal += $credit_balance;
							}
					?>
							<input class='input1 payments <?=$prow_[0]?>' type='<?=($credit_flag==1)?"text":"hidden"?>' id='cbo_payinput_<?=$ip?>' name='cbo_payinput_<?=$ip?>' 
							value='<?=($credit_flag==1)?$credit_balance:0?>' onChange='calc_tot_pay();' size='15' readonly>
	                <?php } else if ($prow_[0]=='CCD') { 
	                	$pay_value_total = 0;
	                	if(!empty($txt_grandtotal)){
	                		$pay_value_total = $txt_grandtotal;
	                	}
	                	if(!empty($total)){
	                		$pay_value_total = $total;
	                	}
	                	//if(!empty($return_total)){
	                	//	$pay_value_total = $return_total;
	                	//}
						//$pay_value = ($autoship_flag==1)?$txt_grandtotal:0;
						//$pay_value = ($autoship_flag==1)?0-$return_total:0;
						if($credit_flag==0){
							$pay_value = $pay_value_total;
							$return_total += $pay_value;
							$paytotal += $pay_value;
						}
					?>
						<input class='input1 payments' type='text' id='cbo_payinput_<?=$ip?>' name='cbo_payinput_<?=$ip?>' value='<?=($credit_flag==1)?0:$pay_value?>'
						onChange='calc_tot_pay();' size='15'>
					<?php 
						} else if ($prow_[0]!='OTH') { 
					?>
						<input class='input1 payments ' type='text' id='cbo_payinput_<?=$ip?>' name='cbo_payinput_<?=$ip?>' value='0'
						onChange='calc_tot_pay();' size='15' >
					<?php } ?>
					<input type='hidden' id='cbo_paytype_<?=$ip?>' name='cbo_paytype_<?=$ip?>' value='<?=$prow_[0]?>'>
				</td>
				
				<?php
					/** close down as per request lacey 27-11-2017 **/
					/*
					if ($prow_[0]=='EW') {
					
					
				?>
					<td>
					  <input type="hidden" value="" name="ew_no" id="ew_no">
						<?php
						<input type="button" value="<?=mxlang(4368)?>" style="margin-left:3px;" onclick="<?=($xmodule_name=="sccb" || $xmodule_name=="iocred")?"validate_member();":"$('wallet').submit();"?>">
						<?php
							if(($xmodule_name=="brcb")||($xmodule_name=="msinv")||($xmodule_name=="brinv")||($xmodule_name=="sccb")){
						?>
								<input type="button" value="<?=mxlang(274)?>" style="margin-left:3px;" onclick="$('ew_payment').update('0');$('ew_no').value=null;$('cbo_payinput_ew').value = 0;$('ep_is_discount').hide();$('ep_discount').value = '0';$('txt_after_deduct_ep').value = 0;calc_tot_pay();">
						<?php
							}
							else{
						?>
								<input type="button" value="<?=mxlang(274)?>" style="margin-left:3px;" onclick="$('ew_payment').update('0');$('ew_no').value=null;$('cbo_payinput_ew').value = 0;calc_tot_pay();">
						<?php
							}
						?>
					</td>
				<?php
					}
					else */ 
                                if ($prow_[0]=='ARR') {
				?>
					<td align='left'>
						<input type="hidden" name="ar_redeem" id="ar_redeem">
						<input type="hidden" name="ar_trxredeem" id="ar_trxredeem">
						<div>
							<input type='button' onclick='show_arrlist()' value='<?=mxlang(3409)?>' name='btn_arrlist'>
							<input type='button' onclick='reset_arr()' value='<?=mxlang(274)?>' name='btn_arr_reset'>
						</div>
					</td>
				<?php
					}
					elseif ($prow_[0]=='DEM') {
					
						$delivery = pg_exec($db,"select br_region from msms_new where br_code='$xloccd'");
						$delivery2=pg_fetch_row($delivery);
						if(pg_num_rows($delivery)<=0) {
							$delivery = pg_exec($db,"select bc_id from mssc where code='$xloccd'");
							$delivery2=pg_fetch_row($delivery);
						}
						$dmRes = pg_exec($db,"select a.delivery_id, a.delivery_name from delivery_method a, delivery_method_country b where a.delivery_id = b.delivery_id and cn_id='$delivery2[0]' order by delivery_name");
				?>
					<td align='left'>
					  <table>
							<tr>
								<td>Company Name</td>
								<td>
									<select name="delivery_method_<?=$ip?>">
										<?php
											if(pg_numrows($dmRes)>0)
												echo "<option value=''>".mxlang(1757)."</option>";
											for ($d=0;$d<pg_num_rows($dmRes);$d++) {
												$dem=pg_fetch_row($dmRes,$d);
												echo "<option value='$dem[0]'>",$dem[1],"</option>";
											}
										?>
									</select>
								</td>
							</tr>
					  </table>
					</td>
				<?php
					}
					elseif ($prow_[0]=='BDEC') {
				?>
					<td align='left'>
						<table>
							<tr>
								<td><?=ucwords(strtolower(mxlang("1709")))?></td>
								<td><input type='text' name='txt_batchno' onBlur="getBatch(this.value)"></td>
								<td><input type='hidden' name='txt_cbno'></td>
							</tr>
						</table>
					</td>
				<?php
					}
					//elseif ($prow_[0]=='CCD' && $show_ccd=='1') {
					elseif ($prow_[0]=='CCD') {

						if($xmodule_name != 'brcr') {
							/** always show detail credit card input payment since most of transaction will use this method **/
							include_once ("../module/conn_centralise_db.php"); 
							$ccd_flag = 1;
							$meminfo = $db3->doQuery("select * from msmemb where code='$txt_key'")->getFirstRow();
		                  	Logger::debug("meminfo:", $meminfo);
							$db3->close_db_con();

							$mem_bill_addr = $meminfo[saddr1];
							$mem_bill_addr .= ($meminfo[saddr2]=="")?"":"\n$meminfo[saddr2]";
							$mem_bill_addr .= ($meminfo[saddr3]=="")?"":"\n$meminfo[saddr3]";
							if(!empty($_POST["back_from_so"])) {
								Logger::debug("read bill from saving data ");
								include_once ("../classes/Encryption.inc.php");
								$enc = new Encryption();

								$trxid = $_POST["trxno"];
								$payment_info = $db2->doQuery("SELECT * FROM sopayment_info WHERE trcd='$trxid' ")->getFirstRow();
								if(!empty($payment_info["cardno"])){
									$memccd_no = $enc->decode($payment_info["cardno"]);
	            					$memccd_name = $enc->decode($payment_info["cardname"]);
	            					$memccd_type = $enc->decode($payment_info["cctype"]);
	            					$exptransaction_localdate = $enc->decode($payment_info["expdate"]);
	            					$memccd_auth = $enc->decode($payment_info["cvv"]);
	            					//$memccd_no = substr($memccd_no, 0, 2) . "XX-XXXX-XXXX-" . substr($memccd_no, strlen($memccd_no) - 4, 4);
	            					//$exptransaction_localdate = "XX/XXXX";
	            					//$memccd_auth = "XX";
	            					//$meminfo["saddr1"] = $enc->decode($ms_data["bill_addr"]);
	            					$mem_bill_addr = $enc->decode($payment_info["bill_addr"]);
	            					$meminfo["spostcd"] = $enc->decode($payment_info["bill_postcd"]);
	            					$meminfo["stown"] = $enc->decode($payment_info["bill_town"]);
	            					$meminfo["sst_id"] = $enc->decode($payment_info["bill_st_id"]);
									$meminfo["ship_tel"] = $enc->decode($payment_info["bill_tel"]);
									Logger::debug("CC Type : ", $memccd_type);
									Logger::debug("CC Member data Bill : ", $meminfo);
								}
							} else {
								if($autoship_flag==1) {
									include_once ("../classes/Encryption.inc.php");
									$enc = new Encryption();
									$autoship_q = "select * from autoship_master_header where code='$txt_key' "
                                                                                . "UNION "
                                                                                . "select * from autoship_cancel_header where code='$txt_key' ";
                                                                        $ms_data = $db2->doQuery($autoship_q)->getFirstRow();
									Logger::debug("Autoship Member data : ", $ms_data);
									$memccd_no = $enc->decode($ms_data["cardno"]);
	            					$memccd_name = $enc->decode($ms_data["cardname"]);
	            					$memccd_type = $enc->decode($ms_data["cardtype"]);
                                                        Logger::debug("CC Type : ", $memccd_type);
	            					//$exptransaction_localdate = $enc->decode($ms_data["expdate"]);
	            					//$memccd_auth = $enc->decode($ms_data["cvv"]);
	            					$memccd_no = substr($memccd_no, 0, 2) . "XX-XXXX-XXXX-" . substr($memccd_no, strlen($memccd_no) - 4, 4);
	            					$exptransaction_localdate = "XX/XXXX";
	            					$memccd_auth = "XX";
	            					//$meminfo["saddr1"] = $enc->decode($ms_data["bill_addr"]);
	            					$mem_bill_addr = $enc->decode($ms_data["bill_addr"]);
	            					$meminfo["spostcd"] = $enc->decode($ms_data["bill_postcd"]);
	            					$meminfo["stown"] = $enc->decode($ms_data["bill_town"]);
	            					$meminfo["sst_id"] = $enc->decode($ms_data["bill_st_id"]);
									$meminfo["ship_tel"] = $enc->decode($ms_data["bill_tel"]);
									Logger::debug("Autoship Member data Bill : ", $meminfo);
								}
							}
						}
						
						$country= 'US';
						//ENHANCEMENT Billing address follow sub_mssc_ext.badd_default
						if ($scadd_default=='0'){
							// var_dump($mem_bill_addr);
							$mem_bill_addr 			= $sc_info['addr1'].",".$sc_info['addr2'].",".$sc_info['addr3'];
							$meminfo['spostcd']		= $sc_info['postcd'];
							$meminfo["stown"]		= $sc_info['city'];
							$meminfo['sst_id']		= $sc_info['st_id'];
							$country				= $sc_info['cn_id'];
							$meminfo["ship_tel"]	= $sc_info['hmphone'];
						} else if ($scadd_default=='1'){
							$mem_bill_addr 			= $sc_info['saddr1'].",".$sc_info['saddr2'].",".$sc_info['saddr3'];
							$meminfo['spostcd']		= $sc_info['spostcd'];
							$meminfo["stown"]		= $sc_info['scity'];
							$meminfo['sst_id']		= $sc_info['sst_id'];
							$country				= $sc_info['scn_id'];
							$meminfo["ship_tel"]	= $sc_info['stel_no'];
						}
						if ($scadd_default=='0'){
							$mem_bill_addr = $sc_info[addr1].($sc_info[addr2]==""?"":"\n$sc_info[addr2]").($sc_info[addr3]==""?"":"\n$sc_info[addr3]");
						} else {
							$mem_bill_addr = $sc_info[saddr1].($sc_info[saddr2]==""?"":"\n$sc_info[saddr2]").($sc_info[saddr3]==""?"":"\n$sc_info[saddr3]");
						}
				?>
					<td align='left'>
						<table>
							<tr>
								<td><?=ucwords(strtolower(mxlang(162)))?></td>
								<td>
									<select name='ccd_type_<?=$ip?>'>
										<option value='MC' <?=($memccd_type=="MC" || $memccd_type=="M")?"selected":""?>><?=ucwords(strtolower(mxlang(1023)))?></option>
										<option value='VS' <?=($memccd_type=="VS" || $memccd_type=="V")?"selected":""?>><?=ucfirst(strtolower(mxlang(1541)))?></option>
										<option value='DC' <?=($memccd_type=="DC" || $memccd_type=="D")?"selected":""?>><?=ucfirst(strtolower(mxlang(513)))?></option>
										<!--<option value='DN' <?=($memccd_type=="DN")?"selected":""?>><?=ucfirst(strtolower(mxlang(3319)))?></option>-->
										<option value='AE' <?=($memccd_type=="AE" || $memccd_type=="A")?"selected":""?>><?=mxlang(3320)?></option>
									</select>
                                   <br><div class="popup"><span class="popuptext" id="span_ccd_type_<?=$ip?>" style="color: red;"></span></div>
								</td>
							</tr>
							<tr>
								<td><?=ucwords(strtolower(mxlang(159)))?></td>
                                <td>
                                	<input type='text' name='ccd_no_<?=$ip?>' size='20' maxlength='16' value="<?=$memccd_no?>"><br>
                                    <div class="popup"><span class="popuptext" id="span_ccd_no_<?=$ip?>" style="color: red;"></span></div>
                                </td>
							</tr>
							<tr>
								<td><?=ucwords(strtolower(mxlang("160")))?></td>
								<td>
									<input type='text' name='ccd_name_<?=$ip?>' size='16' value="<?=$memccd_name?>"><br>
                                    <div class="popup"><span class="popuptext" id="span_ccd_name_<?=$ip?>" style="color: red;"></span></div>
								</td>
							</tr>
							<tr>
								<td><?=ucwords(strtolower(mxlang(161)))?></td>
								<td>
                                                                        
                                        <input class="datexp" type="text" id="ccd_exp_<?=$ip?>" name="ccd_exp_<?=$ip?>" placeholder="MM/YYYY" value="<?=($_POST["ccd_exp_$ip"]=="")?$exptransaction_localdate:$_POST["ccd_exp_$ip"]?>" size="12" maxlength="10" >
								<!-- 									<a href="javascript:void(0)" onClick="gfPop.fPopCalendar(frm_invcnt_fltr.ccd_exp_<?=$ip?>);return false;" hidefocus>
										<img src="../images/cal_show.gif" border="0">
									</a>-->
                                        <br><div class="popup"><span class="popuptext" id="span_ccd_exp_<?=$ip?>" style="color: red;"></span></div>
                                                                        
								</td>
							</tr>
							<tr>
								<td><?=mxlang(3327)?></td>
								<td><input type='text' name='ccd_autho_<?=$ip?>' size='5' maxlength='4' value="<?=$memccd_auth?>"><br>
                                    <div class="popup"><span class="popuptext" id="span_ccd_autho_<?=$ip?>" style="color: red;"></span></div>
								</td>
							</tr>
							
							<tbody id="ccd_bill_addr">
							<tr>
								<td colspan="2"><b><?=mxlang(5916)?></b></td>
							</tr>
							<tr valign="top">
								<td>Same as</td>
								<td>
									<select id='scadd_' name='scadd_' onchange="update_billing_address('<?=$ip?>')">
										<option value='0' <?=$scadd_default=='0'?'selected':''?>>Mailing Address</option>
										<option value='1' <?=$scadd_default=='1'?'selected':''?>>Shipping Address</option>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<td><?=ucwords(strtolower(mxlang(24)))?></td>
								<td>
									<TEXTAREA id="txt_bill_addr_<?=$ip?>" NAME="txt_bill_addr_<?=$ip?>" ROWS=3 COLS=20><?=$mem_bill_addr?></TEXTAREA><br>
									<div class="popup"><span class="popuptext" id="span_txt_bill_addr_<?=$ip?>" style="color: red;"></span></div>
								</td>
							</tr>
                            <tr>
								<td><?=mxlang(1081)?></td>
								<td>
                                    <input type='text' id='txt_bill_postcd_<?=$ip?>'name='txt_bill_postcd_<?=$ip?>' size='16' onchange="" value="<?=$meminfo[spostcd]?>">
                                    <br><div class="popup"><span class="popuptext" id="span_txt_bill_postcd_<?=$ip?>" style="color: red;" ></span></div>
                                </td>
							</tr>
							<tr>
								<td><?=mxlang(382)?>
                                    <script>
	                                    function doGetShipToCity<?=$ip?>(cnid, zipcode, shiptown) {
	                                        var url = "../module/module_pop.php";
											console.log(shiptown);
	                                        jq("#td_bill_city_<?=$ip?>").empty().html("<option value=''>Loading</option>");
	                                        jq.post(url, {
	                                            actship: "zipcity",
	                                            cnid: cnid,
	                                            zipcode: zipcode,
	                                            shiptown: shiptown,
	                                            idname:"txt_bill_city_<?=$ip?>"
	                                        }, function (data) {
	                                            jq("#td_bill_city_<?=$ip?>").empty().html(data);
	                                            doGetShipToState<?=$ip?>(cnid, zipcode,"<?=$meminfo["sst_id"]?>");
	                                        });
	                                    }
	                                    //doGetShipToCity("US", "<?=$meminfo[postcd]?>", "<?=$meminfo[town]?>");
	                                </script>
                                </td>
								<td id="td_bill_city_<?=$ip?>">
									<select id="txt_bill_city_<?=$ip?>" name="txt_bill_city_<?=$ip?>">
									<?php
									$city_info = $GLOBALS["db2"]->doQuery("select distinct city from zipcode order by city")->toArray();
									if(count($city_info)>0) {
										foreach($city_info as $c) {
											$city_selected = (strtoupper($c["city"]) == strtoupper($meminfo["stown"]))?"selected":"";
											//echo "<option value='{$c["city"]}' $city_selected> {$c["city"]} </option>";
										}
									}
									?>
									</select><br>
                                    <span id="span_txt_bill_city_<?=$ip?>" style="color: red;"></span>
                                    
                                                                
								</td>
							</tr>
							<tr>
								<td><?=mxlang(383)?>
                                    <script>
										function update_billing_address(position_input){
											var function_name='doGetShipToCity'+position_input;
											if (jq("#scadd_").val()==0){
												eval(function_name+"('US','<?=$sc_info['postcd']?>','<?=$sc_info['city']?>')");
												jq("#txt_bill_addr_"+position_input).val("<?=$sc_info['addr1'].'\n'.$sc_info['addr2'].'\n'.$sc_info['addr3']?>");
												jq("#txt_bill_postcd_"+position_input).val("<?=$sc_info['postcd']?>");
												jq("#txt_bill_city_"+position_input).val("<?=$sc_info['city']?>");
												jq("#txt_bill_state_"+position_input).val("<?=$sc_info['st_id']?>");
												jq("input[name='txt_bill_tel_"+position_input+"']").val("<?=$sc_info['hmphone']?>");
												jq("#txt_bill_country_"+position_input).val("US");
											} else if (jq("#scadd_").val()==1){
												eval(function_name+"('US','<?=$sc_info['spostcd']?>','<?=$sc_info['scity']?>')");
												jq("#txt_bill_addr_"+position_input).val("<?=$sc_info['saddr1'].'\n'.$sc_info['saddr2'].'\n'.$sc_info['saddr3']?>");
												jq("#txt_bill_postcd_"+position_input).val("<?=$sc_info['spostcd']?>");
												jq("#txt_bill_city_"+position_input).val("<?=$sc_info['scity']?>");
												jq("#txt_bill_state_"+position_input).val("<?=$sc_info['sst_id']?>");
												jq("input[name='txt_bill_tel_"+position_input+"']").val("<?=$sc_info['stel_no']?>");
												jq("#txt_bill_country_"+position_input).val("US");
											}
										}

                                        function doGetShipToState<?=$ip?>(cnid, zipcode, state_id) {
                                            var url = "../module/module_pop.php";
                                            jq("#td_bill_state_<?=$ip?>").empty().html("<option value=''>Loading</option>");
                                            jq.post(url, { 
                                                actship: "zipstate",
                                                cnid: cnid,
                                                zipcode: zipcode,
                                                state_id: state_id,
                                                idname:"txt_bill_state_<?=$ip?>"
                                            }, function (data) {
                                                // jq("select[name=txt_bill_state_<?=$ip?>]").empty().html(data);
                                                jq("#td_bill_state_<?=$ip?>").empty().html(data); 

                                            });
                                        }
                                        function doRundoGetShipToCity(){
											console.log('townn');
                                            var zip = jq('input[name=txt_bill_postcd_<?=$ip?>]').val();
											console.log(zip);
                                            doGetShipToCity<?=$ip?>('US', zip,"<?=$meminfo[stown]?>");
                                        }
                                        function doRundoGetShipToCityTown(town){
											console.log('cut townn');
                                            var zip = jq('input[name=txt_bill_postcd_<?=$ip?>]').val();
											console.log(zip);
                                            doGetShipToCity<?=$ip?>('US', zip,town);
                                        }
                                        doRundoGetShipToCity();
                                        jq(function () {
                                            jq('input[name=txt_bill_postcd_<?=$ip?>]').change(function(){
                                                doGetShipToCity<?=$ip?>('US', this.value);
                                            });
											jq('input[name=txt_bill_tel_<?=$ip?>]').mask('(000)000-0000');
											jq('select[name=ccd_type_<?=$ip?>]').change(function(){
												var xval = this.value;
												var mxlength = 3;
												var mxlength_cc = 16;
												if(xval=='AE'){
													mxlength = 4;
													mxlength_cc = 15;
												}
												jq("input[name=ccd_no_<?=$ip?>]").attr("maxlength",mxlength_cc );
												jq("input[name=ccd_autho_<?=$ip?>]").attr("maxlength", mxlength);
												//jq("input[name=ccd_no_<?=$ip?>]").val("");
												//jq("input[name=ccd_autho_<?=$ip?>]").val("");
												//jq("input[name=ccd_name_<?=$ip?>]").val("");
												//jq("input[name=ccd_exp_<?=$ip?>]").val("");
											});
                                        });
                                    </script>
                                </td>
								<td id="td_bill_state_<?=$ip?>">
									<?php
									if($GLOBALS["db2"]->doQuery("select st_id,st_name from state where st_id='".($meminfo[sst_id]==""?0:$meminfo[sst_id])."' order by st_name ")->isFound()) {
										$st_info = $GLOBALS["db2"]->toArray();
										if(count($st_info)>1) {
											?>
											<select id="txt_bill_state_<?=$ip?>" name="txt_bill_state_<?=$ip?>">
											</select>
											<? 
										} else 
											echo "{$st_info[0]["st_name"]} <input type='hidden' id='txt_bill_state_$ip' name='txt_bill_state_$ip' value='{$meminfo["sst_id"]}'>";
									}
									?>
									<br>
                                    <span id="span_txt_bill_state_<?=$ip?>" style="color: red;"></span>
                                    
								</td>
							</tr>
							
							<tr>
								<td><?=mxlang(72)?></td>
								<td>
                                    UNITED STATES
								<!--								<select name="txt_bill_country_<?=$ip?>">
								<?php
									// $cn_info = $GLOBALS["db2"]->doQuery("select iso,upper(trim(initcap(name))) as name from country_list order by name")->toArray();
									// if(count($cn_info)>0) {
									// 	$meminfo["cn_id"] = (empty($meminfo["cn_id"]))?"US":$meminfo["cn_id"];
									// 	foreach($cn_info as $cn) {
									// 		$cn_selected = ($cn["iso"] == $meminfo["cn_id"])?"selected":"";
									// 		echo "<option value='{$cn["iso"]}' $cn_selected> {$cn["name"]} </option>";
									// 	}
									// }
								?>
								</select>-->
                                    <input type="hidden" id="txt_bill_country_<?=$ip?>" name="txt_bill_country_<?=$ip?>" value="US" ><br>
                                    <span id="span_txt_bill_country_<?=$ip?>" style="color: red;"></span>
								</td>
							</tr>
							<tr>
								<td><?=mxlang(384)?></td>
								<td><input type='text' name='txt_bill_tel_<?=$ip?>' size='16' value="<?=$meminfo["ship_tel"]?>"><br>
									<span id="span_txt_bill_tel_<?=$ip?>" style="color: red;"></span>
								</td>
							</tr>
							</tbody>
						</table>
					</td>
				<?php
					}

					/** add payment for credit card online */
					elseif ($prow_[0]=='CCO') {
				?>
					<td align='left'>
						<table>
							<tr>
								<td><?=ucwords(strtolower(mxlang("159")))?></td>
								<td><input type='text' name='cco_no' size='16' maxlength='16'></td>
							</tr>
							<tr>
								<td><?=ucwords(strtolower(mxlang("160")))?></td>
								<td><input type='text' name='cco_name' size='16'></td>
							</tr>
							<tr>
								<td valign="top"><?=ucwords(strtolower(mxlang("24")))?></td>
								<td><textarea id="cco_address" cols="30" rows="2" name="cco_address"></textarea></td>
							</tr>
							<tr>
								<td><?=ucwords(strtolower(mxlang("1081")))?></td>
								<td><input type='text' name='cco_zipcode' size='10'></td>
							</tr>
							<tr>
								<td><?=ucwords(strtolower(mxlang("161")))?></td>
								<td>
									<input type="text" id="cco_exp" name="cco_exp" value="<?=($cco_exp=="")?$transaction_localdate:$cco_exp;?>" size="12" maxlength="10">
									<a href="javascript:void(0)" onClick="gfPop.fPopCalendar(frm_invcnt_fltr.cco_exp);return false;" hidefocus>
										<img src="../images/cal_show.gif" border="0">
									</a>
								</td>
							</tr>
							<tr>
								<td><?=ucwords(strtolower(mxlang("1972")))?></td>
								<td><input type='text' name='cco_autho' size='16'></td>
							</tr>
						</table>
					</td>
				<?php
					}
				/** end credit card online */

					elseif ($prow_[0]=='BTR' && $show_btr=='1') {
				?>
					<td align='left'>
						<table>
							<tr>
								<td><?=ucwords(strtolower(mxlang("1973")))?></td>
								<td><input type='text' name='btr_no_<?=$ip?>' size='16'></td>
							</tr>
							<tr>
								<td><?=ucwords(strtolower(mxlang("657")))?></td>
								<td><input type='text' name='btr_name_<?=$ip?>' size='16'></td>
							</tr>
						</table>
					</td>
				<?php
					}
					elseif ($prow_[0]=='CKK') {
				?>
					<td align='left'>
						<table>
							<!--<tr>
								<td><?=mxlang("657")?></td>
								<td><input type='text' name='txt_bankName_<?=$ip?>' size='15'></td>
							</tr>-->
							<tr>
								<td><?=ucfirst(strtolower(mxlang("1709")))?></td>
								<td><input type='text' name='cbo_paynumber_<?=$ip?>' size='15'></td>
							</tr>
							<!--<tr>
								<td><?=ucwords(strtolower(mxlang("1658")))?></td>
								<td>
									<input type='text' name='txt_paydate_<?=$ip?>' size='11' maxlength='10'>
									<a href="javascript:void(0)" onClick="gfPop.fPopCalendar(frm_invcnt_fltr.txt_paydate_<?=$ip?>);return false;" hidefocus>
									<img src="../images/cal_show.gif" border="0"></a>
								</td>
							</tr>-->
						</table>
					</td>
				<?php
					}
					elseif ($prow_[0]=='VOU') {
						$vou_bool = true;
						$vou_text="cbo_paynumber_$ip";
						$vou_input="cbo_payinput_$ip";
						$vou_curr="cbo_paycurr_$ip";
						$vou_rate="cbo_payrate_$ip";
				?>
					<td align='left'>
						<table>
							<tr>
								<td><?=ucfirst(strtolower(mxlang("1709")))?></td>
								<td>
									<input type='text' name='<?=$vou_text?>' size='15' onChange='vou_inedit()' <? if ($new_contra_db) echo "onBlur=\"fget_contra_value('V')\""; ?>>
									<input type='hidden' name='<?=$vou_curr?>' size='15' onChange='vou_inedit()'>
									<input type='hidden' name='<?=$vou_rate?>' size='15' onChange='vou_inedit()'>
									<? if ($new_contra_db==1) { ?>
										<input type='button' onclick='' value='<?=mxlang("1617")?>' name='btn_go'>
									<? } else { ?>
										<input type='button' onclick='chkvcr1();' value='<?=mxlang("1617")?>' name='btn_go'>
									<? } ?>
								</td>
							</tr>
						</table>
					</td>
				<?php
					}
					elseif ($prow_[0]=='CHK') {
						$chk_bool = true;
						$chk_text="cbo_paynumber_$ip";
						$chk_input="cbo_payinput_$ip";
						$chk_curr="cbo_paycurr_$ip";
						$chk_rate="cbo_payrate_$ip";
				?>
					<td align='left'>
						<table>
							<!--<tr>
								<td><?=mxlang(657)?></td>
								<td><input type='text' name='chk_bank_name_<?=$ip?>' size='20'>	</td>
							</tr>-->
							<tr>
								<td><?=mxlang(1709)?></td>
								<td>
									<input type='text' name='<?=$chk_text?>' size='15'>
									<input type='hidden' name='<?=$chk_curr?>'>
									<input type='hidden' name='<?=$chk_rate?>'>
								</td>
							</tr>
							<!--<tr>
								<td><?=mxlang(400)?></td>
								<td>
									<input type='text' name='txt_chkdate_<?=$ip?>' value="<?=($_POST["txt_chkdate_$ip"]=="")?$transaction_localdate:$_POST["txt_chkdate_$ip"]?>" size='11' maxlength='10'>
									<a href="javascript:void(0)" onClick="gfPop.fPopCalendar(frm_invcnt_fltr.txt_chkdate_<?=$ip?>);return false;" hidefocus>
									<img src="../images/cal_show.gif" border="0"></a>
								</td>
							</tr>-->
						</table>
					</td>
				<?php
					}
					elseif ($prow_[0]=='DBT' && $show_ccd=='1') {
				?>
					<td align='left'>
						<table>
							<tr>
								<td><?=ucwords(strtolower(mxlang(4131)))?></td>
								<td><input type='text' name='dbt_no_<?=$ip?>' size='16' maxlength='16'></td>
							</tr>
							<tr>
								<td><?=ucwords(strtolower(mxlang(160)))?></td>
								<td><input type='text' name='dbt_name_<?=$ip?>' size='16'></td>
							</tr>
							<tr>
								<td><?=ucwords(strtolower(mxlang(161)))?></td>
								<td>
									<input type="text" id="dbt_exp_<?=$ip?>" name="dbt_exp_<?=$ip?>" value="<?=($_POST["dbt_exp_$ip"]=="")?date("d/m/Y"):$_POST["dbt_exp_$ip"];?>" size="12" maxlength="10">
									<a href="javascript:void(0)" onClick="gfPop.fPopCalendar($('dbt_exp'));return false;" hidefocus>
										<img src="../images/cal_show.gif" border="0">
									</a>
								</td>
							</tr>
							<tr>
								<td><?=ucwords(strtolower(mxlang(162)))?></td>
								<td>
									<select name='dbt_type_<?=$ip?>'>
										<option value='MC'><?=ucwords(strtolower(mxlang("1023")))?></option>
										<option value='VS'><?=ucfirst(strtolower(mxlang("1541")))?></option>
										<option value='DC'><?=ucfirst(strtolower(mxlang("513")))?></option>
									</select>
								</td>
							</tr>
							<tr>
								<td><?=ucwords(strtolower(mxlang(1972)))?></td>
								<td><input type='text' name='dbt_autho_<?=$ip?>' size='16'></td>
							</tr>
						</table>
					</td>
				<?php 	}
					elseif ($prow_[0]=='ET') {
				?>
					<td align='left'>
						<table>
							<tr>
								<td><?=ucwords(strtolower(mxlang("4653")))." ".ucwords(strtolower(mxlang("1755")))?></td>
								<td><input type='text' name='txt_refno'></td>
							</tr>
						</table>
					</td>
				<?php
					}
					 else { ?>
					<td align='left'>&nbsp;</td>
				<?php 	} ?>
			</tr>
		<?php
			if ($prow_[0]=='CCD') {
				/** create row for CCD 2**/
			?>
			<tr bgcolor="#FFFFFF" id="CCD2" style="display:none;">
				<td valign='top'><?=mxlangtxt($prow_[1])?> 2</td>
				<td align='right' valign='top'>
					<input class='input1 payments' type='text' id='cbo_payinput_ccd2' name='cbo_payinput_ccd2' value='0'
					onChange='calc_tot_pay();' size='15'>
					<input type='hidden' id='cbo_paytype_ccd2' name='cbo_paytype_ccd2' value='CCD2'>
				</td>
				<td align='left'>
					<table>
						<tr>
							<td><?=ucwords(strtolower(mxlang(159)))?></td>
							<td><input type='text' name='ccd2_no' size='20' maxlength='16'></td>
						</tr>
						<tr>
							<td><?=ucwords(strtolower(mxlang(160)))?></td>
							<td><input type='text' name='ccd2_name' size='16'></td>
						</tr>
						<tr>
							<td><?=ucwords(strtolower(mxlang(161)))?></td>
							<td>
								<input class="datexp" type="text" id="ccd2_exp" name="ccd2_exp" placeholder="MM/YYYY" value="<?=($_POST["ccd2_exp"]=="")?$exptransaction_localdate:$_POST["ccd2_exp"]?>" size="12" maxlength="10" >
								<!--								<a href="javascript:void(0)" onClick="gfPop.fPopCalendar(frm_invcnt_fltr.ccd2_exp);return false;" hidefocus>
									<img src="../images/cal_show.gif" border="0">
								</a>-->
                                <br><span id="ccd2_exp" style="color: red;"></span>
							</td>
						</tr>
						<tr>
							<td><?=ucwords(strtolower(mxlang(162)))?></td>
							<td>
								<select name='ccd2_type'>
									<option value='MC'><?=ucwords(strtolower(mxlang("1023")))?></option>
									<option value='VS'><?=ucfirst(strtolower(mxlang("1541")))?></option>
									<option value='DC'><?=ucfirst(strtolower(mxlang("513")))?></option>
									<option value='DN'><?=ucfirst(strtolower(mxlang(3319)))?></option>
									<option value='AE'><?=mxlang(3320)?></option>
								</select>
							</td>
						</tr>
						<tr>
							<td><?=mxlang(3327)?></td>
							<td><input type='text' name='ccd2_autho' size='5'></td>
						</tr>
					</table>
				</td>
			</tr>
			<?php
			}
		} //end for
		} // else then AR pay
	} //end if
	
	if($ccd_flag == 1) {
	?>
	<tr bgcolor="#FFFFFF">
		<td align="left" colspan="3">
			<input type="checkbox" class="cekbox8" name="ccd1_flag" id="ccd1_flag" value="1" onChange="ccd1_manual()">
			Credit Card 1 - Manual Approve (Notes: Credit Card 1 data will not send to Authorized.net)<br>
			Payment Reference Number: <input type='text' name='ccd1_ref' id='ccd1_ref' size='20' disabled>
		</td>
	</tr>
	<tr bgcolor="#FFFFFF">
		<td align="left" colspan="3">
			<input type="checkbox" class="cekbox8" name="ccd2_flag" id="ccd2_flag" value="2" onChange="ccd2_manual()">
			Credit Card 2 - Manual Approve (Notes: Credit Card 2 data will not send to Authorized.net)<br>
			Payment Reference Number: <input type='text' name='ccd2_ref' id='ccd2_ref' size='20' disabled>
		</td>
	</tr>
	<?php
	}
?>
	<tr bgcolor="#E6E6E6">
		<td align='right'><font size="2"><b><?=mxlang("164")?> :</b></td>
		<td align='right'><font size="2"><b><div id='total_pay'><?=$cursign.' '.number_format($paytotal,2)?></div></b></td>
		<td align='left'><div id="total_conv" style="display:none;">&nbsp;</div></td>
	</tr>
	<tr bgcolor="#E6E6E6" valign="top">
		<td align='right'><font size="2" ><b id="b_return_txt"><?=mxlang(1952)//mxlang(165)?> :</b></td>
		<td align='right'><font size="2"><b><div id='return_pay'><?=$cursign.' '.number_format($return_total,2)?></div></b></td>
		<td align='left'>&nbsp;
		<span id="return_conv" style="display:none;">&nbsp;</span>
<?php
	//echo("PHP_SELF=".$_SERVER["SCRIPT_NAME"]);
	if (
		(
			preg_match('/^\/cb_order_fltr/',$_SERVER["SCRIPT_NAME"]) ||
			preg_match('/^\/sp_order_fltr/',$_SERVER["SCRIPT_NAME"]) ||
			preg_match('/^\/sc_order_fltr.php/',$_SERVER["SCRIPT_NAME"]) ||
			preg_match('/^\/dist_order_fltr.php/',$_SERVER["SCRIPT_NAME"]) ||
			preg_match('/^\/sccr_order_fltr_new.php/',$_SERVER["SCRIPT_NAME"])
		)
		//&& $priv!='3'
		//change back based 080219-003 at Feedback080219.odt
	 && ($chk_bool || $vou_bool)
	) {
?>
		<input type='checkbox' name='allow_return' value='1' <? if (preg_match('\/product_ordering/sp_order_fltr',$_SERVER["SCRIPT_NAME"]) || preg_match('\/scenters/sccr_order_fltr_new',$_SERVER["SCRIPT_NAME"])) {
		 echo "onClick='changeDis();'";  } ?>>&nbsp;
		<?=ucfirst(strtolower(mxlang("2017"))) 	?>
<?php
	}else{
		echo("<input type='hidden' name='allow_return' value='0'>\n");
	}

	if($ar_flag==1 && $beside_return==1) {
		echo "<span id='ar_pay'><input type='checkbox' name='acc_receive' id='acc_receive' value='Y' onclick='calc_tot_pay()'> ".mxlang(3408)."</span>";
	}
?>
			<input type='hidden' name='ccpay_conv' id='ccpay_conv'>
			</td>
		</tr>

		<tr id="excp_row" bgcolor="#E6E6E6" style="display:none;">
			<td align='right'><font size="2"><b><?=mxlang("3359")?> :</b></td>
			<td align='right'><font size="2"><b><div id='total_excp'><?=$cursign.' 0.00'?></div></b></td>
			<td align='left'>&nbsp;</td>
		</tr>
		
		<tr bgcolor="#E6E6E6">
			<td colspan='3'>&nbsp;
				<input type='hidden' name='count_pay' value='<?=$ip?>'>
				<input id="return_total" name="return_total" type="hidden" value="<?=$return_total?>">
			</td>
		</tr>
	</table>
        <script>
            
        
            jq(function () {
                jq(".datexp").bind("keyup", function (e) {
                    this.value = this.value.replace(/[^0-9/]/g, '');
                });
                jq(".datexp").datepicker({dateFormat: "mm/yy", minDate: "d", showButtonPanel: true, changeMonth: true, changeYear: true, showOn: "button", buttonImage: "../images/cal_show.gif", buttonImageOnly: true, onClose: function(dateText, inst) { 
                var month = jq("#ui-datepicker-div .ui-datepicker-month :selected").val();
                var year = jq("#ui-datepicker-div .ui-datepicker-year :selected").val();
                jq(this).datepicker('setDate', new Date(year, month, 1));
            },
            beforeShow : function(input, inst) {
                if ((datestr = jq(this).val()).length > 0) {
                    actDate = datestr.split('-');
                    year = actDate[0];
                    month = actDate[1]-1;
//                    console.log("this:"+this.value);
                    if(this.value !=""){
                        var tv = this.value.split("/");
                        month=tv[0]-1;
                        year=tv[1];
                        if (month > year){
                            alert("Invalid Expiry Date");
                        }
                    }
                    jq(this).datepicker('option', 'defaultDate', new Date(year, month));
                    jq(this).datepicker('setDate', new Date(year, month));
                }
            }});
            });
        </script>
        <style type="text/css">
            .hasDatepicker{
                padding-top:3px;
                padding-bottom:-3px;
            }
            .ui-datepicker-trigger
            {
                padding:0px;
                padding-left:3px;
                vertical-align:baseline;

                position:relative;
                top:0px;
                height:15px;
            }
            .ui-datepicker-calendar {
            display: none;
            }
        </style>
<?php
}

//3024 - 3770
function frm_payment_tr_ioc() {
	
	global $db,$presult1,$vou_bool,$chk_bool,$vou_text,$vou_curr,$vou_rate,$chk_text,$chk_curr,$chk_rate,$cursign,$vou_input,$chk_input, $priv, $show_ccd, $show_btr, 
	$new_contra_db, $txt_scid, $txt_key, $xtxt_key, $return_total, $tmp_file_ref, $xmodule_name, $xloccd, $date1, $month1, $year1,$show_arlist_trx, $transaction_localdate,
	$txt_grandtotal,$credit_flag,$region,$db2,$db3;
	if ($return_total=='') $return_total = 0 - $txt_grandtotal;
	$transaction_timestamp = get_timestamp(get_timezone(TZ_COUNTRY,$region));
	if (empty($transaction_localdate)) $transaction_localdate = date("d/m/Y",$transaction_timestamp);
    //$exptransaction_localdate=date("m/y",$transaction_timestamp);
?>
        
	<input type="hidden" name="tmp_batch_vou_txt">
	<input type="hidden" name="tmp_batch_che_txt">
	<input type="hidden" name="tmp_exp_vou_txt">
	<input type="hidden" name="tmp_exp_che_txt">
	<input type='hidden' name='is_stockist' value='<?=$txt_scid?>'>
	<input type='hidden' name='xtxt_key' value='<?=(($xtxt_key!='')?$xtxt_key:$txt_key);?>'>
	<input type="hidden" name="tmp_che_contraloc">
	<input type="hidden" name="tmp_che_memcode">
	<input type="hidden" name="tmp_che_status">
	<input type="hidden" name="tmp_vou_contraloc">
	<input type="hidden" name="tmp_vou_memcode">
	<input type="hidden" name="tmp_vou_status">
	<input type="hidden" name="xloccd" value='<?=$xloccd?>'>
	<input type="hidden" name="tmp_file_ref" value='<?=(($tmp_file_ref!='')?$tmp_file_ref:$xmodule_name);?>'>
	<table width="90%" border="0" cellspacing="1" cellpadding="1" bgcolor="#CCCCCC">
		<tr bgcolor="#E6E6E6">
			<td width=20% align="center"><?=ucwords(strtolower(mxlang("157")))?></td>
			<td width=10% align="center"><?=ucwords(strtolower(mxlang("152")))?></td>
			<td align="center" ><?=ucwords(strtolower(mxlang("1708")))?></td>
		</tr>
		<?php
			$ar_flag = 0;
			$beside_return = 0;
			$paytotal = 0;
			$ccd_flag = 0;
			if (pg_numrows($presult1)>0) {
				for ($ip=0;$ip<pg_num_rows($presult1);$ip++) {
					$prow_ = pg_fetch_row($presult1, $ip);
					if ($prow_[0]=='AR' && $prow_[2]=='t') { 
						$ar_flag = 1;
						$beside_return = ($prow_[2]=='t')?1:0;
						echo "<input type='hidden' id='cbo_paytype_$ip' name='cbo_paytype_$ip' value='AR'>";
						echo "<input type='hidden' id='cbo_payinput_ar' name='cbo_payinput_$ip' value='0'>";
					} else {
		?>
			<!--tr bgcolor="#FFFFFF" <?=(($prow_[0]=='OTH' AND $creditflag==1)?"":(($prow_[0]!='OTH')?"":" hidden "))?> -->
			<tr bgcolor="#FFFFFF" class="<?=(($prow_[0]=='OTH')?"trOTH":"")?>">
				<td valign='top'><?=mxlangtxt($prow_[1])?> <?=($prow_[0]=='CCD')?"1":""?></td>
				<td align='right' valign='top'  >
					<?php if ($prow_[0]=='EW') { ?>
						<!--<span id="ew_payment">0</span>-->
						<input class='input1 payments' type='text' id='cbo_payinput_ew' name='cbo_payinput_<?=$ip?>' value='0' onChange='calc_tot_pay();' size='15'>
					<?php } else if ($prow_[0]=='ARR') { ?>
						<span id="arr_payment">0</span>
						<input class='input1 payments' type='hidden' id='cbo_payinput_arr' name='cbo_payinput_<?=$ip?>' value='0'>
					<?php } else if ($prow_[0]=='CSH') { 
						$csh_value = ($prow_[3]=='t')?$txt_grandtotal:0;
						$return_total += $csh_value;
						$paytotal += $csh_value;
					?>
						<input class='input1 payments' type='text' id='cbo_payinput_<?=$ip?>' name='cbo_payinput_<?=$ip?>' value='<?=$csh_value?>'
						onChange='calc_tot_pay();' size='15'>
					<?php 
                                        
                       	} else if ($prow_[0]=='OTH') { 
                       		$credit_balance = 0;
                       		if($credit_flag==1) { 
								Logger::debug("Prow:$prow_[0], creditflag:$credit_flag");
								$credit = pg_exec($db,"select credit_bal from credit_history where code='$txt_key'");
								Logger::debug("select credit_bal from credit_history where code='$txt_key'");
								if(pg_numrows($credit)>0) {
									$crow = pg_fetch_row($credit, 0);
									$credit_balance = $crow[0];
									Logger::debug("credit data : ",$crow);
									if($credit_balance > $txt_grandtotal)
										 $credit_balance = $txt_grandtotal;
								}
								$return_total += $credit_balance;
								$paytotal += $credit_balance;
							}
					?>
							<input class='input1 payments <?=$prow_[0]?>' type='text' id='cbo_payinput_<?=$ip?>' name='cbo_payinput_<?=$ip?>' value='<?=$credit_balance?>' 
	                   		onChange='calc_tot_pay();' size='15' readonly>
					<?php 
						} else if ($prow_[0]!='OTH') { 
					?>
						<input class='input1 payments ' type='text' id='cbo_payinput_<?=$ip?>' name='cbo_payinput_<?=$ip?>' value='0'
						onChange='calc_tot_pay();' size='15' >
					<?php } ?>
					<input type='hidden' id='cbo_paytype_<?=$ip?>' name='cbo_paytype_<?=$ip?>' value='<?=$prow_[0]?>'>
				</td>
				
				<?php
					/** close down as per request lacey 27-11-2017 **/
					/*
					if ($prow_[0]=='EW') {
					
					
				?>
					<td>
					  <input type="hidden" value="" name="ew_no" id="ew_no">
						<?php
						<input type="button" value="<?=mxlang(4368)?>" style="margin-left:3px;" onclick="<?=($xmodule_name=="sccb" || $xmodule_name=="iocred")?"validate_member();":"$('wallet').submit();"?>">
						<?php
							if(($xmodule_name=="brcb")||($xmodule_name=="msinv")||($xmodule_name=="brinv")||($xmodule_name=="sccb")){
						?>
								<input type="button" value="<?=mxlang(274)?>" style="margin-left:3px;" onclick="$('ew_payment').update('0');$('ew_no').value=null;$('cbo_payinput_ew').value = 0;$('ep_is_discount').hide();$('ep_discount').value = '0';$('txt_after_deduct_ep').value = 0;calc_tot_pay();">
						<?php
							}
							else{
						?>
								<input type="button" value="<?=mxlang(274)?>" style="margin-left:3px;" onclick="$('ew_payment').update('0');$('ew_no').value=null;$('cbo_payinput_ew').value = 0;calc_tot_pay();">
						<?php
							}
						?>
					</td>
				<?php
					}
					else */ 
                                if ($prow_[0]=='ARR') {
				?>
					<td align='left'>
						<input type="hidden" name="ar_redeem" id="ar_redeem">
						<input type="hidden" name="ar_trxredeem" id="ar_trxredeem">
						<div>
							<input type='button' onclick='show_arrlist()' value='<?=mxlang(3409)?>' name='btn_arrlist'>
							<input type='button' onclick='reset_arr()' value='<?=mxlang(274)?>' name='btn_arr_reset'>
						</div>
					</td>
				<?php
					}
					elseif ($prow_[0]=='DEM') {
					
						$delivery = pg_exec($db,"select br_region from msms_new where br_code='$xloccd'");
						$delivery2=pg_fetch_row($delivery);
						if(pg_num_rows($delivery)<=0) {
							$delivery = pg_exec($db,"select bc_id from mssc where code='$xloccd'");
							$delivery2=pg_fetch_row($delivery);
						}
						$dmRes = pg_exec($db,"select a.delivery_id, a.delivery_name from delivery_method a, delivery_method_country b where a.delivery_id = b.delivery_id and cn_id='$delivery2[0]' order by delivery_name");
				?>
					<td align='left'>
					  <table>
							<tr>
								<td>Company Name</td>
								<td>
									<select name="delivery_method_<?=$ip?>">
										<?php
											if(pg_numrows($dmRes)>0)
												echo "<option value=''>".mxlang(1757)."</option>";
											for ($d=0;$d<pg_num_rows($dmRes);$d++) {
												$dem=pg_fetch_row($dmRes,$d);
												echo "<option value='$dem[0]'>",$dem[1],"</option>";
											}
										?>
									</select>
								</td>
							</tr>
					  </table>
					</td>
				<?php
					}
					elseif ($prow_[0]=='BDEC') {
				?>
					<td align='left'>
						<table>
							<tr>
								<td><?=ucwords(strtolower(mxlang("1709")))?></td>
								<td><input type='text' name='txt_batchno' onBlur="getBatch(this.value)"></td>
								<td><input type='hidden' name='txt_cbno'></td>
							</tr>
						</table>
					</td>
				<?php
					}
					//elseif ($prow_[0]=='CCD' && $show_ccd=='1') {
					elseif ($prow_[0]=='CCD') {
						if($xmodule_name != 'brcr') {
							/** always show detail credit card input payment since most of transaction will use this method **/
							include_once ("../module/conn_centralise_db.php"); 
							$ccd_flag = 1;
							$meminfo = $db3->doQuery("select * from msmemb where code='$txt_key'")->getFirstRow();
		                  	Logger::debug("meminfo:", $meminfo);
							$db3->close_db_con();
						}
				?>
					<td align='left'>
						<table>
							<tr>
								<td><?=ucwords(strtolower(mxlang("159")))?></td>
                                <td>
                                	<input type='text' name='ccd_no_<?=$ip?>' size='16' maxlength='16'><br>
                                    <div class="popup"><span class="popuptext" id="span_ccd_no_<?=$ip?>" style="color: red;"></span></div>
                                </td>
							</tr>
							<tr>
								<td><?=ucwords(strtolower(mxlang("160")))?></td>
								<td>
									<input type='text' name='ccd_name_<?=$ip?>' size='16'><br>
                                    <div class="popup"><span class="popuptext" id="span_ccd_name_<?=$ip?>" style="color: red;"></span></div>
								</td>
							</tr>
							<tr>
								<td><?=ucwords(strtolower(mxlang(161)))?></td>
								<td>
                                                                        
                                        <input class="datexp" type="text" id="ccd_exp_<?=$ip?>" name="ccd_exp_<?=$ip?>" placeholder="MM/YYYY" value="<?=($_POST["ccd_exp_$ip"]=="")?$exptransaction_localdate:$_POST["ccd_exp_$ip"]?>" size="12" maxlength="10" >
<!--									<a href="javascript:void(0)" onClick="gfPop.fPopCalendar(frm_invcnt_fltr.ccd_exp_<?=$ip?>);return false;" hidefocus>
										<img src="../images/cal_show.gif" border="0">
									</a>-->
                                        <br><div class="popup"><span class="popuptext" id="span_ccd_exp_<?=$ip?>" style="color: red;"></span></div>
                                                                        
								</td>
							</tr>
							<tr>
								<td><?=ucwords(strtolower(mxlang(162)))?></td>
								<td>
									<select name='ccd_type_<?=$ip?>'>
										<option value='MC'><?=ucwords(strtolower(mxlang("1023")))?></option>
										<option value='VS'><?=ucfirst(strtolower(mxlang("1541")))?></option>
										<option value='DC'><?=ucfirst(strtolower(mxlang("513")))?></option>
										<option value='DN'><?=ucfirst(strtolower(mxlang(3319)))?></option>
										<option value='AE'><?=mxlang(3320)?></option>
									</select>
                                   <br><div class="popup"><span class="popuptext" id="span_ccd_type_<?=$ip?>" style="color: red;"></span></div>
								</td>
							</tr>
							<tr>
								<td><?=mxlang(3327)?></td>
								<td><input type='text' name='ccd_autho_<?=$ip?>' size='16'><br>
                                    <div class="popup"><span class="popuptext" id="span_ccd_autho_<?=$ip?>" style="color: red;"></span></div>
								</td>
							</tr>
							
							<tbody id="ccd_bill_addr">
							<tr>
								<td colspan="2"><b><?=mxlang(5916)?></b></td>
							</tr>
							<tr valign="top">
								<td><?=ucwords(strtolower(mxlang(24)))?></td>
								<td>
									<?php
										if ($scadd_default=='0'){
											$mem_bill_addr = $meminfo[addr1].($meminfo[addr2]==""?"":"\n$meminfo[addr2]").($meminfo[addr3]==""?"":"\n$meminfo[addr3]");
										} else {
											$mem_bill_addr = $meminfo[saddr1].($meminfo[saddr2]==""?"":"\n$meminfo[saddr2]").($meminfo[saddr3]==""?"":"\n$meminfo[saddr3]");
										}
										
									?>
									<TEXTAREA id="txt_bill_addr_<?=$ip?>" NAME="txt_bill_addr_<?=$ip?>" ROWS=3 COLS=20><?=$mem_bill_addr?></TEXTAREA><br>
									<div class="popup"><span class="popuptext" id="span_txt_bill_addr_<?=$ip?>" style="color: red;"></span></div>
								</td>
							</tr>
                            <tr>
								<td><?=mxlang(1081)?></td>
								<td>
                                    <input type='text' name='txt_bill_postcd_<?=$ip?>' size='16' onchange="" value="<?=$meminfo[spostcd]?>">
                                    <br><div class="popup"><span class="popuptext" id="span_txt_bill_postcd_<?=$ip?>" style="color: red;" ></span></div>
                                </td>
							</tr>
							<tr>
								<td><?=mxlang(382)?>
                                    <script>
	                                    function doGetShipToCity<?=$ip?>(cnid, zipcode, shiptown) {
	                                        var url = "../module/module_pop.php";
	                                        jq("#td_bill_city_<?=$ip?>").empty().html("<option value=''>Loading</option>");
	                                        jq.post(url, {
	                                            actship: "zipcity",
	                                            cnid: cnid,
	                                            zipcode: zipcode,
	                                            shiptown: shiptown,
	                                            idname:"txt_bill_city_<?=$ip?>"
	                                        }, function (data) {
	                                            jq("#td_bill_city_<?=$ip?>").empty().html(data);
	                                            doGetShipToState<?=$ip?>(cnid, zipcode,"<?=$meminfo["sst_id"]?>");
	                                        });
	                                    }
	                                    //doGetShipToCity("US", "<?=$meminfo[postcd]?>", "<?=$meminfo[town]?>");
	                                </script>
                                </td>
								<td id="td_bill_city_<?=$ip?>">
									<select name="txt_bill_city_<?=$ip?>">
									<?php
									$city_info = $GLOBALS["db2"]->doQuery("select distinct city from zipcode order by city")->toArray();
									if(count($city_info)>0) {
										foreach($city_info as $c) {
											$city_selected = (strtoupper($c["city"]) == strtoupper($meminfo["stown"]))?"selected":"";
											//echo "<option value='{$c["city"]}' $city_selected> {$c["city"]} </option>";
										}
									}
									?>
									</select><br>
                                    <span id="span_txt_bill_city_<?=$ip?>" style="color: red;"></span>
                                    
                                                                
								</td>
							</tr>
							<tr>
								<td><?=mxlang(383)?>
                                    <script>
                                        function doGetShipToState<?=$ip?>(cnid, zipcode, state_id) {
                                            var url = "../module/module_pop.php";
                                            jq("#td_bill_state_<?=$ip?>").empty().html("<option value=''>Loading</option>");
                                            jq.post(url, { 
                                                actship: "zipstate",
                                                cnid: cnid,
                                                zipcode: zipcode,
                                                state_id: state_id,
                                                idname:"txt_bill_state_<?=$ip?>"
                                            }, function (data) {
//                                                jq("select[name=txt_bill_state_<?=$ip?>]").empty().html(data);
                                                jq("#td_bill_state_<?=$ip?>").empty().html(data); 

                                            });
                                        }
                                        function doRundoGetShipToCity(){
                                            var zip = jq('input[name=txt_bill_postcd_<?=$ip?>]').val();
                                            doGetShipToCity<?=$ip?>('US', zip,"<?=$meminfo[stown]?>");
                                        }
                                        function doRundoGetShipToCityTown(town){
                                            var zip = jq('input[name=txt_bill_postcd_<?=$ip?>]').val();
                                            doGetShipToCity<?=$ip?>('US', zip,town);
                                        }
                                        doRundoGetShipToCity();
                                        jq(function () {
                                            jq('input[name=txt_bill_postcd_<?=$ip?>]').change(function(){
                                                doGetShipToCity<?=$ip?>('US', this.value);
                                            });
                                            jq('input[name=txt_bill_tel_<?=$ip?>]').mask('(000)000-0000');
                                        });
                                    </script>
                                </td>
								<td id="td_bill_state_<?=$ip?>">
									<?php
									if($GLOBALS["db2"]->doQuery("select st_id,st_name from state where st_id='".($meminfo[sst_id]==""?0:$meminfo[sst_id])."' order by st_name ")->isFound()) {
										$st_info = $GLOBALS["db2"]->toArray();
										if(count($st_info)>1) {
											?>
											<select name="txt_bill_state_<?=$ip?>">
											</select>
											<? 
										} else 
											echo "{$st_info[0]["st_name"]} <input type='hidden' id='txt_bill_state_$ip' name='txt_bill_state_$ip' value='{$meminfo["sst_id"]}'>";
									}
									?>
									<br>
                                    <span id="span_txt_bill_state_<?=$ip?>" style="color: red;"></span>
                                    
								</td>
							</tr>
							
							<tr>
								<td><?=mxlang(72)?></td>
								<td>
                                    UNITED STATES
<!--								<select name="txt_bill_country_<?=$ip?>">
								<?php
//								$cn_info = $GLOBALS["db2"]->doQuery("select iso,upper(trim(initcap(name))) as name from country_list order by name")->toArray();
//								if(count($cn_info)>0) {
//									$meminfo["cn_id"] = (empty($meminfo["cn_id"]))?"US":$meminfo["cn_id"];
//									foreach($cn_info as $cn) {
//										$cn_selected = ($cn["iso"] == $meminfo["cn_id"])?"selected":"";
//										echo "<option value='{$cn["iso"]}' $cn_selected> {$cn["name"]} </option>";
//									}
//								}
								?>
								</select>-->
                                    <input type="hidden" name="txt_bill_country_<?=$ip?>" value="US" ><br>
                                    <span id="span_txt_bill_country_<?=$ip?>" style="color: red;"></span>
								</td>
							</tr>
							<tr>
								<td><?=mxlang(384)?></td>
								<td><input type='text' id='txt_bill_tel_<?=$ip?>' name='txt_bill_tel_<?=$ip?>' size='16' value="<?=$meminfo["ship_tel"]?>"><br>
									<span id="span_txt_bill_tel_<?=$ip?>" style="color: red;"></span>
								</td>
							</tr>
							</tbody>
						</table>
					</td>
				<?php
					}

					/** add payment for credit card online */
					elseif ($prow_[0]=='CCO') {
				?>
					<td align='left'>
						<table>
							<tr>
								<td><?=ucwords(strtolower(mxlang("159")))?></td>
								<td><input type='text' name='cco_no' size='16' maxlength='16'></td>
							</tr>
							<tr>
								<td><?=ucwords(strtolower(mxlang("160")))?></td>
								<td><input type='text' name='cco_name' size='16'></td>
							</tr>
							<tr>
								<td valign="top"><?=ucwords(strtolower(mxlang("24")))?></td>
								<td><textarea id="cco_address" cols="30" rows="2" name="cco_address"></textarea></td>
							</tr>
							<tr>
								<td><?=ucwords(strtolower(mxlang("1081")))?></td>
								<td><input type='text' name='cco_zipcode' size='10'></td>
							</tr>
							<tr>
								<td><?=ucwords(strtolower(mxlang("161")))?></td>
								<td>
									<input type="text" id="cco_exp" name="cco_exp" value="<?=($cco_exp=="")?$transaction_localdate:$cco_exp;?>" size="12" maxlength="10">
									<a href="javascript:void(0)" onClick="gfPop.fPopCalendar(frm_cb.cco_exp);return false;" hidefocus>
										<img src="../images/cal_show.gif" border="0">
									</a>
								</td>
							</tr>
							<tr>
								<td><?=ucwords(strtolower(mxlang("1972")))?></td>
								<td><input type='text' name='cco_autho' size='16'></td>
							</tr>
						</table>
					</td>
				<?php
					}
				/** end credit card online */

					elseif ($prow_[0]=='BTR' && $show_btr=='1') {
				?>
					<td align='left'>
						<table>
							<tr>
								<td><?=ucwords(strtolower(mxlang("1973")))?></td>
								<td><input type='text' name='btr_no_<?=$ip?>' size='16'></td>
							</tr>
							<tr>
								<td><?=ucwords(strtolower(mxlang("657")))?></td>
								<td><input type='text' name='btr_name_<?=$ip?>' size='16'></td>
							</tr>
						</table>
					</td>
				<?php
					}
					elseif ($prow_[0]=='CKK') {
				?>
					<td align='left'>
						<table>
							<tr>
								<td><?=mxlang("657")?></td>
								<td><input type='text' name='txt_bankName_<?=$ip?>' size='15'></td>
							</tr>
							<tr>
								<td><?=ucfirst(strtolower(mxlang("1709")))?></td>
								<td><input type='text' name='cbo_paynumber_<?=$ip?>' size='15'></td>
							</tr>
							<tr>
								<td><?=ucwords(strtolower(mxlang("1658")))?></td>
								<td>
									<input type='text' name='txt_paydate_<?=$ip?>' size='11' maxlength='10'>
									<a href="javascript:void(0)" onClick="gfPop.fPopCalendar(frm_cb.txt_paydate_<?=$ip?>);return false;" hidefocus>
									<img src="../images/cal_show.gif" border="0"></a>
								</td>
							</tr>
						</table>
					</td>
				<?php
					}
					elseif ($prow_[0]=='VOU') {
						$vou_bool = true;
						$vou_text="cbo_paynumber_$ip";
						$vou_input="cbo_payinput_$ip";
						$vou_curr="cbo_paycurr_$ip";
						$vou_rate="cbo_payrate_$ip";
				?>
					<td align='left'>
						<table>
							<tr>
								<td><?=ucfirst(strtolower(mxlang("1709")))?></td>
								<td>
									<input type='text' name='<?=$vou_text?>' size='15' onChange='vou_inedit()' <? if ($new_contra_db) echo "onBlur=\"fget_contra_value('V')\""; ?>>
									<input type='hidden' name='<?=$vou_curr?>' size='15' onChange='vou_inedit()'>
									<input type='hidden' name='<?=$vou_rate?>' size='15' onChange='vou_inedit()'>
									<? if ($new_contra_db==1) { ?>
										<input type='button' onclick='' value='<?=mxlang("1617")?>' name='btn_go'>
									<? } else { ?>
										<input type='button' onclick='chkvcr1();' value='<?=mxlang("1617")?>' name='btn_go'>
									<? } ?>
								</td>
							</tr>
						</table>
					</td>
				<?php
					}
					elseif ($prow_[0]=='CHK') {
						$chk_bool = true;
						$chk_text="cbo_paynumber_$ip";
						$chk_input="cbo_payinput_$ip";
						$chk_curr="cbo_paycurr_$ip";
						$chk_rate="cbo_payrate_$ip";
				?>
					<td align='left'>
						<table>
							<tr>
								<td><?=mxlang(657)?></td>
								<td><input type='text' name='chk_bank_name_<?=$ip?>' size='20'>	</td>
							</tr>
							<tr>
								<td><?=mxlang(1709)?></td>
								<td>
									<input type='text' name='<?=$chk_text?>' size='15'>
									<input type='hidden' name='<?=$chk_curr?>'>
									<input type='hidden' name='<?=$chk_rate?>'>
								</td>
							</tr>
							<tr>
								<td><?=mxlang(400)?></td>
								<td>
									<input type='text' name='txt_chkdate_<?=$ip?>' value="<?=($_POST["txt_chkdate_$ip"]=="")?$transaction_localdate:$_POST["txt_chkdate_$ip"]?>" size='11' maxlength='10'>
									<a href="javascript:void(0)" onClick="gfPop.fPopCalendar(frm_cb.txt_chkdate_<?=$ip?>);return false;" hidefocus>
									<img src="../images/cal_show.gif" border="0"></a>
								</td>
							</tr>
						</table>
					</td>
				<?php
					}
					elseif ($prow_[0]=='DBT' && $show_ccd=='1') {
				?>
					<td align='left'>
						<table>
							<tr>
								<td><?=ucwords(strtolower(mxlang(4131)))?></td>
								<td><input type='text' name='dbt_no_<?=$ip?>' size='16' maxlength='16'></td>
							</tr>
							<tr>
								<td><?=ucwords(strtolower(mxlang(160)))?></td>
								<td><input type='text' name='dbt_name_<?=$ip?>' size='16'></td>
							</tr>
							<tr>
								<td><?=ucwords(strtolower(mxlang(161)))?></td>
								<td>
									<input type="text" id="dbt_exp_<?=$ip?>" name="dbt_exp_<?=$ip?>" value="<?=($_POST["dbt_exp_$ip"]=="")?date("d/m/Y"):$_POST["dbt_exp_$ip"];?>" size="12" maxlength="10">
									<a href="javascript:void(0)" onClick="gfPop.fPopCalendar($('dbt_exp'));return false;" hidefocus>
										<img src="../images/cal_show.gif" border="0">
									</a>
								</td>
							</tr>
							<tr>
								<td><?=ucwords(strtolower(mxlang(162)))?></td>
								<td>
									<select name='dbt_type_<?=$ip?>'>
										<option value='MC'><?=ucwords(strtolower(mxlang("1023")))?></option>
										<option value='VS'><?=ucfirst(strtolower(mxlang("1541")))?></option>
										<option value='DC'><?=ucfirst(strtolower(mxlang("513")))?></option>
									</select>
								</td>
							</tr>
							<tr>
								<td><?=ucwords(strtolower(mxlang(1972)))?></td>
								<td><input type='text' name='dbt_autho_<?=$ip?>' size='16'></td>
							</tr>
						</table>
					</td>
				<?php 	}
					elseif ($prow_[0]=='ET') {
				?>
					<td align='left'>
						<table>
							<tr>
								<td><?=ucwords(strtolower(mxlang("4653")))." ".ucwords(strtolower(mxlang("1755")))?></td>
								<td><input type='text' name='txt_refno'></td>
							</tr>
						</table>
					</td>
				<?php
					}
					 else { ?>
					<td align='left'>&nbsp;</td>
				<?php 	} ?>
			</tr>
<?php
			if ($prow_[0]=='CCD') {
				/** create row for CCD 2**/
			?>
			<tr bgcolor="#FFFFFF" id="CCD2" style="display:none;">
				<td valign='top'><?=mxlangtxt($prow_[1])?> 2</td>
				<td align='right' valign='top'>
					<input class='input1 payments' type='text' id='cbo_payinput_ccd2' name='cbo_payinput_ccd2' value='0'
					onChange='calc_tot_pay();' size='15'>
					<input type='hidden' id='cbo_paytype_ccd2' name='cbo_paytype_ccd2' value='CCD2'>
				</td>
				<td align='left'>
					<table>
						<tr>
							<td><?=ucwords(strtolower(mxlang(159)))?></td>
							<td><input type='text' name='ccd2_no' size='16' maxlength='16'></td>
						</tr>
						<tr>
							<td><?=ucwords(strtolower(mxlang(160)))?></td>
							<td><input type='text' name='ccd2_name' size='16'></td>
						</tr>
						<tr>
							<td><?=ucwords(strtolower(mxlang(161)))?></td>
							<td>
								<input class="datexp" type="text" id="ccd2_exp" name="ccd2_exp" placeholder="MM/YYYY" value="<?=($_POST["ccd2_exp"]=="")?$exptransaction_localdate:$_POST["ccd2_exp"]?>" size="12" maxlength="10" >
<!--								<a href="javascript:void(0)" onClick="gfPop.fPopCalendar(frm_invcnt_fltr.ccd2_exp);return false;" hidefocus>
									<img src="../images/cal_show.gif" border="0">
								</a>-->
                                <br><span id="ccd2_exp" style="color: red;"></span>
							</td>
						</tr>
						<tr>
							<td><?=ucwords(strtolower(mxlang(162)))?></td>
							<td>
								<select name='ccd2_type'>
									<option value='MC'><?=ucwords(strtolower(mxlang("1023")))?></option>
									<option value='VS'><?=ucfirst(strtolower(mxlang("1541")))?></option>
									<option value='DC'><?=ucfirst(strtolower(mxlang("513")))?></option>
									<option value='DN'><?=ucfirst(strtolower(mxlang(3319)))?></option>
									<option value='AE'><?=mxlang(3320)?></option>
								</select>
							</td>
						</tr>
						<tr>
							<td><?=mxlang(3327)?></td>
							<td><input type='text' name='ccd2_autho' size='16'></td>
						</tr>
					</table>
				</td>
			</tr>
			<?php
			}
		} //end for
		} // else then AR pay
	} //end if
	
	if($ccd_flag == 1) {
	?>
	<tr bgcolor="#FFFFFF">
		<td align="left" colspan="3">
			<input type="checkbox" class="cekbox8" name="ccd1_flag" id="ccd1_flag" value="1" onChange="ccd1_manual()">
			Credit Card 1 - Manual Approve (Notes: Credit Card 1 data will not send to Authorized.net)
		</td>
	</tr>
	<tr bgcolor="#FFFFFF">
		<td align="left" colspan="3">
			<input type="checkbox" class="cekbox8" name="ccd2_flag" id="ccd2_flag" value="2" onChange="ccd2_manual()">
			Credit Card 2 - Manual Approve (Notes: Credit Card 2 data will not send to Authorized.net)
		</td>
	</tr>
	<?php
	}
?>
	<tr bgcolor="#E6E6E6">
		<td align='right'><font size="2"><b><?=mxlang("164")?> :</b></td>
		<td align='right'><font size="2"><b><div id='total_pay'><?=$cursign.' '.number_format($paytotal,2)?></div></b></td>
		<td align='left'><div id="total_conv" style="display:none;">&nbsp;</div></td>
	</tr>
	<tr bgcolor="#E6E6E6" valign="top">
		<td align='right'><font size="2"><b><?=mxlang("165")?> :</b></td>
		<td align='right'><font size="2"><b><div id='return_pay'><?=$cursign.' '.number_format($return_total,2)?></div></b></td>
		<td align='left'>&nbsp;
		<span id="return_conv" style="display:none;">&nbsp;</span>
<?php
	//echo("PHP_SELF=".$_SERVER["SCRIPT_NAME"]);
	if (
		(
			preg_match('/^\/cb_order_fltr/',$_SERVER["SCRIPT_NAME"]) ||
			preg_match('/^\/sp_order_fltr/',$_SERVER["SCRIPT_NAME"]) ||
			preg_match('/^\/sc_order_fltr.php/',$_SERVER["SCRIPT_NAME"]) ||
			preg_match('/^\/dist_order_fltr.php/',$_SERVER["SCRIPT_NAME"]) ||
			preg_match('/^\/sccr_order_fltr_new.php/',$_SERVER["SCRIPT_NAME"])
		)
		//&& $priv!='3'
		//change back based 080219-003 at Feedback080219.odt
	 && ($chk_bool || $vou_bool)
	) {
?>
		<input type='checkbox' name='allow_return' value='1' <? if (preg_match('\/product_ordering/sp_order_fltr',$_SERVER["SCRIPT_NAME"]) || preg_match('\/scenters/sccr_order_fltr_new',$_SERVER["SCRIPT_NAME"])) {
		 echo "onClick='changeDis();'";  } ?>>&nbsp;
		<?=ucfirst(strtolower(mxlang("2017"))) 	?>
<?php
	}else{
		echo("<input type='hidden' name='allow_return' value='0'>\n");
	}

	if($ar_flag==1 && $beside_return==1) {
		echo "<span id='ar_pay'><input type='checkbox' name='acc_receive' id='acc_receive' value='Y' onclick='calc_tot_pay()'> ".mxlang(3408)."</span>";
	}
?>
			<input type='hidden' name='ccpay_conv' id='ccpay_conv'>
			</td>
		</tr>

		<tr id="excp_row" bgcolor="#E6E6E6" style="display:none;">
			<td align='right'><font size="2"><b><?=mxlang("3359")?> :</b></td>
			<td align='right'><font size="2"><b><div id='total_excp'><?=$cursign.' 0.00'?></div></b></td>
			<td align='left'>&nbsp;</td>
		</tr>
		
		<tr bgcolor="#E6E6E6">
			<td colspan='3'>&nbsp;
				<input type='hidden' name='count_pay' value='<?=$ip?>'>
				<input id="return_total" name="return_total" type="hidden" value="<?=$return_total?>">
			</td>
		</tr>
	</table>
        <script>
            
        
            jq(function () {
                jq(".datexp").bind("keyup", function (e) {
                    this.value = this.value.replace(/[^0-9/]/g, '');
                });
                jq(".datexp").datepicker({dateFormat: "mm/yy", minDate: "d", showButtonPanel: true, changeMonth: true, changeYear: true, showOn: "button", buttonImage: "../images/cal_show.gif", buttonImageOnly: true, onClose: function(dateText, inst) { 
                var month = jq("#ui-datepicker-div .ui-datepicker-month :selected").val();
                var year = jq("#ui-datepicker-div .ui-datepicker-year :selected").val();
                jq(this).datepicker('setDate', new Date(year, month, 1));
            },
            beforeShow : function(input, inst) {
                if ((datestr = jq(this).val()).length > 0) {
                    actDate = datestr.split('-');
                    year = actDate[0];
                    month = actDate[1]-1;
//                    console.log("this:"+this.value);
                    if(this.value !=""){
                        var tv = this.value.split("/");
                        month=tv[0]-1;
                        year=tv[1];
                        if (month > year){
                            alert("Invalid Expiry Date");
                        }
                    }
                    jq(this).datepicker('option', 'defaultDate', new Date(year, month));
                    jq(this).datepicker('setDate', new Date(year, month));
                }
            }});
            });
        </script>
        <style type="text/css">
            .hasDatepicker{
                padding-top:3px;
                padding-bottom:-3px;
            }
            .ui-datepicker-trigger
            {
                padding:0px;
                padding-left:3px;
                vertical-align:baseline;

                position:relative;
                top:0px;
                height:15px;
            }
            .ui-datepicker-calendar {
            display: none;
            }
        </style>
<?php
}



function js_payment_transaction($xsc) {
	global $vou_bool,$chk_bool,$vou_text,$vou_curr,$vou_rate,$chk_text,$chk_rate,$chk_curr,$vou_input,$chk_input,
	$trxno, $priv, $PHP_SELF, $new_contra_db;

	if ($xsc!='') {
		//if ($priv==2 || $priv==0)
		$xchk = check_stockist_ex($xsc);
		if ($xchk==0) { //main stockist
			$st_url="ms=$xsc&";
		}elseif ($xchk==1) {
			$st_url="sc=$xsc&";
		}
	}
?>
	<script language='JavaScript'>
	function vou_inedit() {
		var shortcut = 	document.frm_invcnt_fltr;
		shortcut.tmp_vou_valid.value=0;
	}

	function chk_inedit() {
		var shortcut = 	document.frm_invcnt_fltr;
		shortcut.tmp_che_valid.value=0;
	}

	<?
	if (false) {		//$vou_bool
	?>
		function chkvcr1() {
			var shortcut = 	document.frm_invcnt_fltr;
			shortcut.tmp_vou_valid.value=0;
			var vcnum = shortcut.<?=$vou_text?>.value;
			if (shortcut.<?=$vou_text?>.value.length>0) {
				var dimensi="height=200,width=200,left=50,top=5,resizable=yes,scrollbars=yes";
var url="../product_ordering/chkvcr.php?vc="+vcnum+"&<?=$st_url?>thesubmit=off&vtype=0&vou_input=<?=$vou_input?>&confirmvalid=1";
				new_window = window.open(url,"voucher_chk",dimensi);
			}else{
				//alert('<?=mxlang("996")?>');
				alert(shortcut.xalert_996.value);
				shortcut.<?=$vou_text?>.focus();
				return false;
			}
		}
		function voucher_contra2() {
			var shortcut = 	document.frm_invcnt_fltr;
			shortcut.tmp_vou_valid.value=0;
			var vcnum = shortcut.<?=$vou_text?>.value;
			var xis_stockist=shortcut.is_stockist.value;
			var xtxt_key=shortcut.xtxt_key.value;
			var xtxt_ref = shortcut.tmp_file_ref.value;
			var xloccd = shortcut.xloccd.value;
			//if (shortcut.<?=$vou_text?>.value.length>0) {
				var dimensi="height=450,width=900,left=5,top=5,resizable=yes,scrollbars=yes";
				//var url="../product_ordering/chkvcr.php?vc="+vcnum+"&<?=$st_url?>thesubmit=off&vtype=1&vou_input=<?=$vou_input?>&confirmvalid=1";
				var url="../compensation/new_vcr_contra.php?vc="+vcnum+"&<?=$st_url?>thesubmit=off&vtype=1&vou_input=<?=$vou_input?>&input_text=<?=$vou_text?>&vou_curr=<?=$vou_curr?>&vou_rate=<?=$vou_rate?>&confirmvalid=1&xref=<?=urlencode($PHP_SELF)?>&is_stockist="+xis_stockist+"&xtxt_key="+xtxt_key+"&xtxt_ref="+xtxt_ref+"&xloccd="+xloccd;
				new_window = window.open(url,"voucher_chk",dimensi);
			//}else{
			//	alert('Please input contra cheque number !');
			//	shortcut.<?=$vou_text?>.focus();
			//	return false;
			//}
		}
	<?
	}
	if (false) {		//$chk_bool
	?>

		function check_contra2() {
			var shortcut = 	document.frm_invcnt_fltr;
			shortcut.tmp_che_valid.value=0;
			var vcnum = shortcut.<?=$chk_text?>.value;
			var xis_stockist=shortcut.is_stockist.value;
			var xtxt_key=shortcut.xtxt_key.value;
			var xtxt_ref = shortcut.tmp_file_ref.value;
			var xloccd = shortcut.xloccd.value;
			//if (shortcut.<?=$chk_text?>.value.length>0) {
				var dimensi="height=450,width=900,left=5,top=5,resizable=yes,scrollbars=yes";
				//var url="../product_ordering/chkvcr.php?vc="+vcnum+"&<?=$st_url?>thesubmit=off&vtype=1&vou_input=<?=$chk_input?>&confirmvalid=1";
				var url="../compensation/new_check_contra.php?vc="+vcnum+"&<?=$st_url?>thesubmit=off&vtype=1&vou_input=<?=$chk_input?>&input_text=<?=$chk_text?>&chk_curr=<?=$chk_curr?>&chk_rate=<?=$chk_rate?>&confirmvalid=1&xref=<?=urlencode($PHP_SELF)?>&is_stockist="+xis_stockist+"&xtxt_key="+xtxt_key+"&xtxt_ref="+xtxt_ref+"&xloccd="+xloccd;
				new_window = window.open(url,"voucher_chk",dimensi);
			//}else{
			//	alert('Please input contra cheque number !');
			//	shortcut.<?=$chk_text?>.focus();
			//	return false;
			//}
		}
		function chkvcr2() {
			var shortcut = 	document.frm_invcnt_fltr;
			shortcut.tmp_vou_valid.value=0;
			var vcnum = shortcut.<?=$chk_text?>.value;
			if (shortcut.<?=$chk_text?>.value.length>0) {
				var dimensi="height=200,width=200,left=50,top=5,resizable=yes,scrollbars=yes";
var url="../product_ordering/chkvcr.php?vc="+vcnum+"&<?=$st_url?>thesubmit=off&vtype=1&vou_input=<?=$chk_input?>&confirmvalid=1";
				new_window = window.open(url,"voucher_chk",dimensi);
			}else{
				//alert('<?=mxlang("997")?>');
				alert(shortcut.xalert_997.value);
				shortcut.<?=$chk_text?>.focus();
				return false;
			}
		}
	<?
	}
	?>
	function valid_contra_voucher() {
		var shortcut = 	document.frm_invcnt_fltr;
		var xvalid=true;
		var exp_passed_contra=false;
		var xtxt_ref = shortcut.tmp_file_ref.value;
		var xloccd = shortcut.xloccd.value;
	<?
		if ($vou_bool) {
/*			echo("if (shortcut.$vou_text.value=='' && shortcut.$vou_input.value!=0) { \n");
			echo("	alert('Please enter voucher number !');\n");
			echo("	shortcut.$vou_text.focus();\n");
			echo("	xvalid=false;\n");
			echo("}\n\n");*/

			if ($new_contra_db==1) {
				//echo "if (/EXP/.test(shortcut.tmp_exp_vou_txt.value)) {\n";
				echo "if (shortcut.tmp_exp_vou_txt.value=='EXP') {\n";
				//echo "	alert(shortcut.xalert_2938.value);\n";
				echo "	alert('Contra Voucher already expired date');\n";
				echo("	shortcut.tmp_vou_valid.value='0';\n");
				echo("	xvalid=false;exp_passed_contra=true\n");
				echo "}else{\n";
				//echo("	xvalid=true;\n");
/*					echo "if (shortcut.$vou_text.value!='' && shortcut.tmp_memcode.value!=shortcut.xtxt_key.value && shortcut.tmp_contraloc.value!=shortcut.xtxt_key.value) {\n";
					echo "	alert('Member code in voucher not same with member code in transaction !');\n";
					echo "	shortcut.tmp_vou_valid.value='0';\n";
					echo "	xvalid=false;exp_passed_contra=true\n";
					echo "}else{\n";
					echo "	xvalid=true;\n";
					echo "}\n";*/
				echo " var arr_contraloc=shortcut.tmp_vou_contraloc.value.split('+');\n";
				echo " var arr_memcode=shortcut.tmp_vou_memcode.value.split('+');\n";
				echo " var arr_status=shortcut.tmp_vou_status.value.split('+');\n";
				echo " if (arr_memcode.length>0 && arr_contraloc==0){ \n";
				echo "  xvalid=true;\n";
				echo "  for (m=0;m<arr_memcode.length;m++) {\n";
				echo "   if ( arr_status[m]=='C') {\n";
				echo "   	if ((shortcut.$vou_text.value!='' && arr_contraloc[m]!=shortcut.xtxt_key.value)) {\n";
				echo "	  	xvalid=false;\n";
				echo "  	 }\n";
				echo "   }\n";
				echo "   if ( arr_status[m]=='N') {\n";
					echo "   if ( xtxt_ref=='brinv' || xtxt_ref=='whinv' || (xtxt_ref=='brcb' && xloccd == '000000014') ) {}\n";
					echo "   else{ \n";
						echo "   	if ((shortcut.$vou_text.value!='' && arr_memcode[m]!=shortcut.xtxt_key.value)) {\n";
						echo "	  	xvalid=false;\n";
						echo "  	 }\n";
					echo "  	 }\n";
				echo "   }\n";
				echo "  }\n"; //end for



				echo "  if (xvalid==false) { ";
				echo "   exp_passed_contra=true;\n";
				echo "	 alert('Member code in cheque not same with member code in transaction !');\n";
				echo "  }\n";
				echo " }else if (arr_memcode.length>0 && arr_contraloc.length>0){ \n";
				echo "  xvalid=true;\n";
				echo "  for (m=0;m<arr_memcode.length;m++) {\n";
				echo "   if ( arr_status[m]=='C') {\n";
				echo "   	if ((shortcut.$vou_text.value!='' && arr_contraloc[m]!=shortcut.xtxt_key.value)) {\n";
				echo "	  	xvalid=false;\n";
				echo "  	 }\n";
				echo "   }\n";
					echo "   if ( arr_status[m]=='N') {\n";
					echo "   if ( xtxt_ref=='brinv' || xtxt_ref=='whinv' || (xtxt_ref=='brcb' && xloccd == '000000014')){} \n";
					echo "   else{ \n";
						echo "   	if ((shortcut.$vou_text.value!='' && arr_memcode[m]!=shortcut.xtxt_key.value)) {\n";
						echo "	  	xvalid=false;\n";
						echo "  	 }\n";
					echo "  	 }\n";
				echo "   }\n";
				echo "  }\n"; //end for
				echo "  if (xvalid==false) { ";
				echo "   exp_passed_contra=true;\n";
				echo "	 alert('Member code in cheque not same with member code in transaction !');\n";
				echo "  }\n";
				echo " }\n";
				echo "}\n";
			}

			if ($new_contra_db!=1) {
				echo("if (shortcut.$vou_text.value!='' && exp_passed_contra==false) { \n");
				echo("	if (shortcut.tmp_vou_valid.value==1) { \n");
				echo("		if (shortcut.tmp_vou_txt.value!=shortcut.$vou_text.value || \n");
				echo(" 			parseFloat(shortcut.$vou_input.value)!=parseFloat(shortcut.tmp_vou_amount.value)) \n");
				echo("		{	\n");
				echo(" 			alert(shortcut.xalert_1975.value); \n");
				echo("			xvalid=false;exp_passed_contra=true;\n");
				echo("		}\n");
				echo("	}else{\n");
				echo(" 		alert(shortcut.xalert_1975.value); \n");
				echo("		xvalid=false;\n");
				echo("	}\n");
				echo("}\n");
				echo("\n");
			}
		}
		echo("return xvalid;\n\n");
	?>
	}
	function valid_contra_cheque() {
		var shortcut = 	document.frm_invcnt_fltr;
		var xvalid=true;
		var exp_passed_contra=false;
		var xtxt_ref = shortcut.tmp_file_ref.value;
		var xloccd = shortcut.xloccd.value;
<?
		if ($chk_bool) {
/*			echo("if (shortcut.$chk_text.value=='' && shortcut.$chk_input.value!=0) { \n");
			echo("	alert('Please enter cheque number !');\n");
			echo("	shortcut.$chk_text.focus();\n");
			echo("	return false;\n");
			echo("}\n\n");*/

			if ($new_contra_db==1) {
				echo "if (/EXP/.test(shortcut.tmp_exp_che_txt.value)) {\n";
				echo "	alert('Contra Cheque already expired date !')\n";
				echo("	shortcut.tmp_che_valid.value='0';\n");
				echo("	xvalid=false;exp_passed_contra=true;\n");
				echo "}else{\n";
				//echo("	xvalid=true;\n");
/*
					echo "if (shortcut.$chk_text.value!='' && shortcut.tmp_memcode.value!=shortcut.xtxt_key.value && shortcut.tmp_contraloc.value!=shortcut.xtxt_key.value) {\n";
					echo "	alert('Member code in cheque not same with member code in transaction !');\n";
					echo "	shortcut.tmp_che_valid.value='0';\n";
					echo "	xvalid=false;exp_passed_contra=true\n";
					echo "}else{\n";
					echo "	xvalid=true;\n";
					echo "}\n";*/
				echo " var arr_contraloc=shortcut.tmp_che_contraloc.value.split('+');\n";
				echo " var arr_memcode=shortcut.tmp_che_memcode.value.split('+');\n";
				echo " var arr_status=shortcut.tmp_che_status.value.split('+');\n";
				echo " if (arr_memcode.length>0 && arr_contraloc==0){ \n";
				echo "  xvalid=true;\n";
				echo "  for (m=0;m<arr_memcode.length;m++) {\n";
				echo "   if ( arr_status[m]=='C') {\n";
				echo "   	if ((shortcut.$chk_text.value!='' && arr_contraloc[m]!=shortcut.xtxt_key.value)) {\n";
				echo "	  	xvalid=false;\n";
				echo "  	 }\n";
				echo "   }\n";
					echo "   if ( arr_status[m]=='N') {\n";
					echo "   if ( xtxt_ref=='brinv' || xtxt_ref=='whinv' || (xtxt_ref=='brcb' && xloccd == '000000014')){} \n";
					echo "   else{ \n";
						echo "   	if ((shortcut.$chk_text.value!='' && arr_memcode[m]!=shortcut.xtxt_key.value)) {\n";
						echo "	  	xvalid=false;\n";
						echo "  	 }\n";
					echo "  	 }\n";
				echo "   }\n";
				echo "  }\n"; //end for



				echo "  if (xvalid==false) { ";
				echo "   exp_passed_contra=true;\n";
				echo "	 alert('Member code in cheque not same with member code in transaction !');\n";
				echo "  }\n";
				echo " }else if (arr_memcode.length>0 && arr_contraloc.length>0){ \n";
						echo "  xvalid=true;\n";
				echo "  for (m=0;m<arr_memcode.length;m++) {\n";
				echo "   if ( arr_status[m]=='C') {\n";
				echo "   	if ((shortcut.$chk_text.value!='' && arr_contraloc[m]!=shortcut.xtxt_key.value)) {\n";
				echo "	  	xvalid=false;\n";
				echo "  	 }\n";
				echo "   }\n";
				echo "   if ( arr_status[m]=='N') {\n";
					echo "   if ( xtxt_ref=='brinv' || xtxt_ref=='whinv' || (xtxt_ref=='brcb' && xloccd == '000000014')) {}\n";
					echo "   else{ \n";
						echo "   	if ((shortcut.$chk_text.value!='' && arr_memcode[m]!=shortcut.xtxt_key.value)) {\n";
						echo "	  	xvalid=false;\n";
						echo "  	 }\n";
					echo "  	 }\n";
				echo "   }\n";
				echo "  }\n"; //end for

				echo "  if (xvalid==false) { ";
				echo "   exp_passed_contra=true;\n";
				echo "	 alert('Member code in cheque not same with member code in transaction !');\n";
				echo "  }\n";
				echo " }\n";
				echo "}\n";
			}
			if ($new_contra_db!=1) {
				echo("if (shortcut.$chk_text.value!='' && exp_passed_contra==false) {\n");
				echo("	if (shortcut.tmp_che_valid.value==1) { \n");
				echo("		if (shortcut.tmp_che_txt.value!=shortcut.$chk_text.value || \n");
				echo(" 			parseFloat(shortcut.$chk_input.value)!=parseFloat(shortcut.tmp_che_amount.value)) \n");
				echo("		{	\n");
				echo(" 			alert(shortcut.xalert_1976.value); \n");
				echo("			xvalid=false;\n");
				echo("		}\n");
				echo("	}else{\n");
				echo(" 		alert(shortcut.xalert_1976.value); \n");
				echo("		xvalid=false;\n");
				echo("	}\n");
				echo("}\n");
			}
		}
		echo("return xvalid;\n\n");
?>
	}

		function fget_contra_value(xcontratype) {
			var shortcut = document.frm_invcnt_fltr;
			var xis_stockist = shortcut.is_stockist.value;
			var xtxt_key = shortcut.xtxt_key.value;
			var xtxt_ref = shortcut.tmp_file_ref.value;
			var xtx_cheq_batch = shortcut.tmp_batch_che_txt.value;
			var xtx_vou_batch = shortcut.tmp_batch_vou_txt.value;
			var xloccd=shortcut.xloccd.value;
			
			//calc_tot_pay();


<?
		if (false) {		//$vou_bool
?>
			if (xcontratype=='V') {
				if (shortcut.<?=$vou_text?>.value!='') {
					var url = '../module/get_contra_value.php?contrano='+shortcut.<?=$vou_text?>.value+'&contratype='+xcontratype+'&is_stockist='+xis_stockist+'&xtxt_key='+xtxt_key+'&xtxt_ref='+xtxt_ref+'&xtx_vou_batch='+xtx_vou_batch+'&xloccd='+xloccd;
					new Ajax.Request(url, {
						method: 'get',
						onSuccess: function(transport) {
							var lbtxt = transport.responseText;
							<?/*OK||0.35|VOU|070903|EXP*/?>
							var arr_split=lbtxt.split("|");
							if (arr_split[0]=='OK')	 {
								shortcut.<?=$vou_input?>.value=arr_split[2];
								shortcut.tmp_batch_vou_txt.value=arr_split[4];
								calc_tot_pay();
								shortcut.tmp_vou_valid.value='1';
								shortcut.tmp_vou_txt.value=shortcut.<?=$vou_text?>.value;
								shortcut.tmp_vou_amount.value=arr_split[2];
								shortcut.tmp_exp_vou_txt.value=arr_split[5];
								shortcut.tmp_vou_contraloc.value=arr_split[6];
								shortcut.tmp_vou_memcode.value=arr_split[7];
								shortcut.tmp_vou_status.value=arr_split[9];
								if (arr_split[8]=='EXP'){
									//alert('Contra Voucher already expired date !')
									//voucher_contra2();
									return false;
								}
							}else{
								shortcut.<?=$vou_input?>.value='0';
								shortcut.tmp_batch_vou_txt.value='';
								shortcut.tmp_vou_valid.value='0';
								shortcut.tmp_vou_amount.value='0';
								shortcut.tmp_exp_vou_txt.value='';
								shortcut.tmp_vou_txt.value='';
								shortcut.tmp_vou_contraloc.value='';
								shortcut.tmp_vou_memcode.value='';
								shortcut.tmp_vou_status.value=arr_split[3];
								if (arr_split[2]=='NOTFOUND') alert(arr_split[1]);
								if (arr_split[2]=='DOUBLE') alert(arr_split[1]);
								//voucher_contra2();
								return false;
							}
						}
					});
				}else{
					//shortcut.<?=$vou_input?>.value='0';
					shortcut.tmp_batch_vou_txt.value='';
					shortcut.tmp_vou_valid.value='0';
					shortcut.tmp_exp_vou_txt.value='';
					shortcut.tmp_vou_amount.value='0';
					shortcut.tmp_vou_txt.value='';
					shortcut.tmp_vou_contraloc.value='';
					shortcut.tmp_vou_memcode.value='';
					shortcut.tmp_vou_status.value='';
					calc_tot_pay();
				}
			}
<?
		}
		if (false) {		//$chk_bool
?>
			if (xcontratype=='C') {
				if (shortcut.<?=$chk_text?>.value!='') {
					var url = '../module/get_contra_value.php?contrano='+shortcut.<?=$chk_text?>.value+'&contratype='+xcontratype+'&is_stockist='+xis_stockist+'&xtxt_key='+xtxt_key+'&xtxt_ref='+xtxt_ref+'&xtx_cheq_batch='+xtx_cheq_batch+'&xloccd='+xloccd;
					//alert(url);
					//shortcut.txt_remarks.value=url;
					new Ajax.Request(url, {
						method: 'get',
						onSuccess: function(transport) {
							var lbtxt = transport.responseText;
						//	alert(lbtxt);
							<?/*OK||0.35|VOU|070903|EXP*/?>
							var arr_split=lbtxt.split("|");
							if (arr_split[0]=='OK')	 {
								shortcut.<?=$chk_input?>.value=arr_split[2];
								shortcut.tmp_batch_che_txt.value=arr_split[4];
								calc_tot_pay();
								shortcut.tmp_che_valid.value='1';
								shortcut.tmp_exp_che_txt.value=arr_split[5];
								shortcut.tmp_che_txt.value=shortcut.<?=$chk_text?>.value;
								shortcut.tmp_che_amount.value=arr_split[2];
								shortcut.tmp_che_contraloc.value=arr_split[6];
								shortcut.tmp_che_memcode.value=arr_split[7];
								shortcut.tmp_che_status.value=arr_split[9];
								if (arr_split[8]=='EXP'){
									//alert('Contra Cheque already expired date !')
									//check_contra2();
									return false;
								}
							}else{
								shortcut.<?=$chk_input?>.value='0';
								shortcut.tmp_batch_che_txt.value='';
								shortcut.tmp_che_valid.value='0';
								shortcut.tmp_exp_che_txt.value='';
								shortcut.tmp_che_amount.value='0';
								shortcut.tmp_che_txt.value='';
								shortcut.tmp_che_contraloc.value='';
								shortcut.tmp_che_memcode.value='';
								shortcut.tmp_che_status.value=arr_split[3];
								if (arr_split[2]=='NOTFOUND') alert(arr_split[1]);
								if (arr_split[2]=='DOUBLE') alert(arr_split[1]);
									//check_contra2();
									return false;
							}
						}
					});
				}else{
					//shortcut.<?=$chk_input?>.value='0';
					shortcut.tmp_batch_che_txt.value='';
					shortcut.tmp_exp_che_txt.value='';
					shortcut.tmp_che_valid.value='0';
					shortcut.tmp_che_amount.value='0';
					shortcut.tmp_che_txt.value='';
					shortcut.tmp_che_contraloc.value='';
					shortcut.tmp_che_memcode.value='';
					shortcut.tmp_che_status.value='';
					calc_tot_pay();
				}
			}
<?
		}
?>
		}
	</script>
<?
}

function frm_payment_pending_report() {
	global $db, $xxrow, $edit_payment,
	$tmp_vou_valid,$tmp_vou_amount,$tmp_vou_txt,
	$tmp_che_valid,$tmp_che_amount,$tmp_che_txt,
	$vou_bool,$chk_bool,$vou_input,$chk_input,$vou_text,$vou_curr,$vou_rate,$chk_text,$chk_curr,$chk_rate,$txt_batchno,
	$xtotpay,$pquerr2, $region, $vpost, $show_ccd, $show_btr, $xreturn,$new_contra_db,
	$tot_item,$txt_key,$txt_scid,$trxno,$allow_return,$xtxt_key,$xloccd,$xtrtype,$tmp_file_ref,$xmodule_name,
	$vtxt_remark,$exc,$cursign,$debug,$cnt,$del;

	//echo "xtxt_key=".$xtxt_key."<br>";
	$amount1 = 0;
	$amount2 = 0;
	$amount3 = 0;
	$amount4 = 0;
	$amount5 = 0;
	if($xxrow[10]!=0)$amount1 = $xxrow[10];
	if($xxrow[11]!=0)$amount2 = $xxrow[11];
	if($xxrow[12]!=0)$amount3 = $xxrow[12];
	if($xxrow[18]!=0)$amount4 = $xxrow[18];
	if($xxrow[19]!=0)$amount5 = $xxrow[19];

	$payType1 = $xxrow[4];
	$payType2 = $xxrow[5];
	$payType3 = $xxrow[6];
	$payType4 = $xxrow[14];
	$payType5 = $xxrow[15];

	//echo "$payType1-$amount1 $payType2=$amount2 $payType3=$amount3<br>";
	if ($payType1!='') {
		$pay_amount[$payType1]=$amount1;
		$pay_note[$payType1]=$xxrow[7];
	}
	if ($payType2!='') {
		$pay_amount[$payType2]=$amount2;
		$pay_note[$payType2]=$xxrow[8];
	}
	if ($payType3!='') {
		$pay_amount[$payType3]=$amount3;
		$pay_note[$payType3]=$xxrow[9];
	}
	if ($payType4!='') {
		$pay_amount[$payType4]=$amount4;
		$pay_note[$payType4]=$xxrow[16];
	}
	if ($payType5!='') {
		$pay_amount[$payType5]=$amount5;
		$pay_note[$payType5]=$xxrow[17];
	}

		$xtotmspayment=
			$pay_amount[$payType1]+$pay_amount[$payType2]+$pay_amount[$payType3]+
			$pay_amount[$payType4]+$pay_amount[$payType5];

		if ($payType1!='') $pay_amount[$payType1]=$amount1;
		if ($payType2!='') $pay_amount[$payType2]=$amount2;
		if ($payType3!='') $pay_amount[$payType3]=$amount3;
		if ($payType4!='') $pay_amount[$payType4]=$amount4;
		if ($payType5!='') $pay_amount[$payType5]=$amount5;

	$xreturn=$xxrow[13];
	if ($xreturn=='') $xreturn=0;

// 	if ($pay_amount['CHK']>$xtotpay || $pay_amount['VOU']>$xtotpay)
// 		$xallow_return=1;
	if ($xtotmspayment>$xtotpay && $pay_amount['CHK']) $xallow_return=1;

	$tmp_vou_valid='0';
	$tmp_vou_amount=0;
	$tmp_vou_txt='';
	$tmp_che_valid='0';
	$tmp_che_amount=0;
	$tmp_che_txt='';

	//echo("-$pquerr1-<br>\n");
	//if ($xstockist_flag)
	if ($edit_payment==1) {
		$pquerr1=
			"select ptypeid,ptypename ".
			"from pay_type where id_cnt='$region'".
			"order by ptypename";
	}else{
		$pquerr1=
			"select ptypeid,ptypename ".
			"from cb_pay_type ".
			"order by ptypename";
	}
	//echo("$pquerr1<br>");
 	//echo("<tr><td>test -->$edit_payment</td></tr>");
	if ($pquerr2!='') $pquerr1=$pquerr2;
	//echo $pquerr1;
	$presult1=pg_exec($db,$pquerr1);

	$total_pay=0;
	//echo $pquerr1."(".pg_numrows($presult1).")<br>";
	echo "<input type='hidden' name='tmp_batch_vou_txt'>\n";
	echo "<input type='hidden' name='tmp_batch_che_txt'>\n";
	echo "<input type='hidden' name='tmp_exp_vou_txt'>\n";
	echo "<input type='hidden' name='tmp_exp_che_txt'>\n";
	echo "<input type='hidden' name='is_stockist' value='$txt_scid'>\n";
	echo "<input type='hidden' name='xtxt_key' value='".(($xtxt_key!='')?$xtxt_key:$txt_key)."'>\n";
	echo "<input type='hidden' name='xtrxno' value='$trxno'>\n";
	echo "<input type='hidden' name='tmp_che_contraloc'>\n";
	echo "<input type='hidden' name='tmp_che_memcode'>\n";
	echo "<input type='hidden' name='tmp_che_status' value=''>\n";
	echo "<input type='hidden' name='tmp_vou_contraloc'>\n";
	echo "<input type='hidden' name='tmp_vou_memcode'>\n";
	echo "<input type='hidden' name='xtrtype' value='$xtrtype'>\n";
	echo "<input type='hidden' name='tmp_vou_status' value=''>\n";
	echo "<input type='hidden' name='xloccd' value='$xloccd'>\n";
/*	if (preg_match('\/product_ordering/sp_order_fltr',$_SERVER["SCRIPT_NAME"])) $tmp_file_ref='brcr';
	if (preg_match('\/product_ordering/cb_order_fltr',$_SERVER["SCRIPT_NAME"])) $tmp_file_ref='brcb';
	if (preg_match('\/product_ordering/view_invoice_so_staf_prnx',$_SERVER["SCRIPT_NAME"])) $tmp_file_ref='brcr';
	if (preg_match('\/product_ordering/view_invoice_so_prnx',$_SERVER["SCRIPT_NAME"])) $tmp_file_ref='brcr';
*/
	echo "<input type='hidden' name='tmp_file_ref' value='".(($tmp_file_ref!='')?$tmp_file_ref:$xmodule_name)."'>";
	for ($ip=0;$ip<pg_num_rows($presult1);$ip++) {
		$prow_ = pg_fetch_row($presult1, $ip);
		$total_pay+=$pay_amount[$prow_[0]];
		//if (type=CSH and not xallow_return) xcash=xreturn;
		$pay_amount[$prow_[0]]+=($prow_[0]=='CSH' && $xallow_return!=1 && $pay_amount['CSH']!=0)?$xreturn:0;
		if ($edit_payment!='1') {
			//echo("$prow_[0] ".$pay_amount[$prow_[0]]."<br><br>");
			if ($pay_amount[$prow_[0]]!=0) {
				echo("<tr align='left'>\n");
				
				/**
				  implement different format for Colombia
				*/
				
				if ($region=='CO') {
				  echo("<td width='70%'>Forma de Pago : ".mxlangtxt($prow_[1])."</td></tr>\n");
				  $_pay_amount[$prow_[0]]=number_format(n_val($pay_amount[$prow_[0]]),2);
				  echo("<tr><td align='left'><div style='float:left'>Valor :</div><div style='float:right'>Pesos ML$</div></td><td align='right'>".$_pay_amount[$prow_[0]]."</td>\n");
				}
				else {
				  echo("<td width='50%'>".mxlang("157")." : ".mxlangtxt($prow_[1])."</td>\n");
				  $_pay_amount[$prow_[0]]=number_format(n_val($pay_amount[$prow_[0]]),2);
				  echo("<td align='right'>".mxlang("152")." : $cursign ".$_pay_amount[$prow_[0]]."</td>\n");
				}
				
				echo("</tr>\n");
				
				if ($prow_[0]=='BDEC' && $xmodule_name=='brinv') {
					echo("<tr align='left'>\n");
					echo("<td colspan='2'>&nbsp;&nbsp;- ".mxlang("1653")." <i>".preg_replace("[,+]",", ",$pay_note[$prow_[0]])."</i></td>\n");
					echo("</tr>\n");
				}
				if ($prow_[0]=='CHK' || $prow_[0]=='CHKCURR') {
					$tmp_che_amount=$pay_amount[$prow_[0]];
					echo("<tr align='left'>\n");
					echo("<td colspan='2'>&nbsp;&nbsp;- ".mxlang("1977")." <i>".preg_replace("[,+]",", ",$pay_note[$prow_[0]])."</i></td>\n");
					echo("</tr>\n");
				}
				if ($prow_[0]=='VOU' || $prow_[0]=='VOUCURR') {
					$tmp_vou_amount=$pay_amount[$prow_[0]];
					echo("<tr align='left'>\n");
					echo("<td colspan='2'>&nbsp;&nbsp;- ".mxlang("1978")." <i>".preg_replace("[,+]",", ",$pay_note[$prow_[0]])."</i></td>\n");
					echo("</tr>\n");
				}
				if ($prow_[0]=='CKK') {
					echo("<tr align='left'>\n");
					echo("<td colspan='2'>&nbsp;&nbsp;- ".mxlang("1608")." <i>".preg_replace("[,+]",", ",$pay_note[$prow_[0]])."</i></td>\n");
					echo("</tr>\n");
				}
				if ($prow_[0]=='CCD' && $show_ccd=='1') {
					echo("<tr align='left'>\n");
					$txt_ccd=split('#',$pay_note[$prow_[0]]);
					echo("<td colspan='2'>");
					echo("&nbsp;&nbsp;- ".mxlang("1979").": <i>XXXX-XXXX-XXXX-".substr($txt_ccd[0],-4)."</i><br>\n");
					echo("&nbsp;&nbsp;- ".mxlang("1980").": <i>$txt_ccd[2]</i><br>\n");
					echo("</td>\n");
					echo("</tr>\n");
				}
				if ($prow_[0]=='BTR' && $show_btr=='1') {
					echo("<tr align='left'>\n");
					$txt_btr=split('#',$pay_note[$prow_[0]]);
					echo("<td colspan='2'>");
					echo("&nbsp;&nbsp;- ".ucwords(strtolower(mxlang("388"))).": <i>$txt_btr[0]</i><br>\n");
					echo("&nbsp;&nbsp;- ".ucwords(strtolower(mxlang("657"))).": <i>$txt_btr[1]</i><br>\n");
					echo("</td>\n");
					echo("</tr>\n");
				}
			}

		}else{
			echo("<tr align='left'>\n");
			echo("<td colspan='2'><b>".ucwords(strtolower(mxlang("157")))." : $prow_[1]</b></td>\n");
			echo("</tr>\n");
			if ($pay_amount[$prow_[0]]=='') $pay_amount[$prow_[0]]=0;
			//if ($prow_[0]=='VOU' || $prow_[0]=='VOUCURR') {
			if ($prow_[0]=='VOU') {
				$vou_bool = true;
				if ($vou_input==''){
				 $vou_input="cbo_payinput_$ip";
					$vou_text="cbo_paynumber_$ip";
				}
				echo("<tr align='left'>\n");
				echo("<td width='40%'>&nbsp;&nbsp;".ucwords(strtolower(mxlang("1978")))." </td>\n");
				echo("<td>");
				//if ($pay_note[$prow_[0]]!='') {
				//	echo("<input type='hidden' name='vou_text' value='".$pay_note[$prow_[0]]."'/>".
				//		"&nbsp;<a href='#' onclick=\"viewvcr1('".$pay_note[$prow_[0]]."');\">".$pay_note[$prow_[0]]."</a><br>\n");
				//}
				//else{
					echo("<input type='text' size='15' name='vou_text' value='".$pay_note[$prow_[0]]."' onChange='vou_inedit()' ");
					if ($new_contra_db) echo "onBlur=\"fget_contra_value('V')\"";
					echo ("/>\n");
					echo("<input type='hidden' size='15' name='vou_curr'  onChange='vou_inedit()'/>\n");
					echo("<input type='hidden' size='15' name='vou_rate' onChange='vou_inedit()'/>\n");
				if ($new_contra_db==1) {
					echo("<input type='button' name='btn_go' value='".ucfirst(strtolower(mxlang("1617")))."' onclick=''>");
				}else
				{
					echo("<input type='button' name='btn_go' value='".ucfirst(strtolower(mxlang("1617")))."' onclick='chkvcr1();'>");
				}
				//}
				echo("<input type='hidden' name='paytype_".strtolower($ip)."' value='$xpay_index'/>\n");
				echo("</td>\n");
				echo("</tr>\n");
				if ($pay_note[$prow_[0]]!='') {
					$tmp_vou_valid='1';
				}
				$tmp_vou_amount=$pay_amount[$prow_[0]];
				$tmp_vou_txt=$pay_note[$prow_[0]];
			//}elseif ($prow_[0]=='CHK' || $prow_[0]=='CHKCURR') {
			}elseif ($prow_[0]=='CHK') {
				$chk_bool = true;
				if ($chk_input==''){
					 $chk_input="cbo_payinput_$ip";
					$chk_text="cbo_paynumber_$ip";
				}
// 				$chk_curr="cbo_paycurr_$ip";
// 				$chk_rate="cbo_payrate_$ip";

				echo("<tr align='left'>\n");
				echo("<td width='40%'>&nbsp;&nbsp;".ucwords(strtolower(mxlang("1977")))." </td>\n");
				echo("<td>\n");
				//if ($pay_note[$prow_[0]]!='') {
				//	echo("<input type='hidden' name='chk_text_old' value='".$pay_note[$prow_[0]]."'/>");
				//	echo("&nbsp;<a href='#' onclick=\"viewvcr2('".$pay_note[$prow_[0]]."');\">".$pay_note[$prow_[0]]."</a><br>\n");
				//}
				echo("<input type='text' size='15' name='chk_text' value='".$pay_note[$prow_[0]]."' onChange='chk_inedit()' ");
				if ($new_contra_db) echo "onBlur=\"fget_contra_value('C')\"";
				echo ("/>\n");
				echo("<input type='hidden' size='15' name='chk_curr'  onChange='chk_inedit()'/>\n");
				echo("<input type='hidden' size='15' name='chk_rate' onChange='chk_inedit()'/>\n");
				if ($new_contra_db==1) {
					echo("<input type='button' name='btn_go' value='".ucfirst(strtolower(mxlang("1617")))."' onclick=''>");
				}else
				{
					echo("<input type='button' name='btn_go' value='".ucfirst(strtolower(mxlang("1617")))."' onclick='chkvcr2();'>");
				}
				echo("<input type='hidden' name='paytype_".strtolower($prow_[0])."' value='$ip'/>\n");
				echo("</td>\n");
				echo("</tr>\n");
				if ($pay_note[$prow_[0]]!='') {
					$tmp_che_valid='1';
				}
				$tmp_che_txt=$pay_note[$prow_[0]];
				$tmp_che_amount=$pay_amount[$prow_[0]];
			}elseif ($prow_[0]=='CKK') {
				$ckk_bool = true;
				if ($ckk_input=='') $ckk_input="cbo_payinput_$ip";
				echo("<tr align='left'>\n");
				echo("<td width='40%'>&nbsp;&nbsp;".mxlang("1608")." </td>\n");
				echo("<td>\n");
				echo("<input type='text' size='15' name='ckk_text' value='".$pay_note[$prow_[0]]."' onChange='chk_inedit()'/>\n");
				echo("<input type='hidden' name='paytype_".strtolower($prow_[0])."' value='$ip'/>\n");
				echo("</td>\n");
				echo("</tr>\n");
			}
			//$xreadonly = ($prow_[0]=='CHK' || $prow_[0]=='VOU')?'readonly':'';
			echo("<tr align='left' >\n");
			echo("<td width='40%'>&nbsp;&nbsp;".mxlang("152")." : </td>");
			//if ($pay_note[$prow_[0]]!='') {
			//	echo("<td><input type='hidden' name='cbo_payinput_$ip' value='".$pay_amount[$prow_[0]]."'>");
			//	echo("&nbsp;".$pay_amount[$prow_[0]]."\n");
			//}else{
				echo("<td><input type='text' name='cbo_payinput_$ip' size='15' value='".$pay_amount[$prow_[0]]."' ");
				echo("onChange='calc_tot_pay();' $xreadonly>");
			//}
			$pay_default[$prow_[0]]=($pay_amount[$prow_[0]]==0)?0:1;
			echo("<input type='hidden' name='pay_default_$ip' value='".$pay_default[$prow_[0]]."'>");
			echo("<input type='hidden' name='cbo_paytype_$ip' value='$prow_[0]'>");

			echo("</td>\n</tr>\n");
		}
	}
		 /*
if($pay_amount['VOUCURR']!=0){
				echo("<tr align='left'>\n");
				echo("<td width='40%'>Payment Method : $prow_[1]</td>\n");
				echo("<td align='right'>Amount : ".CURRENCY.number_format($pay_amount['VOUCURR'],2)."</td>\n");
				echo("</tr>\n");
				echo("<tr align='left'>\n");
				echo("<td colspan='2'>&nbsp;&nbsp;- Contra Voucher No. <i>".str_replace(",",", ",$pay_note['VOUCURR'])."</i></td>\n");
				echo("</tr>\n");
			}
			else if($pay_amount['CHKCURR']!=0){
				echo("<tr align='left'>\n");
				echo("<td width='40%'>Payment Method : $prow_[1]</td>\n");
				echo("<td align='right'>Amount : ".CURRENCY.number_format($pay_amount['CHKCURR'],2)."</td>\n");
				echo("</tr>\n");
				echo("<tr align='left'>\n");
				echo("<td colspan='2'>&nbsp;&nbsp;- Contra Cheque No. <i>".str_replace(",",", ",$pay_note['CHKCURR'])."</i></td>\n");
				echo("</tr>\n");
			}
			$total_pay1=$pay_amount['VOUCURR'];
			$total_pay2=$pay_amount['CHKCURR'];*/
	pg_free_result($presult1);
	if($exc!=2){
?>

	      <tr align="left">
					<td colspan="2" bgcolor="#000000" heigth="1"></td>
				</tr>
<?}else echo "</hr>" ?>
				<tr bgcolor="#FFFFFF">
				  
					
					
				  
<?
 	if ($edit_payment==1 && $vpost==3) {
		$xtotal=$xreturn+$total_pay+$total_pay1+$total_pay2;
 		$xreturn=$xreturn+$total_pay-$xtotpay;
 	}else{
 		$xreturn=$xxrow[13];
		$xtotal=($xallow_return!=1 && ($pay_amount['CSH']!=0))?($total_pay+$total_pay1+$total_pay2+$xreturn):($total_pay+$total_pay1+$total_pay2);
 	}

	$xwrite_total = $xtotal;
	$xwrite_return= $xreturn;
	if($debug==1) echo "tot_item : $tot_item<br> allow_return : $allow_return<br>";
	if ($tot_item=='') $tot_item=0;
	if($debug==1) echo "$tot_item && ($tmp_che_amount || $tmp_vou_amount || $payType1) && $allow_return <br>";
	if ($tot_item==0 && ($tmp_che_amount!=0 || $tmp_vou_amount!=0 || $payType1=="CSH") && $allow_return=="1") {
		$xwrite_total = 0;
		$xwrite_return= $xtotal;
	}
	// added by weiyee on 24-04-2009
		$_xwrite_total=number_format(n_val($xwrite_total),2);
?>
					  
					<td align='<?=($region=="CO")?"left":"right"?>' width='40%'>
					  <input type='hidden' name='txt_count' value='<?=$ip?>'>
					  <input type='hidden' name='amount_text' value='0'>
					  <b><?=($region=="CO")?"<div style='float:left'>Valor a pagar</div><div style='float:right'>Pesos ML$</div>":ucwords(strtolower(mxlang("164")))?> :</b>
					</td>
					<td align='right'><b><div id='total_pay'><?=($region=="CO")?"":$cursign." "?>
					<?=$_xwrite_total?></div></b></td>
				</tr>
				<tr bgcolor="#FFFFFF">
					<td align='<?=($region=="CO")?"left":"right"?>' width='40%'><b><?=($region=="CO")?"<div style='float:left'>Devolución</div><div style='float:right'>Pesos ML$</div>":ucwords(strtolower(mxlang("165")))?> :</b></td>
					<td align='right'><b><div id='return_pay'><?=($region=="CO")?"":$cursign." "?><?=number_format($xwrite_return,2)?></div></b></td>
					</tr>
					<tr  bgcolor="#FFFFFF">
						<td align='right' colspan='2'>&nbsp;
<?										//echo("PHP_SELF=".$_SERVER["SCRIPT_NAME"]);
				if($edit_payment=='1'){
					//change back based 080219-003 at Feedback080219.odt
					if (
							(preg_match('\/view_invoice_sccb_prnx\.php',$_SERVER["SCRIPT_NAME"]) /*&& $xtrtype==1*/)  || (
							preg_match('\/view_invoice_so_inv_prnx\.php',$_SERVER["SCRIPT_NAME"]) && check_level_ex($xloccd)=='MS')
						&& ($chk_bool || $vou_bool)
						) {
					?>
							<input type='hidden' name='allow_return' value='0'>
					<?
						}else{
							echo ("<input type='checkbox' name='allow_return' value='1'>&nbsp;\n");
							 echo ucfirst(strtolower(mxlang("2017")));
						}
				}?>

					</td>
					<input type='hidden' name='count_pay' value='<?=$ip?>'>
<!-- 					<input name="return_total" type="hidden"> -->
					<input type='hidden' name='xalert_997' value='<?=mxlang('997')?>'>
					<input type='hidden' name='xalert_996' value='<?=mxlang('996')?>'>
					<input type='hidden' name='xalert_809' value='<?=mxlang('809')?>'>
					<input type='hidden' name='xalert_3067' value='<?=mxlang('3067')?>'>
				</tr>
<?
				if ($edit_payment==1 &&
						($xmodule_name=='brcb' || $xmodule_name=='brinv' || $xmodule_name=='brcr' ||
						($xmodule_name=='whinv') || ($xmodule_name=='whcr'))

				) {
?>
				<tr align="left">
					<td colspan="2"><i><?=mxlang("172");?> : </i><br>
&nbsp;<TEXTAREA NAME="vtxt_remark" ROWS=3 COLS=50><?=$vtxt_remark?></TEXTAREA>
					</td>
				</tr>
<?
				}else{
					if ($vtxt_remark!='') {
?>
				<tr align="left">
					<td colspan="2"><i><?=mxlang("172");?> : <?=$vtxt_remark?></i></td>
				</tr>
<?
					}
				}
}

function js_payment_contra($xsc='01-dummy') {
	global $vou_text,$vou_input,$vou_rate,$vou_curr,$chk_text,$chk_curr,$chk_rate,$chk_input,$vou_bool,$chk_bool,
		$trxtype,$trxno,$new_contra_db, $edit_payment, $PHP_SELF;

	if ($xsc!='') {
		//if ($priv==2 || $priv==0)
		$xchk = check_stockist_ex($xsc);
		if ($xchk==0) { //main stockist
			$st_url="ms=$xsc&";
		}elseif ($xchk==1) {
			$st_url="sc=$xsc&";
		}
	}

?>
	function vou_inedit() {
		var shortcut = 	document.frm_invcnt_fltr;
		shortcut.tmp_vou_valid.value=0;
	}

	function chk_inedit() {
		var shortcut = 	document.frm_invcnt_fltr;
		shortcut.tmp_che_valid.value=0;
	}

	function chkvcr1() {
		var shortcut = 	document.frm_invcnt_fltr;
		shortcut.tmp_vou_valid.value=0;
		if (shortcut.vou_text.value.length>0) {
			var vcnum = shortcut.vou_text.value;
			var dimensi="height=200,width=200,left=50,top=5,resizable=yes,scrollbars=yes";
			var url="../product_ordering/chkvcr.php?vc="+vcnum+"&<?=$st_url?>thesubmit=off&vtype=0&vou_input=<?=$vou_input?>&confirmvalid=1&trxtype=<?=$trxtype?>&trx=<?=$trxno?>";
			new_window = window.open(url,"voucher_vou",dimensi);
		}else{
			//alert("<?=mxlang("996")?> ");
			alert(shortcut.xalert_996.value);
			shortcut.vou_text.focus();
			return false;
		}
	}

	function viewvcr1(xno) {
		if (xno.length>0) {
			var vcnum = xno;
			var dimensi="height=200,width=200,left=50,top=5,resizable=yes,scrollbars=yes";
			var url="../product_ordering/chkvcr.php?vc="+vcnum+"&viewvcr=1&vtype=0&vou_input=<?=$chk_input?>&confirmvalid=0";
			new_window = window.open(url,"voucher_vou",dimensi);
		}
	}



	function chkvcr2() {
		var shortcut = 	document.frm_invcnt_fltr;
		shortcut.tmp_che_valid.value=0;
		if (shortcut.chk_text.value.length>0) {
			var vcnum = shortcut.chk_text.value;
			var dimensi="height=200,width=200,left=50,top=5,resizable=yes,scrollbars=yes";
			var url="../product_ordering/chkvcr.php?vc="+vcnum+"&<?=$st_url?>thesubmit=off&vtype=1&vou_input=<?=$chk_input?>&confirmvalid=1";
			new_window = window.open(url,"voucher_chk",dimensi);
		}else{
			//alert("<?=mxlang("997")?> ");
			alert(shortcut.xalert_997.value);
			shortcut.chk_text.focus();
			return false;
		}
	}

	function check_contra2() {
		var shortcut = 	document.frm_invcnt_fltr;
		shortcut.tmp_che_valid.value=0;
		var vcnum = shortcut.chk_text.value;
		var xis_stockist = shortcut.is_stockist.value;
		var xtxt_key = shortcut.xtxt_key.value;
		var xtrxno = shortcut.xtrxno.value;
		var xtrtype = shortcut.xtrtype.value;
		var xtxt_ref = shortcut.tmp_file_ref.value;
		var xloccd = shortcut.xloccd.value;
			var dimensi="height=450,width=900,left=5,top=5,resizable=yes,scrollbars=yes";
			var url="../compensation/new_check_contra.php?vc="+vcnum+"&<?=$st_url?>thesubmit=off&vtype=1&vou_input=<?=$chk_input?>&input_text=chk_text&chk_curr=chk_curr&chk_rate=chk_rate&confirmvalid=1&xref=<?=urlencode($PHP_SELF)?>&is_stockist="+xis_stockist+"&xtxt_key="+xtxt_key+"&xtrxno="+xtrxno+"&xtxt_ref="+xtxt_ref+"&xtrtype="+xtrtype+"&xloccd="+xloccd;
			new_window = window.open(url,"voucher_chk",dimensi);
	}

		function voucher_contra2() {
			var shortcut = 	document.frm_invcnt_fltr;
			shortcut.tmp_vou_valid.value=0;
			var vcnum = shortcut.vou_text.value;
			var xis_stockist = shortcut.is_stockist.value;
			var xtxt_key = shortcut.xtxt_key.value;
			var xtrxno = shortcut.xtrxno.value;
			var xtrtype = shortcut.xtrtype.value;
			var xtxt_ref = shortcut.tmp_file_ref.value;
			var xloccd = shortcut.xloccd.value;
			var dimensi="height=450,width=900,left=5,top=5,resizable=yes,scrollbars=yes";
			var xurl="../compensation/new_vcr_contra.php";
			xurl+="?vc="+vcnum+"&<?=$st_url?>thesubmit=off&vtype=0&vou_input=<?=$vou_input?>";
			xurl+="&input_text=vou_text&vou_curr=vou_curr&vou_rate=vou_rate&confirmvalid=1";
			xurl+="&is_stockist="+xis_stockist+"&xtxt_key="+xtxt_key+"&xtrxno="+xtrxno;
			xurl+="&xref=<?=urlencode($PHP_SELF)?>"+"&xtxt_ref="+xtxt_ref+"&xtrtype="+xtrtype+"&xloccd="+xloccd;
			//alert('test '+' '+xurl);
			new_window = window.open(xurl,"voucher_chk",dimensi);
		}

	function viewvcr2(xno) {
		if (xno.length>0) {
			var vcnum = xno;
			var dimensi="height=200,width=200,left=50,top=5,resizable=yes,scrollbars=yes";
			var url="../product_ordering/chkvcr.php?vc="+vcnum+"&viewvcr=1&vtype=1&vou_input=<?=$chk_input?>&confirmvalid=0";
			new_window = window.open(url,"voucher_chk",dimensi);
		}
	}


	function chk_count_payment() {
		var shortcut = 	document.frm_invcnt_fltr;
		len1 = shortcut.txt_count.value;
		var xresult=true;
		var j=0;
		for (i = 0; i < len1; i++)  {
			if (isNaN(shortcut.elements['cbo_payinput_'+i].value)) {
				shortcut.elements['cbo_payinput_'+i].value=0;
			}
			if (parseFloat(shortcut.elements['cbo_payinput_'+i].value)!=0) {
				j++;
			}
		}
		if (j>5) xresult=false;
		return xresult;
	}

	function fallow_return() {
		var shortcut = 	document.frm_invcnt_fltr;
		var pay_csh=0;
		var pay_chk='-';
		var pay_vou='-';
		var pay_exp='-';
		var return_pay = shortcut.return_total.value;
		len1 = shortcut.count_pay.value;

		for (i = 0; i < len1; i++)  {
			if (shortcut.elements['cbo_paytype_'+i].value=='CSH')
				pay_csh=shortcut.elements['cbo_payinput_'+i].value;
			if (shortcut.elements['cbo_paytype_'+i].value=='CHK')
				pay_chk=shortcut.elements['cbo_payinput_'+i].value;
			if (shortcut.elements['cbo_paytype_'+i].value=='VOU')
				pay_vou=shortcut.elements['cbo_payinput_'+i].value;
			if (shortcut.elements['cbo_paytype_'+i].value=='CEX')
				pay_exp=shortcut.elements['cbo_payinput_'+i].value;
		}


		if (return_pay>0 && pay_csh==0 && pay_chk==0 && pay_vou==0) {
			alert(shortcut.xalert_808.value);
			return false;
		}
		if (shortcut.allow_return.checked==false) {
			if (parseFloat(return_pay)>0) {
				if (parseFloat(pay_csh)==0 &&
					((parseFloat(pay_chk)>0 && pay_chk!='-') || (parseFloat(pay_vou)>0 && pay_vou!='-'))
				) {
					alert("<?=mxlang("809")?>");
					return false;
				}
			}
		}

		if ( pay_chk>0 && shortcut.chk_text.value=='') {
			alert(shortcut.xalert_997.value);
			shortcut.chk_text.focus();
			return false;
		}
		if ( pay_vou>0 && shortcut.vou_text.value=='') {
			shortcut.vou_text.focus();
			alert(shortcut.xalert_996.value);
			return false;
		}
		if ( shortcut.return_total.value>0 && shortcut.allow_return.value=='0') {
			shortcut.chk_text.focus();
			alert(shortcut.xalert_809.value);
			return false;
		}
		if ( shortcut.return_total.value>0 && shortcut.allow_return.checked==false) {
			shortcut.chk_text.focus();
			alert(shortcut.xalert_809.value);
			return false;
		}
		return true;
	}

	function valid_contra_voucher() {
		var shortcut = 	document.frm_invcnt_fltr;
		var xvalid=true;
		var exp_passed_contra=false;
		var xtxt_ref=shortcut.tmp_file_ref.value;
		var xloccd = shortcut.xloccd.value;
	<?
		if ($vou_bool) {
/*			echo("if (shortcut.$vou_text.value=='' && shortcut.$vou_input.value!=0) { \n");
			echo("	alert('Please enter voucher number !');\n");
			echo("	shortcut.$vou_text.focus();\n");
			echo("	xvalid=false;\n");
			echo("}\n\n");*/

			if ($new_contra_db==1) {
				//echo "if (/EXP/.test(shortcut.tmp_exp_vou_txt.value)) {\n";
				echo "if (shortcut.tmp_exp_vou_txt.value=='EXP') {\n";
				//echo "	alert(shortcut.xalert_2938.value);\n";
				echo "	alert('Contra Voucher already expired date');\n";
				echo("	shortcut.tmp_vou_valid.value='0';\n");
				echo("	xvalid=false;exp_passed_contra=true\n");
				echo "}else{\n";
				//echo("	xvalid=true;\n");
/*					echo "if (shortcut.$vou_text.value!='' && shortcut.tmp_memcode.value!=shortcut.xtxt_key.value && shortcut.tmp_contraloc.value!=shortcut.xtxt_key.value) {\n";
					echo "	alert('Member code in voucher not same with member code in transaction !');\n";
					echo "	shortcut.tmp_vou_valid.value='0';\n";
					echo "	xvalid=false;exp_passed_contra=true\n";
					echo "}else{\n";
					echo "	xvalid=true;\n";
					echo "}\n";*/
				echo " var arr_contraloc=shortcut.tmp_vou_contraloc.value.split('+');\n";
				echo " var arr_memcode=shortcut.tmp_vou_memcode.value.split('+');\n";
				echo " var arr_status=shortcut.tmp_vou_status.value.split('+');\n";
				echo " if (arr_memcode.length>0 && arr_contraloc==0){ \n";
				echo "  xvalid=true;\n";
				echo "  for (m=0;m<arr_memcode.length;m++) {\n";
				echo "   if ( arr_status[m]=='C') {\n";
				echo "   	if ((shortcut.vou_text.value!='' && arr_contraloc[m]!=shortcut.xtxt_key.value)) {\n";
				echo "	  	xvalid=false;\n";
				echo "  	 }\n";
				echo "   }\n";
				echo "   if ( arr_status[m]=='N') {\n";
					echo "   if ( xtxt_ref=='brinv' || xtxt_ref=='whinv' || (xtxt_ref=='brcb' && xloccd=='000000014')) {}\n";
					echo "   else{ \n";
						echo "   	if ((shortcut.vou_text.value!='' && arr_memcode[m]!=shortcut.xtxt_key.value)) {\n";
						echo "	  	xvalid=false;\n";
						echo "  	 }\n";
					echo "  	 }\n";
				echo "   }\n";
				echo "  }\n"; //end for

				echo "  if (xvalid==false) { ";
				echo "   exp_passed_contra=true;\n";
				echo "	 alert('Member code in voucher not same with member code in transaction !');\n";
				echo "  }\n";
				echo " }else if (arr_memcode.length>0 && arr_contraloc==0){ \n";
				echo "  xvalid=true;\n";
				echo "  for (m=0;m<arr_memcode.length;m++) {\n";
				echo "   if ( arr_status[m]=='C') {\n";
				echo "   	if ((shortcut.vou_text.value!='' && arr_contraloc[m]!=shortcut.xtxt_key.value)) {\n";
				echo "	  	xvalid=false;\n";
				echo "  	 }\n";
				echo "   }\n";
				echo "   if ( arr_status[m]=='N') {\n";
					echo "   if ( xtxt_ref=='brinv' || xtxt_ref=='whinv' || (xtxt_ref=='brcb' && xloccd=='000000014')) {}\n";
					echo "   else{ \n";
						echo "   	if ((shortcut.vou_text.value!='' && arr_memcode[m]!=shortcut.xtxt_key.value)) {\n";
						echo "	  	xvalid=false;\n";
						echo "  	 }\n";
					echo "  	 }\n";
				echo "   }\n";
				echo "  }\n"; //end for

				echo "  if (xvalid==false) { ";
				echo "   exp_passed_contra=true;\n";
				echo "	 alert('Member code in voucher not same with member code in transaction !');\n";
				echo "  }\n";
				echo " }\n";
				echo "}\n";

			}
			//if ($new_contra_db!=1) {
			/*	echo("if (shortcut.vou_text.value!='' && exp_passed_contra==false) { \n");
				echo("	if (shortcut.tmp_vou_valid.value==1) { \n");
				echo("		if (shortcut.tmp_vou_txt.value!=shortcut.vou_text.value || \n");
				echo(" 			parseFloat(shortcut.$vou_input.value)!=parseFloat(shortcut.tmp_vou_amount.value)) \n");
				echo("		{	\n");
				echo(" 			alert(shortcut.xalert_1975.value); \n");
				echo("			xvalid=false;exp_passed_contra=true;\n");
				echo("		}\n");
				echo("	}else{\n");
				echo(" 		alert(shortcut.xalert_1975.value); \n");
				echo("		xvalid=false;\n");
				echo("	}\n");
				echo("}\n");
				echo("\n");*/
			//}
		}
		echo("return xvalid;\n\n");
	?>
	}
	function valid_contra_cheque() {
		var shortcut = 	document.frm_invcnt_fltr;
		var xvalid=true;
		var exp_passed_contra=false;
		var xtxt_ref = shortcut.tmp_file_ref.value;
		var xloccd = shortcut.xloccd.value;
<?
		if ($chk_bool) {
/*			echo("if (shortcut.$chk_text.value=='' && shortcut.$chk_input.value!=0) { \n");
			echo("	alert('Please enter cheque number !');\n");
			echo("	shortcut.$chk_text.focus();\n");
			echo("	return false;\n");
			echo("}\n\n");*/

			if ($new_contra_db==1) {
				echo "if (/EXP/.test(shortcut.tmp_exp_che_txt.value)) {\n";
				echo "	alert('Contra Cheque already expired date !')\n";
				echo("	shortcut.tmp_che_valid.value='0';\n");
				echo("	xvalid=false;exp_passed_contra=true;\n");
				echo "}else{\n";
				//echo("	xvalid=true;\n");
/*
					echo "if (shortcut.$chk_text.value!='' && shortcut.tmp_memcode.value!=shortcut.xtxt_key.value && shortcut.tmp_contraloc.value!=shortcut.xtxt_key.value) {\n";
					echo "	alert('Member code in cheque not same with member code in transaction !');\n";
					echo "	shortcut.tmp_che_valid.value='0';\n";
					echo "	xvalid=false;exp_passed_contra=true\n";
					echo "}else{\n";
					echo "	xvalid=true;\n";
					echo "}\n";*/
				echo " var arr_contraloc=shortcut.tmp_che_contraloc.value.split('+');\n";
				echo " var arr_memcode=shortcut.tmp_che_memcode.value.split('+');\n";
				echo " var arr_status=shortcut.tmp_che_status.value.split('+');\n";
				echo " if (arr_memcode.length>0 && arr_contraloc==0){ \n";
				echo "  xvalid=true;\n";
				echo "  for (m=0;m<arr_memcode.length;m++) {\n";
				echo "   if ( arr_status[m]=='C') {\n";
				echo "   	if ((shortcut.chk_text.value!='' && arr_contraloc[m]!=shortcut.xtxt_key.value)) {\n";
				echo "	  	xvalid=false;\n";
				echo "  	 }\n";
				echo "   }\n";
				echo "   if ( arr_status[m]=='N') {\n";
					echo "   if ( xtxt_ref=='brinv' || xtxt_ref=='whinv' || (xtxt_ref=='brcb' && xloccd=='000000014')) {}\n";
					echo "   else{ \n";
						echo "   	if ((shortcut.chk_text.value!='' && arr_memcode[m]!=shortcut.xtxt_key.value)) {\n";
						echo "	  	xvalid=false;\n";
						echo "  	 }\n";
					echo "  	 }\n";
				echo "   }\n";
				echo "  }\n"; //end for
				echo "  if (xvalid==false) { ";
				echo "   exp_passed_contra=true;\n";
				echo "	 alert('Member code in cheque not same with member code in transaction !');\n";
				echo "  }\n";
				echo " }else if (arr_memcode.length>0 && arr_contraloc.length>0){ \n";
				echo "  xvalid=true;\n";
				echo "  for (m=0;m<arr_memcode.length;m++) {\n";
				echo "   if ( arr_status[m]=='C') {\n";
				echo "   	if ((shortcut.chk_text.value!='' && arr_contraloc[m]!=shortcut.xtxt_key.value)) {\n";
				echo "	  	xvalid=false;\n";
				echo "  	 }\n";
				echo "   }\n";
				echo "   if ( arr_status[m]=='N') {\n";
					echo "   if ( xtxt_ref=='brinv' || xtxt_ref=='whinv' || (xtxt_ref=='brcb' && xloccd=='000000014')) {}\n";
					echo "   else{ \n";
						echo "   	if ((shortcut.chk_text.value!='' && arr_memcode[m]!=shortcut.xtxt_key.value)) {\n";
						echo "	  	xvalid=false;\n";
						echo "  	 }\n";
					echo "  	 }\n";
				echo "   }\n";
				echo "  }\n"; //end for

				echo "  if (xvalid==false) { ";
				echo "   exp_passed_contra=true;\n";
				echo "	 alert('Member code in cheque not same with member code in transaction !');\n";
				echo "  }\n";
				echo " }\n";
				echo "}\n";
			}
			//if ($new_contra_db!=1) {
		/*		echo("if (shortcut.chk_text.value!='' && exp_passed_contra==false) {\n");
				echo("	if (shortcut.tmp_che_valid.value==1) { \n");
				echo("		if (shortcut.tmp_che_txt.value!=shortcut.chk_text.value || \n");
				echo(" 			parseFloat(shortcut.$chk_input.value)!=parseFloat(shortcut.tmp_che_amount.value)) \n");
				echo("		{	\n");
				echo(" 			alert(shortcut.xalert_1976.value); \n");
				echo("			xvalid=false;\n");
				echo("		}\n");
				echo("	}else{\n");
				echo(" 		alert(shortcut.xalert_1976.value); \n");
				echo("		xvalid=false;\n");
				echo("	}\n");
				echo("}\n");*/
			//}
		}
		echo("return xvalid;\n\n");
?>
	}
	function go_post_payment() {
		var shortcut = 	document.frm_invcnt_fltr;
		var xtxt_ref = shortcut.tmp_file_ref.value;

	/*	if(xtxt_ref=='sccb'){
				if (shortcut.return_total.value>0) {

								alert(shortcut.xalert_3067.value);
								shortcut.cbo_payinput_0.focus();
								return false;
						}
		}*/
		if (!valid_contra_voucher()) return false;
		if (!valid_contra_cheque()) return false;
		if (chk_count_payment()==false) {
			//alert("<?=mxlang("799")?>")
			alert(shortcut.xalert_799.value);
			return false;
		}

		if (shortcut.return_total.value>=0) {
		}else{
			//alert("<?=mxlang("173");?>");
			alert(shortcut.xalert_173.value);
			return false;
		}
		if (!fallow_return()) {
							return false;
		}
		valid=confirm("<?=ucfirst(strtolower(mxlang("1611")))?> ");
		if (valid) {
			document.frm_invcnt_fltr.confirm.value= "2";
			document.frm_invcnt_fltr.submit();
		}
	}
<?
	if ($edit_payment==1) {
?>
		function fget_contra_value(xcontratype) {
			//alert(xcontratype);
			var shortcut = document.frm_invcnt_fltr;
			var xis_stockist = shortcut.is_stockist.value;
			var xtxt_key = shortcut.xtxt_key.value;
			var xtrxno = shortcut.xtrxno.value;
			var xtxt_ref = shortcut.tmp_file_ref.value;
			var xtx_cheq_batch = shortcut.tmp_batch_che_txt.value;
			var xtx_vou_batch = shortcut.tmp_batch_vou_txt.value;
			var xloccd=shortcut.xloccd.value;
<?
		if ($vou_bool) {
?>
			if (xcontratype=='V') {
				if (shortcut.vou_text.value!='') {
					var url = '../module/get_contra_value.php?contrano='+shortcut.vou_text.value+'&contratype='+xcontratype+'&is_stockist='+xis_stockist+'&xtxt_key='+xtxt_key+'&xtrxno='+xtrxno+'&xref=<?=$PHP_SELF?>&xtxt_ref='+xtxt_ref+'&xtx_vou_batch='+xtx_vou_batch+'&xloccd='+xloccd;
					//alert(url);
					new Ajax.Request(url, {
						method: 'get',
						onSuccess: function(transport) {
							var lbtxt = transport.responseText;
							<?/*OK||0.35|VOU|070903*/?>
							var arr_split=lbtxt.split("|");
							if (arr_split[0]=='OK')	 {
								shortcut.<?=$vou_input?>.value=arr_split[2];
								shortcut.tmp_batch_vou_txt.value=arr_split[4];
								calc_tot_pay();
								shortcut.tmp_vou_valid.value='1';
								shortcut.tmp_vou_txt.value=shortcut.vou_text.value;
								shortcut.tmp_vou_amount.value=arr_split[2];
								shortcut.tmp_exp_vou_txt.value=arr_split[5];
								shortcut.tmp_vou_contraloc.value=arr_split[6];
								shortcut.tmp_vou_memcode.value=arr_split[7];
								shortcut.tmp_vou_status.value=arr_split[9];
								if (arr_split[8]=='EXP'){
								//	alert('Contra Voucher already expired date !')
									//voucher_contra2();
									return false;
								}
							}else{
								shortcut.<?=$vou_input?>.value='0';
								shortcut.tmp_batch_vou_txt.value='';
								shortcut.tmp_vou_valid.value='0';
								shortcut.tmp_vou_amount.value='0';
								shortcut.tmp_exp_vou_txt.value='';
								shortcut.tmp_vou_txt.value='';
								shortcut.tmp_vou_contraloc.value='';
								shortcut.tmp_vou_memcode.value='';
								shortcut.tmp_vou_status.value=arr_split[3];
								calc_tot_pay();
								if (arr_split[2]=='NOTFOUND') alert(arr_split[1]);
								if (arr_split[2]=='DOUBLE') alert(arr_split[1]);
								//voucher_contra2();
								return false;
							}

						}
					});
				}else{
					//shortcut.<?=$vou_input?>.value='0';
					shortcut.tmp_batch_vou_txt.value='';
					shortcut.tmp_vou_valid.value='0';
					shortcut.tmp_exp_vou_txt.value='';
					shortcut.tmp_vou_amount.value='0';
					shortcut.tmp_vou_txt.value='';
					shortcut.tmp_vou_status.value='';
					calc_tot_pay();
				}
			}
<?
		}
		if ($chk_bool) {
?>
			if (xcontratype=='C') {
				if (shortcut.chk_text.value!='') {
					var url = '../module/get_contra_value.php?contrano='+shortcut.chk_text.value+'&contratype='+xcontratype+'&is_stockist='+xis_stockist+'&xtxt_key='+xtxt_key+'&xtrxno='+xtrxno+'&xref=<?=$PHP_SELF?>&xtxt_ref='+xtxt_ref+'&xtx_cheq_batch'+xtx_cheq_batch+'&xloccd='+xloccd;
					new Ajax.Request(url, {
						method: 'get',
						onSuccess: function(transport) {
							var lbtxt = transport.responseText;
						//	alert(lbtxt);
							<?/*OK||0.35|VOU|070903*/?>
							var arr_split=lbtxt.split("|");

							if (arr_split[0]=='OK')	 {
								//alert(arr_split[9]+arr_split[7]);
								shortcut.<?=$chk_input?>.value=arr_split[2];
								shortcut.tmp_batch_che_txt.value=arr_split[4];
								calc_tot_pay();
								shortcut.tmp_che_valid.value='1';
								shortcut.tmp_exp_che_txt.value=arr_split[5];
								shortcut.tmp_che_txt.value=shortcut.chk_text.value;
								shortcut.tmp_che_amount.value=arr_split[2];
								shortcut.tmp_che_contraloc.value=arr_split[6];
								shortcut.tmp_che_memcode.value=arr_split[7];
								shortcut.tmp_che_status.value=arr_split[9];
								if (arr_split[8]=='EXP'){
								//	alert(arr_split[8]+arr_split[7]);
									//check_contra2();
									return false;
								}
							}else{
								shortcut.<?=$chk_input?>.value='0';
								shortcut.tmp_batch_che_txt.value='';
								shortcut.tmp_che_valid.value='0';
								shortcut.tmp_exp_che_txt.value='';
								shortcut.tmp_che_amount.value='0';
								shortcut.tmp_che_txt.value='';
								shortcut.tmp_che_contraloc.value='';
								shortcut.tmp_che_memcode.value='';
								shortcut.tmp_che_status.value=arr_split[3];
								calc_tot_pay();
								if (arr_split[2]=='NOTFOUND') alert(arr_split[1]);
								if (arr_split[2]=='DOUBLE') alert(arr_split[1]);
								//check_contra2();
								return false;
							}
						}
					});
				}else{
					//shortcut.<?=$chk_input?>.value='0';
					shortcut.tmp_batch_che_txt.value='';
					shortcut.tmp_exp_che_txt.value='';
					shortcut.tmp_che_valid.value='0';
					shortcut.tmp_che_amount.value='0';
					shortcut.tmp_che_txt.value='';
					shortcut.tmp_che_status.value='';
					calc_tot_pay();
				}
			}
<?
		}
?>
		}
<?
	}
?>
<?
}
function fget_st_data_IOC_item($xtxt_code,$xprdcode,$xtxtTgl1,$xtxtTgl2,$xopening,$xflag='in#out'){
	global $db, $deduct_prod_item,$debug,$region;
	//$ccresult=pg_exec($db,"set datestyle to 'POSTGRES,EUROPEAN';");
	$qsql = "select sum(b.qty) as vqty from newsctrd_ioc b,newsctrh_ioc a where b.trcd=a.trcd and b.prdcd='$xprdcode' and a.loccd='$xtxt_code' and trtype='13' ";

	if ($xopening==1) {
		$qsql .=" and a.trdt<to_date('$xtxtTgl1','dd/mm/yyyy') ";
	} else if ($xopening==11) {
		$qsql .=" and a.trdt<=to_date('$xtxtTgl1','dd/mm/yyyy') ";
	} else if ($xopening==12) {
		$qsql .=" and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
	}else{
		$qsql .=" and a.trdt>=to_date('$xtxtTgl1','dd/mm/yyyy') and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
	}

	if ($deduct_prod_item==1) {
		/*
		$qsql.=	"union all select sum(b.qty*d.inv_qty) as vqty from newsctrh_ioc a, newsctrd_ioc b, msprd_items d
			where a.trcd=b.trcd and a.loccd='$xtxt_code' and a.trtype='13' and 
			((auto_convert='f' and b.prdcd in
			(select a.prdcd from msprd_items a, msprd xx
			where a.prdcd=xx.prdcd and  a.inv_prdcd='$xprdcode' and a.cn_id='$region' 
			and a.prdcd<>'$xprdcode' and a.is_normal='1' and xx.type='2' and xx.scdp2='1'))
			or auto_convert and b.prdcd in (select a.prdcd from msprd_items a, msprd xx
			where a.prdcd=xx.prdcd and  a.inv_prdcd='$xprdcode' and a.cn_id='$region' 
			and a.prdcd<>'$xprdcode' and a.is_normal='1' and xx.type='2'))
			and d.prdcd=b.prdcd and d.inv_prdcd='$xprdcode' and d.cn_id='$region' ";

		if ($xopening==1) {
			$qsql.=" and a.trdt < to_date('$xtxtTgl1','dd/mm/yyyy') ";
		}else if ($xopening==11) {
			$qsql.=" and a.trdt<=to_date('$xtxtTgl1','dd/mm/yyyy') ";
		}else if ($xopening==12) {
			$qsql.=" and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
		}else{
			$qsql.=" and a.trdt>=to_date('$xtxtTgl1','dd/mm/yyyy') and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
		}
		*/
	}
	//print $qsql;
	$sql1 = "select sum(vqty) from ($qsql) as table1 " ;
	$xres = pg_query($db, $sql1);
	$hsql	= 0;
	if(pg_num_rows($xres)>0) {
		$rowsql=pg_fetch_row($xres,0);
		$hsql	=$rowsql[0];
		pg_free_result($xres);
	}
	if ($hsql=='') $hsql=0;
	//Logger::debug("$qsql - ($hsql)");
	return $hsql;
}

function fget_st_data_CB_item($xtxt_code,$xprdcode,$xtxtTgl1,$xtxtTgl2,$xopening,$xflag='in#out'){
	global $db, $deduct_prod_item,$debug,$region;
	//$ccresult=pg_exec($db,"set datestyle to 'POSTGRES,EUROPEAN';");
	$qsql = "select sum(b.qty)  as vqty from newsctrd b,newsctrh a where b.trcd=a.trcd and b.prdcd='$xprdcode' and a.note3='$xtxt_code' and trtype in ('1','3','4') ";

	if ($xopening==1) {
		$qsql .=" and a.trdt<to_date('$xtxtTgl1','dd/mm/yyyy') ";
	} else if ($xopening==11) {
		$qsql .=" and a.trdt<=to_date('$xtxtTgl1','dd/mm/yyyy') ";
	} else if ($xopening==12) {
		$qsql .=" and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
	}else{
		$qsql .=" and a.trdt>=to_date('$xtxtTgl1','dd/mm/yyyy') and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
	}

	if ($deduct_prod_item==1) {
		$qsql.=	"union all select sum(b.qty*d.inv_qty) as vqty from newsctrh a, newsctrd b, msprd_items d
			where a.trcd=b.trcd and a.note3='$xtxt_code' and a.trtype='1' and 
			((auto_convert='f' and b.prdcd in
			(select a.prdcd from msprd_items a, msprd xx
			where a.prdcd=xx.prdcd and  a.inv_prdcd='$xprdcode' and a.cn_id='$region' 
			and a.prdcd<>'$xprdcode' and a.is_normal='1' and xx.type='2' and xx.scdp2='1'))
			or auto_convert and b.prdcd in (select a.prdcd from msprd_items a, msprd xx
			where a.prdcd=xx.prdcd and  a.inv_prdcd='$xprdcode' and a.cn_id='$region' 
			and a.prdcd<>'$xprdcode' and a.is_normal='1' and xx.type='2'))
			and d.prdcd=b.prdcd and d.inv_prdcd='$xprdcode' and d.cn_id='$region' ";

		if ($xopening==1) {
			$qsql.=" and a.trdt < to_date('$xtxtTgl1','dd/mm/yyyy') ";
		}else if ($xopening==11) {
			$qsql.=" and a.trdt<=to_date('$xtxtTgl1','dd/mm/yyyy') ";
		}else if ($xopening==12) {
			$qsql.=" and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
		}else{
			$qsql.=" and a.trdt>=to_date('$xtxtTgl1','dd/mm/yyyy') and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
		}
	}
	// print $qsql;
	$sql1 = "select sum(vqty) from ($qsql)  as table1 " ;
	$xres = pg_query($db, $sql1);
	$hsql	= 0;
	if(pg_num_rows($xres)>0) {
		$rowsql=pg_fetch_row($xres,0);
		$hsql	=$rowsql[0];
		pg_free_result($xres);
	}
	if ($hsql=='') $hsql=0;
	//Logger::debug("$qsql - ($hsql)");
	return $hsql;
}


function fget_st_data_CR_item($xtxt_code,$xprdcode,$xtxtTgl1,$xtxtTgl2,$xopening,$xflag='in#out'){
	global $db, $deduct_prod_item,$debug,$region;
	//$ccresult=pg_exec($db,"set datestyle to 'POSTGRES,EUROPEAN';");
	$qsql =
		"select sum(b.qty)  as vqty
		from newsctrd b,newsctrh a
		where b.trcd=a.trcd and trim(b.prdcd)='$xprdcode' and trim(a.note3)='$xtxt_code'
		and a.trtype='2' ";

	if ($xopening==1) {
		$qsql.=" and a.trdt<to_date('$xtxtTgl1','dd/mm/yyyy') ";
	}else if ($xopening==11) {
		$qsql.=" and a.trdt<=to_date('$xtxtTgl1','dd/mm/yyyy') ";
	}else if ($xopening==12) {
		$qsql.=" and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
	}else{
		$qsql.=" and a.trdt>=to_date('$xtxtTgl1','dd/mm/yyyy') and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
	}

	if ($deduct_prod_item==1) {
		$qsql.=
			"union select sum(b.qty*d.inv_qty) as vqty
 			from newsctrh a, newsctrd b, msprd_items d
			where a.trcd=b.trcd and a.note3='$xtxt_code' and a.trtype='2'
			and b.prdcd in
			(select a.prdcd from msprd_items a, msprd xx
			where a.prdcd=xx.prdcd and  a.inv_prdcd='$xprdcode' and a.prdcd<>'$xprdcode'
			and a.is_normal='1' and a.cn_id='$region' and xx.type='2')
			and d.prdcd=b.prdcd and d.cn_id='$region' and d.inv_prdcd='$xprdcode' ";

		if ($xopening==1) {
			$qsql.=" and a.trdt<to_date('$xtxtTgl1','dd/mm/yyyy') ";
		}else if ($xopening==11) {
			$qsql.=" and a.trdt<=to_date('$xtxtTgl1','dd/mm/yyyy') ";
		}else if ($xopening==12) {
			$qsql.=" and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
		}else{
			$qsql.=" and a.trdt>=to_date('$xtxtTgl1','dd/mm/yyyy') and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
		}
	}

	$sql1 = "select sum(vqty) from ($qsql)  as table1 " ;
	$xres = pg_query($db, $sql1);
	$hsql	= 0;
	if(pg_num_rows($xres)>0) {
		$rowsql=pg_fetch_row($xres,0);
		$hsql	=$rowsql[0];
		pg_free_result($xres);
	}
	if ($hsql=='') $hsql=0;
	//if ('FB001'==$xprdcode) echo($qsql.'-'.$hsql.'<br>');
	return $hsql;
}

function fget_st_data_FOC_item($xtxt_code,$xprdcode,$xtxtTgl1,$xtxtTgl2,$xopening,$xflag='in#out'){
	global $db, $deduct_prod_item,$debug,$region;
	//$ccresult=pg_exec($db,"set datestyle to 'POSTGRES,EUROPEAN';");
	$qsql =
		"select sum(b.qty)  as vqty
		from focsctrd b,focsctrh a
		where trim(b.prdcd)='$xprdcode'
		and trim(a.loccd)='$xtxt_code' and b.trcd=a.trcd and a.trtype='1'";

	if ($xopening==1) {
		$qsql.=" and a.trdt<to_date('$xtxtTgl1','dd/mm/yyyy') ";
	}else if ($xopening==11) {
		$qsql.=" and a.trdt<=to_date('$xtxtTgl1','dd/mm/yyyy') ";
	}else if ($xopening==12) {
		$qsql.=" and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
	}else{
		$qsql.=" and a.trdt>=to_date('$xtxtTgl1','dd/mm/yyyy') and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
	}

	if ($deduct_prod_item==1) {
		$qsql.=
			"union select sum(b.qty*d.inv_qty) as vqty
 			from focsctrh a, focsctrd b, msprd_items d
			where a.trcd=b.trcd
			and a.loccd='$xtxt_code' and b.trcd=a.trcd and a.trtype='1'
			and b.prdcd in
			(select a.prdcd from msprd_items a, msprd xx
			where a.prdcd=xx.prdcd and  a.inv_prdcd='$xprdcode' and a.prdcd<>'$xprdcode'
			and a.is_normal='1' and xx.type='2' and a.cn_id='$region')
			and d.prdcd=b.prdcd and d.cn_id='$region' and d.inv_prdcd='$xprdcode' ";

		if ($xopening==1) {
			$qsql.=" and a.trdt<to_date('$xtxtTgl1','dd/mm/yyyy') ";
		}else if ($xopening==11) {
			$qsql.=" and a.trdt<=to_date('$xtxtTgl1','dd/mm/yyyy') ";
		}else if ($xopening==12) {
			$qsql.=" and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
		}else{
			$qsql.=" and a.trdt>=to_date('$xtxtTgl1','dd/mm/yyyy') and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
		}
	}

	$sql1 = "select sum(vqty) from ($qsql)  as table1 " ;
	$xres = pg_query($db, $sql1);
	$hsql	= 0;
	if(pg_num_rows($xres)>0) {
		$rowsql=pg_fetch_row($xres,0);
		$hsql	=$rowsql[0];
		pg_free_result($xres);
	}
	if ($hsql=='') $hsql=0;
	//if ('FB001'==$xprdcode) echo($qsql.'-'.$hsql.'<br>');
	return $hsql;
}

function fget_st_data_INV_item($xtxt_code,$xprdcode,$xtxtTgl1,$xtxtTgl2,$xopening,$xflag='in#out'){
	global $db, $deduct_prod_item;
	//$ccresult=pg_exec($db,"set datestyle to 'POSTGRES,EUROPEAN';");
	$qsql =
		"select sum(b.qty) as vqty
		from newmsivtrd b,newmsivtrh a
		where trim(b.prdcd)='$xprdcode' and trim(a.loccd)='$xtxt_code'
		and b.trivcd=a.trivcd ";
	if ($xopening==1) {
		$qsql.=" and a.trdt<to_date('$xtxtTgl1','dd/mm/yyyy') ";
	}else if ($xopening==11) {
		$qsql.=" and a.trdt<=to_date('$xtxtTgl1','dd/mm/yyyy') ";
	}else if ($xopening==12) {
		$qsql.=" and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
	}else{
		$qsql.=" and a.trdt>=to_date('$xtxtTgl1','dd/mm/yyyy') and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
	}

	if ($deduct_prod_item==1) {
		$qsql.=
			"union select sum(b.qty*d.inv_qty) as vqty
 			from newmsivtrh a, newmsivtrd b, msprd_items d
			where a.trivcd=b.trivcd and a.note3='$xtxt_code'
			and b.prdcd in
			(select a.prdcd from msprd_items a, msprd xx
			where a.prdcd=xx.prdcd and  a.inv_prdcd='$xprdcode' and a.prdcd<>'$xprdcode'
			and a.is_normal='1' and a.cn_id='$region' and xx.type='2')
			and d.prdcd=b.prdcd and d.cn_id='$region' and d.inv_prdcd='$xprdcode' ";

		if ($xopening==1) {
			$qsql.=" and a.trdt<to_date('$xtxtTgl1','dd/mm/yyyy') ";
		}else if ($xopening==11) {
			$qsql.=" and a.trdt<=to_date('$xtxtTgl1','dd/mm/yyyy') ";
		}else if ($xopening==12) {
			$qsql.=" and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
		}else{
			$qsql.=" and a.trdt>=to_date('$xtxtTgl1','dd/mm/yyyy') and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
		}
	}
	$sql1 = "select sum(vqty) from ($qsql)  as table1 " ;

	$xres = pg_query($db, $sql1);
	$hsql	= 0;
	if(pg_num_rows($xres)>0) {
		$rowsql=pg_fetch_row($xres,0);
		$hsql	=$rowsql[0];
		pg_free_result($xres);
	}
	if ($hsql=='') $hsql=0;
	//if ('KIT2'==$xprdcode) echo($qsql.'-'.$hsql.'<br>');
	return $hsql;
}

function fget_st_data_receive_item($xtxt_code, $xprdcode,$xtxtTgl1,$xtxtTgl2, $xopening){
	global $db, $deduct_prod_item, $debug, $region;
	$qsql=
		"SELECT b.prdcd,sum(b.qty) as vqty
		FROM rrdistrd b,rrdistrh a
		WHERE a.code='$xtxt_code' AND b.trrrcd=a.trrrcd
		AND trim(b.prdcd)='$xprdcode' AND a.status='1'";

	if ($xopening==1) {
		$qsql.=" and a.trdt < to_date('$xtxtTgl1','dd/mm/yyyy') ";
	}else if ($xopening==11) {
		$qsql.=" and a.trdt<=to_date('$xtxtTgl1','dd/mm/yyyy') ";
	}else if ($xopening==12) {
		$qsql.=" and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
	}else{
		$qsql.=" and a.trdt>=to_date('$xtxtTgl1','dd/mm/yyyy') and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
	}
	$qsql.=" group by b.prdcd ";
	

	if ($deduct_prod_item==1) {
		$qsql.=
			"union select b.prdcd,sum(b.qty*d.inv_qty) as vqty
 			from rrdistrh a, rrdistrd b, msprd_items d
			where a.trrrcd=b.trrrcd and a.code='$xtxt_code' AND a.status='1' and
			((auto_convert='f' and b.prdcd in
			(select a.prdcd from msprd_items a, msprd xx
			where a.prdcd=xx.prdcd and  a.inv_prdcd='$xprdcode' and a.cn_id='$region' 
			and a.prdcd<>'$xprdcode' and a.is_normal='1' and xx.type='2' and xx.scdp2='1'))
			or auto_convert and b.prdcd in (select a.prdcd from msprd_items a, msprd xx
			where a.prdcd=xx.prdcd and  a.inv_prdcd='$xprdcode' and a.cn_id='$region' 
			and a.prdcd<>'$xprdcode' and a.is_normal='1' and xx.type='2'))
			and d.prdcd=b.prdcd and d.cn_id='$region' and d.inv_prdcd='$xprdcode' ";

		if ($xopening==1) {
			$qsql.=" and a.trdt < to_date('$xtxtTgl1','dd/mm/yyyy') ";
		}else if ($xopening==11) {
			$qsql.=" and a.trdt<=to_date('$xtxtTgl1','dd/mm/yyyy') ";
		}else if ($xopening==12) {
			$qsql.=" and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
		}else{
			$qsql.=" and a.trdt>=to_date('$xtxtTgl1','dd/mm/yyyy') and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
		}
		$qsql.=" group by b.prdcd ";
	}

	$sql1 = "select sum(vqty) from ($qsql)  as table1 " ;
	$rsql1=pg_query($db,$sql1);
	$hsql	= 0;

	if(pg_num_rows($rsql1)>0) {
		$rowsql=pg_fetch_row($rsql1,0);
		$hsql=$rowsql[0];
		pg_free_result($rsql1);
	}

	if ($hsql=='') $hsql=0;
	Logger::debug("Receive => $sql1 - ($hsql)");
	return $hsql;
}

function fget_st_data_ADJ_item($xtxt_code,$xprdcode,$xtxtTgl1,$xtxtTgl2,$xopening,$xflag='in#out'){
	global $db, $deduct_prod_item, $debug, $region;
	//$ccresult=pg_exec($db,"set datestyle to 'POSTGRES,EUROPEAN';");
	$qsql =
		"select sum(b.qty) as vqty
		from adsctrh a, adsctrd b
		where a.trcd=b.trcd and b.prdcd='$xprdcode' and trim(a.code)='$xtxt_code'  ";

	if ($xopening==1) {
		$qsql.=" and a.trdt<to_date('$xtxtTgl1','dd/mm/yyyy') ";
	}else if ($xopening==11) {
		$qsql.=" and a.trdt<=to_date('$xtxtTgl1','dd/mm/yyyy') ";
	}else if ($xopening==12) {
		$qsql.=" and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
	}else{
		$qsql.=" and a.trdt>=to_date('$xtxtTgl1','dd/mm/yyyy') and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
	}

// 	if ($deduct_prod_item==1) {
// 		$qsql.=
// 			"union select sum(b.qty*d.inv_qty) as vqty
//  			from adsctrh a, adsctrd b, msprd_items d
// 			where a.trcd=b.trcd and a.code='$xtxt_code'
// 			and b.prdcd in (select prdcd from msprd_items where inv_prdcd='$xprdcode' and prdcd<>'$xprdcode' and is_normal=1)
// 			and d.prdcd=b.prdcd and d.inv_prdcd='$xprdcode' ";
//
// 		if ($xopening==1) {
// 			$qsql.=" and a.trdt<'$xtxtTgl1' ";
// 		}else if ($xopening==11) {
// 			$qsql.=" and a.trdt<='$xtxtTgl1' ";
// 		}else{
// 			$qsql.=" and a.trdt>='$xtxtTgl1' and a.trdt<='$xtxtTgl2' ";
// 		}
// 	}

	$sql1 = "select sum(vqty) from ($qsql)  as table1 " ;
	$xres = pg_query($db, $sql1);
	$hsql	= 0;
	if(pg_num_rows($xres)>0) {
		$rowsql=pg_fetch_row($xres,0);
		$hsql	=$rowsql[0];
		pg_free_result($xres);
	}
	if ($hsql=='') $hsql=0;
	//if ('FB001'==$xprdcode) echo($qsql.'-'.$hsql.'<br>');
	return $hsql;
}

function fempty_voucher_data($xtrxno,$xmode) {
	// $xmode 0 --> vinv  1 --> vcb
	global $db, $debug;
	if ($xmode=='0') {
		//cek loccd is mainstockist
// 		$xcek =
// 			"select b.stockist,a.loccd,a.code
// 			from newmsivtrh a, sub_mssc_extra b, sub_mssc_extra c
// 			where a.loccd=b.scname and a.code=c.scname
// 			and b.stockist='1' and a.trivcd='$xtrxno'; ";

		$xcek =
			"select c.stockist,a.loccd,a.code
 			from newmsivtrh a, sub_mssc_extra c
			where a.loccd=c.scname
			and c.stockist='0' and a.trivcd='$xtrxno';";

		if ($debug) echo($xcek."<br>\n");
		$rcek1 = pg_query($db, $xcek);
		//echo("pg_num_rows=".pg_num_rows($rcek1)."<br>\n");
		if (pg_num_rows($rcek1)>0) { //if loccd==ms stockist
			$xsql =
				"update voucher_data set msvinv=null, msvloc=null, mstrxdate=null
				where msvinv='$xtrxno'; ";
		}else{
			$xsql =
				"update voucher_data set vinv=null, scvloc=null, sctrxdate=null
				where vinv='$xtrxno'; ";
		}
		pg_free_result($rcek1);
	}else{
		$xsql =
			"update voucher_data set vcb=null, vloc=null, trxdate=null
			where vcb='$xtrxno'; ";
	}
	if ($debug) echo("$xsql<br>\n");
	else pg_exec($db, $xsql);
}

function fget_price($xmsid, $xprdcd, $xbr=1) {
	global $db;
	$xresult=0;
	$region = chkregion($xmsid, $xbr);
	if ($xmsid=='') {
		$xdp = " dp ";
	}else{
		if($region=="WEST") $xdp = " wdp ";
		else $xdp = " dp ";
	}
	$xsql1 = "select $xdp from msprd where prdcd='$xprdcd' ";
	$xres1 = pg_exec($db, $xsql1);
	if (pg_num_rows($xres1)>0)
		$xresult=pg_fetch_result($xres1, 0, 0);
	else
		$xresult=0;
	pg_free_result($xres1);
	return $xresult;
}
?>
<?
function date_validation() {
?>
	<script language = "Javascript">
	/**
	* DHTML date validation script. Courtesy of SmartWebby.com (http://www.smartwebby.com/dhtml/)
	*/
	// Declaring valid date character, minimum year and maximum year
	var dtCh= "/";
	var minYear=1900;
	var maxYear=2100;

	function isInteger(s){
		var i;
			for (i = 0; i < s.length; i++){
					// Check that current character is number.
					var c = s.charAt(i);
					if (((c < "0") || (c > "9"))) return false;
			}
			// All characters are numbers.
			return true;
	}

	function stripCharsInBag(s, bag){
		var i;
			var returnString = "";
			// Search through string's characters one by one.
			// If character is not in bag, append to returnString.
			for (i = 0; i < s.length; i++){
					var c = s.charAt(i);
					if (bag.indexOf(c) == -1) returnString += c;
			}
			return returnString;
	}

	function daysInFebruary (year){
		// February has 29 days in any year evenly divisible by four,
			// EXCEPT for centurial years which are not also divisible by 400.
			return (((year % 4 == 0) && ( (!(year % 100 == 0)) || (year % 400 == 0))) ? 29 : 28 );
	}
	function DaysArray(n) {
		for (var i = 1; i <= n; i++) {
			this[i] = 31
			if (i==4 || i==6 || i==9 || i==11) {this[i] = 30}
			if (i==2) {this[i] = 29}
		}
		return this
	}

	function isDate(dtStr){
		var daysInMonth = DaysArray(12)
		var pos1=dtStr.indexOf(dtCh)
		var pos2=dtStr.indexOf(dtCh,pos1+1)
		var strDay=dtStr.substring(0,pos1)
		var strMonth=dtStr.substring(pos1+1,pos2)
		var strYear=dtStr.substring(pos2+1)
		strYr=strYear
		if (strDay.charAt(0)=="0" && strDay.length>1) strDay=strDay.substring(1)
		if (strMonth.charAt(0)=="0" && strMonth.length>1) strMonth=strMonth.substring(1)
		for (var i = 1; i <= 3; i++) {
			if (strYr.charAt(0)=="0" && strYr.length>1) strYr=strYr.substring(1)
		}
		month=parseInt(strMonth)
		day=parseInt(strDay)
		year=parseInt(strYr)
		if (pos1==-1 || pos2==-1){
			alert("<?=mxlang("1981")?> : dd/mm/yyyy")
			return false
		}
		if (strMonth.length<1 || month<1 || month>12){
			alert("<?=mxlang("1982")?>")
			return false
		}
		if (strDay.length<1 || day<1 || day>31 || (month==2 && day>daysInFebruary(year)) || day > daysInMonth[month]){
			alert("<?=mxlang("1983")?>")
			return false
		}
		if (strYear.length != 4 || year==0 || year<minYear || year>maxYear){
			alert("<?=mxlang("1984")?> "+minYear+" <?=mxlang("1985")?> "+maxYear)
			return false
		}
		if (dtStr.indexOf(dtCh,pos2+1)!=-1 || isInteger(stripCharsInBag(dtStr, dtCh))==false){
			alert("<?=mxlang("1986")?>")
			return false
		}
	return true
	}
	</script>
<?
}

function selBANK($fldname, $xval) {
	global $db,$debug;
	echo("<select name='$fldname'>\n");
	echo("<option value=''>--".mxlang("1967")."--</option>\n");
	$xsql="select fcode,fdesc from tbank";
	$xres=pg_exec($db, $xsql);
	for ($i=0;$i<pg_num_rows($xres);$i++) {
		$xrow = pg_fetch_row($xres, $i);
		$xselected=($xval==$xrow[0])?'selected':'';
		echo("<option value='$xrow[0]' $xselected>$xrow[1]</option>\n");
	}
	echo("</select>\n");
	pg_free_result($xres);
}

function fget_bankname($xval) {
	global $db;
	$xresult='';
	$xsql="select fdesc from tbank where fcode='$xval'";
	$xres=pg_exec($db, $xsql);
	if (pg_num_rows($xres))
		$xresult=pg_fetch_result($xres, 0, 0);
	pg_free_result($xres);
	return $xresult;
}

function fget_batchno() {
	global $db, $debug;
	$xmonth = date("ymd");
	$xbsql = "select max(fbatchno) from tcheque where fbatchno like '$xmonth%'";
	//if ($debug)	 echo($xbsql);
	$xbres = pg_exec($db, $xbsql);
	if (pg_num_rows($xbres)==0) $xresult=$xmonth.'01';
	else{
		$xmax = substr(pg_fetch_result($xbres, 0, 0),6,2);
		if (substr($xmax, 0, 1)=='0') $xmax=substr($xmax, 1, 1);
		$xmax=$xmax+1;
		$xresult=$xmonth.str_pad($xmax, 2, "0", STR_PAD_LEFT);
	}
	pg_free_result($xbres);
	return $xresult;
}

function selCURR($fldname, $xval, $xall=0, $xfunction='') {
	global $db,$debug;
	echo("<select name='$fldname' $xfunction>\n");
	if ($xall==1) {
		echo("<option value=''>--".mxlang("1969")."--</option>\n");
	}
	$xsql="select fcode,fdesc from tcurr where factive='Y' order by fcode ";
	$xres=pg_exec($db, $xsql);
	for ($i=0;$i<pg_num_rows($xres);$i++) {
		$xrow = pg_fetch_row($xres, $i);
		$xselected=($xval==$xrow[0])?'selected':'';
		echo("<option value='$xrow[0]' $xselected>$xrow[1] ($xrow[0])</option>\n");
	}
	echo("</select>\n");
	pg_free_result($xres);
}

function selCURRSET($fldname, $xval, $xall=0) {
	global $db,$debug;
	echo("<select name='$fldname'>\n");
	if ($xall==1) {
		echo("<option value=''>--".ucwords(strtolower(mxlang("1987")))."--</option>\n");
	}
	$xsql="select fcode,fdesc from tcurrset where factive='Y' order by fcode ";
	$xres=pg_exec($db, $xsql);
	for ($i=0;$i<pg_num_rows($xres);$i++) {
		$xrow = pg_fetch_row($xres, $i);
		$xselected=($xval==$xrow[0])?'selected':'';
		echo("<option value='$xrow[0]' $xselected>$xrow[1] ($xrow[0])</option>\n");
	}
	echo("</select>\n");
	pg_free_result($xres);
}

function fget_cb_data_IOC_item($xtxt_code, $xprdcode,$xtxtTgl1,$xtxtTgl2, $xopening){
	global $db, $deduct_prod_item, $vonly_positive,$opnm,$debug;
	$qsql=	"SELECT sum(b.qty) as vqty FROM msprd a,newmstrd_ioc b,newmstrh_ioc c
		WHERE b.trcd=c.trcd AND trim(b.prdcd)=trim(a.prdcd) and trtype='13' and b.prdcd='$xprdcode' ";
	if ($vonly_positive) $qsql.="and b.qty>0 ";
	if ($xtxt_code=='WEST' || $xtxt_code=='EAST') {
		$qsql.=" and c.loccd in (
				select msms_new.br_code
					from msms_new where msms_new.br_region ='$xtxt_code'
			) ";
	}elseif ($xtxt_code=='ALL') {
		$qsql.=" and c.loccd in (select br_code from msms_new,users_braccess  where msms_new.br_code=users_braccess.brcode and br_status=true and br_deleted=false and users_braccess.uname='$opnm') ";
	}else{
		$qsql.=" and c.loccd='$xtxt_code' ";
	}

	if ($xopening==1) {
		$qsql.=" and c.trdt<'$xtxtTgl1' ";
	}else if ($xopening==11) {
		$qsql.=" and c.trdt<='$xtxtTgl1' ";
	}else if ($xopening==12) {
		$qsql.=" and c.trdt<='$xtxtTgl2' ";
	}else{
		$qsql.=" and c.trdt>='$xtxtTgl1' and c.trdt<='$xtxtTgl2' ";
	}

	//if ($xtxt_code=='ALL') 
	// echo "$xprdcode : ".$qsql."<br>";

	if ($deduct_prod_item==1) {
	/*
	$qsql.=
		"union select sum(b.qty*d.inv_qty) as vqty
		from newmstrd b, newmstrh c, msprd_items d
		where  b.trcd=c.trcd
		and b.prdcd in
		(select a.prdcd from msprd_items a, msprd xx
		where a.prdcd=xx.prdcd and  a.inv_prdcd='$xprdcode' and a.prdcd<>'$xprdcode'
		and a.is_normal=1 and xx.type=2)
		and d.prdcd=b.prdcd and
		(c.trtype='1' or c.trtype='3' or c.trtype='4')
		and d.inv_prdcd='$xprdcode' ";
		if ($vonly_positive) $qsql.="and b.qty>0 ";

		if ($xtxt_code=='WEST' || $xtxt_code=='EAST') {
			$qsql.=" and c.loccd in (
					select msms_new.br_code
					from msms_new where msms_new.br_region ='$xtxt_code'
				) ";
		}elseif ($xtxt_code=='ALL') {
			$qsql.=" and c.loccd in (select br_code from msms_new,users_braccess  where msms_new.br_code=users_braccess.brcode and br_status=true and br_deleted=false and users_braccess.uname='$opnm') ";
		}else{
			$qsql.=" and c.loccd='$xtxt_code' ";
		}



		if ($xopening==1) {
			$qsql.=" and c.trdt<'$xtxtTgl1' ";
		}else if ($xopening==11) {
			$qsql.=" and c.trdt<='$xtxtTgl1' ";
		}else if ($xopening==12) {
			$qsql.=" and c.trdt<='$xtxtTgl2' ";
		}else{
			$qsql.=" and c.trdt>='$xtxtTgl1' and c.trdt<='$xtxtTgl2' ";
		} */
	}
	$sql1 = "select sum(vqty) from ($qsql)  as table1 " ;
	//$qsql.=" GROUP by a.prdcd ";

	$rsql=pg_query($db,$sql1);
	$hsql	= 0;
	if(pg_num_rows($rsql)>0) {
		$rowsql=pg_fetch_row($rsql,0);
		$hsql=$rowsql[0];
		pg_free_result($rsql);
	}
	//if ('FB001'==$xprdcode) echo($sql1.'<br>');
	if ($hsql=='') $hsql=0;
	return $hsql;
}

function fget_cb_data_CB_item($xtxt_code, $xprdcode,$xtxtTgl1,$xtxtTgl2, $xopening){
	global $db, $deduct_prod_item, $vonly_positive,$opnm,$debug;
	$qsql=	"SELECT sum(b.qty) as vqty FROM msprd a,newmstrd b,newmstrh c
		WHERE b.trcd=c.trcd AND trim(b.prdcd)=trim(a.prdcd) and
		(trtype='1' or trtype='3' or trtype='4') and b.prdcd='$xprdcode' ";
	if ($vonly_positive) $qsql.="and b.qty>0 ";
	if ($xtxt_code=='WEST' || $xtxt_code=='EAST') {
		$qsql.=" and c.loccd in (
				select msms_new.br_code
					from msms_new where msms_new.br_region ='$xtxt_code'
			) ";
	}elseif ($xtxt_code=='ALL') {
		$qsql.=" and c.loccd in (select br_code from msms_new,users_braccess  where msms_new.br_code=users_braccess.brcode and br_status=true and br_deleted=false and users_braccess.uname='$opnm') ";
	}else{
		$qsql.=" and c.loccd='$xtxt_code' ";
	}

	if ($xopening==1) {
		$qsql.=" and c.trdt<'$xtxtTgl1' ";
	}else if ($xopening==11) {
		$qsql.=" and c.trdt<='$xtxtTgl1' ";
	}else if ($xopening==12) {
		$qsql.=" and c.trdt<='$xtxtTgl2' ";
	}else{
		$qsql.=" and c.trdt>='$xtxtTgl1' and c.trdt<='$xtxtTgl2' ";
	}

	//if ($xtxt_code=='ALL') echo $qsql."\n";

	if ($deduct_prod_item==1) {
	/*
	$qsql.=
		"union select sum(b.qty*d.inv_qty) as vqty
		from newmstrd b, newmstrh c, msprd_items d
		where  b.trcd=c.trcd
		and b.prdcd in
		(select a.prdcd from msprd_items a, msprd xx
		where a.prdcd=xx.prdcd and  a.inv_prdcd='$xprdcode' and a.prdcd<>'$xprdcode'
		and a.is_normal=1 and xx.type=2)
		and d.prdcd=b.prdcd and
		(c.trtype='1' or c.trtype='3' or c.trtype='4')
		and d.inv_prdcd='$xprdcode' ";
		if ($vonly_positive) $qsql.="and b.qty>0 ";

		if ($xtxt_code=='WEST' || $xtxt_code=='EAST') {
			$qsql.=" and c.loccd in (
					select msms_new.br_code
					from msms_new where msms_new.br_region ='$xtxt_code'
				) ";
		}elseif ($xtxt_code=='ALL') {
			$qsql.=" and c.loccd in (select br_code from msms_new,users_braccess  where msms_new.br_code=users_braccess.brcode and br_status=true and br_deleted=false and users_braccess.uname='$opnm') ";
		}else{
			$qsql.=" and c.loccd='$xtxt_code' ";
		}



		if ($xopening==1) {
			$qsql.=" and c.trdt<'$xtxtTgl1' ";
		}else if ($xopening==11) {
			$qsql.=" and c.trdt<='$xtxtTgl1' ";
		}else if ($xopening==12) {
			$qsql.=" and c.trdt<='$xtxtTgl2' ";
		}else{
			$qsql.=" and c.trdt>='$xtxtTgl1' and c.trdt<='$xtxtTgl2' ";
		} */
	}
	$sql1 = "select sum(vqty) from ($qsql)  as table1 " ;
	//$qsql.=" GROUP by a.prdcd ";

	$rsql=pg_query($db,$sql1);
	$hsql	= 0;
	if(pg_num_rows($rsql)>0) {
		$rowsql=pg_fetch_row($rsql,0);
		$hsql=$rowsql[0];
		pg_free_result($rsql);
	}
	//if ('FB001'==$xprdcode) echo($sql1.'<br>');
	if ($hsql=='') $hsql=0;
	return $hsql;
}

function fget_cb_data_FOC_item($xtxt_code, $xprdcode,$xtxtTgl1,$xtxtTgl2, $xopening){
	global $db, $deduct_prod_item,$opnm;
	$qsql=
		"SELECT sum(b.qty) as vqty
		FROM msprd a,focmstrd b,focmstrh c
		WHERE
			b.trcd=c.trcd AND trim(b.prdcd)=trim(a.prdcd)
			and b.prdcd='$xprdcode' ";

	if ($xtxt_code=='WEST' || $xtxt_code=='EAST') {
		$qsql.=" and c.loccd in (
				select msms_new.br_code
					from msms_new where msms_new.br_region ='$xtxt_code'
			) ";
	}elseif ($xtxt_code=='ALL') {
		$qsql.=" and c.loccd in (select br_code from msms_new,users_braccess  where msms_new.br_code=users_braccess.brcode and br_status=true and br_deleted=false and users_braccess.uname='$opnm') ";
	}else{
		$qsql.=" and c.loccd='$xtxt_code' ";
	}


	if ($xopening==1) {
		$qsql.=" and c.trdt<'$xtxtTgl1' ";
	}else if ($xopening==11) {
		$qsql.=" and c.trdt<='$xtxtTgl1' ";
	}else if ($xopening==12) {
		$qsql.=" and c.trdt<='$xtxtTgl2' ";
	}else{
		$qsql.=" and c.trdt>='$xtxtTgl1' and c.trdt<='$xtxtTgl2' ";
	}
	if ($deduct_prod_item==1) { /*
	$qsql.=
		"union select sum(b.qty*d.inv_qty) as vqty
		from focmstrd b, focmstrh c, msprd_items d
		where  b.trcd=c.trcd
		and b.prdcd in
		(select a.prdcd from msprd_items a, msprd xx
		where a.prdcd=xx.prdcd and  a.inv_prdcd='$xprdcode' and a.prdcd<>'$xprdcode'
		and a.is_normal=1 and xx.type=2)
		and d.prdcd=b.prdcd and d.inv_prdcd='$xprdcode' ";

		if ($xtxt_code=='WEST' || $xtxt_code=='EAST') {
		$qsql.=" and c.loccd in (
				select msms_new.br_code
					from msms_new where msms_new.br_region ='$xtxt_code'
			) ";
		}elseif ($xtxt_code=='ALL') {
			$qsql.=" and c.loccd in (select br_code from msms_new,users_braccess  where msms_new.br_code=users_braccess.brcode and br_status=true and br_deleted=false and users_braccess.uname='$opnm') ";
		}else{
			$qsql.=" and c.loccd='$xtxt_code' ";
		}


		if ($xopening==1) {
			$qsql.=" and c.trdt<'$xtxtTgl1' ";
		}else if ($xopening==11) {
			$qsql.=" and c.trdt<='$xtxtTgl1' ";
		}else if ($xopening==12) {
			$qsql.=" and c.trdt<='$xtxtTgl2' ";
		}else{
			$qsql.=" and c.trdt>='$xtxtTgl1' and c.trdt<='$xtxtTgl2' ";
		}*/
	}
	$sql1 = "select sum(vqty) from ($qsql)  as table1 " ;

	$rsql=pg_query($db,$sql1);
	$hsql	= 0;
	if(pg_num_rows($rsql)>0) {
		$rowsql=pg_fetch_row($rsql,0);
		$hsql=$rowsql[0];
		pg_free_result($rsql);
	}
	//if ('FB001'==$xprdcode) echo($qsql.'-'.$hsql.'<br>');
	if ($hsql=='') $hsql=0;
	return $hsql;
}

function fget_cb_data_CR_item($xtxt_code, $xprdcode,$xtxtTgl1,$xtxtTgl2, $xopening, $xcr=0){
	global $db, $deduct_prod_item,$opnm;
	//$xcr = 0 --> all
	//$xcr = 1 --> SP
	//$xcr = 2 --> CR
	$qsql=
		"SELECT sum(b.qty) as vqty
		FROM msprd a,newmstrd b, newmstrh c
		WHERE
			b.trcd=c.trcd AND trim(b.prdcd)=trim(a.prdcd) and c.trtype='2'
			and b.prdcd='$xprdcode' ";
	if ($xcr==1) $qsql.="and (c.note3='' or c.note3 is null) ";
	// ($xcr==2) $qsql.="and (c.note3='CR') ";
	if ($xcr==2) $qsql.="and (not c.note3 is null and c.note3<>'') ";

	if ($xtxt_code=='WEST' || $xtxt_code=='EAST') {
		$qsql.=" and c.loccd in (
				select msms_new.br_code
					from msms_new where msms_new.br_region ='$xtxt_code'
			) ";
	}elseif ($xtxt_code=='ALL') {
		$qsql.=" and c.loccd in (select br_code from msms_new,users_braccess  where msms_new.br_code=users_braccess.brcode and br_status=true and br_deleted=false and users_braccess.uname='$opnm') ";
	}else{
		$qsql.=" and c.loccd='$xtxt_code' ";
	}

	if ($xopening==1) {
		$qsql.=" and c.trdt<'$xtxtTgl1' ";
	}else if ($xopening==11) {
		$qsql.=" and c.trdt<='$xtxtTgl1' ";
	}else if ($xopening==12) {
		$qsql.=" and c.trdt<='$xtxtTgl2' ";
	}else{
		$qsql.=" and c.trdt>='$xtxtTgl1' and c.trdt<='$xtxtTgl2' ";
	}

	if ($deduct_prod_item==1) { /*
	$qsql.=
		"union select sum(b.qty*d.inv_qty) as vqty
		from newmstrd b, newmstrh c, msprd_items d
		where  b.trcd=c.trcd
		and b.prdcd in
		(select a.prdcd from msprd_items a, msprd xx
		where a.prdcd=xx.prdcd and  a.inv_prdcd='$xprdcode' and a.prdcd<>'$xprdcode'
		and a.is_normal=1 and xx.type=2)
		and d.prdcd=b.prdcd and d.inv_prdcd='$xprdcode'	and c.trtype='2' ";

	if ($xcr==1) $qsql.="and (c.note3='' or c.note3 is null) ";
	//if ($xcr==2) $qsql.="and (c.note3='CR') ";
	if ($xcr==2) $qsql.="and (not c.note3 is null and c.note3<>'') ";

		if ($xtxt_code=='WEST' || $xtxt_code=='EAST') {
			$qsql.=" and c.loccd in (
					select msms_new.br_code
					from msms_new where msms_new.br_region ='$xtxt_code'
				) ";
		}elseif ($xtxt_code=='ALL') {
			$qsql.=" and c.loccd in (select br_code from msms_new,users_braccess  where msms_new.br_code=users_braccess.brcode and br_status=true and br_deleted=false and users_braccess.uname='$opnm') ";
		}else{
			$qsql.=" and c.loccd='$xtxt_code' ";
		}

		if ($xopening==1) {
			$qsql.=" and c.trdt<'$xtxtTgl1' ";
		}else if ($xopening==11) {
			$qsql.=" and c.trdt<='$xtxtTgl1' ";
		}else if ($xopening==12) {
			$qsql.=" and c.trdt<='$xtxtTgl2' ";
		}else{
			$qsql.=" and c.trdt>='$xtxtTgl1' and c.trdt<='$xtxtTgl2' ";
		}*/
	}
	$sql1 = "select sum(vqty) from ($qsql)  as table1 " ;


	$rsql=pg_query($db,$sql1);
	$hsql	= 0;
	if(pg_num_rows($rsql)>0) {
		$rowsql=pg_fetch_row($rsql,0);
		$hsql=$rowsql[0];
		pg_free_result($rsql);
	}
	//if ('B0002'==$xprdcode && $opnm=='hima' && $xopening==0) echo($qsql.'-'.$hsql.'<br>');
	//if ($opnm=='hima') echo($qsql.'-'.$hsql.'<br>');
	if ($hsql=='') $hsql=0;
	return $hsql;
}

function fget_cb_data_INV_item($xtxt_code, $xprdcode,$xtxtTgl1,$xtxtTgl2, $xopening, $xflag='in#out'){
	global $db, $deduct_prod_item, $vonly_positive,$opnm;
	$qsql=	"SELECT sum(b.qty) as vqty FROM msprd a,newmsivtrd b, newmsivtrh c
		WHERE b.trivcd=c.trivcd AND trim(b.prdcd)=trim(a.prdcd)
		and b.prdcd='$xprdcode' ";
	if ($vonly_positive) $qsql.="and b.qty>0 ";

	if ($xtxt_code=='WEST' || $xtxt_code=='EAST') {
		$qsql.=" and c.loccd in (
			select msms_new.br_code
			from msms_new where msms_new.br_region ='$xtxt_code'
			) ";
	}elseif ($xtxt_code=='ALL') {
		$qsql.=" and c.loccd in (select br_code from msms_new,users_braccess  where msms_new.br_code=users_braccess.brcode and br_status=true and br_deleted=false and users_braccess.uname='$opnm') ";
	}else{
		$qsql.=" and c.loccd='$xtxt_code' ";
	}

	if ($xflag=='out') {
		$qsql.="and b.qty>0";
	}elseif ($xflag=='in') {
		$qsql.="and b.qty<0";
	}
	if ($xopening==1) {
		$qsql.=" and c.trdt<'$xtxtTgl1' ";
	}else if ($xopening==11) {
		$qsql.=" and c.trdt<='$xtxtTgl1' ";
	}else if ($xopening==12) {
		$qsql.=" and c.trdt<='$xtxtTgl2' ";
	}else{
		$qsql.=" and c.trdt>='$xtxtTgl1' and c.trdt<='$xtxtTgl2' ";
	}

	if ($xtxt_code!='WEST' || $xtxt_code!='EAST' || $xtxt_code!='ALL') $region = chkregion($xtxt_code,1);

	if ($deduct_prod_item==1) {
	/*
	$qsql.=	"union select sum(b.qty*d.inv_qty) as vqty from newmsivtrd b, newmsivtrh c, msprd_items d
		where  b.trivcd=c.trivcd and b.prdcd in (select a.prdcd from msprd_items a, msprd xx
		where a.prdcd=xx.prdcd and  a.inv_prdcd='$xprdcode' and a.cn_id='$region'
		and a.is_normal=1 and xx.type=2) and d.prdcd=b.prdcd and d.cn_id='$region' and d.inv_prdcd='$xprdcode' ";
		if ($vonly_positive) $qsql.="and b.qty>0 ";


		if ($xtxt_code=='WEST' || $xtxt_code=='EAST') {
			$qsql.=" and c.loccd in (
					select msms_new.br_code
					from msms_new where msms_new.br_region ='$xtxt_code'
				) ";
		}elseif ($xtxt_code=='ALL') {
			$qsql.=" and c.loccd in (select br_code from msms_new,users_braccess  where msms_new.br_code=users_braccess.brcode and br_status=true and br_deleted=false and users_braccess.uname='$opnm') ";
		}else{
			$qsql.=" and c.loccd='$xtxt_code' ";
		}

		if ($xflag=='out') {
			$qsql.="and b.qty>0";
		}elseif ($xflag=='in') {
			$qsql.="and b.qty<0";
		}
		if ($xopening==1) {
			$qsql.=" and c.trdt<'$xtxtTgl1' ";
		}else if ($xopening==11) {
			$qsql.=" and c.trdt<='$xtxtTgl1' ";
		}else if ($xopening==12) {
			$qsql.=" and c.trdt<='$xtxtTgl2' ";
		}else{
			$qsql.=" and c.trdt>='$xtxtTgl1' and c.trdt<='$xtxtTgl2' ";
		}
	*/
	}
	$sql1 = "select sum(vqty) from ($qsql)  as table1 " ;

	$rsql=pg_query($db,$sql1);
	$hsql	= 0;
	if(pg_num_rows($rsql)>0) {
		$rowsql=pg_fetch_row($rsql,0);
		$hsql=$rowsql[0];
		pg_free_result($rsql);
	}
	//if ('ALOE'==$xprdcode) echo($qsql.'-'.$hsql.'<br>');
	if ($hsql=='') $hsql=0;
	return $hsql;
}

function fget_cb_data_GRN_item($xtxt_code, $xprdcode,$xtxtTgl1,$xtxtTgl2, $xopening, $xflag){
	global $db, $deduct_prod_item,$opnm;
	$qsql=
		"SELECT sum(b.qty) as vqty
		FROM grnmstrd b, grnmstrh a
		WHERE
			b.trcd=a.trcd and b.prdcd='$xprdcode' ";

	if ($xflag=='in') {
		$qsql.=" and flag='1'";

		if ($xtxt_code=='WEST' || $xtxt_code=='EAST') {
			$qsql.=" and a.loccd in (
					select msms_new.br_code
					from msms_new where msms_new.br_region ='$xtxt_code'
				) ";
		}elseif ($xtxt_code=='ALL') {
		$qsql.=" and a.loccd in (select br_code from msms_new,users_braccess  where msms_new.br_code=users_braccess.brcode and br_status=true and br_deleted=false and users_braccess.uname='$opnm') ";
		}else{
			$qsql.=" and a.loccd='$xtxt_code' ";
		}

	}elseif ($xflag=='out') {

		if ($xtxt_code=='WEST' || $xtxt_code=='EAST') {
			$qsql.=" and a.code in (
					select msms_new.br_code
					from msms_new where msms_new.br_region ='$xtxt_code'
				) ";
		}elseif ($xtxt_code=='ALL') {
			$qsql.=" and a.code in (select br_code from msms_new,users_braccess  where msms_new.br_code=users_braccess.brcode and br_status=true and br_deleted=false and users_braccess.uname='$opnm') ";
		}else{
			$qsql.=" and a.code='$xtxt_code' ";
		}

		$qsql.=" and a.loccd='DDEPT' and flag='0'";
	} else {

		if ($xtxt_code=='WEST' || $xtxt_code=='EAST') {
			$qsql.=" and (a.code in (
					select msms_new.br_code
					from msms_new where msms_new.br_region ='$xtxt_code'
				) or a.loccd in (
					select msms_new.br_code
					from msms_new where msms_new.br_region ='$xtxt_code'))";

		}elseif ($xtxt_code=='ALL') {
			$qsql.=" and (a.code in (select br_code from msms_new,users_braccess  where msms_new.br_code=users_braccess.brcode and br_status=true and br_deleted=false and users_braccess.uname='$opnm')   or a.loccd in (select br_code from msms_new,users_braccess  where msms_new.br_code=users_braccess.brcode and br_status=true and br_deleted=false and users_braccess.uname='$opnm'))";
		}else{
			$qsql.="and (a.code='$xtxt_code' or a.loccd='$xtxt_code') ";
		}

		//$qsql.="and (a.code='$xtxt_code' or a.loccd='$xtxt_code') ";
	}
	if ($xopening==1) {
		$qsql.=" and a.trdt<'$xtxtTgl1' ";
	}else if ($xopening==11) {
		$qsql.=" and a.trdt<='$xtxtTgl1' ";
	}else if ($xopening==12) {
		$qsql.=" and a.trdt<='$xtxtTgl2' ";
	}else{
		$qsql.=" and a.trdt>='$xtxtTgl1' and a.trdt<='$xtxtTgl2' ";
	}

	if ($deduct_prod_item==1) { /*
	$qsql.=
		"union select sum(b.qty*d.inv_qty) as vqty
		from grnmstrd b, grnmstrh a, msprd_items d
		where  b.trcd=a.trcd
		and b.prdcd in
		(select a.prdcd from msprd_items a, msprd xx
		where a.prdcd=xx.prdcd and  a.inv_prdcd='$xprdcode' and a.prdcd<>'$xprdcode'
		and a.is_normal=1 and xx.type=2)
		and d.prdcd=b.prdcd and d.inv_prdcd='$xprdcode' ";
		if ($xflag=='in') {
		$qsql.=" and flag=1";

		if ($xtxt_code=='WEST' || $xtxt_code=='EAST') {
			$qsql.=" and a.loccd in (
					select msms_new.br_code
					from msms_new where msms_new.br_region ='$xtxt_code'
				) ";
		}elseif ($xtxt_code=='ALL') {
			$qsql.=" and a.loccd in (select br_code from msms_new,users_braccess  where msms_new.br_code=users_braccess.brcode and br_status=true and br_deleted=false and users_braccess.uname='$opnm') ";
		}else{
			$qsql.=" and a.loccd='$xtxt_code' ";
		}

	}elseif ($xflag=='out') {

		if ($xtxt_code=='WEST' || $xtxt_code=='EAST') {
			$qsql.=" and a.code in (
					select msms_new.br_code
					from msms_new where msms_new.br_region ='$xtxt_code'
				) ";
		}elseif ($xtxt_code=='ALL') {
			$qsql.=" and a.code in (select br_code from msms_new,users_braccess  where msms_new.br_code=users_braccess.brcode and br_status=true and br_deleted=false and users_braccess.uname='$opnm') ";
		}else{
			$qsql.=" and a.code='$xtxt_code' ";
		}

		$qsql.=" and a.loccd='DDEPT' and flag=0";
	} else {

		if ($xtxt_code=='WEST' || $xtxt_code=='EAST') {
			$qsql.=" and (a.code in (
					select msms_new.br_code
					from msms_new where msms_new.br_region ='$xtxt_code'
				) or a.loccd in (
					select msms_new.br_code
					from msms_new where msms_new.br_region ='$xtxt_code'))";

			}elseif ($xtxt_code=='ALL') {
				$qsql.=" and (a.code in (select br_code from msms_new,users_braccess  where msms_new.br_code=users_braccess.brcode and br_status=true and br_deleted=false and users_braccess.uname='$opnm')   or a.loccd in (select br_code from msms_new,users_braccess  where msms_new.br_code=users_braccess.brcode and br_status=true and br_deleted=false and users_braccess.uname='$opnm'))";
			}else{
				$qsql.="and (a.code='$xtxt_code' or a.loccd='$xtxt_code') ";
			}
		}
		if ($xopening==1) {
			$qsql.=" and a.trdt<'$xtxtTgl1' ";
		}else if ($xopening==11) {
			$qsql.=" and a.trdt<='$xtxtTgl1' ";
		}else if ($xopening==12) {
			$qsql.=" and a.trdt<='$xtxtTgl2' ";
		}else{
			$qsql.=" and a.trdt>='$xtxtTgl1' and a.trdt<='$xtxtTgl2' ";
		}*/
	}
	$sql1 = "select sum(vqty) from ($qsql)  as table1 " ;
	$rsql=pg_query($db,$sql1);
	$hsql	= 0;
	//if ($xprdcode=="FB001") print "$qsql<br>";
	if(pg_num_rows($rsql)>0) {
		$rowsql=pg_fetch_row($rsql,0);
		$hsql=$rowsql[0];
		if ($hsql=='') $hsql=0;
		pg_free_result($rsql);
	}
	//if ($hsql>0) echo($qsql.'-'.$hsql.'<br>');
	if ($hsql=='') $hsql=0;
	return $hsql;

}

function fget_cb_data_DO_item($xtxt_code, $xprdcode,$xtxtTgl1,$xtxtTgl2, $xopening){
	global $db, $deduct_prod_item,$opnm;
	$qsql=
		"SELECT sum(b.qty) as vqty FROM rrdistrd b,rrdistrh c
		WHERE b.trrrcd=c.trrrcd
		AND trim(b.prdcd)='$xprdcode' AND c.status='1' ";

		if ($xtxt_code=='WEST' || $xtxt_code=='EAST') {
		$qsql.=" and c.code in (
				select msms_new.br_code
					from msms_new where msms_new.br_region ='$xtxt_code'
			) ";
	}elseif ($xtxt_code=='ALL') {
			$qsql.=" and c.code in (select br_code from msms_new,users_braccess  where msms_new.br_code=users_braccess.brcode and br_status=true and br_deleted=false and users_braccess.uname='$opnm') ";
	}else{
		$qsql.=" and c.code='$xtxt_code' ";
	}

	if ($xopening==1) {
		$qsql.=" and c.trdt<'$xtxtTgl1' ";
	}else if ($xopening==11) {
		$qsql.=" and c.trdt<='$xtxtTgl1' ";
	}else if ($xopening==12) {
		$qsql.=" and c.trdt<='$xtxtTgl2' ";
	}else{
		$qsql.=" and c.trdt>='$xtxtTgl1' and c.trdt<='$xtxtTgl2' ";
	}

	if ($deduct_prod_item==1) {/*
	$qsql.=
		"union select sum(b.qty*d.inv_qty) as vqty
		from rrdistrd b, rrdistrh c, msprd_items d
		where b.trrrcd=c.trrrcd  AND c.status='1'
		and b.prdcd in
		(select a.prdcd from msprd_items a, msprd xx
		where a.prdcd=xx.prdcd and  a.inv_prdcd='$xprdcode' and a.prdcd<>'$xprdcode'
		and a.is_normal=1 and xx.type=2)
		and d.prdcd=b.prdcd and d.inv_prdcd='$xprdcode' ";

		if ($xtxt_code=='WEST' || $xtxt_code=='EAST') {
		$qsql.=" and c.code in (
				select msms_new.br_code
					from msms_new where msms_new.br_region ='$xtxt_code'
			) ";
		}elseif ($xtxt_code=='ALL') {
				$qsql.=" and c.code in (select br_code from msms_new,users_braccess  where msms_new.br_code=users_braccess.brcode and br_status=true and br_deleted=false and users_braccess.uname='$opnm')  ";
		}else{
			$qsql.=" and c.code='$xtxt_code' ";
		}

		if ($xopening==1) {
			$qsql.=" and c.trdt<'$xtxtTgl1' ";
		}else if ($xopening==11) {
			$qsql.=" and c.trdt<='$xtxtTgl1' ";
		}else if ($xopening==12) {
			$qsql.=" and c.trdt<='$xtxtTgl2' ";
		}else{
			$qsql.=" and c.trdt>='$xtxtTgl1' and c.trdt<='$xtxtTgl2' ";
		} */
	}
	$sql1 = "select sum(vqty) from ($qsql)  as table1 " ;

	//if ($opnm=='hima' && $xtxt_code=='000000001' && $xprdcode=='FB001' && $xopening==12) echo "$sql1<br>";
	$rsql1=pg_query($db,$sql1);
	$hsql	= 0;
	if(pg_num_rows($rsql1)>0) {
		$rowsql=pg_fetch_row($rsql1,0);
		$hsql=$rowsql[0];
		pg_free_result($rsql1);
	}

	if ($hsql=='') $hsql=0;
	return $hsql;
	//if ('FB001'==$xprdcode) echo($qsql.'-'.$hsql.'<br>');
}

function fget_dn_data_item($xtxt_code, $xprdcode,$xtxtTgl1,$xtxtTgl2, $xopening){
	global $db, $deduct_prod_item,$opnm;

	$qsql = "select sum(b.qty) as vqty from newmsdndrtrh a, newmsdndrtrd b where trim(b.prdcd)='$xprdcode' and a.trcd=b.trcd and a.trtype='1'";

	if ($xtxt_code=='WEST' || $xtxt_code=='EAST') {
		$qsql.=" and a.loccd in (select msms_new.br_code from msms_new where msms_new.br_region ='$xtxt_code') ";
	} elseif ($xtxt_code=='ALL') {
		$qsql.=" and a.loccd in (select br_code from msms_new,users_braccess  where msms_new.br_code=users_braccess.brcode and br_status=true and br_deleted=false and users_braccess.uname='$opnm') ";
	} else {
		$qsql.=" and a.loccd='$xtxt_code' ";
	}

	if ($xopening==1) {
		$qsql.=" and a.trdt<'$xtxtTgl1' ";
	} else if ($xopening==11) {
		$qsql.=" and a.trdt<='$xtxtTgl1' ";
	} else if ($xopening==12) {
		$qsql.=" and a.trdt<='$xtxtTgl2' ";
	} else{
		$qsql.=" and a.trdt>='$xtxtTgl1' and a.trdt<='$xtxtTgl2' ";
	}

	$sql1 = "select sum(vqty) from ($qsql)  as table1 " ;
	$rsql=pg_query($db,$sql1);
	
	$hsql= 0;
	if(pg_num_rows($rsql)>0) {
		$rowsql=pg_fetch_row($rsql,0);
		$hsql=$rowsql[0];
		pg_free_result($rsql);
	}
	if ($hsql=='') $hsql=0;
	return $hsql;
}

function fget_dn_sales_data_item($xtxt_code, $xprdcode,$xtxtTgl1,$xtxtTgl2, $xopening){
	global $db, $deduct_prod_item,$opnm;

	$qsql = "select sum(b.dsqty) as vqty from newmsdndrtrh a, newmsdndrtrd b where trim(b.prdcd)='$xprdcode' and a.trcd=b.trcd ";

	if ($xtxt_code=='WEST' || $xtxt_code=='EAST') {
		$qsql.=" and a.loccd in (select msms_new.br_code from msms_new where msms_new.br_region ='$xtxt_code') ";
	} elseif ($xtxt_code=='ALL') {
		$qsql.=" and a.loccd in (select br_code from msms_new,users_braccess  where msms_new.br_code=users_braccess.brcode and br_status=true and br_deleted=false and users_braccess.uname='$opnm') ";
	} else {
		$qsql.=" and a.loccd='$xtxt_code' ";
	}

	if ($xopening==1) {
		$qsql.=" and a.trdt<'$xtxtTgl1' ";
	} else if ($xopening==11) {
		$qsql.=" and a.trdt<='$xtxtTgl1' ";
	} else if ($xopening==12) {
		$qsql.=" and a.trdt<='$xtxtTgl2' ";
	} else{
		$qsql.=" and a.trdt>='$xtxtTgl1' and a.trdt<='$xtxtTgl2' ";
	}

	$sql1 = "select sum(vqty) from ($qsql)  as table1 " ;
	$rsql=pg_query($db,$sql1);

	$hsql= 0;
	if(pg_num_rows($rsql)>0) {
		$rowsql=pg_fetch_row($rsql,0);
		$hsql=$rowsql[0];
		pg_free_result($rsql);
	}
	if ($hsql=='') $hsql=0;
	return $hsql;
}

function fget_dn_return_data_item($xtxt_code, $xprdcode,$xtxtTgl1,$xtxtTgl2, $xopening){
	global $db, $deduct_prod_item,$opnm;

	$qsql = "select sum(b.qty) as vqty from newmsdndrtrh a, newmsdndrtrd b where trim(b.prdcd)='$xprdcode' and a.trcd=b.trcd and a.trtype='2'";

	if ($xtxt_code=='WEST' || $xtxt_code=='EAST') {
		$qsql.=" and a.loccd in (select msms_new.br_code from msms_new where msms_new.br_region ='$xtxt_code') ";
	} elseif ($xtxt_code=='ALL') {
		$qsql.=" and a.loccd in (select br_code from msms_new,users_braccess  where msms_new.br_code=users_braccess.brcode and br_status=true and br_deleted=false and users_braccess.uname='$opnm') ";
	} else {
		$qsql.=" and a.loccd='$xtxt_code' ";
	}

	if ($xopening==1) {
		$qsql.=" and a.trdt<'$xtxtTgl1' ";
	} else if ($xopening==11) {
		$qsql.=" and a.trdt<='$xtxtTgl1' ";
	} else if ($xopening==12) {
		$qsql.=" and a.trdt<='$xtxtTgl2' ";
	} else{
		$qsql.=" and a.trdt>='$xtxtTgl1' and a.trdt<='$xtxtTgl2' ";
	}

	$sql1 = "select sum(vqty) from ($qsql)  as table1 " ;
	$rsql=pg_query($db,$sql1);

	$hsql= 0;
	if(pg_num_rows($rsql)>0) {
		$rowsql=pg_fetch_row($rsql,0);
		$hsql=$rowsql[0];
		pg_free_result($rsql);
	}
	if ($hsql=='') $hsql=0;
	return $hsql;
}

function fget_cb_data_ADJ_item($xtxt_code, $xprdcode,$xtxtTgl1,$xtxtTgl2, $xopening){
	global $db, $deduct_prod_item,$opnm;

	$qsql="SELECT sum(b.qty) as vqty
		FROM admstrh a, admstrd b
		WHERE trim(b.prdcd)='$xprdcode'
		AND a.trcd=b.trcd ";

		if ($xtxt_code=='WEST' || $xtxt_code=='EAST') {
		$qsql.=" and a.loccd in (
				select msms_new.br_code
					from msms_new where msms_new.br_region ='$xtxt_code'
			) ";
		}elseif ($xtxt_code=='ALL') {
			$qsql.=" and a.loccd in (select br_code from msms_new,users_braccess  where msms_new.br_code=users_braccess.brcode and br_status=true and br_deleted=false and users_braccess.uname='$opnm') ";
		}else{
			$qsql.=" and a.loccd='$xtxt_code' ";
		}

	if ($xopening==1) {
		$qsql.=" and a.trdt<'$xtxtTgl1' ";
	}else if ($xopening==11) {
		$qsql.=" and a.trdt<='$xtxtTgl1' ";
	}else if ($xopening==12) {
		$qsql.=" and a.trdt<='$xtxtTgl2' ";
	}else{
		$qsql.=" and a.trdt>='$xtxtTgl1' and a.trdt<='$xtxtTgl2' ";
	}

/*	if ($deduct_prod_item==1) {
	$qsql.=
		"union select sum(b.qty*d.inv_qty) as vqty
		from admstrd b, admstrh a, msprd_items d
		where  a.loccd='$xtxt_code' and  a.trcd=b.trcd
		and b.prdcd in
		(select prdcd from msprd_items where inv_prdcd='$xprdcode' and prdcd<>'$xprdcode' and is_normal=1)
		and d.prdcd=b.prdcd and d.inv_prdcd='$xprdcode' ";

		if ($xopening==1) {
			$qsql.=" and a.trdt<'$xtxtTgl1' ";
		}else if ($xopening==11) {
			$qsql.=" and a.trdt<='$xtxtTgl1' ";
		}else{
			$qsql.=" and a.trdt>='$xtxtTgl1' and a.trdt<='$xtxtTgl2' ";
		}
	}*/
	$sql1 = "select sum(vqty) from ($qsql)  as table1 " ;
	$rsql=pg_query($db,$sql1);

	$hsql	= 0;
	if(pg_num_rows($rsql)>0) {
		$rowsql=pg_fetch_row($rsql,0);
		$hsql=$rowsql[0];
		pg_free_result($rsql);
	}
	if ($hsql=='') $hsql=0;
	return $hsql;
	//if ('FB001'==$xprdcode) echo($qsql.'-'.$hsql.'<br>');

}

function fget_dd_data_DO_item($xtxt_code, $xprdcode,$xtxtTgl1,$xtxtTgl2, $xopening, $xdotype){
	// DO out trtype=2, DO in trtype=3
	global $db, $debug, $deduct_prod_item;
	if ($xdotype=='in') {
		$qsql = "select sum(b.qty) as vqty from dodistrh a, dodistrd b
			where a.trdocd=b.trdocd and a.trtype='3' and a.code='$xtxt_code' and trim(b.prdcd)='$xprdcode' ";
		if ($xopening==1) {
			$qsql.=" and a.trdt<'$xtxtTgl1' ";
		}else if ($xopening==11) {
			$qsql.=" and a.trdt<='$xtxtTgl1' ";
		}else if ($xopening==12) {
			$qsql.=" and a.trdt<='$xtxtTgl2' ";
		} else {
			$qsql.=" and a.trdt>='$xtxtTgl1' and a.trdt<='$xtxtTgl2' ";
		}

	}elseif ($xdotype=='out') {
		$qsql = "select sum(b.qty) as vqty from dodistrh a, dodistrd b
			where a.trdocd=b.trdocd and a.trtype='2' and a.loccd='$xtxt_code' and trim(b.prdcd)='$xprdcode' ";
		if ($xopening==1) {
			$qsql.=" and trdt<'$xtxtTgl1' ";
		}else if ($xopening==11) {
			$qsql.=" and trdt<='$xtxtTgl1' ";
		}else if ($xopening==12) {
			$qsql.=" and trdt<='$xtxtTgl2' ";
		} else {
			$qsql.=" and trdt>='$xtxtTgl1' and trdt<='$xtxtTgl2' ";
		}

	}

	$sql1 = "select sum(vqty) from ($qsql)  as table1 " ;
	$rsql=pg_query($db,$sql1);
	$hsql	= 0;
	if(pg_num_rows($rsql)>0) {
		$rowsql=pg_fetch_row($rsql,0);
		$hsql=$rowsql[0];
		if ($hsql=='') $hsql=0;
		pg_free_result($rsql);
	}
	return $hsql;

}

function fget_dd_data_ADJ_item($xtxt_code, $xprdcode,$xtxtTgl1,$xtxtTgl2, $xopening,$xadjtype){
	global $db, $debug, $deduct_prod_item;
	$qsql=
		"SELECT sum(b.qty) as vqty FROM addotrh a, addotrd b
		WHERE a.loccd='$xtxt_code' AND trim(b.prdcd)='$xprdcode'
		AND a.trcd=b.trcd ";
	if ($xadjtype=='in') {
		$qsql.=" and b.qty>0 ";
	}elseif ($xadjtype=='out'){
		$qsql.=" and b.qty<0 ";
	}
	if ($xopening==1) {
		$qsql.=" and a.trdt<'$xtxtTgl1' ";
	}else if ($xopening==11) {
		$qsql.=" and a.trdt<='$xtxtTgl1' ";
	}else if ($xopening==12) {
		$qsql.=" and a.trdt<='$xtxtTgl2' ";
	} else {
		$qsql.=" and a.trdt>='$xtxtTgl1' and a.trdt<='$xtxtTgl2' ";
	}

	$sql1 = "select sum(vqty) from ($qsql)  as table1 " ;
	$rsql=pg_query($db,$sql1);
	$hsql	= 0;
	if(pg_num_rows($rsql)>0) {
		$rowsql=pg_fetch_row($rsql,0);
		$hsql=$rowsql[0];
		if ($hsql=='') $hsql=0;
		pg_free_result($rsql);
	}
	if ($xadjtype=='out') $hsql=-$hsql;
	return $hsql;

}

function fget_dd_data_GRN_item($xtxt_code, $xprdcode,$xtxtTgl1,$xtxtTgl2, $xopening, $xflag){
	global $db, $opnm, $deduct_prod_item;
	$qsql=
		"SELECT sum(b.qty) as vqty
		FROM grnmstrd b, grnmstrh a
		WHERE
			b.trcd=a.trcd and b.prdcd='$xprdcode'
			and a.code='$xtxt_code' ";

	if ($xflag=='out') {
		if ($xopening==1) {
			$qsql.=" and a.trdt<'$xtxtTgl1'  ";
		}else if ($xopening==11) {
			$qsql.=" and a.trdt<='$xtxtTgl1' ";
		}else if ($xopening==12) {
			$qsql.=" and a.trdt<='$xtxtTgl2' ";
		} else {
			$qsql.=" and a.trdt>='$xtxtTgl1' and a.trdt<='$xtxtTgl2' ";
		}

		$sql1 = "select sum(vqty) from ($qsql)  as table1 " ;
		$rsql=pg_query($db,$sql1);
		$hsql	= 0;
		if(pg_num_rows($rsql)>0) {
			$rowsql=pg_fetch_row($rsql,0);
			$hsql=$rowsql[0];
			if ($hsql=='') $hsql=0;
			pg_free_result($rsql);
		}
	} else {
		$hsql=0;
	}
	return $hsql;

}

function fget_dd_data_INV_item($xtxt_code, $xprdcode,$xtxtTgl1,$xtxtTgl2, $xopening, $xflag='in#out'){
	global $db, $deduct_prod_item;
	$qsql=
		"SELECT sum(b.qty) as vqty FROM newmsivtrd b, newmsivtrh a
		WHERE b.trivcd=a.trivcd and b.prdcd='$xprdcode' and a.loccd='$xtxt_code' and a.trtype='1'";

	if ($xflag=='in') {
		$qsql.="and b.qty<0 ";
	}elseif ($xflag=='out') {
		$qsql.="and b.qty>=0 ";
	}
	if ($xopening==1) {
		$qsql.=" and a.trdt<'$xtxtTgl1' ";
	}else if ($xopening==11) {
		$qsql.=" and a.trdt<='$xtxtTgl1' ";
	}else if ($xopening==12) {
		$qsql.=" and a.trdt<='$xtxtTgl2' ";
	} else {
		$qsql.=" and a.trdt>='$xtxtTgl1' and a.trdt<='$xtxtTgl2' ";
	}

	$sql1 = "select sum(vqty) from ($qsql)  as table1 " ;
	$rsql=pg_query($db,$sql1);
	$hsql	= 0;
	if(pg_num_rows($rsql)>0) {
		$rowsql=pg_fetch_row($rsql,0);
		$hsql=$rowsql[0];
		if ($hsql=='') $hsql=0;
		pg_free_result($rsql);
	}
	return $hsql;
}

function fget_dd_data_CR_item($xtxt_code, $xprdcode,$xtxtTgl1,$xtxtTgl2, $xopening){
	global $db, $deduct_prod_item;
	$qsql=
		"SELECT sum(b.qty) as vqty FROM newddtrd b, newddtrh a
		WHERE b.trcd=a.trcd and b.prdcd='$xprdcode' and a.loccd='$xtxt_code' ";

	if ($xopening==1) {
		$qsql.=" and a.trdt<'$xtxtTgl1' ";
	}else if ($xopening==11) {
		$qsql.=" and a.trdt<='$xtxtTgl1' ";
	}else if ($xopening==12) {
		$qsql.=" and a.trdt<='$xtxtTgl2' ";
	} else {
		$qsql.=" and a.trdt>='$xtxtTgl1' and a.trdt<='$xtxtTgl2' ";
	}

	$sql1 = "select sum(vqty) from ($qsql)  as table1 " ;
	$rsql=pg_query($db,$sql1);
	$hsql	= 0;
	if(pg_num_rows($rsql)>0) {
		$rowsql=pg_fetch_row($rsql,0);
		$hsql=$rowsql[0];
		if ($hsql=='') $hsql=0;
		pg_free_result($rsql);
	}
	return $hsql;

}

function fget_dd_data_FOC_item($xtxt_code, $xprdcode,$xtxtTgl1,$xtxtTgl2, $xopening){
	global $db, $deduct_prod_item;
	$qsql=
		"SELECT sum(b.qty) as vqty
		FROM focddtrd b, focddtrh a
		WHERE
			b.trcd=a.trcd and b.prdcd='$xprdcode' and a.loccd='$xtxt_code' ";

	if ($xopening==1) {
		$qsql.=" and a.trdt<'$xtxtTgl1' ";
	}else if ($xopening==11) {
		$qsql.=" and a.trdt<='$xtxtTgl1' ";
	}else if ($xopening==12) {
		$qsql.=" and a.trdt<='$xtxtTgl2' ";
	} else {
		$qsql.=" and a.trdt>='$xtxtTgl1' and a.trdt<='$xtxtTgl2' ";
	}

// 	if ($deduct_prod_item==1) {
// 		$qsql.=
// 			"union select sum(b.qty*d.inv_qty) as vqty
//  			from focddtrh a, focddtrd b, msprd_items d
// 			where a.trcd=b.trcd and a.loccd='$xtxt_code'
// 			and b.prdcd in
// 			(select a.prdcd from msprd_items a, msprd xx
// 			where a.prdcd=xx.prdcd and  a.inv_prdcd='$xprdcode' and a.prdcd<>'$xprdcode'
// 			and a.is_normal=1 and xx.type=2)
//
// 			and d.prdcd=b.prdcd and d.inv_prdcd='$xprdcode' ";
//
// 		if ($xopening==1) {
// 			$qsql.=" and a.trdt<'$xtxtTgl1' ";
// 		}else if ($xopening==11) {
// 			$qsql.=" and a.trdt<='$xtxtTgl1' ";
// 		}else if ($xopening==12) {
// 			$qsql.=" and a.trdt<='$xtxtTgl2' ";
// 		} else {
// 			$qsql.=" and a.trdt>='$xtxtTgl1' and a.trdt<='$xtxtTgl2' ";
// 		}
// 	}

	$sql1 = "select sum(vqty) from ($qsql)  as table1 " ;
	$rsql=pg_query($db,$sql1);
	$hsql	= 0;
	if(pg_num_rows($rsql)>0) {
		$rowsql=pg_fetch_row($rsql,0);
		$hsql=$rowsql[0];
		if ($hsql=='') $hsql=0;
		pg_free_result($rsql);
	}

	return $hsql;
	//if ('FB001'==$xprdcode) echo($qsql.'-'.$hsql.'<br>');
}

function fget_ms_transfer_item($xtxt_code, $xprdcode,$xtxtTgl1,$xtxtTgl2, $xopening, $xflag='in#out'){
	global $db, $deduct_prod_item,$opnm;

	$qsql="SELECT sum(b.qty) as vqty FROM brtranstrh a, brtranstrd b
		WHERE trim(b.prdcd)='$xprdcode' AND a.trbrcd=b.trbrcd ";

	if ($xtxt_code=="ALL") {
		if ($xflag=='in')
			$qsql.=" and a.br_to in (select br_code from msms_new,users_braccess where msms_new.br_code=users_braccess.brcode and br_status=true and br_deleted=false and users_braccess.uname='$opnm') ";
		else
			$qsql.=" and a.br_from in (select br_code from msms_new,users_braccess where msms_new.br_code=users_braccess.brcode and br_status=true and br_deleted=false and users_braccess.uname='$opnm') ";
	}
	else {
		if ($xflag=='in')
			$qsql.=" and a.br_to='$xtxt_code'";
		else
			$qsql.=" and a.br_from='$xtxt_code' ";
	}
	
	if ($xopening==1) {
		$qsql.=" and a.trdt<'$xtxtTgl1' ";
	}else if ($xopening==11) {
		$qsql.=" and a.trdt<='$xtxtTgl1' ";
	}else{
		$qsql.=" and a.trdt>='$xtxtTgl1' and a.trdt<='$xtxtTgl2' ";
	}
	
	$sql1 = "select sum(vqty) from ($qsql)  as table1 " ;

	$rsql=pg_query($db,$sql1);

	$hsql	= 0;
	if(pg_num_rows($rsql)>0) {
		$rowsql=pg_fetch_row($rsql,0);
		$hsql=$rowsql[0];
		pg_free_result($rsql);
	}
	if ($hsql=='') $hsql=0;
	return $hsql;
}

function fpg_exec($xsql) {
	global $db, $debug, $opnm;
	$xres1=pg_exec($db, $xsql);
	if (!$xres1 && $debug) echo "error fpg_exec=".$xsql."<br>";
	if (pg_num_rows($xres1)>0)
		$xrow1=pg_fetch_row($xres1, 0);
	pg_free_result($xres1);
	return $xrow1;
}

function checkLocalname($xvalset) {
	$localname="false";
	if(substr($xvalset,3,1)=='Y')$localname="true";
	return $localname;
}

function get_memname_local($memidx) {
	global $db, $debug;
	$memrealname = "-";
	$querr="select b.loc_name,mid_locname,last_locname from msmemb a,msmemb_extra b where a.code='$memidx' and b.code=a.code;";
	$result=pg_exec($db,$querr);
	if (pg_num_rows($result)>0) {
		$rowname=pg_fetch_row($result,0);
		$memrealname = strtoupper("$rowname[0]");
	} else {
		$querr="select name from users where username='$memidx';";
		$result=pg_exec($db,$querr);
		if (pg_num_rows($result)>0) {
			$rowname=pg_fetch_row($result,0);
			$memrealname = strtoupper("$rowname[0] $rowname[1] $rowname[2]");
		} else $memrealname = "-";
	}
	pg_free_result($result);
	if ($debug) echo "$memrealname=get_memname_local($memidx);<br/>\n";
	return trim(stripslashes($memrealname));
}

function sel_stockist($xstr_name,$xobj_name, $xtype=2) {
	// 0 --> main stockist
	// 1 --> stockist
	// 2 --> main or stockist
	// 3 --> display --All-- option
	global $db,$priv,$opnm,$debug;
	$xsql="select sccode from users_extra where uname='$opnm'";
	$xres=pg_exec($db, $xsql);
	if (pg_num_rows($xres)>0) $xrow=pg_fetch_row($xres,0);
	if ($xrow[0]=='1') {
		echo "<select name='$xstr_name'> \n";
	  if ($xtype==3) echo "<option value='x'>--".mxlang("934")."--</option>\n";
		$xsql_st ="select a.code,a.sub_name,c.name ".
			"from sub_mssc a ".
			"left join msmemb c on c.code=a.code ".
			", sub_mssc_extra b ".
			"where b.scname=a.code ";
		if ($xtype==0 || $xtype==1) $xsql_st.="and b.stockist=$xtype ";
		$xsql_st.="order by a.sub_name";
		$xres_st=pg_exec($db, $xsql_st);
		for ($i=0;$i<pg_num_rows($xres_st);$i++) {
			$xrow_st=pg_fetch_row($xres_st, $i);
			$xsel = ($xobj_name==$xrow_st[0])?'selected':'';
			echo "<option value='$xrow_st[0]' $xsel>$xrow_st[0] ($xrow_st[2])</option>\n";
		}
		echo "</select> \n";
	}else{
		echo "<input type='text' name='$xstr_name' size='16' maxlength='9' value='$xobj_name'>\n";
	}
	pg_free_result($xres);
}

	function getCovertedx($curr,$vcramt){
		global $db,$debug,$xcurrency_set,$xcurrency_code,$cursign;
		$xreturn=1.00;
		$xsql1 = "
		select fexcurr, fcurrcode, fexrate from
		(select a.fexcurr,a.fcurrcode, b.fexrate, b.feffdate
		from texrate a, texrate_log b
		where a.fcurrset='$xcurrency_set' and a.fcurrcode='$xcurrency_code'
		and a.fexcurr='$curr' and a.factive='A'
		and a.fid=b.fid
		union
		select a.fexcurr,a.fcurrcode,a.fexrate, a.feffdate
		from texrate a
		where a.fcurrset='$xcurrency_set' and a.fcurrcode='$xcurrency_code'
		and a.fexcurr='$curr' and a.factive='A'
		) as table1 where feffdate<now() order by feffdate desc limit 1; ";

		$xres1 = pg_exec($db, $xsql1);
		if($debug==1) print "Convert : <b>$xsql1 (".pg_numrows($xres1).")</b> <br>";
		if (pg_numrows($xres1)>0){
			$xrow1 = pg_fetch_row($xres1, 0);
			$xreturn=$xrow1[2];
		}else{
			$xsql1 = "
			select fexcurr, fcurrcode, fexrate from
			(select a.fexcurr,a.fcurrcode, b.fexrate, b.feffdate
			from texrate a, texrate_log b
			where a.fcurrset='$xcurrency_set' and a.fcurrcode='$curr' and a.fexcurr='$xcurrency_code'
			and a.fid=b.fid
			union
			select a.fexcurr,a.fcurrcode,a.fexrate, a.feffdate
			from texrate a
			where a.fcurrset='$xcurrency_set' and a.fcurrcode='$curr' and a.fexcurr='$xcurrency_code'
			) as table1 where feffdate<now(); ";

			$xres1 = pg_exec($db, $xsql1);
			if (pg_num_rows($xres1)>0){
				$xrow1 = pg_fetch_row($xres1, 0);
				$xreturn=1/$xrow1[2];
			}
		}
		return $xreturn;
	}

	function fsave_new_contra($xcontra_type,$xcontra_no,$xcontra_batch,$xcode,$xmsid,$xtrxno) {
		global $db, $debug, $xche_value, $xvou_value, $opnm, $xcurrency_code,
			$xche_foreign,$xvou_foreign,$cursign;
		$xarr_batch = split('[,+]',$xcontra_batch);
		$xarr_cheqno= split('[,+]',$xcontra_no);
		for ($c=0;$c<count($xarr_cheqno);$c++) {
			if ($xcontra_type=='C') {
				$xche_sql ="select fcheqno,fcheqamt,fbatchno,fcurrcode from tcheque ";
				$xche_sql.="where fcheqno='".$xarr_cheqno[$c]."' ";
				if ($xarr_batch[$c]!='') $xche_sql.="and fbatchno='".$xarr_batch[$c]."' ";
				$xche_sql.="and (fstatus='N' or fstatus='C') order by fbatchno desc limit 1;";
				if ($debug) echo "$xche_sql<br>";
				$xche_row = fpg_exec($xche_sql);
				$xexrate=getCovertedx($xche_row[3],$xche_row[1]);
				//$xexrate=getCovertedx($cursign,$xche_row[1]);

				$xpsql = "update tcheque set fstatus='C',factionby='$opnm',factiondate=now(), ";
				$xpsql.= "fcontramem='$xcode', fcontradate=now(), fcontraloc='$xmsid',";
				$xpsql.= "ftrxno='$xtrxno', fcontracurr='$xcurrency_code', fcontraexrate=$xexrate, fconvertedamt=$xche_row[1]*$xexrate ";
				//$xpsql.= "ftrxno='$xtrxno', fcontracurr='$xcurrency_code', fcontraexrate=$xexrate, fconvertedamt=$xche_row[1]/$xexrate ";
				$xpsql.= "where fcheqno='$xarr_cheqno[$c]' ";
				$xpsql.= "and fbatchno='$xche_row[2]';";
				$xche_value+=$xche_row[1]*$xexrate;
				//$xche_value+=$xche_row[1]/$xexrate;
				if ($debug) echo $xpsql.'<br>';
				else $xpres = pg_exec($db, $xpsql);

				$xpsql ="insert into tcheqlog ";
				$xpsql.="(fcheqno,fbatchno,fremark,faction,factionby, ";
				$xpsql.="factiondate,fcontramem,fcontradate,fcontraloc,fcontracurr, ";
				$xpsql.="fcontraexrate,ftrxno,fconvertedamt) ";
				$xpsql.="select ";
				$xpsql.="fcheqno,fbatchno,fremark,fstatus,factionby, ";
				$xpsql.="factiondate,fcontramem,fcontradate,fcontraloc,fcontracurr, ";
				$xpsql.="fcontraexrate,ftrxno,fcheqamt*$xexrate ";
				//$xpsql.="fcontraexrate,ftrxno,fcheqamt/$xexrate ";
				$xpsql.="from tcheque ";
				$xpsql.="where fcheqno='".$xarr_cheqno[$c]."' and fbatchno='".$xche_row[2]."';";
				if ($debug) echo $xpsql.'<br><br>';
				else $xpres = pg_exec($db, $xpsql);
				if ($xcurrency_code!=$xche_row[3] && $xche_foreign==false) $xche_foreign = true;
			}
			if ($xcontra_type=='V') {
				$xvou_sql ="select fvcrno,fvcramt,fbatchno,fcurrcode from tvoucher ";
				$xvou_sql.="where fvcrno='".$xarr_cheqno[$c]."' ";
				if ($xarr_batch[$c]!='') $xvou_sql.="and fbatchno='".$xarr_batch[$c]."' ";
				$xvou_sql.="and (fstatus='N' or fstatus='C') order by fbatchno desc limit 1;";
				if ($debug) echo "$xvou_sql<br>";
				$xvou_row = fpg_exec($xvou_sql);
				//if ($debug) echo "$xvou_row[3]<br>";
				$xexrate=getCovertedx($xvou_row[3],$xvou_row[1]);

				$xpsql = "update tvoucher set fstatus='C',factionby='$opnm',factiondate=now(), ";
				$xpsql.= "fcontramem='$xcode', fcontradate=now(), fcontraloc='$xmsid',";
				$xpsql.= "ftrxno='$xtrxno', fcontracurr='$xcurrency_code', fcontraexrate=$xexrate, fconvertedamt=$xvou_row[1]*$xexrate ";
				$xpsql.= "where fvcrno='$xarr_cheqno[$c]' ";
				$xpsql.= "and fbatchno='$xvou_row[2]';";
				$xvou_value+=$xvou_row[1]*$xexrate;
				if ($debug) echo $xpsql."<br>";
				else $xpres = pg_exec($db, $xpsql);

				$xpsql ="insert into tvcrlog ";
				$xpsql.="(fvcrno,fbatchno,fremark,faction,factionby, ";
				$xpsql.="factiondate,fcontramem,fcontradate,fcontraloc,fcontracurr, ";
				$xpsql.="fcontraexrate,ftrxno,fconvertedamt) ";
				$xpsql.="select ";
				$xpsql.="fvcrno,fbatchno,fremark,fstatus,factionby, ";
				$xpsql.="factiondate,fcontramem,fcontradate,fcontraloc,fcontracurr, ";
				$xpsql.="fcontraexrate,ftrxno,fvcramt*$xexrate ";
				$xpsql.="from tvoucher ";
				$xpsql.="where fvcrno='".$xarr_cheqno[$c]."' and fbatchno='".$xvou_row[2]."';";
				if ($xcurrency_code!=$xvou_row[3] && $xvou_foreign==false) $xvou_foreign = true;
				if ($debug) echo $xpsql."<br><br>";
				else $xpres = pg_exec($db, $xpsql);
			}
		}//end for
	}//end function

function fwh_set_balance($xprdcd, $txtTgl1, $txtTgl2, $xautosave) {
	global $db, $debug;
	$hDOin=fget_dd_data_DO_item('DDEPT',$xprdcd,$txtTgl1,$txtTgl2,11,'in');
	$hADJin=fget_dd_data_ADJ_item('DDEPT',$xprdcd,$txtTgl1,$txtTgl2,11,'in');
	$hGRNin=fget_dd_data_GRN_item('DDEPT',$xprdcd,$txtTgl1,$txtTgl2,11,'in');
	$hINV=fget_dd_data_INV_item('DDEPT',$xprdcd,$txtTgl1,$txtTgl2,11);
	$hDOout=fget_dd_data_DO_item('DDEPT',$xprdcd,$txtTgl1,$txtTgl2,11,'out');
	$hCR=fget_dd_data_CR_item('DDEPT',$xprdcd,$txtTgl1,$txtTgl2,11);
	$hFOC=fget_dd_data_FOC_item('DDEPT',$xprdcd,$txtTgl1,$txtTgl2,11);
	$hADJout=fget_dd_data_ADJ_item('DDEPT',$xprdcd,$txtTgl1,$txtTgl2,11,'out');
	$hGRNout=fget_dd_data_GRN_item('DDEPT',$xprdcd,$txtTgl1,$txtTgl2,11,'out');
	$hStock=($hDOin+$hADJin+$hGRNin)-($hINV+$hDOout+$hCR+$hFOC+$hADJout+$hGRNout);
	if ($xautosave==1) {
		$xsql="update inloc set qoh=$hStock where loccd='DDEPT' and prdcd='$xprdcd'";
		if ($debug) echo $xsql.'<br>';
		pg_exec($db, $xsql);
		//pg_exec();
	}else{
		return $hStock;
	}
}

function selCOUNTRY2($idx,$fldname, $xdata='') {
	global $db,$txt_country, $SCRIPT_NAME, $_POST;
	if($fldname=='') $fldname = "txt_country";
	$svcquerr="select iso,printable_name from country_list order by printable_name ASC;";
	$ressvc=pg_exec($db,$svcquerr);

	print "<select name=\"$fldname\" $xsubmit>\n";
	print "<option value=\"0\">-- ".ucwords(strtolower(mxlang("1496")))." --</option>\n";
	for($ls=0;$ls<pg_num_rows($ressvc);$ls++) {
		$rowls=pg_fetch_row($ressvc,$ls);
		$selc = "";
		$xtxt=(strlen(stripslashes($rowls[1]))>25)?substr(stripslashes($rowls[1]), 0, 25).'...':stripslashes($rowls[1]);
		if ($_POST[$fldname]==$rowls[0] || $xdata==$rowls[0]) $selc = " selected";
		print "<option value=\"$rowls[0]\" $selc>".$xtxt."</option>\n";
	}
	print "</select>\n";
}

/*
function update_bts($lst_brcd, $xloccd, $xtrcd, $xprdcd, $xflag) {
	//xflag = sccb, brcb, msinv, brinv
	global $brcd_no, $debug, $db;
	$brcd_no='';
	//$lst_brcd = $_POST["lst_barcode_".trim($row[0])];
	if (isset($lst_brcd)) {
		reset($lst_brcd);
		if ($debug) echo "<b>BTS</b> <br>";
		while (list($key2,$val2)=each($lst_brcd)){
			$brcd_no.="$val2  ";
			//if ($debug) echo "$key2 $val2<br>";
			list($brcd1,$brcd2)= split ("/", $val2);

			if (trim($brcd2)!='') {
				$brcd_prefix=substr($brcd1, 0, strlen($brcd1)-strlen($brcd2));
				$brcd_begin =substr($brcd1, 0-strlen($brcd2));
				$brcd_end   =$brcd2;
				if ($brcd_begin>$brcd_end) {
					$temp1 			= $brcd_begin;
					$brcd_begin	=	$brcd_end;
					$brcd_end 	= $temp1;
				}
				for ($ibrcd=$brcd_begin;$ibrcd<=$brcd_end;$ibrcd++) {
					$sbrcd=str_pad($ibrcd,strlen($brcd_end),'0',STR_PAD_LEFT);
					update_brcd($xtrcd, $xloccd, $xprdcd, $brcd_prefix.$sbrcd, $xflag);
				}
				//$bts_exist = true;
			}else{
				update_brcd($xtrcd, $xloccd, $xprdcd, $brcd1, $xflag);
				//$bts_exist = true;
			}
		}
		$brcd_no=trim($brcd_no);
		if ($xflag=='sccb')
			$xpsql = "update newsctrd set brcd_no='$brcd_no' where trcd='$xtrcd' and prdcd='$xprdcd';";
		elseif ($xflag=='sccr')
			$xpsql = "update newsctrd set brcd_no='$brcd_no' where trcd='$xtrcd' and prdcd='$xprdcd';";
		elseif ($xflag=='brcb')
			$xpsql = "update newmstrd set brcd_no='$brcd_no' where trcd='$xtrcd' and prdcd='$xprdcd';";
		elseif ($xflag=='brcr')
			$xpsql = "update newmstrd set brcd_no='$brcd_no' where trcd='$xtrcd' and prdcd='$xprdcd';";
		elseif ($xflag=='msinv' || $xflag=='brinv')
			$xpsql = "update newmsivtrd set brcd_no='$brcd_no' where trivcd='$xtrcd' and prdcd='$xprdcd';";
		if ($debug) echo $xpsql.'<br>';
		if ($xpsql!='') pg_exec($db, $xpsql);

	}
}
*/
function update_bts($lst_brcd, $xloccd, $xtrcd, $xprdcd, $xflag) {
}
function update_bts2($lst_brcd, $xloccd, $xtrcd, $xprdcd, $xflag, $xcust) {
	//xflag = sccb, brcb, msinv, brinv
	$xcust=addslashes($xcust);
	global $brcd_no, $debug, $db;
	$brcd_no='';
	//$lst_brcd = $_POST["lst_barcode_".trim($row[0])];
	if (isset($lst_brcd)) {
		reset($lst_brcd);
		if ($debug) echo "<b>BTS</b> <br>";
		while (list($key2,$val2)=each($lst_brcd)){
			$brcd_no.="$val2  ";
			//if ($debug) echo "$key2 $val2<br>";
			list($brcd1,$brcd2)= split ("/", $val2);

			if (trim($brcd2)!='') {
				$brcd_prefix=substr($brcd1, 0, strlen($brcd1)-strlen($brcd2));
				$brcd_begin =substr($brcd1, 0-strlen($brcd2));
				$brcd_end   =$brcd2;
				if ($brcd_begin>$brcd_end) {
					$temp1 			= $brcd_begin;
					$brcd_begin	=	$brcd_end;
					$brcd_end 	= $temp1;
				}
				for ($ibrcd=$brcd_begin;$ibrcd<=$brcd_end;$ibrcd++) {
					$sbrcd=str_pad($ibrcd,strlen($brcd_end),'0',STR_PAD_LEFT);
					update_brcd($xtrcd, $xloccd, $xprdcd, $brcd_prefix.$sbrcd, $xflag, $xcust);
					update_brcd_log($xtrcd,$xloccd,$xprdcd,$brcd_prefix.$sbrcd,$xflag, $xcust);
				}
				//$bts_exist = true;
			}else{
				update_brcd($xtrcd, $xloccd, $xprdcd, $brcd1, $xflag, $xcust);
				update_brcd_log($xtrcd,$xloccd,$xprdcd,$brcd1,$xflag, $xcust);
				//$bts_exist = true;
			}

		}
		$brcd_no=trim($brcd_no);
		if ($brcd_no!='') {
			if ($xflag=='doin')
				$xpsql = "update dodistrd set brcd_no='$brcd_no' where trdocd 	='$xtrcd' and prdcd='$xprdcd';";
			elseif ($xflag=='doc')
				$xpsql = "update dodistrd set brcd_no='$brcd_no' where trdocd 	='$xtrcd' and prdcd='$xprdcd';";
			elseif ($xflag=='wrcr')
				$xpsql = "update newddtrd set brcd_no='$brcd_no' where trcd 	='$xtrcd' and prdcd='$xprdcd';";
			elseif ($xflag=='wrfoc')
				$xpsql = "update focddtrd set brcd_no='$brcd_no' where trcd 	='$xtrcd' and prdcd='$xprdcd';";
			elseif ($xflag=='wrgrn')
				$xpsql = "update grnmstrd set brcd_no='$brcd_no' where trcd 	='$xtrcd' and prdcd='$xprdcd';";
			elseif ($xflag=='whinv')
				$xpsql = "update newmsivtrd set brcd_no='$brcd_no' where trivcd 	='$xtrcd' and prdcd='$xprdcd';";
			elseif ($xflag=='brcb' || $xflag=='brcr')
				$xpsql = "update newmstrd set brcd_no='$brcd_no' where trcd 	='$xtrcd' and prdcd='$xprdcd';";
			elseif ($xflag=='sccb')
				$xpsql = "update newsctrd set brcd_no='$brcd_no' where trcd 	='$xtrcd' and prdcd='$xprdcd';";
			elseif ($xflag=='brinv')
				$xpsql = "update newmsivtrd set brcd_no='$brcd_no' where trivcd 	='$xtrcd' and prdcd='$xprdcd';";
			elseif ($xflag=='msinv')
				$xpsql = "update newmsivtrd set brcd_no='$brcd_no' where trivcd 	='$xtrcd' and prdcd='$xprdcd';";
			if ($debug) echo '<br><b>'.$xpsql.'</b><br>';
			if (trim($xpsql)!='')
				pg_exec($db, $xpsql);
		}
	}
}

function update_bts3($xloccd, $xtrcd,$xprdcd, $xflag, $xcust, $xtmp_trcd, $xopnm, $xqty=1) {
	global $db, $debug;
	$xbrcd_sql ="select batch_no,ctrn_no,brcd_no from tmp_brcd ";
	$xbrcd_sql.="where xuser='$xopnm' and trcd='$xtmp_trcd' and prdcd='$xprdcd' and saved='0' ";
	$xbrcd_sql.="order by brcd_no ";
	if ($debug) echo "<br>".$xbrcd_sql."*<br>";
	$xbrcd_res=pg_exec($db, $xbrcd_sql);
	if (pg_num_rows($xbrcd_res)>0) {
		for ($i=0; $i<pg_num_rows($xbrcd_res); $i++) {
			$xbrcd_row=pg_fetch_row($xbrcd_res, $i);
			//echo $xbrcd_row[2]."<br>";
			//echo "update_brcd3($xtrcd, $xloccd, $xprdcd, $xbrcd_row[2], $xflag, $xcust, $xbrcd_row[0], $xbrcd_row[1])<br>";
			update_brcd3($xtrcd, $xloccd, $xprdcd, $xbrcd_row[2], $xflag, $xcust, $xbrcd_row[0], $xbrcd_row[1], $xqty);
		}
	}
	$xpsql ="update tmp_brcd set saved='1' ";
	$xpsql.="where xuser='$xopnm' and trcd='$xtmp_trcd' and prdcd='$xprdcd'";
	if ($debug) echo "$xpsql*<br>";
	pg_exec($db, $xpsql);
	pg_free_result($xbrcd_res);
}

function update_brcd3($xtrcd,$xloccd,$xprdcd,$xbrcdno,$xflag,$xcust,$xbatchno,$xctrnno,$xqty) {
	global $db, $debug,$opnm;
	$xbrcd_sql2 ="select batch_no,ctrn_no,brcd_no from msprd_brcd ";
	$xbrcd_sql2.="where prdcd='$xprdcd' and batch_no='$xbatchno' ";
	$xbrcd_sql2.="and ctrn_no='$xctrnno' and brcd_no='$xbrcdno' ";
	if ($debug) echo "<br>".$xbrcd_sql2."**<br>";
	$xbrcd_res2=pg_exec($db, $xbrcd_sql2);
	if (pg_num_rows($xbrcd_res2)>0) {
		$sqlexe =
			"update msprd_brcd
			set last_trcd='$xtrcd',last_flag='$xflag', ";

		if ($xqty>0)	$sqlexe.= "last_loc='$xloccd', last_cust='$xcust' ";
		else $sqlexe.= "last_loc='$xcust', last_cust='$xloccd' ";

		$sqlexe.= ",fcreated_by='$opnm', fcreated_date=now(), batch_no='$xbatchno', ctrn_no='$xctrnno' ";
		$sqlexe.=" where batch_no='$xbatchno' and ctrn_no='$xctrnno' and brcd_no='$xbrcdno'";
	}else{
		if ($xqty>0)
			$sqlexe=
			"insert into msprd_brcd
			(brcd_no,prdcd,last_loc,last_trcd,last_flag,last_cust,fcreated_by,fcreated_date,batch_no,ctrn_no)
			values
			('$xbrcdno','$xprdcd','$xloccd','$xtrcd','$xflag','$xcust','$opnm',now(),'$xbatchno','$xctrnno');";
		else
			$sqlexe=
			"insert into msprd_brcd
			(brcd_no,prdcd,last_loc,last_trcd,last_flag,last_cust,fcreated_by,fcreated_date,batch_no,ctrn_no)
			values
			('$xbrcdno','$xprdcd','$xcust','$xtrcd','$xflag','$xloccd','$opnm',now(),'$xbatchno','$xctrnno');";

	}
	if($debug) echo "$sqlexe<br>";
	pg_exec($db, $sqlexe);

	if ($xqty>0)
		$sqllog=
			"insert into msprd_brcd_log
			(trtype,trcd,loccd,prdcd,brcd_no,log_date,cust,fcreated_by,fcreated_date,batch_no,ctrn_no)
			values
			('$xflag','$xtrcd','$xloccd','$xprdcd','$xbrcdno',now(),'$xcust','$opnm',now(),'$xbatchno','$xctrnno');";
	else
		$sqllog=
			"insert into msprd_brcd_log
			(trtype,trcd,loccd,prdcd,brcd_no,log_date,cust,fcreated_by,fcreated_date,batch_no,ctrn_no)
			values
			('$xflag','$xtrcd','$xcust','$xprdcd','$xbrcdno',now(),'$xloccd','$opnm',now(),'$xbatchno','$xctrnno');";
	if ($debug) echo $sqllog.'<br>';
	pg_exec($db, $sqllog);

	//echo "$xbrcd_sql2<br>";
	pg_free_result($xbrcd_res2);
}

function update_brcd($xtrcd,$xloccd,$xprdcd,$xbrcd,$xflag,$xcust) {
	//xflag = sccb, brcb, msinv, brinv
	global $db, $debug, $opnm;
	if ($xbrcd!='') {
		$sql1 =
			"select brcd_no from msprd_brcd where brcd_no='$xbrcd'";
		$res1 = pg_exec($db, $sql1);
		if (pg_num_rows($res1)>0) { //check exist
			$sqlexe =
				"update msprd_brcd
				set last_loc='$xloccd',last_trcd='$xtrcd',last_flag='$xflag',last_cust='$xcust' ";
			$sqlexe.= ",fcreated_by='$opnm', fcreated_date=now() ";
			$sqlexe.=" where brcd_no='$xbrcd'";
		}else{
			$sqlexe=
			"insert into msprd_brcd
			(brcd_no,prdcd,last_loc,last_trcd,last_flag,last_cust,fcreated_by,fcreated_date)
			values
			('$xbrcd','$xprdcd','$xloccd','$xtrcd','$xflag','$xcust','$opnm',now());";
		}
		if($debug) echo "$sqlexe<br>";
		else pg_exec($db, $sqlexe);
	}
}
function update_brcd_log($xtrcd,$xloccd,$xprdcd,$xbrcd,$xflag,$xcust) {
	//xflag = sccb, brcb, msinv, brinv
	global $db, $debug,$opnm;
	//if ($xflag=='doin' || $xflag=='doc' || $xflag=='wrcr' || $xflag=='wrfoc' || $xflag=='wrgrn' || $xflag=='whinv'){
	if ($xbrcd!='') {
		$sqllog=
		"insert into msprd_brcd_log
		(trtype,trcd,loccd,prdcd,brcd_no,log_date,cust,fcreated_by,fcreated_date)
		values
		('$xflag','$xtrcd','$xloccd','$xprdcd','$xbrcd',now(),'$xcust','$opnm',now());";
		if ($debug) echo $sqllog.'<br>';
		pg_exec($db, $sqllog);
	}

}

function js_bts($xflag,$xloccd,$xcode='') {
	global $str_select_all, $str_check_bts,$str_check_bts2, $xuse_bts, $xtmp_trcd;
//	if ($xuse_bts==1) {
?>
	<script language='JavaScript'>
	function fbts_enterMoveCtrn(evt, xidx){
 		//var shortcut = document.frm_invcnt_fltr;
		var type= evt.srcElement ? evt.srcElement.type : evt.target.type;
		var id = evt.srcElement ? evt.srcElement.id : evt.target.id;
		var key;

		if(window.event){ key = evt.keyCode;
		}else if(evt.which) { key = evt.which;
		}else return true;

		//var xcarton_no=shortcut.elements['carton_no_'+xidx].value;
		if(key==13){
			//alert('xidx '+xidx);
			fadd_carton(xidx);
			return false;
		}
	}

	function fadd_carton(xidx) {
 		var shortcut = document.frm_invcnt_fltr;
		var xcarton_no=shortcut.elements['carton_no_'+xidx].value;
		var xprdcd    =shortcut.elements['xindex'+xidx].value;
		shortcut.elements['carton_no_'+xprdcd].value=xcarton_no;

		var this_brcd1=shortcut.elements['barcode_num_'+xidx].value;
		var this_cart1=shortcut.elements['carton_no_'+xidx].value;
		var this_qty1 =shortcut.elements['txt_qty_'+xidx].value;
		shortcut.elements['barcode_num_'+xprdcd].value=this_brcd1;

		var xarr_data=xcarton_no.split("\n");
		var	xtmp_crtn_insert='';
		var xexist = false;
		for (xi=0; xi<xarr_data.length; xi++) {
			xexist = false;
			xarr_data[xi]=xarr_data[xi].replace(/^\s+|\s+$/g, '') ;
			if (xarr_data[xi]!='') { // no empty data
				//alert(xarr_data[x]);
				//if (xarr_data[xi].substring(0,1)!='M') {
				//	alert('Prefix barcode should \'M\' character !');
				//	return false;
				//}

				if (xtmp_crtn_insert=='') {
					xtmp_crtn_insert=xarr_data[xi];
				}else{
				 xtmp_crtn_insert+='|'+xarr_data[xi];
				}
				//alert(xtmp_crtn_insert);
			}
		}
		//end for
		var url = '../module/add_crtn.php?xtmp_trcd=<?=urlencode($xtmp_trcd)?>&xprdcd='+xprdcd+'&xidx='+xidx+'&xflag=<?=$xflag?>&xloccd=<?=$xloccd?>&xcrtn='+xtmp_crtn_insert+'&xcode=<?=$xcode?>&xqty='+this_qty1;
		//shortcut.elements['barcode_num_'+xidx].value=url;
		new Ajax.Request(url, {
			method: 'get',
			onSuccess: function(transport) {
				var shortcut = 	document.frm_invcnt_fltr;
				var lbtxt = transport.responseText;
				//shortcut.elements['xcount_ctrn0'].value='5';
				var arr_split=lbtxt.split("@");
				var xstr_table=arr_split[0];
				document.getElementById('xtable_'+xidx).innerHTML=xstr_table;
				var xstr_alert=arr_split[1].split("|");
				var xcheck_status=xstr_alert[0];
				var xcheck_msg=xstr_alert[1];
				var xcheck_count=xstr_alert[2];
				var xsum_brcd=xstr_alert[3];
				shortcut.elements['xcount_ctrn'+xidx].value=xcheck_count;
				shortcut.elements['xsumbrcd_'+xidx].value=xsum_brcd;
				$('idsumbrcd_'+xidx).innerHTML='Tot. Barcode: '+xsum_brcd;
				if (xcheck_msg!='')
					alert(xcheck_msg);
				//if (xcheck_status=='OK') shortcut.elements['carton_no_'+xidx].value='';

			}//end onSuccess
		});
	}

	function fcheck_carton(xidx) {
 		var shortcut = document.frm_invcnt_fltr;
		var xcarton_no=shortcut.elements['carton_no_'+xidx].value;
		if (xcarton_no=='') {
			alert('Carton should not empty !');
			xcarton_no.focus();
			return false;
		}

		var xprdcd    =shortcut.elements['xindex'+xidx].value;
		var xarr_data=xcarton_no.split("\n");
		var	xtmp_crtn_insert='';
		var xexist = false;
		for (xi=0; xi<xarr_data.length; xi++) {
			xexist = false;
			xarr_data[xi]=xarr_data[xi].replace(/^\s+|\s+$/g, '') ;
			if (xarr_data[xi]!='') { // no empty data
				//alert(xarr_data[x]);
				if (xarr_data[xi].substring(0,1)!='M') {
					alert('Prefix Carton should \'M\' character !');
					return false;
				}

				if (xtmp_crtn_insert=='') {
					xtmp_crtn_insert=xarr_data[xi];
				}else{
				 xtmp_crtn_insert+='|'+xarr_data[xi];
				}
				//alert(xtmp_crtn_insert);
			}
		}
		//end for

		var url = '../module/check_crtn.php?xtmp_trcd=<?=urlencode($xtmp_trcd)?>&xprdcd='+xprdcd+'&xidx='+xidx+'&xflag=<?=$xflag?>&xloccd=<?=$xloccd?>&xcrtn='+xtmp_crtn_insert;
		//shortcut.elements['barcode_num_'+xidx].value=url;

		new Ajax.Request(url, {
			method: 'get',
			onSuccess: function(transport) {
				var shortcut = 	document.frm_invcnt_fltr;
				var lbtxt = transport.responseText;
				//shortcut.elements['xcount_ctrn0'].value='5';
				var arr_split=lbtxt.split("@");
				var xstr_table=arr_split[0];
				document.getElementById('xtable_'+xidx).innerHTML=xstr_table;
				var xstr_alert=arr_split[1].split("|");
				var xcheck_status=xstr_alert[0];
				var xcheck_msg=xstr_alert[1];
/*				var xcheck_count=xstr_alert[2];
				var xsum_brcd=xstr_alert[3];
				shortcut.elements['xcount_ctrn'+xidx].value=xcheck_count;
				shortcut.elements['xsumbrcd_'+xidx].value=xsum_brcd;*/
				if (xcheck_msg!='')
					alert(xcheck_msg);
				if (xcheck_status=='OK') shortcut.elements['carton_no_'+xidx].value+="\n";

			}//end onSuccess
		});

	}

	function fdel_carton_test(xidx) {
		var shortcut = document.frm_invcnt_fltr;
		alert('xidx='+xidx+' '+$('chk_00').checked);
		//$('ftest').value='ok';
	}

	function fdel_carton(xidx) {
		var shortcut = document.frm_invcnt_fltr;
		//alert('xidx='+xidx);
		var xcount_ctrn= shortcut.elements['xcount_ctrn'+xidx].value;
		var xprdcd   = shortcut.elements['xindex'+xidx].value;
		var xctrn_del='';
		for (i=0; i<xcount_ctrn; i++) {
			//alert('chk_'+xidx+i);
			//var xcheckbox= shortcut.elements['chk_'+xidx+i];
			var xcheckbox= $('chk_'+xidx+i);
			if (xcheckbox.checked) {
				//alert(xcheckbox[i].value);
				if (xctrn_del=='') xctrn_del=xcheckbox.value;
				else xctrn_del+='|'+xcheckbox.value;
			}
		}
		var url = '../module/del_crtn.php?xtmp_trcd=<?=urlencode($xtmp_trcd)?>&xprdcd='+xprdcd+'&xidx='+xidx+'&xflag=<?=$xflag?>&xloccd=<?=$xloccd?>&xctrn_del='+xctrn_del;
		//shortcut.elements['barcode_num_'+xidx].value=url;
		new Ajax.Request(url, {
			method: 'get',
			onSuccess: function(transport) {
				var lbtxt = transport.responseText;
				var arr_split=lbtxt.split("@");
				var xstr_table=arr_split[0];
				document.getElementById('xtable_'+xidx).innerHTML=xstr_table;
				var xstr_alert=arr_split[1].split("|");
				var xcheck_status=xstr_alert[0];
				var xcheck_msg=xstr_alert[1];
				var xcheck_count=xstr_alert[2];
				var xsum_brcd=xstr_alert[3];
				shortcut.elements['xcount_ctrn'+xidx].value=xcheck_count;
				shortcut.elements['xsumbrcd_'+xidx].value=xsum_brcd;
				$('idsumbrcd_'+xidx).innerHTML='Tot. Barcode: '+xsum_brcd;
			}
		});
	}

	function fchange_carton(xidx) {
		var shortcut = document.frm_invcnt_fltr;
		var xprdcd    =shortcut.elements['xindex'+xidx].value;
		var this_cart1=shortcut.elements['carton_no_'+xidx].value;
		shortcut.elements['carton_no_'+xprdcd].value=this_cart1;
	}

	function fchange_barcode(xidx) {
		var shortcut = document.frm_invcnt_fltr;
		var xprdcd    =shortcut.elements['xindex'+xidx].value;
		var this_brcd1=shortcut.elements['barcode_num_'+xidx].value;
		shortcut.elements['barcode_num_'+xprdcd].value=this_brcd1;
	}

	function fadd_barcode(xidx) {
		var shortcut = document.frm_invcnt_fltr;
		var i;
		var j;
		var k;
		var brcd_str='';
		var brcd_split='';
		var valid=true;
		var xprdcd    =shortcut.elements['xindex'+xidx].value;
		var this_brcd1=shortcut.elements['barcode_num_'+xidx].value;
		var this_cart1=shortcut.elements['carton_no_'+xidx].value;
		var this_qty1 =shortcut.elements['txt_qty_'+xidx].value;

		shortcut.elements['barcode_num_'+xprdcd].value=this_brcd1;
		shortcut.elements['carton_no_'+xprdcd].value=this_cart1;

		//alert('this_brcd1: '+this_brcd1);
		if (this_brcd1=='') {
			alert('Barcode should not empty !');
			this_brcd1.focus();
			return false;
		}
		//if (this_brcd1.substring(0,1)!='M') {
		//	alert('Prefix barcode should \'M\' character !');
		//	this_brcd1.focus();
		//	return false;
		//}
		var item_brcd = this_brcd1;
		if (item_brcd.indexOf('/')>0) {
			arrayOfStrings = item_brcd.split('/');
			brcd1=arrayOfStrings[0];
			brcd2=arrayOfStrings[1];
			brcd_prefix='';
			//alert('brcd_prefix='+brcd_prefix+' brcd1='+brcd1+' brcd2='+brcd2);
			if (brcd1.length==brcd2.length) {
				brcd_stop=false;
				for (j=0;j<brcd1.length;j++) {
					if (brcd1.substring(j,j+1)==brcd2.substring(j,j+1) && !brcd_stop) {
						brcd_prefix+=brcd1.substring(j,j+1);
					}else{
						brcd_stop=true;
					}
					//alert('brcd_prefix='+brcd_prefix+' ='+brcd1.substring(j,j+1));
				}
				//alert('brcd_prefix='+brcd_prefix+' brcd1='+brcd1+' brcd2='+brcd2);
				brcd_begin =brcd1.substring(brcd_prefix.length, brcd1.length);
				brcd_end   =brcd2.substring(brcd_prefix.length, brcd2.length);
			}else{
				brcd_prefix=brcd1.substring(0, brcd1.length-brcd2.length);
				brcd_begin =brcd1.substring(brcd_prefix.length, brcd1.length);
				brcd_end   =brcd2;
			}
			for (ibrcd=brcd_begin;ibrcd<=brcd_end;ibrcd++) {
				var sbrcd='';
				var xbrcd=ibrcd.toString();
				if (xbrcd.length<brcd_end.length) {
					for (k=1;k<=brcd_end.length-xbrcd.length;k++)
						xbrcd='0'+xbrcd;
					//xbrcd=sbrcd;
				}
				if (brcd_str=='')
					brcd_str+=brcd_prefix+xbrcd;
				else
					brcd_str+='|'+brcd_prefix+xbrcd;
				//confirm_qty++;
			}
		}else{
			brcd_str=this_brcd1;
		}
		//alert(brcd_str);
		//send M0001/3 via php:  become check_bts.php?xbrcd=M0001|M0002|M0003
		var url = '../module/check_bts2.php?xtmp_trcd=<?=urlencode($xtmp_trcd)?>&xprdcd='+xprdcd+'&xidx='+xidx+'&xflag=<?=$xflag?>&xbrcd='+brcd_str+'&xloccd=<?=$xloccd?>&xcode=<?=$xcode?>&xqty='+this_qty1;
		//shortcut.elements['carton_no_'+xidx].value=url;
		//$('carton_no_'+xidx).value=url;
		new Ajax.Request(url, {
			method: 'get',
			onSuccess: function(transport) {
				//var shortcut = document.frm_invcnt_fltr;
				var lbtxt = transport.responseText;
				var arr_split=lbtxt.split("@");
				var xstr_table=arr_split[0];
				document.getElementById('xtable_'+xidx).innerHTML=xstr_table;
				var xstr_alert=arr_split[1].split("|");
				var xcheck_status=xstr_alert[0];
				var xcheck_msg=xstr_alert[1];
				var xcheck_count=xstr_alert[2];
				var xsum_brcd=xstr_alert[3];
				$('xcount_ctrn'+xidx).value=xcheck_count;
				$('xsumbrcd_'+xidx).value=xsum_brcd;
				$('idsumbrcd_'+xidx).innerHTML='Tot. Barcode: '+xsum_brcd;
/*				shortcut.elements['xcount_ctrn'+xidx].value=xcheck_count;
				shortcut.elements['xsumbrcd_'+xidx].value=xsum_brcd;*/
				if (xcheck_msg!='') alert(xcheck_msg);
			}
		});
		//document.form1.test_dummy.value=url;
		//alert(this_brcd1);
	}

/*	function CheckCountBTS() {
		var shortcut = document.frm_invcnt_fltr;
		var xcount_item=shortcut.elements['xcount_item'].value;
		var cek_bts=true;
		for (i=0;i<xcount_item;i++) {
			var xtxt_qty=shortcut.elements['txt_qty_'+i].value;
			var xprdcd=shortcut.elements['xindex'+i].value;
			var xsumbrcd=shortcut.elements['xsumbrcd_'+i].value;
			if (xsumbrcd!=xtxt_qty) {
				alert('Count of barcode '+xprdcd+' is not same with item quantity');
				cek_bts=false;
			}
		}
		return cek_bts;
	}*/

	function SelectAll() {
		//not used function
	}

		function CheckCountBTS() {
			var shortcut = document.frm_invcnt_fltr;
	<?
	$str_check_bts	="var cek_bts1=true;\n".$str_check_bts;
	$str_check_bts .="return cek_bts1;\n";
	echo($str_check_bts);
	?>
		}
<?
	if ($xflag=='doc') {
?>

	function frm_bts4(jid, jprdcd, jqty) {
		var xstr='';
		xstr+="<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
		xstr+="		<tr bgcolor='#EEEEEE'><input type='hidden' name='br_code' value='ok'>\n";
		xstr+="			<td colspan='2'><b>Barcode Track System.</b></td>\n";
		xstr+="		</tr>\n";
		xstr+="		<tr>\n";
		xstr+="			<td>\n";
		xstr+="				<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
		xstr+="					<tr bgcolor='#FFFFFF'>\n";
		xstr+="	\n";
		xstr+="						<td colspan='2'>&nbsp;</td>\n";
		xstr+="					</tr>\n";
		xstr+="					<tr bgcolor='#FFFFFF'>\n";
		xstr+="						<td valign='top' align='left'>Carton No. </td>\n";
		xstr+="						<td nowrap  align='left'>\n";
		xstr+="							<TEXTAREA NAME='carton_no_"+jid+"' ROWS='3' COLS='20' onKeypress=\"return fbts_enterMoveCtrn(event,'"+jid+"')\" onclick=\"fchange_carton('"+jid+"')\"></TEXTAREA><br><br>\n";
		xstr+="							<input type='button' name='btn_add_crt_"+jid+"' value='Add' onclick=\"fadd_carton('"+jid+"')\">\n";
		xstr+="						</td>\n";
		xstr+="					</tr>\n";
		xstr+="					<tr bgcolor='#FFFFFF'>\n";
		xstr+="						<td colspan='2'>&nbsp;</td>\n";
		xstr+="					</tr>\n";
		xstr+="					<tr bgcolor='#FFFFFF'>\n";
		xstr+="						<td valign='top' align='left'>Serial No. </td>\n";
		xstr+="						<td nowrap  align='left'>\n";
		xstr+="							<input type='text' name='barcode_num_"+jid+"' value='' onclick=\"fchange_barcode('"+jid+"')\">\n";
		xstr+="							<input type='hidden' name='bool_"+jid+"'><br>\n";
		xstr+="							<input type='button' name='btn_add_brc_"+jid+"' value='Add' onclick=\"fadd_barcode('"+jid+"');\">\n";
		xstr+="							<input type='hidden' name='barcode_num_"+jprdcd+"' value=''>\n";
		xstr+="							<input type='hidden' name='carton_no_"+jprdcd+"' value=''>\n";
		xstr+="						</td>\n";
		xstr+="					</tr>\n";
		xstr+="					<tr bgcolor='#FFFFFF'>\n";
		xstr+="						<td colspan='2'>&nbsp;</td>\n";
		xstr+="					</tr>\n";
		xstr+="					<tr bgcolor='#FFFFFF'>\n";
		xstr+="						<td valign='top' colspan='2'  align='left'>\n";
		//xstr+="							<!--INPUT type='text' size='50' name='test_"+jid+"'><br-->\n";
		xstr+="							List to be confirmed :\n";
		xstr+="						</td>\n";
		xstr+="					</tr>\n";
		xstr+="					<tr bgcolor='#FFFFFF'>\n";
		xstr+="						<td valign='top' colspan='2'  align='left'>\n";
		xstr+="							<div id='xtable_"+jid+"'>\n";
		xstr+="							<table border='0' width='100%' bgcolor='#E6E6E6'>\n";
		xstr+="								<tr bgcolor='#EEEEEE'>\n";
		xstr+="								<td width='10' align='center'><b>&nbsp;</b></td>\n";
		xstr+="								<td width='60' align='center'><b>Batch No</b></td>\n";
		xstr+="								<td width='100' align='center'><b>Carton No</b></td>\n";
		xstr+="								<td align='center'><b>Serial No</b></td>\n";
		xstr+="								</tr>\n";
		xstr+="								<tr bgcolor='#FFFFFF'><td colspan='4' align='center'><font color='red'>Data empty</font></td></tr>\n";
		xstr+="							</table>\n";
		xstr+="							</div>\n";
		xstr+="							<input type='hidden' id='xcount_ctrn"+jid+"' name='xcount_ctrn"+jid+"' value='0'><br>\n";
		xstr+="							<input type='hidden' id='xsumbrcd_"+jid+"' name='xsumbrcd_"+jid+"' value='0'><br>\n";
		xstr+="							<input type='hidden' name='xindex"+jid+"' value='"+jprdcd+"'>\n";
		xstr+="							<input type='hidden' name='txt_qty_"+jid+"' value='"+jqty+"'>\n";
		xstr+="							<div id='idsumbrcd_"+jid+"'>Tot. Barcode: 0</div><br>\n";
 		xstr+="							<input type='button' name='btn_del' value='Del' onclick=\"fdel_carton('"+jid+"')\">\n";
		xstr+="						</td>\n";
		xstr+="					</tr>\n";
		xstr+="				</table>\n";
		xstr+="			</td>\n";
		xstr+="		</tr>\n";
		xstr+="	</table>\n";
		return xstr;
	}
<?
	} //end if xflag=='doc'
?>

	</script>
<?
//	}
}

function frm_bts($vprdcd, $vqty, $xidx=0) {
	global $xuse_bts, $xopnm, $xtmp_trcd, $str_check_bts, $xsum_brcd, $xsum_crtn;
	if ($xuse_bts==1) {
?>
	<br><br>
	<table width='100%' border='0' cellspacing='0' cellpadding='0'>
		<tr bgcolor='#EEEEEE'><input type='hidden' name='br_code' value='ok'>
			<td colspan='2'><b>Barcode Track System .<?/*$opnm*/?></b></td>
		</tr>
		<tr>
			<td>
				<table width='100%' border='0' cellspacing='0' cellpadding='0'>
					<tr bgcolor='#FFFFFF'>
						<td colspan='2'>&nbsp;</td>
					</tr>
					<tr bgcolor='#FFFFFF'>
						<td valign='top' align='left'>Carton No. </td>
						<td nowrap  align='left'>
							<TEXTAREA NAME='carton_no_<?=$xidx?>' ROWS='3' COLS='20' onKeypress="return fbts_enterMoveCtrn(event,'<?=$xidx?>')" onclick="fchange_carton('<?=$xidx?>')"><?=$_POST['carton_no_'.$vprdcd]?></TEXTAREA><br><br>
							<input type="button" name="btn_add_crt_<?=$xidx?>" value="Add" onclick="fadd_carton('<?=$xidx?>')">
							<!--input type="button" name="btn_chk_crt_<?=$xidx?>" value="Check Test" onclick="fcheck_carton('<?=$xidx?>');"-->
						</td>
					</tr>
					<tr bgcolor='#FFFFFF'>
						<td colspan='2'>&nbsp;</td>
					</tr>
					<tr bgcolor='#FFFFFF'>
						<td valign='top' align='left'>Serial No. </td>
						<td nowrap  align='left'>
							<input type='text' name='barcode_num_<?=$xidx?>' value='<?=$_POST['barcode_num_'.$vprdcd]?>' onclick="fchange_barcode('<?=$xidx?>')">
							<input type='hidden' name='bool_<?=$xidx?>'><br>
							<input type="button" name="btn_add_brc_<?=$xidx?>" value="Add" onclick="fadd_barcode('<?=$xidx?>');">
							<input type='hidden' name='barcode_num_<?=$vprdcd?>' value='<?=$_POST['barcode_num_'.$vprdcd]?>'>
							<input type='hidden' name='carton_no_<?=$vprdcd?>' value='<?=$_POST['carton_no_'.$vprdcd]?>'>
						</td>
					</tr>
					<tr bgcolor='#FFFFFF'>
						<td colspan='2'>&nbsp;</td>
					</tr>
					<tr bgcolor='#FFFFFF'>
						<td valign='top' colspan='2'  align='left'>
							<!--INPUT type='text' size='50' name='test_<?=$xidx?>'><br-->
							List to be confirmed :
						</td>
					</tr>
					<tr bgcolor='#FFFFFF'>
						<td valign='top' colspan='2'  align='left'>
							<div id='xtable_<?=$xidx?>'><?
	$xsum_brcd=0;
	$xsum_crtn=0;
	$xresult=fdisplay_carton($xopnm, $xtmp_trcd, $vprdcd, $xidx);
	echo $xresult;
							?></div>
							<input type='hidden' id='xcount_ctrn<?=$xidx?>' name='xcount_ctrn<?=$xidx?>' value='<?=$xsum_crtn?>'><br>
							<input type='hidden' id='xsumbrcd_<?=$xidx?>' name='xsumbrcd_<?=$xidx?>' value='<?=$xsum_brcd?>'><br>
							<div id='idsumbrcd_<?=$xidx?>'>Tot. Barcode: <?=$xsum_brcd?></div><br>
							<input type='button' name='btn_del' value='Del' onclick="fdel_carton('<?=$xidx?>')">
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
<?
		$str_check_bts.=
			"	if (shortcut.xsumbrcd_".$xidx.".value==0) {\n".
			"		alert('Barcode List Item $vprdcd should not empty');\n".
			"		cek_bts1=false;\n".
			" }\n".
			" var vtxt_qty_"."$xidx=parseInt(shortcut.txt_qty_".$xidx.".value);\n".
			" if (parseInt(shortcut.txt_qty_".$xidx.".value)<0) vtxt_qty_"."$xidx=-1*parseInt(shortcut.txt_qty_".$xidx.".value);\n".
			" //alert(vtxt_qty_"."$xidx+' '+shortcut.xsumbrcd_".$xidx.".value);\n".
			"	if (shortcut.xsumbrcd_".$xidx.".value!=0 && vtxt_qty_".$xidx."!=parseInt(shortcut.xsumbrcd_".$xidx.".value)) {\n".
			"		alert('Count of barcode $vprdcd is not same with item quantity');\n".
			"		cek_bts1=false;\n".
			"	}\n";
	}//end if xuse_bts
}
?>
<?
	function fget_maxnumber($xtablename, $xnstart, $xthe_msid) {
		global $db, $debug, $br_new_prefix;

		if ($br_new_prefix==1){
			$xnsql="select nvalue from $xtablename where nstart='$xnstart' and loccd='$xthe_msid';";
			if ($debug) echo "$xnsql<br>";
			$dresult=pg_exec($db, $xnsql);
			if (pg_numrows($dresult)>0) {
				list($xnewtrx)=pg_fetch_row($dresult,0);
				$xnewtrx=$xnewtrx+1;
				$xceksql="select loccd from $xtablename
					where nstart='$xnstart' and loccd='$xthe_msid';";
				$xcekres=pg_exec($db, $xceksql);
				if (pg_num_rows($xcekres)>0) {
					$xpsql="update $xtablename set nvalue=$xnewtrx
						where nstart='$xnstart' and loccd='$xthe_msid'";
				}else{
					$xpsql="insert into $xtablename values('$xnstart',$xnewtrx,'$xthe_msid');";
				}
			}else{
				$xpsql="insert into $xtablename values('$xnstart',1,'$xthe_msid');";
				$xnewtrx=1;
			}
		}else{ //sharing number for branch state group
			$xnsql="select max(nvalue) from $xtablename
				where nstart='$xnstart' and substr(loccd, 7, 3) in
				(select bc_id from branch where bc_state in
					(select bc_state from branch where bc_id='".substr($xthe_msid,6,3)."')
				) ";
			if ($debug) echo "$xnsql<br>";
			$dresult=pg_exec($db, $xnsql);
			if (pg_numrows($dresult)>0) {
				list($xnewtrx)=pg_fetch_row($dresult,0);
				$xnewtrx=$xnewtrx+1;
				$xceksql="select loccd from $xtablename
					where nstart='$xnstart' and loccd='$xthe_msid';";
				$xcekres=pg_exec($db, $xceksql);
				if (pg_num_rows($xcekres)>0) {
					$xpsql="update $xtablename set nvalue=$xnewtrx
						where nstart='$xnstart' and loccd='$xthe_msid'";
				}else{
					$xpsql="insert into $xtablename values('$xnstart',$xnewtrx,'$xthe_msid');";
				}
			}else{
				$xpsql="insert into $xtablename values('$xnstart',1,'$xthe_msid');";
				$xnewtrx=1;
			}

			if ($debug==1) {
				echo "$xpsql<br>";
			}else{
				$dresult=pg_exec($db, $xpsql);
			}
		}
		return $xnewtrx;
	}
	
	function daily_inloc($xloccd, $xprdcd, $xqty, $xtype) {
		global $db, $debug, $region, $seller_type;
		$region = (empty($region))?"US":$region;
		$seller_type = (empty($seller_type))?"MS":$seller_type;
		$monthinfo = date("mY",get_timestamp(get_timezone(TZ_COUNTRY,$region)));
		$dateinfo = date("d",get_timestamp(get_timezone(TZ_COUNTRY,$region)));
		$table_name = "inloc_$monthinfo";
		$asql = "select * from pg_tables where tablename='$table_name' and schemaname='public'";
		if(!$GLOBALS["db2"]->doQuery($asql)->isFound()) {
			$bsql = "CREATE TABLE $table_name (
					trxdate integer,
					code varchar,
					seller_type varchar,
					trxtype varchar,
					prdcd varchar,
					qty double precision
				);";
			$GLOBALS["db2"]->doQuery($bsql);
		}
		
		$GLOBALS["db2"]->doInsert("$table_name",array(
			"trxdate" => $dateinfo,
			"code" => $xloccd,
			"seller_type" => $seller_type,
			"trxtype" => $xtype,
			"prdcd" => $xprdcd,
			"qty" => $xqty
		));
	}
	
	function fget_br_opening_stock($xloccd, $xprdcd, $xdate) {
		//eg: xdate= 15/01/2008
		$xmktime=mktime(0, 0, 0, substr($xdate,3,2), substr($xdate,0,2)-1, substr($xdate,6,4));
		$xdate_minusone=date('d/m/Y', $xmktime);
		//eg: xdate_minusone= 14/01/2008
		$xmktime=mktime(0, 0, 0, substr($xdate,3,2), 0, substr($xdate,6,4));
		$xdate_lastmonth=date('d/m/Y', $xmktime);
		//eg: xdate_lastmonth= 31/12/2007
		$xhasil=0;
		if ($xdate_lastmonth==$xdate_minusone) {
			$xhasil=fget_br_monthly_stock($xloccd, $xprdcd, $xdate_lastmonth);
		}else{
		//eg: xdate_lastmonth= 31/12/2007
			$xhasil=fget_br_monthly_stock($xloccd, $xprdcd, $xdate_lastmonth);
			$xmk_awal =mktime(0, 0, 0, substr($xdate,3,2), 1, substr($xdate,6,4));
			$xdate_awal=date('d/m/Y', $xmk_awal);
			$xdate_akhir=$xdate_minusone;
			//eg: xdate_awal 01/01/2008 xdate_akhir 14/01/2008

			$hDO =fget_cb_data_DO_item($xloccd,$xprdcd,$xdate_awal,$xdate_akhir,0);
			$hADJ =fget_cb_data_ADJ_item($xloccd,$xprdcd,$xdate_awal,$xdate_akhir,0);

			$hCB=fget_cb_data_CB_item($xloccd,$xprdcd,$xdate_awal,$xdate_akhir,0);
			$hFOC=fget_cb_data_FOC_item($xloccd,$xprdcd,$xdate_awal,$xdate_akhir,0);
			$hCR=fget_cb_data_CR_item($xloccd,$xprdcd,$xdate_awal,$xdate_akhir,0);
			$hINV=fget_cb_data_INV_item($xloccd,$xprdcd,$xdate_awal,$xdate_akhir,0,'out');
			$hRETURN=fget_cb_data_INV_item($xloccd,$xprdcd,$xdate_awal,$xdate_akhir,0,'in');
			$hGRN	=fget_cb_data_GRN_item($xloccd,$xprdcd,$xdate_awal,$xdate_akhir,0,'all');
			$xhasil += ($hDO+$hADJ)-($hCB+$hFOC+$hCR+$hINV+$hRETURN+$hGRN);
		}
		return $xhasil;
	}

	function fget_br_monthly_stock($xloccd, $xprdcd, $xdate) {
		global $db;
		$xhasil=0;
		$xsql1 = "select fstock from inloc_monthly ";
		$xsql1.= "where prdcd='$xprdcd' and loccd='$xloccd' and fdate='$xdate'; ";
		//echo $xsql1."<br>";
		$xres1 = pg_exec($db, $xsql1);
		if (pg_num_rows($xres1)>0) {
			$xhasil=pg_fetch_result($xres1, 0, 0);
		}else{
			$hCB=fget_cb_data_CB_item($xloccd,$xprdcd,$xdate,$xdate,12);
			$hFOC=fget_cb_data_FOC_item($xloccd,$xprdcd,$xdate,$xdate,12);
			$hCR=fget_cb_data_CR_item($xloccd,$xprdcd,$xdate,$xdate,12);
			$hINV=fget_cb_data_INV_item($xloccd,$xprdcd,$xdate,$xdate,12,'out');
			$hRETURN=fget_cb_data_INV_item($xloccd,$xprdcd,$xdate,$xdate,12,'in');
			$hGRN	=fget_cb_data_GRN_item($xloccd,$xprdcd,$xdate,$xdate,12,'all');
			$hDO =fget_cb_data_DO_item($xloccd,$xprdcd,$xdate,$xdate,12);
			$hADJ =fget_cb_data_ADJ_item($xloccd,$xprdcd,$xdate,$xdate,12);
			$xhasil =
				($hDO+$hADJ)-($hCB+$hFOC+$hCR+$hINV+$hRETURN+$hGRN);
		}
		pg_free_result($xres1);
		return $xhasil;
	}

	function fget_wh_monthly_stock($xloccd, $xprdcd, $xdate) {
		global $db;
		$xhasil=0;
		$xsql1 = "select fstock from inloc_monthly ";
		$xsql1.= "where prdcd='$xprdcd' and loccd='$xloccd' and fdate='$xdate'; ";
		//echo $xsql1."<br>";
		$xres1 = pg_exec($db, $xsql1);
		if (pg_num_rows($xres1)>0) {
			$xhasil=pg_fetch_result($xres1, 0, 0);
		}else{
			$xmk_awal =mktime(0, 0, 0, substr($xdate_lastmonth,3,2)+1, 1, substr($xdate_lastmonth,6,4));
			$xdate_awal=date('d/m/Y', $xmk_awal);
			$xdate_akhir=$xdate;
			$hDOin=fget_dd_data_DO_item('DDEPT',$xprdcd,$xdate,$xdate,12,'in');
			$hADJin=fget_dd_data_ADJ_item('DDEPT',$xprdcd,$xdate,$xdate,12,'in');
			$hGRNin=fget_dd_data_GRN_item('DDEPT',$xprdcd,$xdate,$xdate,12,'in');
			$hINV=fget_dd_data_INV_item('DDEPT',$xprdcd,$xdate,$xdate,12);
			$hDOout=fget_dd_data_DO_item('DDEPT',$xprdcd,$xdate,$xdate,12,'out');
			$hCR=fget_dd_data_CR_item('DDEPT',$xprdcd,$xdate,$xdate,12);
			$hFOC=fget_dd_data_FOC_item('DDEPT',$xprdcd,$xdate,$xdate,12);
			$hADJout=fget_dd_data_ADJ_item('DDEPT',$xprdcd,$xdate,$xdate,12,'out');
			$hGRNout=fget_dd_data_GRN_item('DDEPT',$xprdcd,$xdate,$xdate,12,'out');
			$xhasil=($hDOin+$hADJin+$hGRNin)-($hINV+$hDOout+$hCR+$hFOC+$hADJout+$hGRNout);
		}
		pg_free_result($xres1);
		return $xhasil;
	}

	function fget_br_current_stock($xloccd, $xprdcd, $xdate) {
		//eg: xdate= 15/01/2008
		global $db, $opnm;

		$xcsql="select fstock from inloc_monthly
		where prdcd='$xprdcd' and loccd='$xloccd' and fdate='$xdate';";
		//if ($opnm=='hima') echo "$xcsql<br>";
		$xcres=pg_exec($db, $xcsql);
		if (pg_num_rows($xcres)>0) {
			$xhasil=pg_fetch_result($xcres, 0, 0);
		}else{
// 			$xmktime=mktime(0, 0, 0, substr($xdate,3,2), 0, substr($xdate,6,4));
// 			$xdate_lastmonth=date('d/m/Y', $xmktime);
			if (substr($xdate,3,7)==date('m/Y')) {
				$xmktime=mktime(0, 0, 0, substr($xdate,3,2)-1, 0, substr($xdate,6,4));
			}else{
				$xmktime=mktime(0, 0, 0, substr($xdate,3,2), 0, substr($xdate,6,4));
			}
			$xdate_lastmonth=date('d/m/Y', $xmktime);
			//echo substr($xdate,3,7)."  ".date('m/Y')." $xdate_lastmonth<br>";

			//eg: xdate_lastmonth= 31/12/2007
			$xcsql2="select fstock from inloc_monthly
			where prdcd='$xprdcd' and loccd='$xloccd' and fdate='$xdate_lastmonth';";
			//echo "$xcsql2<br>";
			$xcres2=pg_exec($db, $xcsql2);
			$xhasil=0;
			if (pg_num_rows($xcres2)>0) {
				$xhasil=pg_fetch_result($xcres2, 0, 0);
				$xmk_awal =mktime(0, 0, 0, substr($xdate_lastmonth,3,2)+1, 1, substr($xdate_lastmonth,6,4));
				$xdate_awal=date('d/m/Y', $xmk_awal);
				$xdate_akhir=$xdate;
				//eg: xdate_awal 01/01/2008 xdate_akhir 14/01/2008
				pg_free_result($xcres2);
				//echo "*$xhasil $xdate_awal $xdate_akhir<br>";
				$hDO =fget_cb_data_DO_item($xloccd,$xprdcd,$xdate_awal,$xdate_akhir,0);
				$hADJ =fget_cb_data_ADJ_item($xloccd,$xprdcd,$xdate_awal,$xdate_akhir,0);

				$hCB=fget_cb_data_CB_item($xloccd,$xprdcd,$xdate_awal,$xdate_akhir,0);
				$hFOC=fget_cb_data_FOC_item($xloccd,$xprdcd,$xdate_awal,$xdate_akhir,0);
				$hCR=fget_cb_data_CR_item($xloccd,$xprdcd,$xdate_awal,$xdate_akhir,0);
				$hINV=fget_cb_data_INV_item($xloccd,$xprdcd,$xdate_awal,$xdate_akhir,0,'out');
				$hRETURN=fget_cb_data_INV_item($xloccd,$xprdcd,$xdate_awal,$xdate_akhir,0,'in');
				$hGRN	=fget_cb_data_GRN_item($xloccd,$xprdcd,$xdate_awal,$xdate_akhir,0,'all');
				$xhasil += ($hDO+$hADJ)-($hCB+$hFOC+$hCR+$hINV+$hRETURN+$hGRN);
			}else{
				$xdate_awal=$xdate;
				$xdate_akhir=$xdate;
				pg_free_result($xcres2);
				//echo "**$xhasil $xdate_awal $xdate_akhir<br>";
				$hDO =fget_cb_data_DO_item($xloccd,$xprdcd,$xdate_awal,$xdate_akhir,12);
				$hADJ =fget_cb_data_ADJ_item($xloccd,$xprdcd,$xdate_awal,$xdate_akhir,12);

				$hCB=fget_cb_data_CB_item($xloccd,$xprdcd,$xdate_awal,$xdate_akhir,12);
				$hFOC=fget_cb_data_FOC_item($xloccd,$xprdcd,$xdate_awal,$xdate_akhir,12);
				$hCR=fget_cb_data_CR_item($xloccd,$xprdcd,$xdate_awal,$xdate_akhir,12);
				$hINV=fget_cb_data_INV_item($xloccd,$xprdcd,$xdate_awal,$xdate_akhir,12,'out');
				$hRETURN=fget_cb_data_INV_item($xloccd,$xprdcd,$xdate_awal,$xdate_akhir,12,'in');
				$hGRN	=fget_cb_data_GRN_item($xloccd,$xprdcd,$xdate_awal,$xdate_akhir,12,'all');
				$xhasil += ($hDO+$hADJ)-($hCB+$hFOC+$hCR+$hINV+$hRETURN+$hGRN);
			}
		}
		return $xhasil;
	}

	function fget_wh_current_stock($xloccd, $xprdcd, $xdate) {
		global $db;
		//eg: xdate= 15/01/2008
		$xcsql="select fstock from inloc_monthly
		where prdcd='$xprdcd' and loccd='$xloccd' and fdate='$xdate';";
		$xcres=pg_exec($db, $xcsql);
		if (pg_num_rows($xcres)>0) {
			$xhasil=pg_fetch_result($xcres, 0, 0);
		}else{
			$xmktime=mktime(0, 0, 0, substr($xdate,3,2), 0, substr($xdate,6,4));
			$xdate_lastmonth=date('d/m/Y', $xmktime);
			//eg: xdate_lastmonth= 31/12/2007
			$xcsql2="select fstock from inloc_monthly
			where prdcd='$xprdcd' and loccd='$xloccd' and fdate='$xdate_lastmonth';";
			//echo $xcsql2."<br>";
			$xcres2=pg_exec($db, $xcsql2);
			if (pg_num_rows($xcres2)>0) {
				$xhasil=pg_fetch_result($xcres2, 0, 0);
			}
			pg_free_result($xcres2);
			$xmk_awal =mktime(0, 0, 0, substr($xdate,3,2), 1, substr($xdate,6,4));
			$xdate_awal=date('d/m/Y', $xmk_awal);
			$xdate_akhir=$xdate;
			//eg: xdate_awal 01/01/2008 xdate_akhir 14/01/2008
			$hDOin=fget_dd_data_DO_item($xloccd,$xprdcd,$xdate_awal,$xdate_akhir,0,'in');
			$hADJin=fget_dd_data_ADJ_item($xloccd,$xprdcd,$xdate_awal,$xdate_akhir,0,'in');
			$hGRNin=fget_dd_data_GRN_item($xloccd,$xprdcd,$xdate_awal,$xdate_akhir,0,'in');
			$hINV=fget_dd_data_INV_item($xloccd,$xprdcd,$xdate_awal,$xdate_akhir,0);
			$hDOout=fget_dd_data_DO_item($xloccd,$xprdcd,$xdate_awal,$xdate_akhir,0,'out');
			$hCR=fget_dd_data_CR_item($xloccd,$xprdcd,$xdate_awal,$xdate_akhir,0);
			$hFOC=fget_dd_data_FOC_item($xloccd,$xprdcd,$xdate,$xdate_akhir,0);
			$hADJout=fget_dd_data_ADJ_item($xloccd,$xprdcd,$xdate_awal,$xdate_akhir,0,'out');
			$hGRNout=fget_dd_data_GRN_item($xloccd,$xprdcd,$xdate_awal,$xdate_akhir,0,'out');
			$xhasil+=($hDOin+$hADJin+$hGRNin)-($hINV+$hDOout+$hCR+$hFOC+$hADJout+$hGRNout);
		}
		pg_free_result($xcres);
		return $xhasil;
	}

	function fget_trxstaff_name($xtrcd) {
		global $db;
		$xhasil="-";
		$xsql="
		select '' as staff_name,code,trtype from newsctrh where trcd='$xtrcd'
		union
		select staff_name,code,trtype from newmstrh where trcd='$xtrcd'
		union
		select staff_name,code,trtype from newmsivtrh where trivcd='$xtrcd'
		union
		select staff_name,code,trtype from newddtrh where trcd='$xtrcd'";
		$xres = pg_exec($db, $xsql);
		if (pg_num_rows($xres)>0) {
			$xrow=pg_fetch_row($xres, 0);
			if ($xrow[2]=='2' && preg_match('\*',$xrow[1])) {
				$xhasil=$xrow[0];
			}else{
				$xhasil=$xrow[1];
			}
		}
		pg_free_result($xres);
		return $xhasil;
	}

	function fget_brname($xbr_code){
		global $db;
		$xsql="select br_name from msms_new where br_code='$xbr_code';";
		$xres = pg_exec($db, $xsql);
		if (pg_num_rows($xres)>0) {
			$xrow=pg_fetch_row($xres, 0);
			$xhasil=strtoupper($xrow[0]);
		}
		pg_free_result($xres);
		return $xhasil;
	};

function listSCID3($idx,$fldname) {
	global $db,$usrtype,$priv,$opnm;
/*	if ($usrtype==0) $svcquerr="select sub_name from sub_mssc where code='$idx' and status='N' order by sub_name ASC;";
	if ($usrtype==1) $svcquerr="select sub_name from sub_mssc where sub_name='$idx' and status='N' order by sub_name ASC;";*/
	if ($priv==2) {
		$bcid = substr($opnm,6,9);
		$svcquerr="select sub_name from sub_mssc where bc_id='$bcid' and status='N' order by sub_name ASC;";
	}else{
		$svcquerr="select sub_name from sub_mssc where status='N' order by sub_name ASC;";
	}
	$ressvc=pg_exec($db,$svcquerr);
	print $svcquerr;
	//print pg_num_rows($ressvc);
	if (pg_num_rows($ressvc)<0) {
		print "<INPUT type=\"text\" name=\"$fldname\" size=\"10\" maxlength=\"9\">\n";
	} else {
		print "<select name=\"$fldname\">\n";
		for($ls=0;$ls<pg_num_rows($ressvc);$ls++) {
			$rowls=pg_fetch_row($ressvc,$ls);
			$seltxt=($_POST[$fldname]==$rowls[0])?" selected ":" ";
			print "<option value=\"$rowls[0]\" $seltxt>$rowls[0]</option>\n";
		}
		print "</select>\n";
	}
}

function fget_rr_trxno() {
	global $db;
	$q1="SELECT trrrcd FROM rrdistrh UNION SELECT trrrcd FROM delrrdistrh
		ORDER BY trrrcd DESC limit 1;";
	//if ($debug) echo "$q1<br>";
	$r1=pg_query($db,$q1);
	if(pg_num_rows($r1)>0) {
		$subRRnum=substr(pg_fetch_result($r1,0),2,6)+1;
		$back=$subRRnum%1000000;
		//echo $back."<br>";
		$RRnum="RR".str_pad($back, 6, '0', STR_PAD_LEFT);
			pg_free_result($r1);
		} else {
			$RRnum="RR000000";
		}
	return $RRnum;
}

function fget_grn_trxno($xloccd) {
	global $db, $year1;
	$thismonth=$year1;
	$squery =
		"select nvalue from newmsgrnmonth
		where nstart='$thismonth' and loccd='$xloccd';";
	$dresult=pg_exec($db,$squery);
	if (pg_numrows($dresult)>0)	{
		list($newTransId)=pg_fetch_row($dresult,0);
		$newTransId=$newTransId+1;
		$xsql="update newmsgrnmonth set nvalue=nvalue+1 where nstart='$thismonth' and loccd='$xloccd';";
		if ($debug==1) echo "$xsql<br>";
		$dresult=pg_exec($db, $xsql);
	} else {
		$xsql="insert into newmsgrnmonth values('$thismonth',1,'$xloccd');";
		if ($debug==1) echo("$xsql<br>");
		$dresult=pg_exec($db, $xsql);
		$newTransId=1;
	}

	$digitcb='DDGR';
 	for ($i=0;strlen($newTransId)<=5;$i++)
   	$newTransId="0".$newTransId;

 	$newTransId=$digitcb.part_date().$newTransId;
	return $newTransId;

}

function fget_dlvname($xcode){
	global $db;
	if($xcode=="") $xcode = 0; 
	$xsql="select delivery_name from delivery_method where delivery_id='$xcode';";
	$xres = pg_exec($db, $xsql);
	if (pg_num_rows($xres)>0) {
		$xrow=pg_fetch_row($xres, 0);
		$xhasil=strtoupper($xrow[0]);
	} else $xhasil ="-";
	pg_free_result($xres);
	return $xhasil;
};

function fdisplay_carton($xopnm, $xtmp_trcd, $xprdcd, $xidx) {
	global $db, $debug, $xsum_brcd, $xsum_crtn;
	$xresult ="<table border='0' width='100%' bgcolor='#E6E6E6'>\n";
	$xresult.="<tr bgcolor='#EEEEEE'>\n";
	$xresult.="<td width='10' align='center'><b>&nbsp;</b></td>\n";
	$xresult.="<td width='60' align='center'><b>Batch No</b></td>\n";
	$xresult.="<td width='100' align='center'><b>Carton No</b></td>\n";
	$xresult.="<td align='center'><b>Serial No</b></td>\n";
	$xresult.="</tr>\n";
	$xcsql3 ="select distinct batch_no,ctrn_no from tmp_brcd ";
	$xcsql3.="where xuser='$xopnm' and trcd='$xtmp_trcd' and prdcd='$xprdcd' and saved='0'";
	//if ($debug) echo $xcsql3."<br>";

	$xcres3=pg_exec($db, $xcsql3);
	$xsumbrcd=0;
	if (pg_num_rows($xcres3)>0) {
		for ($i=0; $i<pg_num_rows($xcres3); $i++) {
			$xbrcd_str='';
			$xrow3=pg_fetch_row($xcres3, $i);
			$xbrcd_sql ="select brcd_no from tmp_brcd ";
			$xbrcd_sql.="where xuser='$xopnm' and trcd='$xtmp_trcd' and prdcd='$xprdcd' ";
			$xbrcd_sql.="and batch_no='$xrow3[0]' and ctrn_no='$xrow3[1]' order by brcd_no ";
			$xbrcd_res =pg_exec($db, $xbrcd_sql);
			for ($j=0; $j<pg_num_rows($xbrcd_res); $j++) {
				$xbrcd_row=pg_fetch_row($xbrcd_res, $j);
				if ($xbrcd_str=='') $xbrcd_str=$xbrcd_row[0];
				else $xbrcd_str.=", ".$xbrcd_row[0];
				$xsumbrcd++;
			}
			pg_free_result($xbrcd_res);
			$xresult.="<tr bgcolor='#FFFFFF'>\n";
			$xresult.="<td valign='top' align='center'><input type='checkbox' id='chk_".$xidx.$i."' name='chk_".$xidx.$i."' value='$xrow3[1]'></td>\n";
			$xresult.="<td valign='top' align='left'>&nbsp;$xrow3[0]</td>\n";
			$xresult.="<td valign='top' align='left'>&nbsp;$xrow3[1]</td>\n";
			$xresult.="<td valign='top' align='left'>$xbrcd_str</td>\n";
			$xresult.="</tr>\n";
		}
	}else{
		$xresult.="<tr bgcolor='#FFFFFF'><td colspan='4' align='center'><font color='red'>".mxlang('3066')."</font></td></tr>\n";
	}

	$xresult.="</table>\n";//<br><input name='ftest' id='ftest' value='hallo'>\n";
	//$xresult.="<input type='text' name='xcount_ctrn".$xidx."' id='xcount_ctrn".$xidx."' value='$i'>\n";
	//$xresult.="<input type='hidden' name='xsumbrcd_$xidx' id='xsumbrcd_$xidx' value='$xsumbrcd'>\n";
	$xsum_crtn =pg_num_rows($xcres3);
	$xsum_brcd=$xsumbrcd;
	pg_free_result($xcres3);
	return $xresult;
}

function fdisp_brcd_no($xtrcd, $xprdcd, $xcolspan=2, $xwith_TR=1) {
	global $db, $debug;
// begin display barcode number
	$xsql_brcd="select distinct brcd_no from msprd_brcd_log where trcd='$xtrcd' and prdcd='$xprdcd'";
	//if ($debug) echo $xsql_brcd."<br>";
	$xres_brcd=pg_exec($db, $xsql_brcd);
	if (pg_num_rows($xres_brcd)>0) {
		$xstr_barcode='';
		for ($z=0; $z<pg_num_rows($xres_brcd); $z++) {
			$xrow_brcd=pg_fetch_row($xres_brcd, $z);
			if ($xstr_barcode=='') $xstr_barcode=$xrow_brcd[0];
			else $xstr_barcode.=', '.$xrow_brcd[0];
		}
	}
	if ($xstr_barcode!='') {
		if ($xwith_TR==1) {
?>
			<tr valign="top"  bgcolor="#FFFFFF">
				<td>&nbsp;</td>
				<td colspan='<?=$xcolspan?>'>
<?
								echo("<b>".mxlang("805")." :</b> ".$xstr_barcode);
?>
				</td>
			</tr>
<?
		}elseif ($xwith_TR==0) {
				echo("<br><b>".mxlang("805")." :</b> ".$xstr_barcode);
		}
	}
	pg_free_result($xres_brcd);
// end displaying barcode number
}

function get_stockintransit($vprdcd, $vtgl1, $vtgl2) {
	global $db, $opnm;
	$sql_pending =
		"
		select sum(a.do_qty) as pending,sum(b.rec_qty) as received
		from (
			select v.trdocd,sum(w.qty) as do_qty
			from dodistrh v, dodistrd w
			where v.trdocd=w.trdocd and w.prdcd='$vprdcd' ".
			//and v.code='$txt_code'
			"and v.code in (select br_code from msms_new,users_braccess  where msms_new.br_code=users_braccess.brcode and br_status=true and br_deleted=false and users_braccess.uname='$opnm') ".
			"and v.trdt>='$vtgl1' and v.trdt<='$vtgl2'
			and w.dp is null
			group by v.trdocd
		) a
		left join (
		select x.invno,sum(y.qty) as rec_qty
		from rrdistrh x, rrdistrd y
		where x.trrrcd=y.trrrcd and y.prdcd='$vprdcd'
		and x.trdt>='$vtgl1' and x.trdt<='$vtgl2' ".//and x.code='$txt_code'
		"and x.code in (select br_code from msms_new,users_braccess  where msms_new.br_code=users_braccess.brcode and br_status=true and br_deleted=false and users_braccess.uname='$opnm') ".
		"group by x.invno
		) b on b.invno=a.trdocd ";

	$res_pending = pg_exec($db, $sql_pending);
	if (pg_num_rows($res_pending)>0) {
		$hPENDINGX = pg_fetch_row($res_pending,0);
		if ($hPENDINGX[1]=='') $hPENDINGX[1]=0;
		$hPENDING = $hPENDINGX[0]-$hPENDINGX[1];
	}else{
		$hPENDING = 0;
	}
	pg_free_result($res_pending);
	return $hPENDING;
}

function check_payment_note($xpay_type, $xpay_table, $xtrcd_field, $xtrcd_no) {
	//check_payment_note('VOU', 'mspayment', 'trcd', 'KD-MC080707000086')
	global $db;
	$xcheck=false;
	$xsql = "select paytype1, paytype2, paytype3, paytype4, paytype5, ";
	$xsql.= "totpay1, totpay2, totpay3, totpay4, totpay5, ";
	$xsql.= "paynote1, paynote2, paynote3, paynote4, paynote5 ";
	$xsql.= "from $xpay_table where $xtrcd_field='$xtrcd_no' ";
	$xrow=fpg_exec($xsql);
	if ($xrow[0]==$xpay_type && $xrow[5]!=0 && $xrow[10]=='') {
		$xcheck=true;
	}
	if ($xrow[1]==$xpay_type && $xrow[6]!=0 && $xrow[11]=='') {
		$xcheck=true;
	}
	if ($xrow[2]==$xpay_type && $xrow[7]!=0 && $xrow[12]=='') {
		$xcheck=true;
	}
	if ($xrow[3]==$xpay_type && $xrow[8]!=0 && $xrow[13]=='') {
		$xcheck=true;
	}
	if ($xrow[4]==$xpay_type && $xrow[9]!=0 && $xrow[14]=='') {
		$xcheck=true;
	}
	return $xcheck;
}

function freserve_brcd($xtrcd, $xprdcd, $xqty) {
	global $db, $debug;
	//begin update barcode
	$xbrcd_sql="select brcd_no from msprd_brcd_log ".
		"where trcd='$xtrcd' and prdcd='$xprdcd' ";
	if ($debug) echo $xbrcd_sql.'<br>';
	$xbrcd_res=pg_exec($db, $xbrcd_sql);
	for ($xb=0; $xb<pg_numrows($xbrcd_res); $xb++) {
		$xbrcd_row=pg_fetch_row($xbrcd_res, $xb);
		//if ($debug) echo $xbrcd_row[1].'<br>';
		$xsql4="select log_id,trcd,loccd,trtype,cust, ".
			"batch_no, ctrn_no ".
			"from msprd_brcd_log where brcd_no='$xbrcd_row[0]' ".
			"order by log_id desc ";
		if ($debug) echo $xsql4.'<br><br>';
		$xres4=pg_exec($db, $xsql4);
		if (pg_numrows($xres4)>1) {
			if (pg_fetch_result($xres4, 0, 1)==$xtrcd) {
				$xnew_trcd =pg_fetch_result($xres4, 1, 1);
				$xnew_loccd=pg_fetch_result($xres4, 1, 2);
				$xnew_trtype=pg_fetch_result($xres4, 1, 3);
				$xnew_cust=pg_fetch_result($xres4, 1, 4);
				$xnew_batch_no=pg_fetch_result($xres4, 1, 5);
				$xnew_ctrn_no=pg_fetch_result($xres4, 1, 6);
				if ($debug) echo "$xnew_trcd $xnew_loccd $xnew_trtype $xnew_cust $xnew_batch_no $xnew_ctrn_no<br>";

				update_brcd3($xnew_trcd,$xnew_loccd,$xprdcd,$xbrcd_row[0],$xnew_trtype,$xnew_cust,$xnew_batch_no,$xnew_ctrn_no,$xqty);
			}
		}
		pg_free_result($xres4);
	}
	pg_free_result($xbrcd_res);
	//end update barcode
}

function SCIDlist($fldname, $controller='') {
	global $db,$usrtype,$priv,$opnm;
	/*
	$svcquerr="
		SELECT u.sccode,sc.st_id,sce.sc_reg 
		FROM users_scaccess u 
			join sub_mssc sc on u.sccode=sc.code 
			join sub_mssc_ext sce on u.sccode=sce.sccode
		WHERE u.uname='$opnm' and u.flag='1' and sc.status='N' 
		order by u.sccode;";
		*/
	$svcquerr = "SELECT sc.sub_name,sc.st_id,sce.sc_reg FROM users_scaccess u join sub_mssc sc on u.sccode=sc.sub_name join sub_mssc_ext sce on u.sccode=sce.sccode WHERE u.uname='$opnm' and u.flag='1' and sc.status='N' order by sc.sub_name;";
	$ressvc=pg_exec($db,$svcquerr);
	// print $svcquerr;
	if (pg_num_rows($ressvc)<0)
		print "<INPUT type=\"text\" name=\"$fldname\" size=\"10\" maxlength=\"9\">\n";
	else {
		print "<select id=\"$fldname\" name=\"$fldname\" $controller>\n";
		for($ls=0;$ls<pg_num_rows($ressvc);$ls++) {
			$rowls=pg_fetch_row($ressvc,$ls);
 			$seltxt=($_POST[$fldname]==$rowls[0])?" selected ":"";
			if (empty($seltxt)){
				if ($ls==0) $seltxt="selected";
				else $seltxt="";
			}
			print "<option value=\"$rowls[0]\" $seltxt>$rowls[0] (".get_memname($rowls[0]).") </option>\n";
		}
		print "</select>\n";
	}
	
	return $GLOBALS["db2"]->doQuery($svcquerr)->getFirstRow();
}

function ms_auto_convert($xprdcd, $xqty, $xloccd, $xnote, $xcancel='') {
	global $db, $debug, $opnm, $year1, $month1, $date1,$region,$trx_date;
	
	$cbdate = substr($trx_date,0,2);
	$cbmonth = substr($trx_date,3,2);
	$cbyear = substr($trx_date,6,4);
	$trdt_date = $cbyear."/".$cbmonth."/".$cbdate;

	$querr2="select br_prefix from msms_new where br_code='$xloccd'";
	$result2=pg_exec($db,$querr2);
	if (pg_numrows($result2)>0) {
		$row2=pg_fetch_row($result2,0);
		if ($debug==1) print "$querr2<br>";
		$branch_timestamp = get_timestamp(get_timezone(TZ_COUNTRY,$region));
		$digitcb=$row2[0]."-AD";
		$Local_Date=date("ymd",$branch_timestamp);
		//$digitcb=$digitcb.fpart_date();
		$digitcb=$digitcb.$Local_Date;
		$thismonth=$year1;
		$bresult=pg_exec($db,"begin transaction");
		$squery = "select nvalue from newmsadmonth where nstart='$thismonth' and loccd='$xloccd';";
		Logger::debug("trcd admstrh:",$digitcb);
		$dresult=pg_exec($db,$squery);
		if ($debug==1) print $squery."<br>";
			if (pg_numrows($dresult)>0)	{
				list($newTransId)=pg_fetch_row($dresult,0);
				$newTransId=$newTransId+1;
				$dresult=pg_exec($db,"update newmsadmonth set nvalue=nvalue+1 where nstart='$thismonth' and loccd='$xloccd';");
				if ($debug==1) print "update newmsadmonth set nvalue=nvalue+1 where nstart='$thismonth' and loccd='$xloccd';"."<br>";
			} else {
				$dresult=pg_exec($db,"insert into newmsadmonth values('$thismonth',1,'$xloccd')");
				if ($debug==1) print "insert into newmsadmonth values('$thismonth',1,'$xloccd')<br>";
				$newTransId=1;
			}
			for ($i=0;strlen($newTransId)<=5;$i++)
				$newTransId="0" . $newTransId;
			$newTransId=$digitcb.$newTransId;
			$tdate=array_values(getdate());


			$branch_date = date("d M Y",$branch_timestamp);
			$branch_time = date("H:i:s.U",$branch_timestamp);
			$_date1=date("d",$branch_timestamp);
			$_month1=date("m",$branch_timestamp);
			$_year1=date("Y",$branch_timestamp);

			if ($xcancel=='cancel')	{
				$querr="insert into adjustment_description values
				('$newTransId','Cancel Auto convert ".-$xqty." pcs $xprdcd at $_date1/$_month1/$_year1 $xnote');";
			}else{
				$querr="insert into adjustment_description values
				('$newTransId','Auto convert $xqty pcs $xprdcd at $_date1/$_month1/$_year1 $xnote');";
			}
			if ($debug==1) print $querr."<br><br>";
			$result=pg_exec($db,$querr);
			
			/*$querr=	"insert into admstrh (trcd,trtype,code,bc_id,upcode,loccd,trdt,etdt,createnm,opnm,status,trtm) values
				('$newTransId','1','$xloccd','$region','$upcd','$xloccd','$branch_date','$branch_date','$opnm','$opnm','1','$branch_time')";*/
			$querr=	"insert into admstrh (trcd,trtype,code,bc_id,upcode,loccd,trdt,etdt,createnm,opnm,status,trtm) values
				('$newTransId','1','$xloccd','$region','$upcd','$xloccd','$trdt_date','$branch_date','$opnm','$opnm','1','$branch_time')";
			if ($debug==1) print $querr."<br>";
			$result=pg_exec($db,$querr);


			$sql_item="select trim(inv_prdcd),inv_qty from msprd_items where prdcd='$xprdcd' and cn_id='$region' ";
			if ($debug) echo("$sql_item<br>\n");
			$res_item=pg_exec($db, $sql_item);
			if (pg_num_rows($res_item)>0)	 {
				for ($r=0;$r<pg_num_rows($res_item);$r++) {
					$row_item=pg_fetch_row($res_item, $r);
					$x_qoh=hitungqoh_basic($row_item[0], $xloccd);
					$xqty_item=-($row_item[1]*$xqty);
					$query2trd ="insert into admstrd(trcd,prdcd,qty,dp,cp,sp,pv,bv) values
						('$newTransId','".$row_item[0]."',$xqty_item,0,0,0,0,$x_qoh);";
					if ($debug==1) print $query2trd."<br>";
					else $result2trd = pg_exec($db,$query2trd);
					update_inloc_basic($row_item[0],$row_item[1]*$xqty,$debug,$xloccd,0,'-');

				}
			}
			pg_free_result($res_item);

			//update inloc package +
			$x_qoh=hitungqoh_basic($xprdcd, $xloccd);
			$query2trd ="insert into admstrd(trcd,prdcd,qty,dp,cp,sp,pv,bv) values
				('$newTransId','".$xprdcd."','".$xqty."',0,0,0,0,$x_qoh);";
			if ($debug==1) print $query2trd."<br>";
			else $result2trd = pg_exec($db,$query2trd);
			update_inloc_basic($xprdcd,$xqty,$debug,$xloccd,0,'+');

			if ($debug==0) {
				$bresult=pg_exec($db,"commit transaction");
			}
	}
}

function ms_convert_reverse($xprdcd, $xqty, $xloccd, $xnote, $xcancel='') {
	global $db, $debug, $opnm, $year1, $month1, $date1;

	$querr2="select br_prefix,br_region from msms_new where br_code='$xloccd'";
	$result2=pg_exec($db,$querr2);
	$row2=pg_fetch_row($result2,0);
	if($row2[1]=="") $region = "ME";
	else $region = $row2[1];
	if ($debug==1) print "$querr2<br>";

	$localtimestamp = get_timestamp(get_timezone(TZ_COUNTRY,$region));
	$local_date=date("Y-m-d",$localtimestamp);
	$local_time=date("H:i:s.U",$localtimestamp);
	$date_prefix=date("ymd",$localtimestamp);
	$reverse_adjust_date=date("d/m/Y",$localtimestamp);
	$digitcb=$row2[0]."-AD";
	//$digitcb=$digitcb.fpart_date();
	$digitcb=$digitcb.$date_prefix;
	$thismonth=$year1;
	$bresult=pg_exec($db,"begin transaction");
	$squery = "select nvalue from newmsadmonth where nstart='$thismonth' and loccd='$xloccd';";
	$dresult=pg_exec($db,$squery);
	if ($debug==1) print $squery."<br>";

		if (pg_numrows($dresult)>0)	{
			list($newTransId)=pg_fetch_row($dresult,0);
			$newTransId=$newTransId+1;
			$dresult=pg_exec($db,"update newmsadmonth set nvalue=nvalue+1 where nstart='$thismonth' and loccd='$xloccd';");
			if ($debug==1) print "update newmsadmonth set nvalue=nvalue+1 where nstart='$thismonth' and loccd='$xloccd';"."<br>";
		} else {
			$dresult=pg_exec($db,"insert into newmsadmonth values('$thismonth',1,'$xloccd')");
			if ($debug==1) print "insert into newmsadmonth values('$thismonth',1,'$xloccd')<br>";
			$newTransId=1;
		}
		for ($i=0;strlen($newTransId)<=5;$i++)
			$newTransId="0" . $newTransId;
		$newTransId=$digitcb.$newTransId;
		$tdate=array_values(getdate());

		if ($xcancel=='cancel')	{
			$querr="insert into adjustment_description values
			('$newTransId','Cancel Auto convert ".-$xqty." pcs $xprdcd at $reverse_adjust_date $xnote');";
		}else{
			$querr="insert into adjustment_description values
			('$newTransId','Reverse Auto convert $xqty pcs $xprdcd at $reverse_adjust_date $xnote');";
		}
		if ($debug==1) print $querr."<br><br>";
		$result=pg_exec($db,$querr);

		$querr=	"insert into admstrh (trcd,trtype,code,bc_id,upcode,loccd,trdt,etdt,createnm,opnm,status,trtm) values
			('$newTransId','1','$xloccd','$region','$upcd','$xloccd','$local_date','$local_date','$opnm','$opnm','1','$local_time')";
		if ($debug==1) print $querr."<br>";
		$result=pg_exec($db,$querr);
		
		$sql_item="select trim(inv_prdcd),inv_qty from msprd_items where prdcd='$xprdcd' and cn_id='$region' ";
		if ($debug) echo("$sql_item<br>\n");
		$res_item=pg_exec($db, $sql_item);
		if (pg_num_rows($res_item)>0)	 {
			for ($r=0;$r<pg_num_rows($res_item);$r++) {
				$row_item=pg_fetch_row($res_item, $r);
				$x_qoh=hitungqoh_basic($row_item[0], $xloccd);
				$xqty_item=($row_item[1]*$xqty);
				$query2trd ="insert into admstrd(trcd,prdcd,qty,dp,cp,sp,pv,bv) values
					('$newTransId','".$row_item[0]."',$xqty_item,0,0,0,0,$x_qoh);";
				if ($debug==1) print $query2trd."<br>";
				else $result2trd = pg_exec($db,$query2trd);
				update_inloc_basic($row_item[0],$row_item[1]*$xqty,$debug,$xloccd,0,'+');

			}
		}
		pg_free_result($res_item);

		//update inloc package +
		$x_qoh=hitungqoh_basic($xprdcd, $xloccd);
		$xqty_pack = -($xqty);
		$query2trd ="insert into admstrd(trcd,prdcd,qty,dp,cp,sp,pv,bv) values
			('$newTransId','".$xprdcd."',".$xqty_pack.",0,0,0,0,$x_qoh);";
		if ($debug==1) print $query2trd."<br>";
		else $result2trd = pg_exec($db,$query2trd);
		update_inloc_basic($xprdcd,$xqty,$debug,$xloccd,0,'-');

		if ($debug==0) {
			$bresult=pg_exec($db,"commit transaction");
		}
}

function set_time($region) {
global $db, $debug, $opnm;
if($region=="") $region=$default_cnt;
$querzone="select locx,locxx,upper(localx) from localtime_setup where cnt_id='$region';";
$resultzone=pg_exec($db,$querzone);
if (pg_numrows($resultzone)>0) list($locz,$loczz,$timecnt)=pg_fetch_row($resultzone,0);
$querzone="set TIME ZONE '$locz';";
$resultzone=pg_exec($db,$querzone);
$querzone="set TIME ZONE '$loczz';";
$resultzone=pg_exec($db,$querzone);
$querzone="select to_char(now(),'dd'),to_char(now(),'mm'),to_char(now(),'yyyy');";
$resultzone=pg_exec($db,$querzone);
list($date1,$month1,$year1)=pg_fetch_row($resultzone,0);
}
	
function get_products($country_id) {
	return $GLOBALS["db2"]->doQuery("select pricelist.prdcd as product_id, p.prdnm as product_name
		from msprd_pricesetup pricelist 
		join msprd p on pricelist.prdcd=p.prdcd
		join msprd_extra pe on p.prdcd=pe.prdcd
		left join msprd_conditions mc on pricelist.prdcd=mc.prdcd and mc.cn_id='$country_id'
		where pricelist.cn_id='$country_id' and p.pdcid is null and pe.viewable='1' and pe.discontinue='0' 
		and (mc.discontinue='f' or mc.discontinue is null) and p.type<>'2' order by pricelist.prdcd")->toArray();
	}

/** for SC auto_convert product **/
function fget_st_data_CBconvert_item($xtxt_code,$xprdcode,$xtxtTgl1,$xtxtTgl2,$xopening,$xflag='in#out'){
        global $db, $deduct_prod_item,$debug,$region;
        $qsql = "select sum(b.qty)  as vqty from newsctrd b,newsctrh a where b.trcd=a.trcd and b.prdcd='$xprdcode' and a.note3='$xtxt_code' and trtype in ('1','3','4') ";

        if ($xopening==1) {
                $qsql .=" and a.trdt<to_date('$xtxtTgl1','dd/mm/yyyy') ";
        } else if ($xopening==11) {
                $qsql .=" and a.trdt<=to_date('$xtxtTgl1','dd/mm/yyyy') ";
        } else if ($xopening==12) {
                $qsql .=" and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
        }else{
                $qsql .=" and a.trdt>=to_date('$xtxtTgl1','dd/mm/yyyy') and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
        }

	if ($deduct_prod_item==1) {
                $qsql.= "union all select sum(b.qty*d.inv_qty) as vqty from newsctrh a, newsctrd b, msprd_items d
                        where a.trcd=b.trcd and a.note3='$xtxt_code' and a.trtype='1' and
                        ((auto_convert='f' and b.prdcd in
                        (select a.prdcd from msprd_items a, msprd xx
                        where a.prdcd=xx.prdcd and  a.inv_prdcd='$xprdcode' and a.cn_id='$region'
                        and a.prdcd<>'$xprdcode' and a.is_normal='1' and xx.type='2' and xx.scdp2='1'))
                        or auto_convert and b.prdcd in (select a.prdcd from msprd_items a, msprd xx
                        where a.prdcd=xx.prdcd and  a.inv_prdcd='$xprdcode' and a.cn_id='$region'
                        and a.prdcd<>'$xprdcode' and a.is_normal='1' and xx.type='2'))
                        and d.prdcd=b.prdcd and d.inv_prdcd='$xprdcode' and d.cn_id='$region' ";

                if ($xopening==1) {
                        $qsql.=" and a.trdt < to_date('$xtxtTgl1','dd/mm/yyyy') ";
                }else if ($xopening==11) {
                        $qsql.=" and a.trdt<=to_date('$xtxtTgl1','dd/mm/yyyy') ";
                }else if ($xopening==12) {
                        $qsql.=" and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
                }else{
                        $qsql.=" and a.trdt>=to_date('$xtxtTgl1','dd/mm/yyyy') and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
                }
        }
        $sql1 = "select sum(vqty) from ($qsql)  as table1 " ;
        $xres = pg_query($db, $sql1);
        $hsql   = 0;
	if(pg_num_rows($xres)>0) {
                $rowsql=pg_fetch_row($xres,0);
                $hsql   =$rowsql[0];
                pg_free_result($xres);
        }
        if ($hsql=='') $hsql=0;
        Logger::debug("SC CB => $sql1 - ($hsql)");
        return $hsql;
}

function fget_st_data_receive_convert_item($xtxt_code, $xprdcode,$xtxtTgl1,$xtxtTgl2, $xopening){
        global $db, $deduct_prod_item, $debug, $region;
        $qsql=
                "SELECT b.prdcd,sum(b.qty) as vqty
                FROM rrdistrd b,rrdistrh a
                WHERE a.code='$xtxt_code' AND b.trrrcd=a.trrrcd
                AND trim(b.prdcd)='$xprdcode' AND a.status='1'";

        if ($xopening==1) {
                $qsql.=" and a.trdt < to_date('$xtxtTgl1','dd/mm/yyyy') ";
        }else if ($xopening==11) {
                $qsql.=" and a.trdt<=to_date('$xtxtTgl1','dd/mm/yyyy') ";
        }else if ($xopening==12) {
                $qsql.=" and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
        }else{
                $qsql.=" and a.trdt>=to_date('$xtxtTgl1','dd/mm/yyyy') and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
        }
        $qsql.=" group by b.prdcd ";

	if ($deduct_prod_item==1) {
                $qsql.=
                        "union select b.prdcd,sum(b.qty*d.inv_qty) as vqty
                        from rrdistrh a, rrdistrd b, msprd_items d
                        where a.trrrcd=b.trrrcd and a.code='$xtxt_code' AND a.status='1' and
                        ((auto_convert='f' and b.prdcd in
                        (select a.prdcd from msprd_items a, msprd xx
                        where a.prdcd=xx.prdcd and  a.inv_prdcd='$xprdcode' and a.cn_id='$region'
                        and a.prdcd<>'$xprdcode' and a.is_normal='1' and xx.type='2' and xx.scdp2='1'))
                        or auto_convert and b.prdcd in (select a.prdcd from msprd_items a, msprd xx
                        where a.prdcd=xx.prdcd and  a.inv_prdcd='$xprdcode' and a.cn_id='$region'
                        and a.prdcd<>'$xprdcode' and a.is_normal='1' and xx.type='2'))
                        and d.prdcd=b.prdcd and d.cn_id='$region' and d.inv_prdcd='$xprdcode' ";

                if ($xopening==1) {
                        $qsql.=" and a.trdt < to_date('$xtxtTgl1','dd/mm/yyyy') ";
                }else if ($xopening==11) {
                        $qsql.=" and a.trdt<=to_date('$xtxtTgl1','dd/mm/yyyy') ";
                }else if ($xopening==12) {
                        $qsql.=" and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
                }else{
                        $qsql.=" and a.trdt>=to_date('$xtxtTgl1','dd/mm/yyyy') and a.trdt<=to_date('$xtxtTgl2','dd/mm/yyyy') ";
                }
                $qsql.=" group by b.prdcd ";
        }

        $sql1 = "select sum(vqty) from ($qsql)  as table1 " ;
        $rsql1=pg_query($db,$sql1);
        $hsql   = 0;

        if(pg_num_rows($rsql1)>0) {
                $rowsql=pg_fetch_row($rsql1,0);
                $hsql=$rowsql[0];
                pg_free_result($rsql1);
        }
	
	if ($hsql=='') $hsql=0;
        Logger::debug("Receive => $sql1 - ($hsql)");
        return $hsql;
}

function pack_qoh($packid,$msid,$priceid) {
	global $db2,$debug;
	
	$asql = "select a.pcprdcd,a.pricelist_id,coalesce(a.qoh,b.item_qoh) as qoh from 
		(select pcprdcd,i.qoh,p.pricelist_id from promopack p left join inloc i on i.prdcd=p.pcprdcd and i.loccd='$msid') a
		left join
		(select pcprdcd,min(coalesce(i.qoh/p.pcitem_qty,0)) as item_qoh,p.pricelist_id from promopack_item p
		left join msprd e on p.pcitem_prdcd=e.prdcd
		left join inloc i on i.prdcd=e.prdcd and i.loccd='$msid'
		group by pcprdcd,pricelist_id) b on a.pcprdcd=b.pcprdcd where a.pcprdcd='$packid' and a.pricelist_id='$priceid'";
	$pc = $db2->doQuery($asql)->getFirstRow();
	Logger::debug($pc);
	$qoh = $pc["qoh"];
	return $qoh;
}

function update_propack_inloc($packid,$packqty,$priceid,$msid){
	global $db2,$debug;
	
	$item = $db2->doQuery("select pcitem_prdcd,pcitem_qty from promopack_item where pcprdcd='$packid' and pricelist_id='$priceid'")->toArray();
	Logger::debug("Package item : ", $item);
	foreach($item as &$it) {
		$totqty = $it["pcitem_qty"]*$packqty;
		if ($db2->doUpdate("inloc",array("qoh"=>"\\qoh-$totqty"),"prdcd='{$it["pcitem_prdcd"]}' and loccd='$msid'")==0)
			$db2->doInsert("inloc",array("qoh"=>-$totqty,"prdcd"=>$it["pcitem_prdcd"],"loccd"=>$msid));
	}
}

function fget_DSP_data_item($xtxt_code, $xprdcode,$xtxtTgl1,$xtxtTgl2, $xopening){
	global $db, $vonly_positive,$opnm,$debug;
	$qsql=	"SELECT sum(b.qty) as vqty FROM msprd a,newmstrd b,newmstrh c
		WHERE b.trcd=c.trcd AND b.prdcd=a.prdcd and trtype='14' and b.prdcd='$xprdcode' ";
	if ($vonly_positive) $qsql.="and b.qty>0 ";
	if ($xtxt_code=='ALL') {
		$qsql.=" and c.loccd in (select br_code from msms_new,users_braccess where msms_new.br_code=users_braccess.brcode 
		and br_status=true and br_deleted=false and users_braccess.uname='$opnm') ";
	}else{
		$qsql.=" and c.loccd='$xtxt_code' ";
	}

	if ($xopening==1) 
		$qsql.=" and c.trdt<'$xtxtTgl1' ";
	else if ($xopening==11) 
		$qsql.=" and c.trdt<='$xtxtTgl1' ";
	else if ($xopening==12) 
		$qsql.=" and c.trdt<='$xtxtTgl2' ";
	else
		$qsql.=" and c.trdt>='$xtxtTgl1' and c.trdt<='$xtxtTgl2' ";
	
	$sql1 = "select sum(vqty) from ($qsql)  as table1 " ;
	$rsql = pg_query($db,$sql1);
	$hsql = 0;
	if(pg_num_rows($rsql)>0) {
		$rowsql=pg_fetch_row($rsql,0);
		$hsql=$rowsql[0];
		pg_free_result($rsql);
	}
	//if ('FB001'==$xprdcode) echo($sql1.'<br>');
	if ($hsql=='') $hsql=0;
	return $hsql;
}

function fget_SC_DSP_data_item($xtxt_code, $xprdcode,$xtxtTgl1,$xtxtTgl2, $xopening){
	global $db, $vonly_positive,$opnm,$debug;
	$qsql=	"SELECT sum(b.qty) as vqty FROM msprd a,newsctrd b,newsctrh c
		WHERE b.trcd=c.trcd AND b.prdcd=a.prdcd and trtype='14' and b.prdcd='$xprdcode' ";
	if ($vonly_positive) $qsql.="and b.qty>0 ";
	if ($xtxt_code=='ALL') {
		$qsql.=" and c.loccd in (select sccode from users_scaccess where uname='$opnm' and flag='1' and sccode 
		in (select code from sub_mssc where status='N')) ";
	}else{
		$qsql.=" and c.loccd='$xtxt_code' ";
	}

	if ($xopening==1) 
		$qsql.=" and c.trdt<'$xtxtTgl1' ";
	else if ($xopening==11) 
		$qsql.=" and c.trdt<='$xtxtTgl1' ";
	else if ($xopening==12) 
		$qsql.=" and c.trdt<='$xtxtTgl2' ";
	else
		$qsql.=" and c.trdt>='$xtxtTgl1' and c.trdt<='$xtxtTgl2' ";
	
	$sql1 = "select sum(vqty) from ($qsql)  as table1 " ;
	$rsql = pg_query($db,$sql1);
	$hsql = 0;
	if(pg_num_rows($rsql)>0) {
		$rowsql=pg_fetch_row($rsql,0);
		$hsql=$rowsql[0];
		pg_free_result($rsql);
	}
	//if ('FB001'==$xprdcode) echo($sql1.'<br>');
	if ($hsql=='') $hsql=0;
	return $hsql;
}

function eppay_cancel() {
	global $debug,$epoint_server,$ewpay_note,$ewpay_amt,$ewpay_code,$tmp_trcd;
	$port = 443;
	if(!empty($ewpay_amt)) {
		$path = "$epoint_server/web/epointmod/payment_cancel.php?ewno=".urlencode($ewpay_note)."&amt=".urlencode($ewpay_amt);
		$path .= "&memcode=".urlencode($ewpay_code)."&rollback_flag=1";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_PORT , $port);
		curl_setopt($ch, CURLOPT_HEADER, 0); 
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_URL, $path);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_REFERER, 'https://obs5.dxn2u.com');
		$xresult=curl_exec($ch);
		curl_close($ch);
		Logger::debug($path);
		$GLOBALS["db2"]->doUpdate("tmp_trxval",array("eppay_cancel"=>$path),"trcd='$tmp_trcd'");
	}
}

function eppay_confirm() {
	global $debug,$epoint_server,$memid,$total_payment,$newTransId,$transaction_datetime,$payment_note,$tmp_trcd;
	$port = 443;
	$path = "$epoint_server/web/epointmod/payment_confirm.php?ewno=".urlencode($payment_note)."&cbDate=".urlencode($transaction_datetime);
	$path .= "&cbno=".urlencode($newTransId)."&epMemCode=".urlencode($memid)."&amt=$total_payment";
	if($debug==0) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_PORT , $port);
		curl_setopt($ch, CURLOPT_HEADER, 0); 
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_URL, $path);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		//curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,100);
		curl_setopt($ch, CURLOPT_REFERER, 'https://obs5.dxn2u.com');
		$xresult=curl_exec($ch);
		curl_close($ch);
	} 
	Logger::debug($path);
	$GLOBALS["db2"]->doUpdate("tmp_trxval",array("eppay_confirm_send"=>$path,"eppay_confirm_return"=>$xresult),"trcd='$tmp_trcd'");
	return $xresult;
}

function format_us_phone_number($words){
	$string = preg_replace('/[^0-9]/', '', $words);
    if(strlen($string)>=10){
        $string = substr_replace($string, '-', 6, 0);
        $string = substr_replace($string, ' ', 3, 0);
        $string = substr_replace($string, ')', 3, 0);
        $string = substr_replace($string, '(', 0, 0);
    }
    return $string;
}
?>
