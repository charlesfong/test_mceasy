<?php
	$request_path = explode('/',$_SERVER["REQUEST_URI"]);
	$path_to_application = $_SERVER["DOCUMENT_ROOT"].'/'.$request_path[1].'/';
	
	set_include_path($path_to_application);
	
	include("module/global.inc");
	include("module/global2_inc.php");
	check_login();
	$debug = ($opnm=="weifen87" || $opnm=="wisnu");
	
	Logger::setLevel(LOGGER_LEVEL_DEBUG);
	if ($getdata==1) {
		$_cntarr = explode(',',$getcnt);
		if ($getcol=='ms') {
			//print $getcnt;			
			//print_r($_cntarr);
			$ms_list = new DXNMainStockistList();
			$ms_list->setId("txt_ms");
			$ms_list->setName("txt_ms");
			$ms_list->setAdditionalOptions(array(""=>"-- All --"));
			$ms_list->setOptions($ms_list->getModel()->getPerUser($opnm,$_cntarr));
			if (isset($_REQUEST["txt_ms"]))
			$ms_list->setValue($txt_ms);
			$ms_list->getComponent();
		}
		if ($getcol=='sc') {
		$sc_list = new DXNServiceCenterList();
			$sc_list->setId("txt_sc");
			$sc_list->setName("txt_sc");
			$sc_list->setAdditionalOptions(array(""=>"-- All --"));
			$sc_list->setOptions($sc_list->getModel()->getPerUser($opnm,$_cntarr));
			if (isset($_REQUEST["txt_sc"]))
			$sc_list->setValue($txt_sc);
			$sc_list->getComponent();
		}
		exit;
	}
	$_date1 = date("d");
	$_month1 = date("m");
	$_year1 = date("Y");
	Logger::debug($filterby);
	Logger::debug($pinormember);
	Logger::debug($locorint);
	$total = 0;
	$_datalist = '';
	$_filter = '';
	$dbf_file = '';
	$fresult=pg_exec($db,"set datestyle to 'POSTGRES,EUROPEAN'");
	//if ($filterby=="" || $filterby=="0") {
			$_memfilter = '';
			$cn_access = $db2->doQuery("select all_country from users_extra where uname='$opnm'")->toArray();
			Logger::debug($cn_access);
			if ($cn_access[0]['all_country']=="0") {
				$cn_access = $db2->doQuery("select iso from users_cnaccess a,country_list c where a.id_cnt=c.iso and uname='$opnm'")->toArray();
				//Logger::debug($cn_access);

				foreach($cn_access as $cx) {
					$_clstring .= "'$cx[iso]',";
				}
				$_clstring = substr($_clstring,0,-1);
				$_memfilter .= " and icountry in ($_clstring) ";
				if (count($cn_access)==1) {
					Logger::debug("1 country access : ".$cn_access[0]['iso']);
					$branch_timestamp = get_timestamp(get_timezone(TZ_COUNTRY,$cn_access[0]['iso']));
					$_date1 = date("d",$branch_timestamp);
					$_month1 = date("m",$branch_timestamp);
					$_year1 = date("Y",$branch_timestamp);
				}
			}
			$default_list = $db2->doQuery("select distinct upper(flag) as flag,type from msmemb_ioc where code<>'' $_filter $_memfilter order by type;")->toArray();
			$ii = 0;
			foreach($default_list as $d_list) {
				$_value = $d_list['flag'].":".$d_list['type'];
				if ($d_list['type']=="loc") $_label = "Local"; else $_label = "International";
				$_datalist .= "<tr bgcolor='#ffffff' align='left' valign='top'><td colspan='2' style='padding-left:5px' >&nbsp;</td><td colspan='6'><input type='radio' name='locorint' value='{$_value}'>{$_label} - {$d_list['flag']}</td></tr>";
			} 
			$_memfilter = '';
	// }
                        
        $transaction_timestamp = get_timestamp(get_timezone(TZ_COUNTRY, "US"));
        $localdate = date('m/d/Y', $transaction_timestamp);

	if ($gencsv==1) {
		$_memfilter = '';
		if (trim($cnid_list)<>'') {
			 $cl = explode(",",$cnid_list);
			 $_clstring = '';
			 foreach($cl as $cx) {
				 if ($cx<>'') $_clstring .= "'$cx',";
			 }
			 $_clstring = substr($_clstring,0,-1);
			 $_memfilter .= " and a.icountry in ($_clstring) ";
		}
		if ($filterby=="0") {
			$cn_access = $db2->doQuery("select all_country from users_extra where uname='$opnm'")->toArray();
			Logger::debug($cn_access);
			if ($cn_access[0]['all_country']=="0") {
				$cn_access = $db2->doQuery("select iso from users_cnaccess a,country_list c where a.id_cnt=c.iso and uname='$opnm'")->toArray();
				//Logger::debug($cn_access);

				foreach($cn_access as $cx) {
					if ($cx['iso']<>'') $_clstring .= "'$cx[iso]',";
				}
				$_clstring = substr($_clstring,0,-1);
				$_memfilter .= " and a.icountry in ($_clstring) ";
			}
			//$cn_access = $db2->doQuery("select iso,name from users_cnaccess a,country_list c where a.id_cnt=c.iso and uname='$opnm' order by name")->toArray();
			//Logger::debug($cn_access);
		}
		$_datefilter = '';
		if ($txt_datefrom==$txt_dateto) $_datefilter = "and to_char(a.joindt,'dd/mm/yyyy')='$txt_datefrom'";
		else $_datefilter = " and cast(a.joindt as date)>='$txt_datefrom' and cast(a.joindt as date)<='$txt_dateto'";
		$_memfilter .= $_datefilter;
		if ($pinormember==0) $_opt = "pinno";
		if ($pinormember==1) { 
			$_opt = "newmem";
			if ($iocpack<>'') $_opt .= "-".$iocpack; else  $_opt .= "-ALL";
			
			//$_optext = str_replace(":","-",$locorint);
			//$_opt .= "-".$_optext;
		}
		$_cnopt = '';
		if (trim($cnid_list)<>'') $_cnopt = str_replace("'","",$_clstring); else $_cnopt = "ALL";
		$_cnopt = "-".str_replace(",","-",$_cnopt);
		if ($pinormember==2) { 
			$_opt = "newpv";
			if ($_scorbr=='') $_opt .= "-ALL";
			if ($_scorbr=='_ms' && $txt_ms=='') $_opt .= "-ALLMS";
			if ($_scorbr=='_ms' && $txt_ms<>'') $_opt .= "-$txt_ms";
			if ($_scorbr=='_sc' && $txt_sc=='') $_opt .= "-ALLSC";
			if ($_scorbr=='_sc' && $txt_sc<>'') $_opt .= "-$txt_sc";
		}
		$filename=$opnm."-".$_opt."-".str_replace("/",'',$txt_datefrom)."-".str_replace("/",'',$txt_dateto)."$_cnopt.csv";
		$fp = fopen($path_to_application."upload/".$filename, "w");
		if ($pinormember==0) {
			$pin_list = $db2->doQuery("select b.pinno,a.code,a.icode,coalesce(trim(c.name),'')||' '||coalesce(nullif(trim(d.mid_name),''),'')||' '|| coalesce(nullif(trim(d.last_name),''),'') as name,COALESCE(d.country_code_mobile,'')||COALESCE(d.local_code_mobile,'')||COALESCE(c.tel_hp,'') AS cellphone from msmemb_ioc a, msmemb_pinno_ioc b, msmemb c, msmemb_extra d where b.code=a.code and c.code=a.code and d.code=c.code $_memfilter order by a.code,a.icode,a.joindt;")->toArray();			
			fwrite($fp, "code,name,mobile_phone,pin_number\n");
			$_tempcode = '';
			foreach($pin_list as $p_list) {
				if ($p_list['code']<>$_tempcode) {
					$_memname = $p_list['name'];
					$_memname =	preg_replace('/\s+/', ' ', $_memname);  
					fwrite($fp, "\"{$p_list['code']}\",\"{$_memname}\",\"{$p_list['cellphone']}\",\"{$p_list['pinno']}\"\n");
					Logger::debug("{$p_list['code']},{$_memname},{$p_list['cellphone']},{$p_list['pinno']}");
				}
				$_tempcode = $p_list['code'];
			}
		}
		if ($pinormember==1) {
			if (trim($locorint)<>'') {
				$_loc = explode(":",$locorint);
				if ($_loc[0]=='') $_memfilter .= " and (a.flag is null or trim(a.flag) = '') "; else $_memfilter .= " and trim(upper(a.flag))='".trim($_loc[0])."' ";
				if ($_loc[1]<>'')  $_memfilter .= " and trim(a.type)='".trim($_loc[1])."' ";
			}
			if ($iocpack<>'') $_memfilter .= " and a.prdcd='$iocpack'";
			$pin_list = $db2->doQuery("select a.*,to_char(a.joindt,'yyyy-mm-dd') as xjoindt,coalesce(trim(c.name),'')||' '||coalesce(nullif(trim(d.mid_name),''),'')||' '|| coalesce(nullif(trim(d.last_name),''),'') as name from msmemb_ioc a, msmemb c, msmemb_extra d where a.code<>'' and c.code=a.code and d.code=a.code $_memfilter order by a.code,a.icode,a.joindt;")->toArray();
			// print $_memfilter;			
			fwrite($fp, "code,name,icode,iupcode,icountry,joindt\n");
			foreach($pin_list as $p_list) {
				$_memname = $p_list['name'];
				$_memname =	preg_replace('/\s+/', ' ', $_memname);  
				fwrite($fp, "\"{$p_list['code']}\",\"".trim($_memname)."\",\"{$p_list['icode']}\",\"{$p_list['iupcode']}\",\"{$p_list['icountry']}\",\"{$p_list['xjoindt']}\"\n");
			}
		}
		if ($pinormember==2) {
			$dbf_file = str_replace("csv","dbf",$filename);
			
			$_datefilter = " and trdt>='$txt_datefrom' and trdt<='$txt_dateto'";
			if ($cnid_list<>'') {
				$_cnfilter = "and bc_id in ($_clstring)";
			}

			if ($_scorbr=='') {
				$mpv_list = $db2->doQuery("select * from (select trdt,trcd,code,upcode,npv,bc_id,to_char(trdt,'YYYYMMDD') as dbfdate,1 as MS from newmstrh_ioc where trtype='13' $_datefilter $_cnfilter union select trdt,trcd,code,upcode,npv,bc_id,to_char(trdt,'YYYYMMDD') as dbfdate,0 as MS from newsctrh_ioc where trtype='13' $_datefilter $_cnfilter) as xxx order by trdt;")->toArray();
			}
			if ($_scorbr=='_ms') {
				if ($txt_ms<>'') $_cnfilter .= " and loccd='$txt_ms' ";
				$mpv_list = $db2->doQuery("select * from (select trdt,trcd,code,upcode,npv,bc_id,to_char(trdt,'YYYYMMDD') as dbfdate,1 as MS from newmstrh_ioc where trtype='13' $_datefilter $_cnfilter) as xxx order by trdt;")->toArray();
			}
			if ($_scorbr=='_sc') {
				if ($txt_sc<>'') $_cnfilter .= " and loccd='$txt_sc' ";
				$mpv_list = $db2->doQuery("select * from (select trdt,trcd,code,upcode,npv,bc_id,to_char(trdt,'YYYYMMDD') as dbfdate,1 as MS from newsctrh_ioc where trtype='13' $_datefilter $_cnfilter) as xxx order by trdt;")->toArray();
			}

			$def = array(
			  array("date", "D"),
			  array("trcd", "C",  25),
			  array("code", "C",  9),
			  array("upcode", "C",  9),
			  array("mpv", "N", 15, 2),
			  array("cn_id", "C", 2)
			);
			if (!dbase_create($path_to_application."upload/".$dbf_file, $def)) {
				$msg = "Error, can't create the database (dbf)";
			} else {
				$dbfopen = dbase_open($path_to_application."upload/".$dbf_file, 2);
				fwrite($fp, "date,trcd,code,upcode,mpv,cn_id\n");
				foreach($mpv_list as $p_list) {
					fwrite($fp, "{$p_list['trdt']},{$p_list['trcd']},{$p_list['code']},{$p_list['upcode']},{$p_list['npv']},{$p_list['bc_id']}\n");
					if ($dbfopen) {
						 dbase_add_record($dbfopen, array("{$p_list['dbfdate']}","{$p_list['trcd']}","{$p_list['code']}","{$p_list['upcode']}","{$p_list['npv']}","{$p_list['bc_id']}"));   
					}
					//if ($debug) print "{$p_list['dbfdate']}<br>";
				}
				dbase_close($dbfopen);
			}
		}
	}
	if (isset($cnid_list)) {
		//print "$cnid_list|";
		if ($cnid_list[0]==',') $cnid_list = substr($cnid_list,1);
		//print $cnid_list;
	}
	if (isset($filterby)) {
		// unset($filterby);
	}
	if (isset($pinormember)) {
		//unset($pinormember);
	}
		/*
	if (isset($cbo_status)) {
		if ($_REQUEST["cbo_status"]<>'a') $status_filter = " and status='{$_REQUEST["cbo_status"]}'";
	}
	if (isset($cbo_country)) {
		$query_prod = "select * from msprd_ioc where prdcd<>'' $country_filter $status_filter order by prdcd asc;";
		$result = pg_exec($db,$query_prod);
		$total = pg_numrows($result);
	}*/
	
	check_access_page();
	$r = array(
		"page.title" => "Download Data (IOC)",
		"label.header" => "Download Data",
		"label.filter" => "Filter By",
		"label.country" => "Country",
		"label.allcountry" => "All Country",
		"label.datefrom" => "Date From",
		"label.pin" => "Pin Number",
		"label.new_member" => "New Member",
		"label.local" => "Local",
		"label.international" => "International",
		"label.status" => "Status",
		"label.prefix" => "Prefix",
		"label.all" => "All",
		"label.active" => "Active",
		"label.inactive" => "Inactive",
		"label.pkgid" => "Package ID",
		"label.pkgname" => "Package Name",
		"label.price" => "Price",
		"label.package_id" => mxlang(893)." ".mxlang(2979),
		"label.pv" => mxlang(2893),
		"label.sv" => mxlang(2894),
		"label.new_salespv" => "New Sales PV",
		"button.process" => "Process",
		"button.submit" => mxlang(19),
		"button.cancel" => mxlang(20),
		"button.delete" => mxlang(396),
		"item.select_country"=>mxlang(1496),
		"alert.country" => "Please select country!",
		"alert.value" => "Value required!"
	);
	$use_prototype_161 = true;
	include("module/header.inc");
	
?>
<link href="../jquery-ui-1.12.1/jquery-ui.css" rel="stylesheet">
<script src="../jquery-ui-1.12.1/jquery-ui.js"></script>
<style>
#product {
						border:solid 1px;
						width:600px;
						padding:5px;
						margin:5px;
						position:absolute;
						top:100px;
						background-color:white;
					}
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
</style>
<script language="JavaScript">
Ajax.Responders.register({
					onCreate: function() { $('addloading').show(); },
					onComplete: function() {
						if (Ajax.activeRequestCount==0)
							$('addloading').hide();
					}
				});

var items_country=new Array();

function get_list_data(xyz) {
	$('addloading').show();
	// alert(xyz);
	var url = "../ioc/get_locorint.php";
		new Ajax.Request(url,{
		parameters:{cn_list:$F('cnid_list'),xchecked:xyz},
			onSuccess:function(transport) {
				if (!transport.responseText.blank()) {
					country_info = transport.responseText.evalJSON();
				}							
			},
			onComplete: function(transport){
				if (200 == transport.status) {
					if (!transport.responseText.blank()) {
						country_info = transport.responseText.evalJSON();
						$('type_list').update(country_info);
						$('addloading').hide();
					}
				}
			}
		});
}

function process_down() {
	$('gencsv').value = '1';
	if (document.frm_ioc_prod.filterby[1].checked==true && items_country.length==0) {
		 alert('Please select country!');
		 return false;
	}
	document.frm_ioc_prod.submit();
}

function cert_country() {
	if (document.frm_ioc_prod.pinormember[1].checked==true) {
		$('type_list').update('');
		document.frm_ioc_prod.cnid_list.value = '';
	}
}

function all_country() {
	items_country.clear();
	$('cl_list').update('');
	document.frm_ioc_prod.cnid_list.value = '';
	if (document.frm_ioc_prod.pinormember[1].checked==true) {
		$('type_list').update("<?=$_datalist?>");
	} else $('type_list').update('');
}

function pin_member() {
	$('urldl').update();
	// alert('xxx 1');
	if (document.frm_ioc_prod.pinormember[0].checked==true) {
		$('type_list').update('');
	}
	if (document.frm_ioc_prod.pinormember[2].checked==true) {
		$('type_list').update('');
	}
	if (document.frm_ioc_prod.pinormember[1].checked==true) {
		if (document.frm_ioc_prod.filterby[0].checked==true) {
			$('type_list').update("<?=$_datalist?>");
		}
		if (document.frm_ioc_prod.filterby[1].checked==true) {
			//alert('xxx');
			// $('type_list').update("<?=$_datalist?>");
			get_list_data('');
		}
	}
}

function add_clist(cn_id,xxx) {

	$('addloading').show();
	$('add_country').disable();
	var arrcn = document.frm_ioc_prod.cnid_list.value;
	tmp_country = arrcn.split(",");		
	if (tmp_country.length>0) {
		//alert(tmp_country.length);
		items_country = tmp_country;
	}
	var cek_exist = items_country.indexOf(items_country.find(function(prod) {
		return prod==cn_id;
	}));
	// cek_exist = -1;
	var insert_position = items_country.indexOf(
			items_country.find(function(existing_prd) {return existing_prd>cn_id})
		);
	if (cek_exist==-1) {		
		if (insert_position==-1) insert_position = items_country.length;
		items_country.splice(insert_position,0,cn_id);
	} else {
		if (xxx=='add') {
			if (cn_id=='') alert('Please select country!'); else alert(cn_id+' found in the list'); 
			} else {
			items_country.splice(cek_exist,1);
		}
	}
	var _text= '';
	for(var i=0; i<items_country.length; ++i) {
		if (items_country[i]!='') _text = _text + items_country[i] + ' [<a href="#'+items_country[i]+'" onClick="add_clist(\''+items_country[i]+'\',\'del\')">del</a>] ';
	}
	var cek_exist = items_country.indexOf(items_country.find(function(prod) {
		return prod==cn_id;
	}));

	document.frm_ioc_prod.cnid_list.value = items_country;
	// $('messages').update(items_country);
	$('cl_list').update(_text);
	if (document.frm_ioc_prod.pinormember[1].checked==true) {
		get_list_data('');
	} else $('addloading').hide();
	$('add_country').enable();

	new Ajax.Updater('_ms','ioc_download_data_admin.php',{parameters:{getdata: '1', getcol: 'ms', getcnt: items_country.toString() }});
	new Ajax.Updater('_sc','ioc_download_data_admin.php',{parameters:{getdata: '1', getcol: 'sc', getcnt: items_country.toString() }});
	// $('_ms').update(items_country);
	// _sc _ms	
}

document.observe("dom:loaded", function() {
  // initially hide all containers for tab content
  // alert('<?=$_scorbr?>');
  $('_scorbr').value = '<?=$_scorbr?>';
  $('<?=$_scorbr?>').show();
});

</script>
<table width="790" border="0" align="center" cellspacing="0" cellpadding="0">
	<tr>
		<td width="50%" valign="middle">
			<b><img src="../images/company.gif" hspace="2" vspace="2" align="absmiddle" > <?=$r["page.title"]?> (Admin)</b>
		</td>
		<td align="right" id="messages" class="red"><?=$msg?></td>
	</tr>
</table>
<div id="product" style="display:none;"></div>
<form id="frm_ioc_prod" name="frm_ioc_prod" method="post">
<input type="hidden" id="msg" name="msg" value="">
<input type="hidden" id="gencsv" name="gencsv">
<input type="hidden" id="cnid_list" name="cnid_list" value="<? if (isset($cnid_list)) print $cnid_list; ?>">
<table border="0" cellpadding="2" bgcolor="#CCCCCC" cellspacing="1" align="center" width="790">
		<tr bgcolor="#e6e6e6" align="center" valign="top">
			<td colspan="8" style="font-weight:bold"><?=$r["label.header"]?><span id="addloading" style="display:none;"><img style="float : right; margin-top: 1px;" width="18" src="../images/loading.gif" alt="..." /></span></td>
		</tr>
		<tr bgcolor="#ffffff" align="left" valign="top">
			<td rowspan="2" colspan="2" style="padding-left:5px" nowrap><label><?=$r["label.filter"]?></label></td>
			<td colspan="6" style="padding-left:4px"><input type="radio" onClick="all_country()" name="filterby" value="0" <? if ($filterby=="" || $filterby=="0") print "checked"; ?>> <?=$r["label.allcountry"]?></td>			
		</tr>
		<tr bgcolor="#ffffff" align="left" valign="top">
			<td colspan="6" style="padding-left:4px"><input type="radio" onClick="cert_country()" name="filterby" value="1" <? if ($filterby=="1") print "checked"; ?>> <?
					$countrylist = new DXNCountryList();
					$countrylist->setId("cbo_country");
					$countrylist->setName("cbo_country");
					$countrylist->setAdditionalOptions(array(""=>"-{$r["item.select_country"]}-"));
					//$countrylist->setOptions($countrylist->getModel()->getPerUser($opnm));
					$accessible = $countrylist->getModel()->getPerUser($opnm);
					$countrylist->setOptions($accessible["countries"]);
					// $countrylist->setValue($_REQUEST["cbo_country"]);
					// $countrylist->setOnChange("submit()");
					// $countrylist->setClass("mandatory");
					$countrylist->getComponent();
			?> <input type="button" id="add_country" name="add_country" onClick="if (document.frm_ioc_prod.filterby[1].checked==true) add_clist(document.frm_ioc_prod.cbo_country.value,'add');" value="Add"><ul id="cbo_country-errors" class="error-messages" style="margin:0;padding-left:15px;color:red"></ul><div id="cl_list"><?
			if (isset($cnid_list) && trim($cnid_list)<>'') {
				$items_country = explode(",",$cnid_list);
				foreach ($items_country as $value) {
					print "$value [<a href=\"#$value\" onClick=\"add_clist('$value','del')\">del</a>] ";
				}
			}
			?>
			</div></td>
		</tr>
		<tr bgcolor="#ffffff" align="left" valign="top">
			<td colspan="2" style="padding-left:5px" nowrap><label><?=$r["label.datefrom"]?></label></td>
                        <td colspan="2" style="padding-left:4px">
                            <input class="date" type="text" id="txt_datefrom" id="txt_datefrom" name="txt_datefrom" placeholder="MM/YYYY" value="<?= isset($txt_datefrom) ? $txt_datefrom : $localdate ?>" size="10" maxlength="10" style="text-align: center" readonly>
                        </td>
<!--			<td colspan="2" style="padding-left:4px" ><input type="text" size="12" maxlength="10" name="txt_datefrom" value="<? if (isset($txt_datefrom)) print $txt_datefrom; else print "$_date1/$_month1/$_year1"?>">
						<a href="javascript:void(0)" onClick="gfPop.fPopCalendar(frm_ioc_prod.txt_datefrom);return false;" HIDEFOCUS> <img src="../images/cal_show.gif" border="0"></a></td>-->
			<td style="padding-left:4px">To</td>
                        <td colspan="2" style="padding-left:4px">
                            <input class="date" type="text" id="txt_dateto" id="txt_dateto" name="txt_dateto" placeholder="MM/YYYY" value="<?= isset($txt_dateto) ? $txt_dateto : $localdate ?>" size="10" maxlength="10" style="text-align: center" readonly>
                        </td>
<!--			<td colspan="3" style="padding-left:4px" width="60%"><input type="text" size="12" maxlength="10" name="txt_dateto" value="<? if (isset($txt_dateto)) print $txt_dateto; else print "$_date1/$_month1/$_year1"?>">
						<a href="javascript:void(0)" onClick="gfPop.fPopCalendar(frm_ioc_prod.txt_dateto);return false;" HIDEFOCUS> <img src="../images/cal_show.gif" border="0"></a></td>-->
		</tr>
		<tr bgcolor="#ffffff" align="left" valign="top" style="display: none;">
			<td colspan="8"><input type="radio" name="pinormember" onClick="pin_member();" value="0" <? if ($pinormember=="0") print "checked"; ?>> <?=$r["label.pin"]?></td>
		</tr>
		<tr bgcolor="#ffffff" align="left" valign="top">
			<td ><input type="radio" name="pinormember" onClick="pin_member();" value="1" <? if ($pinormember=="" || $pinormember=="1") print "checked"; ?>> <?=$r["label.new_member"]?></td>
			<td colspan="7"><select id="iocpack" name="iocpack"><option value=""><?="-- ".$r["label.all"]." --"?></option>
			<?
			$prdlist = $db2->doQuery("select distinct prdcd from msprd_ioc order by prdcd asc;")->toArray();
			$ii = 0;
			foreach($prdlist as $prd_list) {
				if ($iocpack==$prd_list['prdcd']) $_sel = "selected"; else $_sel = '';
				print "<option value=\"$prd_list[prdcd]\" $_sel>$prd_list[prdcd]</option>";
			}
			?>
			</select></td>
		</tr>		
		<thead>
		<tr bgcolor="#ffffff" align="left" valign="top" style="display:none;">
			<td colspan="2"></td>
			<td colspan="6"></td>
		</tr>
		</thead>
		<tbody id="type_list" style="display: none;">
		<? /*
		
		*/ ?>
		</tbody>
		<tr bgcolor="#ffffff" align="left" valign="top">
			<td nowrap><input type="radio" name="pinormember" onClick="pin_member();" value="2" <? if ($pinormember=="2") print "checked"; ?>> <?=$r["label.new_salespv"]?></td>
			<td colspan="7">
			<div id="scorbr" style="float: left; padding-right: 5px;">
			<select id="_scorbr" name="_scorbr" onchange="$('_sc').hide(); $('_ms').hide(); if (this.value!='') $(this.value).show(); ">
			<option value="">-- All --</option>
			<option value="_ms">Main Stockist</option>
			<option value="_sc">Service Center</option>
			</select></div>
			<div id="_ms" style="float: left; display: none;"><? 
			$ms_list = new DXNMainStockistList();
			$ms_list->setId("txt_ms");
			$ms_list->setName("txt_ms");
			$ms_list->setAdditionalOptions(array(""=>"-- ".$r["label.all"]." --"));
			$ms_list->setOptions($ms_list->getModel()->getPerUser($opnm,array('BD','HU')));
			//$seller_list->setOnChange("seller_onchange();");
			if (isset($_REQUEST["txt_ms"]))
			$ms_list->setValue($txt_ms);
			$ms_list->getComponent();
			?></div>
			<div id="_sc" style="float: left; display: none;"><? 
			$sc_list = new DXNServiceCenterList();
			$sc_list->setId("txt_sc");
			$sc_list->setName("txt_sc");
			$sc_list->setAdditionalOptions(array(""=>"-- ".$r["label.all"]." --"));
			$sc_list->setOptions($sc_list->getModel()->getPerUser($opnm));
			//$seller_list->setOnChange("seller_onchange();");
			if (isset($_REQUEST["txt_sc"]))
			$sc_list->setValue($txt_sc);
			$sc_list->getComponent();
			?></div></td>
		</tr>
		<tr bgcolor="#e6e6e6" align="left" valign="top">
			<td colspan="8" style="font-weight:bold"><input type="button" name="add" value="<?=$r["button.process"]?>" onClick="process_down()"> <input type="button" name="cancel" value="<?=$r["button.cancel"]?>" onClick="location.href='../member/index.php'"><span id="addloading" style="display:none;"><img style="float : right; margin-top: 1px;" width="18" src="../images/loading.gif" alt="..." /></span></td>
		</tr>
		<div id="urldl">
		<? if ($gencsv==1) { 
		//$filename=$opnm."-SC_commission_detail-".$txt_Year.$cbo_month.".txt";
		
		?>
		<tr bgcolor="#ffffff" align="left" valign="top">
			<td colspan="8" style="font-weight:bold">Click here to get file : <a href="../module/get_filedata.php?type=csv&file_name=<?=$filename?>"><?=$filename?></a> (<?=filesize($path_to_application."upload/$filename")." Bytes"?>)
			<? if ($dbf_file<>'') {?><br>Click here to get file : <a href="../module/get_filedata.php?type=dbf&file_name=<?=$dbf_file?>"><?=$dbf_file?></a> (<?=filesize($path_to_application."upload/$dbf_file")." Bytes"?>)
			<? } ?>
			</td>
		</tr>	
		<? }?>
		</div>
</table>
<script>
    jq(function () {
        var ds = new Date();
        ds.setMonth(ds.getMonth() - 1);
        ds.setDate("01");
        console.log(ds);
        jq(".date").datepicker({dateFormat: "mm/dd/yy", showButtonPanel: true, changeMonth: true, changeYear: true, showOn: "button", buttonImage: "../images/cal_show.gif", buttonImageOnly: true});
    });
</script>
</form>
<iframe width="174" height="189" name="gToday:normal:../jsfile/agenda.js" id="gToday:normal:../jsfile/agenda.js" src="../jsfile/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; left:-500px; top:0px;"></iframe>
<script language="JavaScript">
<? 
	//if ($filterby=="" || $filterby=="0") {  filterby
?>
	if (document.frm_ioc_prod.filterby[0].checked==true && document.frm_ioc_prod.pinormember[1].checked==true) {
		all_country();
		//alert('ddd');
	} 
	if (document.frm_ioc_prod.filterby[1].checked==true && document.frm_ioc_prod.pinormember[1].checked==true) {
		get_list_data('<?=$locorint?>');
		//alert('xxx');
	}
<?	// } 
?>
</script>
<?php
	if ($debug) { 
		include("module/postdebug.inc");
	}
	include("module/footer.inc");
?>