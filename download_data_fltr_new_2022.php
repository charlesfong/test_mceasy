<?php
	$request_path = explode('/',$_SERVER["REQUEST_URI"]);
	$path_to_application = $_SERVER["DOCUMENT_ROOT"].'/'.$request_path[1].'/';
	
	set_include_path($path_to_application);
	
	include("module/global.inc");
	include("module/global2_inc.php");
	$debug = 0;
	if ($opnm=='nanang' || $opnm=='charles') $debug=1;
	check_login();
	$shouldpriv=2;
	check_access_page();
//	$fresult=pg_exec($db,"set datestyle to 'POSTGRES,EUROPEAN'");
        $transaction_timestamp = get_timestamp(get_timezone(TZ_COUNTRY,'US'));
	
	include("module/header.inc");
	
	$r = array(
		"label.all_country"=>mxlang(1301),
		"label.date_from"=>mxlang(211),
		"label.date_to"=>mxlang(212),
		"item.please_select_country"=>mxlang(1496),
		"label.filter_by"=>mxlang(685),
		"button.add"=>mxlang(465),
		"label.no"=>mxlang(1755),
		"label.country_name"=>mxlang(1228),
		"label.option"=>mxlang(4043),
		"label.new_member"=>mxlang(4093),
		"label.edited_member"=>mxlang(4395),
		"label.foreign_member"=>mxlang(4396),
		"label.change_upcode"=>mxlang(4397),
		"label.new_sales"=>mxlang(4398),
		"label.maintain_sales"=>mxlang(4399),
		"label.invoice"=>mxlang(255),
		"label.staff_receipt"=>mxlang(2838),
		"label.product_master"=>mxlang(4421),
		"label.sv"=>mxlang(2894),
		"label.exchange_rate"=>mxlang(982),
		"label.normal"=>mxlang(191),
		"label.all"=>mxlang(934),
		"label.address"=>mxlang(24),
		"label.email"=>mxlang(1319),
		"label.dob"=>mxlang(43),
		"label.contact"=>mxlang(2025)
	);
?>
<script src="../module/prototype_151.js"></script>
<script src="../module/dxn_mlm.js"></script>
<script language='Javascript'>
	var countries = new Array();

	function validateInput() {
//		if ($('choosed_cnt').checked &&  $('country_table')==null) {
//			alert("Please select country to download");
//			return false;
//		} else {
			var checked_buttons = 0;
			$$('input[name="rdo_dl"]').each(
				function (button) {
					if (button.checked)
						checked_buttons++;
				}
			);
			
			if (checked_buttons==0) {
				alert("Please select one data type to download");
				return false;
			}
			else {
				return true;
			}
//		}

//		if ($('choosed_cnt').checked){
//		  if ($('country_table')==null) {
//				alert('Please select one or more countries for this user !');
//				return false;
//			}
//		}
	}
	
	function refresh_options(){
//		var i, strY;
//		strY = "";
//	
//		for (i=0;i<arCountry.length;i++)
//			strY += arCountry[i]+",";
//		
//		$('selected_cnt').value = strY.substr(0,strY.length-1);

		new Ajax.Updater('locators','../module/get_location_for_download.php',{parameters:{country_id:'US',show_wh:($('rdoInvoice').checked)?'1':'0'}});
	}

	function refresh_options2(){
		new Ajax.Updater('locatorsx','../module/get_location_for_download2.php',{parameters:{country_id:'US',show_wh:($('rdoInvoice').checked)?'1':'0'}});
	}

	function refresh_location() {
		switch ($F('location_type')) {
			case '1':
				$('cbo_mainstockist').show();
				$('cbo_scenter').hide();
				break;
			case '2':
				$('cbo_mainstockist').hide();
				$('cbo_scenter').show();
				break;
			case '3':
				$('cbo_mainstockist').hide();
				$('cbo_scenter').hide();
				break;
		}
	}

	function refresh_location2() {
		switch ($F('location_type2')) {
			case '1':
				$('cbo_ms').show();
				$('cbo_sc').hide();
				break;
			case '2':
				$('cbo_ms').hide();
				$('cbo_sc').show();
				break;
			case '3':
				$('cbo_ms').hide();
				$('cbo_sc').hide();
				break;
		}
	}

	function show_options(placeholder,placeholder2,warehouse_option,newmember_option,newsales_option,maintainsales_option) {
		newsales_option = typeof(newsales_option) != 'undefined' ? newsales_option : false;
		maintainsales_option = typeof(maintainsales_option) != 'undefined' ? maintainsales_option : false;
		show_mem_options(false);

		if (newsales_option)
			$$('tr.new_sales_options').invoke('show');
		else
			$$('tr.new_sales_options').invoke('hide');

//		if (maintainsales_option)
//			$$('tr.maintain_sales_options').invoke('show');
//		else
//			$$('tr.maintain_sales_options').invoke('hide');

// 		if (placeholder2=="null") $(placeholder).appendChild($("locators")); 
// 		else {
			$(placeholder).appendChild($("locators"));
//			$(placeholder2).appendChild($("locatorsx"));
// 		}

		if (newmember_option)
			$$('tr.new_member_options').invoke('show');
		else
			$$('tr.new_member_options').invoke('hide');

//		if (!$F('cbo_country').blank()) {
//			if (warehouse_option)
//				$('wh_option').show();
//			else {
//				if ($('wh_option')) {
//					if ($('wh_option').selected) {
//						$('ms_option').selected=true;
//						$('cbo_mainstockist').show();
//					}
//					$('wh_option').hide();
//				}
//			}
//		}
	}

	function show_mem_options(edited_member) {
		if (edited_member) {
			show_options("dummy_placeholder","dummy_placeholderx",false,false);
			$$('tr.edited_member_options').invoke('show');
		} else
			$$('tr.edited_member_options').invoke('hide');
		
	}
	  
	function Add_Country(){
		if ($('choosed_cnt').checked){
			add_table('Country Name','countries',countries,$F('cbo_country'),$('cbo_country').options[$('cbo_country').selectedIndex].text,'tbl_country','country_table');
		}
	}

	function refresh_alloption(choice){
		if (choice == 1){
			if (arCountry.length > 0){
				for(var i=0;i < arCountry.length;i++)
					del_table('Country Name','countries',arCountry,i,'tbl_country','country_table');
			}

			$('divShowSelectedCnt').hide();

			new Ajax.Updater('locators','../module/get_location_for_download.php',{parameters:{country_id:'',show_wh:($('rdoInvoice').checked)?'1':'0'}});
			
			new Ajax.Updater('locatorsx','../module/get_location_for_download2.php',{parameters:{country_id:'',show_wh:($('rdoInvoice').checked)?'1':'0'}});
		}else{
			$('divShowSelectedCnt').show();
			refresh_options();
			refresh_options2();
		}
	}
        
        document.addEventListener("DOMContentLoaded", function(event) { 
            //do work
            refresh_options();
            refresh_options2();
          });
</script>
<table width="790" border="0" align="center" cellspacing="0" cellpadding="0">
	<tr>
		<td width="50%" valign="middle"><b><img src="../images/products.gif" width="18" height="18" hspace="2" vspace="2" align="absmiddle" >&nbsp;<?=mxlang("1311")?></b></td>
		<td align="right" class="red"><?=$msg?><!--[ debug code :  cmd=<?//=$cmd?>, next=<?//=$next?>, me=<?//=$menext?> ]--></td>
	</tr>
</table>
<form name="frm_cbcnt_fltr" method="post" action='download_data_new.php?new_mo=true' onsubmit="return validateInput()">
	<input type='hidden' id='dsp_simp' name='dsp_simp' value="<?=$dsp_simp?>">
	<span id="dummy_placeholder" style="display:none"><span id="locators"></span></span>
	<span id="dummy_placeholderx" style="display:none"><span id="locatorsx"></span></span>
  <table width="790" border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC" align="center">
		<tr>
    	<td bgcolor="#e6e6e6" align="center" colspan="6"><b><?=mxlang("1311")?></b></td>
    </tr>
                <tr bgcolor="white">
			<td align="left" width="25%" valign="top">Country</td>
			<td align="left" colspan="3">United States</td>
		</tr>
		<tr bgcolor="white">
			<td valign="middle"><?=$r["label.date_from"]?></td>
			<td align="left" width="20%">
				<input type="text" name="txtTgl1" value="<?=($txtTgl1=="")? date("m/d/Y"):$txtTgl1?>" size="12" maxlength="10">
				<a href="javascript:void(0)" onClick="gfPop.fPopCalendar(frm_cbcnt_fltr.txtTgl1);return false;" hidefocus>
					<img src="../images/cal_show.gif" border="0">
				</a>
			</td>
			<td align="left" valign="middle" width="5%"><?=$r["label.date_to"]?></td>
			<td align="left">
				<input type="text" name="txtTgl2" value="<?=($txtTgl2=="")? date("m/d/Y"):$txtTgl2?>" size="12" maxlength="10">
				<a href="javascript:void(0)" onClick="gfPop.fPopCalendar(frm_cbcnt_fltr.txtTgl2);return false;" hidefocus>
					<img src="../images/cal_show.gif" border="0">
				</a>
			</td>
		</tr>
		<tr bgcolor="white">
			<td colspan='4'>
				<input type='radio' name='rdo_dl' value='0' onclick='show_options("dummy_placeholder","dummy_placeholderx",false,true);'>
				<?=$r["label.new_member"]?> (based on Join Date)</td>
		</tr>
		
		<tr bgcolor="white">
			<td colspan='4'>
				<input type='radio' name='rdo_dl' value='1' onclick='show_options("newsales_selector","newsales_selector2",false,false,true,false);'>
				<?=$r["label.new_sales"]?>
			</td>
		</tr>
		<tr class="new_sales_options" bgcolor='white' style="display:none">
			<td align='right'><input type='radio' name='rdo_sales' value='0' checked></td>
			<td align='left' colspan='3'>&nbsp; <?=$r["label.normal"]?> &nbsp;<span id="newsales_selector"></span></td>
		</tr>
<!--		<tr class="new_sales_options" bgcolor='white' style="display:none">
			<td align='right'><input type='radio' name='rdo_sales' value='1'></td>
			<td align='left' colspan='3'>&nbsp; <?="{$r["label.sv"]} * {$r["label.exchange_rate"]}"?> &nbsp;<span id="newsales_selector2"></span></td>
		</tr>-->
                
                <tr bgcolor="white" align="left" valign="top">
			<td ><input type="radio" name="rdo_dl" value="2">&nbsp; <?=$r["label.new_member"]?> (IOC)</td>
			<td colspan="3"><select id="iocpack" name="iocpack"><option value=""><?="-- ".$r["label.all"]." --"?></option>
			<?
			$prdlist = $db2->doQuery("select distinct prdcd from msprd_ioc where status=true order by prdcd asc;")->toArray();
			$ii = 0;
			foreach($prdlist as $prd_list) {
				if ($iocpack==$prd_list['prdcd']) $_sel = "selected"; else $_sel = '';
				print "<option value=\"$prd_list[prdcd]\" $_sel>$prd_list[prdcd]</option>";
			}
			?>
			</select></td>
		</tr>
                <tr bgcolor="white" align="left" valign="top">
			<td nowrap><input type="radio" name="rdo_dl" value="3">&nbsp; <?=$r["label.new_sales"]?> (IOC)</td>
			<td colspan="3">
			<div id="scorbr" style="float: left; padding-right: 5px;">
			<select id="_scorbr" name="_scorbr" onchange="$('_sc').hide(); $('_ms').hide(); if (this.value!='') $(this.value).show(); ">
			<option value="">-- All --</option>
			<option value="_ms">Main Stockist</option>
			<option value="_sc">Service Center</option>
			</select></div>
			<div id="_ms" style="float: left; display: none;"><?php 
			$ms_list = new DXNMainStockistList();
			$ms_list->setId("txt_ms");
			$ms_list->setName("txt_ms");
			$ms_list->setAdditionalOptions(array(""=>"-- ".$r["label.all"]." --"));
			$ms_list->setOptions($ms_list->getModel()->getPerUser($opnm));
			//$seller_list->setOnChange("seller_onchange();");
                        
//			if (isset($_REQUEST["txt_ms"]))
//			$ms_list->setValue($txt_ms);
			$ms_list->getComponent();
			?></div>
			<div id="_sc" style="float: left; display: none;"><?php 
			$sc_list = new DXNServiceCenterList();
			$sc_list->setId("txt_sc");
			$sc_list->setName("txt_sc");
			$sc_list->setAdditionalOptions(array(""=>"-- ".$r["label.all"]." --"));
			$sc_list->setOptions($sc_list->getModel()->getPerUser($opnm));
			//$seller_list->setOnChange("seller_onchange();");
                        
//			if (isset($_REQUEST["txt_sc"]))
//			$sc_list->setValue($txt_sc);
			$sc_list->getComponent();
			?></div></td>
		</tr>
		<tr bgcolor="white" align="left" valign="top">
			<td colspan="4"><input type="radio" name="rdo_dl" value="6">&nbsp; New Sales PV</td>
		</tr>
		<tr bgcolor="white" align="left" valign="top">
			<td colspan="4"><input type="radio" name="rdo_dl" value="5">&nbsp; <?=$r["label.new_sales"]?> (Retail)</td>
		</tr>
		<input type='hidden' id='rdoInvoice'>

<!--		<tr bgcolor="white">
			<td colspan='4'><input type='radio' name='rdo_dl' value='4' onclick='show_options("dummy_placeholder","dummy_placeholderx",false,false);'>
			<?=$r["label.product_master"]?></td>
		</tr>-->

		<tr> 
			<td align="left" bgcolor="#e6e6e6" colspan="4">
				<input type="submit" name="btnOK" value="<?=mxlang("267")?>">
				<input type="button" name="btnCancel" value="<?=mxlang("20")?>" onClick="location.href='../member/index.php'">
			</td>
		</tr>
	</table>
</form>

<?
	function list_($xms) {
		global $db,$rdo_loccd0, $rdo_loccd1, $opnm, $rdo_local;
		
		/**
		* block-commented. local sales only for malaysia.
		*/
		
		if (FALSE) {
?>
		<tr bgcolor='white'>
			<td align='right'>&nbsp;<input type='radio' name='rdo_local' value='0' <?=($rdo_local==0)?' checked':''?>></td>
			<td align='left'>&nbsp;Local :</td>
			<td align='right'>&nbsp;<input type='radio' name='rdo_loccd0' value='0' <?=($rdo_loccd0==0)?' checked':''?>></td>
			<td align='left'>&nbsp;<?=mxlang("37")?> :</td>
			<td colspan='3'>
			<select id='cbo_branch0' name='cbo_branch0'>
				<?
					include("module/get_mainstockist_peruser.php");
				?>
			</select>
			</td>
		</tr>
		<tr bgcolor='white'>
			<td align='left' colspan='2'>&nbsp;</td>
			<td align='right'>&nbsp;<input type='radio' name='rdo_loccd0' value='1'  <?=($rdo_loccd0==1)?' checked':''?>></td>
			<td align='left'>&nbsp;<?=mxlang("59")?> :</td>
			<td colspan='3'>
			<select name='cbo_stockist0'>
				<option value='x'>-<?=mxlang("934")?>-</option>
		<?
				$xsql=
					"select b.code, b.name 
					from sub_mssc_extra a,msmemb b 
					where a.scname=b.code	order by b.code";
				$xres=pg_exec($db, $xsql);
				if (pg_num_rows($xres)>0) {
					for ($xi=0;$xi<pg_num_rows($xres);$xi++) {
						$xrow = pg_fetch_row($xres, $xi);
						echo("<option value='$xrow[0]'>$xrow[0] - ".stripslashes($xrow[1])."</option>\n");
					}
				}
				pg_free_result($xres);
		?>
			</select>
			</td>
		</tr>
		<?} ?>
		
		<tr bgcolor='white'>
			<td align='right'>&nbsp;<input type='radio' name='rdo_loccd1' value='0' <?=($rdo_loccd1==0)?' checked':''?>></td>
			<td align='left'>&nbsp;<?=mxlang("37")?> :<input type='radio' name='rdo_local' value='1' checked style="display:none"></td>
			<td colspan='4'>
				<select id='cbo_branch1' name='cbo_branch1' style='display:none'>
					<?
						include("module/get_mainstockist_peruser.php");
					?>
				</select>
			</td>
		</tr>
		<tr bgcolor='white'>
			
			<td align='right'>&nbsp;<input type='radio' name='rdo_loccd1' value='1'  <?=($rdo_loccd1==1)?' checked':''?>></td>
			<td align='left'>&nbsp;<?=mxlang("59")?> :</td>
			<td colspan='4'>
				<select id='cbo_stockist1' name='cbo_stockist1' style='display:none'>
					<?
						include("module/get_servicecenter_peruser.php");
					?>
				</select>
			</td>
		</tr>
<?
	}
	function list_branch() {
		global $db,$rdo_loccd, $opnm;
?>
		<tr bgcolor='white'>
			<td align='right'>&nbsp;<input type='radio' name='rdo_loccd' value='0' <?=($rdo_loccd==0)?' checked':''?>></td>
			<td align='left'>&nbsp;<?=mxlang("37")?> :</td>
			<td colspan='4'>
			<select id='cbo_branch' name='cbo_branch' style='display:none'>
				<?
					include("module/get_mainstockist_peruser.php");
				?>
			</select>
			</td>
		</tr>
<?
	}

	function list_stockist($xms) {
		global $db,$rdo_loccd;
?>
		<tr bgcolor='white'>
			<td align='right'>&nbsp;<input type='radio' name='rdo_loccd' value='1'  <?=($rdo_loccd==1)?' checked':''?>></td>
			<td align='left'>&nbsp;<?=mxlang("59")?> :</td>
			<td colspan='4'>
			<select id='cbo_stockist' name='cbo_stockist' style='display:none'>
				<?
					include("module/get_servicecenter_peruser.php");
				?>
			</select>
			</td>
		</tr>
<?
	}

	function list_department() {
		global $db,$rdo_loccd;
?>
		<tr bgcolor='white'>
			<td align='right'>&nbsp;<input type='radio' name='rdo_loccd' value='2'  <?=($rdo_loccd==2)?' checked':''?>></td>
			<td align='left' colspan='6'>&nbsp;<?=mxlang("524")?> </td>
		</tr>
<?
	}
?>
<?
	function list_date() {
		global $db,$rdo_loccd;
?>
		<tr bgcolor='white'>
			<td align='right'>&nbsp;<input type='radio' name='rdo_loccd' value='0' <?=($rdo_loccd==0)?' checked':''?>></td>
			<td align='left' colspan='5'>&nbsp;<?=mxlang("2952")?></td>
		</tr>
		<tr bgcolor='white'>
			<td align='right'>&nbsp;<input type='radio' name='rdo_loccd' value='1' <?=($rdo_loccd==1)?' checked':''?>></td>
			<td align='left' colspan='5'>&nbsp;<?=mxlang("2953")?></td>
		</tr>
<?
	}
?>
<iframe width="174" height="189" name="gToday:normal:../jsfile/agenda.js" id="gToday:normal:../jsfile/agenda.js" src="../jsfile/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; left:-500px; top:0px;"></iframe>
<?
	if ($debug) include("module/postdebug.inc");
	include("module/footer.inc");
?>
