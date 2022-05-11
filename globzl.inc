<?
/*

**************************************************************************
Version Note : my_new
Last Update Date : 29-Aug-2009 09:26
Last Update By : himawan
Remark:
  - update fdo_updatecontra for delete contra voucher / cheque
**************************************************************************
*/
//var
$arr_table_POS=array(
  'temp_newmstrh','temp_newmstrd','temp_mspayment','temp_delnewmstrh','temp_delnewmstrd',
  'temp_delmspayment');

$arr_table_msmemb=array(
  'msmemb','msmemb_extra','msmemb_delete','msmemb_extra_delete'
);

function get_client_ip() {
  $ipaddress = '';
  if (getenv('HTTP_CLIENT_IP'))
      $ipaddress = getenv('HTTP_CLIENT_IP');
  else if(getenv('HTTP_X_FORWARDED_FOR'))
      $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
  else if(getenv('HTTP_X_FORWARDED'))
      $ipaddress = getenv('HTTP_X_FORWARDED');
  else if(getenv('HTTP_FORWARDED_FOR'))
      $ipaddress = getenv('HTTP_FORWARDED_FOR');
  else if(getenv('HTTP_FORWARDED'))
     $ipaddress = getenv('HTTP_FORWARDED');
  else if(getenv('REMOTE_ADDR'))
      $ipaddress = getenv('REMOTE_ADDR');
  else
      $ipaddress = 'UNKNOWN';
  return $ipaddress;
}

function stillAlive($what_to_do){
  global $db,$opnm,$xmodule_name;
  $ip = get_client_ip();
  $check_login = db_select_record("*","online_users","where opnm='$opnm' and ip='$ip'");
  $array = array(
    "opnm"          => $opnm,
    "module"        => $xmodule_name,
    "description"   => $what_to_do,
    "last_seen"     => date('Y-m-d H:m:s'),
    "ip"            => $ip
  );
  
  if (empty($check_login)){
    db_insert("online_users",$array);
  } else {
    db_update2("online_users",$array,array("opnm"=>$opnm,"ip"=>$ip));
  }
}
function frm_payment_pending_report2() {
  global $db, $xxrow, $edit_payment, $debug,
  $tmp_vou_valid,$tmp_vou_amount,$tmp_vou_txt,
  $tmp_che_valid,$tmp_che_amount,$tmp_che_txt,
  $vou_bool,$chk_bool,$vou_input,$chk_input,$vou_text,$vou_curr,$vou_rate,$chk_text,$chk_curr,$chk_rate,$txt_batchno,
  $xtotpay,$pquerr2, $default_cnt, $vpost, $show_ccd, $show_btr, $xreturn,$new_contra_db,
  $tot_item,$txt_key,$txt_scid,$trxno,$allow_return,$xtxt_key,$xloccd,$xtrtype,$tmp_file_ref,$xmodule_name,
  $vtxt_remark,$exc, $chkcurr_bool, $chkcurr_input, $chkcurr_text, $voucurr_bool, $voucurr_input, $voucurr_text,
  $newepoint, $xoth_amount, $xtype_act;

  //echo "xtxt_key=".$xtxt_key."<br/>";
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

  //echo "$payType1-$amount1 $payType2=$amount2 $payType3=$amount3<br/>";
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

//  if ($pay_amount['CHK']>$xtotpay || $pay_amount['VOU']>$xtotpay)
//    $xallow_return=1;
  if ($xtotmspayment>$xtotpay && $pay_amount['CHK']) $xallow_return=1;

  $tmp_vou_valid='0';
  $tmp_vou_amount=0;
  $tmp_vou_txt='';
  $tmp_che_valid='0';
  $tmp_che_amount=0;
  $tmp_che_txt='';

  //echo("-$pquerr1-<br/>\n");
  //if ($xstockist_flag)
  $xdisable_epoint='';
  if ($edit_payment==1) {
    if ($xmodule_name=='brinv_new') {
			if ($newepoint==1) $xdisable_epoint='';
		}else{
			$xdisable_epoint='disabled';
		}
    $pquerr1=
      "select ptypeid,ptypename ".
      "from pay_type where id_cnt='$default_cnt' ".
      "order by ptypename";
    if (preg_match('/brinv/', $xmodule_name)) {
			$pquerr1=
				"select ptypeid,ptypename ".
				"from pay_type_br_inv where id_cnt='$default_cnt' ".
				"order by ptypename";
    }
  }else{
    $pquerr1=
      "select ptypeid,ptypename ".
      "from cb_pay_type ".
      "order by ptypename";
  }
  //echo("$pquerr1<br/>");
  //echo("<tr><td>test -->$edit_payment</td></tr>");
/*  if ($debug) echo $pquerr2.'<br/>';*/
  if ($pquerr2!='') $pquerr1=$pquerr2;
  if ($debug) echo $pquerr1.'<br/>';
  $presult1=pg_query($db,$pquerr1);

  $total_pay=0;
  $xtxt_key=trim($xtxt_key);
  $txt_key =trim($txt_key);
  //echo(pg_num_rows($presult1));
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
/*  if (eregi('\/product_ordering/sp_order_fltr',$_SERVER["SCRIPT_NAME"])) $tmp_file_ref='brcr';
  if (eregi('\/product_ordering/cb_order_fltr',$_SERVER["SCRIPT_NAME"])) $tmp_file_ref='brcb';
  if (eregi('\/product_ordering/view_invoice_so_staf_prnx',$_SERVER["SCRIPT_NAME"])) $tmp_file_ref='brcr';
  if (eregi('\/product_ordering/view_invoice_so_prnx',$_SERVER["SCRIPT_NAME"])) $tmp_file_ref='brcr';
*/
  echo "<input type='hidden' name='tmp_file_ref' value='".(($tmp_file_ref!='')?$tmp_file_ref:$xmodule_name)."'>";
  $epo_input='';
  for ($ip=0;$ip<pg_num_rows($presult1);$ip++) {
    $prow_ = pg_fetch_row($presult1, $ip);
    $total_pay+=$pay_amount[$prow_[0]];
    //if (type=CSH and not xallow_return) xcash=xreturn;
    $pay_amount[$prow_[0]]+=($prow_[0]=='CSH' && $xallow_return!=1 && $pay_amount['CSH']!=0)?$xreturn:0;
    if ($edit_payment!='1') {
      //echo("$prow_[0] ".$pay_amount[$prow_[0]]."<br/><br/>");
      if ($pay_amount[$prow_[0]]!=0) {
        echo("<tr align='left'>\n");
        echo("<td width='50%'>".mxlang("157","")." : ".mxlangtxt($prow_[1])."</td>\n");
        echo("<td align='right'>".mxlang("152","")." : ".CURRENCY.number_format($pay_amount[$prow_[0]],2)."</td>\n");
        echo("</tr>\n");
        if ($prow_[0]=='BDEC' && $xmodule_name=='brinv') {
          echo("<tr align='left'>\n");
          echo("<td colspan='2'>&nbsp;&nbsp;- ".mxlang("1653","")." <i>".preg_replace("/[,+]/",", ",$pay_note[$prow_[0]])."</i></td>\n");
          echo("</tr>\n");
        }
        if ($prow_[0]=='CHK' || $prow_[0]=='CHKCURR') {
          $tmp_che_amount=$pay_amount[$prow_[0]];
          echo("<tr align='left'>\n");
          echo("<td colspan='2'>&nbsp;&nbsp;- ".mxlang("1977","")." <i>".preg_replace("/[,+]/",", ",$pay_note[$prow_[0]])."</i></td>\n");
          echo("</tr>\n");
        }
        if ($prow_[0]=='VOU' || $prow_[0]=='VOUCURR') {
          $tmp_vou_amount=$pay_amount[$prow_[0]];
          echo("<tr align='left'>\n");
          echo("<td colspan='2'>&nbsp;&nbsp;- ".mxlang("1978","")." <i>".preg_replace("/[,+]/",", ",$pay_note[$prow_[0]])."</i></td>\n");
          echo("</tr>\n");
        }
        if ($prow_[0]=='CKK') {
          echo("<tr align='left'>\n");
          echo("<td colspan='2'>&nbsp;&nbsp;- ".mxlang("1608","")." <i>".preg_replace("/[,+]/",", ",$pay_note[$prow_[0]])."</i></td>\n");
          echo("</tr>\n");
        }
        if (($prow_[0]=='CCD'||$prow_[0]=='EBC') && $show_ccd=='1') {
          echo("<tr align='left'>\n");
          $txt_ccd=preg_split('/\#/',$pay_note[$prow_[0]]);
          $invoiceno = $pay_note[$prow_[0]];
          include('../module/connection_db_usc1.php');
          if ($prow_[0]=='CCD'){
            $querry ="SELECT * FROM network.response_pbb where invoiceno='$invoiceno'";
            $result = pg_exec($db_memlive,$querry);
            $result = pg_fetch_assoc($result);
            pg_close($db_memlive);
          } else if ($prow_[0]=='EBC'){
            $querry ="SELECT * FROM ebc_response where req_reference_number='$invoiceno'";
            $result = pg_exec($db,$querry);
            $result = pg_fetch_assoc($result);
          }
          if ($result==""){
            $prow_[0]='EBC';
            $querry ="SELECT * FROM ebc_response where req_reference_number='$invoiceno'";
            $result = pg_exec($db,$querry);
            $result = pg_fetch_assoc($result);
          }

          // var_dump($result);

          echo("<td colspan='2'>");
          if ($prow_[0]=='CCD'){
            echo("&nbsp;&nbsp;- ".mxlang("1979","").": <i>XXXX-XXXX-XXXX-".$result['pan']."</i><br/>\n");
          } else if ($prow_[0]=='EBC'){
            echo("&nbsp;&nbsp;- ".mxlang("1979","").": <i>XXXX-XXXX-XXXX-".substr($result['req_card_number'],-4)."</i><br/>\n");
            // echo("&nbsp;&nbsp;- ".mxlang("1979","").": <i>".substr($result['req_card_number'],0,4)."-XXXX-XXXX-XXXX</i><br/>\n");
          }

          // echo("&nbsp;&nbsp;- ".mxlang("1979","").": <i>XXXX-XXXX-XXXX-".substr($txt_ccd[0],-4)."</i><br/>\n");
          // echo("&nbsp;&nbsp;- ".mxlang("1980","").": <i>$txt_ccd[2]</i><br/>\n");
          echo("</td>\n");
          echo("</tr>\n");
        }
        if ($prow_[0]=='BTR' && $show_btr=='1') {
          echo("<tr align='left'>\n");
          $txt_btr=preg_split('/\#/',$pay_note[$prow_[0]]);
          echo("<td colspan='2'>");
          echo("&nbsp;&nbsp;- ".ucwords(strtolower(mxlang("388",""))).": <i>$txt_btr[0]</i><br/>\n");
          echo("&nbsp;&nbsp;- ".ucwords(strtolower(mxlang("657",""))).": <i>$txt_btr[1]</i><br/>\n");
          echo("</td>\n");
          echo("</tr>\n");
        }
      }

    }else{
      echo("<tr align='left'>\n");
      echo("<td colspan='2'><b>".ucwords(strtolower(mxlang("157","")))." : $prow_[1]</b></td>\n");
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
        echo("<td width='40%'>&nbsp;&nbsp;".ucwords(strtolower(mxlang("1978","")))." </td>\n");
        echo("<td>");
        //if ($pay_note[$prow_[0]]!='') {
        //  echo("<input type='hidden' name='vou_text' value='".$pay_note[$prow_[0]]."'/>".
        //    "&nbsp;<a href='#' onclick=\"viewvcr1('".$pay_note[$prow_[0]]."');\">".$pay_note[$prow_[0]]."</a><br/>\n");
        //}
        //else{
          echo("<input type='text' size='15' name='vou_text' value='".$pay_note[$prow_[0]]."' onChange='vou_inedit()' ");
          if ($new_contra_db) echo "onBlur=\"fget_contra_value('V')\"";
          echo ("/>\n");
          echo("<input type='hidden' size='15' name='vou_curr'  onChange='vou_inedit()'/>\n");
          echo("<input type='hidden' size='15' name='vou_rate' onChange='vou_inedit()'/>\n");
        if ($new_contra_db==1) {
          echo("<input type='button' name='btn_go' value='".ucfirst(strtolower(mxlang("1617","")))."' onclick='voucher_contra2();'>");
        }else
        {
          echo("<input type='button' name='btn_go' value='".ucfirst(strtolower(mxlang("1617","")))."' onclick='chkvcr1();'>");
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
//        $chk_curr="cbo_paycurr_$ip";
//        $chk_rate="cbo_payrate_$ip";

        echo("<tr align='left'>\n");
        echo("<td width='40%'>&nbsp;&nbsp;".ucwords(strtolower(mxlang("1977","")))." </td>\n");
        echo("<td>\n");
        //if ($pay_note[$prow_[0]]!='') {
        //  echo("<input type='hidden' name='chk_text_old' value='".$pay_note[$prow_[0]]."'/>");
        //  echo("&nbsp;<a href='#' onclick=\"viewvcr2('".$pay_note[$prow_[0]]."');\">".$pay_note[$prow_[0]]."</a><br/>\n");
        //}
        echo("<input type='text' size='15' name='chk_text' value='".$pay_note[$prow_[0]]."' onChange='chk_inedit()' ");
        if ($new_contra_db) echo "onBlur=\"fget_contra_value('C')\"";
        echo ("/>\n");
        echo("<input type='hidden' size='15' name='chk_curr'  onChange='chk_inedit()'/>\n");
        echo("<input type='hidden' size='15' name='chk_rate' onChange='chk_inedit()'/>\n");
        if ($new_contra_db==1) {
          echo("<input type='button' name='btn_go' value='".ucfirst(strtolower(mxlang("1617","")))."' onclick='check_contra2();'>");
        }else
        {
          echo("<input type='button' name='btn_go' value='".ucfirst(strtolower(mxlang("1617","")))."' onclick='chkvcr2();'>");
        }
        echo("<input type='hidden' name='paytype_".strtolower($prow_[0])."' value='$ip'/>\n");
        echo("</td>\n");
        echo("</tr>\n");
        if ($pay_note[$prow_[0]]!='') {
          $tmp_che_valid='1';
        }
        $tmp_che_txt=$pay_note[$prow_[0]];
        $tmp_che_amount=$pay_amount[$prow_[0]];
      }elseif ($prow_[0]=='CHKCURR') {
        $chkcurr_bool = true;
        if ($xtype_act=='editpayment')
          $chkcurr_bool = false;
        if ($chkcurr_input==''){
          $chkcurr_input="cbo_payinput_$ip";
          $chkcurr_text="cbo_paynumber_$ip";
        }
        echo("<tr align='left'>\n");
        echo("<td width='40%'>&nbsp;&nbsp;Foreign Contra Cheque No.</td>\n");
        echo("<td>\n");
        echo("  <input type=text size=15 name=$chkcurr_text value=".$pay_note[$prow_[0]]."> ");
        echo("</td>\n");
        echo("</tr>\n");

      }elseif ($prow_[0]=='VOUCURR') {
        $voucurr_bool = true;
        if ($xtype_act=='editpayment')
          $voucurr_bool = false;
        if ($voucurr_input==''){
          $voucurr_input="cbo_payinput_$ip";
          $voucurr_text="cbo_paynumber_$ip";
        }
        echo("<tr align='left'>\n");
        echo("<td width='40%'>&nbsp;&nbsp;Foreign Contra Voucher No.</td>\n");
        echo("<td>\n");
        echo("  <input type=text size=15 name=$voucurr_text value=".$pay_note[$prow_[0]]."> ");
        echo("</td>\n");
        echo("</tr>\n");
      }elseif ($prow_[0]=='CKK') {
        $ckk_bool = true;
        if ($ckk_input=='') $ckk_input="cbo_payinput_$ip";
        echo("<tr align='left'>\n");
        echo("<td width='40%'>&nbsp;&nbsp;".mxlang("1608","")." </td>\n");
        echo("<td>\n");
        echo("<input type='text' size='15' name='ckk_text' value='".$pay_note[$prow_[0]]."' onChange='chk_inedit()'/>\n");
        echo("<input type='hidden' name='paytype_".strtolower($prow_[0])."' value='$ip'/>\n");
        echo("</td>\n");
        echo("</tr>\n");
      }elseif ($prow_[0]=='BTR') {
        $txt_btr=preg_split('/#/',$pay_note[$prow_[0]]);
        echo("<tr align='left'>\n");
        echo("<td>&nbsp;&nbsp;".ucwords(strtolower(mxlang("1973","")))."</td>\n");
        echo("<td><input type='text' name='btr_no' size='16' value='$txt_btr[0]'></td>\n");
        echo("</tr>\n");
        echo("<tr>\n");
        echo("<td>&nbsp;&nbsp;".ucwords(strtolower(mxlang("657","")))."</td>\n");
        echo("<td><input type='text' name='btr_name' size='16' value='$txt_btr[1]'></td>\n");
        echo("</tr>\n");
      }elseif ($prow_[0]=='CCD'){
        $txt_ccd=preg_split('/#/',$pay_note[$prow_[0]]);
        echo("<tr>\n");
        echo("<td>".ucwords(strtolower(mxlang("159","")))."</td>\n");
        echo("<td><input type='text' name='ccd_no' size='16' maxlength='16' value='$txt_ccd[0]'>-<input type='text' name='ccd_csv' size='4' maxlength='4' value='$txt_ccd[1]'></td>\n");
        echo("</tr>\n");
        echo("<tr>\n");
        echo("<td>".ucwords(strtolower(mxlang("160","")))."</td>\n");
        echo("<td><input type='text' name='ccd_name' size='16' value='$txt_ccd[2]'></td>\n");
        echo("</tr>\n");
        echo("<tr>\n");
        echo("<td>".ucwords(strtolower(mxlang("161","")))."</td>\n");
        echo("<td>\n");
        echo("<input type=\"text\" name=\"ccd_exp\" value=\"");
        if($txt_ccd[3]=="") echo date("d/m/Y"); else echo $txt_ccd[3];
        echo("\" size=\"12\" maxlength=\"10\">\n");
        echo("<a href=\"javascript:void(0)\" onClick=\"gfPop.fPopCalendar(frm_invcnt_fltr.ccd_exp);return false;\" HIDEFOCUS>\n");
        echo("<img src=\"../images/cal_show.gif\" border=\"0\">\n");
        echo("</a>\n");
        echo("</td>\n");
        echo("</tr>\n");
        echo("<tr>\n");
        echo("<td>".ucwords(strtolower(mxlang("162","")))."</td>\n");
        echo("<td>\n");
        echo("<select name='ccd_type'>\n");
        echo("<option value='MC' ".(($txt_ccd[4]=='MC')?'selected':'').">".ucwords(strtolower(mxlang("1023","")))."</option>\n");
        echo("<option value='VS' ".(($txt_ccd[4]=='VS')?'selected':'').">".ucfirst(strtolower(mxlang("1541","")))."</option>\n");
        echo("<option value='DC' ".(($txt_ccd[4]=='DC')?'selected':'').">".ucfirst(strtolower(mxlang("513","")))."</option>\n");
        echo("</select>\n");
        echo("</td>\n");
        echo("</tr>\n");
        echo("<tr>\n");
        echo("<td>".ucwords(strtolower(mxlang("1972","")))."</td>\n");
        echo("<td><input type='text' name='ccd_autho' size='16' value='$txt_ccd[5]'></td>\n");
        echo("</tr>\n");
      }elseif ($prow_[0]=='EW'){
        $epo_input="cbo_payinput_$ip";
        $_SESSION['xepo_input']="cbo_payinput_$ip";
        if (preg_match('/brinv_new/i', $xmodule_name)) {
					$xdisable_epoint=($pay_amount[$prow_[0]]>0)?'disabled':'';
        }else{
          $xdisable_epoint='disabled';
        }
?>
        <tr align='left'>
          <td></td>
          <td>
          <input type="hidden" name="ew_no_old" value="<?=$pay_note[$prow_[0]]?>">
          <input type="hidden" name="ew_no" value="<?/*$pay_note[$prow_[0]]*/?>">
					<input type="hidden" name="encash_ew_no" value="">
					<input type="hidden" name="encash_amt" value="0">
          <input <?=$xdisable_epoint?> type='button' onclick="epo_submit('<?=$epo_input?>')" value='e-Point Payment' name='btn_epo_pay' id='btn_epo_pay'>
          <input <?=$xdisable_epoint?> type='button' onclick="this.form.cbo_payinput_<?=$ip?>.value=0;calc_tot_pay();" value='Reset' name='btn_epo_reset' id='btn_epo_reset'>
          </td>
        </tr>
<?
      }elseif ($prow_[0]=='OTH'){
        $xoth_amount=$pay_amount[$prow_[0]];
      }



      //$xreadonly = ($prow_[0]=='CHK' || $prow_[0]=='VOU')?'readonly':'';
      echo("<tr align='left' >\n");
      echo("<td width='40%'>&nbsp;&nbsp;".mxlang("152","")." : </td>");
      //if ($pay_note[$prow_[0]]!='') {
      //  echo("<td><input type='hidden' name='cbo_payinput_$ip' value='".$pay_amount[$prow_[0]]."'>");
      //  echo("&nbsp;".$pay_amount[$prow_[0]]."\n");
      //}else{
      $xreadonly='';
      if ($prow_[0]=='EW') $xreadonly='readonly';
        echo("<td><input $xdisable_epoint2 type=\"hidden\" name=\"xindex_".$prow_[0]."\" value=\"$ip\" ><input type='text' name='cbo_payinput_$ip' size='15' value='".$pay_amount[$prow_[0]]."' ");
        echo("onChange='calc_tot_pay();' $xreadonly>");
      //}
      $pay_default[$prow_[0]]=($pay_amount[$prow_[0]]==0)?0:1;
      echo("<input type='hidden' name='pay_default_$ip' value='".$pay_default[$prow_[0]]."'>");
      echo("<input type='hidden' name='cbo_paytype_$ip' value='$prow_[0]'>");

      echo("</td>\n</tr>\n");
    }
  }
  if ($epo_input=='') {
?>
		<input type="hidden" name="ew_no" value="">
		<input type="hidden" name="ew_no_old" value="">
<?
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
          <td align='right' width='40%'>
            <input type='hidden' name='txt_count' value='<?=$ip?>'>
            <input type='hidden' name='amount_text' value='0'>
            <b><?=ucwords(strtolower(mxlang("164","")))?> :</b>
          </td>
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
//  if ($xreturn!=0)
//    $xwrite_return= $xreturn;
//  else
//    $xwrite_return= $txt_grandtotal-$xtotal;

  if ($tot_item=='') $tot_item=0;
  if ($tot_item==0 && ($tmp_che_amount!=0 || $tmp_vou_amount!=0 || $payType1=="CSH") && $allow_return=="1") {
    $xwrite_total = 0;
    $xwrite_return= $xtotal;
  }

?>
          <td align='right'><b><div id='total_pay'>
<?=CURRENCY." ".number_format($xwrite_total,2)?></div></b></td>
        </tr>
        <tr bgcolor="#FFFFFF">
          <td align='right' width='40%'><b><?=ucwords(strtolower(mxlang("165","")))?> :</b></td>
          <td align='right'><b><div id='return_pay'><?=CURRENCY." ".number_format($xwrite_return,2)?></div></b></td>
          </tr>
          <tr  bgcolor="#FFFFFF">
            <td align='right' colspan='2'>&nbsp;
<?                    //echo("PHP_SELF=".$_SERVER["SCRIPT_NAME"]);
        if($edit_payment=='1'){
          //change back based 080219-003 at Feedback080219.odt
          if (
              (preg_match('/\/view_invoice_sccb/',$_SERVER["SCRIPT_NAME"]) /*&& $xtrtype==1*/)  || (
              preg_match('/\/view_invoice_so_inv/',$_SERVER["SCRIPT_NAME"]) && check_level_ex($xloccd)=='MS')
            && ($chk_bool || $vou_bool)
            ) {
          ?>
              <input type='hidden' name='allow_return' value='0'>
          <?
            }else{
              echo ("<input type='checkbox' name='allow_return' value='1'>&nbsp;\n");
               echo ucfirst(strtolower(mxlang("2500","")));
            }
        }?>

          </td>
          <input type='hidden' name='count_pay' value='<?=$ip?>'>
<!--          <input name="return_total" type="hidden"> -->
          <input type='hidden' name='xalert_997' value='<?=mxlang('997','')?>'>
          <input type='hidden' name='xalert_996' value='<?=mxlang('996','')?>'>
          <input type='hidden' name='xalert_809' value='<?=mxlang('809','')?>'>
          <input type='hidden' name='xalert_3067' value='<?=mxlang('3067','')?>'>
        </tr>
<?
        if ($edit_payment==1 &&
            ($xmodule_name=='brcb' || $xmodule_name=='brinv' || $xmodule_name=='brcr' ||
            ($xmodule_name=='whinv') || ($xmodule_name=='whcr'))

        ) {
?>
        <tr align="left">
          <td colspan="2"><i><?=mxlang("172","");?> : </i><br/>
&nbsp;<TEXTAREA NAME="vtxt_remark" ROWS=3 COLS=50><?=nl2br($vtxt_remark)?></TEXTAREA>
          </td>
        </tr>
<?
        }else{
          if ($vtxt_remark!='') {
            if ($edit_payment==1) {
?>
        <tr align="left">
          <td colspan="2">
          <?=mxlang("172","");?> : <br/><TEXTAREA NAME="txt_remarks" ROWS=3 COLS=50><?=nl2br($vtxt_remark)?></TEXTAREA>
          </td>
        </tr>
<?
            }else{
							if ($xmodule_name!='brcb') {
?>
        <tr align="left">
          <td colspan="2"><i><?=mxlang("172","");?> : <?=fremove_backslashes(nl2br($vtxt_remark))?></i></td>
        </tr>
<?
							}
            }
          }
        }
}

function fpg_exec__($xsql) {
  global $db, $debug, $opnm;
  $xres1=pg_query($db, $xsql);
//   if ($debug) echo "$xsql<br/>";
  //echo "numrows=".pg_num_rows($xres1)."<br/>";
  if (!$xres1 && $debug) echo "error fpg_exec__=".$xsql."<br/>";
  if (pg_num_rows($xres1)>0)
    $xrow1=pg_fetch_row($xres1, 0);
  pg_free_result($xres1);
  //if ($debug) " +++ ".$xsql." xrow10=".$xrow1[0]." xrow11=".$xrow1[1]."<br/>";
  return $xrow1;
}

function fcheck_stat_trx($xtrx_no) {
  $xtsql=
    "select 0 as xtype, trtype from newsctrh where trcd='$xtrx_no'
    union
    select 1 as xtype, trtype from newmstrh where trcd='$xtrx_no'
    union
    select 2 as xtype, trtype from newmsivtrh where trivcd='$xtrx_no'
    union
    select 0 as xtype, trtype from delsctrh where trcd='$xtrx_no'
    union
    select 1 as xtype, trtype from delmstrh where trcd='$xtrx_no'
    union
    select 2 as xtype, trtype from delmsivtrh where trivcd='$xtrx_no' ";
  $xtrow=fpg_exec4($xtsql);
  $xstr="";
  if      ($xtrow[0]==0 && $xtrow[1]==1) {
    $xstr="sccb";
  }elseif ($xtrow[0]==1 && $xtrow[1]==1) {
    $xstr="brcb";
  }elseif ($xtrow[0]==2 && $xtrow[1]==1) {
    $xstr="brinv";
  }elseif ($xtrow[0]==2 && $xtrow[1]==2) {
    $xstr="msinv";
  }elseif ($xtrow[0]==0 && ($xtrow[1]==2 || $xtrow[1]==10)) {
    $xstr="sccr";
  }elseif ($xtrow[0]==1 && ($xtrow[1]==2 || $xtrow[1]==10)) {
    $xstr="brcr";
  }
  //echo "<b>$xtsql</b> $xtrow[0] $xtrow[1] $xstr<br/>";
  return $xstr;
}

function fdo_updatecontra($idcontra,$xtype,$xtrxno,$opnm) {
  global $db, $debug;
  if ($debug) echo "fdo_updatecontra($idcontra,$xtype,$xtrxno,$opnm)<br/>";
  if($xtype=="C"){
/*
    $selectMaxFno = "select max(fno) from tcheqlog ";
    $xresMaxFno = pg_exec($db, $selectMaxFno);
    $xrowMaxFno = pg_fetch_row($xresMaxFno, 0);
    $nextFno = $xrowMaxFno[0]+1;

    $xsql_ce = "select fbatchno from tcheqlog where fcheqno='$idcontra' and ftrxno='$xtrxno' order by factiondate DESC limit 1;";
    $xres_ce =pg_query($db, $xsql_ce);
    if ($debug) echo $xpsql_ce.'<br/>';
    if (pg_num_rows($xres_ce)>0) {
      $xrow_ce = pg_fetch_row($xres_ce, 0);
      $xsql_ce2 = "select ftrxno,fcontramem,fcontracurr,fconvertedamt,fcontraexrate,fremark,fcontraloc from tcheqlog where fcheqno='$idcontra' and fbatchno='$xrow_ce[0]' and ftrxno <> '$xtrxno' and faction='C' order by factiondate DESC limit 1;";
      $xres_ce2 =pg_query($db, $xsql_ce2);
      if (pg_num_rows($xres_ce2)>0) {
        $xrow_ce2 = pg_fetch_row($xres_ce2, 0);
        if($xrow_ce2[4]=='')$xrow_ce2[4]=0;
        if($xrow_ce2[3]=='')$xrow_ce2[3]=0;
        $xpsql ="update tcheque set fstatus='C',factionby='$opnm',factiondate=now(), ";
        $xpsql.="ftrxno='$xrow_ce2[0]',fcontramem='$xrow_ce2[1]', fcontracurr='$xrow_ce2[2]', fcontraexrate=$xrow_ce2[4], fconvertedamt=$xrow_ce2[3],fremark='$xrow_ce2[5]',fcontraloc='$xrow_ce2[6]' ";
        $xpsql.="where fcheqno='$idcontra' and fbatchno='$xrow_ce[0]'";
        if ($debug) echo $xpsql.'<br/>';
        //else
        $xpres = pg_exec($db, $xpsql);

        //insert log
        $sqlUpdateLog = "insert into tcheqlog (fcheqno, fbatchno, ftrxno, fcontramem, fcontradate, fcontraloc, fcontracurr, fcontraexrate, fconvertedamt, faction, factionby, factiondate) ".
              " values ('$idcontra', '$xrow_ce[0]', '$xtrxno', '$xrow_ce2[1]', now(), '$xrow_ce2[6]', '$xrow_ce2[2]', $xrow_ce2[4],$xrow_ce2[3], 'D', '$opnm', now() )";
        pg_exec($db, $sqlUpdateLog);
        if($debug==1) print $sqlUpdateLog."<br/>";

      }else{
        $xpsql ="update tcheque set fstatus='N',fremark='' ,factionby='$opnm',factiondate=now(), ";
        $xpsql.="ftrxno='',fcontramem='', fcontracurr='', fcontraexrate=0, fconvertedamt=0,fcontraloc='' ";
        $xpsql.="where fcheqno='$idcontra' and fbatchno='$xrow_ce[0]'";
        if ($debug) echo $xpsql.'<br/>';
        //else
          $xpres = pg_exec($db, $xpsql);

        //insert log
        $sqlUpdateLog = "insert into tcheqlog (fcheqno, fbatchno, ftrxno, fcontradate, faction, factionby, factiondate) ".
              " values ('$idcontra', '$xrow_ce[0]', '$xtrxno', now(), 'D', '$opnm', now() )";
        pg_exec($db, $sqlUpdateLog);
        if($debug==1) print $sqlUpdateLog."<br/>";
        $sqlUpdateLog = "insert into tcheqlog (fcheqno, fbatchno, ftrxno, fcontradate, faction, factionby, factiondate) ".
              " values ('$idcontra', '$xrow_ce[0]', '$xtrxno', now(), 'N', '$opnm', now() )";
        pg_exec($db, $sqlUpdateLog);
        if($debug==1) print $sqlUpdateLog."<br/>";

      }
      pg_free_result($xres_ce2);
    }
*/
    $xc_row=fpg_exec4("select fcheqno, fbatchno from tcheque where ftrxno='$xtrxno'");
    $xcheqno = $xc_row[0];
    $xbatchno = $xc_row[1];
    if ($debug) echo "$xcheqno $xbatchno<br/>";

    $xvlog_sql=
      "select fno, faction, ftrxno, ftrxno, fcontramem,
      fcontracurr, fcontraexrate, fconvertedamt, fremark, fcontraloc
      from tcheqlog where fcheqno='$xcheqno' and fbatchno='$xbatchno'
      and faction='C' and ftrxno='$xtrxno'
      order by fno desc ";
    if ($debug) echo $xvlog_sql.'*<br/>';
    $xvlog_res=pg_query($db, $xvlog_sql);
    $xvlog_c = fpg_exec4("select count(fcheqno) from tcheqlog where fcheqno='$xcheqno' and fbatchno='$xbatchno' and faction='C'");
    //if (pg_num_rows($xvlog_res)==1) {
/*      $xvlog_row=pg_fetch_row($xvlog_res, 0);
      if ($xvlog_row[1]=='C') { //only 1 contra
      }*/
    //}else{
    $xvlog_row=pg_fetch_row($xvlog_res, 0);
    if ($xvlog_row[1]=="C") {
      $xstat_trx=fcheck_stat_trx($xvlog_row[2]);
      if ($debug) echo "$xvlog_row[2] xstat_trx=$xstat_trx ".pg_num_rows($xvlog_res)." countlog=".$xvlog_c[0]."<br/>";

      if ($xstat_trx=='sccb' || $xstat_trx=='sccr' || $xstat_trx=='brcb' || $xstat_trx=='brcr'
        || (($xstat_trx=='brinv'||$xstat_trx=='msinv') && $xvlog_c[0]==1)) {
        if ($xvlog_row[2]==$xtrxno) {
          if ($debug) echo "$xvlog_row[0]<br/>";
          $xpsql="delete from tcheqlog where ftrxno='$xtrxno' and fcheqno='$xcheqno' and fbatchno='$xbatchno';";
          if ($debug) echo $xpsql.'<br/>';
          $xpres = pg_exec($db, $xpsql);

          $xpsql ="update tcheque set fstatus='N', fflag=null, fremark='' ,factionby='$opnm',factiondate=now(), ";
          $xpsql.="ftrxno='',fcontramem='', fcontracurr='', fcontraexrate=0, fconvertedamt=0,fcontraloc='' ";
          $xpsql.="where fcheqno='$xcheqno' and fbatchno='$xbatchno' ";
          if ($debug) echo $xpsql.'<br/>';
          $xpres = pg_exec($db, $xpsql);
        }else{
          $xsccb_no=$xvlog_row[0];
        }
      }
      if ($debug) echo "$xstat_trx=='brinv'||'msinv' && $xvlog_row[2]==$xtrxno && $xvlog_c[0]>1 <br/>";
      if (($xstat_trx=='brinv'||$xstat_trx=='msinv') && $xvlog_row[2]==$xtrxno && $xvlog_c[0]>1) {
        if ($debug) echo "$xvlog_row[0]**<br/>";
        $xpsql="delete from tcheqlog where ftrxno='$xtrxno' and fcheqno='$xcheqno' and fbatchno='$xbatchno';";
        if ($debug) echo $xpsql.'<br/>';
        $xpres = pg_exec($db, $xpsql);


        $xvsccb_sql=
          "select fno, faction, ftrxno, fcontramem, fcontracurr,
          fcontraexrate, fconvertedamt, fremark, fcontraloc
          from tcheqlog where fcheqno='$xcheqno' and fbatchno='$xbatchno' and faction='C' ";
        if ($debug) echo $xvsccb_sql.'<br/>';
        $xvsccb_res=pg_query($db, $xvsccb_sql);
        if (pg_num_rows($xvsccb_res)>0) {
          $xvsccb_row=pg_fetch_row($xvsccb_res, 0);
          $xstat_trx2=fcheck_stat_trx($xvsccb_row[2]);
          if ($xstat_trx2=='sccb' || $xstat_trx2=='sccr') {
            $xpsql ="update tcheque set fstatus='C',fremark='$xvsccb_row[7]' ,factionby='$opnm',factiondate=now(), ";
            $xpsql.="ftrxno='$xvsccb_row[2]',fcontramem='$xvsccb_row[3]', fcontracurr='$xvsccb_row[4]', fcontraexrate=$xvsccb_row[5], fconvertedamt=$xvsccb_row[6],fcontraloc='$xvsccb_row[8]' ";
            $xpsql.="where fcheqno='$xcheqno' and fbatchno='$xbatchno' ";
            if ($debug) echo $xpsql.'<br/>';
            $xpres = pg_exec($db, $xpsql);
          }
        }

      }
    }

  }else{// xtype=V

//     $selectMaxFno = "select max(fno) from tvcrlog ";
//     $xresMaxFno = pg_exec($db, $selectMaxFno);
//     $xrowMaxFno = pg_fetch_row($xresMaxFno, 0);
//     $nextFno = $xrowMaxFno[0]+1;
    //if ($debug) echo "select fvcrno, fbatchno from tvoucher where ftrxno='$xtrxno'<br/>";
    $xsql="select fvcrno, fbatchno from tvoucher where ftrxno='$xtrxno'";
    if ($debug) echo "$xsql<br/>";
    $xv_row=fpg_exec4($xsql);
    $xvcrno = $xv_row[0];
    $xbatchno = $xv_row[1];
    if ($debug) echo "$xvcrno $xbatchno<br/>";

    $xvlog_sql=
      "select fno, faction, ftrxno, ftrxno, fcontramem,
      fcontracurr, fcontraexrate, fconvertedamt, fremark, fcontraloc
      from tvcrlog where fvcrno='$xvcrno' and fbatchno='$xbatchno'
      and faction='C' and ftrxno='$xtrxno'
      order by fno desc ";
    if ($debug) echo $xvlog_sql.'*<br/>';
    $xvlog_res=pg_query($db, $xvlog_sql);
    $xvlog_c = fpg_exec4("select count(fvcrno) from tvcrlog where fvcrno='$xvcrno' and fbatchno='$xbatchno' and faction='C'");
    //if (pg_num_rows($xvlog_res)==1) {
/*      $xvlog_row=pg_fetch_row($xvlog_res, 0);
      if ($xvlog_row[1]=='C') { //only 1 contra
      }*/
    //}else{
    if (pg_num_rows($xvlog_res)>0) {
      $xvlog_row=pg_fetch_row($xvlog_res, 0);
      if ($xvlog_row[1]=="C") {
        $xstat_trx=fcheck_stat_trx($xvlog_row[2]);
        if ($debug) {
          echo "$xstat_trx=fcheck_stat_trx($xvlog_row[2]);<br/>";
          echo "$xvlog_row[2] xstat_trx=$xstat_trx ".pg_num_rows($xvlog_res)." ".$xvlog_c[0]."<br/>";
        }

        if ($xstat_trx=='sccb' || $xstat_trx=='sccr' || $xstat_trx=='brcb' || $xstat_trx=='brcr'
          || (($xstat_trx=='brinv'||$xstat_trx=='msinv') && $xvlog_c[0]==1)) {
          if ($xvlog_row[2]==$xtrxno) {
            if ($debug) echo "$xvlog_row[0]<br/>";
            $xpsql="delete from tvcrlog where ftrxno='$xtrxno' and fvcrno='$xvcrno' and fbatchno='$xbatchno';";

            if ($debug) echo $xpsql.'<br/>';
            $xpres = pg_exec($db, $xpsql);

            $xpsql ="update tvoucher set fstatus='N', fflag=null, fremark='' ,factionby='$opnm',factiondate=now(), ";
            $xpsql.="ftrxno='',fcontramem='', fcontracurr='', fcontraexrate=0, fconvertedamt=0,fcontraloc='' ";
            $xpsql.="where fvcrno='$xvcrno' and fbatchno='$xbatchno' ";
            if ($debug) echo $xpsql.'<br/>';
            $xpres = pg_exec($db, $xpsql);
          }else{
            $xsccb_no=$xvlog_row[0];
          }
        }
        //if ($debug) echo "$xstat_trx=='brinv' && $xvlog_row[2]==$xtrxno && $xvlog_c[0]>1 <br/>";
        if (($xstat_trx=='brinv'||$xstat_trx=='msinv') && $xvlog_row[2]==$xtrxno && $xvlog_c[0]>1) {
          if ($debug) echo "$xvlog_row[0]**<br/>";
          $xpsql="delete from tvcrlog where ftrxno='$xtrxno' and fvcrno='$xvcrno' and fbatchno='$xbatchno';";
          if ($debug) echo $xpsql.'<br/>';
          $xpres = pg_exec($db, $xpsql);


          $xvsccb_sql=
            "select fno, faction, ftrxno, fcontramem, fcontracurr,
            fcontraexrate, fconvertedamt, fremark, fcontraloc
            from tvcrlog where fvcrno='$xvcrno' and fbatchno='$xbatchno' and faction='C' ";
          if ($debug) echo $xvsccb_sql.'<br/>';
          $xvsccb_res=pg_query($db, $xvsccb_sql);
          if (pg_num_rows($xvsccb_res)>0) {
            $xvsccb_row=pg_fetch_row($xvsccb_res, 0);
            $xstat_trx2=fcheck_stat_trx($xvsccb_row[2]);
            if ($xstat_trx2=='sccb' || $xstat_trx2=='sccr' || $xvsccb_row[2]='UPDVCR') {
              $xpsql ="update tvoucher set fstatus='C',fremark='$xvsccb_row[7]' ,factionby='$opnm',factiondate=now(), ";
              $xpsql.="ftrxno='$xvsccb_row[2]',fcontramem='$xvsccb_row[3]', fcontracurr='$xvsccb_row[4]', fcontraexrate=$xvsccb_row[5], fconvertedamt=$xvsccb_row[6],fcontraloc='$xvsccb_row[8]' ";
              $xpsql.="where fvcrno='$xvcrno' and fbatchno='$xbatchno' ";
              if ($debug) echo $xpsql.'<br/>';
              $xpres = pg_exec($db, $xpsql);
            }
          }

        }
      }
    }

  }
  //pg_free_result($xres_ce);
}

function fcheck_flag_action($xcode, $xfield_name, $xpage, $xmemtype=0) {//$xmemtype=0 member, $xmemtype=1 sponsor
  //$xfield_name: a_edit, a_view, a_reg, a_purc
  global $debug;
/*  $arr_field_flag=array(
    'a_edit'=>"Member ($xcode) not allowed to edit !",
    'a_view'=>"Member ($xcode) not allowed to view !",
    'a_reg'=>"Member ($xcode) not allowed to become sponsor !",
    'a_purc'=>"Member ($xcode) not allowed to purchase",
  );*/
  $xsql_m="select flag from msmemb where code='$xcode' ";
  $xrow_m=fpg_exec4($xsql_m);

  if ($xmemtype==0)
	 $xmsg_extra="Action not allowed! The membership ";

  if ($xmemtype==1)
	 $xmsg_extra="Unable to become sponsor: Membership ";

  $arr_xflag=array(
    0 => "$xmsg_extra ($xcode) is suspended!",
    1 => "$xmsg_extra ($xcode) is suspended!",
    2 => "$xmsg_extra ($xcode) is terminated!",
    3 => "$xmsg_extra ($xcode) is resigned!",
    4 => "$xmsg_extra ($xcode) is expired!",
    5 => "$xmsg_extra ($xcode) is permanent expired!",
    6 => "$xmsg_extra ($xcode) is has transferred!",
    7 => "$xmsg_extra ($xcode) is rejoined!",
    8 => "$xmsg_extra ($xcode) is deceased!"
  );

  $arr_field_flag=array(
    'a_edit'=>"Member code ($xcode) not allowed to edit !",
    'a_view'=>"Member code ($xcode) not allowed to view!",
    'a_reg' =>"Member code ($xcode) not allowed to become sponsor!",
    'a_purc'=>"Member code ($xcode) not allowed to purchase!",
  );

  //$xresult=false;
  if ($xrow_m[0]!='') {
    $xsql_f="select $xfield_name from mflag_setup where mflag='".$xrow_m[0]."' ";
    $xrow_f=fpg_exec4($xsql_f);
//     if ($debug) echo("$xsql_f ".$xrow_f[0]."<br/>");
    if ($xrow_f[0]=='f') {
      //$msg=$arr_field_flag[$xfield_name];
      $msg=$arr_xflag[$xrow_m[0]];
      if (preg_match('/\?/', $xpage)) {
				$xurl="Location: $xpage&msg=".urlencode($msg);
      }else{
				$xurl="Location: $xpage?msg=".urlencode($msg);
      }
      if ($debug) {
        echo($xurl);
      }else{
        header($xurl);
        exit;
      }
      $xresult=false;
    }else{
      $xresult=true;
    }
  }
}//end function

	function fcheck_flag_action4($xcode, $xfield_name) {
		//$xfield_name: a_edit, a_view, a_reg, a_purc
		global $debug;
		if ($debug) echo "fcheck_flag_action4($xcode, $xfield_name);<br/>\n";
	/*  $arr_field_flag=array(
			'a_edit'=>"Member ($xcode) not allowed to edit !",
			'a_view'=>"Member ($xcode) not allowed to view !",
			'a_reg'=>"Member ($xcode) not allowed to become sponsor !",
			'a_purc'=>"Member ($xcode) not allowed to purchase",
		);*/
		$xsql_m="select flag from msmemb where code='$xcode' ";
		$xrow_m=fpg_exec4($xsql_m);

		$arr_xflag=array(
			0 => "Member code ($xcode) has been suspended!",
			1 => "Member code ($xcode) has been suspended!",
			2 => "Member code ($xcode) has been terminated!",
			3 => "Member code ($xcode) has been resigned!",
			4 => "Member code ($xcode) has been expired!",
			5 => "Member code ($xcode) has been permanent expired!",
			6 => "Member code ($xcode) has been has transferred!",
			7 => "Member code ($xcode) has been rejoined!",
			8 => "Member code ($xcode) has been deceased!"
		);

		$arr_field_flag=array(
			'a_edit'=>"Member code ($xcode) not allowed to edit !",
			'a_view'=>"Member code ($xcode) not allowed to view!",
			'a_reg' =>"Member code ($xcode) not allowed to become sponsor!",
			'a_purc'=>"Member code ($xcode) not allowed to purchase!",
		);

		//$xresult=false;
		if ($xrow_m[0]!='') {
			$xsql_f="select $xfield_name from mflag_setup where mflag='".$xrow_m[0]."' ";
			$xrow_f=fpg_exec4($xsql_f);
	    if ($debug) echo("$xsql_f ".$xrow_f[0]."<br/>");
			if ($xrow_f[0]=='f') {
				//$msg=$arr_field_flag[$xfield_name];
				$msg=$arr_xflag[$xrow_m[0]];
				$xarr_result['xmsg']=$msg;
				$xarr_result['xstatus']=false;
			}else{
				$xarr_result['xstatus']=true;
			}
		}//end if
		return $xarr_result;
	}//end function


function xdec_to_utf8($xdec) {
  $utf='';
  if ( $xdec <128 ){
      $byte1 = $xdec;
      $utf  .= chr($byte1);
  }
  if ( $xdec >=128 && $xdec <=2047 ){
      $byte1 = 192 + (int)($xdec / 64);
      $byte2 = 128 + ($xdec % 64);
      $utf  .= chr($byte1).chr($byte2);
  }
  if ( $xdec >=2048 && $xdec <=65535){
      $byte1 = 224 + (int)($xdec / 4096);
      $byte2 = 128 + ((int)($xdec / 64) % 64);
      $byte3 = 128 + ($xdec % 64);

      $utf  .= chr($byte1).chr($byte2).chr($byte3);
  }
  if ( $xdec >=65536 && $xdec <=2097151){
      $byte1 = 240 + (int)($xdec / 262144);
      $byte2 = 128 + ((int)($xdec / 4096) % 64);
      $byte3 = 128 + ((int)($xdec / 64) % 64);
      $byte4 = 128 + ($xdec % 64);
      $utf  .= chr($byte1).chr($byte2).chr($byte3).chr($byte4);
  }
  if ( $xdec >=2097152 && $xdec <=67108863){
      $byte1 = 248 + (int)($xdec / 16777216);
      $byte2 = 128 + ((int)($xdec / 262144) % 64);
      $byte3 = 128 + ((int)($xdec / 4096) % 64);
      $byte4 = 128 + ((int)($xdec / 64) % 64);
      $byte5 = 128 + ($xdec % 64);
      $utf  .= chr($byte1).chr($byte2).chr($byte3).chr($byte4).chr($byte5);
  }
  if ( $xdec >=67108864 && $xdec <=2147483647){
      $byte1 = 252 + ($xdec / 1073741824);
      $byte2 = 128 + (($xdec / 16777216) % 64);
      $byte3 = 128 + (($xdec / 262144) % 64);
      $byte4 = 128 + (($xdec / 4096) % 64);
      $byte5 = 128 + (($xdec / 64) % 64);
      $byte6 = 128 + ($xdec % 64);
      $utf  .= chr($byte1).chr($byte2).chr($byte3).chr($byte4).chr($byte5).chr($byte6);
  }
  return $utf;
}

function unicode_to_utf( $input, $array=TRUE){
  $xutf = '';
  for ($z=0; $z<strlen($input); $z++) {
    if (preg_match('/\&/',$input[$z])) {
      if (preg_match('/\#/',$input[$z+1])) {
        $xtemp='';
        while(!preg_match('/\;/',$input[$z])) {
          $z++;
          $xtemp.=$input[$z];
        }
        $xtemp=preg_replace('/[\&\#\;]/','',$xtemp);
        $xutf.=xdec_to_utf8($xtemp);
      }else{
        $xutf.=$input[$z];
      }
    }else{
      $xutf.=$input[$z];
    }
  }
  return $xutf;
}

function fget_address($xcode) {
  $xcode=trim($xcode);
  $xasql="select a.name,a.addr1||' '||a.addr2||' '||a.addr3, a.town, a.postcd, b.st_name ".
    "from msmemb a ".
    "left join state b on b.st_id::varchar(5)=a.st_id ".
    "where a.code='$xcode' ";
//   echo $xasql;
  $xarow=fpg_exec4($xasql);
  $xresult="$xarow[1]<br/>$xarow[2] $xarow[3] $xarow[4]";
  return $xresult;
}

  function db_update($xtbname, $xarr_data, $xfieldwhere,$xdb=null) {
    //usage:db_update('msmemb', $xarr_msmemb, 'code');
    global $db, $debug, $opnm;
    $str_data='';
    while (list($key, $val) = each($xarr_data)) {
      //$val = eregi_replace("[\\]+", "", $val);
      $val = preg_replace('/[\\\\]+/i', '', $val);
      if ($key==$xfieldwhere) {
        $xvaluewhere=$val;
      }else{
        if ($str_data!='')
          $str_data.=",";

//         if ($key=='bank_acc_name' && eregi('paulus agung',$val)) {
//           echo $val."<br/>\n";
//         }

        if ($val=='null')
          $str_data.="$key=null";
        elseif ($val=='null' || $val=='now()'  || strtolower($val)=='true' || strtolower($val)=='false')
          $str_data.="$key=".preg_replace("/\'/", "",pg_escape_string($val));
        else
          $str_data.="$key='".pg_escape_string($val)."'";
      }
    }
    $xpsql="update $xtbname set $str_data ";
    if ($xfieldwhere!='')
      $xpsql.="where $xfieldwhere='$xvaluewhere' ";

    if (!isset($xdb)) {
      $xdb=$db;
    }

    if ($debug){
      echo $xpsql."<br/>\n";
    }else{
      $xpres=pg_query($xdb, $xpsql);
      if (!$xpres) {
        echo $xpsql."<br/>\n";
      }
    }

  }

  function db_update2($xtbname, $xarr_data, $xarr_where, $db4=null,$show_error=null) {
    //usage:db_update2('msmemb', $xarr_msmemb, $xarr_where);
    global $db, $debug;
    $str_data='';
    while (list($key, $val) = each($xarr_data)) {
      //$val = eregi_replace("[\\]+", "", $val);
      $val = preg_replace('/[\\\\]+/i', '', $val);

      //if ($key==$xfieldwhere) {
      //  $xvaluewhere=$val;
      //}else{
        if ($str_data!='')
          $str_data.=",";

//         if ($key=='bank_acc_name' && eregi('paulus agung',$val)) {
//           echo $val."<br/>\n";
//         }

        if ($val=='null')
          $str_data.="$key=null";
        elseif ($val=='null' || $val=='now()' || strtolower($val)=='true' || strtolower($val)=='false'){
          //$str_data.="$key=".preg_replace("/\'/", "",addslashes($val));
          $str_data.="$key=".preg_replace("/\'/", "",$val);
        }else
          $str_data.="$key=E'".pg_escape_string($val)."'";
          //$str_data.="$key=E'".$val."'";
      //}
    }
    $xpsql="update $xtbname set $str_data ";
    $xfieldwhere='';
    while (list($key, $val) = each($xarr_where)) {
      if ($xfieldwhere=='')
        $xfieldwhere.="where $key='$val' ";
      else
        $xfieldwhere.="and $key='$val' ";
    }
    if ($xfieldwhere!='')
      $xpsql.=" $xfieldwhere ";

    if ($debug){
      echo $xpsql."<br/>\n";
    }
    if (isset($db4)) {
      $dbx=$db4;
    }else{
      $dbx=$db;
    }
    $xpres=pg_query($dbx, $xpsql);
    if ((!$xpres && $opnm=='hima')||(!$xpres && $show_error)) {
      if (!$show_error){
        echo $xpsql."<br/>\n";
      } else {
        echo "error";
      }
    }
  }

  function db_insert($xtbname, $xarr_data,$xdb=null) {
    //usage:db_insert('msmemb',$xarr_msmemb);
    global $db, $debug;
    $str_field='';
    $str_value='';
    while (list($key, $val) = each($xarr_data)) {
      $val=preg_replace('/[\\\\]+/i','',$val);
      if ($str_field!='') {
        $str_field.=",";
        $str_value.=",";
      }
      $str_field.="$key";
      //if ($val=='null') {
      //echo 'val0='.$val."<br/>\n";//.' preg_replace(''/(\\)+/i'', '''', '.$val.');."<br/>\n";
      //$val = preg_replace('/(\\)+/i', '', $val);
      //echo 'val1='.$val."<br/>\n";//.' preg_replace(''/(\\)+/i'', '''', '.$val.');."<br/>\n";*/
      //if (preg_match('/(null|now\(\))/i', $val)) {
			if (strtolower($val)=='null' || strtolower($val)=='now()'  || strtolower($val)=='true' || strtolower($val)=='false') {
        //$str_value.="null";
        $str_value.=preg_replace("/\'/", "",pg_escape_string($val));
      }else
        $str_value.="'".pg_escape_string($val)."'";
    }
    $xpsql="insert into $xtbname ($str_field) values ($str_value);";
    if ($debug)
       echo "db_insert:".$xpsql."<br/>\n";
//else{
    if (!isset($xdb)) {
      $xdb=$db;
    }

    $xr=pg_query($xdb, $xpsql);
    if (!$xr&&$debug) 
      echo "error:".$xpsql."<br/>\n";
//}
  }

  function db_exist_record($xfield,$xtable,$xwhere,$xdb=null) {
    //usage: if (db_exist_record('code', 'msmemb',"where code='".$row['CODE']."'")){
    global $db, $debug;
    if (!isset($xdb))
      $xdb=$db;
    $xsql="select $xfield from $xtable $xwhere";
    if ($debug) echo "db_exist: $xsql<br/>\n";
    $xres=pg_query($xdb, $xsql);
    $xresult=false;
    if (pg_num_rows($xres)>0)
      $xresult=true;
    return $xresult;
  }

  function db_select_record($xfield,$xtable,$xwhere,$xdb=null) {
    global $db, $debug;
    $xsql="select $xfield from $xtable $xwhere";
    if ($debug) echo "//<b>$xsql</b><br/>\n";
    if (!isset($xdb))
      $xdb=$db;

    $xres=pg_query($xdb, $xsql);
    //if ($debug || !$xres) echo "db_select: $xsql ".pg_num_rows($xres)."<br/>\n";
    $xresult=array();
    if (pg_num_rows($xres)>0) {
      $xresult=pg_fetch_assoc($xres, 0);
/*      if ($debug) {
        echo 'xresult=';
        print_r($xresult);
        echo '<br/>';
      }*/
    }
    return $xresult;
  }

  function db_select_record_all($xfield,$xtable,$xwhere=null,$xdb=null) {
    global $db, $debug;
    $xsql="select $xfield from $xtable $xwhere";
    if ($debug) echo "//<b>$xsql</b><br/>\n";  
    if (!isset($xdb))
      $xdb=$db;

    $xres=pg_query($xdb, $xsql);
    $xresult=array();
    if (pg_num_rows($xres)>0) {
      while ($row = pg_fetch_assoc($xres))
						$xresult[]=$row;
    }
    return $xresult;
  }
  
  function db_select_record_multi($xfield,$xtable,$xwhere,$xorder) {
    global $db, $debug;
    if (count($xtable)>1) {
      for ($i=0; $i<count($xtable); $i++) {
        if ($i>0)
          $xsql.="union all ";
        $xsql.="select ".$xfield[$i]." from ".$xtable[$i]." $xwhere ";
      }
    $xsql.=$xorder;
    }else{
      $xsql="select $xfield[0] from $xtable[0] $xwhere $xorder";
    }
    if ($debug) echo "$xsql*<br/>\n";
    $xres=pg_query($db, $xsql);
    $xresult=false;
    if (pg_num_rows($xres)>0) {
      for ($i=0; $i<pg_num_rows($xres); $i++) {
        $xresult=pg_fetch_assoc($xres, $i);
        $xarr_result[]=$xresult;
      }
    }
    return $xarr_result;
  }

  function fbulat_pecahan2($xsource) {
    $xbulat=floor($xsource);
    $xpecahan=$xsource-$xbulat; //0.52
    $xarr_data=split('[\.]', $xsource);
    //echo $xarr_data[0].' '.$xarr_data[1].'<br/>';

    $xpecahan=substr($xarr_data[1],0,2);
    $xdigit_pecahan_1=substr($xpecahan, 0, 1);
    $xdigit_pecahan_2=substr($xpecahan, 1, 1);
    if ($xdigit_pecahan_2==0 || $xdigit_pecahan_2==1 || $xdigit_pecahan_2==2)
      $xpecahan=$xdigit_pecahan_1.'0';
    elseif ($xdigit_pecahan_2==3 || $xdigit_pecahan_2==4)
      $xpecahan=$xdigit_pecahan_1.'5';
    elseif ($xdigit_pecahan_2==6 || $xdigit_pecahan_2==7)
      $xpecahan=$xdigit_pecahan_1.'5';
    elseif ($xdigit_pecahan_2==8 || $xdigit_pecahan_2==9) {
      if ($xdigit_pecahan_1==9) {
        $xbulat+=1;
        $xpecahan='00';
      }else{
        $xpecahan=($xdigit_pecahan_1+1).'0';
      }
    }

    $xresult=$xbulat.'.'.$xpecahan;
    $xselisih=$xresult-$xsource;

    $xarr_result[0]=$xresult;
    $xarr_result[1]=number_format($xselisih,2,'.',',');
    return $xarr_result;
  }

  function fbulat_pecahan($xsource) {
    $xbulat=floor($xsource);
    $xpecahan=$xsource-$xbulat; //0.52
    $xarr_data=split('[\.]', $xsource);
    $xpecahan=substr($xarr_data[1],0,2);
    if (strlen($xpecahan)==1) $xpecahan=$xpecahan*10;

    $xdigit_pecahan_1=substr($xpecahan, 0, 1);
    $xdigit_pecahan_2=substr($xpecahan, 1, 1);
    if ($xpecahan<50) {
      $xselisih=-($xpecahan/100);
      $xarr_result[1]=$xselisih;
      $xarr_result[0]=$xbulat;
      //echo "$xselisih**<br/>\n";
    }else{
      $xselisih=1-((100-$xpecahan)/100);
      $xarr_result[1]=(100-$xpecahan)/100;
      $xarr_result[0]=$xbulat+1;
    }
    return $xarr_result;
  }

  function fget_flag_other_status($xlength, $xother_status, $xdigit) {
    $xstr=substr($xother_status, $xdigit-1, 1);
    if ($xstr=='') $xstr='N';
    return $xstr;
  }

  function fset_flag_other_status($xlength, $xother_status, $xdigit, $xvalue) {
    for ($s=0; $s<=$xlength-1; $s++) {
      $xbit=substr($xother_status, $s, 1);
      if ($xbit=='' || ($xbit!='Y' && $xbit!='N')) $xbit='N';
      if ($s==$xdigit-1) $xbit=$xvalue;
      //echo "$xlength $s. xbit=$xbit<br/>";
      $xresult.=$xbit;
    }
    return $xresult;
  }

  function fwrite_csv($xsql, $xo_filename, $xnull=1) {
    global $db;
    if (file_exists($xo_filename)) {
      unlink($xo_filename);
    }
    $fo = fopen($xo_filename, 'w');
    if (!$fo) {
      exit;
    }
    $xres = pg_exec($db, $xsql);
    for ($i=0; $i<pg_num_rows($xres); $i++) {
      $xrow=pg_fetch_row($xres, $i);
      $xstr='';
      if ($i==0) {
        for ($y=0; $y<pg_num_fields($xres); $y++) {
          if ($y>0)
            $xstr.="\t";
          $xstr.='"'.pg_field_name($xres, $y).'"';
          //$xstr.=pg_fieldtype($xres, $y);
        }
        $xstr.="\n";
        $xstr=strtoupper($xstr);
      }
      for ($y=0; $y<pg_num_fields($xres); $y++) {
        //$xrow[$y]=$xrow[$y];
        $xrow[$y]=addslashes(unicode_to_utf($xrow[$y]));
        if ($y>0)
          $xstr.="\t";
        if (preg_match('/int|float|date|time/', pg_fieldtype($xres, $y)) && $xrow[$y]=='') {
          if ($xnull==1) $xstr.='null';
          else  $xstr.='';
        }else{
          $xstr.='"'.stripslashes($xrow[$y]).'"';
        }
      }
      $xstr.="\n";
//       echo $xstr;
      fputs($fo, $xstr);
    }//end for
    pg_free_result($xres);
    fclose($fo);
  }

  function fchecking_changedata($xsql, $arr_input) {
    //$xberubah=fchecking_changedata($esql, $arr_input);
    global $db, $debug;
    $eres=pg_query($db, $xsql);
    if (pg_num_rows($eres)>0) {
      $erow=pg_fetch_row($eres);
      for ($e=0; $e<pg_num_fields($eres); $e++) {
        $arr_data[]=$erow[$e];
      }
      $xberubah=false;
      for ($k=0; $k<count($arr_input); $k++) {
        if ($arr_input[$k]!='') {
          if ($arr_input[$k]!=$arr_data[$k]) {
            if ($debug) echo $arr_data[$k].' --> '.$arr_input[$k].'<br/>';
            $xberubah=true;
          }
        }
      }
    }
    return $xberubah;
  }

function fdelete_old_files($xfilename, $xdays) {
  global $debug;
  for ($d=$xdays; $d<=$xdays+7; $d++) {
    $mktime_x = mktime(0,0,0,date('m'),date('d')-$d,date('Y'));
    $xfilename1=$xfilename.date('y-m-d', $mktime_x).".csv";
    //if ($debug) echo "checking before delete $xfilename1<br/>\n";
    if (file_exists($xfilename1)) {
//       if ($debug) echo "delete ".$xfilename1."<br/>";
      unlink($xfilename1);
    }

    $xfilename1=$xfilename.date('ymd', $mktime_x).".csv";
    //if ($debug) echo "checking before delete $xfilename1<br/>\n";
    if (file_exists($xfilename1)) {
      //echo $xfilename1."<br/>";
      unlink($xfilename1);
    }

  }
}

function fread_csv2table($uploadfile, $xtable) {
  global $db, $debug;

  $xpsql="DELETE FROM $xtable;";
  if ($debug) {
    echo "$xtable $uploadfile<br/>\n";
    echo "$xpsql<br/>\n";
  }
  pg_exec($db, $xpsql);

//   $xcsql="select * from $xtable limit 1 ";
//   $xcres=pg_query($db, $xpsql);

  //$xarr_index_notprocess=array();
  if (file_exists($uploadfile)) {
/*
    if ($debug)
      echo "$uploadfile***<br/>\n";*/
    $fhandle = fopen ($uploadfile,"r");
    $i=0;
    $xcount_records=0;
    $xarr_pass_index=array();
    while ($xdata = fgetcsv ($fhandle, 1000, "\t")) {
     if ($i==0) {
        $arr_header=$xdata;
/*        if ($debug) {
          print_r($arr_header);
          echo "<br/>\n";
        }*/
        for ($x=0; $x<count($arr_header); $x++) {
          //echo $arr_header[$x]."<br/>\n";
          if ($arr_header[$x]=='FLAG')
            $arr_header[$x]='SYNC_FLAG';
          $arr_header[$x]=preg_replace('/\"/','',$arr_header[$x]);
          if ($arr_header[$x]=='foreign')
            $arr_header[$x]='"foreign"';

          //if ($xtable=='xtemp_msmemb') {
            if (preg_match('/mlm|trcd/',$arr_header[$x])) {
              //$xarr_index_notprocess[]=$x;
            }else{
              if ($x==0)
                $xfields=$arr_header[$x];
              else
                $xfields.=",".$arr_header[$x];
            }
          //}

        }
        //if ($debug) {
        //  echo "$xfields<br/>\n";
        //}

      }else{
        //print_r($xarr_index_notprocess);
        /*if ($debug) {
          print_r($xdata);
          echo "<br/>\n";
        }*/
        for ($x=0; $x<count($xdata); $x++) {
/*
          if (($arr_header[$x]=='SYNC_DATE' || $arr_header[$x]=='LUPDDT' ||
            $arr_header[$x]=='LUPDTM')
*/
          //if (!in_array($x, $xarr_index_notprocess)) {
            //echo "$x<br/>\n";
            if (preg_match('/sync_date|lupddt|lupdtm|birthdt|joindt|jointm|etdt|lupddt|lupdtm|trdt|expired_date|admupdt|flagdt/i', $arr_header[$x])
              && ($xdata[$x]=='' || $xdata[$x]=='\N') )
            {
              if ($x==0)
                $xvalues="null";
              else
                $xvalues.=",null";
            }elseif (preg_match('/loccd/i', $arr_header[$x]))
            {
              $xdata[$x]=str_pad($xdata[$x], 9, '0', STR_PAD_LEFT);
              if ($x==0)
                $xvalues=$xdata[$x];
              else
                $xvalues.=",".$xdata[$x];
            }elseif (preg_match('/ship_cost|incp_fee/i', $arr_header[$x])
              && ($xdata[$x]=='' || $xdata[$x]=='\N')
            ){
              if ($x==0)
                $xvalues="0";
              else
                $xvalues.=",0";
            }else{
              $xdata[$x]=preg_replace('/\"/','',$xdata[$x]);
              if ($xdata[$x]=='\N')
                $xdata[$x]='';
              if ($x==0)
                $xvalues="'".$xdata[$x]."'";
              else
                $xvalues.=",'".$xdata[$x]."'";
            }//end if
         // }//end if

        }//end for
        if (trim($xfields)!='' && !preg_match('/DOCTYPE/', $xvalues)) {
          $xpsql="INSERT INTO $xtable ($xfields) VALUES ($xvalues);";
          if ($debug) echo "<i>$xpsql</i><br/>\n";
          $xpres=pg_query($db, $xpsql);
          if ($xpres) {
            $xcount_records++;
          }
        }
      }
      $i++;
    }
    //print_r($arr_header);
    //if ($debug) echo "get $xcount_records records.<br/>\n";
    fclose($fhandle);
  }
  $xrow1=fpg_exec4("select min(trcd) as xtrcd from $xtable");
  $xrow2=fpg_exec4("select max(trcd) as xtrcd from $xtable");
  $xarr_result = array('count_record'=>$xcount_records, 'min_trcd'=>$xrow1[0], 'max_trcd'=>$xrow2[0]);

  if ($xtable=='xtemp_newmstrh') {
    $xpsql="delete from temp_newmstrh where trcd in (select trcd from xtemp_newmstrh)";
    if ($debug) echo "$xpsql<br/>\n";
    $xpres=pg_query($db, $xpsql);
    $xpsql="insert into temp_newmstrh select * from xtemp_newmstrh ";
    if ($debug) echo "$xpsql<br/>\n";
    $xpres=pg_query($db, $xpsql);
  }elseif ($xtable=='xtemp_newmstrd') {
    $xpsql="delete from temp_newmstrd where trcd in (select trcd from xtemp_newmstrd)";
    if ($debug) echo "$xpsql<br/>\n";
    $xpres=pg_query($db, $xpsql);
    $xpsql="insert into temp_newmstrd select * from xtemp_newmstrd ";
    if ($debug) echo "$xpsql<br/>\n";
    $xpres=pg_query($db, $xpsql);
  }elseif ($xtable=='xtemp_mspayment') {
    $xpsql="delete from temp_mspayment where trcd in (select trcd from xtemp_mspayment)";
    if ($debug) echo "$xpsql<br/>\n";
    $xpres=pg_query($db, $xpsql);
    $xpsql="insert into temp_mspayment select * from xtemp_mspayment ";
    if ($debug) echo "$xpsql<br/>\n";
    $xpres=pg_query($db, $xpsql);
  }elseif ($xtable=='xtemp_delnewmstrh') {
    $xpsql="delete from temp_delnewmstrh where trcd in (select trcd from xtemp_delnewmstrh)";
    if ($debug) echo "$xpsql<br/>\n";
    $xpres=pg_query($db, $xpsql);
    $xpsql="insert into temp_delnewmstrh select * from xtemp_delnewmstrh ";
    if ($debug) echo "$xpsql<br/>\n";
    $xpres=pg_query($db, $xpsql);
  }elseif ($xtable=='xtemp_delnewmstrd') {
    $xpsql="delete from temp_delnewmstrd where trcd in (select trcd from xtemp_delnewmstrd)";
    if ($debug) echo "$xpsql<br/>\n";
    $xpres=pg_query($db, $xpsql);
    $xpsql="insert into temp_delnewmstrd select * from xtemp_delnewmstrd ";
    if ($debug) echo "$xpsql<br/>\n";
    $xpres=pg_query($db, $xpsql);
  }elseif ($xtable=='xtemp_delmspayment') {
    $xpsql="delete from temp_delmspayment where trcd in (select trcd from xtemp_delmspayment)";
    if ($debug) echo "$xpsql<br/>\n";
    $xpres=pg_query($db, $xpsql);
    $xpsql="insert into temp_delmspayment select * from xtemp_delmspayment ";
    if ($debug) echo "$xpsql<br/>\n";
    $xpres=pg_query($db, $xpsql);
  }
  return $xarr_result;
}

function fpg_exec4($xsql, $xtype=0) {
  global $db, $debug, $opnm;
  //if ($debug) echo "$xsql<br/>";
  $xres1=pg_query($db, $xsql);
  if (!$xres1 && $debug) echo "error fpg_exec=".$xsql."<br/>";
  if (pg_num_rows($xres1)>0) {
    if ($xtype==0)
      $xrow1=pg_fetch_row($xres1, 0);
    else
      $xrow1=pg_fetch_assoc($xres1, 0);
  }
  pg_free_result($xres1);
  return $xrow1;
}

function fprocess_POS($xdb) {
  $arr_result=array(
      'xvalid_all'=>$xvalid_all,
      'xvalid_qoh'=>$xvalid_qoh,
      'xstr_email_err'=>$xstr_email_err,'xstr_email_ok'=>$xstr_email_ok,
      'xcount_trh_trcd'=>$xcount_trh_trcd,
      'xvalid_price'=>$xvalid_price, 'xstr_price_err'=>$xstr_price_err, 'xstr_price_ok'=>$xstr_price_ok,
      'xvalid_msmemb'=>$xvalid_msmemb, 'xstr_msmemb_err'=>$xstr_msmemb_err);
  return $arr_result;
}

/*
function fprocess_POS($xdb) {
  global $db, $debug, $xschema;
  //if ($debug) echo "xschema=$xschema<br/>\n";
  //if ($xschema!='')
  if ($xschema!='' && !ereg('[\.]', $xschema))
    $xschema=$xschema.'.';
  $bresult=pg_query($db,"begin transaction");
  $xsql1="SELECT * FROM ".$xschema."temp_newmstrh ".
    "where (sync_flag is null or sync_flag='' ".
    "or sync_flag='0' or sync_flag='2') ".
    "ORDER BY TRCD";
  if ($debug) echo "<b>$xsql1</b><br/>\n";
  $xres1=pg_query($xdb, $xsql1);
  //echo pg_num_rows($xres1)."<br/>";
  $xvalid_all=true;
  $xcount_trh_trcd=0;
  $xcount_trd_trcd=0;
  $xcount_pay_trcd=0;
  for ($i=0; $i<pg_num_rows($xres1); $i++) {
    //$xcount_trh_trcd++;
    $xrow1=pg_fetch_assoc($xres1, $i);
    unset($arr_trh_data);
    $arr_trh_data=array();//reset
    $xsql2="SELECT * FROM ".$xschema."temp_newmstrd WHERE TRCD='".$xrow1['trcd']."' ";
    $xres2=pg_query($xdb, $xsql2);
    if ($debug) echo "<b>$xsql2</b><br/>\n";
    $xvalid_trcd=true;
    //echo $xrow1['trcd']." <br/>\n";
    for ($j=0; $j<pg_num_rows($xres2); $j++) {
      $xrow2=pg_fetch_assoc($xres2, $j);
      //$xcount_trd_trcd++;
      $xqoh=hitungqoh_basic($xrow2['prdcd'], $xrow1['loccd']);
      //fget_br_monthly_stock($xloccd, $xprdcd, $xdate)
      $xget_qoh=fget_br_monthly_stock($xrow1['loccd'], $xrow2['prdcd'], date('d/m/Y'));
      if ($debug) echo $xrow1['loccd'].' '.$xrow2['trcd'].' '.$xrow2['prdcd'].' '.$xrow2['qty']." $xqoh $xget_qoh<br/>\n";
      if ($xqoh!=$xget_qoh){
        $xcsql="select qoh from inloc where loccd='".$xrow1['loccd']."' and prdcd='".$xrow2['prdcd']."'";
        $xcres=pg_query($db, $xcsql);
        if (pg_num_rows($xcres)>0) {
          $xsql="update inloc set qoh=$xget_qoh where loccd='".$xrow1['loccd']."' and prdcd='".$xrow2['prdcd']."'";
        }else{
          $xsql="insert into inloc (loccd,prdcd,qoh) values ('".$xrow1['loccd']."','".$xrow2['prdcd']."',$xget_qoh);";
        }
        if ($debug) echo "$xsql<br/>\n";
        pg_exec($db, $xsql);
        pg_free_result($xcres);
      }

      if ($xget_qoh<$xrow2['qty']) {
        //if ($debug) echo "$xget_qoh<".$xrow2['qty']." <br/>\n";
        $xvalid_all =false;
        $xvalid_trcd=false;
        $xstr_email_err.=
          str_pad($xrow1['trcd'], 20, " ", STR_PAD_RIGHT).
          str_pad($xrow2['prdcd'], 15, " ", STR_PAD_RIGHT).
          str_pad($xqoh, 10, " ", STR_PAD_LEFT).
          str_pad($xrow2['qty'], 10, " ", STR_PAD_LEFT)."<br/>\n";
        //break;
        if (!isset($xtrh_trcd_err))
          $xtrh_trcd_err=array();
        if (!in_array($xrow2['trcd'], $xtrh_trcd_err))
          $xtrh_trcd_err[]=$xrow2['trcd'];
        if (!isset($xtrd_trcd_err))
          $xtrd_trcd_err=array();
        $xtrd_trcd_err[]=$xrow2['trcd'];

        //$xcount_trd_trcd--;
        //$xcount_trh_trcd--;
      }else{
        if (!isset($xtrh_trcd))
          $xtrh_trcd=array();
        if (!in_array($xrow1['trcd'], $xtrh_trcd))
          $xtrh_trcd[]=$xrow1['trcd'];
        if (!isset($xtrd_trcd))
          $xtrd_trcd=array();
        $xtrd_trcd[]=$xrow1['trcd'];

      }


    }//end for

    if ($debug) echo "xvalid_trcd=$xvalid_trcd --> qoh=".(!$xvalid_trcd?'not enough':'enough')."<BR/>\n";
    if ($xvalid_trcd) {
      if ($debug) echo "<i>".$xrow1['trcd']." $xvalid_trcd</i><br/>\n";
      for ($k=0; $k<pg_num_fields($xres1); $k++) {
          if (!eregi('FLAG|FRMSYS|IS_SYNC|SYNC_DATE|IS_SENT',pg_fieldname($xres1, $k)))
          {
            if (eregi('LUPDDT|LUPDTM|SYNC_DATE|TRTM|DATEPOST',pg_fieldname($xres1, $k))
            && $xrow1[pg_fieldname($xres1, $k)]=='')
            {
              $arr_trh_data[pg_fieldname($xres1, $k)]='null';
            }elseif (eregi('TRTYPE', pg_fieldname($xres1, $k))) {
              if ($xrow1['trtype']=='1')
                $arr_trh_data['trtype']='1';
              elseif ($xrow1['trtype']=='2') {
                $arr_trh_data['trtype']='6';
                $xrow1['staff_name']='WALK IN CUSTOMER';
                $xrow1['code']='*********';
              }
            }else{
              $arr_trh_data[pg_fieldname($xres1, $k)]=$xrow1[pg_fieldname($xres1, $k)];
            }//end if
          }//end if
      }//end for
      //echo "$xfields<br/>\n";
      $xcsql="select trcd from newmstrh where trcd='".$xrow1['trcd']."'";
      if ($debug) echo "$xcsql<br/>\n";
      $xcres=pg_query($db, $xcsql);
      if (pg_num_rows($xcres)==0) {
//         print_r($arr_trh_data);
        $arr_trh_data['frmsys']='2';
        db_insert('newmstrh', $arr_trh_data);
      }

      /////////DETAIL/////////
      for ($l=0; $l<pg_num_rows($xres2); $l++) {
        $xrow2_=pg_fetch_assoc($xres2, $l);
        for ($m=0; $m<pg_num_fields($xres2); $m++) {
          if (!eregi('FLAG|IS_SYNC|SYNC_DATE',pg_fieldname($xres2, $m)))
          {
            if ($m==0) {
              $xfields2=pg_fieldname($xres2, $m);
              $xvalues2="'".$xrow2_[pg_fieldname($xres2, $m)]."'";
            }else{
              $xfields2.=",".pg_fieldname($xres2, $m);
              $xvalues2.=",'".$xrow2_[pg_fieldname($xres2, $m)]."'";
            }
          }
        }
        $xcsql="select trcd from newmstrd where trcd='".$xrow2_['trcd']."' and prdcd='".$xrow2_['prdcd']."'";
        if ($debug) {
          echo $xcsql."*<br/>\n";
        }
        $xcres=pg_query($db, $xcsql);
        if (pg_num_rows($xcres)==0) {
          $xpsql2="INSERT INTO newmstrd ($xfields2) VALUES ($xvalues2)";
          if ($debug) {
            echo $xpsql2."*<br/>\n";
          }
          pg_exec($db, $xpsql2);
          update_inloc_basic($xrow2_['prdcd'],$xrow2_['qty'],$debug,$xrow1['loccd'],0,'-');
        }
      }

      /////////PAYMENT/////////
      $xsql3="SELECT * FROM ".$xschema."temp_mspayment WHERE TRCD='".$xrow1['trcd']."'";
      if ($debug) echo $xsql3."<br/>\n";
      $xres3=pg_query($xdb, $xsql3);
      for ($ii=0; $ii<pg_num_rows($xres3); $ii++) {
        $xrow3=pg_fetch_assoc($xres3, $ii);
        for ($jj=0; $jj<pg_num_fields($xres3); $jj++) {
          if (!eregi('FLAG|IS_SYNC|SYNC_DATE',pg_fieldname($xres3, $jj)))
          {
            if ($jj==0) {
              $xfields3=pg_fieldname($xres3, $jj);
              $xvalues3="'".$xrow3[pg_fieldname($xres3, $jj)]."'";
            }else{
              $xfields3.=",".pg_fieldname($xres3, $jj);
              $xvalues3.=",'".$xrow3[pg_fieldname($xres3, $jj)]."'";
            }
          }
        }
      }
      pg_free_result($xres3);

      $xcsql3="select trcd from mspayment where trcd='".$xrow1['trcd']."'";
      $xcres3=pg_query($db, $xcsql3);
      if (pg_num_rows($xcres)==0) {
        $xpsql3="INSERT INTO mspayment ($xfields3) VALUES ($xvalues3)";
        if ($debug) {
          echo $xpsql3."<br/>\n";
        }
        pg_exec($db, $xpsql3);
      }
      pg_free_result($xcres3);


      $xpsql1="UPDATE ".$xschema."temp_newmstrh set sync_flag='1',is_sync='1',sync_date=now() where trcd='".$xrow1['trcd']."'";
      if ($debug)
        echo "$xpsql1<br/>\n";
      pg_exec($xdb, $xpsql1);

      $xpsql1="UPDATE ".$xschema."temp_newmstrd set sync_flag='1' where trcd='".$xrow1['trcd']."'";
      if ($debug)
        echo "$xpsql1<br/>\n";
      pg_exec($xdb, $xpsql1);

      $xpsql1="UPDATE ".$xschema."temp_mspayment set sync_flag='1' where trcd='".$xrow1['trcd']."'";
      if ($debug)
        echo "$xpsql1<br/>\n";
      pg_exec($xdb, $xpsql1);

    }else{//else if valid
      $xpsql1="UPDATE ".$xschema."temp_newmstrh set sync_flag='2' where trcd='".$xrow1['trcd']."'";
      if ($debug)
        echo "$xpsql1<br/>\n";
      pg_exec($xdb, $xpsql1);

      $xpsql1="UPDATE ".$xschema."temp_newmstrd set sync_flag='2' where trcd='".$xrow1['trcd']."'";
      if ($debug)
        echo "$xpsql1<br/>\n";
      pg_exec($xdb, $xpsql1);

      $xpsql1="UPDATE ".$xschema."temp_mspayment set sync_flag='2' where trcd='".$xrow1['trcd']."'";
      if ($debug)
        echo "$xpsql1<br/>\n";
      pg_exec($xdb, $xpsql1);

    }//end if valid
    pg_free_result($xres2);
  }
  pg_free_result($xres1);
  if ($debug==0) {
    $bresult=pg_query($db,"commit transaction");
  }else{
    //echo "no commit<br/>\n";
  }

      if ($debug) {
        print_r($xtrh_trcd_err);
        echo "xtrh_trcd_err <br/>\n";
        print_r($xtrh_trcd);
        echo "xtrh_trcd <br/>\n";
      }
  if (isset($xtrh_trcd)) {
    sort ($xtrh_trcd);
    reset ($xtrh_trcd);
      for ($z=0; $z<count($xtrh_trcd); $z++) {
        //echo $xtrh_trcd[$z]."<br/>\n";
        if (!isset($xtrh_trcd_err))
          $xtrh_trcd_err=array();
        if (!in_array($xtrh_trcd[$z], $xtrh_trcd_err)) {
          if ($xmin_trh_trcd=='')
            $xmin_trh_trcd=$xtrh_trcd[$z];
          $xmax_trh_trcd=$xtrh_trcd[$z];
          $xcount_trh_trcd++;
        }
      }
  }
  if (isset($xtrd_trcd)) {

      sort ($xtrd_trcd);
      reset ($xtrd_trcd);
      if (!isset($xtrd_trcd_err))
        $xtrd_trcd_err=array();

      for ($z=0; $z<count($xtrd_trcd); $z++) {
        //echo $xtrd_trcd[$z]."<br/>\n";
        if (!in_array($xtrd_trcd[$z], $xtrd_trcd_err)) {
          $xcount_trd_trcd++;
        }
      }

    $xstr_email_ok=
      "[temp_newmstrh] \n".
      "number of records = $xcount_trh_trcd \n".
      "first transaction code =  $xmin_trh_trcd \n".
      "last transaction code =  $xmax_trh_trcd \n\n".
      "[temp_newmstrd] \n".
      "number of records = $xcount_trd_trcd \n".
      "first transaction code =  $xmin_trh_trcd \n".
      "last transaction code =  $xmax_trh_trcd \n\n".
      "[temp_mspayment] \n".
      "number of records = $xcount_trh_trcd \n".
      "first transaction code =  $xmin_trh_trcd \n".
      "last transaction code =  $xmax_trh_trcd \n\n";

    $arr_result=array('xvalid'=>$xvalid_all, 'xstr_email_err'=>$xstr_email_err,
      'xstr_email_ok'=>$xstr_email_ok, 'xcount_trh_trcd'=>$xcount_trh_trcd);
    return $arr_result;
  }
}
*/
function fprocess_POS_delete($xdb) {
  global $db, $debug, $xopnm, $xschema;
  $bresult=pg_query($db,"begin transaction");
  if ($xschema!='' && !ereg('[\.]', $xschema))
    $xschema=$xschema.'.';
//   if ($debug)
//     echo "xschema=$xschema<br/>\n";

  $xsql1="SELECT * FROM ".$xschema."temp_delnewmstrh ".
    "where (sync_flag is null or sync_flag='' ".
    "or sync_flag='0' or sync_flag='2') ".
    //"and trcd<>'POS-MP100709000071' ".
    "ORDER BY TRDT, TRCD";

  if ($debug) echo "$xsql1<br/>\n";
  $xres1=pg_query($xdb, $xsql1);
  $xcount_trh_trcd=0;
  $xcount_trd_trcd=0;
  for ($i=0; $i<pg_num_rows($xres1); $i++) {
    $xrow1=pg_fetch_assoc($xres1, $i);
    if ($debug) echo "<b>".$xrow1['trcd']."</b><br/>\n";
    $xcount_trh_trcd++;
    if ($xmin_trh_trcd=='')
      $xmin_trh_trcd=$xrow1['trcd'];
    $xmax_trh_trcd=$xrow1['trcd'];
    //if ($debug) echo $xrow1['trcd']."<br/>\n";
    $trcd_del=$xrow1['trcd'];
    $xsql2="select code,bc_id,loccd from newmstrh where trcd='$trcd_del'";
    if ($debug) echo "<i>".$xsql2."</i><br/>\n";
    $xres2=pg_query($db, $xsql2);
    if (pg_num_rows($xres2)>0) {//checking exist trcd in newmstrh
      $xcode =pg_fetch_result($xres2, 0, 0);
      $xbc_id=pg_fetch_result($xres2, 0, 1);
      $xloccd=pg_fetch_result($xres2, 0, 2);

      $xcsql="select trcd from delmstrh where trcd='$trcd_del'";
      $xcres=pg_query($db, $xcsql);
      if (pg_num_rows($xcres)==0) {
        $xquerr=
          "insert into delmstrh
          (
          trcd, trtype, code, bc_id, upcode,
          trdt, etdt, loccd, tdp, tpv, tbv,
          pdisc, ptax, ndp, npv, nbv,
          totpay, note1, note2, note3, createnm,
          lupddt, lupdtm, opnm, send, flag,
          status, post, datepost, cbno, staff_name,
          trtm, bpdt
          )
          select
          trcd, trtype, code, bc_id, upcode,
          trdt, etdt, loccd, tdp, tpv, tbv,
          pdisc, ptax, ndp, npv, nbv,
          totpay, note1, note2, note3, createnm,
          lupddt, lupdtm, opnm, send, flag,
          status, post, datepost, cbno, staff_name,
          trtm, bpdt
          from newmstrh where trcd='$trcd_del' ";

        $xresult  = pg_exec($db,$xquerr);
        if($debug==1) print $xquerr."<br/>";

        $xquerr   = "insert into delmstrd select * from newmstrd where trcd='$trcd_del'";
        $xresult  = pg_exec($db,$xquerr);
        if($debug==1) print $xquerr."<br/>";

        $xquerr   = "insert into delmspayment select * from mspayment where trcd='$trcd_del'";
        $xresult  = pg_exec($db,$xquerr);
        if($debug==1) print $xquerr."<br/>";

        $xquerr   = "insert into delmsship select * from msship where trcd='$trcd_del'";
        $xresult  = pg_exec($db,$xquerr);
        if($debug==1) print $xquerr."<br/>";

        $xquerr   = "update delmstrh set opnm='$xopnm',etdt=date(now()),reason='$txtremark' where trcd='$trcd_del'";
        $xresult  = pg_exec($db,$xquerr);
        if($debug==1) print $xquerr."<br/>";

        $querrtrd = "select qty,trim(prdcd) from newmstrd where trcd = '$trcd_del'";
        $res_trd  = pg_exec($db,$querrtrd);
        if($debug==1) print $querrtrd."<br/>";

        for ($nI=0;$nI < pg_numrows( $res_trd );$nI++) {
          $preqty = 0;
          $row_trd = pg_fetch_row( $res_trd,$nI );
          $preqty = $row_trd[0];
          update_inloc($row_trd[1],$preqty,$debug,$xloccd,0,'+');
        }
        pg_free_result($res_trd);
      }// end if
      $xsql2="SELECT trcd FROM ".$xschema."temp_delnewmstrd ".
        "WHERE TRCD='$trcd_del'";
      $xres2=pg_query($xdb, $xsql2);
      if ($debug) echo "**$xsql2***<br/>\n";
      //echo $xrow1['trcd']." <br/>\n";
      for ($ji=0; $ji<pg_num_rows($xres2); $ji++) {
        $xrow2_=pg_fetch_assoc($xres2, $ji);
        $xcount_trd_trcd++;
        if ($xmin_trd_trcd=='')
          $xmin_trd_trcd=$xrow2_['trcd'];
        $xmax_trd_trcd=$xrow2_['trcd'];
      }
      pg_free_result($xres2);

      $xquerr   = "delete from newmstrh where trcd='$trcd_del'";
      $xresult  = pg_exec($db,$xquerr);
      if($debug==1) print $xquerr."<br>";
      if ($xuse_bts) fdelete_barcode($trcd_del);

      $xquerr   = "delete from newmstrd where trcd='$trcd_del'";
      $xresult  = pg_exec($db,$xquerr);
      if($debug==1) print $xquerr."<br>";

      $xquerr   = "delete from mspayment where trcd='$trcd_del'";
      $xresult  = pg_exec($db,$xquerr);
      if($debug==1) print $xquerr."<br>";

      $xquerr   = "delete from mspayment_info where trcd='$trcd_del'";
      $xresult  = pg_exec($db,$xquerr);
      if($debug==1) print $xquerr."<br>";

    }else{
      //not found in newmstrh
/*      $xsql1="SELECT * FROM ".$xschema."temp_delnewmstrh ORDER BY TRDT, TRCD";
      if ($debug) echo "$xsql1<br/>\n";
      $xres1=pg_query($xdb, $xsql1);
      $xvalid_all=true;
      for ($i=0; $i<pg_num_rows($xres1); $i++) {
        $xrow1=pg_fetch_assoc($xres1, $i);
*/
        $xsql2="SELECT * FROM ".$xschema."temp_delnewmstrd WHERE TRCD='".$xrow1['trcd']."'";
        $xres2=pg_query($xdb, $xsql2);
        if ($debug) echo "$xsql2<br/>\n";
        $xvalid_trcd=true;
        //echo $xrow1['trcd']." <br/>\n";
        for ($j=0; $j<pg_num_rows($xres2); $j++) {
          $xrow2=pg_fetch_assoc($xres2, $j);
          //$xqoh=hitungqoh_basic($xrow2['prdcd'], $xrow1['loccd']);
          //if ($debug) echo $xrow1['loccd'].' '.$xrow2['trcd'].' '.$xrow2['prdcd'].' '.$xrow2['qty']." $xqoh<br/>\n";
        }
        //if ($debug) echo "valid=$xvalid --> qoh=".(!$xvalid?'not enough':'enough')."<BR/>\n";
        if ($xvalid_trcd) {
          if ($debug) echo $xrow1['trcd']." $xvalid_trcd <br/>\n";
          for ($k=0; $k<pg_num_fields($xres1); $k++) {
              if (!eregi('FLAG|FRMSYS|IS_SYNC|SYNC_DATE|IS_SENT',pg_fieldname($xres1, $k)))
              {
                if (eregi('LUPDDT|LUPDTM|SYNC_DATE|TRTM|DATEPOST',pg_fieldname($xres1, $k))
                && $xrow1[pg_fieldname($xres1, $k)]=='')
                {
                  if ($k==0){
                    $xfields=pg_fieldname($xres1, $k);
                    $xvalues="null";
                  }else{
                    $xfields.=",".pg_fieldname($xres1, $k);
                    $xvalues.=",null";
                  }
                }elseif (eregi('TRTYPE', pg_fieldname($xres1, $k))) {
                  if ($k==0){
                    $xfields=pg_fieldname($xres1, $k);
                    $xvalues.="'12'";
                  }else{
                    $xfields.=",".pg_fieldname($xres1, $k);
                    $xvalues.=",'12'";
                  }
                }else{
                  if ($k==0) {
                    $xfields=pg_fieldname($xres1, $k);
                    $xvalues="'".$xrow1[pg_fieldname($xres1, $k)]."'";
                  }else{
                    $xfields.=",".pg_fieldname($xres1, $k);
                    $xvalues.=",'".$xrow1[pg_fieldname($xres1, $k)]."'";
                  }
                }//end if
              }//end if
          }//end for
          //echo "$xfields<br/>\n";
          $xcsql="select trcd from delmstrh where trcd='".$xrow1['trcd']."'";
          if ($debug) echo "$xcsql<br/>\n";
          $xcres=pg_query($db, $xcsql);
          if (pg_num_rows($xcres)==0) {
            $xpsql="INSERT INTO delmstrh ($xfields,frmsys) VALUES ($xvalues,'2')";
            if ($debug) {
              echo $xpsql."<br/>\n";
            }
            pg_exec($db, $xpsql);
          }

          /////////DETAIL/////////
          for ($l=0; $l<pg_num_rows($xres2); $l++) {
            $xrow2_=pg_fetch_assoc($xres2, $l);
            for ($m=0; $m<pg_num_fields($xres2); $m++) {
              if (!eregi('FLAG|IS_SYNC|SYNC_DATE',pg_fieldname($xres2, $m)))
              {
                if ($m==0) {
                  $xfields2=pg_fieldname($xres2, $m);
                  $xvalues2="'".$xrow2_[pg_fieldname($xres2, $m)]."'";
                }else{
                  $xfields2.=",".pg_fieldname($xres2, $m);
                  $xvalues2.=",'".$xrow2_[pg_fieldname($xres2, $m)]."'";
                }
              }
            }
            $xcsql="select trcd from delmstrd where trcd='".$xrow2_['trcd']."' and prdcd='".$xrow2_['prdcd']."'";
            if ($debug) {
              echo $xcsql."*<br/>\n";
            }
            $xcres=pg_query($db, $xcsql);
            if (pg_num_rows($xcres)==0) {
              $xpsql2="INSERT INTO delmstrd ($xfields2) VALUES ($xvalues2)";
              if ($debug) {
                echo $xpsql2."<br/>\n";
              }
              pg_exec($db, $xpsql2);
              //update_inloc_basic($xrow2_['prdcd'],$xrow2_['qty'],$debug,$xrow1['loccd'],0,'-');
            }

            $xcount_trd_trcd++;
            if ($xmin_trd_trcd=='')
              $xmin_trd_trcd=$xrow2_['trcd'];
            $xmax_trd_trcd=$xrow2_['trcd'];


          }

          /////////PAYMENT/////////
          $xsql3="SELECT * FROM ".$xschema."temp_delmspayment WHERE TRCD='".$xrow1['trcd']."'";
          if ($debug) echo $xpsql3."<br/>\n";
          $xres3=pg_query($xdb, $xsql3);
          for ($ii=0; $ii<pg_num_rows($xres3); $ii++) {
            $xrow3=pg_fetch_assoc($xres3, $ii);
            for ($jj=0; $jj<pg_num_fields($xres3); $jj++) {
              if (!eregi('FLAG|IS_SYNC|SYNC_DATE',pg_fieldname($xres3, $jj)))
              {
                if ($jj==0) {
                  $xfields3=pg_fieldname($xres3, $jj);
                  $xvalues3="'".$xrow3[pg_fieldname($xres3, $jj)]."'";
                }else{
                  $xfields3.=",".pg_fieldname($xres3, $jj);
                  $xvalues3.=",'".$xrow3[pg_fieldname($xres3, $jj)]."'";
                }
              }
            }
          }
          pg_free_result($xres3);

          $xcsql3="select trcd from delmspayment where trcd='".$xrow1['trcd']."'";
          $xcres3=pg_query($db, $xcsql3);
          if (pg_num_rows($xcres)==0) {
            $xpsql3="INSERT INTO delmspayment ($xfields3) VALUES ($xvalues3)";
            pg_exec($db, $xpsql3);
          }
          if ($debug) {
            echo $xpsql3."<br/>\n";
          }
          pg_free_result($xcres3);

/*
          $xpsql1="UPDATE ".$xschema."temp_delnewmstrh set sync_flag='1',is_sync='1',sync_date=now() where trcd='".$xrow1['trcd']."'";
          if ($debug)
            echo "$xpsql1<br/>\n";
          pg_exec($xdb, $xpsql1);

          $xpsql1="UPDATE ".$xschema."temp_delnewmstrd set sync_flag='1' where trcd='".$xrow1['trcd']."'";
          if ($debug)
            echo "$xpsql1<br/>\n";
          pg_exec($xdb, $xpsql1);

          $xpsql1="UPDATE ".$xschema."temp_delmspayment set sync_flag='1' where trcd='".$xrow1['trcd']."'";
          if ($debug)
            echo "$xpsql1<br/>\n";
          pg_exec($xdb, $xpsql1);
*/
        }else{//else if valid
    /*
          $xpsql1="UPDATE temp_delnewmstrh set sync_flag='2' where trcd='".$xrow1['trcd']."'";
          if ($debug)
            echo "$xpsql1<br/>\n";
          pg_exec($db, $xpsql1);

          $xpsql1="UPDATE temp_delnewmstrd set sync_flag='2' where trcd='".$xrow1['trcd']."'";
          if ($debug)
            echo "$xpsql1<br/>\n";
          pg_exec($db, $xpsql1);

          $xpsql1="UPDATE temp_delmspayment set sync_flag='2' where trcd='".$xrow1['trcd']."'";
          if ($debug)
            echo "$xpsql1<br/>\n";
          pg_exec($db, $xpsql1);
    */
        }//end if valid
        pg_free_result($xres2);
      //}end for
      //pg_free_result($xres1);
      //if ($debug==0) $bresult=pg_query($db,"commit transaction");
    }
    //pg_free_result($xres2);

    $xpsql1="UPDATE ".$xschema."temp_delnewmstrh set sync_flag='1',is_sync='1',sync_date=now() where trcd='".$xrow1['trcd']."'";
    if ($debug)
      echo "$xpsql1<br/>\n";
    pg_exec($xdb, $xpsql1);

    $xpsql1="UPDATE ".$xschema."temp_delnewmstrd set sync_flag='1' where trcd='".$xrow1['trcd']."'";
    if ($debug)
      echo "$xpsql1<br/>\n";
    pg_exec($xdb, $xpsql1);

    $xpsql1="UPDATE ".$xschema."temp_delmspayment set sync_flag='1' where trcd='".$xrow1['trcd']."'";
    if ($debug)
      echo "$xpsql1<br/>\n";
    pg_exec($xdb, $xpsql1);

  }//end for
  if ($debug==0) $bresult=pg_query($db,"commit transaction");
  $xstr_email_ok=
    "[temp_delnewmstrh] \n".
    "number of records = $xcount_trh_trcd \n".
    "first transaction code =  $xmin_trh_trcd \n".
    "last transaction code =  $xmax_trh_trcd \n\n".
    "[temp_delnewmstrd] \n".
    "number of records = $xcount_trd_trcd \n".
    "first transaction code =  $xmin_trd_trcd \n".
    "last transaction code =  $xmax_trd_trcd \n\n".
    "[temp_delmspayment] \n".
    "number of records = $xcount_trh_trcd \n".
    "first transaction code =  $xmin_trh_trcd \n".
    "last transaction code =  $xmax_trh_trcd \n\n";
  $arr_result=array(
    'xstr_email_ok'=>$xstr_email_ok, 'xcount_trh_trcd'=>$xcount_trh_trcd);
  return $arr_result;
}


function write_ini_file($assoc_arr, $path, $has_sections=FALSE) {
    $content = "";
    if ($has_sections) {
        foreach ($assoc_arr as $key=>$elem) {
            $content .= "[".$key."]\n";
            foreach ($elem as $key2=>$elem2) {
                if(is_array($elem2))
                {
                    for($i=0;$i<count($elem2);$i++)
                    {
                        $content .= $key2."[] = \"".$elem2[$i]."\"<br/>\n";
                    }
                }
                else if($elem2=="") $content .= $key2." = \n";
                else $content .= $key2." = \"".$elem2."\"<br/>\n";
            }
        }
    }
    else {
        foreach ($assoc_arr as $key=>$elem) {
            if(is_array($elem))
            {
                for($i=0;$i<count($elem);$i++)
                {
                    $content .= $key2."[] = \"".$elem[$i]."\"<br/>\n";
                }
            }
            else if($elem=="") $content .= $key2." = \n";
            else $content .= $key2." = \"".$elem."\"<br/>\n";
        }
    }

    if (!$handle = fopen($path, 'w')) {
        return false;
    }
    if (!fwrite($handle, $content)) {
        return false;
    }
    fclose($handle);
    return true;
}

function f_str_split($str, $l=1) {
  $str_array = explode("\r\n", chunk_split($str,$l));
  return $str_array;
}
function futf8_to_html ($data) {
  return preg_replace("/([\\xC0-\\xF7]{1,1}[\\x80-\\xBF]+)/e", 'f_utf8_to_html("\\1")', $data);
}
function f_utf8_to_html ($data){
  $ret = 0;
  foreach((f_str_split(strrev(chr((ord($data{0}) % 252 % 248 % 240 % 224 % 192) + 128) . substr($data, 1)))) as $k => $v)
    $ret += (ord($v) % 128) * pow(64, $k);
  return "&#$ret;";
}

function fencoding_($xsource) {
  $xresult=addslashes(unicode_to_utf(futf8_to_html(iconv("UTF-8","UTF-8//IGNORE",$xsource))));
  //$xresult=unicode_to_utf($xresult);
  return $xresult;
}
/*
function fencoding2_($xsource) {
  global $debug;
  $xresult=preg_replace("/[\\\\]+/","",$xsource);
  $xresult=iconv("UTF-8","UTF-8//IGNORE",$xresult);
  $xresult=trim($xresult);
  $xresult=unicode_to_utf($xresult);
  //$xresult=addslashes($xresult);
  $xresult =str_replace("'", "''", $xresult);
  //$xresult =str_replace("'", "", $xresult);
  $xresult = str_replace("\n", "", $xresult);
  $xresult = str_replace("\r", "", $xresult);


/ * if ($debug)
    echo "fencoding2_ $xsource $xresult<br/>\n";* /
  return $xresult;
}*/

function fencoding2_($xsource) {
  global $debug;
  $xresult='';
  if (trim($xsource)!='') {
//   if ($debug) echo "fencoding2_($xsource);<br/>\n";
  //$xresult=preg_replace("/[\\]+/","",$xsource);
  $xresult=str_replace('\\', '', $xsource);
  //$xresult=str_replace('/', '', $xsource);
  $xresult=iconv("UTF-8","UTF-8//IGNORE",$xresult);
  $xresult=trim($xresult);
  $xresult=unicode_to_utf($xresult);
  //$xresult=addslashes($xresult);
  $xresult =str_replace("'", "''", $xresult);
  //$xresult =str_replace("'", "", $xresult);
  $xresult = str_replace("\n", "", $xresult);
  $xresult = str_replace("\r", "", $xresult);

  }
/*  if ($debug)
    echo "fencoding2_ $xsource $xresult<br/>\n";*/
  return $xresult;
}

function fdummy_to_sql($xsql, $xparentdir, $xfilename, $xtablename, $xnoencode=0) {
  global $db, $debug;
  if (file_exists($xparentdir.'/upload/'.$xfilename)) {
    unlink($xparentdir.'/upload/'.$xfilename);
  }
  if (file_exists($xparentdir.'/upload/'.$xfilename.'.zip')) {
    unlink($xparentdir.'/upload/'.$xfilename.'.zip');
  }
  $fo = fopen($xparentdir.'/upload/'.$xfilename, 'w');
/*
  $xstr=
    "CREATE TABLE msmemb (
      code character varying,
      upcode character varying,
      name character varying,
      upname character varying,
      ic character varying,
      tel_hm character varying,
      tel_hp character varying,
      tel_of character varying,
      addr1 character varying,
      addr2 character varying,
      addr3 character varying,
      postcd character varying,
      town character varying,
      st_name character varying,
      cn_id character varying,
      print character varying,
      birthdt date,
      sex character varying,
      joindt date,
      banknm character varying,
      accno character varying,
      bank_br_name character varying,
      bankcode character varying,
      fpay_type character varying
    );";*/
  //fputs($fo, $xstr."\n\n");
  $xres=pg_query($db, $xsql);
  //echo pg_num_fields($xres);
  $xstr2="CREATE TABLE $xtablename (\n";
  for ($y=0; $y<pg_num_fields($xres); $y++) {
    $xstr_name=pg_field_name($xres, $y);
    $xstr_type=pg_field_type($xres, $y);
    //if ($debug)
    if (pg_num_fields($xres)-1==$y) {
      $xstr2.="$xstr_name $xstr_type\n";
    }else{
      $xstr2.="$xstr_name $xstr_type,\n";
    }
  }
  $xstr2.=");\n";
  //echo "$xstr2";
  fputs($fo, $xstr2."\n\n");
  for ($i=0; $i<pg_num_rows($xres); $i++) {
    $xstr='';
    $xrow=pg_fetch_row($xres, $i);
    if ($i==0) {
      for ($y=0; $y<pg_num_fields($xres); $y++) {
        if ($y==0)
          $xstr=pg_field_name($xres, $y);
        else
          $xstr.=",".pg_field_name($xres, $y);
      }//end if
      //$xstr.=";\n";
      $xfield=strtolower($xstr);
    }//end if
    $xstr='';
    for ($y=0; $y<pg_num_fields($xres); $y++) {
      //$xrow[$y]=fencoding_($xrow[$y]);
      $xfield_type=pg_field_type($xres, $y);
      if (preg_match('/name/',pg_field_name($xres, $y))) {
        $xrow[$y]=preg_replace("/[\ ]+[\(](exp)[0-9]+[\)]+/","",$xrow[$y]);
      }
      if ($y==0){
        if (preg_match('/date/', $xfield_type) && $xrow[$y]=='') {
          $xstr.="null";
        }else{
          if ($xnoencode==1) {
            $xstr.="'".preg_replace("/[\']/","''",$xrow[$y])."'";
          }else{
            $xstr.="'".fencoding2_($xrow[$y])."'";
          }
        }
      }else{
        if (preg_match('/date/', $xfield_type) && $xrow[$y]=='') {
          $xstr.=",null";
        }else{
          if ($xnoencode==1) {
            $xstr.=",'".preg_replace("/[\']/","''",$xrow[$y])."'";
          }else{
            $xstr.=",'".fencoding2_($xrow[$y])."'";
          }
        }
      }//end if
    }//end for
    $xvalue=$xstr;
    $xstr="insert into $xtablename ($xfield) values ($xvalue);\n";
    fputs($fo, $xstr);
  }//end for
  pg_free_result($xres);
  fclose($fo);
  //$xdir_="/.data/vhost-www/mlm1.dxn2u.com/htdocs/mytest/upload";
  //chdir($xparentdir.'/upload');
  //exec("/bin/tar -czf $xfilename.tar.gz $xfilename");
  fzip_process($xparentdir.'/upload/'.$xfilename.'.zip', $xparentdir.'/upload/'.$xfilename, '/'.$xfilename);
  chdir($xparentdir.'/sysadm');
}

function fencoding3_($xsource) {
  //$xresult=ereg_replace("[\t\\\/\']+", '', $xsource);
  $xresult=preg_replace("/[\/\']+/", '', $xsource);
  return $xresult;
}

function fdummy_to_sql_copy($xsql, $xparentdir, $xfilename, $xtablename, $xnoencode=0) {
  global $db, $debug;
  if (file_exists($xparentdir.'/upload/'.$xfilename)) {
    unlink($xparentdir.'/upload/'.$xfilename);
  }
  if (file_exists($xparentdir.'/upload/'.$xfilename.'.zip')) {
    unlink($xparentdir.'/upload/'.$xfilename.'.zip');
  }
  $fo = fopen($xparentdir.'/upload/'.$xfilename, 'w');
  $xres=pg_query($db, $xsql);
  //echo pg_num_fields($xres);
  if ($debug)
    fputs($fo, "DROP TABLE $xtablename;\n\n");

  $xstr2="CREATE TABLE $xtablename (\n";
  for ($y=0; $y<pg_num_fields($xres); $y++) {
    $xstr_name=pg_field_name($xres, $y);
    $xstr_type=pg_field_type($xres, $y);
    //if ($debug)
    if (pg_num_fields($xres)-1==$y) {
      $xstr2.="$xstr_name $xstr_type\n";
      $xstr_field.="$xstr_name";
    }else{
      $xstr2.="$xstr_name $xstr_type,\n";
      $xstr_field.="$xstr_name,";
    }
  }
  $xstr2.=");\n";
  fputs($fo, $xstr2."\n\n");
  $xstr_copy="COPY $xtablename ($xstr_field) FROM stdin;\n";
  fputs($fo, $xstr_copy);
  for ($i=0; $i<pg_num_rows($xres); $i++) {
    $xstr='';
    $xrow=pg_fetch_row($xres, $i);
    $xstr_data='';
    for ($y=0; $y<pg_num_fields($xres); $y++) {
      $xrow[$y]=fencoding3_($xrow[$y]);
      if ($xrow[$y]=='')
        $xrow[$y]='\N';
      //if ($debug) echo $xrow[$y]."<br/>\n";
      if ($y>0)
        $xstr_data.="\t";
      $xstr_data.=$xrow[$y];
    }
    //if ($debug) echo $xstr_data."<br/>\n";
    fputs($fo, $xstr_data."\n");
  }
  pg_free_result($xres);
  fclose($fo);
  //$xdir_="/.data/vhost-www/mlm1.dxn2u.com/htdocs/mytest/upload";
//   chdir($xparentdir.'/upload');
//   $xfilename_utf=preg_replace('/\.sql/', '', $xfilename).'_utf8.sql';
//   exec("/bin/cat $xfilename | recode UTF-8..utf8 > $xfilename_utf ");
//   exec("/bin/tar -czf $xfilename.tar.gz $xfilename_utf");
//   chdir($xparentdir.'/sysadm');
	fzip_process($xparentdir.'/upload/'.$xfilename.'.zip', $xparentdir.'/upload/'.$xfilename, '/'.$xfilename);
}

function fdummy_to_sql2($xsql, $xparentdir, $xfilename, $xtablename, $xdummy_str, $xarr_field) {
  global $db, $debug;
  if (file_exists($xparentdir.'/upload/'.$xfilename)) {
    unlink($xparentdir.'/upload/'.$xfilename);
  }
  if (file_exists($xparentdir.'/upload/'.$xfilename.'.tar.gz')) {
    unlink($xparentdir.'/upload/'.$xfilename.'.tar.gz');
  }
  $fo = fopen($xparentdir.'/upload/'.$xfilename, 'w');

  $xres=pg_query($db, $xsql);
  $xstr2="CREATE TABLE $xtablename (\n";
  for ($y=0; $y<pg_num_fields($xres); $y++) {
    $xstr_name=pg_field_name($xres, $y);
    $xstr_type=pg_field_type($xres, $y);
    //if ($debug)
    $xfound=-1;
    for ($i=0; $i<count($xdummy_str); $i++) {
      if ($xdummy_str[$i]==$xstr_name) {
        $xfound=$i;
      }
    }
    if ($xfound>=0) {
        $xarr_=split(';',$xarr_field[$xfound]);
        for ($j=0; $j<count($xarr_); $j++) {
          $xstr2.=$xarr_[$j]." varchar,\n";
        }
    }else{
      $xstr2.="$xstr_name $xstr_type,\n";
    }
    //if (pg_num_fields($xres)-1==$y) {
    //  $xstr2.="<br/>\n";
    //}else{
     // $xstr2.=",\n";
    //}
  }
  $xstr2.=");\n";

  $xstr2=preg_replace("/\,\n\)/i","\n)",$xstr2);
  if ($debug) {
    echo str_replace("\n", '<br/>', $xstr2);
  }
  fputs($fo, $xstr2."\n\n");

  for ($i=0; $i<pg_num_rows($xres); $i++) {
    $xstr='';
    $xrow=pg_fetch_row($xres, $i);
    if ($i==0) {
      for ($y=0; $y<pg_num_fields($xres); $y++) {
        $xfound=-1;
        for ($zi=0; $zi<count($xdummy_str); $zi++) {
          if ($xdummy_str[$zi]==pg_field_name($xres, $y)) {
            $xfound=$zi;
          }
        }
        if ($xfound>=0) {//eg:found cb_header
          $xarr_=split(';',$xarr_field[$xfound]);
          $xdata1='';
          $xistate=-1;
          for ($zj=0; $zj<count($xarr_); $zj++) {
/*            if ($xdata1=='')
              $xdata1.=$xarr_[$zj];
            else
              $xdata1.=','.$xarr_[$zj];*/
            if ($xdata1!='')
              $xdata1.=',';//.$xarr_[$zj];
            $xdata1.=$xarr_[$zj];
            if ($xarr_[$zj]=='state'){
              $xistate=$zj;
            }
          }
        }else{
          $xdata1=pg_field_name($xres, $y);
        }

        if ($xstr=='')
          $xstr=$xdata1;
        else
          $xstr.=",".$xdata1;
      }//end if
      //$xstr.=";\n";
      $xfield=strtolower($xstr);
      if ($debug)
        echo "$xfield<br/>\n";
    }//end if
    $xstr='';
    for ($y=0; $y<pg_num_fields($xres); $y++) {
      //$xrow[$y]=fencoding_($xrow[$y]);
      $xfield_type=pg_field_type($xres, $y);
      if (preg_match('/name/',pg_field_name($xres, $y))) {
        $xrow[$y]=preg_replace("/[\ ]+[\(](exp)[0-9]+[\)]+/","",$xrow[$y]);
      }
      /*
      if ($y==0){
      }else{
        if (eregi('date', $xfield_type) && $xrow[$y]=='') {
          $xstr.=",null";
        }else{
          $xstr.=",'".fencoding2_($xrow[$y])."'";
        }
      }//end if  */
      $xfound=-1;
      for ($zi=0; $zi<count($xdummy_str); $zi++) {
        if ($xdummy_str[$zi]==pg_field_name($xres, $y)) {
          $xfound=$zi;
        }
      }
      if ($xfound>=0) {
        //$xarr_data1=split('[\_]',fencoding2_($xrow[$y]));
        $xarr_data1=preg_split('/[\_]/', $xrow[$y]);
        $xstr1='';

        //for ($zj=0; $zj<count($xarr_data1); $zj++) {
        for ($zj=0; $zj<count($xarr_); $zj++) {
/*          if ($xstr1==''){
            $xstr1.="'".$xarr_data1[$zj]."'";
          }else{
            $xstr1.=','."'".$xarr_data1[$zj]."'";
          }*/
          if ($xstr1!=''){
            $xstr1.=',';
          }
          if ($xistate==$zj) {
            $xarr_data1[$zj]=fget_state($xarr_data1[$zj]);
            //echo $xarr_data1[$zj]."<br/>";
          }
          $xstr1.="'".$xarr_data1[$zj]."'";

        }
        $xdata1=$xstr1;
      }else{
        if (eregi('date', $xfield_type) && $xrow[$y]=='') {
          $xdata1="null";
        }else{
          //$xdata1="'".fencoding2_($xrow[$y])."'";
          $xdata1="'".$xrow[$y]."'";
        }
      }
      if ($xstr==''){
        $xstr.=$xdata1;
      }else{
        $xstr.=','.$xdata1;
      }

    }//end for
    $xvalue=$xstr;
    $xstr1="insert into $xtablename ($xfield) values ($xvalue);\n";
    //if ($debug)
    //  echo "$xstr1<br/>\n";
    fputs($fo, $xstr1);
  }//end for

  pg_free_result($xres);
  fclose($fo);
  chdir($xparentdir.'/upload');
  exec("/bin/tar -czf $xfilename.tar.gz $xfilename");
  chdir($xparentdir.'/sysadm');

}

function flink($xstr, $xhref){
  $xhref=preg_replace('/XVAL/', $xstr, $xhref);
  $xresult="<a href='$xhref'>$xstr</a>";
  return $xresult;
}

function fget_grnd_field($xfield, $xgrn_no){
  global $db;
  $xsqld="select distinct $xfield from tmpgrntrd where grn_no='$xgrn_no' ";
  $xresd=pg_query($db, $xsqld);
  $pr_no='';
  $pr_check=false;
  for ($j=0; $j<pg_num_rows($xresd);$j++) {
    $xrowd=pg_fetch_assoc($xresd, $j);
    $pr_no.=$xrowd[$xfield].' '.'<br/>';
  }
  return $pr_no;
}

function fget_deptname($xdept_code){
  global $db;
  $xsql="select dept_name from department_new where dept_code='$xdept_code';";
  $xres = pg_exec($db, $xsql);
  if (pg_num_rows($xres)>0) {
    $xrow=pg_fetch_row($xres, 0);
    $xhasil=strtoupper($xrow[0]);
  }
  pg_free_result($xres);
  return $xhasil;
}

function fpos_email($subject, $message) {
  global $true_email, $debug, $send_email, $xrow_from;

  $headers  = 'MIME-Version: 1.0' . "\r\n";
  $headers .= 'Content-type: text/plain; charset=UTF-8' . "\r\n";
  $headers .= 'From: '.$xrow_from['fname'].' <'.$xrow_from['femail'].'>' . "\r\n";
  if ($debug) {
    echo "xrow_from=";
    print_r($xrow_from);
    echo "<br/><br/>\n";
  }
  if ($true_email) {
    $xfields[0]="fname, femail";
    $xtables[0]="pos_email";
    $xwhere   ="where ftype='Cc'";
    $xrow_cc=db_select_record_multi($xfields, $xtables, $xwhere, '');
    for ($j=0; $j<count($xrow_cc); $j++) {
      $headers .= "Cc: ".$xrow_cc[$j]['fname']." <".$xrow_cc[$j]['femail'].">\r\n";
    }
    $xrow_to=db_select_record('fname, femail','pos_email',"where ftype='To'");
    $to_mail = $xrow_to['fname'].' <'.$xrow_to['femail'].'>';
  }else{
    $to_mail = "himawan@suryasoft.com";
    //$headers .= "Cc: lim kimlee <lim_kimlee@dxn2u.com>\r\n";
  }
  //echo $headers."<br/>\n".$to_mail;
  if ($debug==1) {
    if ($send_email)
      echo "mail($to_mail, $subject, $message, $headers);\n";
  }else{
    if ($send_email)
      mail($to_mail, $subject, $message, $headers);
  }
}
/*
function fget_st_current_stock($xloccd, $xprdcd, $txtTgl1, $txtTgl2, $xopening) {
  global $debug;
  $hCB   =fget_st_data_CB_item($xloccd, $xprdcd, $txtTgl1,$txtTgl2, $xopening);
  $hCR   =fget_st_data_CR_item($xloccd, $xprdcd, $txtTgl1,$txtTgl2, $xopening);
  $hINV  =fget_st_data_INV_item($xloccd, $xprdcd, $txtTgl1,$txtTgl2, $xopening);
  $hREC  =fget_st_data_receive_item($xloccd, $xprdcd, $txtTgl1, $txtTgl2, $xopening);
  $hADJ  =fget_st_data_ADJ_item($xloccd, $xprdcd, $txtTgl1, $txtTgl2, $xopening);
  //echo "$xloccd, $xprdcd, $txtTgl1, $txtTgl2 <br/>\n";
  //echo "$hCLOSE= ($hREC+($hADJ))-$hCB-$hCR-$hINV; <br/>\n";
  $hCLOSE= ($hREC+($hADJ))-$hCB-$hCR-$hINV;
  if ($debug)
    echo "$hCLOSE= ($hREC+($hADJ))-$hCB-$hCR-$hINV; $txtTgl1 $txtTgl2 $xopening<br/>\n";
  return $hCLOSE;
}
*/
function fget_st_current_stock($xloccd, $xprdcd, $xdate) {
	$txtTgl1=date('d/m/Y', mktime(0,0,0, substr($xdate, 3, 2), substr($xdate, 0, 2)+1, substr($xdate, 6, 4)));
	$xhasil = fget_st_data_item_opening($xloccd, $xprdcd, $txtTgl1);
	return $xhasil;
}

function set_inloc4($prdcode,$xqty,$debug,$loccd) {
  global $db;
  $sql1 = "select prdcd from inloc where loccd='$loccd' and trim(prdcd)='$prdcode';";
  $res1 = pg_exec($db, $sql1);
  if (pg_num_rows($res1)>0) {
    $querr11=
      "update inloc set qoh=$xqty where loccd='$loccd' and trim(prdcd)='$prdcode';";
  }else{
    $querr11="insert into inloc values ('$loccd','$prdcode',$xqty);";
  }
  pg_free_result($res1);
  if ($debug) echo($querr11."<br>\n");
  else $updateInlocBasic = @pg_exec($db, $querr11);
  return $updateInlocBasic;
}

function fget_type_trx($trxno) {
  $xresult='';
  if ($xresult=='') {
    $xrow=db_select_record('trtype', 'newmstrh', "where trcd='$trxno'");
    if ($xrow['trtype']=='1')
      $xresult='BRCB';
    elseif ($xrow['trtype']=='2')
      $xresult='BRSTAFF';
    elseif ($xrow['trtype']=='6')
      $xresult='BRCR';
    elseif ($xrow['trtype']=='7')
      $xresult='BRSR';
    elseif ($xrow['trtype']=='8')
      $xresult='BRMR';
  }
  if ($xresult=='') {
    $xrow=db_select_record('trtype', 'newmsivtrh', "where trivcd='$trxno'");
    //echo "xrow['trtype']=".$xrow['trtype']."<br/>";
    if ($xrow['trtype']=='1' || $xrow['trtype']=='3')
      $xresult='BRINV';
    elseif ($xrow['trtype']=='2')
      $xresult='MSINV';
  }
  if ($xresult=='') {
    $xrow=db_select_record('trtype', 'newsctrh', "where trcd='$trxno'");
    if ($xrow['trtype']=='1')
      $xresult='SCCB';
    elseif ($xrow['trtype']=='2')
      $xresult='SCSTAFF';
    elseif ($xrow['trtype']=='6')
      $xresult='SCCR';
    elseif ($xrow['trtype']=='7')
      $xresult='SCSR';
    elseif ($xrow['trtype']=='8')
      $xresult='SCMR';
  }
  return $xresult;
}

function fget_field($xtable, $xfield, $xwhere) {
  global $db;
  $xresult='';
  $xsql="select $xfield from $xtable $xwhere";
//   echo $xsql;
  $xres=pg_query($db, $xsql);
  if (pg_num_rows($xres)>0) {
    $xrow=pg_fetch_row($xres, 0);
    $xresult=$xrow[0];
  }
  pg_free_result($xres);
  return $xresult;
}

  function fgenerate_tax_invoice($xtrxno, $xloccd, $xyear, $xprefix='010') {
    global $db, $opnm, $debug, $default_cnt;
    /*

  br_code  |      br_name
-----------+--------------------
 000000001 | JAKARTA BRANCH
 000000002 | SURABAYA BRANCH
 000000003 | MEDAN BRANCH
 000000004 | MAKASAR BRANCH
 000000005 | PEKANBARU BRANCH
 000000006 | BANJARMASIN BRANCH
 000000007 | JABABEKA BRANCH
 000000008 | KEBAYORAN BRANCH
 000000009 | JAMBI BRANCH
 000000010 | PALEMBANG BRANCH
 000000011 | BUKIT TINGGI
 000000012 | TAMINI
 000000013 | LAMPUNG

    CPK = 010.000-yy.xxxxxxxx
    MDN = 010.001-yy.xxxxxxxx
    SBY = 010.002-yy.xxxxxxxx
    BJM = 010.003-yy.xxxxxxxx
    MKS = 010.004-yy.xxxxxxxx
    PKB = 010.005-yy.xxxxxxxx
    */
//     echo "xloccd=<i>$xloccd</i><br/>\n";
/*    $xnstart=$xyear;//date('Y');*/
    $xyear =substr($xyear, -4);
    $xyear2=substr($xyear, -2);
    $xnstart=$xyear;//date('Y');
    if ($debug)
      echo "<b>$xnstart $xyear</b><br/>";

    //$xbc_id=substr($xloccd,-3);
    if ($xloccd=='000000010' || $xloccd=='000000001' || $xloccd=='000000012' || $xloccd=='000000013') $xbc_id='000';
    if ($xloccd=='000000002') $xbc_id='002';
    if ($xloccd=='000000003') $xbc_id='001';
    if ($xloccd=='000000004') $xbc_id='004';
    if ($xloccd=='000000009' || $xloccd=='000000005' || $xloccd=='000000011') $xbc_id='005';
    if ($xloccd=='000000006') $xbc_id='003';

      $xrec=db_select_record('trcd', 'newmstaxtrh', "where trcd='$xtrxno'");
      $xloccd_alias=$xloccd;
      if ($xloccd=='000000010' || $xloccd=='000000012' || $xloccd=='000000013' ) $xloccd_alias='000000001';
      if ($xloccd=='000000009' || $xloccd=='000000011') $xloccd_alias='000000005';

      $xrow_taxcard=db_select_record('fcard_no','tax_card',"where (trtaxcd='' or trtaxcd is null) and loccd='$xloccd_alias' order by fcard_no limit 1"); //

      //if ($xrow_taxcard['fcard_no']!='') {

      if (!$xrec && $xrow_taxcard['fcard_no']!='') {
        //$newTransId=fget_maxnumber('newmstaxtrhmonth', date('Y'), '000000001');
/*        if ($xtype==0)
          $xrec1=db_select_record('loccd', 'newmstrh', "where trcd='$xtrxno'");
        else
          $xrec1=db_select_record('loccd', 'newmsivtrh', "where trivcd='$xtrxno'");*/
/*
        $xnsql="select nvalue from newmstaxtrhmonth ".
          "where nstart='$xnstart' ";
        if ($xloccd=='000000010' || $xloccd=='000000001' || $xloccd=='000000012') {
          //$xnsql.="and loccd='000000001'";
          $xloccd2='000000001';
        }elseif ($xloccd=='000000009' || $xloccd=='000000005' || $xloccd=='000000011') {
          //$xnsql.="and loccd='000000005'";
          $xloccd2='000000005';
        }else{
          //$xnsql.="and loccd='".$xloccd."'";
          $xloccd2=$xloccd;
        }
        $xnsql.="and loccd='".$xloccd2."'";

        if ($debug) echo "$xnsql<br/>\n";
        $xnres=pg_query($db, $xnsql);
        if (pg_num_rows($xnres)>0) {
          $xnrow=pg_fetch_row($xnres, 0);
          $xpsql="update newmstaxtrhmonth set nvalue=".$xnrow[0]."+1 ".
            "where nstart='$xnstart' and loccd='".$xloccd2."'";
          $newTransId=$xnrow[0]+1;
        }else{
          $xpsql="insert into newmstaxtrhmonth values('$xnstart','1','".$xloccd2."');";
          $newTransId=1;
        }
        if ($debug) echo "$xpsql<br/>\n";
        pg_exec($db, $xpsql);
*/
        //$newTransId="$xprefix.$xbc_id-$xyear2.".str_pad($newTransId, 8, "0", STR_PAD_LEFT);
        $xrow_prefix=db_select_record('tax_prefix', 'company', "where country_id='$default_cnt'");

        $newTransId="$xprefix.".$xrow_prefix['tax_prefix']."-$xyear2.".str_pad($xrow_taxcard['fcard_no'], 8, "0", STR_PAD_LEFT);
        $xarr_data1=array(
          'trcd'=>$xtrxno,
          'trtaxcd'=>$newTransId,
          'lupddt'=>'now()',
          'lupdtm'=>'now()',
          'opnm'=>$opnm
        );

        db_insert('newmstaxtrh', $xarr_data1);

          $xarr_tax_card=array(
            'trtaxcd'=>$newTransId,
            'loccd'=>$xloccd
          );
          $xarr_where = array('fcard_no'=>$xrow_taxcard['fcard_no']);//

          db_update2('tax_card', $xarr_tax_card, $xarr_where);
        //end if
      }
  }


  function fdel_generate_tax_invoice($trcd_del){
    global $db, $debug;
    $xsql1 = "select trtaxcd from newmstaxtrh where trcd='$trcd_del';";
    $xres1 = pg_exec($db, $xsql1);
    if (pg_num_rows($xres1)>0) {
      $xrow1 = pg_fetch_assoc($xres1, 0);
      $xtrtaxcd = $xrow1['trtaxcd'];

      $xpsql = "insert into deltax_card select * from tax_card where trtaxcd='$xtrtaxcd'";
      if ($debug) echo "$xpsql<br/>\n";
      pg_exec($db, $xpsql);

      $xpsql = "update tax_card set trtaxcd='' where trtaxcd='$xtrtaxcd'";
      if ($debug) echo "$xpsql<br/>\n";
      pg_exec($db, $xpsql);

      $xpsql = "insert into delmstaxtrh select * from newmstaxtrh where trtaxcd='$xtrtaxcd'";
      if ($debug) echo "$xpsql<br/>\n";
      pg_exec($db, $xpsql);

      $xpsql = "delete from newmstaxtrh where trtaxcd='$xtrtaxcd'";
      if ($debug) echo "$xpsql<br/>\n";
      pg_exec($db, $xpsql);
    }

  }

function fwrite_csv_header($f, $arr_header) {
  $xstr='';
  for ($d=0; $d<count($arr_header); $d++) {
    if ($xstr!='') {
      $xstr.=',';
    }
    $xstr.='"'.$arr_header[$d].'"';
  }
//   echo $xstr."<br/>\n";
  fputs($f, $xstr."\n");
}

function fget_datafield($xtable, $xfname, $xwherefname, $xwherefvalue) {
  //fget_datafield('staff', 'staff_name', 'staff_id', 'IT002')
  global $debug;
  $xasql="select $xfname ".
    "from $xtable ";
  if ($xwherefname!='') {
    $xasql.="where $xwherefname='$xwherefvalue' ";
  }
  if ($debug) echo "//$xasql<br/>\n";
  $xarow=fpg_exec4($xasql);
  $xresult=$xarow[0];
  return $xresult;
}

function formatRawSize($bytes) {
  //CHECK TO MAKE SURE A NUMBER WAS SENT
  if(!empty($bytes)) {
    //SET TEXT TITLES TO SHOW AT EACH LEVEL
    $s = array('bytes', 'kb', 'MB', 'GB', 'TB', 'PB');
    $e = floor(log($bytes)/log(1024));
    //CREATE COMPLETED OUTPUT
    $output = sprintf('%.2f '.$s[$e], ($bytes/pow(1024, floor($e))));
    //SEND OUTPUT TO BROWSER
    return $output;
  }
}

function formatTime($secs) {
   $times = array(3600, 60, 1);
   $time = '';
   $tmp = '';
   for($i = 0; $i < 3; $i++) {
      $tmp = floor($secs / $times[$i]);
      if($tmp < 1) {
         $tmp = '00';
      }
      elseif($tmp < 10) {
         $tmp = '0' . $tmp;
      }
      $time .= $tmp;
      if($i < 2) {
         $time .= ':';
      }
      $secs = $secs % $times[$i];
   }
   return $time;
}

  function array_implode($array1) {
    $xresult='';
    for ($i=0; $i<count($array1); $i++) {
      if ($i>0)
        $xresult.=", ";
      $xresult.="'".$array1[$i]."'";
    }
    return $xresult;
  }

  function sel_stockist4($xstr_name, $xobj_name, $xtype=2, $txt_event='') {
    // 0 --> main stockist
    // 1 --> stockist
    // 2 --> main or stockist
    // 3 --> display --All-- option
    global $db,$priv,$opnm,$debug;
    $xsql="select sccode from users_extra where uname='$opnm'";
    $xres=pg_query($db, $xsql);
    $xrow=pg_fetch_row($xres,0);
    if ($xrow[0]=='1') {
      echo "<select name='$xstr_name' id='$xstr_name' $txt_event> \n";
  //    if ($xtype==3) echo "<option value='x'>--".mxlang("934","")."--</option>\n";
      $xsql_st ="select a.code,a.sub_name,c.name from sub_mssc a left join msmemb c on a.code=c.code ";
      if ($xtype==0 || $xtype==1) $xsql_st.="and b.stockist=$xtype ";
      $xsql_st.="order by a.sub_name";
      $xres_st=pg_query($db, $xsql_st);
      for ($i=0;$i<pg_num_rows($xres_st);$i++) {
        $xrow_st=pg_fetch_row($xres_st, $i);
        $xsel = ($xobj_name==$xrow_st[0])?'selected':'';
        echo "<option value='$xrow_st[0]' $xsel>$xrow_st[1]-".stripslashes($xrow_st[2])."</option>\n";
      }
      echo "</select> \n";
    }else{
      echo "<select name='$xstr_name'> \n";
//       $xsql_st ="select a.code,a.sub_name,c.name
//         from sub_mssc a
//         left join msmemb c on a.code=c.code
//         where a.code='$opnm'";
      $xsql_st ="select a.code,a.sub_name,c.name
        from sub_mssc a
        left join msmemb c on a.code=c.code
        where a.code='$xrow[0]'";

      $xsql_st.="order by a.sub_name";
      //echo "$xsql_st";
      $xres_st=pg_query($db, $xsql_st);
      for ($i=0;$i<pg_num_rows($xres_st);$i++) {
        $xrow_st=pg_fetch_row($xres_st, $i);
        $xsel = ($xobj_name==$xrow_st[0])?'selected':'';
        echo "<option value='$xrow_st[0]' $xsel>$xrow_st[1]-".stripslashes($xrow_st[2])."</option>\n";
      }
      echo "</select> \n";
    }
  }

  function selBANK4($fldname, $xval) {
    global $db,$debug;
    echo("<select name='$fldname' tabindex='50'>\n");
    echo("<option value=''>--".mxlang("1967","")."--</option>\n");
    $xsql="select fcode,fdesc from tbank where factive='A'";
    $xres=pg_query($db, $xsql);
    for ($i=0;$i<pg_num_rows($xres);$i++) {
      $xrow = pg_fetch_row($xres, $i);
      $xselected=($xval==$xrow[0])?'selected':'';
      echo("<option value='$xrow[0]' $xselected>$xrow[1]</option>\n");
    }//end for
    echo("</select>\n");
    pg_free_result($xres);
  }//end function

  function fget_br_totpay($xloccd, $txtTgl1, $txtTgl2) {
    global $db, $debug;
    $xsql=
      "select sum(xtotpay) from (
        select
          case
            when paytype1='CSH' then totpay1
            when paytype2='CSH' then totpay2
            when paytype3='CSH' then totpay3
            when paytype4='CSH' then totpay4
            when paytype5='CSH' then totpay5
            else 0
          end as xtotpay
        from newmstrh a, mspayment b
        where a.trcd=b.trcd
        and a.loccd='$xloccd' and a.trdt>='$txtTgl1' and a.trdt<='$txtTgl2'
        and a.trtype in ('1','12','9','23') and (a.send<>'4' or a.send is null)
        and (b.paytype1='CSH' or b.paytype2='CSH' or b.paytype3='CSH' or b.paytype4='CSH'or b.paytype5='CSH')
        union all
        select
          case
            when paytype1='CSH' then totpay1
            when paytype2='CSH' then totpay2
            when paytype3='CSH' then totpay3
            when paytype4='CSH' then totpay4
            when paytype5='CSH' then totpay5
            else 0
          end as xtotpay
        from newmstrh a, mspayment b
        where a.trcd=b.trcd
        and a.loccd='$xloccd' and a.trdt>='$txtTgl1' and a.trdt<='$txtTgl2'
        and a.trtype in ('2','6','7','8','10') and a.status='1'
        and (b.paytype1='CSH' or b.paytype2='CSH' or b.paytype3='CSH' or b.paytype4='CSH'or b.paytype5='CSH')
        union all
        select
          case
            when paytype1='CSH' then totpay1
            when paytype2='CSH' then totpay2
            when paytype3='CSH' then totpay3
            when paytype4='CSH' then totpay4
            when paytype5='CSH' then totpay5
            else 0
          end as xtotpay
        from newmsivtrh a, mspayment b
        where a.trivcd=b.trivcd
        and a.loccd='$xloccd' and a.trdt>='$txtTgl1' and a.trdt<='$txtTgl2'
        and (b.paytype1='CSH' or b.paytype2='CSH' or b.paytype3='CSH' or b.paytype4='CSH'or b.paytype5='CSH')
        union all
        select
          case
            when paytype1='CSH' then totpay1
            when paytype2='CSH' then totpay2
            when paytype3='CSH' then totpay3
            when paytype4='CSH' then totpay4
            when paytype5='CSH' then totpay5
            else 0
          end as xtotpay
        from newmstrh a, mspayment b
        where a.trcd=b.trcd
        and a.loccd='$xloccd' and a.trdt>='$txtTgl1' and a.trdt<='$txtTgl2'
        and a.trtype in ('3', '4')
        and (b.paytype1='CSH' or b.paytype2='CSH' or b.paytype3='CSH' or b.paytype4='CSH'or b.paytype5='CSH')

        union all

        select
          case
            when paytype1='CSH' then totpay1
            when paytype2='CSH' then totpay2
            when paytype3='CSH' then totpay3
            when paytype4='CSH' then totpay4
            when paytype5='CSH' then totpay5
            else 0
          end as xtotpay
        from newmstrh a, mspayment b
        where a.trcd=b.trcd
        and a.loccd='$xloccd' and a.trdt>='$txtTgl1' and a.trdt<='$txtTgl2'
        and a.trtype in ('1') and a.send='4'
        and (b.paytype1='CSH' or b.paytype2='CSH' or b.paytype3='CSH' or b.paytype4='CSH'or b.paytype5='CSH')

      ) as table1 ";
    //if ($debug) echo nl2br($xsql)."<br/>\n";

    $xres=pg_query($db, $xsql);
    $xrow=pg_fetch_row($xres, 0);
    $xhasil = $xrow[0];
    if ($xhasil=='')
      $xhasil=0;

    $xsql1=
      "select sum(xtotpay) from (
        select sum(b.return) as xtotpay
        from newmstrh a, mspayment b
        where a.trcd=b.trcd
        and a.loccd='$xloccd' and a.trdt>='$txtTgl1' and a.trdt<='$txtTgl2'
        and a.trtype in ('1','12','9','23') and (a.send<>'4' or a.send is null)
        and b.paytype1<>'CSH'
        union
        select sum(b.return) as xtotpay
        from newmstrh a, mspayment b
        where a.trcd=b.trcd
        and a.loccd='$xloccd' and a.trdt>='$txtTgl1' and a.trdt<='$txtTgl2'
        and a.trtype in ('2','6','7','8','10') and a.status='1'
        and b.paytype1<>'CSH'
        union
        select sum(b.return) as xtotpay
        from newmsivtrh a, mspayment b
        where a.trivcd=b.trivcd
        and a.loccd='$xloccd' and a.trdt>='$txtTgl1' and a.trdt<='$txtTgl2'
        and b.paytype1<>'CSH'
        union
        select sum(b.return) as xtotpay
        from newmstrh a, mspayment b
        where a.trcd=b.trcd
        and a.loccd='$xloccd' and a.trdt>='$txtTgl1' and a.trdt<='$txtTgl2'
        and a.trtype in ('3', '4')
        and b.paytype1<>'CSH'
        union
        select sum(b.return) as xtotpay
        from newmstrh a, mspayment b
        where a.trcd=b.trcd
        and a.loccd='$xloccd' and a.trdt>='$txtTgl1' and a.trdt<='$txtTgl2'
        and a.trtype in ('1') and a.send='4'
        and b.paytype1<>'CSH'
      ) as table1 ";
    //if ($debug) echo "$xsql<br/>";

    $xres1=pg_query($db, $xsql1);
    $xrow1=pg_fetch_row($xres1, 0);
    $xhasil1 = $xrow1[0];
    if ($xhasil1=='')
      $xhasil1=0;
    $xhasil=$xhasil-$xhasil1;

    return $xhasil;
  }

  function fget_tot_dailycash($xloccd, $txtTgl1, $txtTgl2) {
    global $db, $debug;
    $xhasil = 0;
    $xsql=
      "select sum(a.amt)+sum(a.oth_amt) as xtotal
      from tdaily_cash a
      where a.loccd='$xloccd' and a.csdt>='$txtTgl1' and a.csdt<='$txtTgl2'  ";
    //if ($debug)
    //  echo "<b>$xsql</b><br/>\n";
    $xres=pg_query($db, $xsql);
    $xrow=pg_fetch_row($xres, 0);
    $xhasil = $xrow[0];
    if ($xhasil=='')
      $xhasil = 0;

    $xhasil2 = 0;
    $xsql2=
      "select sum(adj_amt) from tdaily_cash_adj where loccd='$xloccd' and csdt>='$txtTgl1' and csdt<='$txtTgl2' ";
    //if ($debug)
    //  echo "$xsql2<br/>\n";
    $xres2=pg_query($db, $xsql2);
    if (pg_num_rows($xres2)>0) {
      $xrow2=pg_fetch_row($xres2, 0);
      $xhasil2 = $xrow2[0];
      if ($xhasil2=='')
        $xhasil2 = 0;
    }
    $xhasil=$xhasil+$xhasil2;
    return $xhasil;
  }

  function fdate_format4($xval, $xtype=0){
              //0123456789
    //$xtype==0 dd-mm-yyyy to yyyy-mm-dd
    //$xtype==1 yyyy-mm-dd to dd-mm-yyyy
    //$xtype==2 yyyymmdd to yyyy-mm-dd
    //$xtype==3 yyyymmdd to dd-mm-yyyy
    //$xtype==4 dd-mm-yyyy to yyyymmdd
    //$xtype==5 dd-mm-yyyy to mm/dd/yyyy
    if (trim($xval)!='') {
      if ($xtype==0)
        $xresult=substr($xval, 6, 4).'-'.substr($xval, 3, 2).'-'.substr($xval, 0, 2);
      elseif ($xtype==1)
        $xresult=substr($xval, 8, 2).'-'.substr($xval, 5, 2).'-'.substr($xval, 0, 4);
      elseif ($xtype==2)
        $xresult=substr($xval, 0, 4).'-'.substr($xval, 4, 2).'-'.substr($xval, 6, 2);
      elseif ($xtype==3)
        $xresult=substr($xval, 6, 2).'-'.substr($xval, 4, 2).'-'.substr($xval, 0, 4);
      elseif ($xtype==4)
        $xresult=substr($xval, 6, 4).substr($xval, 3, 2).substr($xval, 0, 2);
      elseif ($xtype==5)
        $xresult=substr($xval, 3,2).'/'.substr($xval, 0,2).'/'.substr($xval, 8,2);
    }else
      $xreturn='';
    return $xresult;
  }


  function fauto_process_POS_manual4($xdb, $xarr_table1, $txt_trcd, $txt_key, $xboulder_valley1) {
    global $db, $debug, $xschema, $xfrom, $xmethod, $xcheck_member, $xarr_table2,
      $month1, $date1, $year1;
    if($debug)
      echo "fauto_process_POS_manual4($xdb, $xarr_table1, $txt_trcd, $txt_key);<br/>\n";
    $xflag_field='flag';
    if (preg_match('/temp_/', $xarr_table1[0])) {
      $xflag_field='sync_flag';
    }
    //if ($debug) echo "xschema=$xschema<br/>\n";
    //if ($xschema!='')
    if ($xschema!='' && !preg_match('/[\.]/', $xschema))
      $xschema=$xschema.'.';
    //$bresult=pg_query($db,"begin transaction");
    //$xsql1="SELECT * FROM ".$xschema."temp_newmstrh ".
    if ($xcheck_member) { //begin process member
      $xsqlm=
				"SELECT * FROM ".$xschema.$xarr_table1[3]." ".
        "where code='$txt_key' ".
        "order by code ";
      if ($debug)
        echo "<b>$xsqlm</b><br/>\n";
      $xresm=pg_query($xdb, $xsqlm);
      if ($debug) echo "count=".pg_num_rows($xresm)."<br/>";
      for ($z=0; $z<pg_num_rows($xresm); $z++) {//looping row
        $xfieldsm='';
        $xvaluesm='';
        $xrowm=pg_fetch_assoc($xresm, $z);
        for ($m=0; $m<pg_num_fields($xresm); $m++) {//looping row
          //if ($debug) echo pg_fieldname($xresm, $m)."<br/>";
          if (!preg_match('/sync/i', pg_fieldname($xresm, $m))) {
            $xdatavalue=$xrowm[pg_fieldname($xresm, $m)];

//             if ($debug) {
//               if (pg_fieldname($xresm, $m)=='flag') {
//                 echo pg_fieldname($xresm, $m)."<br/>";
//                 echo $xrowm[pg_fieldname($xresm, $m)]."<br/>";
//               }
//             }

            if ($xfieldsm!='') {
              $xfieldsm.=',';
              $xvaluesm.=',';
            }

            if (preg_match('/foreign/i', pg_fieldname($xresm, $m)))
            {
              $xfieldsm.='"'.pg_fieldname($xresm, $m).'"';
            }else{
              $xfieldsm.=pg_fieldname($xresm, $m);
            }


            $xreg='/sync_date|lupddt|lupdtm|birthdt|joindt|'.
              'jointm|etdt|lupddt|lupdtm|trdt|'.
              'expired_date|admupdt|flagdt|colony_id|co_birthday|'.
              'reg_date_atis|contra'.
              '/i';
            if (preg_match($xreg, pg_fieldname($xresm, $m)) && (trim($xdatavalue)=='')
            ){
              $xvaluesm.='null';
            }else{
              $xvaluesm.="'".$xdatavalue."'";
            }

          }
        }//end for field

        $xcsql="select code from msmemb where code='".$xrowm['code']."'";
        if ($debug) {
          echo $xcsql."*<br/>\n";
        }
        $xcres=pg_query($db, $xcsql);
        if (pg_num_rows($xcres)==0) {
          $xpsqlm="INSERT INTO msmemb ($xfieldsm) VALUES ($xvaluesm)";
          if ($debug) {
            echo $xpsqlm."**<br/>\n";
          }
          pg_exec($db, $xpsqlm);

          $xpsqlm="update msmemb set \"foreign\"='1',frmsys='1' where code='".$xrowm['code']."'";
          if ($debug) {
            echo $xpsqlm."*".KITX."<br/>\n";
          }
          pg_exec($db, $xpsqlm);

          $xpsql1="UPDATE ".$xschema.$xarr_table1[3]." set sync_flag='1',is_sync='1',sync_date=now() where code='".$xrowm['code']."'";
          if ($debug)
            echo "$xpsql1 (on ecom)<br/>\n";
          else
            pg_exec($xdb, $xpsql1);


        }

      }//end for row


      $xsqlm=
				"SELECT * FROM ".$xschema.$xarr_table1[4]." ".
        "where code='$txt_key' ".
        "order by code";
      if ($debug)
        echo "<b>$xsqlm</b> (on ecom)<br/>\n";
      $xresm=pg_query($xdb, $xsqlm);
      if ($debug) echo "count=".pg_num_rows($xresm)."<br/>";
      for ($z=0; $z<pg_num_rows($xresm); $z++) {
        $xrowm=pg_fetch_assoc($xresm, $z);
        for ($m=0; $m<pg_num_fields($xresm); $m++) {
          $xdatavalue=$xrowm[pg_fieldname($xresm, $m)];
          if ($m==0) {
            $xfieldsm='';
            $xvaluesm='';
          }else{
            $xfieldsm.=',';
            $xvaluesm.=',';
          }

          if (preg_match('/foreign/i', pg_fieldname($xresm, $m)))
          {
            $xfieldsm.='"'.pg_fieldname($xresm, $m).'"';
          }else{
            $xfieldsm.=pg_fieldname($xresm, $m);
          }


          $xreg='/co_birthday|withdraw_date_atis|reg_date_atis|colony_id|'.
            'tax_reg|tax_effdt|tax_enddt|tax_lvfdt|self_billed|selfbilled_effdt|selfbilled_enddt'.
            '/i';

          if (preg_match($xreg, pg_fieldname($xresm, $m)) && (trim($xdatavalue)=='')
          ){
            $xvaluesm.='null';
          }else{
            $xvaluesm.="'".$xdatavalue."'";
          }

        }
        $xcsql1="select code from msmemb where code='".$xrowm['code']."'";
        $xcres1=pg_query($db, $xcsql1);
        if (pg_num_rows($xcres1)>0) {
          $xcsql2="select code from msmemb_extra where code='".$xrowm['code']."'";
          if ($debug) {
            echo $xcsql2."*<br/>\n";
          }
          $xcres2=pg_query($db, $xcsql2);
          if (pg_num_rows($xcres2)==0) {
            $xpsqlm="INSERT INTO msmemb_extra ($xfieldsm) VALUES ($xvaluesm)";
            if ($debug) {
              echo $xpsqlm."*<br/>\n";
            }
            pg_exec($db, $xpsqlm);
          }
        }
      }//end for

    }//end process member

    $xsql1 ="SELECT * FROM ".$xschema.$xarr_table1[0]." ";
    $xsql1.="where trcd in ('$txt_trcd') ";
    $xsql1.="ORDER BY TRCD";
    if ($debug)
      echo "<b>$xsql1</b>*(on ecom)<br/>\n";
    $xres1=pg_query($xdb, $xsql1);
    if ($debug) echo "count=".pg_num_rows($xres1)."<br/>";
    $xvalid_all=true;
    $xcount_trh_trcd=0;
    $xcount_trd_trcd=0;
    $xcount_pay_trcd=0;
    $xvalid_qoh=true;
    $xvalid_price=true;
    $xvalid_msmemb=true;
    $xvalid_detail=true;
    for ($i=0; $i<pg_num_rows($xres1); $i++) {
      $xrow1=pg_fetch_assoc($xres1, $i);

      $xmsql="select code from msmemb where code='{$xrow1['code']}'";
        if ($debug){
        echo "<b><i>$xmsql</i></b><br/>\n";
      }
      $xmrow=fpg_exec4($xmsql);
      if ($xmrow[0]!=$xrow1['code']) {
        $xvalid_msmemb=false;
      }

      $xrow1['loccd']=str_pad($xrow1['loccd'], 9, '0', STR_PAD_LEFT);

      $xarr_[$xrow1['trcd']]['valid_qoh']     =true;
      $xarr_[$xrow1['trcd']]['valid_price']   =true;
      $xarr_[$xrow1['trcd']]['valid_msmemb']  =true;
      $xarr_[$xrow1['trcd']]['valid_backdate']=true;

      if ($debug)
        echo "trcd=".$xrow1['trcd']."<br/>\n";
      unset($arr_trh_data);
      $arr_trh_data=array();//reset
      //$xsql2="SELECT * FROM ".$xschema."temp_newmstrd WHERE TRCD='".$xrow1['trcd']."' ";
      $xsql2 ="SELECT * FROM ".$xschema.$xarr_table1[1]." WHERE TRCD='".$xrow1['trcd']."' ";
      $xres2=pg_query($xdb, $xsql2);
      if ($debug) echo "<b>$xsql2</b>(on ecom) //detail<br/>\n";
      if ($xrow1['trtype']!='23') {
        if (pg_num_rows($xres2)==0) {
          $xvalid_detail=false;
        }else{
          for ($xi=0; $xi<pg_num_rows($xres2); $xi++) {
            $xrow2=pg_fetch_assoc($xres2, $xi);

            if ($debug){
              print_r($xrow2);
              echo "<br/>\n";
            }
          }
        }
      }
      $xsql5 ="SELECT * FROM ".$xschema.$xarr_table1[5]." WHERE TRCD='".$xrow1['trcd']."' ";
      $xres5=pg_query($xdb, $xsql5);
      if ($debug) echo "<b>$xsql5</b>(on ecom)<br/>\n";

      $xexisttrcd=false;
      $xsqldb="SELECT trcd FROM newmstrh WHERE trcd='".$xrow1['trcd']."' ";
      $xresdb=pg_query($db, $xsqldb);
      if (pg_num_rows($xresdb)>0) {
        $xexisttrcd=true;
      }
      if ($debug)
        echo "checking= $xsqldb xexisttrcd=$xexisttrcd<br/>\n";

      //echo $xrow1['trcd']." <br/>\n";
      for ($j=0; $j<pg_num_rows($xres2); $j++) {// for detail
        $xrow2=pg_fetch_assoc($xres2, $j);
        //$xcount_trd_trcd++;
        $xqoh=hitungqoh_basic($xrow2['prdcd'], $xrow1['loccd']);
        //fget_br_monthly_stock($xloccd, $xprdcd, $xdate)
        $xget_qoh=fget_br_monthly_stock($xrow1['loccd'], $xrow2['prdcd'], date('d/m/Y'));

        if ($debug) echo $xrow1['loccd'].' '.$xrow2['trcd'].' '.$xrow2['prdcd'].' '.$xrow2['qty']." $xqoh $xget_qoh<br/>\n";
        //update inloc also
        if ($xqoh!=$xget_qoh){
          $xcsql="select qoh from inloc where loccd='".$xrow1['loccd']."' and prdcd='".$xrow2['prdcd']."'";
          $xcres=pg_query($db, $xcsql);
          if (pg_num_rows($xcres)>0) {
            $xsql="update inloc set qoh=$xget_qoh where loccd='".$xrow1['loccd']."' and prdcd='".$xrow2['prdcd']."'";
          }else{
            $xsql="insert into inloc (loccd,prdcd,qoh) values ('".$xrow1['loccd']."','".$xrow2['prdcd']."',$xget_qoh);";
          }
          if ($debug) echo "$xsql<br/>\n";
          pg_exec($db, $xsql);
          pg_free_result($xcres);
          $xqoh=$xget_qoh;
        }

        $xregion=chkregion($xrow1['loccd'],1);
        $xprice_field='dp as xdp, pv as xpv, bv as xbv, cp as xcp ';
        if ($xregion=='WEST')
          $xprice_field='wdp as xdp, wpv as xpv, wbv as xbv, wcp as xcp ';

        $xrow_item=db_select_record($xprice_field,'msprd',"where prdcd='".$xrow2['prdcd']."'");
        if ($xrow_item['xdp']=='')
          $xrow_item['xdp']=0;
        if ($xrow_item['xpv']=='')
          $xrow_item['xpv']=0;
        if ($xrow_item['xbv']=='')
          $xrow_item['xbv']=0;

        if ($debug) {
  //        print_r($xrow_item);
  //        echo "<br/>\n";
  //        print_r($xrow2);
  //        echo "<br/>\n";
          //echo "<b>".$xrow2['prdcd'].'-->'.$xrow_item['dp'].' '.$xrow_item['pv'].' '.$xrow_item['bv']."</b><br/>\n";
          echo $xrow1['loccd']." $xregion $xprice_field<br/>\n";
          echo "<b>".$xrow2['prdcd'].'-->master: '.$xrow_item['xdp'].' '.$xrow_item['xpv'].' '.$xrow_item['xbv']."</b><br/>\n";
          echo "<b>".$xrow2['prdcd'].'-->temp_data:'.$xrow2['dp'].' '.$xrow2['pv'].' '.$xrow2['bv']."</b><br/>\n";
        }

        $xmsql="select code from msmemb where code='".$xrow1['code']."'";
  /*      if ($debug){
          echo "$xmsql<br/>\n";
        }*/
        $xmrow=fpg_exec4($xmsql);

        if ($debug) {
          echo "<b>you are here0</b><br/>\n";
          echo $xexisttrcd."==false && ";
          echo "xget_qoh-> ".$xget_qoh.'>='.$xrow2['qty'];
          echo "(".$xrow1['trtype']."=='1' && ".$xrow_item['xdp']."==".$xrow2['dp']." && xpv ".
            $xrow_item['xpv']."==".$xrow2['pv']." && xbv ".$xrow_item['xbv']."==".$xrow2['bv']." &&".
            $xmrow[0]."==".$xrow1['code'].") ".
            "||".
            "(".$xrow1['trtype']."=='2' && ".$xrow_item['xcp']."==".$xrow2['cp'].") ";
        }

        if
(
        (
          $xexisttrcd==false &&
          $xget_qoh>=$xrow2['qty']
          &&
          (
            ($xrow1['trtype']=='1' && $xrow_item['xdp']==$xrow2['dp'] &&
            $xrow_item['xpv']==$xrow2['pv'] && $xrow_item['xbv']==$xrow2['bv'] &&
            $xmrow[0]==$xrow1['code'])
            ||
            ($xrow1['trtype']=='2' && $xrow_item['xcp']==$xrow2['cp'])
          )
        )
        ||
        $debug//hima
)
        {
          if (!isset($xtrh_trcd))
            $xtrh_trcd=array();
          if (!in_array($xrow1['trcd'], $xtrh_trcd))
            $xtrh_trcd[]=$xrow1['trcd'];
          if (!isset($xtrd_trcd))
            $xtrd_trcd=array();
          $xtrd_trcd[]=$xrow1['trcd'];

          if ($debug) {
            echo "<b>you are here</b><br/>\n";
            print_r($xtrh_trcd);
            echo "<br/>\n";
            print_r($xtrd_trcd);
            echo "<br/>\n";
          }

        }else{
          if ($debug) {
            echo "<b>you are here, error2</b><br/>\n";
            echo 'test='.$xexisttrcd."==false && $xget_qoh<".$xrow2['qty']."<br/>\n";
          }
          // checking qoh
          if ($xexisttrcd==false && $xget_qoh<$xrow2['qty']) {
            if ($debug) echo "checking $xget_qoh<".$xrow2['qty']." <br/>\n";
            //$xvalid_all =false;
            $xvalid_qoh=false;
            $xarr_[$xrow1['trcd']]['valid_qoh']=false;
            $xstr_email_err.=
              str_pad($xrow1['trcd'], 20, " ", STR_PAD_RIGHT).
              str_pad($xrow2['prdcd'], 15, " ", STR_PAD_RIGHT).
              str_pad($xqoh, 10, " ", STR_PAD_LEFT).
              str_pad($xrow2['qty'], 10, " ", STR_PAD_LEFT)."\n";
            //break;
            if (!isset($xtrh_trcd_err))
              $xtrh_trcd_err=array();
            if (!in_array($xrow2['trcd'], $xtrh_trcd_err))
              $xtrh_trcd_err[]=$xrow2['trcd'];
            if (!isset($xtrd_trcd_err))
              $xtrd_trcd_err=array();
            $xtrd_trcd_err[]=$xrow2['trcd'];
          }
          //if ($debug) echo "checking $xget_qoh<".$xrow2['qty']." xvalid_qoh=".((!$xvalid_qoh)?'falsex':'truex')."<br/>\n";

          //checking price
          if (
            $xexisttrcd==false &&
            (
            ($xrow_item['xdp']!=$xrow2['dp'] && $xrow1['trtype']=='1')
            ||
            ($xrow_item['xcp']!=$xrow2['cp'] && $xrow1['trtype']=='2')
            ||
            ($xrow_item['xpv']!=$xrow2['pv'] && $xrow1['trtype']=='1')
            ||
            ($xrow_item['xbv']!=$xrow2['bv'] && $xrow1['trtype']=='1')
            )
          )
          {
            if ($debug) echo "checking <b>price not tally</b><br/>\n";
            //$xvalid_all =false;
            $xvalid_price=false;
            $xarr_[$xrow1['trcd']]['valid_price']=false;

            $xstr1=
              str_pad($xrow1['trcd'], 20, " ", STR_PAD_RIGHT).
              str_pad($xrow2['prdcd'], 15, " ", STR_PAD_RIGHT);
            if ($xrow1['trtype']=='2') {
              $xstr1.=
                str_pad($xrow_item['xcp'], 10, " ", STR_PAD_LEFT).
                str_pad($xrow2['cp'], 10, " ", STR_PAD_LEFT);
            }else{
              $xstr1.=
                str_pad($xrow_item['xdp'], 10, " ", STR_PAD_LEFT).
                str_pad($xrow2['dp'], 10, " ", STR_PAD_LEFT);
            }
            $xstr1.=
              str_pad($xrow_item['xpv'], 10, " ", STR_PAD_LEFT).
              str_pad($xrow2['pv'], 10, " ", STR_PAD_LEFT).
              str_pad($xrow_item['xbv'], 10, " ", STR_PAD_LEFT).
              str_pad($xrow2['bv'], 10, " ", STR_PAD_LEFT).
              "\n";
            $xstr_price_err.=$xstr1;
            //if ($debug) echo $xstr1."*<br/>\n";
            if (!isset($xtrh_trcd_err))
              $xtrh_trcd_err=array();
            if (!in_array($xrow2['trcd'], $xtrh_trcd_err))
              $xtrh_trcd_err[]=$xrow2['trcd'];
            if (!isset($xtrd_trcd_err))
              $xtrd_trcd_err=array();
            $xtrd_trcd_err[]=$xrow2['trcd'];
            //if ($debug) echo "checking xvalid_price=".((!$xvalid_price)?'falsex':'truex')."<br/>\n";
          } //end if valid price
        }//end if valid
        //if ($debug) echo "checking xvalid_price=".((!$xvalid_price)?'falsex':'truex')."<br/>\n";
      }//end for detail

      // checking msmemb
      if ($debug) echo "xexisttrcd=$xexisttrcd trtype={$xrow1['trtype']}<br/>\n";
      if ($xexisttrcd==false && $xrow1['trtype']=='1') {
        //if (pg_num_rows($xmres)==0) {
        if ($xmrow[0]=='') {
          //$xvalid_all =false;
          $xvalid_msmemb=false;
          $xarr_[$xrow1['trcd']]['valid_msmemb']=false;
          $xstr_msmemb_err.=
            str_pad($xrow1['trcd'], 20, " ", STR_PAD_RIGHT).
            str_pad($xrow1['code'], 15, " ", STR_PAD_RIGHT)."\n";
        }else{
          if ($xmethod=='manual'){
            if ($xfrom=='0')
              $xfrmsys='2';
            else
              $xfrmsys='3';
            $xpsql="update msmemb set frmsys='$xfrmsys' where code='".$xrow1['code']."'";
            if ($debug) echo $xpsql."<br/>\n";
            pg_exec($db, $xpsql);
          }
        }
      }


      if ($debug) {
        echo "month1=$month1 trdt--->".$xrow1['trdt'].' <br/>';
      }
      $xbackdate_mode=false;
      if ($month1==substr($xrow1['trdt'],3,2)) {
        //echo 'same month<br/>';
      }else{
        $xsql_backdate='select day_allow from backdate_sync_setup limit 1';
        $xres_backdate=pg_query($db, $xsql_backdate);
        if (pg_num_rows($xres_backdate)>0){
          $xrow_backdate=pg_fetch_assoc($xres_backdate,0);
          $xday_allow=$xrow_backdate['day_allow'];
          if ($date1<=$xday_allow) {
          }else{
            $xbackdate_mode=true;
          }
        }
      }

      //print_r($xarr_);
      //echo "<br/>\n";
      //if ($xvalid_qoh && $xvalid_price && $xvalid_msmemb) {
      if ($xarr_[$xrow1['trcd']]['valid_qoh'] &&
        $xarr_[$xrow1['trcd']]['valid_price'] &&
        $xarr_[$xrow1['trcd']]['valid_msmemb']
      )
      {
        //if ($debug) {
        //  echo "<i>".$xrow1['trcd']." xvalid_qoh=".((!$xvalid_qoh)?'falsex':'truex')."</i>*<br/>\n";
        //  echo "<i>".$xrow1['trcd']." xvalid_price=".((!$xvalid_price)?'falsex':'truex')."</i>*<br/>\n";
        //}

        if($xbackdate_mode) {
          //$xday_allow=4;
          //$xrow1['trdt']=str_pad($xday_allow, 2, '0', STR_PAD_LEFT)."-$month1-$year1";
          $xrow1['trdt']="$date1-$month1-$year1";
          if ($debug) {
            echo "new trdt--->{$xrow1['trdt']}".'<br/>';
          }
        }
        $xship=0;
        for ($k=0; $k<pg_num_fields($xres1); $k++) {
            if ($debug) {
              if (pg_fieldname($xres1, $k)=='ship_cost') {
                echo pg_fieldname($xres1, $k)."*<br/>\n";
                echo $xrow1[pg_fieldname($xres1, $k)]."<br/>\n";
              }
            }


            if (!preg_match('/FLAG|FRMSYS|IS_SYNC|SYNC_DATE|IS_SENT|VOID_AMT|TOTDISCITEM|DISC_PERCENT|ROUNDED|STATUS|SYNC_TXT|DP_DSP/i',
              pg_fieldname($xres1, $k)))
            {
              if (preg_match('/TRDT|ETDT|LUPDDT|LUPDTM|SYNC_DATE|TRTM|DATEPOST|BPDT|'.
              'seller_has_tax|ship_vat|disc_vat|vat|seller_taxid/i',pg_fieldname($xres1, $k))
              && trim($xrow1[pg_fieldname($xres1, $k)])=='')
              {
                $arr_trh_data[pg_fieldname($xres1, $k)]='null';
              }elseif (preg_match('/INCP_FEE/i',pg_fieldname($xres1, $k))
              && $xrow1[pg_fieldname($xres1, $k)]=='')
              {
                $arr_trh_data[pg_fieldname($xres1, $k)]='0';
              }elseif (preg_match('/TRTYPE/i', pg_fieldname($xres1, $k))) {
                if ($xrow1['trtype']=='1')
                  $arr_trh_data['trtype']='1';
                elseif ($xrow1['trtype']=='2') {
                  //$arr_trh_data['trtype']='6';
                  $arr_trh_data['trtype']='2';
                  $xrow1['staff_name']='WALK IN CUSTOMER';
                  $xrow1['code']='*********';
                  $xrow1['note3']='CR';
                }else{
                  $arr_trh_data['trtype']=$xrow1[pg_fieldname($xres1, $k)];
                }
              }elseif (preg_match('/^SHIP$/i',pg_fieldname($xres1, $k))) {
                $xship=$xrow1[pg_fieldname($xres1, $k)];
              }elseif (preg_match('/BALANCE/i',pg_fieldname($xres1, $k)) && $xrow1[pg_fieldname($xres1, $k)]==''){
								$arr_trh_data[pg_fieldname($xres1, $k)]=0;
              }else{
                $arr_trh_data[pg_fieldname($xres1, $k)]=$xrow1[pg_fieldname($xres1, $k)];
              }//end if
            }//end if
        }//end for
//         if ($debug) echo "xfields=$xfields<br/>\n";

        if (pg_num_rows($xres5)>0) {
          $xrow5=pg_fetch_assoc($xres5, 0);
          $arr_trh_data['ship_cost']=$xrow5['cost'];
        }

        if ($debug) echo "<br/>checking discount<br/>\n";

        //checking discount

        //disc new
        $res_rep = "";
        $trxmonth = "";
        $asql = "select pdisc, code, trdt,discpack from network_mlm.newmstrh where trcd='$txt_trcd'";
        if ($debug)
              echo "$asql<br/>\n";
        $result_1=pg_query($db, $asql);
        if (pg_num_rows($result_1)>0) {
        // if($db2->doQuery($asql)->isFound()){
          $arow = '';
          while ($row = pg_fetch_assoc($result_1)) {
            $arow=$row;
          }

          $trxmonth = date('Ym',strtotime($arow['trdt']));
          // $arow=$db2->getFirstRow();
          if ($debug) echo "<br/>pdisc:".$arow['pdisc']." discpack:".$arow['discpack']."<br/>\n";
          if($arow['pdisc']>0 && $arow['discpack']!="") {
            $bsql = "select trcd, discpack from discpack_balancelog where trcd='$txt_trcd' ";
            $result_2=pg_query($db, $bsql);
            if ($debug)
              echo "$bsql(on ecom)<br/>\n";
            if (pg_num_rows($result_2)>0) {
            // if($db2->doQuery($bsql)->isFound()){
              $brow = '';
              while ($row = pg_fetch_assoc($result_2)) {
                $brow=$row;
              }
              // $brow=$db2->getFirstRow();
              $xcode = $arow['code'];
                $csql =
                "select discpack_balance.dspbal, discpack_balance.dspamt, x1.xbefore_value, x1.xuse_value,x1.xafter_value
                from network_mlm.newmstrh,
                (
                  select code, discpack,
                  sum(before_value) as xbefore_value,
                  sum(use_value) as xuse_value,
                  sum(after_value) as xafter_value
                  from discpack_balancelog
                  where discpack_balancelog.code='$xcode' and (discpack_balancelog.reversed<>'1' or discpack_balancelog.reversed is null or discpack_balancelog.reversed='0')  and discpack='{$brow["discpack"]}'
                  group by code,discpack
                ) x1,
                discpack_balance
                where network_mlm.newmstrh.code=discpack_balance.code and network_mlm.newmstrh.bc_id=discpack_balance.cn_id
                and discpack_balance.discpack=x1.discpack and network_mlm.newmstrh.code=x1.code and network_mlm.newmstrh.trcd='$txt_trcd' ";

              $result_3=pg_query($db, $csql);
              if ($debug)
              echo "$csql(on ecom)<br/>\n";
              if (pg_num_rows($result_3)>0) {
              // if($db2->doQuery($csql)->isFound()){
                // $crow=$db2->getFirstRow();
                $crow = '';
                while ($row = pg_fetch_assoc($result_3)) {
                  $crow=$row;
                }
                $xselisih = $crow["xuse_value"];
                $xselisih2 = $crow['dspamt']-$xselisih;
                if ($debug) {
                  echo ("Cek Balance".round($crow['dspbal'],2)."==".round($xselisih2,2));
                }

                // Logger::debug("checking F15: {$crow['dspbal']}==$xselisih2");
                if (round($crow['dspbal'],2)==round($xselisih2,2)) {
                }else{
                  $query = "UPDATE network_mlm.sales_log SET
                  send_flag='2',sendextra_flag='F15'
                  where trcd='$txt_trcd'";
                  // f15 di remark karena hanya perlu pengecekan dari online purchase
                  // $result = pg_exec($db,$query);
                  if ($debug)
                  echo "$query(on ecom)<br/>\n";
                  // $db2->doUpdate("network_mlm.sales_log",array("send_flag"=>'2',"sendextra_flag"=>'F15'),"trcd='$txt_trcd'");
                  // fwrite($ourFileHandle, "- $txt_trcd (F15: Send fail due to DSP balance is insufficient) \n");

                  // f15 di remark karena hanya perlu pengecekan dari online purchase
                  // $res_rep .= "- $txt_trcd (F15: Send fail due to DSP balance is insufficient) <br>";
                }
              }else{
                $query = "UPDATE network_mlm.sales_log SET
                send_flag='2',sendextra_flag='F15'
                where trcd='$txt_trcd'";
                // f15 di remark karena hanya perlu pengecekan dari online purchase
                // $result = pg_exec($db,$query);
                if ($debug)
                  echo "$query(on ecom)<br/>\n";
                // $db2->doUpdate("network_mlm.sales_log",array("send_flag"=>'2',"sendextra_flag"=>'F15'),"trcd='$txt_trcd'");
                // fwrite($ourFileHandle, "- $txt_trcd (F15: Send fail due to DSP balance is insufficient) \n");

                // f15 di remark karena hanya perlu pengecekan dari online purchase
                // $res_rep .= "- $txt_trcd (F15: Send fail due to DSP balance is insufficient) <br>";
              }
            }else{
              $query = "UPDATE network_mlm.sales_log SET
              send_flag='2',sendextra_flag='F14'
              where trcd='$txt_trcd'";
              $result = pg_exec($db,$query);
              if ($debug)
                  echo "$query(on ecom)<br/>\n";
              // $db2->doUpdate("network_mlm.sales_log",array("send_flag"=>'2',"sendextra_flag"=>'F14'),"trcd='$txt_trcd'");
              // fwrite($ourFileHandle, "- $txt_trcd (F14: Send fail due to DSP transaction not found) \n");
              $res_rep .= "- $txt_trcd (F14: Send fail due to DSP transaction not found) <br>";
            }
          }
        }

      if ($debug) {
        echo "$res_rep(on ecom trxdata)<br/>\n";
      }


        if ($debug) echo "<br/>end of checking discount<br/><br/>\n";
        //end of checking discount

        $xcsql="select trcd from newmstrh where trcd='".$xrow1['trcd']."'";
        if ($debug) echo "$xcsql ship=$xship<br/>\n";
        $xcres=pg_query($db, $xcsql);
        if (pg_num_rows($xcres)==0) {
          $arr_trh_data['frmsys']='2';
          $arr_trh_data['frmsys']='3';

          $arr_trh_data['new_format']='1';
          // $arr_trh_data['reference_trcd']=$xrow1['trcd'];
          if ($debug) print_r($arr_trh_data);
          if ($res_rep==""){
            db_insert('newmstrh', $arr_trh_data);
          }

          if ($arr_trh_data['trtype']==2) {
						Logger::debug('generate trh trtype 11 here*');
            $xloccd				= $arr_trh_data['loccd'];
            $querr2="select br_prefix from msms_new where br_code='$xloccd'";
            $result2=pg_query($db,$querr2);
            $row2=pg_fetch_row($result2,0);
            $digitcb_11=$row2[0].'-';

            $arr_trh_data_11 = $arr_trh_data;
            $xtrxno_counter=substr($arr_trh_data_11['trcd'], -10);    // returns "ef"
            $xthe_trcd_11 = $digitcb_11.'CRMC'.$xtrxno_counter;
            //Logger::debug('xthe_trcd_11',$xthe_trcd_11);
            $arr_trh_data_11['trcd']=$xthe_trcd_11;
            $arr_trh_data_11['trtype']='11';
            // $arr_trh_data_11['code']  =$hea['code'];
            // $arr_trh_data_11['upcode']= get_upcode($hea['code']);
            $arr_trh_data_11['frmsys']='null';

            $arr_trh_data_11['tdp']   = 0;
            $arr_trh_data_11['ndp']   = 0;
            $arr_trh_data_11['totpay']= 0;
            // $arr_trh_data_11['tpv']=$hea['tpv'];
            // $arr_trh_data_11['npv']=$hea['npv'];
            // $arr_trh_data_11['tbv']=$hea['tbv'];
            // $arr_trh_data_11['nbv']=$hea['nbv'];

            $arr_trh_data_11['note3']='CR';
            // $arr_trh_data_11['cbno']=$arr_trh_data['trcd']; //$newTransId;
            //$arr_trh_data_11['reference_trcd']=$newTransId;
            Logger::debug('arr_trh_data_11',$arr_trh_data_11);
            db_insert('newmstrh', $arr_trh_data_11);
          }//end if

        }
        pg_free_result($xcres);

        if (pg_num_rows($xres5)>0) {
          $xcsql="select trcd from msship where trcd='".$xrow1['trcd']."'";
          $xcres=pg_query($db, $xcsql);
          if (pg_num_rows($xcres)==0) {
            //$arr_trh_data1 = array('trcd'=>$xrow1['trcd'], 'cost'=>$xship);
            if ($res_rep==""){
              db_insert('msship', $xrow5);
            }
          }
        }


        //if ($xship>0) {
        /////////DETAIL/////////
        if ($res_rep==""){
          for ($l=0; $l<pg_num_rows($xres2); $l++) {
            $xrow2_=pg_fetch_assoc($xres2, $l);
            for ($m=0; $m<pg_num_fields($xres2); $m++) {
              if (!preg_match('/FLAG|IS_SYNC|SYNC_DATE|DISC|TRDT|PROMO_ID|UNIT_PRICE|UNIT_VAT|DP_DSP/i',pg_fieldname($xres2, $m)))
              {
                if (preg_match('/DP|CP|SP|PV|BV/i', pg_fieldname($xres2, $m)) && $xrow2_[pg_fieldname($xres2, $m)]=='')
                {
                  $xrow2_[pg_fieldname($xres2, $m)]='0';
                }
                if ($m==0) {
                  $xfields2=pg_fieldname($xres2, $m);
                  $xvalues2="'".$xrow2_[pg_fieldname($xres2, $m)]."'";
                  if ($arr_trh_data['trtype']==2) {
                    $xvalues2_11="'".$arr_trh_data_11['trcd']."'";
                  }
                }else{
                  $xfields2.=",".pg_fieldname($xres2, $m);
                  $xvalues2.=",'".$xrow2_[pg_fieldname($xres2, $m)]."'";
                  if ($arr_trh_data['trtype']==2) {
                    if (preg_match('/DP/i', pg_fieldname($xres2, $m))) {
                      $xvalues2_11.=",'".$xrow2_['cp']."'";
                    } else if (preg_match('/SP/i', pg_fieldname($xres2, $m))) {
                      $xvalues2_11.=",'".$xrow2_['dp']."'";
                    } else {
                      $xvalues2_11.=",'".$xrow2_[pg_fieldname($xres2, $m)]."'";
                    }
                  }
                }
              }
            }
            $xcsql="select trcd from newmstrd where trcd='".$xrow2_['trcd']."' and prdcd='".$xrow2_['prdcd']."'";
            if ($debug) {
              echo $xcsql."*<br/>\n";
            }
            $xcres=pg_query($db, $xcsql);
            if (pg_num_rows($xcres)==0) {
              $xpsql2="INSERT INTO newmstrd ($xfields2) VALUES ($xvalues2)";
              if ($debug) {
                echo $xpsql2."*<br/>\n";
              }
              pg_exec($db, $xpsql2);
              update_inloc_basic($xrow2_['prdcd'],$xrow2_['qty'],$debug,$xrow1['loccd'],0,'-');

              $xboulder_valley1->fupdate_boulder($xrow1['code'], $xrow2_['prdcd'], $xrow2_['qty'], $xrow2_['trcd']);

              if ($arr_trh_data['trtype']==2) {
                Logger::debug('generate trd trtype 11 here*');
                $xpsql2_11="INSERT INTO newmstrd ($xfields2) VALUES ($xvalues2_11)";
                pg_exec($db, $xpsql2_11);
              }
            }
          }
        }

        /////////PAYMENT/////////
        //$xsql3="SELECT * FROM ".$xschema."temp_mspayment WHERE TRCD='".$xrow1['trcd']."'";
        if ($res_rep==""){
          $xsql3 ="SELECT * FROM ".$xschema.$xarr_table1[2]." WHERE TRCD='".$xrow1['trcd']."'";
          if ($debug) echo $xsql3."(on ecom)<br/>\n";
          $xres3=pg_query($xdb, $xsql3);
          for ($ii=0; $ii<pg_num_rows($xres3); $ii++) {
            $xrow3=pg_fetch_assoc($xres3, $ii);
            for ($jj=0; $jj<pg_num_fields($xres3); $jj++) {
              if (!preg_match('/FLAG|IS_SYNC|SYNC_DATE|TRDT|DP_DSP|CONVERTED_PAY/i',pg_fieldname($xres3, $jj)))
              {
                if (preg_match('/TOTPAY/i', pg_fieldname($xres3, $jj)) && $xrow3[pg_fieldname($xres3, $jj)]=='')
                {
                  $xrow3[pg_fieldname($xres3, $jj)]='0';
                }
                if (strtoupper(pg_fieldname($xres3, $jj))=='RETURN' && $xrow3[pg_fieldname($xres3, $jj)]=='')
                {
                  $xrow3[pg_fieldname($xres3, $jj)]='0';
                }

                if ($jj==0) {
                  $xfields3=pg_fieldname($xres3, $jj);
                  $xvalues3="'".$xrow3[pg_fieldname($xres3, $jj)]."'";
                }else{
                  $xfields3.=",".pg_fieldname($xres3, $jj);
                  if ($arr_trh_data['trtype']==2 && pg_fieldname($xres3, $jj)=='trtype') {
                    $xvalues3.=",'"."BSP"."'";
                  } else {
                    $xvalues3.=",'".$xrow3[pg_fieldname($xres3, $jj)]."'";
                  }
                }
              }
            }
          }
          pg_free_result($xres3);
        }


        $xcsql3="select trcd from mspayment where trcd='".$xrow1['trcd']."'";
        $xcres3=pg_query($db, $xcsql3);
        if ($res_rep==""){
          if (pg_num_rows($xcres)==0) {
            $xpsql3="INSERT INTO mspayment ($xfields3) VALUES ($xvalues3)";
            if ($debug) {
              echo $xpsql3."<br/>\n";
            }
            pg_exec($db, $xpsql3);
          }
          pg_free_result($xcres3);
        }

        //$xpsql1="UPDATE ".$xschema."temp_newmstrh set flag='1',is_sync='1',sync_date=now() where trcd='".$xrow1['trcd']."'";
        $xpsql1="UPDATE ".$xschema.$xarr_table1[0]." set $xflag_field='1',is_sync='1',sync_date=now() where trcd='".$xrow1['trcd']."'";
        if ($debug)
          echo "$xpsql1(on ecom)<br/>\n";
        if ($res_rep==""){
          pg_exec($xdb, $xpsql1);
        }

        //$xpsql1="UPDATE ".$xschema."temp_newmstrd set sync_flag='1' where trcd='".$xrow1['trcd']."'";
        $xpsql1="UPDATE ".$xschema.$xarr_table1[1]." set $xflag_field='1' where trcd='".$xrow1['trcd']."'";
        if ($debug)
          echo "$xpsql1(on ecom)<br/>\n";
        if ($res_rep==""){
          pg_exec($xdb, $xpsql1);
        }
        //$xpsql1="UPDATE ".$xschema."temp_mspayment set sync_flag='1' where trcd='".$xrow1['trcd']."'";
        $xpsql1="UPDATE ".$xschema.$xarr_table1[2]." set $xflag_field='1' where trcd='".$xrow1['trcd']."'";
        if ($debug)
          echo "$xpsql1(on ecom)<br/>\n";
        if ($res_rep==""){
          pg_exec($xdb, $xpsql1);
        }
      }else{//else if not valid_
        //$xpsql1="UPDATE ".$xschema."temp_newmstrh set sync_flag='2' where trcd='".$xrow1['trcd']."'";
        $xpsql1="UPDATE ".$xschema.$xarr_table1[0]." set $xflag_field='2' where trcd='".$xrow1['trcd']."'";
        if ($debug)
          echo "$xpsql1(on ecom)<br/>\n";
        if ($res_rep==""){
          pg_exec($xdb, $xpsql1);
        }
        //$xpsql1="UPDATE ".$xschema."temp_newmstrd set sync_flag='2' where trcd='".$xrow1['trcd']."'";
        $xpsql1="UPDATE ".$xschema.$xarr_table1[1]." set $xflag_field='2' where trcd='".$xrow1['trcd']."'";
        if ($debug)
          echo "$xpsql1(on ecom)<br/>\n";
        if ($res_rep==""){
          pg_exec($xdb, $xpsql1);
        }
        //$xpsql1="UPDATE ".$xschema."temp_mspayment set sync_flag='2' where trcd='".$xrow1['trcd']."'";
        $xpsql1="UPDATE ".$xschema.$xarr_table1[2]." set $xflag_field='2' where trcd='".$xrow1['trcd']."'";
        if ($debug)
          echo "$xpsql1(on ecom)<br/>\n";
        if ($res_rep==""){
          pg_exec($xdb, $xpsql1);
        }
      }//end if not valid
      if ($res_rep==""){
        pg_free_result($xres2);
      }
    }
    if ($res_rep==""){
      pg_free_result($xres1);
    }
    if ($debug==0) {
      //$bresult=pg_query($db,"commit transaction");
    }else{
      echo "no commit<br/>\n";
    }

    if (isset($xtrh_trcd)) {
      sort ($xtrh_trcd);
      reset ($xtrh_trcd);
        for ($z=0; $z<count($xtrh_trcd); $z++) {
          //echo $xtrh_trcd[$z]."<br/>\n";
          if (!isset($xtrh_trcd_err))
            $xtrh_trcd_err=array();
          if (!in_array($xtrh_trcd[$z], $xtrh_trcd_err)) {
            if ($xmin_trh_trcd=='')
              $xmin_trh_trcd=$xtrh_trcd[$z];
            $xmax_trh_trcd=$xtrh_trcd[$z];
            $xcount_trh_trcd++;
          }
        }
    }

    if (isset($xtrd_trcd)) {
      sort ($xtrd_trcd);
      reset ($xtrd_trcd);
      if (!isset($xtrd_trcd_err))
        $xtrd_trcd_err=array();

      for ($z=0; $z<count($xtrd_trcd); $z++) {
        //echo $xtrd_trcd[$z]."<br/>\n";
        if (!in_array($xtrd_trcd[$z], $xtrd_trcd_err)) {
          $xcount_trd_trcd++;
        }
      }

      if ($xcount_trh_trcd>0) {
        $xstr_email_ok=
          "[newmstrh] \n".
          "number of records = $xcount_trh_trcd \n".
          "first transaction code =  $xmin_trh_trcd \n".
          "last transaction code =  $xmax_trh_trcd \n\n".
          "[newmstrd] \n".
          "number of records = $xcount_trd_trcd \n".
          "first transaction code =  $xmin_trh_trcd \n".
          "last transaction code =  $xmax_trh_trcd \n\n".
          "[mspayment] \n".
          "number of records = $xcount_trh_trcd \n".
          "first transaction code =  $xmin_trh_trcd \n".
          "last transaction code =  $xmax_trh_trcd \n\n";
      }
    }

    if ($debug) echo "<br/><br/>check eligible<br/><br/>\n";

    if ($res_rep=="") {
      /* check for discount enhancement */
      $asql = "select pdisc,tdp,tpv,tbv from network_mlm.newmstrh where trcd='$txt_trcd'";
      $result_1=pg_query($db, $asql);
      if ($debug)
            echo "$asql(on ecom trx)<br/>\n";
      $trxdata = '';
      while ($row = pg_fetch_assoc($result_1)) {
        $trxdata=$row;
      }
      if ($debug) {
        echo "(on ecom trxdata)<br/>\n";
        var_dump($trxdata);
      }

      // $trxdata = $db2->doQuery("select pdisc,tdp,tpv,tbv from network_mlm.newmstrh where trcd='$txt_trcd'")->getFirstRow();
      if($trxdata["pdisc"]>0) {
        $bsql = "select * from discpack_balancelog where trcd='$txt_trcd' ";
        $result_2=pg_query($db, $bsql);
        if ($debug)
            echo "$bsql(on ecom)<br/>\n";
        if (pg_num_rows($result_2)>0) {
        // if($db2->doQuery("select * from discpack_balancelog where trcd='$txt_trcd'")->isFound()){
          $c = '';
          while ($row = pg_fetch_assoc($result_2)) {
            $c=$row;
          }
          // $c = $db2->getFirstRow();
          $csql = "select * from discpack_eligible where year_month='$trxmonth' and discpack_id='{$c["discpack"]}' and code='{$c["code"]}' and cn_id='MY'";
          $result_3=pg_query($db, $csql);
          if ($debug)
            echo "$csql(on ecom)<br/>\n";
          if (pg_num_rows($result_3)==0) {
          // if(!$db2->doQuery("select * from discpack_eligible where year_month='$trxmonth' and discpack_id='{$c["discpack"]}' and code='{$c["code"]}' and cn_id='$region'")->isFound()){
              $eligible_flag = 0;

              $asql = "select discbasis as based,minvalue as minval,discrate_dp as dpval,discrate_pv as pvval,
                discrate_sv as svval from disccond_setup where id_cnt='MY' and discpack='{$c["discpack"]}'";
              $result_4=pg_query($db, $asql);
              if ($debug)
                echo "$asql(on ecom)<br/>\n";
              $dtcond = '';
              while ($row = pg_fetch_assoc($result_4)) {
                $dtcond=$row;
              }
              // $dtcond = $db2->doQuery($asql)->getFirstRow();
              if($dtcond["based"]=="DP") {
                $dp_disc = (empty($dtcond["dpval"]))?0:$dtcond["dpval"];
                if($trxdata["tdp"]>=$dp_disc)
                  $eligible_flag = 1;
              }
              if($dtcond["based"]=="PV") {
                $pv_disc = (empty($dtcond["pvval"]))?0:$dtcond["pvval"];
                if($trxdata["tpv"]>=$pv_disc)
                  $eligible_flag = 1;
              }
              if($dtcond["based"]=="SV") {
                $sv_disc = (empty($dtcond["svval"]))?0:$dtcond["svval"];
                if($trxdata["tbv"]>=$sv_disc)
                  $eligible_flag = 1;
              }
              if ($debug)
                echo "$eligible_flag(on ecom Eligible)<br/>\n";
              if($eligible_flag==1) {
                // $db2->doInsert("discpack_eligible",
                // array(
                //   "cn_id" => $region,
                //   "year_month" => $trxmonth,
                //   "code" => $c["code"],
                //   "trcd" => $newTransId,
                //   "discpack_id" => $c["discpack"],
                //   "eligible" => "true"
                // ));

                $conddet = array(
                  "cn_id" => "'MY'",
                  "year_month" => "'$trxmonth'",
                  "code" => "'$c[code]'",
                  "trcd" => "'$txt_trcd'",
                  "discpack_id" => "'$c[discpack]'",
                  "eligible" => "true"
                );

                $query = "INSERT INTO discpack_eligible (".implode(",",array_keys($conddet)).")
                    values (".implode(",",array_values($conddet)).")";
                $result = pg_exec($db,$query);
                if ($debug)
                echo "$query(on ecom)<br/>\n";
                $discpack = $c["discpack"];
                $query = "UPDATE newmstrh SET
                post='1',discpack='$discpack'
                where trcd='$txt_trcd'";
                $result = pg_exec($db,$query);
                if ($debug)
                echo "$query(on ecom)<br/>\n";

                // $db2->doUpdate("newmstrh",array("post"=>'1','discpack'=>$c["discpack"]),"trcd='$newTransId'");
              }
            }
          }
        }
      }

      if ($debug) echo "<br/>end of check eligible<br/><br/>\n";

    $arr_result=array(
        'xvalid_all'=>$xvalid_all,
        'xvalid_qoh'=>$xvalid_qoh,
        'xstr_email_err'=>$xstr_email_err,'xstr_email_ok'=>$xstr_email_ok,
        'xcount_trh_trcd'=>$xcount_trh_trcd,
        'xvalid_price'=>$xvalid_price, 'xstr_price_err'=>$xstr_price_err, 'xstr_price_ok'=>$xstr_price_ok,
        'xvalid_msmemb'=>$xvalid_msmemb, 'xstr_msmemb_err'=>$xstr_msmemb_err,
        'xvalid_detail'=>$xvalid_detail,
        'xvalid_balance'=>$res_rep
      );
    if ($debug) {
      echo "(on ecom last result)<br/>";
      var_dump($arr_result);
    }

    return $arr_result;
  }//end function

  function is_leapyear($year = 2004) {
    $is_leap = date('L', strtotime("$year-1-1"));
    return $is_leap;
  }

  function fget_qty_salescb($cbo_country, $cbo_loccd_type, $xloccd, $xprdcd, $xmonth) {
    global $db, $debug;
    $xhasil=0;


    $xsql_ms=
      "select coalesce(sum(qty),0) as xtotqty from newmstrh a, newmstrd b
      where a.trcd=b.trcd and a.trtype='1'
      and to_char(a.trdt, 'YYYY-MM')='$xmonth' and b.prdcd='$xprdcd' ";
    if ($cbo_country!='x') {
      $xsql_ms.="and a.bc_id='$cbo_country' ";
    }
//     if ($xloccd!='x') {
//       $xsql_ms.="and loccd='$xloccd' ";
//     }
    if ($cbo_country!='x') {
      if ($xloccd!='x' && $xloccd!='') {
        $xsql_ms.="and (a.loccd='$xloccd') ";
      }else{
        //$xsql_ms.="and a.loccd in (select mn.br_code from msms_new mn where mn.br_region = '$cbo_country')";
        $xsql_ms.="and a.bc_id='$cbo_country' ";
      }
    }//end if

    //$xsql1.=
    //  "union all ";
    $xsql_sc=
      "select coalesce(sum(qty),0) as xtotqty from newsctrh a, newsctrd b
      where a.trcd=b.trcd and a.trtype='1'
      and to_char(a.trdt, 'YYYY-MM')='$xmonth' and b.prdcd='$xprdcd' ";
    if ($cbo_country!='x' && $cbo_country!='') {
      $xsql_sc.="and a.bc_id='$cbo_country' ";
    }

    if ($cbo_country!='x') {
      if ($xloccd!='x' && $xloccd!='') {
        $xsql_sc.="and (a.loccd='$xloccd') ";
      }else{
/*        $xsql_sc.="and a.loccd in (select sme.sccode from sub_mssc_ext sme, sub_mssc sm
          where sm.code = sme.sccode  and sme.sc_reg = '$cbo_country') ";//and  sm.status = 'N'*/
        $xsql_sc.="and a.bc_id='$cbo_country' ";
      }
    }//end if

    $xsql1=
      "select sum(xtotqty) from ( ";
    if ($cbo_loccd_type==0) {
      $xsql1.="$xsql_ms union all $xsql_sc ";
    }elseif ($cbo_loccd_type==1) {
      $xsql1.="$xsql_ms ";
    }elseif ($cbo_loccd_type==2) {
      $xsql1.="$xsql_sc ";
    }

    $xsql1.=
      ") as table1 ";

    if ($debug /*&& preg_match('/09/i', $xmonth)*/ /*&& $xprdcd=='AEWS'*/) {
//       echo nl2br($xsql1)." <br/>\n";
    //if ($debug)
    //  echo "xmonth=$xmonth <br/>\n";
      //echo "$xmonth $xprdcd <br/>\n";
    }
    $xres1=pg_query($db, $xsql1);
    if (pg_num_rows($xres1)>0) {
      $xrow1=pg_fetch_row($xres1, 0);
      $xhasil=$xrow1[0];
    }

    return $xhasil;
  }

  function fget_curr_code($xloccd) {
    global $db, $debug;
    $cursign="USD";
    if (check_level_ex($xloccd)=='BR') {
      $sqlku = "select curr_code from msms_new where br_code='$xloccd'";
      $resku = pg_exec($db,$sqlku);
      if(pg_numrows($resku)>0) {
        list($cursign) = pg_fetch_row($resku,0);
        if($cursign=='') $cursign="USD";
      } else $cursign="USD";
  /*    if ($debug)
        echo "$sqlku $cursign<br/>\n";*/
    }else{
      $region = chkregion($xloccd,0);
      $sqlku = "select def_currency from mlm_setup where id_cnt='$region'";
      $resku = pg_exec($db,$sqlku);
      if(pg_numrows($resku)>0) {
        list($cursign) = pg_fetch_row($resku,0);
        if($cursign=='') $cursign="USD";
      } else $cursign="USD";
    }
    return $cursign;
  }

  function fget_cls_bal($txt_msid, $csdt) {
    global $debug;
    $xsql1="select closing_bal_amt from tdaily_closing_bal where loccd='$txt_msid' and csdt<'$csdt' order by csdt desc limit 1;";
    if ($debug)
      echo "$xsql1<br/>\n";
    $xrow1=fpg_exec4($xsql1);
    $xresult=0;
    if ($xrow1[0]!=''){
      $xresult=$xrow1[0];
    }
    return -$xresult;
  }

  function fupdate_tdaily_closing($xbranch, $txtTgl, $txtTgl2='') {
    global $db, $debug;
		if ($debug) echo "fupdate_tdaily_closing($xbranch, $txtTgl, $txtTgl2);<br>\n";
    list($dd1, $mm1, $yy1)=preg_split('/[\-\/]/', $txtTgl);
    if ($debug) echo "$dd1 $mm1 $yy1 <br/>\n";
    $txtTgl1 = '01/09/2012';
    //if (preg_match('/training/', $REQUEST_URI)) {
    //if (preg_match('/training/', $REQUEST_URI)) {
      //$txtTgl1 = '01/01/'.($yy1-1);
      $mktime1 = mktime(0,0,0,$mm1-2, 1, $yy1);
      $txtTgl1 = date('d/m/Y', $mktime1);
    //}
/*    $xsql1 = "select min(csdt) as xmintgl from tdaily_cash where loccd='$xbranch' ";
    $xres1 = pg_exec($db, $xsql1);
    if (pg_num_rows($xres1)>0) {
      $xrow1=pg_fetch_row($xres1, 0);
      $txtTgl1 = $xrow1[0];
    }*/
    if ($txtTgl2=='') $txtTgl2=date('d/m/Y');

    if ($debug) echo "<b>$txtTgl1 $txtTgl2</b><br/>\n";

    $xsql1=
      "select distinct trdt from
      (
      select csdt as trdt
      from tdaily_cash
      where loccd='$xbranch' and csdt>='$txtTgl1' and csdt<='$txtTgl2'
      union
      select csdt as trdt
      from tdaily_cash_adj
      where loccd='$xbranch' and csdt>='$txtTgl1' and csdt<='$txtTgl2'
      union
      select trdt
      from newmstrh
      where loccd='$xbranch' and trdt>='$txtTgl1' and trdt<='$txtTgl2'
      union
      select trdt
      from newmsivtrh
      where loccd='$xbranch' and trdt>='$txtTgl1' and trdt<='$txtTgl2'
      )
      as table1
      order by trdt ";
    $xres1=pg_query($db, $xsql1);

    if ($debug)
       echo nl2br($xsql1)." ".pg_num_rows($xres1)."<br/>\n";

    //$xprev_bal   =0;
    $xprev_bal   =fget_cls_bal($xbranch, $txtTgl1);

    for ($i=0; $i<pg_num_rows($xres1); $i++)
    {
      $xrow1 = pg_fetch_row($xres1, $i);
      if ($debug) echo $xrow1[0]."<br/>\n";

      $xdaily_cash2=fget_tot_dailycash($xbranch, $txtTgl1, $xrow1[0]);
      $xsales_amt2 =fget_br_totpay($xbranch, $txtTgl1, $xrow1[0]);

      $xcur_bal4=number_format($xprev_bal+$xdaily_cash2-$xsales_amt2, 2, '.', '');

      if ($debug) {
        echo "$xdaily_cash2=fget_tot_dailycash($xbranch, $txtTgl1, $xrow1[0])<br/>\n";
        echo "$xsales_amt2 =fget_br_totpay($xbranch, $txtTgl1, $xrow1[0])<br/>\n";
        echo "$xcur_bal4=$xprev_bal+$xdaily_cash2-$xsales_amt2 xprev_bal=$xprev_bal<br/>\n";
      }
//       $xcsql ="delete from tdaily_closing_bal where loccd='$xbranch' and csdt>'$xrow1[0]';";
//       if ($debug)
//         echo "$xcsql<br/>\n";
//       $xcres = pg_exec($db, $xcsql);

      $xcur_bal4_save = -($xcur_bal4);
      $xcsql ="select closing_bal_amt from tdaily_closing_bal where loccd='$xbranch' and csdt='$xrow1[0]';";
      $xcres = pg_exec($db, $xcsql);
      if (pg_num_rows($xcres)>0)
      {
        $xpsql = "update tdaily_closing_bal set closing_bal_amt=$xcur_bal4_save where loccd='$xbranch' and csdt='$xrow1[0]'; ";
      }else{
        $xpsql = "insert into tdaily_closing_bal (loccd, csdt, closing_bal_amt) values ('$xbranch', '$xrow1[0]', '$xcur_bal4_save');";
      }
      if ($debug)
        echo "$xpsql<br/>\n";
      else
        pg_exec($db, $xpsql);

      $xpsql="update tdaily_cash set flag='1' where flag='0' and loccd='$xbranch' and csdt='$xrow1[0]'; ";
      if ($debug)
        echo "$xpsql<br/>\n";
      else
        pg_exec($db, $xpsql);


//       $xpsql="update tdaily_cash_delete set del_flag='1' where del_flag='0'; ";
//       if ($debug)
//         echo "$xpsql<br/>\n";
//       else
//         pg_exec($db, $xpsql);
    }//end for
  }

function fcheck_updatecontra4($xtrxno,$opnm) {
  // $xmode 0 --> vinv  1 --> vcb
  global $db, $debug;
    if ($debug) echo "<b>fcheck_updatecontra4($xtrxno,$opnm)</b><br/>\n";
      $xsql3 = "select paytype1,paytype2,paytype3,paytype4,paytype5,paynote1,paynote2,paynote3,paynote4,paynote5 from mspayment where trcd='$xtrxno' or trivcd='$xtrxno' limit 1;";
      if ($debug) echo $xsql3.'<br>';
      //else
      $xres3 =pg_query($db, $xsql3);

      if (pg_num_rows($xres3)>0){
          $xrow3 = pg_fetch_row($xres3, 0);
          $paytype1=$xrow3[0];
          $paytype2=$xrow3[1];
          $paytype3=$xrow3[2];
          $paytype4=$xrow3[3];
          $paytype5=$xrow3[4];
          $paynote1=$xrow3[5];
          $paynote2=$xrow3[6];
          $paynote3=$xrow3[7];
          $paynote4=$xrow3[8];
          $paynote5=$xrow3[9];

        if($paytype1=="CHK" || $paytype1=="CHKCURR"){
            $arr_chk = split('[,+]',$paynote1);
            if(count($arr_chk)<1)fdo_updatecontra($paynote1,"C",$xtrxno,$opnm);
            else
            for ($i=0;$i< count($arr_chk);$i++) {
              fdo_updatecontra($arr_chk[$i],"C",$xtrxno,$opnm);
            }
        }else if($paytype2=="CHK" || $paytype2=="CHKCURR"){
            $arr_chk = split('[,+]',$paynote2);
            if(count($arr_chk)<1)fdo_updatecontra($paynote2,"C",$xtrxno,$opnm,$opnm);
            else
            for ($i=0;$i< count($arr_chk);$i++) {
              fdo_updatecontra($arr_chk[$i],"C",$xtrxno,$opnm);
            }
        }else if($paytype3=="CHK" || $paytype3=="CHKCURR"){
            $arr_chk = split('[,+]',$paynote3);
            if(count($arr_chk)<1)fdo_updatecontra($paynote3,"C",$xtrxno,$opnm);
            else
            for ($i=0;$i< count($arr_chk);$i++) {
              fdo_updatecontra($arr_chk[$i],"C",$xtrxno,$opnm);
            }
        }else if($paytype4=="CHK" || $paytype4=="CHKCURR"){
            $arr_chk = split('[,+]',$paynote4);
            if(count($arr_chk)<1)fdo_updatecontra($paynote4,"C",$xtrxno,$opnm);
            else
            for ($i=0;$i< count($arr_chk);$i++) {
              fdo_updatecontra($arr_chk[$i],"C",$xtrxno,$opnm);
            }
        }else if($paytype5=="CHK" || $paytype5=="CHKCURR"){
            $arr_chk = split('[,+]',$paynote5);
            if(count($arr_chk)<1)fdo_updatecontra($paynote5,"C",$xtrxno,$opnm);
            else
            for ($i=0;$i< count($arr_chk);$i++) {
              fdo_updatecontra($arr_chk[$i],"C",$xtrxno,$opnm);
            }
        }

       if($paytype1=="VOU" || $paytype1=="VOUCURR"){
            $arr_chk = split('[,+]',$paynote1);
            if(count($arr_chk)<1)fdo_updatecontra($paynote1,"V",$xtrxno,$opnm);
            else
            for ($i=0;$i< count($arr_chk);$i++) {
              fdo_updatecontra($arr_chk[$i],"V",$xtrxno,$opnm);
            }
        }else if($paytype2=="VOU" || $paytype2=="VOUCURR"){
            $arr_chk = split('[,+]',$paynote2);
            if(count($arr_chk)<1)fdo_updatecontra($paynote2,"V",$xtrxno,$opnm);
            else
            for ($i=0;$i< count($arr_chk);$i++) {
              fdo_updatecontra($arr_chk[$i],"V",$xtrxno,$opnm);
            }
        }else if($paytype3=="VOU" || $paytype3=="VOUCURR"){
            $arr_chk = split('[,+]',$paynote3);
            if(count($arr_chk)<1)fdo_updatecontra($paynote3,"V",$xtrxno,$opnm);
            else
            for ($i=0;$i< count($arr_chk);$i++) {
              fdo_updatecontra($arr_chk[$i],"V",$xtrxno,$opnm);
            }
        }else if($paytype4=="VOU" || $paytype4=="VOUCURR"){
            $arr_chk = split('[,+]',$paynote4);
            if(count($arr_chk)<1)fdo_updatecontra($paynote4,"V",$xtrxno,$opnm);
            else
            for ($i=0;$i< count($arr_chk);$i++) {
              fdo_updatecontra($arr_chk[$i],"V",$xtrxno,$opnm);
            }
        }else if($paytype5=="VOU" || $paytype5=="VOUCURR"){
            $arr_chk = split('[,+]',$paynote5);
            if(count($arr_chk)<1)fdo_updatecontra($paynote5,"V",$xtrxno,$opnm);
            else
            for ($i=0;$i< count($arr_chk);$i++) {
              fdo_updatecontra($arr_chk[$i],"V",$xtrxno,$opnm);
            }
        }

      }
      pg_free_result($xres3);
}

  function fget_br_monthly_stock4($xloccd, $xprdcd, $xdate) {
    global $db;
    if ($debug)
      echo "fget_br_monthly_stock4($xloccd, $xprdcd, $xdate);<br/>\n";
    $xhasil=0;
      $xdate1  = $xdate;
      //$xdate1  = "01/01/2012";

    $xsql1="select  fstock from inloc_monthly where loccd='$xloccd' and prdcd='$xprdcd' and fdate='$xdate' ";
    $xres1=pg_query($db, $xsql1);

    if (pg_num_rows($xres1)>0) {
      $xrow1=pg_fetch_row($xres1, 0);
      $xhasil=$xrow1[0];
    }else{
      $hOPEN  =fget_br_data_item_opening($xloccd, $xprdcd, $xdate1);

      $xdate2  = $xdate;
//       echo "$xdate1 $xdate2 <br/>\n";
//       $hCB  =fget_st_data_CB_item($xloccd,$xprdcd,$xdate1,$xdate2, 0);
//       $hCR  =fget_st_data_CR_item($xloccd,$xprdcd,$xdate1,$xdate2, 0);
//       $hINV =fget_st_data_INV_item($xloccd,$xprdcd,$xdate1,$xdate2, 0);
//       $hREC =fget_st_data_receive_item($xloccd, $xprdcd,$xdate1,$xdate2, 0);
//       $hADJ =fget_st_data_ADJ_item($xloccd,$xprdcd,$xdate1,$xdate2, 0);
//       $hFOC =fget_st_data_FOC_item($xloccd,$xprdcd,$xdate1,$xdate2, 0);
//
//       $xhasil  = $hOPEN+($hREC+$hADJ)-$hCB-$hCR-$hINV-$hFOC;


          $hDO    =fget_cb_data_DO_item($xloccd,$xprdcd,$xdate1,$xdate2,0);
          $hTRout =fget_cb_data_TR_item($xloccd,$xprdcd,$xdate1,$xdate2,0,'out');
          $hTRin  =fget_cb_data_TR_item($xloccd,$xprdcd,$xdate1,$xdate2,0,'in');
          $hADJ   =fget_cb_data_ADJ_item($xloccd,$xprdcd,$xdate1,$xdate2,0);
          $hCB    =fget_cb_data_CB_item($xloccd,$xprdcd,$xdate1,$xdate2,0);
          $hCBIOC =fget_cb_data_CBIOC_item($xloccd,$xprdcd,$xdate1,$xdate2,0);
          $hFOC   =fget_cb_data_FOC_item($xloccd,$xprdcd,$xdate1,$xdate2,0);
          $hCRall =fget_cb_data_CR_item($xloccd,$xprdcd,$xdate1,$xdate2,0);
          $hINV   =fget_cb_data_INV_item($xloccd,$xprdcd,$xdate1,$xdate2,0,'out');
          $hRETURN=fget_cb_data_INV_item($xloccd,$xprdcd,$xdate1,$xdate2,0,'in');
          $hRETURN-=fget_cb_data_CNSC_item($xloccd,$xprdcd,$xdate1,$xdate2,0);

          $hGRN   =fget_cb_data_GRN_item($xloccd,$xprdcd,$xdate1,$xdate2,0,'all');
          $hREP   =fget_cb_data_REP_item($xloccd,$xprdcd,$xdate1,$xdate2,0);

          //if ($prdcode=="1001.1") print "$qCB<br>$qINV<br>$qFOC<br>$qCR";
          /*  CLOSING  */

          //$hCLOSE= ($hOPEN+$hDO+$hADJ)-($hCB+$hFOC+$hSP+$hCR+$hINV+$hRETURN+$hGRN);
          $xhasil= ($hOPEN+$hDO+$hADJ+$hTRin-$hTRout)-($hCB+$hCBIOC+$hFOC+$hCRall+$hINV+$hRETURN+$hGRN+$hREP);

      $xarr_data=array(
        'loccd' =>$xloccd,
        'prdcd' =>$xprdcd,
        'fdate' =>$xdate,
        'fstock'=>$xhasil,
        'factiondate'=>'now()',
      );
      db_insert('inloc_monthly', $xarr_data);
    }

//       if ($debug) echo "$xhasil  = $hOPEN+($hREC+$hADJ)-$hCB-$hCR-$hINV-$hFOC; <br/>\n";
    return $xhasil;
  }

  function formatSizeUnits($bytes)
  {
    if ($bytes >= 1073741824)
    {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    }
    elseif ($bytes >= 1048576)
    {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    }
    elseif ($bytes >= 1024)
    {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    }
    elseif ($bytes > 1)
    {
        $bytes = $bytes . ' Bytes';
    }
    elseif ($bytes == 1)
    {
        $bytes = $bytes . ' Byte';
    }
    else
    {
        $bytes = '0 Byte';
    }

    return $bytes;
  }

  function fget_balance_redemp_member($xcode, $xprdcd, $xloccd='') {
    $xresult=0;
    $xsql1  =
      "select sum(fdebet)-sum(fkredit) as balance from (
        select 0 as fdebet, sum(a.ioc_advpay) as fkredit
        from newmstrh_ioc a
        where a.trtype='13' and a.code='$xcode' and a.note2='$xprdcd' and a.loccd='$xloccd'
        union all
        select 0 as fdebet, sum(a.ioc_advpay) as fkredit
        from newsctrh_ioc a
        where a.trtype='13' and a.code='$xcode' and a.note2='$xprdcd' and a.loccd='$xloccd'
      ) as table1; ";

    $xrow1=fpg_exec4($xsql1);
    return $xrow1[0];
  }

  function fget_balance_member($xcode, $xprdcd, $xloccd='') {
    global $debug;
    $xresult=0;
    $xsql1  =
      "select sum(fdebet)-sum(fkredit) as balance from (
        select sum(b.qty*b.dp) as fdebet, 0 as fkredit
        from newmstrh_ioc a, newmstrd_ioc b
        where a.trcd=b.trcd and a.trtype='12'
        and a.code='$xcode' and b.prdcd='$xprdcd' and a.loccd='$xloccd'
        union all
        select 0 as fdebet, sum(a.ioc_advpay) as fkredit
        from newmstrh_ioc a
        where a.trtype='13' and a.code='$xcode' and a.note2='$xprdcd' and a.loccd='$xloccd'
        union all
        select sum(b.qty*b.dp) as fdebet, 0 as fkredit
        from newsctrh_ioc a, newsctrd_ioc b
        where a.trcd=b.trcd and a.trtype='12'
        and a.code='$xcode' and b.prdcd='$xprdcd' and a.loccd='$xloccd'
        union all
        select 0 as fdebet, sum(a.ioc_advpay) as fkredit
        from newsctrh_ioc a
        where a.trtype='13' and a.code='$xcode' and a.note2='$xprdcd' and a.loccd='$xloccd'

      ) as table1 ";
    //echo "$xsql1<br/>\n";
    $xrow1=fpg_exec4($xsql1);
    $xrow2=fpg_exec4("select balance from balance_ioc where code='$xcode' and prdcd='$xprdcd' and loccd='$xloccd'");

    if  ($xrow1[0]!=$xrow2[0]) {
      $xarr_where1['code'] =$xcode;
      $xarr_where1['prdcd']=$xprdcd;
      $xarr_where1['loccd']=$xloccd;
      if ($xtype=='plus')
        $xarr_data_balance['balance']=$xrow1[0];
      else
        $xarr_data_balance['balance']=$xrow1[0];
      db_update2('balance_ioc', $xarr_data_balance, $xarr_where1);
    }

    if ($xrow1[0]=='')
      $xresult=0;
    else
      $xresult=$xrow1[0];
    if ($debug)
      echo "$xresult=fget_balance_member($xcode, $xprdcd, $xloccd);<br/>\n";

    return $xresult;
  }

  function update_balance_ioc($xcode, $xprdcd, $xtotal, $xloccd, $xtype='plus') {
    global $default_cnt;
    //echo "$xtotal<br/><br/>";
    $xrow1=db_select_record('balance', 'balance_ioc', "where loccd='$xloccd' and code='$xcode' and prdcd='$xprdcd' ");
    if ($xrow1['balance']=='') {
      $xarr_data_balance = array (
        'code'   =>$xcode,
        'prdcd'  =>$xprdcd,
        'pcn_id' =>$default_cnt,
        'balance'=>$xtotal,
        'loccd'  =>$xloccd,
      );
      db_insert('balance_ioc', $xarr_data_balance);
    }else{
      $xarr_where1['code'] =$xcode;
      $xarr_where1['prdcd']=$xprdcd;
      $xarr_where1['loccd']=$xloccd;
      if ($xtype=='plus')
        $xarr_data_balance['balance']=$xrow1['balance']+$xtotal;
      else
        $xarr_data_balance['balance']=$xrow1['balance']-$xtotal;

      db_update2('balance_ioc', $xarr_data_balance, $xarr_where1);

    }
  }

  function fregenerate_msmemb_ioc($xcode) {
    global $db, $debug;
    $xsql_msmembioc = "select icode, packno from msmemb_ioc where code='$xcode' order by icode ";
    $xres_msmembioc = pg_exec($db, $xsql_msmembioc);
    if (pg_num_rows($xres_msmembioc)>0) {
      for ($xi=0; $xi<pg_num_rows($xres_msmembioc); $xi++) {
        $xrow_msmembioc=pg_fetch_assoc($xres_msmembioc, $xi);
        $xpsql="update msmemb_ioc set packno='".($xi+1)."' where icode='".$xrow_msmembioc['icode']."';";
        if ($debug)
          echo "$xpsql<br/>\n";
        else
          pg_exec($db, $xpsql);
      }
    }else{
      $xpsql="delete from msmemb_pinno_ioc where code='$xcode'";
      if ($debug)
        echo "$xpsql<br/>\n";
      else
        pg_exec($db, $xpsql);
    }
  }

  function secondsToTime($inputSeconds) {
    $secondsInAMinute = 60;
    $secondsInAnHour  = 60 * $secondsInAMinute;
    $secondsInADay    = 24 * $secondsInAnHour;

    // extract days
    $days = floor($inputSeconds / $secondsInADay);

    // extract hours
    $hourSeconds = $inputSeconds % $secondsInADay;
    $hours = floor($hourSeconds / $secondsInAnHour);

    // extract minutes
    $minuteSeconds = $hourSeconds % $secondsInAnHour;
    $minutes = floor($minuteSeconds / $secondsInAMinute);

    // extract the remaining seconds
    $remainingSeconds = $minuteSeconds % $secondsInAMinute;
    $seconds = ceil($remainingSeconds);

    // return the final array
    $obj = array(
        'd' => (int) $days,
        'h' => (int) $hours,
        'm' => (int) $minutes,
        's' => (int) $seconds,
    );
    return $obj;
  }


  function fget_balance_ioc_voucher($xcode) {
    //global $debug;
    $xresult=0;
    $xsql1  =
      "select sum(fdebet)-sum(fkredit) as balance from (
        select sum(b.iocvcramt) as fdebet, 0 as fkredit
        from newmstrh_ioc a, ioc_voucher b
        where a.trcd=b.trcd
        and a.code='$xcode'
        union all
        select sum(b.iocvcramt) as fdebet, 0 as fkredit
        from newsctrh_ioc a, ioc_voucher b
        where a.trcd=b.trcd
        and a.code='$xcode'
        union all
        select 0 as fdebet, sum(a.ioc_voucher_amt) as fkredit
        from newmstrh a
        where a.code='$xcode' and ioc_voucher_amt>0
        union all
        select 0 as fdebet, sum(a.ioc_voucher_amt) as fkredit
        from newsctrh a
        where a.code='$xcode' and ioc_voucher_amt>0
      ) as table1 ";
    //echo "$xsql1<br/>\n";
    $xrow1=fpg_exec4($xsql1);
    $xrow2=fpg_exec4("select balance from ioc_voucher_balance where code='$xcode' ");

    if  ($xrow1[0]!=$xrow2[0]) {
      $xarr_where1['code'] =$xcode;
      if ($xtype=='plus')
        $xarr_data_balance['balance']=$xrow1[0];
      else
        $xarr_data_balance['balance']=$xrow1[0];
      db_update2('ioc_voucher_balance', $xarr_data_balance, $xarr_where1);
    }

    if ($xrow1[0]=='')
      $xresult=0;
    else
      $xresult=$xrow1[0];
    if ($debug)
      echo "$xresult=fget_balance_ioc_voucher($xcode);<br/>\n";

    return $xresult;
  }

  function fupdate_ioc_vcr_balance($xcode, $xamount){
    global $db, $debug;
    if ($debug) echo "<b>fupdate_ioc_vcr_balance($xcode, $xamount)</b><br/>\n";
    $xsql="select code from ioc_voucher_balance where code='$xcode'";
    if ($debug)
      echo "$xsql<br/>\n";
    $xres=pg_query($db, $xsql);

    if (pg_num_rows($xres)>0) {
      $xpsql="update ioc_voucher_balance set balance=balance+(".$xamount.") where code='$xcode' ";
      if ($debug) echo "$xpsql";
      pg_query($db, $xpsql);
    }else{
      $xarr_data = array (
        'code'  => $xcode,
        'balance' => $xamount
      );
      db_insert('ioc_voucher_balance', $xarr_data);
    }
  }

  function fgst_amount_disc($xpdisc) {
    global $xdisc_vat, $xdisc_vat_set, $xvat_txt;

    $xamount_disc_incl = 0;
    $xamount_disc_vat  = 0;
    $xamount_disc_excl = 0;

    if ($xdisc_vat=='t') {
      if ($xdisc_vat_set=='t'){
        // disc inclusive
        $xamount_disc_incl = $xpdisc;
        $xamount_disc_vat  = $xamount_disc_incl*($xvat_txt/(100+$xvat_txt));
        $xamount_disc_excl = $xamount_disc_incl - $xamount_disc_vat;
      }else{
        $xamount_disc_excl = $xpdisc;
        $xamount_disc_vat  = $xamount_disc_excl*$xvat_txt/100;
        $xamount_disc_incl = $xamount_disc_excl + $xamount_disc_vat;
      }
    }//end if
    $xarr_result['amount_disc_incl'] = $xamount_disc_incl;
    $xarr_result['amount_disc_vat']  = $xamount_disc_vat;
    $xarr_result['amount_disc_excl'] = $xamount_disc_excl;

    return $xarr_result;
  }

  function fgst_amount_shcost($xshcost) {
    global $xvat_snh, $xvat_txt, $debug;
    if ($debug) {
      echo "$xvat_snh <br/>\n";
    }
    $xamount_shcost_incl = 0;
    $xamount_shcost_vat  = 0;
    $xamount_shcost_excl = 0;

    if ($xvat_snh=='t'){
      // disc inclusive
      $xamount_shcost_incl = $xshcost;
      $xamount_shcost_vat  = $xamount_shcost_incl*($xvat_txt/(100+$xvat_txt));
      $xamount_shcost_vat  = round($xamount_shcost_vat,2);
      $xamount_shcost_excl = $xamount_shcost_incl - $xamount_shcost_vat;
    }else{
      $xamount_shcost_excl = $xshcost;
      $xamount_shcost_vat  = $xamount_shcost_excl*$xvat_txt/100;
      $xamount_shcost_vat  = round($xamount_shcost_vat,2);
      $xamount_shcost_incl = $xamount_shcost_excl + $xamount_shcost_vat;
    }
    $xarr_result['amount_shcost_incl'] = $xamount_shcost_incl;
    $xarr_result['amount_shcost_vat']  = $xamount_shcost_vat;
    $xarr_result['amount_shcost_excl'] = $xamount_shcost_excl;
    return $xarr_result;
  }

function fget_header_cn_db() {
  global $db, $xuse_vat, $_xtaxid, $xarr_mssc_extra, $debug, $default_cnt, $xseller_has_tax;
  $xrow=db_select_record('*', 'company',"where country_id='$default_cnt'");
//   print_r($xrow);
  echo "<br/>\n";
    echo "<span style=\"font-family: 'Times New Roman', Georgia, Serif;font-size: 20px;font-weight: bold;\">DXN MARKETING SDN. BHD.</span><span>(Company No.:".$xrow['name_no'].")</span><br>
  ".$xrow['address'].",<BR>
  ".$xrow['pobox']." ".$xrow['town'].", ".$xrow['address1'].", MALAYSIA<BR>
  TEL: ".$xrow['phone1']."/".$xrow['phone2']." &nbsp;&nbsp; FAX: ".$xrow['fax']."<BR>";
  if ($xseller_has_tax=='t')
    echo "<b>(GST ID NO.:".$xrow['tax_id'].")</b>";
}

function fget_header_cn_branch() {
  global $db, $xuse_vat, $_xtaxid, $xarr_mssc_extra, $debug, $default_cnt,$xseller_has_tax;
//  $xrow=db_select_record('*', 'company',"where country_id='$default_cnt'");
//   print_r($xrow);
?>
  <br/>
  <span style="font-family: 'Times New Roman', Georgia, Serif;font-size: 20px;font-weight: bold;">DXN MARKETING SDN. BHD.</span>
  <span>(Company No.:283904-P)</span><BR/>
  No. 43, JALAN SS 22/23,<BR/>
  DAMANSARA JAYA, 47400 PETALING JAYA, SELANGOR<BR/>
  TEL: 603-77253388/ &nbsp;&nbsp; FAX: 603-77251188<BR/>
<?
  if ($xseller_has_tax=='t')
    echo "<b>(GST ID NO.:001662255104)</b>\n";
}

function fget_header_cn_sc_db($xloccd, $xshow_gst=1) {
  global $db, $debug;
  echo "<br/>\n";

    $reslt=pg_query($db,"select a.cbheader, a.col_id, b.tax_id from sub_mssc a, sub_mssc_extra b where a.code=b.scname and a.code='$xloccd';");
    if (pg_num_rows($reslt)>0) {
      $rowhd=pg_fetch_assoc($reslt,0);
      $infosc = explode("_",$rowhd['cbheader']);
//       if ($debug) print_r($infosc);

        $alamat = "$infosc[0]<br/>";
        if ($infosc[8]!='') $alamat.= "$infosc[8] <br/>";
        if ($infosc[9]!='') $alamat.= "$infosc[9] ";

        if ($rowhd['col_id']=='65'){
          $xaddr[0]=$infosc[2];
        }else{
          //$xssql="select st_name, cn_id from state where st_id='$infosc[2]'";
          $xssql="
            select a.st_name, a.cn_id, b.name from state a
            left join country_list b on b.iso=a.cn_id
            where a.st_id='$infosc[2]' ";

//           if ($debug) echo "$xssql <br/>\n";
          $xaddr=fpg_exec($xssql);
        }
        $alamat.= "$infosc[3] $infosc[1], ";
        if ($xaddr[0]!='') $alamat.= $xaddr[0];
        if ($xaddr[1]!='') $alamat.= ", ".$xaddr[2];
        $alamat=strtoupper($alamat);
        $xphone_office=($infosc[6]!='')?$infosc[6]:'-';
        $xfax_office=($infosc[7]!='')?$infosc[7]:'-';
        if ($xphone_office!='-' && $xfax_office!='-') {
          $alamat.= "<br/>";
          $alamat.= mxlang("622","")." : $xphone_office, ".mxlang("991","")." : $xfax_office";
        }
        $xhome_phone=($infosc[4]!='')?$infosc[4]:'-';
        $xcell_phone=($infosc[5]!='')?$infosc[5]:'-';
        if ($xhome_phone!='-' && $xcell_phone!='-') {
          $alamat.= "<br/>";
          $alamat.= mxlang("1513","")." : $xhome_phone, ".mxlang("1514","")." : $xcell_phone";
        }
      //}
      //echo "<b>".$xloccd." ".get_memname($xloccd)."</b>&nbsp;<br/>$alamat"."&nbsp;<br/>";
      //if ($rowhd['tax_id']!='') {
        //echo "GST ID No. ".$rowhd['tax_id']."&nbsp;<br/>";
      //}//end if

      echo "<span style=\"font-family: 'Times New Roman', Georgia, Serif;font-size: 20px;font-weight: bold;\">$xloccd ".get_memname($xloccd)."</span><br>";
      echo "$alamat"."&nbsp;<br/>";
      if ($rowhd['tax_id']!='' && $xshow_gst==1) {
        echo "<b>(GST ID No. ".$rowhd['tax_id'].")</b>";
      }//end if
    }
}

function ship_to_cn($pshiphead,$code, $xname='', $xtrtype=1){
  //global $opnm;
  global $debug, $opnm;
	$result="";
	$shiphead=str_replace("\r\n","|",trim($pshiphead));
	$shiphead=str_replace('\r\n',"|",$shiphead);
  $shiphead=str_replace("\n","|",$shiphead);
  $shiphead=stripslashes($shiphead);
// 	if ($opnm=='hima') echo "<i>".$pshiphead."</i><br>";
	$shiparr = preg_split ("/[\|]/", $shiphead);
// 	if ($debug) print_r($shiparr);
	$i=1;
	foreach ($shiparr as $itemShip) {
//     if ($opnm=='hima') echo " $i <b>$itemShip</b>*<br/>";
		if($i==1){
			if ($code=='*' && $xname!='')  {
        $result.=$xname."<br/>";
      }else{
        $result.=$itemShip." (".$code.")<br/>";
      }
		}else{
      if (trim(str_replace('<br/>','',$itemShip))!='')
        $result.=$itemShip."<br/>";
		}
		$i++;
	}

	return $result;
}

  function sel_stockist2($xstr_name,$xobj_name, $xtype=2) {
    // 0 --> main stockist
    // 1 --> stockist
    // 2 --> main or stockist
    // 3 --> display --All-- option
    global $db,$priv,$opnm,$debug;

    if ($xtype=='MS') $xtype="0";
    if ($xtype=='SC') $xtype="1";
    if ($xtype=='ALL') $xtype="-1";

    $xsql="select sccode from users_extra where uname='$opnm'";
    $xres=pg_query($db, $xsql);
    $xrow=pg_fetch_row($xres,0);
    if ($xrow[0]=='1') {
      echo "<select name='$xstr_name' id='$xstr_name'> \n";
  //    if ($xtype==3) echo "<option value='x'>--".mxlang("934","")."--</option>\n";
      $xsql_st ="select a.code,a.sub_name,c.name
        from sub_mssc a
        left join msmemb c on a.code=c.code
        inner join sub_mssc_extra b on b.scname=a.code ";
      if ($xtype==0 || $xtype==1) $xsql_st.="where b.stockist='$xtype' and a.status='N' ";
      $xsql_st.="order by a.sub_name";
      $xres_st=pg_query($db, $xsql_st);
      for ($i=0;$i<pg_num_rows($xres_st);$i++) {
        $xrow_st=pg_fetch_row($xres_st, $i);
        $xsel = ($xobj_name==$xrow_st[0])?'selected':'';
        echo "<option value='$xrow_st[0]' $xsel>$xrow_st[1]-".stripslashes($xrow_st[2])."</option>\n";
      }
      echo "</select> \n";
    }else{
      echo "<select name='$xstr_name' id='$xstr_name'> \n";
      $xsql_st ="select a.code,a.sub_name,c.name from sub_mssc a left join msmemb c on a.code=c.code where a.code='$xrow[0]'";
      $xsql_st.="order by a.sub_name";
      $xres_st=pg_query($db, $xsql_st);
      for ($i=0;$i<pg_num_rows($xres_st);$i++) {
        $xrow_st=pg_fetch_row($xres_st, $i);
        $xsel = ($xobj_name==$xrow_st[0])?'selected':'';
        echo "<option value='$xrow_st[0]' $xsel>$xrow_st[1]-".stripslashes($xrow_st[2])."</option>\n";
      }
      echo "</select> \n";
    }
//     echo "$xsql_st";
  }

  function fwrite_format($xtext, $xcolor='', $xmode='') {
    $xresult=$xtext;
    if ($xmode=='()' && $xtext>0) {
      $xresult="($xresult)";
    }
    if ($xcolor!='') {
      $xresult="<font color=\"$xcolor\">$xresult</font>";
    }
    return $xresult;
  }

  function fwrite_format2($xtext, $xcolor='', $xmode='', $xnumber_format=true) {
    if ($xtext=='-0') $xtext=0;
//     if ($xtext>1000) echo "*$xtext*";
    if ($xnumber_format) $xresult=number_format($xtext,2,'.',',');
    else $xresult=$xtext;

    if ($xtext<0 && $xtext!='-0') {
      if ($xnumber_format) $xtext=number_format(-$xtext, 2);
      else $xtext=-$xtext;

      if ($xmode!='')
        $xresult=substr($xmode, 0, 1).$xtext.substr($xmode, 1, 1);
      if ($xcolor!='') {
        $xresult="<font color=\"$xcolor\">$xresult</font>";
      }
    }
    return $xresult;
  }

  function fupdate_temp_price_dbf($xregion, $xarr_data) {
    global $db, $xopnm, $debug;
    if ($debug) {
      print_r($xarr_data);
      echo "region=$xregion<br/>\n";
    }
    $xprdcd=trim($xarr_data['SITEM']);
    $xprdnm=preg_replace("/[^A-Z\'\-\ 0-9]/i",'',$xarr_data['DESC']);
    //$xprdnm=pg_escape_string(trim($xprdnm));
    $xslimit=$xarr_data['QTY'];
    $xmsic =trim($xarr_data['MSIC']);
    $xnote =trim($xarr_data['NOTE']);
    if ($xregion==1) {
      $xdp  =$xarr_data['PRICE_W'];
      $xcp  =$xarr_data['CPRICE_W'];
      $xsp  =$xarr_data['SPRICE_W'];
      $xpv  =$xarr_data['PV_W'];
      $xbv  =$xarr_data['SV_W'];
      $xdisc=$xarr_data['DISC_W'];
      $xstkp=$xarr_data['PRC_W_STK'];

      $xdp_excl  = $xarr_data['DPEXCL_W'];
      $xcp_excl  = $xarr_data['CPEXCL_W'];
      $xsp_excl  = $xarr_data['SPEXCL_W'];
      $xstp_excl = $xarr_data['STKEXCL_W'];
      $xdp_vat   = $xarr_data['DPVAT_W'];
      $xcp_vat   = $xarr_data['CPVAT_W'];
      $xsp_vat   = $xarr_data['SPVAT_W'];
      $xstp_vat  = $xarr_data['STKVAT_W'];
    }else{
			//echo "i am here";
      $xdp  =$xarr_data['PRICE_E'];
      $xcp  =$xarr_data['CPRICE_E'];
      $xsp  =$xarr_data['SPRICE_E'];
      $xpv  =$xarr_data['PV_E'];
      $xbv  =$xarr_data['SV_E'];
      $xdisc=$xarr_data['DISC_E'];
      $xstkp=$xarr_data['PRC_E_STK'];
      $xdp_excl  = $xarr_data['DPEXCL_E'];
      $xcp_excl  = $xarr_data['CPEXCL_E'];
      $xsp_excl  = $xarr_data['SPEXCL_E'];
      $xstp_excl = $xarr_data['STKEXCL_E'];
      $xdp_vat   = $xarr_data['DPVAT_E'];
      $xcp_vat   = $xarr_data['CPVAT_E'];
      $xsp_vat   = $xarr_data['SPVAT_E'];
      $xstp_vat  = $xarr_data['STKVAT_E'];
    }
    $xslimit = $xarr_data['QTY'];
		if ($debug) {
      echo "SPRICE_E={$xarr_data['SPRICE_E']}<br/>\n";
			echo "xsp=$xsp<br/>\n";
		}
    if (trim($xdp)=='') $xdp='null';
    if (trim($xcp)=='') $xcp='null';
    if (trim($xsp)=='') $xsp='null';
    if (trim($xpv)=='') $xpv='null';
    if (trim($xbv)=='') $xbv='null';
    if (trim($xdisc)=='') $xdisc='null';
    if (trim($xstkp=='')) $xstkp='null';

    if (trim($xdp_excl)=='') $xdp_excl='null';
    if (trim($xcp_excl)=='') $xcp_excl='null';
    if (trim($xsp_excl)=='') $xsp_excl='null';
    if (trim($xstp_excl)=='') $xstp_excl='null';
    if (trim($xdp_vat)=='') $xdp_vat='null';
    if (trim($xcp_vat)=='') $xcp_vat='null';
    if (trim($xsp_vat)=='') $xsp_vat='null';
    if (trim($xstp_vat)=='') $xstp_vat='null';
    if (trim($xslimit)=='') $xslimit='null';

    $xarr_tmp_msprd=array(
      'prdcd'=>$xprdcd,
      'prdnm'=>$xprdnm,
      'dp'   =>$xdp,
      'cp'   =>$xcp,
      'sp'   =>$xsp,

      'pv'   =>$xpv,
      'bv'   =>$xbv,
      'opnm' =>$xopnm,
      'disc' =>$xdisc,
      'stp'  =>$xstkp,

      'staff_limit'=>$xslimit,
      'dp_excl' => $xdp_excl,
      'cp_excl' => $xcp_excl,
      'sp_excl' => $xsp_excl,
      'stp_excl'=> $xstp_excl,

      'dp_vat' => $xdp_vat,
      'cp_vat' => $xcp_vat,
      'sp_vat' => $xsp_vat,
      'stp_vat'=> $xstp_vat,
      'msic_code'   => $xmsic,
      'note'   => $xnote,
    );
    if ($debug) echo "xprdnm=$xprdnm<br/>\n";
    db_insert('tmp_msprd', $xarr_tmp_msprd);

  }

  function fupdate_temp_price_csv($xregion, $xarr_data) {
    global $db, $xopnm, $debug;
    if ($debug) {
      print_r($xarr_data);
      echo "$xregion<br/>\n";
    }

    $xprdcd=trim($xarr_data['SITEM']);
    $xprdnm=preg_replace("/[^A-Z\ 0-9]/i",'',$xarr_data['DESC']);
    $xprdnm=pg_escape_string(trim($xprdnm));
    $xmsic =trim($xarr_data['MSIC']);
    if ($xregion==0) {
      $xdp  =$xarr_data['PRICE_E'];
      $xcp  =$xarr_data['CPRICE_E'];
      $xsp  =$xarr_data['SPRICE_E'];
      $xpv  =$xarr_data['PV_E'];
      $xbv  =$xarr_data['SV_E'];
      $xdisc=$xarr_data['DISC_E'];
      $xstkp=$xarr_data['PRC_E_STK'];

      $xdp_excl  = $xarr_data['DPEXCL_E'];
      $xcp_excl  = $xarr_data['CPEXCL_E'];
      $xsp_excl  = $xarr_data['SPEXCL_E'];
      $xstp_excl = $xarr_data['STKEXCL_E'];
      $xdp_vat   = $xarr_data['DPVAT_E'];
      $xcp_vat   = $xarr_data['CPVAT_E'];
      $xsp_vat   = $xarr_data['SPVAT_E'];
      $xstp_vat  = $xarr_data['STKVAT_E'];

    }else{
      $xdp  =$xarr_data['PRICE_W'];
      $xcp  =$xarr_data['CPRICE_W'];
      $xsp  =$xarr_data['SPRICE_W'];
      $xpv  =$xarr_data['PV_W'];
      $xbv  =$xarr_data['SV_W'];
      $xdisc=$xarr_data['DISC_W'];
      $xstkp=$xarr_data['PRC_W_STK'];

      $xdp_excl  = $xarr_data['DPEXCL_W'];
      $xcp_excl  = $xarr_data['CPEXCL_W'];
      $xsp_excl  = $xarr_data['SPEXCL_W'];
      $xstp_excl = $xarr_data['STKEXCL_W'];
      $xdp_vat   = $xarr_data['DPVAT_W'];
      $xcp_vat   = $xarr_data['CPVAT_W'];
      $xsp_vat   = $xarr_data['SPVAT_W'];
      $xstp_vat  = $xarr_data['STKVAT_W'];
    }
    $xslimit = $xarr_data['QTY'];
    if (trim($xdp)=='') $xdp=0;
    if (trim($xcp)=='') $xcp=0;
    if (trim($xsp)=='') $xsp=0;
    if (trim($xpv)=='') $xpv=0;
    if (trim($xbv)=='') $xbv=0;
    if (trim($xdisc)=='') $xdisc=0;
    if (trim($xstkp=='')) $xstkp=0;

    if (trim($xdp_excl)=='') $xdp_excl=0;
    if (trim($xcp_excl)=='') $xcp_excl=0;
    if (trim($xsp_excl)=='') $xsp_excl=0;
    if (trim($xstp_excl)=='') $xstp_excl=0;
    if (trim($xdp_vat)=='') $xdp_vat=0;
    if (trim($xcp_vat)=='') $xcp_vat=0;
    if (trim($xsp_vat)=='') $xsp_vat=0;
    if (trim($xstp_vat)=='') $xstp_vat=0;

    if (trim($xslimit)=='') $xslimit=0;
//     if ($debug)
//       echo "limit=$xslimit<br/>";
//     $xpsql="insert into tmp_msprd ".
//       "(prdcd, prdnm, dp, cp, sp, ".
//       "pv, bv, opnm, disc, stp, staff_limit ) ".
//       "values ".
//       "('$xprdcd', '$xprdnm', $xdp, $xcp, $xsp, ".
//       "$xpv, $xbv, '$xopnm', $xdisc, $xstkp, $xslimit );";
//    if ($debug) echo "$xpsql<br/>";
    //pg_exec($db, $xpsql);

    $xarr_tmp_msprd=array(
      'prdcd'=>$xprdcd,
      'prdnm'=>pg_escape_string($xprdnm),
      'dp'   =>$xdp,
      'cp'   =>$xcp,
      'sp'   =>$xsp,

      'pv'   =>$xpv,
      'bv'   =>$xbv,
      'opnm' =>$xopnm,
      'disc' =>$xdisc,
      'stp'  =>$xstkp,

      'staff_limit'=>$xslimit,
      'dp_excl' => $xdp_excl,
      'cp_excl' => $xcp_excl,
      'sp_excl' => $xsp_excl,
      'stp_excl'=> $xstp_excl,

      'dp_vat' => $xdp_vat,
      'cp_vat' => $xcp_vat,
      'sp_vat' => $xsp_vat,
      'stp_vat'=> $xstp_vat,
      'msic_code'   => $xmsic,

    );
    db_insert('tmp_msprd', $xarr_tmp_msprd);
    //exit;
  }//end function

  function fupdate_temp_price_xls($xregion, $xlsdata) {
    global $db, $xopnm;
    $sheet= 0;
    $rowtitlestart=4;
    for($col=1;$col<=$xlsdata->colcount($sheet);$col++) {
      $val = $xlsdata->val($rowtitlestart,$col,$sheet);
      $val = strtoupper($val);
      $xarr_xls_title[$val]=$col;
    }
    //print_r($xarr_xls_title);


    for($row=$rowtitlestart+1;$row<=$xlsdata->rowcount($sheet);$row++) {
      $xprdcd=$xlsdata->val($row,$xarr_xls_title['SITEM'],$sheet);
      $xprdnm=$xlsdata->val($row,$xarr_xls_title['DESC'],$sheet);
      if ($xregion==0) {
        $xdp = preg_replace('/[^0-9\.]/','',$xlsdata->val($row,$xarr_xls_title['PRICE_E'],$sheet));
        $xcp = preg_replace('/[^0-9\.]/','',$xlsdata->val($row,$xarr_xls_title['CPRICE_E'],$sheet));
        $xsp = preg_replace('/[^0-9\.]/','',$xlsdata->val($row,$xarr_xls_title['SPRICE_E'],$sheet));

        $xpv  = preg_replace('/[^0-9\.]/','',$xlsdata->val($row,$xarr_xls_title['PV_E'],$sheet));
        $xbv  = preg_replace('/[^0-9\.]/','',$xlsdata->val($row,$xarr_xls_title['SV_E'],$sheet));
        $xdisc= preg_replace('/[^0-9\.]/','',$xlsdata->val($row,$xarr_xls_title['DISC_E'],$sheet));
        $xstkp= preg_replace('/[^0-9\.]/','',$xlsdata->val($row,$xarr_xls_title['PRC_E_STK'],$sheet));

        $xdp_excl = preg_replace('/[^0-9\.]/','',$xlsdata->val($row,$xarr_xls_title['DPEXCL_E'],$sheet));
        $xcp_excl = preg_replace('/[^0-9\.]/','',$xlsdata->val($row,$xarr_xls_title['CPEXCL_E'],$sheet));
        $xsp_excl = preg_replace('/[^0-9\.]/','',$xlsdata->val($row,$xarr_xls_title['SPEXCL_E'],$sheet));
        $xstp_excl= preg_replace('/[^0-9\.]/','',$xlsdata->val($row,$xarr_xls_title['STKEXCL_E'],$sheet));

        $xdp_vat  = preg_replace('/[^0-9\.]/','',$xlsdata->val($row,$xarr_xls_title['DPVAT_E'],$sheet));
        $xcp_vat  = preg_replace('/[^0-9\.]/','',$xlsdata->val($row,$xarr_xls_title['CPVAT_E'],$sheet));
        $xsp_vat  = preg_replace('/[^0-9\.]/','',$xlsdata->val($row,$xarr_xls_title['SPVAT_E'],$sheet));
        $xstp_vat = preg_replace('/[^0-9\.]/','',$xlsdata->val($row,$xarr_xls_title['STKVAT_E'],$sheet));

      }else{
        $xdp = preg_replace('/[^0-9\.]/','',$xlsdata->val($row,$xarr_xls_title['PRICE_W'],$sheet));
        $xcp = preg_replace('/[^0-9\.]/','',$xlsdata->val($row,$xarr_xls_title['CPRICE_W'],$sheet));
        $xsp = preg_replace('/[^0-9\.]/','',$xlsdata->val($row,$xarr_xls_title['SPRICE_W'],$sheet));

        $xpv  = preg_replace('/[^0-9\.]/','',$xlsdata->val($row,$xarr_xls_title['PV_W'],$sheet));
        $xbv  = preg_replace('/[^0-9\.]/','',$xlsdata->val($row,$xarr_xls_title['SV_W'],$sheet));
        $xdisc= preg_replace('/[^0-9\.]/','',$xlsdata->val($row,$xarr_xls_title['DISC_W'],$sheet));
        $xstkp= preg_replace('/[^0-9\.]/','',$xlsdata->val($row,$xarr_xls_title['PRC_W_STK'],$sheet));

        $xdp_excl = preg_replace('/[^0-9\.]/','',$xlsdata->val($row,$xarr_xls_title['DPEXCL_W'],$sheet));
        $xcp_excl = preg_replace('/[^0-9\.]/','',$xlsdata->val($row,$xarr_xls_title['CPEXCL_W'],$sheet));
        $xsp_excl = preg_replace('/[^0-9\.]/','',$xlsdata->val($row,$xarr_xls_title['SPEXCL_W'],$sheet));
        $xstp_excl= preg_replace('/[^0-9\.]/','',$xlsdata->val($row,$xarr_xls_title['STKEXCL_W'],$sheet));

        $xdp_vat  = preg_replace('/[^0-9\.]/','',$xlsdata->val($row,$xarr_xls_title['DPVAT_W'],$sheet));
        $xcp_vat  = preg_replace('/[^0-9\.]/','',$xlsdata->val($row,$xarr_xls_title['CPVAT_W'],$sheet));
        $xsp_vat  = preg_replace('/[^0-9\.]/','',$xlsdata->val($row,$xarr_xls_title['SPVAT_W'],$sheet));
        $xstp_vat = preg_replace('/[^0-9\.]/','',$xlsdata->val($row,$xarr_xls_title['STKVAT_W'],$sheet));
      }
//       $xslimit=0;
      if ($xdp=='') $xdp=0;
      if ($xcp=='') $xcp=0;
      if ($xsp=='') $xsp=0;
      if ($xpv=='') $xpv=0;
      if ($xbv=='') $xbv=0;
      if ($xdisc=='') $xdisc=0;
      if ($xstkp=='') $xstkp=0;

      if ($xdp_excl=='') $xdp_excl=0;
      if ($xcp_excl=='') $xcp_excl=0;
      if ($xsp_excl=='') $xsp_excl=0;
      if ($xstp_excl=='') $xstp_excl=0;

      if ($xdp_vat=='') $xdp_vat=0;
      if ($xcp_vat=='') $xcp_vat=0;
      if ($xsp_vat=='') $xsp_vat=0;
      if ($xstp_vat=='') $xstp_vat=0;

      if (trim($xprdcd)!='') {
        $xarr_tmp_msprd=array(
          'prdcd'=>$xprdcd,
          'prdnm'=>pg_escape_string($xprdnm),
          'dp'   =>$xdp,
          'cp'   =>$xcp,
          'sp'   =>$xsp,

          'pv'   =>$xpv,
          'bv'   =>$xbv,
          'opnm' =>$xopnm,
          'disc' =>$xdisc,
          'stp'  =>$xstkp,

          'staff_limit'=>$xslimit,
          'dp_excl' => $xdp_excl,
          'cp_excl' => $xcp_excl,
          'sp_excl' => $xsp_excl,
          'stp_excl'=> $xstp_excl,

          'dp_vat' => $xdp_vat,
          'cp_vat' => $xcp_vat,
          'sp_vat' => $xsp_vat,
          'stp_vat'=> $xstp_vat,
          'msic_code'   => $xmsic,

        );
        //print_r($xarr_tmp_msprd);
        //echo "<br/>\n";
        db_insert('tmp_msprd', $xarr_tmp_msprd);
      }//end if
    }
/*
    for($row=4;$row<=$xlsdata->rowcount($sheet);$row++) {
      for($col=1;$col<=$xlsdata->colcount($sheet);$col++) {
        $val = $xlsdata->val($row,$col,$sheet);
        echo "val($row,$col,$sheet) = $val <br/>\n";
      }
    }*/
  }//end function

  function fget_grn_trxno4($xtname, $xloccd) {
    global $db, $year1;
    $thismonth=$year1;
    $squery =
      "select nvalue from xtable_trh_month
      where tname='$xtname' and nstart='$thismonth' and loccd='$xloccd';";
    $dresult=pg_query($db,$squery);
    if (pg_numrows($dresult)>0) {
      list($newTransId)=pg_fetch_row($dresult,0);
      $newTransId=$newTransId+1;
      $xsql="update xtable_trh_month set nvalue=nvalue+1 where nstart='$thismonth' and loccd='$xloccd' and tname='$xtname';";
      if ($debug==1) echo "$xsql<br/>";
      $dresult=pg_query($db, $xsql);
    } else {
      $xsql="insert into xtable_trh_month values('$xtname','$thismonth',1,'$xloccd');";
      if ($debug==1) echo("$xsql<br/>");
      $dresult=pg_query($db, $xsql);
      $newTransId=1;
    }

    $digitcb='';
    for ($i=0;strlen($newTransId)<=5;$i++)
      $newTransId="0".$newTransId;

    $newTransId=$digitcb.part_date().$newTransId;
    return $newTransId;

  }

  function fposted_BRCB($xsql_where){
    global $db, $debug;
    $xpsql1="insert into newtrh
      (trcd,trtype,code,bc_id,upcode,trdt,etdt,loccd,tdp,tpv,tbv,pdisc,ptax,ndp,npv,nbv,totpay,note1,note2,note3,createnm,lupddt,lupdtm,opnm,send,flag,status,cbno,
      seller_has_tax, ship_vat, disc_vat, vat, seller_taxid,trtm,frmsys)
      select
      trcd,trtype,code,bc_id,upcode,trdt,etdt,loccd,tdp,tpv,tbv,pdisc,ptax,ndp,npv,nbv,totpay,note1,note2,note3,createnm,lupddt,lupdtm,opnm,send,flag,status,cbno,
      seller_has_tax, ship_vat, disc_vat, vat, seller_taxid,trtm,frmsys
      from newmstrh
      $xsql_where ";
    if ($debug==1) echo($xpsql1."<br>");
    $xresult=pg_query($db,$xpsql1);

    $xquerr="insert into newtrd
          (trcd,prdcd,qty,dp,cp,sp,pv,bv,dp_excl,cp_excl,sp_excl,unit_tax,vat,stp_excl)
          select
          trcd,prdcd,qty,dp,cp,sp,pv,bv,dp_excl,cp_excl,sp_excl,unit_tax,vat,stp_excl
      from newmstrd where trcd in (select trcd from newmstrh $xsql_where)";
    $xresult=pg_query($db,$xquerr);
    if ($debug==1) print $xquerr."<br>";

    $xquerr="insert into newpayment select * from mspayment where trcd in (select trcd from newmstrh $xsql_where)";

    $xresult=pg_query($db,$xquerr);
    if ($debug==1) print $xquerr."<br>";
  }

  function fposted_BRINV($xsql_where){
    global $db, $debug;
    $xpsql1="insert into newivtrh
      (trivcd,trtype,code,bc_id,upcode,trdt,etdt,loccd,tdp,tpv,tbv,pdisc,ptax,ndp,npv,nbv,totpay,note1,note2,note3,createnm,lupddt,lupdtm,opnm,send,flag,status,
      seller_has_tax, ship_vat, disc_vat, vat, seller_taxid, zero_rate)
      select
      trivcd,trtype,code,bc_id,upcode,trdt,etdt,loccd,tdp,tpv,tbv,pdisc,ptax,ndp,npv,nbv,totpay,note1,note2,note3,createnm,lupddt,lupdtm,opnm,send,flag,status,
      seller_has_tax, ship_vat, disc_vat, vat, seller_taxid, zero_rate
      from newmsivtrh
      $xsql_where ";
    if ($debug==1) echo($xpsql1."<br>");
    $xresult=pg_query($db,$xpsql1);

    $xquerr="insert into newivtrd
        (trivcd,prdcd,qty,dp,cp,sp,pv,bv,dp_excl,cp_excl,sp_excl,unit_tax,vat)
        select
        trivcd,prdcd,qty,dp,cp,sp,pv,bv,dp_excl,cp_excl,sp_excl,unit_tax,vat
      from newmsivtrd where trcd in (select trivcd from newmsivtrh $xsql_where)";
    $xresult=pg_query($db,$xquerr);
    if ($debug==1) print $xquerr."<br>";

    $xquerr="insert into newpayment select * from mspayment where trivcd in (select trivcd from newmsivtrh $xsql_where)";

    $xresult=pg_query($db,$xquerr);
    if ($debug==1) print $xquerr."<br>";
  }

  function fposted_SCCB($xsql_where){
    global $db, $debug;
    $xpsql1="insert into newtrh
      (trcd,trtype,code,bc_id,upcode,trdt,etdt,loccd,tdp,tpv,tbv,pdisc,ptax,ndp,npv,nbv,totpay,note1,note2,note3,createnm,lupddt,lupdtm,opnm,send,flag,status,cbno,
      seller_has_tax, ship_vat, disc_vat, vat, seller_taxid)
      select
      trcd,trtype,code,bc_id,upcode,trdt,etdt,loccd,tdp,tpv,tbv,pdisc,ptax,ndp,npv,nbv,totpay,note1,note2,note3,createnm,lupddt,lupdtm,opnm,send,flag,status,cbno,
      seller_has_tax, ship_vat, disc_vat, vat, seller_taxid
      from newsctrh
      $xsql_where ";
    if ($debug==1) echo($xpsql1."<br>");
    $xresult=pg_query($db,$xpsql1);

    $xquerr=
      "insert into newtrd
        (trcd,prdcd,qty,dp,cp,sp,pv,bv,dp_excl,cp_excl,sp_excl,unit_tax,vat,stp_excl)
      select
        trcd,prdcd,qty,dp,cp,sp,pv,bv,dp_excl,cp_excl,sp_excl,unit_tax,vat,stp_excl
      from newsctrd where trcd in (select trcd from newmstrh $xsql_where)";
    $xresult=pg_query($db,$xquerr);
    if ($debug==1) print $xquerr."<br>";

    $xquerr="insert into newpayment select * from scpayment where trcd in (select trcd from newsctrh $xsql_where)";

    $xresult=pg_query($db,$xquerr);
    if ($debug==1) print $xquerr."<br>";
  }

  function fsub_total3($xcalc_total=true, $xtypetrx='', $xbgcolor='#e6e6e6') {
    global $subtotCB, $subtotSP, $subtotINV, $subtotATIS,
      $subdiscCB, $subdiscINV, $subdiscSP, $subdiscATIS,
      $subtotCB1, $subtotSP1, $subtotINV1, $subtotATIS1,
      $subtotCB2, $subtotSP2, $subtotINV2, $subtotATIS2, $res_pay,
      $sub_tot_paytype_per_date, $debug;
    if ($debug) echo "fsub_total3($xcalc_total,$xtypetrx);<br/>\n";
?>
    <tr bgcolor="<?=$xbgcolor?>"  valign="top">
      <td  colspan="4" align="right">Total&nbsp;</td>
  <?
    $tot_sub_tot_paytype_per_date=0;
    for ($j=0;$j<pg_num_rows($res_pay);$j++) {
      echo("<td align=\"right\">".fwrite_format2($sub_tot_paytype_per_date[$j] , 'red', '()')."</td>\n");
      $tot_sub_tot_paytype_per_date+=$sub_tot_paytype_per_date[$j];
    }
  ?>
      <td  align="right"><?=number_format($total_disc,2)?></td>
      <td  align="right"><?
        //$xtot_date=$total_date+$total_date2+$total_date2+$total_disc;
  echo fwrite_format2($tot_sub_tot_paytype_per_date, 'red', '()');
  ?></td>
    </tr>
  <?
  }

  function fsub_total2($xcalc_total=true, $xtypetrx='',$colspan='4') {
    global $subtotCB, $subtotSP, $subtotINV, $subtotATIS,
      $subdiscCB, $subdiscINV, $subdiscSP, $subdiscATIS,
      $subtotCB1, $subtotSP1, $subtotINV1, $subtotATIS1,
      $subtotCB2, $subtotSP2, $subtotINV2, $subtotATIS2, $res_pay, $debug;
    if ($debug) echo "fsub_total2($xcalc_total,$xtypetrx);<br/>\n";
?>
    <tr bgcolor="#e6e6e6"  valign="top">
      <td  colspan="<?php echo($colspan);?>" align="right">Total&nbsp;</td>
  <?
    for ($j=0;$j<pg_num_rows($res_pay);$j++) {
      if ($xcalc_total)
        $xtotpay=$subtotCB[$j]+$subtotSP[$j]+$subtotINV[$j]+$subtotATIS[$j];
      else
        $xtotpay=0;

      echo("<td align=\"right\">".fwrite_format2($xtotpay , 'red', '()')."</td>\n");
      if ($xcalc_total){
        $total_date+=$subtotCB[$j]+$subtotSP[$j]+$subtotINV[$j]+$subtotATIS[$j];
        $total_disc+= $subdiscCB+$subdiscINV+$subdiscSP+$subdiscATIS;
      }
      $subdiscCB = 0;
      $subdiscINV = 0;
      $subdiscSP = 0;
      $subdiscATIS = 0;

      $subtotCB[$j]=0;
      $subtotSP[$j]=0;
      $subtotINV[$j]=0;
      $subtotATIS[$j]=0;
    }
    if ($xcalc_total) {
      $total_date1+=$subtotCB1[$j]+$subtotSP1[$j]+$subtotINV1[$j]+$subtotATIS1[$j];
      $total_date2+=$subtotCB2[$j]+$subtotSP2[$j]+$subtotINV2[$j]+$subtotATIS2[$j];
    }
    //echo("<td align=\"right\">".number_format($subtotCB1[$j]+$subtotSP1[$j]+
    //  $subtotINV1[$j]+$subtotATIS1[$j],2)."</td>\n");
    //echo("<td align=\"right\">".number_format($subtotCB2[$j]+$subtotSP2[$j]+$subtotINV2[$j]+$subtotATIS2[$j],2)."</td>\n");

      $subtotCB1[$j]=0;
      $subtotSP1[$j]=0;
      $subtotINV1[$j]=0;
      $subtotATIS1[$j]=0;
      $subtotCB2[$j]=0;
      $subtotSP2[$j]=0;
      $subtotINV2[$j]=0;
      $subtotATIS2[$j]=0;

  ?>
      <td  align="right"><?=number_format($total_disc,2)?></td>
      <td  align="right"><?
      //if ($xcalc_total) {
        if ($default_cnt=='ID')
          $xtot_date=$total_date+$total_date2+$total_date2;
        else
          $xtot_date=$total_date+$total_date2+$total_date2+$total_disc;
      //}
  echo fwrite_format2($xtot_date, 'red', '()');
  ?></td>
    </tr>
<?

  }


  function flist_scid_input($xfield_name, $txt_event='',$all='') {
    global $db, $opnm, $priv, $debug, $usrtype;
//     if ($debug) echo "flist_scid_input($xfield_name, $txt_event);<br/>\n";
    $xscsql ="SELECT a.sccode FROM users_extra a WHERE a.uname='$opnm'  ";
//     if ($debug) echo "$xscsql<br/>\n";
    $xscres =pg_query($db, $xscsql);
    if (pg_num_rows($xscres)>0)
      $xscrow = pg_fetch_row($xscres, 0);
    if ($xscrow[0]=='' && $priv!=3) {
  ?>
        <TR bgcolor="#FFFFFF">
          <TD align="left" width="25%">&nbsp;<?=mxlang("208","")?></TD>
          <TD align="left" colspan="3">
          <input name="<?=$xfield_name?>" id="<?=$xfield_name?>" type="text" size="13" maxlength="9" <?=$txt_event?>></TD>
        </TR>
  <?
    }else{
        ?>
        <tr bgcolor="#FFFFFF">
          <td align="left" >&nbsp;<?=mxlang("208","")?></td>
          <td align="left" colspan="3">
            <?
              $usrtype=0;
              echo listSCID($xscrow[0], $xfield_name, $txt_event,$all);
            ?>
          </td>
        </tr>
  <?
    }//end if
  }//end function

  function floorDec($input, $decimals)
  {
    return round($input - (5 / pow(10, $decimals + 1)), $decimals);
  }

  function fremove_backslashes($xval, $xtype=0) {
    $xresultval='';
    if ($xval!='') {
      $xval = preg_replace("/\\\\/", '', $xval);
      //echo "xval=$xval<br/>\n";
      $xresultval=preg_replace('/[\\\\]+/i','',$xval);
			$xresultval=preg_replace('/[\']+/i',"'",$xval);
			if ($xtype==1)
				$xresultval = pg_escape_string($xval);
		}
    return $xresultval;
  }

  function fget_total_cn($xcode, $xtgl1, $xtgl2) {
    global $db, $debug;
    if ($debug) echo "fget_total_cn($xcode, $xtgl1, $xtgl2)<br/>\n";
    $xsql_cn =
      "select coalesce(sum(a.ndp), 0) as xndp  ".
      "from delsctrh a ".
      "where a.loccd='$xcode' and a.bpdt>='$xtgl1' and a.bpdt<='$xtgl2'  ";
    if ($debug) echo "$xsql_cn<br/>\n";
    $xres_cn = pg_query($db, $xsql_cn);
    $xrow_cn = pg_fetch_row($xres_cn, 0);
    if ($xrow_cn[0]=='') $xrow_cn[0]=0;
    $xtotal1 = $xrow_cn[0];
    $xsql_cn =
      "select coalesce(sum(a.ndp), 0) as xndp  ".
      "from delmsivtrh a ".
      "where a.loccd='$xcode' and a.trdt>='$xtgl1' and a.trdt<='$xtgl2'  ";
    if ($debug) echo "$xsql_cn<br/>\n";
    $xres_cn = pg_query($db, $xsql_cn);
    $xrow_cn = pg_fetch_row($xres_cn, 0);
    if ($xrow_cn[0]=='') $xrow_cn[0]=0;
    $xtotal2 = $xrow_cn[0];
    return $xtotal1+$xtotal2;
  }

  function fget_sc_total_sales($xcode, $xtgl1, $xtgl2) {
    global $db, $debug;
    //echo "fget_total_cn($xcode, $xtgl1, $xtgl2)<br/>\n";
    $xsql_cn =
      "select coalesce(sum(a.ndp), 0) as xndp  ".
      "from newsctrh a ".
      "where a.loccd='$xcode' and a.bpdt>='$xtgl1' and a.bpdt<='$xtgl2' and post='1'  ";
//     if ($debug) echo "$xsql_cn<br/>\n";
    $xres_cn = pg_query($db, $xsql_cn);
    $xrow_cn = pg_fetch_row($xres_cn, 0);
    if ($xrow_cn[0]=='') $xrow_cn[0]=0;
    $xtotal1 = $xrow_cn[0];
    $xsql_cn =
      "select coalesce(sum(a.ndp), 0) as xndp  ".
      "from newmsivtrh a ".
      "where a.loccd='$xcode' and a.trdt>='$xtgl1' and a.trdt<='$xtgl2' and post='1' ";
//     if ($debug) echo "$xsql_cn<br/>\n";
    $xres_cn = pg_query($db, $xsql_cn);
    $xrow_cn = pg_fetch_row($xres_cn, 0);
    if ($xrow_cn[0]=='') $xrow_cn[0]=0;
    $xtotal2 = $xrow_cn[0];
    return $xtotal1+$xtotal2;
  }

  function fget_total_gst_deduction($xcode, $xmonth, $xyear) {
    global $db, $debug;
    if ($debug) echo "fget_total_gst_deduction($xcode, $xmonth, $xyear)<br/>\n";
    $xmonth=str_pad($xmonth, 2, '0', STR_PAD_LEFT).$xyear;
    $xsql=
      "select coalesce(sum(x.amount), 0)
      from sccom_deduction x
      inner join add_deduct_setup a on a.title_id=x.title_id
      inner join item_setup b on b.item_id=x.item_id
      where x.sccode='$xcode' and a.gst=TRUE and x.fmonth='$xmonth' " ;
    if ($debug) echo "$xsql";
    $xres = pg_query($db, $xsql);
    $xrow = pg_fetch_row($xres, 0);
    if ($debug) print_r($xrow);
    return $xrow[0];
  }

  function fget_total_gst_addition($xcode, $xmonth, $xyear) {
    global $db, $debug;
    if ($debug) echo "fget_total_gst_deduction($xcode, $xmonth, $xyear)<br/>\n";
    $xmonth=str_pad($xmonth, 2, '0', STR_PAD_LEFT).$xyear;
    $xsql=
      "select coalesce(sum(x.amount), 0)
      from sccom_addition x
      inner join add_deduct_setup a on a.title_id=x.title_id
      inner join item_setup b on b.item_id=x.item_id
      where x.sccode='$xcode' and a.gst=TRUE and x.fmonth='$xmonth' " ;
    if ($debug) echo "$xsql";
    $xres = pg_query($db, $xsql);
    $xrow = pg_fetch_row($xres, 0);
    if ($debug) print_r($xrow);
    return $xrow[0];
  }

  function fget_sc_sales_cb($xloccd, $xTgl1, $xTgl2) {
    global $db, $debug;
    $xsql="select coalesce(sum(a.ndp), 0) as xsumndp, coalesce(sum(a.tdp), 0) as xsumtdp, coalesce(sum(a.ptax), 0) as xsumptax ".
      "from newsctrh a ".
      "where a.loccd='$xloccd' and a.bpdt>='$xTgl1' and a.bpdt<='$xTgl2' and a.trtype in ('1','3','5') and a.post='1' ";
    if ($debug) {
      echo "fget_sc_sales_cb($xloccd, $xTgl1, $xTgl2) <br/>$xsql<br/>\n";//('1', '3', '5')
    }
    $xres=pg_query($db, $xsql);
    $xrow=pg_fetch_assoc($xres, 0);
    return $xrow;
  }

  function fget_sc_sales_cb3($xloccd, $xTgl1, $xTgl2) {
    global $db, $debug;
    $xsql="select coalesce(sum(a.ndp), 0) as xsumndp, coalesce(sum(a.tdp), 0) as xsumtdp, coalesce(sum(a.ptax), 0) as xsumptax ".
      "from newsctrh a ".
      "where a.loccd='$xloccd' and a.bpdt>='$xTgl1' and a.bpdt<='$xTgl2' and a.trtype in ('1','3') and a.post='1' ";
    if ($debug) {
      echo "fget_sc_sales_cb($xloccd, $xTgl1, $xTgl2) <br/>$xsql<br/>\n";//('1', '3', '5')
    }
    $xres=pg_query($db, $xsql);
    $xrow=pg_fetch_assoc($xres, 0);
    return $xrow;
  }

  function fget_sc_sales_inv($xloccd, $xTgl1, $xTgl2) {
    global $db, $debug;
    $xsql="select coalesce(sum(a.ndp), 0) as xsumndp, coalesce(sum(a.tdp), 0) as xsumtdp, coalesce(sum(a.ptax), 0) as xsumptax ".
      "from newmsivtrh a ".
      "where a.loccd='$xloccd' and a.trdt>='$xTgl1' and a.trdt<='$xTgl2' and a.post='1' ";
    if ($debug) {
      echo "fget_sc_sales_inv($xloccd, $xTgl1, $xTgl2) <br/>$xsql<br/>\n";
    }
    $xres=pg_query($db, $xsql);
    $xrow=pg_fetch_assoc($xres, 0);
    return $xrow;
  }

  function fget_sc_purchase($xcode, $xTgl1, $xTgl2, $xtrtype='MS') {
    global $db, $debug;
//     $xsql=
//       "select coalesce(sum(a.ndp), 0) as xsumndp, sum(coalesce(b.cost,0)) as xsumcost,coalesce(sum(a.tdp), 0) as xsumtdp, coalesce(sum(a.ptax), 0) as xsumptax ".
//       "from newmsivtrh a ".
//       "left join msivship b on b.trivcd=a.trivcd ".
//       "where a.code='$xcode' and a.trdt>='$xTgl1' and a.trdt<='$xTgl2' and a.post='1' ";

    $xsql1=
      "select
      sum(coalesce(a.ndp,0)) as xsumndp,
      sum(coalesce(b.cost,0)) as xsumcost,
      sum(coalesce(a.tdp,0)) as xsumtdp,
      sum(coalesce(a.ptax,0)) as xsumptax,
      sum(coalesce(a.ship_vat,0)) as xsumship_vat
      from newmsivtrh a
      left join msivship b on b.trivcd=a.trivcd
      where a.code='$xcode' and a.trdt>='$xTgl1' and a.trdt<='$xTgl2'
      and a.post='1' ";


    if ($xtrtype=='BR')
      $xsql1.="and a.trtype='1' ";
    elseif ($xtrtype=='MS')
      $xsql1.="and a.trtype='2' ";
    if ($debug) {
      echo "fget_sc_purchase($xcode, $xTgl1, $xTgl2, $xtrtype) <br/>\n";
      echo "$xsql1<br/>\n";
    }
    $xres1=pg_query($db, $xsql1);
    $xrow1=pg_fetch_assoc($xres1, 0);

///////////////////
    $xsql2 =
      "select
      sum(a.qty) as qty,
      sum(coalesce(a.dp_excl*a.qty,0)) as sv_exgst,
      sum(coalesce((a.unit_tax*a.qty),0)) as valgst,
      sum(coalesce((a.dp*a.qty),0)) as sv
      from cntrd a
      inner join cntrh c on a.trcncd=c.trcncd
      inner join msprd_extra e on e.prdcd=a.prdcd
      where c.code='$xcode' and c.trdt>='$xTgl1' and c.trdt<='$xTgl2' ";
    $xres2=pg_query($db, $xsql2);
    $xrow2=pg_fetch_assoc($xres2, 0);

    $xrow = array_merge($xrow1, $xrow2);
    return $xrow;
  }

  function fget4Entitled($xtype,$xcat,$xamt) {
      global $db,$debug;
      $result=0;
      if($xamt=='')$xamt=0;
    $xsc_sql2="select fentitled from sccom_setup_extra where ftype='$xtype' and fcategory='$xcat' and $xamt between fcondition3 and fcondition5 limit 1;";
      $xsc_res2=pg_query($db, $xsc_sql2);
      if(pg_num_rows($xsc_res2)>0){
        $xsc_row=pg_fetch_row($xsc_res2, 0);
        $result=$xsc_row[0];
      }
      pg_free_result($xsc_res2);
      return $result;
  }

  function fget4totalcom($xloccd, $xtype, $xtgl1, $xtgl2, $xstockist=1) {
    global $db, $debug;
    if ($debug) echo "fget4totalcom($xloccd, $xtype, $xtgl1, $xtgl2, $xstockist);<br/>\n";

    $xsc_sql="select a.code, c.name,b.stockist,b.fcat_sccom,s.st_name,a.cbheader,b.fcat_sccom_ms ".
      "from sub_mssc a, sub_mssc_extra b, msmemb c,state s ".
      "where a.code=b.scname and a.code=c.code and a.st_id=s.st_id and a.code='$xloccd' ";
    $xsc_row=fpg_exec4($xsc_sql);
//     if ($debug) print_r($xsc_row);

    if($xtype=="SC" || $xtype=="ALL" || $xtype=="MS"){
      $querr="select sum(a.tpv),sum(a.tbv),sum(a.tdp),sum(a.totpay)
        from newsctrh a
        where a.loccd ='$xloccd'
        and a.bpdt>='$xtgl1' and a.bpdt<='$xtgl2'
        and a.trtype in ('1','3','5') and a.post='1'
        group by a.loccd ;";
       if ($debug) echo "<b>$querr</b><br/>\n";//('1','3','5');
      $xres2 = pg_exec($db, $querr);
      if(pg_num_rows($xres2)>0){
        $xrow2=pg_fetch_row($xres2, 0);
//         if ($debug) {print_r($xrow2);echo "<br/>\n";}
        $xtotpv=$xrow2[0];
        $xtotsv=$xrow2[1];
        $xtotdp=$xrow2[2];
        $xtotpay=$xrow2[3];
      }
      if ($debug) echo "xtotsv = $xtotsv *<br/>\n";
      $xsql3= "select sum(b.qty*b.dp),sum(b.qty*b.dp),
        sum(b.qty*b.dp)
        from newsctrd b left join msprd c on c.prdcd=b.prdcd,newsctrh a
        where a.trcd=b.trcd and a.loccd='$xloccd'
        and a.trdt>='$xtgl1' and a.trdt<='$xtgl2'
        and a.trtype in ('1','3','5') and c.kit>='1' and a.post='1'; ";
//       if ($debug) echo "$xsql3<br>";//('1','3')
      $xres3=pg_query($db, $xsql3);
      $xtot_pv=0;
      $xtot_sv=0;
      $xtot_dp=0;
      if (pg_num_rows($xres3)>0) {
        for ($o=0;$o<pg_num_rows($xres3);$o++) {
          $xrow3=pg_fetch_row($xres3, $o);
//           if ($debug) {print_r($xrow3);echo "<br/>\n";}
          $xtot_pv+=$xrow3[0];
          $xtot_sv+=$xrow3[1];
          $xtot_dp+=$xrow3[2];
        }
      }


      pg_free_result($xres3);
      $xtotpv+=$xtot_pv;
      $xtotsv+=$xtot_sv;
  //    $xtotdp+=$xtot_dp;
      $xentitled=fget4Entitled("SC",$xsc_row[3],$xtotpay);
    }//end if xtype

    if ($debug) echo "xtotsv = $xtotsv **<br/>\n";

    if(($xtype=="MS" || $xtype=="ALL") && $xstockist=='0'){
        $querr="select sum(a.tpv),sum(a.tbv),sum(a.tdp),sum(a.totpay)
          from newmsivtrh a
          where a.loccd ='$xloccd' and a.trdt>='$xtgl1' and a.trdt<='$xtgl2'
          and a.post='1'
          group by a.loccd ;";
        if ($debug) echo "<b>$querr</b><br/>\n";
        $xres2 = pg_exec($db, $querr);
        if(pg_num_rows($xres2)>0){
          $xrow2=pg_fetch_row($xres2, 0);
//           if ($debug) {print_r($xrow2);echo "<br/>\n";}
          $xtotpv2=$xrow2[0]+$xtotpv;
          $xtotsv2=$xrow2[1]+$xtotsv;
          $xtotdp2=$xrow2[2]+$xtotdp;
          $xtotpay2=$xrow2[3]+$xtotpay;
        } else {
          $xtotpv2=$xtotpv;
          $xtotsv2=$xtotsv;
          $xtotdp2=$xtotdp;
          $xtotpay2=$xtotpay;

        }
        $xsql3= "select sum(b.qty*b.dp),sum(b.qty*b.dp),
        sum(b.qty*b.dp)
        from newmsivtrd b left join msprd c on c.prdcd=b.prdcd,newmsivtrh a
        where a.trivcd=b.trivcd and a.loccd='$xloccd'
        and trdt>='$xtgl1' and trdt<='$xtgl2' and c.kit='1' and a.post='1'; ";
//         if ($debug) echo "$xsql3<br>";
        $xres3=pg_query($db, $xsql3);
        $xtot_pv2=0;
        $xtot_sv2=0;
        $xtot_dp2=0;
        if (pg_num_rows($xres3)>0) {
          for ($o=0;$o<pg_num_rows($xres3);$o++) {
            $xrow3=pg_fetch_row($xres3, $o);
//             if ($debug) {print_r($xrow3);echo "<br/>\n";}
            $xtot_pv2+=$xrow3[0];
            $xtot_sv2+=$xrow3[1];
            $xtot_dp2+=$xrow3[2];
          }
        }
        pg_free_result($xres3);
        $xtotpv2+=$xtot_pv2;
        $xtotsv2+=$xtot_sv2;
  //      $xtotdp2+=$xtot_dp2;
        $xentitled2=fget4Entitled("MS",$xsc_row[6],$xtotpay2);


    }//end if xtype

//     if ($debug) echo "xtotsv2 = $xtotsv2 <br/>\n";

    if($xtype=="ALL") {
      $xtotalcom = round((($xentitled*$xtotsv)/100),2) + round((($xentitled2*($xtotsv2))/100),2);
			if ($debug) echo ("$xtotalcom = round((($xentitled*$xtotsv)/100),2) + round((($xentitled2*($xtotsv2))/100),2);<br/>\n");
    }else if ($xtype=="SC") {
      $xtotalcom = round((($xentitled*$xtotsv)/100),2);
      if ($debug) echo ("$xtotalcom = round((($xentitled*$xtotsv)/100),2);<br/>\n");
    }else if ($xtype=="MS") {
      $xtotalcom = round((($xentitled2*($xtotsv2))/100),2);
      if ($debug) echo ("$xtotalcom = round((($xentitled2*$xtotsv2)/100),2);<br/>\n");
		}
    if ($xtotsv2=='') $xtotsv2=0;
    if ($xtotpv2=='') $xtotpv2=0;
    if ($xtotsv=='')  $xtotsv=0;
    if ($xtotsv2=='') $xtotsv2=0;
    if ($xtotalcom=='') $xtotalcom=0;

    $xarr_result['xtotsv'] =$xtotsv;
    $xarr_result['xtotsv2']=$xtotsv2;
    $xarr_result['xtotpv2']=$xtotpv2;
    $xarr_result['xtotdp2']=$xtotdp2;
    $xarr_result['totalcom']=$xtotalcom;
    return $xarr_result;

  }//end function

  function fget_total_deduct($xcode, $xmonth, $xvat_persen, $xbol_sc_gst=false) {
    global $db, $debug;
    if ($debug) echo "fget_total_deduct($xcode, $xmonth, $xvat_persen, $xbol_sc_gst);<br/>\n";
/*    $xsql_deduct_gst=
      "select distinct a.title_id, a.title, a.fshow, a.gst
      from sccom_deduction x
      inner join add_deduct_setup a on a.title_id=x.title_id
      inner join item_setup b on b.item_id=x.item_id
      where x.sccode='$xcode' and x.fmonth='$xmonth'
      order by a.title_id";*/
/*    $xsql_deduct_gst=
      "select distinct x.title_id, x.title, x.fshow, x.gst
      from add_deduct_setup x
      left join sccom_deduction a on a.title_id=x.title_id and a.sccode='$xcode' and a.fmonth='$xmonth'
      left join item_setup b on b.item_id=a.item_id
      where x.category_id='2' ";

  */  $xsql_deduct_gst=
      "select x.title_id, x.title, x.fshow, x.gst, max(a.amount) as max_amount
      from add_deduct_setup x
      left join sccom_deduction a on a.title_id=x.title_id and a.sccode='$xcode' and a.fmonth='$xmonth'
      left join item_setup b on b.item_id=a.item_id
      where x.category_id='2'
      group by x.title_id, x.title, x.fshow, x.gst  ";//having max(a.amount)>0

    if ($debug) echo "xsql_deduct_gst=$xsql_deduct_gst<br/>\n";
    $xres_deduct_gst=pg_query($db, $xsql_deduct_gst);


    $xgst_tot_gst_deduction=0;
    $xgst_tot_gst_deduction_vat=0;

    for ($xi=0; $xi<pg_num_rows($xres_deduct_gst); $xi++) {
      $xrow_deduct_gst=pg_fetch_row($xres_deduct_gst, $xi);
      if ($debug) {
        print_r($xrow_deduct_gst);
        echo "<br/>\n";
      }
      $xsql_deduct_item=
        "select b.item_id, b.item, x.amount
        from sccom_deduction x
        inner join add_deduct_setup a on a.title_id=x.title_id
        inner join item_setup b on b.item_id=x.item_id
        where x.sccode='$xcode' and a.title_id=$xrow_deduct_gst[0] and x.fmonth='$xmonth' " ;
      if ($debug) echo "xsql_deduct_item=$xsql_deduct_item<br/>\n";
      $xres_deduct_item=pg_query($db, $xsql_deduct_item);
      $xgst_sub_deduction=0;
      $xgst_subtot_gst_deduction=0;
      $xgst_subtot_gst_deduction_vat=0;
      for ($yi=0; $yi<pg_num_rows($xres_deduct_item); $yi++) {
        $xrow_deduct_item=pg_fetch_row($xres_deduct_item, $yi);
        if ($debug) {
          print_r($xrow_deduct_item);
          echo "<br/>\n";
        }
        $xgst_subtot_gst_deduction+=$xrow_deduct_item[2];
        $xgst_tot_gst_deduction+=$xrow_deduct_item[2];

      }//end for item
      if ($xrow_deduct_gst[3]=='t' && $xbol_sc_gst){
        $xgst_subtot_gst_deduction_vat=$xgst_subtot_gst_deduction*$xvat_persen/100;
        $xgst_tot_gst_deduction_vat+=$xgst_subtot_gst_deduction_vat;
      }
    }//end for
    $xarr_result['total']=number_format($xgst_tot_gst_deduction, 2, '.', '');
    $xarr_result['vat']  =number_format($xgst_tot_gst_deduction_vat, 2, '.', '');
    return $xarr_result;
  }//end function

  function fget_total_addit($xcode, $xmonth, $xvat_persen, $xbol_sc_gst=false, $xstartotal) {
    global $db, $debug;
    $xtotal1=$xstartotal;
/*    $xsql_addit_gst=
      "select distinct a.title_id, a.title, a.fshow, a.gst
      from sccom_addition x
      inner join add_deduct_setup a on a.title_id=x.title_id
      inner join item_setup b on b.item_id=x.item_id
      where x.sccode='$xcode' and x.fmonth='$xmonth'
      order by a.title_id";*/
/*    $xsql_addit_gst=
      "select distinct x.title_id, x.title, x.fshow, x.gst
      from add_deduct_setup x
      left join sccom_addition a on a.title_id=x.title_id and a.sccode='$xcode' and a.fmonth='$xmonth'
      left join item_setup b on b.item_id=a.item_id
      where x.title ilike '%Fee%' and x.category_id='1' ";*/
    $xsql_addit_gst=
      "select x.title_id, x.title, x.fshow, x.gst, max(a.amount) as max_amount
      from add_deduct_setup x
      left join sccom_addition a on a.title_id=x.title_id and a.sccode='$xcode' and a.fmonth='$xmonth'
      left join item_setup b on b.item_id=a.item_id
      where x.category_id='1'
      group by x.title_id, x.title, x.fshow, x.gst  ";//having max(a.amount)>0

    if ($debug) echo "$xsql_addit_gst**<br/>\n";
    $xres_addit_gst=pg_query($db, $xsql_addit_gst);

    $xgst_tot_gst_addition=0;
    $xgst_tot_gst_addition_gst=0;

    for ($xi=0; $xi<pg_num_rows($xres_addit_gst); $xi++) {
      $xrow_addit_gst=pg_fetch_row($xres_addit_gst, $xi);
      if ($debug) {
        print_r($xrow_addit_gst);
        echo "<br/>\n";
      }
      $xsql_addit_item=
        "select b.item_id, b.item, x.amount
        from sccom_addition x
        inner join add_deduct_setup a on a.title_id=x.title_id
        inner join item_setup b on b.item_id=x.item_id
        where x.sccode='$xcode' and a.title_id=$xrow_addit_gst[0] and x.fmonth='$xmonth' " ;
      if ($debug) echo "$xsql_addit_item<br/>\n";
      $xres_addit_item=pg_query($db, $xsql_addit_item);
      $xgst_sub_addition=0;
      $xgst_subtot_gst_addition=0;
      $xgst_subtot_gst_addition_gst=0;
      for ($yi=0; $yi<pg_num_rows($xres_addit_item); $yi++) {
        $xrow_addit_item=pg_fetch_row($xres_addit_item, $yi);
        if ($debug) {
          print_r($xrow_addit_item);
          echo "<br/>\n";
        }
        $xgst_subtot_gst_addition+=$xrow_addit_item[2];
        $xgst_tot_gst_addition   +=$xrow_addit_item[2];
        $xtotal1                 +=$xrow_addit_item[2];
      }//end for item

      $xgst_subtot_gst_addition_gst=0;
      if ($xrow_addit_gst[3]=='t' && $xbol_sc_gst){
        $xgst_subtot_gst_addition_gst=number_format($xtotal1*$xvat_persen/100,2,'.','');
      }

        $xgst_tot_gst_addition_gst+=$xgst_subtot_gst_addition_gst;
        if ($debug) echo "xtotal1=$xtotal1 xgst_subtot_gst_addition_gst=$xgst_subtot_gst_addition_gst<br/>\n";
        $xtotal1+=$xgst_subtot_gst_addition_gst;
        if ($debug) echo "xtotal1=$xtotal1 <br/>\n";

    }//end for
    $xarr_result['total']=number_format($xgst_tot_gst_addition, 2, '.', '');
    $xarr_result['vat']  =number_format($xgst_tot_gst_addition_gst, 2, '.', '');
    $xarr_result['xtotal1']=number_format($xtotal1, 2, '.', '');
    return $xarr_result;
  }//end function


  function fcheck4_flag_action($xcode, $xfield_name) {
    //$xfield_name: a_edit, a_view, a_reg, a_purc
    global $debug;
  /*  $arr_field_flag=array(
      'a_edit'=>"Member ($xcode) not allowed to edit !",
      'a_view'=>"Member ($xcode) not allowed to view !",
      'a_reg'=>"Member ($xcode) not allowed to become sponsor !",
      'a_purc'=>"Member ($xcode) not allowed to purchase",
    );*/
    $msg1='';
    $xsql_m="select flag from msmemb where code='$xcode' ";
    $xrow_m=fpg_exec4($xsql_m);

    $arr_xflag=array(
      0 => "Member code ($xcode) has been suspended!",
      1 => "Member code ($xcode) has been suspended!",
      2 => "Member code ($xcode) has been terminated!",
      3 => "Member code ($xcode) has been resigned!",
      4 => "Member code ($xcode) has been expired!",
      5 => "Member code ($xcode) has been permanent expired!",
      6 => "Member code ($xcode) has been has transferred!",
      7 => "Member code ($xcode) has been rejoined!",
      8 => "Member code ($xcode) has been deceased!"
    );

    $arr_field_flag=array(
      'a_edit'=>"Member code ($xcode) not allowed to edit !",
      'a_view'=>"Member code ($xcode) not allowed to view!",
      'a_reg' =>"Member code ($xcode) not allowed to become sponsor!",
      'a_purc'=>"Member code ($xcode) not allowed to purchase!",
    );

    //$xresult=false;
    if ($xrow_m[0]!='') {
      $xsql_f="select $xfield_name from mflag_setup where mflag='".$xrow_m[0]."' ";
      $xrow_f=fpg_exec4($xsql_f);
  //     if ($debug) echo("$xsql_f ".$xrow_f[0]."<br/>");
      if ($xrow_f[0]=='f') {
        //$msg=$arr_field_flag[$xfield_name];
        $msg =$arr_xflag[$xrow_m[0]];
        $msg1=$msg;
      }
    }
    return $msg1;
  }

  function floor_dec($number,$precision = 2,$separator = '.') {
    //global $debug;
    if ($number=='') $txt_vat=0;
    $xresult = $number;

    $numberpart=explode($separator,$number);
    if ($debug) {
      echo "$number <br/>\n";
      print_r($numberpart);
      echo "<br/>\n";
      echo "floor_dec($number,$precision,$separator); <br/>\n";
    }
    if (strlen($numberpart[1])>2) {
      $xtail=substr($numberpart[1], 2, 1);
    }

    $numberpart[1]=substr($numberpart[1], 0, 2);

    if ($debug) {
      echo "xtail=$xtail <br/>\n";
    }
    if ($xtail>5) {
      $numberpart[1]+=1;
    }

    //$numberpart[1]=substr_replace($numberpart[1],$separator,$precision,0);
//     if ($debug) {
//       echo "(";
//       print_r($numberpart);
//       echo ")<br/>\n";
//     }
     if($numberpart[0]>=0) {
//       if ($debug) {
//         print_r($numberpart);
//         echo "* ".substr(floor('1'.$numberpart[1]),1)."<br/>\n";
//       }
//       $numberpart[1]=substr(floor('1'.$numberpart[1]),1);
//       if ($debug) {
//         print_r($numberpart);
//         echo "**<br/>\n";
//       }
//     } else {
//       $numberpart[1]=substr(ceil('1'.$numberpart[1]),1);
      $xresult = $numberpart[0].'.'.$numberpart[1];
     }
//     $ceil_number= array($numberpart[0],$numberpart[1]);
//     return implode($separator,$ceil_number);
    return $xresult;
  }

  function fform_epoin() {
    global $txt_msid, $txt_key, $txt_grandtotal, $default_cnt, $opnm, $year1, $month1, $date1, $time1,
      $XURL, $xmodule_name, $epoint_server, $epoint_dir, $ewallet_application, $xurl_epoint_responder,
      $xepoint_trtype,$txt_seller_statdeposit, $txt_buyer_statdeposit, $debug, $txt_keyid,
      $txt_seller_statdeposit_enc, $txt_buyer_statdeposit_enc;

    $xkey=$txt_key;
//     if ($debug) {
//       echo "txt_key=$txt_key $xkey txt_keyid=$txt_keyid<br/>";
//     }
    if (preg_match('/\-/', $txt_key)) {
      $xarr_key = preg_split('/\-/', $txt_key);
      $xkey = trim($xarr_key[0]);
      if ($debug) {
        echo "/*";
        print_r($xarr_key);
        echo "*/";
      }
    }

    $xlevel = check_level_ex($txt_msid);
//    $txt_sellerflag=1;

/*    if (preg_match('/brcb|brinv|brcr/', $xmodule_name)) {
      $txt_sellerflag=1;
    }elseif (preg_match('/msinv/', $xmodule_name)) {
      $txt_sellerflag=3;
    }elseif (preg_match('/sccb|sccr/', $xmodule_name)) {
      $txt_sellerflag=2;
    }*/
    if ($xlevel=='BR') {
      $txt_sellerflag=1;
    }elseif ($xlevel=='SC') {
      $txt_sellerflag=2;
    }elseif ($xlevel=='MS') {
      $txt_sellerflag=3;
    }

    //$path = "https://$epoint_server"."$ewallet_application/transaction/payment_login.php";
    $path = "https://$epoint_server"."/$epoint_dir/payment_login.php";


    if ($txt_grandtotal=='') $txt_grandtotal=0;

?>
<form name="frm_epoint" id="frm_epoint" method="post" action="<?=$path?>" target="_blank">
  <input type="hidden" name="txt_sellerflag" value="<?=$txt_sellerflag?>">
  <input type="hidden" name="txt_seller" value="<?=/*($xlevel=='BR')?'008888888':*/$txt_msid?>">
  <input type="hidden" name="txt_buyer" value="<?=$xkey?>">
  <input type="hidden" name="txt_grandtotal" value="<?=$txt_grandtotal?>">
  <input type="hidden" name="curr_code" value="<?=trim(CURRENCY)?>">
  <input type="hidden" name="txt_grandtotalRM" value="<?=$txt_grandtotal?>">
  <input type="hidden" name="curr_res" value="<?=trim(CURRENCY)?>">
  <input type="hidden" name="company" value="DXN">
  <input type="hidden" name="postURL" value="<?=$xurl_epoint_responder?>">
  <input type="hidden" name="fromsys" value="1">
  <input type="hidden" name="txt_cnid" value='<?=$default_cnt?>'>
  <input type="hidden" name="hOpnm" value="<?=$opnm?>">
  <input type="hidden" name="date" value="<?="$year1-$month1-$date1 ".substr($time1, 0, 8)?>">
  <input type="hidden" name="lang_id" value="EN">
  <input type="hidden" name="txt_seller_statdeposit" value="<?=$txt_seller_statdeposit?>">
  <input type="hidden" name="txt_buyer_statdeposit" value="<?=$txt_buyer_statdeposit?>">
<?
	if ($txt_seller_statdeposit_enc!='' && $txt_buyer_statdeposit_enc!='') {
?>
		<input type="hidden" name="txt_seller_statdeposit_enc" value="<?=$txt_seller_statdeposit_enc?>">
		<input type="hidden" name="txt_buyer_statdeposit_enc" value="<?=$txt_buyer_statdeposit_enc?>">
<?
	}
?>
</form>
<?
    //<!--input type="hidden" name="trtype" value="=$xepoint_trtype"-->
		//fdisable_f5_button();
  }//end function fform_epoin

  function fform_bpp($tmp_trcd,$online=false) {
      global $txt_msid, $txt_key, $txt_grandtotal, $default_cnt, $opnm, $year1, $month1, $date1, $time1,
        $XURL, $xmodule_name, $epoint_server, $epoint_dir, $ewallet_application, $xurl_ep_responder,
        $xepoint_trtype,$txt_seller_statdeposit, $txt_buyer_statdeposit, $debug, $txt_keyid,
        $txt_seller_statdeposit_enc, $txt_buyer_statdeposit_enc, $xno_target_new,$bpp_dir;

        $xkey=$txt_key;
        if (preg_match('/\-/', $txt_key)) {
          $xarr_key = preg_split('/\-/', $txt_key);
          $xkey = trim($xarr_key[0]);
          if ($debug) {
            echo "/*";
            print_r($xarr_key);
            echo "*/";
          }
        }

        $xlevel = check_level_ex($txt_msid);
        if ($xlevel=='BR') {
          $txt_sellerflag=1;
        }elseif ($xlevel=='SC') {
          $txt_sellerflag=2;
        }elseif ($xlevel=='MS') {
          $txt_sellerflag=3;
        }

        $path = "https://$epoint_server/$bpp_dir/bpp_payment_login.php"; 


        if ($txt_grandtotal=='') $txt_grandtotal=0;

    ?>
      <form name="frm_bpp" id="frm_bpp" method="post" action="<?=$path?>" <? if (!$online && !$xno_target_new) echo "target=\"_blank\";"?>>
          <input type="hidden" name="txt_sellerflag" value="<?=$txt_sellerflag?>">
          <input type="hidden" name="txt_seller" value="<?=/*($xlevel=='BR')?'008888888':*/$txt_msid?>">
          <input type="hidden" name="txt_buyer" value="<?=$xkey?>">
          <input type="hidden" name="txt_grandtotal" value="<?=$txt_grandtotal?>">
          <input type="hidden" name="curr_code" value="<?=trim(CURRENCY)?>">
          <input type="hidden" name="txt_grandtotalRM" value="<?=$txt_grandtotal?>">
          <input type="hidden" name="curr_res" value="<?=trim(CURRENCY)?>">
          <input type="hidden" name="company" value="DXN">
          <input type="hidden" name="postURL" value="<?=$xurl_ep_responder?>">
          <input type="hidden" name="fromsys" value="1">
          <input type="hidden" name="txt_cnid" value='<?=$default_cnt?>'>
          <input type="hidden" name="hOpnm" value="<?=$opnm?>">
          <input type="hidden" name="date" value="<?="$year1-$month1-$date1 ".substr($time1, 0, 8)?>">
          <input type="hidden" name="lang_id" value="EN">
          <input type="hidden" name="txt_seller_statdeposit" value="<?=$txt_seller_statdeposit?>">
          <input type="hidden" name="txt_buyer_statdeposit" value="<?=$txt_buyer_statdeposit?>"> 
        <?php
          if ($online){
        ?>
          <input type="hidden" name="onestep" value="1">
        <?php
          }
        ?>
        <?
          if ($txt_seller_statdeposit_enc!='' && $txt_buyer_statdeposit_enc!='') {
        ?>
            <input type="hidden" name="txt_seller_statdeposit_enc" value="<?=$txt_seller_statdeposit_enc?>">
            <input type="hidden" name="txt_buyer_statdeposit_enc" value="<?=$txt_buyer_statdeposit_enc?>">
        <?
          }
        ?>
          <input type="hidden" name="invoiceNo" value="<?=$tmp_trcd?>">
      </form>
    <?
  }//end function fform_epoin2
  
  function fform_epoin2($tmp_trcd,$online=false) {
    global $txt_msid, $txt_key, $txt_grandtotal, $default_cnt, $opnm, $year1, $month1, $date1, $time1,
      $XURL, $xmodule_name, $epoint_server, $epoint_dir, $ewallet_application, $xurl_ep_responder,
      $xepoint_trtype,$txt_seller_statdeposit, $txt_buyer_statdeposit, $debug, $txt_keyid,
      $txt_seller_statdeposit_enc, $txt_buyer_statdeposit_enc, $xno_target_new;

    $xkey=$txt_key;
//     if ($debug) {
//       echo "txt_key=$txt_key $xkey txt_keyid=$txt_keyid<br/>";
//     }
    if (preg_match('/\-/', $txt_key)) {
      $xarr_key = preg_split('/\-/', $txt_key);
      $xkey = trim($xarr_key[0]);
      if ($debug) {
        echo "/*";
        print_r($xarr_key);
        echo "*/";
      }
    }

    $xlevel = check_level_ex($txt_msid);
//    $txt_sellerflag=1;

/*    if (preg_match('/brcb|brinv|brcr/', $xmodule_name)) {
      $txt_sellerflag=1;
    }elseif (preg_match('/msinv/', $xmodule_name)) {
      $txt_sellerflag=3;
    }elseif (preg_match('/sccb|sccr/', $xmodule_name)) {
      $txt_sellerflag=2;
    }*/
    if ($xlevel=='BR') {
      $txt_sellerflag=1;
    }elseif ($xlevel=='SC') {
      $txt_sellerflag=2;
    }elseif ($xlevel=='MS') {
      $txt_sellerflag=3;
    }

    //$path = "https://$epoint_server"."$ewallet_application/transaction/payment_login.php";
    $path = "https://$epoint_server"."/$epoint_dir/payment_login.php";


    if ($txt_grandtotal=='') $txt_grandtotal=0;

?>
<form name="frm_epoint" id="frm_epoint" method="post" action="<?=$path?>" <? if (!$online && !$xno_target_new) echo "target=\"_blank\";"?>>
  <input type="hidden" name="txt_sellerflag" value="<?=$txt_sellerflag?>">
  <input type="hidden" name="txt_seller" value="<?=/*($xlevel=='BR')?'008888888':*/$txt_msid?>">
  <input type="hidden" name="txt_buyer" value="<?=$xkey?>">
  <input type="hidden" name="txt_grandtotal" value="<?=$txt_grandtotal?>">
  <input type="hidden" name="curr_code" value="<?=trim(CURRENCY)?>">
  <input type="hidden" name="txt_grandtotalRM" value="<?=$txt_grandtotal?>">
  <input type="hidden" name="curr_res" value="<?=trim(CURRENCY)?>">
  <input type="hidden" name="company" value="DXN">
  <input type="hidden" name="postURL" value="<?=$xurl_ep_responder?>">
  <input type="hidden" name="fromsys" value="1">
  <input type="hidden" name="txt_cnid" value='<?=$default_cnt?>'>
  <input type="hidden" name="hOpnm" value="<?=$opnm?>">
  <input type="hidden" name="date" value="<?="$year1-$month1-$date1 ".substr($time1, 0, 8)?>">
  <input type="hidden" name="lang_id" value="EN">
  <input type="hidden" name="txt_seller_statdeposit" value="<?=$txt_seller_statdeposit?>">
  <input type="hidden" name="txt_buyer_statdeposit" value="<?=$txt_buyer_statdeposit?>">
<?php
  if ($online){
?>
  <input type="hidden" name="onestep" value="1">
<?php
  }
?>
<?
	if ($txt_seller_statdeposit_enc!='' && $txt_buyer_statdeposit_enc!='') {
?>
		<input type="hidden" name="txt_seller_statdeposit_enc" value="<?=$txt_seller_statdeposit_enc?>">
		<input type="hidden" name="txt_buyer_statdeposit_enc" value="<?=$txt_buyer_statdeposit_enc?>">
<?
	}
?>
  <input type="hidden" name="invoiceNo" value="<?=$tmp_trcd?>">
</form>
<?
    //<!--input type="hidden" name="trtype" value="=$xepoint_trtype"-->
		//fdisable_f5_button();
  }//end function fform_epoin2

  function fchecking_ew_validation($xtrcd, $xstr_request) {
    //result 0: not ew, 1: ew pass, 2: ew: failed

    $xresult=0;
    $xarr_ew_request=preg_split('/\,/', $xstr_request);
    //print_r($xarr_ew_request);
    //echo "<br/>\n";
    for ($i=0; $i<count($xarr_ew_request); $i++) {
      if (trim($xarr_ew_request[$i])!='') {
        $xarr_ew_request2=preg_split('/\|/', $xarr_ew_request[$i]);
        //print_r($xarr_ew_request2);
        //echo "<br/>\n";
        if (trim($xtrcd)==trim($xarr_ew_request2[0]) ) {
          if (trim($xarr_ew_request2[1])=='Pass') {
            $xresult=1;
          }elseif (preg_match('/fail_by_bal/i',trim($xarr_ew_request2[1]))) {
            $xresult=3;
          }else{
            //echo $xarr_ew_request2[1]." here <br/>";
            $xresult=2;
          }
          break;
        }
      }
    }//end for
    return $xresult;
  }

  function fget_ew_transaction($xtrx, $sign) {
    global $db, $debug;
    //$debug=1;
    if ($debug) {
      echo "fget_ew_transaction($xtrx, $sign)<br/>\n";
    }
    $xtable1     = 'newmstrh';
    $xtrcd_field = 'trcd';
    $xtable3     = 'mspayment';
    if ($sign==2) {
      $xtable1     = 'newmsivtrh';
      $xtrcd_field = 'trivcd';
    }elseif ($sign==3) {
      $xtable1     = 'newsctrh';
      $xtrcd_field = 'trcd';
      $xtable3     = 'scpayment';
    }
    $xsql1=
			"select 
			a.paytype1, a.paytype2, a.paytype3, a.paytype4, a.paytype5, 
			a.totpay1, a.totpay2, a.totpay3, a.totpay4, a.totpay5, 
			a.paynote1, a.paynote2, a.paynote3, a.paynote4, a.paynote5, 
			b.$xtrcd_field as xcbno, b.code ".
      "from $xtable3 a ".
      "left join $xtable1 b on a.$xtrcd_field=b.$xtrcd_field ".
      "where a.$xtrcd_field='$xtrx' and  ".
      "(
      a.paytype1='EW' or a.paytype2='EW' or a.paytype3='EW' or a.paytype4='EW' or a.paytype5='EW' or
      a.paytype1='DXN' or a.paytype2='DXN' or a.paytype3='DXN' or a.paytype4='DXN' or a.paytype5='DXN' 
			) ";

    $xsql1=
      "select 
			a.paytype1, a.paytype2, a.paytype3, a.paytype4, a.paytype5, 
			a.totpay1, a.totpay2, a.totpay3, a.totpay4, a.totpay5, 
			a.paynote1, a.paynote2, a.paynote3, a.paynote4, a.paynote5, 
      b.trcd as xcbno, b.code ".
      "from mspayment a ".
      "left join newmstrh b on a.trcd=b.trcd ".
      "where a.trcd='$xtrx' and  ".
      "(
      a.paytype1='EW' or a.paytype2='EW' or a.paytype3='EW' or a.paytype4='EW' or a.paytype5='EW' or
      a.paytype1='DXN' or a.paytype2='DXN' or a.paytype3='DXN' or a.paytype4='DXN' or a.paytype5='DXN' 
			) ".
      "union all ".
      "select 
			a.paytype1, a.paytype2, a.paytype3, a.paytype4, a.paytype5, 
			a.totpay1, a.totpay2, a.totpay3, a.totpay4, a.totpay5, 
			a.paynote1, a.paynote2, a.paynote3, a.paynote4, a.paynote5, 
      b.trcd as xcbno, b.code ".
      "from scpayment a ".
      "left join newsctrh b on a.trcd=b.trcd ".
      "where a.trcd='$xtrx' and  ".
      "(
      a.paytype1='EW' or a.paytype2='EW' or a.paytype3='EW' or a.paytype4='EW' or a.paytype5='EW' or
      a.paytype1='DXN' or a.paytype2='DXN' or a.paytype3='DXN' or a.paytype4='DXN' or a.paytype5='DXN' 
			) ".
      "union all ".
      "select 
			a.paytype1, a.paytype2, a.paytype3, a.paytype4, a.paytype5, 
			a.totpay1, a.totpay2, a.totpay3, a.totpay4, a.totpay5, 
			a.paynote1, a.paynote2, a.paynote3, a.paynote4, a.paynote5, 
      b.trivcd as xcbno, b.code ".
      "from mspayment a ".
      "left join newmsivtrh b on a.trivcd=b.trivcd ".
      "where a.trivcd='$xtrx' and  ".
      "(
      a.paytype1='EW' or a.paytype2='EW' or a.paytype3='EW' or a.paytype4='EW' or a.paytype5='EW' or
      a.paytype1='DXN' or a.paytype2='DXN' or a.paytype3='DXN' or a.paytype4='DXN' or a.paytype5='DXN' 
			) ";

    $xres1=pg_query($db, $xsql1);
    if ($debug) echo "<b>$xsql1 (".pg_num_rows($xres1).") recs</b><br/><br>\n";
    $xarr_result['amount'] =0;
    $xarr_result['ewtrxno']='';
    if (pg_num_rows($xres1)>0) {
      $xrow=pg_fetch_assoc($xres1, 0);
      if ($debug) {
        print_r($xrow);
        echo "*********<br/><br>\n";
      }
      if ($xrow['paytype1']=='EW' || $xrow['paytype1']=='DXN'){
        $xamount=$xrow['totpay1'];
        $xpaynote = $xrow['paynote1'];
      }elseif ($xrow['paytype2']=='EW' || $xrow['paytype2']=='DXN'){
        $xamount=$xrow['totpay2'];
        $xpaynote = $xrow['paynote2'];
      }elseif ($xrow['paytype3']=='EW' || $xrow['paytype3']=='DXN'){
        $xamount=$xrow['totpay3'];
        $xpaynote = $xrow['paynote3'];
      }elseif ($xrow['paytype4']=='EW' || $xrow['paytype4']=='DXN'){
        $xamount=$xrow['totpay4'];
        $xpaynote = $xrow['paynote4'];
      }elseif ($xrow['paytype5']=='EW' || $xrow['paytype5']=='DXN'){
        $xamount=$xrow['totpay5'];
        $xpaynote = $xrow['paynote5'];
      }
    }
    $xarr_result['amount'] =$xamount;
    $xarr_result['ewtrxno']=$xpaynote;
//     if ($debug) {
//       print_r($xarr_result);
//       echo "<br/>";
//     }
    return $xarr_result;
  }

  function fget_rl_transaction($xtrx, $sign) {
    global $db, $debug;
    //$debug=1;
    if ($debug) {
      echo "fget_rl_transaction($xtrx, $sign)<br/>\n";
    }
    $xtable1     = 'newmstrh';
    $xtrcd_field = 'trcd';
    $xtable3     = 'mspayment';
    if ($sign==2) {
      $xtable1     = 'newmsivtrh';
      $xtrcd_field = 'trivcd';
    }elseif ($sign==3) {
      $xtable1     = 'newsctrh';
      $xtrcd_field = 'trcd';
      $xtable3     = 'scpayment';
    }
    $xsql1=
      "select a.*, b.trcd as xcbno, b.code, b.totpay, b.note3 ".
      "from mspayment a ".
      "left join newmstrh b on a.trcd=b.trcd ".
      "where a.trcd='$xtrx'  ".
      "union ".
      "select a.*, b.trcd as xcbno, b.code, b.totpay, b.note3 ".
      "from scpayment a ".
      "left join newsctrh b on a.trcd=b.trcd ".
      "where a.trcd='$xtrx'  ".
      "union ".
      "select a.*, b.trivcd as xcbno, b.code, b.totpay, b.note3 ".
      "from mspayment a ".
      "left join newmsivtrh b on a.trivcd=b.trivcd ".
      "where a.trivcd='$xtrx'  ";

    $xres1=pg_query($db, $xsql1);
    if ($debug) echo "<b>$xsql1 (".pg_num_rows($xres1).") recs</b><br/><br>\n";
    $xarr_result['amount'] =0;
    $xarr_result['ewtrxno']='';
    /*
    if (pg_num_rows($xres1)>0) {
      $xrow=pg_fetch_assoc($xres1, 0);
      if ($debug) {
        print_r($xrow);
        echo "*********<br/><br>\n";
      }
      if ($xrow['paytype1']=='EW'){
        $xamount=$xrow['totpay1'];
        $xpaynote = $xrow['paynote1'];
      }elseif ($xrow['paytype2']=='EW'){
        $xamount=$xrow['totpay2'];
        $xpaynote = $xrow['paynote2'];
      }elseif ($xrow['paytype3']=='EW'){
        $xamount=$xrow['totpay3'];
        $xpaynote = $xrow['paynote3'];
      }elseif ($xrow['paytype4']=='EW'){
        $xamount=$xrow['totpay4'];
        $xpaynote = $xrow['paynote4'];
      }elseif ($xrow['paytype5']=='EW'){
        $xamount=$xrow['totpay5'];
        $xpaynote = $xrow['paynote5'];
      }
    }
    */
    if (pg_num_rows($xres1)>0) {
      $xrow=pg_fetch_assoc($xres1, 0);
      if ($debug) {
        print_r($xrow);
        echo "*********<br/><br>\n";
      }
    }
    $xarr_result['amount'] =$xrow['totpay'];
    $xarr_result['ewtrxno']=$xrow['note3'];
//     if ($debug) {
//       print_r($xarr_result);
//       echo "<br/>";
//     }
    return $xarr_result;
  }

  function fepoint_refundposting($xtrtype, $xcbno, $xdeltrtype) {
    global $debug, $opnm, $epoint_port, $epoint_server, $epoint_dir, $ewallet_application,
      $xarr_ew_info, $xrowh, $time1, $year1, $month1, $date1, $xurl, $txt_seller_statdeposit,
      $txt_buyer_statdeposit;
    if ($debug) {
      print_r($xrowh);
      echo "<br/>\n"."****fepoint_refundposting($xtrtype, $xcbno, $xdeltrtype)<br/>\n";
    }
    // $xtrtype brcb|brinv => 1;
    // $xtrtype msinv => 3;
    // $xtrtype sccb => 2;
    $xtrdt="$year1-$month1-$date1";
    if ($xrowh['xpaytrtype']=='SCB') {
      $xtrdt=fdate_format4("$year1-$month1-$date1", 1);
		}

    if ($xrowh['xpaytrtype']=='2' && (check_level_ex($xrowh['loccd'])=='MS')) {
      $xtrdt=fdate_format4("$year1-$month1-$date1", 1);
    }

    $xmemcode=$xrowh["code"];
    if ($xrowh['xpaytrtype']=='BSP') {
      if (preg_match('/\-/', $xrowh['staff_name'])) {
        $xarr_key = preg_split('/\-/', $xrowh['staff_name']);
        $xmemcode = trim($xarr_key[0]);
      }
    }

    //$path1 = "https://$epoint_server".$ewallet_application."/transaction/refundconfirm.php?".
    $path1 = "https://$epoint_server"."/$epoint_dir/refundconfirm.php?".
      "remark=".urlencode($xcbno).
      "&amt=".urlencode($xarr_ew_info["amount"])."&ewtrxno=".urlencode($xarr_ew_info["ewtrxno"]).
      "&sccode=".urlencode($xrowh["loccd"]).
      "&memcode=".urlencode($xmemcode)."&date=".urlencode("$xtrdt".' '.substr($time1, 0, 8))."&opnm=".urlencode($opnm).
      "&fromsys=1&txt_seller_statdeposit=$txt_seller_statdeposit&txt_buyer_statdeposit=$txt_buyer_statdeposit";

    //if ($xrowh['xpaytrtype']=='2') {
      $xloccd_level=check_level_ex($xrowh["loccd"]);
    //}

    //if ($xrowh['xpaytrtype']=='BINV'){
//     if ($xrowh['xpaytrtype']=='2' && $xloccd_level=='BR') {
//       $path1.="&trtype=2";
//       $path1.="&ivtrtype=1";
//     //}elseif ($xrowh['xpaytrtype']=='MINV'){
//     }elseif ($xrowh['xpaytrtype']=='2' && $xloccd_level=='MS') {
//       $path1.="&trtype=2";
//       $path1.="&ivtrtype=2";
//     }else{
//       $path1.="&trtype=".$xrowh['xpaytrtype'];
//     }
/*
		if ($xloccd_level=='BR') {
      $path1.="&trtype=BEC";
    }elseif ($xloccd_level=='MS') {
      $path1.="&trtype=MSEC";
    }elseif ($xloccd_level=='SC') {
      $path1.="&trtype=SEC";
    }
*/
		if ($xdeltrtype=='MSINV') {
			$path1.="&trtype=2&ivtype=2";
		}elseif ($xdeltrtype=='BINV') {
			$path1.="&trtype=2&ivtype=1";
		}elseif ($xdeltrtype=='SEC') {
			if ($xloccd_level=='MS') {
				$path1.="&trtype=MSEC";
			}else{
				$path1.="&trtype=SEC";
			}
		}else{
		  if ($xdeltrtype=='SCB') {
		    if ($xloccd_level=='MS')
		      $path1.="&trtype=MSCB";
		    else
		      $path1.="&trtype=SCB";
		  }else{
				$path1.="&trtype=$xdeltrtype";
			}
		}


//     if ($debug) {
//       echo "$path1<br/>\n";
//     }else{
        $ch = curl_init();
        //if($_SERVER["SERVER_NAME"]!="192.168.101.130")
        curl_setopt($ch, CURLOPT_REFERER, 'https://mlm1.dxn2u.com');
        curl_setopt($ch, CURLOPT_PORT , $epoint_port);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $path1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $xepoint_result=curl_exec($ch);
        if ($debug) echo "$path1<br/>xepoint_result=$xepoint_result<br/>";
        curl_close($ch);
        return $xepoint_result;
//     }
  }//end function fepoint_refundposting

  function  fepoint_checking_result($xepoint_result, $xurl) {
		global $db, $debug;
    return true;
/*
    if (preg_match('/sukses/i',$xepoint_result)) {
// 			if ($debug) {
// 				echo "$xepoint_result<br>";
// 				pg_exec($db, 'rollback');
// 				exit;
// 			}
      return true;
    }else{
			if ($debug) {
				echo("location: $xurl?msg=".urlencode('Sorry, e-Point transaction failed !'));
			}else{
				header("location: $xurl?msg=".urlencode('Sorry, e-Point transaction failed !'));
			}
			pg_exec($db, 'rollback');
      exit;
    }
*/
  }

  function js_epoint_checkbal_b4submit($xtmp_trcd){
		global $xmodule_name, $txt_key, $epoint_server, $epoint_dir, $txt_keyid, $epo_input, $xfull_url, $xloccd;
/*
?>
						if (shortcut.ew_no.value!='') {
						<?

								//$path1 = "../module/epoint_payment_checkbal.php";
								$path1 = "https://$epoint_server"."/$epoint_dir/payment_checkbal.php";

								if ($xmodule_name=='brcr' || $xmodule_name=='sccr') {
									$xarr_code=preg_split('/\-/', $txt_key);
									$txt_key  =trim($xarr_code[0]);
								}
						?>
								var xurl = '<?=$path1?>?po_no='+shortcut.ew_no.value+'&member_code=<?=$txt_key?>';
								if (shortcut.txt_epointvalue_fieldname.value!='')
								  xurl+='&camount='+shortcut.elements[shortcut.txt_epointvalue_fieldname.value].value;
								console.log('test continuePressedNew '+xurl);
								new Ajax.Request(xurl, {
									method: 'GET',
									onCreate: function(request) {
										request.transport.setRequestHeader = Prototype.emptyFunction;
										//$('overlay').show();
									},
									onSuccess: function(transport) {
										var xstring = transport.responseText;
										var xarray  = xstring.split('|');
										//checking balance
										if (xarray[0]=='proceed' && shortcut.ew_no.value==xarray[1]) {
											//return true;
											shortcut.cmd.value="masuk";
											shortcut.submit();
										}else{
											shortcut.btnAdd.disabled=false;
											document.getElementById('id_msg').innerHTML='Available balance of <?=$txt_key?> is not enough!';
											return false;
										}
									}
								});
* /
							fform_epoint_checkbal();
            }else{
              //shortcut.btnAdd.disabled=false;
							//shortcut.cmd.value="masuk";
							//shortcut.submit();
							fcheck_qty_before_submit();
            }//end if
<?
*/
?>
		function fform_epoint_checkbal() {
		  var shortcut=document.frm_invcnt_fltr;
<?
// 			$path1 = "../module/epoint_payment_checkbal.php";
			$path1 = "https://$epoint_server"."/$epoint_dir/payment_checkbal.php";
			$path1_cancel = "https://$epoint_server"."/$epoint_dir/payment_cancel.php";

			if ($xmodule_name=='brcr' || $xmodule_name=='sccr') {
				$xarr_code=preg_split('/\-/', $txt_key);
				$txt_key  =trim($xarr_code[0]);
			}
			echo "//$xmodule_name $txt_key\n";
			$txt_key=preg_replace('/\'/', '', $txt_key);
?>
			var xurl = '<?=$path1?>';
			var xparam='po_no='+shortcut.ew_no.value+'&member_code=<?=$txt_key?>';
			if (shortcut.txt_epointvalue_fieldname.value!='')
				xparam+='&camount='+shortcut.elements[shortcut.txt_epointvalue_fieldname.value].value;
			if (shortcut.encash_ew_no.value!='') {
				xparam+='&po_no_enc='+shortcut.encash_ew_no.value+'&eamount='+shortcut.encash_amt.value;
			}
			xparam+='&xmodule_name=<?=$xmodule_name?>&xloccd=<?=$xloccd?>';
			//console.log('test continuePressedNew global4'+xurl);
			///if (!confirm('checking bal')) {
			  var xstring ='';
				new Ajax.Request(xurl+'?'+xparam, {
					method: 'GET',
					onCreate: function(request) {
						request.transport.setRequestHeader = Prototype.emptyFunction;
						//$('overlay').show();
					},
					onSuccess: function(transport) {
						xstring = transport.responseText;
						var xarray  = xstring.split('|');
						//checking balance
						if (xarray[0]=='proceed' && shortcut.ew_no.value==xarray[1]) {
							//return true;
							//shortcut.cmd.value="masuk";
							//shortcut.submit();
							fform_submit();
						}else{
						  epo_reset('<?=$epo_input?>');
							foverlay_confirm_hide();
							$('overlay1').hide();
							shortcut.btnAdd.disabled=false;
							shortcut.btnBACK.disabled=false;
							document.getElementById('id_msg').innerHTML='Processing epoint failed !';
							return false;
						}

//							fform_submit();
					},
/*
					onFailure: function(transport) {
						  epo_reset('<?=$epo_input?>');
							foverlay_confirm_hide();
							$('overlay1').hide();
							shortcut.btnAdd.disabled=false;
							shortcut.btnBACK.disabled=false;
							document.getElementById('id_msg').innerHTML='Processing epoint failed !';

							var xurl = '<?=$path1_cancel?>';
							var xparam='?memcode=<?=$xcode?>&rollback_flag=1';
							xparam+='&ewno='+shortcut.ew_no.value;

							if (shortcut.txt_epointvalue_fieldname.value!='') {
								xparam+='&amt='+shortcut.elements[shortcut.txt_epointvalue_fieldname.value].value;
							}

							if (shortcut.encash_ew_no.value!='') {
								xparam+='&ewno_enc='+shortcut.encash_ew_no.value+'&amt_enc='+shortcut.encash_amt.value;
							}

							new Ajax.Request(xurl+xparam, {
								method  : 'GET',
								onSuccess: function(transport) {
									return true;
								}
							});

							return false;
					}
					,
*/
					onComplete: function(transport) {
					  var xfull_url='<?=$xfull_url?>/module/epointlog.php';
						new Ajax.Request(xfull_url, {
							method  : 'POST',
							postBody: 'xmember_code=<?=$txt_key?>&xloccd=<?=$xloccd?>&xurl='+xurl+'&'+xparam+'&xresult='+xstring+'&xmodule_name=<?=$xmodule_name?>',
							asynchronous: true,
							onSuccess: function(transport) {
							  return true;
							}
						});
					}

				});
			//}
    }//end fform_epoint_checkbal
<?
  }


  function fget_info_detail($xmodule_name, $xloccd, $xprdcd, $xcr_mode='') {
    global $debug, $db;
    if ($debug) echo "fget_info_detail($xmodule_name, $xloccd, $xprdcd)<br/>\n";
    $xarr_result =array();
		$xlcd_level = check_level_ex($xloccd);
		if ($xlcd_level=='BR') {
			$xregion = chkregion($xloccd,1);
		}elseif ($xlcd_level=='MS' || $xlcd_level=='SC') {
			$xregion = chkregion($xloccd,0);
		}
		if ($debug) echo "region=$xregion xlcd_level=$xlcd_level<br/>\n";

    if (preg_match('/brcb|sccb|sccr/', $xmodule_name)) {
			$xfield_str = "a.pv as xpv, a.bv as xbv, a.dp as xdp, a.cp as xcp, a.dp_excl as xdp_excl, a.dp_vat as xdp_vat ";
			if($xregion=="WEST") {
				$xfield_str = "a.wpv as xpv, a.wbv as xbv, a.wdp as xdp, a.wcp as xcp, a.wdp_excl as xdp_excl, a.wdp_vat as xdp_vat ";
			}
			$xarr_result=db_select_record($xfield_str, 'msprd a', "where a.prdcd='$xprdcd' ");
			if ($xarr_result['xdp_vat']==0) $xarr_result['xdp_excl']=$xarr_result['xdp'];
      $xarr_result['xunit_price']=$xarr_result['xdp'];
      $xarr_result['xexcl_price']=$xarr_result['xdp_excl'];
      $xarr_result['xvat_price'] =$xarr_result['xdp_vat'];
    }if (preg_match('/inv/', $xmodule_name)) {
			$xfield_str = "a.pv as xpv, a.bv as xbv, a.stp as xstp, a.cp as xcp, a.stp_excl as xstp_excl, a.stp_vat as xstp_vat ";
			if($xregion=="WEST") {
				$xfield_str = "a.wpv as xpv, a.wbv as xbv, a.wstp as xstp, a.wcp as xcp, a.wstp_excl as xstp_excl, a.wstp_vat as xstp_vat ";
			}
			$xarr_result=db_select_record($xfield_str, 'msprd a', "where a.prdcd='$xprdcd' ");
			if ($xarr_result['xstp_vat']==0) $xarr_result['xstp_excl']=$xarr_result['xstp'];
      $xarr_result['xunit_price']=$xarr_result['xstp'];
      $xarr_result['xexcl_price']=$xarr_result['xstp_excl'];
      $xarr_result['xvat_price'] =$xarr_result['xstp_vat'];
    }if (preg_match('/brcr/', $xmodule_name)) {
/*
			$price = "a.sp,a.sp";
			if($region=="WEST")	$price = "a.wsp,a.wsp";
			if ($SR=="1") {
				$price = "a.stp,a.stp";
				if($region=="WEST")	$price = "a.wstp,a.wstp";
			}
			if ($CR=="1") {
				$price = "a.cp,a.cp";
				if($region=="WEST")	$price = "a.wcp,a.wcp";
			}
			if ($MR=="1") {
				$price = "a.dp,a.dp";
				if($region=="WEST")	$price = "a.wdp,a.wdp";
			}
*/
			$xfield_str = "a.sp as xdp, a.sp_excl as xdp_excl, a.sp_vat as xdp_vat ";
			if($xregion=="WEST") {
				$xfield_str = "a.wsp as xdp, a.wsp_excl as xdp_excl, a.wsp_vat as xdp_vat ";
			}
			if ($xcr_mode=="CR") {
				$xfield_str = "a.cp as xdp, a.cp_excl as xdp_excl, a.cp_vat as xdp_vat ";
				if($xregion=="WEST") {
					$xfield_str = "a.wcp as xdp, a.wcp_excl as xdp_excl, a.wcp_vat as xdp_vat ";
				}
			}
			if ($xcr_mode=="MR") {
				$xfield_str = "a.dp as xdp, a.dp_excl as xdp_excl, a.dp_vat as xdp_vat ";
				if($xregion=="WEST") {
					$xfield_str = "a.wdp as xdp, a.wdp_excl as xdp_excl, a.wdp_vat as xdp_vat ";
				}
			}
			if ($xcr_mode=="SR") {
				$xfield_str = "a.stp as xdp, a.stp_excl as xdp_excl, a.stp_vat as xdp_vat ";
				if($xregion=="WEST") {
					$xfield_str = "a.wstp as xdp, a.wstp_excl as xdp_excl, a.wstp_vat as xdp_vat ";
				}
			}

			$xarr_result=db_select_record($xfield_str, 'msprd a', "where a.prdcd='$xprdcd' ");
			if ($xarr_result['xdp_vat']==0) $xarr_result['xdp_excl']=$xarr_result['xdp'];
      $xarr_result['xunit_price']=$xarr_result['xdp'];
      $xarr_result['xexcl_price']=$xarr_result['xdp_excl'];
      $xarr_result['xvat_price'] =$xarr_result['xdp_vat'];
    }


    //$xarr_result['xregion']=$xregion;
    return $xarr_result;
  }

	function fcalc_package_converstion($prdcode, $whid, $xqty) {
		global $db, $debug, $xarr_inloc;
		//if ($debug) echo "fcalc_package_converstion($prdcode,$whid);<br/>\n";
		$spoons = 0;
		$bigx = 0;
		$prdcode = trim($prdcode);
		$xvalid  =true;
		$xmessage='';
		$querrp=
			"select trim(inv_prdcd),inv_qty from msprd_items
			where trim(prdcd)='$prdcode' order by inv_prdcd;";
// 		if ($debug) echo "$querrp<br/>\n";
		$resultp=pg_query($db,$querrp);
		if (pg_numrows($resultp)>0) {
			for($mx=0;$mx<pg_numrows($resultp);$mx++) {
				$rowp=pg_fetch_row($resultp, $mx);
				$xinv_qty=$rowp[1];

				if (!isset($xarr_inloc[$rowp[0]])) {
					$querrpx=
						"select qoh from inloc where trim(prdcd)='$rowp[0]' and loccd='$whid';";
// 					if ($debug) {
// 						echo "$querrpx<br/>\n";
// 					}
					$resultpx=pg_query($db,$querrpx);
					if (pg_numrows($resultpx)>0) {
						$rowpx=pg_fetch_row($resultpx, 0);
						//$xinloc	= $rowpx[0]/$xinv_qty;
						$xarr_inloc[$rowp[0]]=$rowpx[0];
// 						if ($debug) {
// 							print_r($rowpx);
// 							echo "<br/>\n";
// 						}
					}else{
						//$xinloc	= 0;
						$xarr_inloc[$rowp[0]]=0;
					}
				}
				//print_r($xarr_inloc);
				//echo "<br/>\n";
				$xarr_inloc[$rowp[0]]-=$xqty*$xinv_qty;
				//print_r($xarr_inloc);
				//echo "<br/>\n";
/*
				$querrpx=
					"select qoh from inloc where trim(prdcd)='$rowp[0]' and loccd='$whid';";
				$resultpx=pg_query($db,$querrpx);
				if (pg_numrows($resultpx)>0) {
					$rowpx=pg_fetch_row($resultpx, 0);
					$xinloc	= $rowpx[0]/$xinv_qty;
				}else{
					$xinloc	= 0;
				}
*/
// 				$xinloc	= $xarr_inloc[$rowp[0]]/$xinv_qty;
// 				if ($mx==0) {
// 					$spoons=$xinloc;
// 				}else{
// 					if ($xinloc<$spoons) $spoons=$xinloc;
// 				}
        if ($xarr_inloc[$rowp[0]]<0) {
          $xvalid  =false;
          $xmessage="QOH of {$rowp[0]} not enough";
          break;
        }
			}//end for
		}
// 		$spoons = round_down($spoons);
//
// 		if (pg_numrows($resultp)>0) {
// 			for($mx=0;$mx<pg_numrows($resultp);$mx++) {
// 				$rowp=pg_fetch_row($resultp, $mx);
// 				//if ($debug) echo "$rowp[0] {$xarr_inloc[$rowp[0]]} $spoons<br/>";
// 				//$xarr_inloc[$rowp[0]]-=$spoons;
// 				//if ($debug) echo "$rowp[0] {$xarr_inloc[$rowp[0]]} $spoons*<br/>";
// 			}//end for
// 		}
    $xarr_result['xstatus'] =$xvalid;
    $xarr_result['xmessage']=$xmessage;
		return $xarr_result;
	}//end 	fcalc_package_converstion

function fencrypt($xsrc) {
  $key = pack('H*',   "55cd22a123456aca");
	$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
	$iv = 'a42defa12345bceb';//mcrypt_create_iv($iv_size, MCRYPT_RAND);
	$ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $xsrc, MCRYPT_MODE_CBC, $iv);
  $ciphertext = $iv . $ciphertext;
  $ciphertext_base64 = base64_encode($ciphertext);
  return $ciphertext_base64;
}

function fdecrypt($xtgt) {
  $key = pack('H*',   "55cd22a123456aca");
	$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
	$iv = 'a42defa12345bceb';//
	$ciphertext_dec = base64_decode($xtgt);
	$iv_dec = substr($ciphertext_dec, 0, $iv_size);
	$ciphertext_dec = substr($ciphertext_dec, $iv_size);
	$plaintext_dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);
	return $plaintext_dec;
}

function ferp_sync_process($xdata) {// trtype='1'
  global $db, $debug;
        $xdsql="select distinct po_no from tmpgrntrd where grn_no='".$xdata['grn_no']."'";
    if ($debug) echo "<b>$xdsql</b><br/>\n";
        $xdres=pg_query($db, $xdsql);
        $xsql_inloc='';
        $_insert_into_inventory=true;
        $xpo_no='';
        for ($z=0; $z<pg_num_rows($xdres); $z++) {
          $xdrow=pg_fetch_assoc($xdres, $z);
          if ($xpo_no=='')
            $xpo_no=$xdrow['po_no'];
          else{
            if (!preg_match('/'.$xdrow['po_no'].'/i', $xpo_no))
              $xpo_no.=','.$xdrow['po_no'];
          }
        } //end for
        pg_free_result($xdres);
        //echo $xdata['grn_no']." $xpo_no <br/>";

        $xtrdt=substr($xdata["grn_date"],0,10);
        if ($xtrdt=='') $xtrdt="null";
        else $xtrdt="'$xtrdt'";

        $xparam_trdt=substr($xdata["grn_date"],0,10);
        if ($xtrdt=="null") $xparam_trdt=date('Y-m-d');

////////////////////////
        $xcsql="select trdocd from dodistrh where invno='".$xdata['grn_no']."'";
        $xcres=pg_query($db, $xcsql);
        if (pg_num_rows($xcres)==0) {
          $xdo_no=fgen_erpdoin($xparam_trdt);

          $xtrdocd=$xdo_no.'#ES#'.$xdata["supp_id"];

          if ($debug) echo "$xtrdocd<br/>\n";

          $xpsql="INSERT INTO dodistrh
            (trdocd,trtype,code,bc_id,loccd,
            trdt,etdt,totpay,tdp,ndp,
            tpv,npv,tbv,nbv,pdisc,
            ptax,createnm,opnm,status,post,
            flag,note2,note3,invno,idvendor,
            do_no,trtm,note1)
            VALUES
            ('$xtrdocd','3','ES','".ID_CNT."','DDEPT',
            $xtrdt,now(),0,0,0,
            0,0,0,0,0,
            0,'$opnm','$opnm','1','0',
            '1','".$xdata['grn_no']."','".$xdata["supp_id"]."','".$xdata['grn_no']."','".$xdata["supp_id"]."',
            '$xdo_no',now(),'auto sync at ".date('d/m/Y H:i')."');";
          if ($debug)
            echo $xpsql.'<br/>';
          //else
          pg_exec($db, $xpsql);

          //$xdsql="select distinct prd_cd, po_no, qty from tmpgrntrd where grn_no='".$xdata['grn_no']."'";
          $xdsql="select prd_cd, po_no, qty from tmpgrntrd where grn_no='".$xdata['grn_no']."'";
          $xdres=pg_query($db, $xdsql);
          $xsql_inloc='';
          $_insert_into_inventory=true;
          $xpo_no='';
          for ($z=0; $z<pg_num_rows($xdres); $z++) {
            $xdrow=pg_fetch_assoc($xdres, $z);
            if ($xpo_no=='')
              $xpo_no=$xdrow['po_no'];
            else{
              if (!preg_match('/'.$xdrow['po_no'].'/', $xpo_no))
                $xpo_no.=','.$xdrow['po_no'];
            }
            $xcdsql="select prdcd from msprd where prdcd='".$xdrow["prd_cd"]."'";
            $xcdres=pg_query($db, $xcdsql);
            // if exist on msprd then insert detail item and update stock
            if (pg_num_rows($xcdres)>0) {
              if ($xdstatus=='' || $xdstatus==0)
                $xdstatus=1;
              $xsql_inloc.=sql_update_inloc_basic($xdrow['prd_cd'],$xdrow['qty'],$debug,'DDEPT',0,'+');
            }else{
              $xdstatus=-1;//false
              $_insert_into_inventory=false;
            }
            $xpcdsql="insert into dodistrd (trdocd, prdcd, qty, from_no) ".
              "values ".
              "('$xtrdocd', '".$xdrow['prd_cd']."', ".$xdrow['qty'].", 'ES');";
            if ($debug)
              echo $xpcdsql.'<br/>';
            //else
            pg_exec($db, $xpcdsql);
          } //end for

          $xpsql="update dodistrh set invno='$xpo_no' where trdocd='$xtrdocd'";
          if ($debug)
            echo $xpsql.'<br/>';
          else
            pg_exec($db, $xpsql);


          if ($_insert_into_inventory==true) {
            if ($debug)
              echo $xsql_inloc.'<br/>';
            //else
              pg_exec($db, $xsql_inloc);
            $_xsts=3;
          }
          if ($_insert_into_inventory==false)
            $_xsts=5;

          $xpsql="update tmpgrntrh set sts=$_xsts where grn_no='".$xdata['grn_no']."'";
          if ($debug)
            echo $xpsql.'<br/>';
          //else
          pg_exec($db, $xpsql);

          $xpsql="update dodistrh set trtype=$_xsts where trdocd='$xtrdocd'";
          if ($debug)
            echo $xpsql.'<br/>';
          //else
            pg_exec($db, $xpsql);

        }else{
          $xmsg="GRN No. '".$xdata['grn_no']."' already exist !";
        }
}//end function ferp_sync_process

function ferp_sync_process2($xdata) {// trtype='2'
  global $db, $debug, $opnm;
    $xdsql="select distinct po_no from tmpgrntrd where grn_no='".$xdata['grn_no']."'";
    if ($debug) echo "$xdsql<br/>\n";

        $xdres=pg_query($db, $xdsql);
        $xsql_inloc='';
        $_insert_into_inventory=true;
        $xpo_no='';
        for ($z=0; $z<pg_num_rows($xdres); $z++) {
          $xdrow=pg_fetch_assoc($xdres, $z);
          if ($xpo_no=='')
            $xpo_no=$xdrow['po_no'];
          else{
            if (!preg_match('/'.$xdrow['po_no'].'/i', $xpo_no))
              $xpo_no.=','.$xdrow['po_no'];
          }
        } //end for
        pg_free_result($xdres);
        //echo $xdata['grn_no']." $xpo_no <br/>";

        $xtrdt=substr($xdata["grn_date"],0,10);
        if ($xtrdt=='') $xtrdt="null";
        else $xtrdt="'$xtrdt'";

        $xparam_trdt=substr($xdata["grn_date"],0,10);
        if ($xtrdt=="null") $xparam_trdt=date('Y-m-d');

////////////////////////

        //$xcsql="select trdocd from dodistrh where invno='".$xdata['grn_no']."'";
        $xcsql="select trdocd from dodistrh where note2 like '%".$xdata['grn_no']."%'";

        if ($debug) echo "$xcsql<br/>\n";
        $xcres=pg_query($db, $xcsql);
        if (pg_num_rows($xcres)==0) {
          //$xdo_no=fgen_erpdoin($xparam_trdt);
          //$xtrdocd=$xdo_no.'#ES#'.$xdata["supp_id"];


          $year2=substr($xparam_trdt, 2,2);
          $xmonth1 =substr($xparam_trdt, 5,2);
          $xdate1  =substr($xparam_trdt, 8,2);

          $xsql="select max(trdocd) as xmax from dodistrh ".
            "where  ".
            "trdocd like 'ERP%' and code='SD' ".//
            "and trdt='$xparam_trdt'";
          if ($debug) echo "$xsql<br/>";
          $xres1=pg_query($db, $xsql);
          //if ($db1->next_record()) {
          if (pg_num_rows($xres1)>0) {
            //$xtrdocd=$db1->f("xmax");
            $xrow1=pg_fetch_assoc($xres1, 0);
            //$xtrdocd=$db1->f("xmax");
            $xtrdocd=$xrow1["xmax"];
            if ($debug) echo "max xtrdocd=$xtrdocd*<br/>";
            //$xtrdocd="ERP09102902#ES#";
            if ($xtrdocd=='') {
              $xresult='ERP-PL'.$year2.$xmonth1.$xdate1.'0001';
            }else{
              //0123456789012345
              //ERP-PL1503260001#SD#
              $xtrdocd=substr($xtrdocd, 12, 4);
              $xtrdocd=$xtrdocd+1;
              $xresult='ERP-PL'.$year2.$xmonth1.$xdate1.str_pad($xtrdocd, 4, '0', STR_PAD_LEFT);
            }
          }else{
            $xresult='ERP-PL'.$year2.$xmonth1.$xdate1.'0001';
          }
          $xtrdocd=$xresult;
          if ($debug) echo "new xtrdocd=$xtrdocd<br/>\n";

          $xpsql="INSERT INTO dodistrh
            (trdocd,trtype,code,bc_id,loccd,
            trdt,etdt,totpay,tdp,ndp,
            tpv,npv,tbv,nbv,pdisc,
            ptax,createnm,opnm,status,post,
            flag,note2,note3,invno,idvendor,
            do_no,trtm,note1)
            VALUES
            ('$xtrdocd','3','SD','".ID_CNT."','DDEPT',
            $xtrdt,now(),0,0,0,
            0,0,0,0,0,
            0,'$opnm','$opnm','1','0',
            '1','".$xdata['grn_no'].", ".$xpo_no."','".$xdata["supp_id"]."','','".$xdata["supp_id"]."',
            '$xtrdocd',now(),'auto sync at ".date('d/m/Y H:i')."');";//".$xdata['grn_no']."
          if ($debug)
            echo $xpsql.'*<br/>';
          //else
          pg_exec($db, $xpsql);

          $xdsql="select prd_cd, po_no, qty from tmpgrntrd where grn_no='".$xdata['grn_no']."'";
          if ($debug)
            echo $xdsql.'<br/>';
          $xdres=pg_query($db, $xdsql);
          $xsql_inloc='';
          $_insert_into_inventory=true;
          $xpo_no='';
          for ($z=0; $z<pg_num_rows($xdres); $z++) {
            $xdrow=pg_fetch_assoc($xdres, $z);
            if ($xpo_no=='')
              $xpo_no=$xdrow['po_no'];
            else{
              if (preg_match('/'.$xdrow['po_no'].'/', $xpo_no))
                $xpo_no.=','.$xdrow['po_no'];
            }
            $xcdsql="select prdcd from msprd where prdcd='".$xdrow["prd_cd"]."'";
            $xcdres=pg_query($db, $xcdsql);
            // if exist on msprd then insert detail item and update stock
            if (pg_num_rows($xcdres)>0) {
              if ($xdstatus=='' || $xdstatus==0)
                $xdstatus=1;
              $xsql_inloc.=sql_update_inloc_basic($xdrow['prd_cd'],$xdrow['qty'],$debug,'DDEPT',0,'+');
            }else{
              $xdstatus=-1;//false
              $_insert_into_inventory=false;
            }
            $xpcdsql="insert into dodistrd (trdocd, prdcd, qty, from_no) ".
              "values ".
              "('$xtrdocd', '".$xdrow['prd_cd']."', ".$xdrow['qty'].", 'SD');";
            if ($debug)
              echo $xpcdsql.'<br/>';
            //else
            pg_exec($db, $xpcdsql);
          } //end for

//           $xpsql="update dodistrh set invno='$xpo_no' where trdocd='$xtrdocd'";
//           if ($debug)
//             echo $xpsql.'<br/>';
//           else
//             pg_exec($db, $xpsql);


          if ($_insert_into_inventory==true) {
            if ($debug)
              echo $xsql_inloc.'<br/>';
            //else
              pg_exec($db, $xsql_inloc);
            $_xsts=3;
          }
          if ($_insert_into_inventory==false)
            $_xsts=5;

          $xpsql="update tmpgrntrh set sts=$_xsts where grn_no='".$xdata['grn_no']."'";
          if ($debug)
            echo $xpsql.'<br/>';
          //else
          pg_exec($db, $xpsql);

          $xpsql="update dodistrh set trtype=$_xsts where trdocd='$xtrdocd'";
          if ($debug)
            echo $xpsql.'<br/>';
          //else
            pg_exec($db, $xpsql);

        }//end if

}//end ferp_sync_process2

  function fepoint_checking_prnx($xsession_name, $xurlback, $xtmp_table, $xtmp_field, $tmp_trcd) {
    global $db, $xepoint_cancel, $next, $txt_key, $debug;
    if ($debug) {
      echo "fepoint_checking_prnx($xsession_name, $xurlback, $xtmp_table, $xtmp_field, $tmp_trcd)<br>";
      echo "xepoint_cancel=$xepoint_cancel, next=$next, txt_key=$txt_key, debug=$debug<br/>";
      echo "opnm=".$_SESSION['opnm']."<br/>\n";
    }
		if (!isset($_SESSION['opnm'])) {
			$xepoint_cancel=true;
		}

// 		if ($next==1) {
// 			$_SESSION[$xsession_name]='';
// 		}
// 		if ($next==2 || $next==3) {

			//at payment_page
// 			if ($_SESSION[$xsession_name]==1) {
// 				$xepoint_cancel=true;
// 			}else{
// 				$_SESSION[$xsession_name]=1;
// 			}
    //check logout
		if (!isset($_SESSION['opnm'])) {
			$sql_mstrh=
				"select a.*
				from $xtmp_table a
				where a.$xtmp_field='$tmp_trcd' ";
			if ($debug) {
			  echo "$sql_mstrh*<br/>\n";
			}
			$res_mstrh=pg_query($db, $sql_mstrh);
			if (pg_num_rows($res_mstrh)>0) {
				$xepoint_msg_error='../logout.php?msg=';
				$row_mstrh = pg_fetch_array($res_mstrh, 0);
				if ($row_mstrh['ewtrxno']!='' && $row_mstrh['saved']!='1') {
// 					db_update2($xtmp_table, array('rollback'=>'1'), array($xtmp_field=>$tmp_trcd));
					fepoint_rollback($row_mstrh['ewtrxno'], $row_mstrh['ewtrx_amt'], $row_mstrh['code'], $xepoint_msg_error, 2);
					if ($debug) {
						echo "$xpsql<br/>";
						exit;
					}
				}else{
					if ($debug)
						echo("location: $xepoint_msg_error<br/>");
					else
						header("location: $xepoint_msg_error");
				}
				pg_query($db, 'rollback');
				exit;
			}else{
				if ($_POST['ew_no']!='' ) {
// 				db_update2($xtmp_table, array('rollback'=>'2'), array($xtmp_field=>$tmp_trcd));
					fepoint_rollback($_POST['ew_no'], $_POST[$_SESSION['xepo_input']], $txt_key, $xepoint_msg_error, 2);
					if ($debug) {
						echo "$xpsql<br/>";
						exit;
					}
				}else{
					if ($debug)
						echo("location: $xepoint_msg_error<br/>");
					else
						header("location: $xepoint_msg_error");
					exit;
				}
			}
   		exit;
		}

    //check temporary table
		$sql_mstrh=
			"select a.*
			from $xtmp_table a
			where a.$xtmp_field='$tmp_trcd' ";
		if ($debug) {
// 		  print_r($_POST);
			echo "$sql_mstrh*<br/>\n";
//       exit;
		}
		$res_mstrh=pg_query($db, $sql_mstrh);
		if (pg_num_rows($res_mstrh)==0) {
			if ($xsession_name=='brcr' || $xsession_name=='sccr') {
				$xarr_code=preg_split('/\-/', $txt_key);
				$txt_key  =trim($xarr_code[0]);
			}

			$xepoint_msg_error="$xurlback?msg=".mxlang('1567');
			if ($_POST['ew_no']!='') {
				fepoint_rollback($_POST['ew_no'], $_POST[$_SESSION['xepo_input']], $txt_key, $xepoint_msg_error, 2);
				if ($debug) {
					echo "$xpsql<br/>";
					exit;
				}
			}else{
				if ($debug)
				  echo("location: $xepoint_msg_error<br/>");
				else
 				  header("location: $xepoint_msg_error");
				exit;
			}
		}
		/*
			$sql_mstrh=
				"select a.*
				from $xtmp_table a
				where a.$xtmp_field='$tmp_trcd' ";
			if ($debug) {
			  echo "$sql_mstrh<br/>\n";
			}
			$res_mstrh=pg_query($db, $sql_mstrh);
			if (pg_num_rows($res_mstrh)>0) {
				$row_mstrh = pg_fetch_array($res_mstrh, 0);
				if ($row_mstrh['ewtrxno']!='') {
				  if ($xepoint_cancel) {
						$xepoint_msg_error='../logout.php?msg=';
// 					if ($_SESSION[$xsession_name]==1){
// 						$xepoint_msg_error="$xurlback?msg=".mxlang('1564');
// 					}
						fepoint_rollback($row_mstrh['ewtrxno'], $row_mstrh['ewtrx_amt'], $row_mstrh['code'], $xepoint_msg_error, 2);
					}
				}
			}else{//end if
				$xepoint_msg_error="$xurlback?msg=".mxlang('1567');
				if ($_POST['ew_no']!='') {
					fepoint_rollback($_POST['ew_no'], $_POST[$_SESSION['xepo_input']], $txt_key, $xepoint_msg_error, 2);
				}else{
					header("location: $xepoint_msg_error");
					exit;
				}

			}
			*/
// 		}//end if next2

  }//end function

  function fepoint_checking_prnx2($xsession_name, $xurlback, $xtmp_table, $xtmp_field, $tmp_trcd) {
    global $db, $xepoint_cancel, $next, $txt_key, $debug, $txt_encash_contra, $opnm;
    if ($debug) {
      echo "fepoint_checking_prnx2($xsession_name, $xurlback, $xtmp_table, $xtmp_field, $tmp_trcd)<br>";
      echo "xepoint_cancel=$xepoint_cancel, next=$next, txt_key=$txt_key, debug=$debug<br/>";
      echo "opnm=".$_SESSION['opnm']."<br/>\n";
    }
		if (!isset($_SESSION['opnm'])) {
			$xepoint_cancel=true;
		}

    //check logout
		if (!isset($_SESSION['opnm']) ) {
		  sleep(10);
			$sql_mstrh=
				"select a.*
				from $xtmp_table a
				where a.$xtmp_field='$tmp_trcd' ";
			if ($debug) {
			  echo "$sql_mstrh*<br/>\n";
			}

// 			flogdebug('global4', "fepoint_checking_prnx2 [logout] txt_encash_contra=$txt_encash_contra ");

			$res_mstrh=pg_query($db, $sql_mstrh);
			$xepoint_msg_error='../logout.php?msg=';
			if (pg_num_rows($res_mstrh)>0) {
				$row_mstrh = pg_fetch_array($res_mstrh, 0);
				if ($row_mstrh['ewtrxno']!='' && $row_mstrh['saved']!='1') {
// 					db_update2($xtmp_table, array('rollback'=>'1'), array($xtmp_field=>$tmp_trcd));
					if ($row_mstrh['encash_ewtrxno']) {
						fepoint_rollback2($row_mstrh['ewtrxno'].'#'.$row_mstrh['encash_ewtrxno'], $row_mstrh['ewtrx_amt'].'#'.$row_mstrh['encash_ewtrx_amt'], $row_mstrh['code'], $xepoint_msg_error, 2);
					}else{
						fepoint_rollback2($row_mstrh['ewtrxno'], $row_mstrh['ewtrx_amt'], $row_mstrh['code'], $xepoint_msg_error, 2);
					}
				}
				pg_query($db, 'rollback');
				fout_json_prnx(false, '', $xepoint_msg_error);
			}else{
				if ($_POST['ew_no']!='' ) {
					fepoint_rollback2($_POST['ew_no'], $_POST[$_SESSION['xepo_input']], $txt_key, $xepoint_msg_error, 2);
				}
				fout_json_prnx(false, '', $xepoint_msg_error);
			}
		}

    //check temporary table
		$sql_mstrh=
			"select a.*
			from $xtmp_table a
			where a.$xtmp_field='$tmp_trcd' ";
		$res_mstrh=pg_query($db, $sql_mstrh);
		if ($debug) {
			echo "$sql_mstrh ".pg_num_rows($res_mstrh)."*<br/>\n";
		}

		if (pg_num_rows($res_mstrh)==0) {
			if ($xsession_name=='brcr' || $xsession_name=='sccr') {
				$xarr_code=preg_split('/\-/', $txt_key);
				$txt_key  =trim($xarr_code[0]);
			}

		  if (preg_match('/\?/', $xurlback)) {
				$xepoint_msg_error="$xurlback&msg=".urlencode(mxlang('1567'));
		  }else{
				$xepoint_msg_error="$xurlback?msg=".urlencode(mxlang('1567'));
		  }
			if ($_POST['ew_no']!='') {
				if ($_SESSION['encash_ewno']!='') {
					fepoint_rollback2($_POST['ew_no'].'#'.$_SESSION['encash_ewno'], $_POST[$_SESSION['xepo_input']].'#'.$_SESSION['encash_amt'], $txt_key, $xepoint_msg_error, 2);
				}else{
					fepoint_rollback2($_POST['ew_no'], $_POST[$_SESSION['xepo_input']], $txt_key, $xepoint_msg_error, 2);
				}
			}
			fout_json_prnx(false, mxlang('1567'), $xepoint_msg_error);
		}

  }//end function

  function fepoint_rollback($xew_no, $xew_amt, $xcode, $xepoint_msg_error, $xmode_submit=1) {
    global $db, $epoint_server, $epoint_dir, $debug, $opnm;
    $debug=1;
    if ($debug) {
      echo "opnm=".$_SESSION['opnm']."<br/>\n";
      echo "fepoint_rollback($xew_no, $xew_amt, $xcode, $xepoint_msg_error, $xmode_submit)<br>";
      //exit;
    }
    //$xmode_submit=1 ==> ajax
    //$xmode_submit=2 ==> curl
   //
			//payment rollback :
			//url : payment_cancel
			//parameter : ewno=PO1512150000009-1010&amt=77&memcode=011133014
		//	fepoint_rollback($_POST['ew_no'], $xep_amount, $txt_key);
		//$xstr_error = pg_last_error($db);
/*
		//$path1 = "https://$epoint_server"."/$epoint_dir/payment_cancel.php?".
			"ewno=".urlencode($xew_no)."&amt=".urlencode($xew_amt)."&memcode=$xcode&rollback_flag=1";
		if ($debug) {
      echo "$path1<br/>";
      exit;
		}
		//if ($xstr_error!='') {
			$ch = curl_init();
			//if($_SERVER["SERVER_NAME"]!="192.168.101.130")
			curl_setopt($ch, CURLOPT_REFERER, 'https://mlm1.dxn2u.com');
			curl_setopt($ch, CURLOPT_PORT , $epoint_port);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			//curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_URL, $path1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			$xepoint_result=curl_exec($ch);
			curl_close($ch);
			if ($debug) {
				echo "xepoint_result=$xepoint_result<br/>";
				exit;
			}
*/
			if ($xmode_submit==1) {
				$xepoint_msg_error.="&epoint_rollback=1&ewno=$xew_no&amt=$xew_amt&memcode=$xcode";
				header("Location: $xepoint_msg_error");
				exit;
			}else if ($xmode_submit==2) {
				$path1 = "https://$epoint_server"."/$epoint_dir/payment_cancel.php?".
					"ewno=".urlencode($xew_no)."&amt=".urlencode($xew_amt)."&memcode=$xcode&rollback_flag=1";
				if ($debug) {
					echo "$path1<br/>";
					//exit;
				}
				$ch = curl_init();
				//if($_SERVER["SERVER_NAME"]!="192.168.101.130")
				curl_setopt($ch, CURLOPT_REFERER, 'https://mlm1.dxn2u.com');
				curl_setopt($ch, CURLOPT_PORT , $epoint_port);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				//curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_URL, $path1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				$xepoint_result=curl_exec($ch);
				curl_close($ch);
				if ($debug) {
					echo "xepoint_result=$xepoint_result<br/>\n";
					//exit;
				}
				if ($debug)
				  echo ("Location: $xepoint_msg_error<br/>");
				else
					header("Location: $xepoint_msg_error");
				pg_query($db, 'rollback');
				if ($debug) echo "rollback here<br/>";
				exit;
			}
			exit;
		//}
  }//end if function

  function fepoint_rollback2($xew_no, $xew_amt, $xcode, $xepoint_msg_error, $xmode_submit=1) {
    global $db, $epoint_server, $epoint_dir, $debug, $opnm, $txt_encash_contra;
//     if ($opnm=='hima') $debug=1;
    //sleep()
    if ($debug) {
      print_r($_SESSION);
      echo $_SERVER['HTTP_REFERER']."<br/><br/>\n";
      echo "opnm=".$_SESSION['opnm']."<br/>\n";
      echo "fepoint_rollback2($xew_no, $xew_amt, $xcode, $xepoint_msg_error, $xmode_submit)<br>";
      //exit;
    }
//     flogdebug('global4 fepoint_rollback2', "fepoint_rollback2($xew_no, $xew_amt, $xcode, $xepoint_msg_error, $xmode_submit)");
    $xarr_ew_no=preg_split('/#/', $xew_no);
    if (count($xarr_ew_no)>1) {
      $xew_no=$xarr_ew_no[0];
      $xencash_ew_no=$xarr_ew_no[1];
    }
    $xarr_ew_amt=preg_split('/#/', $xew_amt);
    if (count($xarr_ew_amt)>1) {
      $xew_amt=$xarr_ew_amt[0];
      $xencash_ew_amt=$xarr_ew_amt[1];
    }

			if ($xmode_submit==1) {
				$xepoint_msg_error.="&epoint_rollback=1&ewno=$xew_no&amt=$xew_amt&memcode=$xcode";
				header("Location: $xepoint_msg_error");
				exit;
			}else if ($xmode_submit==2) {
				$path1 = "https://$epoint_server"."/$epoint_dir/payment_cancel.php?".
					"ewno=".urlencode($xew_no)."&amt=".urlencode($xew_amt)."&memcode=$xcode&rollback_flag=1";
				if ($xencash_ew_no!='' && $txt_encash_contra==1) {
					$path1.= "&ewno_enc=$xencash_ew_no&amt_enc=$xencash_ew_amt";
				}else{
// 					$path1.= "&ewno_enc=&amt_enc=";
				}

// 				if ($opnm=='hima') {
// 					flogdebug('global4', "fepoint_rollback2 txt_encash_contra=$txt_encash_contra \n".$path1);
// 				}

				if ($debug) {
					echo "$path1<br/>";
					exit;
				}
				$ch = curl_init();
				//if($_SERVER["SERVER_NAME"]!="192.168.101.130")
				curl_setopt($ch, CURLOPT_REFERER, 'https://mlm1.dxn2u.com');
				curl_setopt($ch, CURLOPT_PORT , $epoint_port);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				//curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_URL, $path1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				$xepoint_result=curl_exec($ch);
				curl_close($ch);
				if ($debug) {
					echo "xepoint_result=$xepoint_result<br/>\n";
					//exit;
				}
			}
		//}
  }//end if function

  function fepoint_posting($xew_no, $xew_amt, $xcode) {
    global $db, $epoint_server, $epoint_dir, $newTransId, $newTransIdx, $debug, $year1, $month1, $date1, $time1,
			$encash_ew_no, $encash_amt;
    if ($debug) {
      echo "opnm=".$_SESSION['opnm']."<br/>\n";
      echo "fepoint_posting($xew_no, $xew_amt, $xcode)<br>";
			echo "$epoint_server, $epoint_dir, $newTransId, $debug, $year1, $month1, $date1, $time1<br/>";
      //exit;
    }

		$data  = "ewno=".urlencode($xew_no)."&cbno=".urlencode($newTransId);
		$data .= "&cbDate=".urlencode("$year1-$month1-$date1 ".substr($time1, 0, 8))."&epMemCode=".urlencode($xcode);
		$data .= "&amt=$xew_amt";
		$data .= "&ewno_enc=$encash_ew_no&amt_enc=$encash_amt";
		$data .= "&cbno_enc=$newTransIdx";
		$port = 443;

		//$path = "https://$epoint_server"."$ewallet_application/transaction/payment_confirm.php?$data";
		$path1 = "https://$epoint_server"."/$epoint_dir/payment_confirm.php?$data";

				if ($debug) {
					echo "$path1<br/>";
					exit;
				}


				$ch = curl_init();
				//if($_SERVER["SERVER_NAME"]!="192.168.101.130")
				curl_setopt($ch, CURLOPT_REFERER, 'https://mlm1.dxn2u.com');
				curl_setopt($ch, CURLOPT_PORT , $epoint_port);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				//curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_URL, $path1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				$xepoint_result=curl_exec($ch);
				if ($debug) {
				  echo "$xepoint_result<br>";
				  exit;
				}
				return $xepoint_result;
			//}
			//exit;
		//}
  }//end if function

	function fdisable_f5_button($xunloading=false) {
	  global $next;
	  //echo ;
/*
?>
		<script type="text/javascript">
		<?
			if ($next==2) {
		?>
			var shortcut = document.frm_invcnt_fltr;
			document.onkeydown=function(e) {
				e=e||window.event;
				if (e.keyCode === 116 ) {//f5
					e.keyCode = 0;
					//alert("This action is not allowed");
					if(e.preventDefault)e.preventDefault();
					else e.returnValue = false;
					return false;
				}
				if (e.keyCode === 82 ) { //R button
					if (e.ctrlKey){
						e.returnValue = false;
						e.keyCode = 0;
						return false;
					}
				}
			}
		<?
				if ($xunloading) {
?>
					window.onbeforeunload = function() {
						return ('Please do not press stop/back button until process finished');
					};
<?
				}
			}
		?>
		</script>
<?
*/
			if ($next==2 || preg_match('/view|encashment/', $_SERVER['PHP_SELF'])) {
?>
		<script type="text/javascript">
			document.onkeydown = function(evt){
				var key;
				var key = (window.event)?event.keyCode:evt.which;
				switch (key){
					case 116 : //F5 button
						//console.log('You are press F5 Button!');
						return false;
					case 82 : //R button
						if (event.ctrlKey){
// 							event.returnValue = false;
// 							event.keyCode = 0;
							return false;
						}
					default:
						return true;
						//alert("keyCode : "+ event.keyCode);
				}
			}
			<?
			if ($xunloading) {
			?>
			window.onbeforeunload = function() {
				return ('Please do not press stop/back button until process finished');
			};
			<?
			}
			?>
		</script>
<?
		}
	}

	function fout_json_prnx($xstatus, $xmessage, $xlocation) {
		$xarr_json_result['xstatus']  =$xstatus;
		$xarr_json_result['xmessage'] =$xmessage;
		$xarr_json_result['xlocation']=$xlocation;
		echo json_encode($xarr_json_result);
		exit;
  }

  function epoint_ewno_exist2($xew_no, $xurlreceipt, $xmodule_name, $xurlback, $tmp_trcd) {
    global $db;//, $debug;
// 		$debug=1;
    sleep(25);
    if (preg_match('/brcb|brcr/', $xmodule_name)) {
      $xtmp_table_name='tmp_mstrh';
      $xtmp_field_name='trcd';
    }elseif (preg_match('/sccb/', $xmodule_name)) {
      $xtmp_table_name='tmp_scivtrh';
      $xtmp_field_name='trivcd';
    }elseif (preg_match('/brinv|msinv|sccr/', $xmodule_name)) {
      $xtmp_table_name='tmp_msivtrh';
      $xtmp_field_name='trivcd';
    }

		$xsql="select * from $xtmp_table_name where $xtmp_field_name='$tmp_trcd' ";

		$xres=pg_query($db, $xsql);
		if ($debug) {
      echo "$xsql".pg_num_rows($xres)."<br>";
		}
		if (pg_num_rows($xres)>0) {
		  $xrow=pg_fetch_assoc($xres, 0);
		  if ($debug) {
		    print_r($xrow);
		    echo "<br/><br/>";
		    print_r($_SESSION);
		    echo "<br/><br/>";
		    //exit;
		  }
		  if ($xrow['saved']==1) {
				$xurlreceipt.='&trxno='.$xrow['trxno'];
				if ($debug) {
					echo("Location: $xurlreceipt");
					exit;
				}else{
					unset($_SESSION['epoint_ewno']);
					unset($_SESSION['epoint_amt']);
					unset($_SESSION['epoint_memcode']);

					unset($_SESSION['epoint_encash_ewno']);
					unset($_SESSION['epoint_encash_amt']);
					header("Location: $xurlreceipt");
					exit;
				}

		  }else{
				sleep(10);
				//$xres2=pg_query($db, $xsql);
				//if (pg_num_rows($xres2)>0) {
					//$xrow2=pg_fetch_assoc($xres2, 0);
				//if  ($xrow2['saved']!=1 && $xrow['ewtrxno']!='') {
					if ($_SESSION['epoint_ewno']!='') {
						$xepoint_msg_error=$xurlback;
						fepoint_rollback2($_SESSION['epoint_ewno'], $_SESSION['epoint_amt'], $_SESSION['epoint_memcode'], $xepoint_msg_error, 2);
						if ($debug) {
							echo("Location: $xurlback");
						}else{
							unset($_SESSION['epoint_ewno']);
							unset($_SESSION['epoint_amt']);
							unset($_SESSION['epoint_memcode']);

							unset($_SESSION['epoint_encash_ewno']);
							unset($_SESSION['epoint_encash_amt']);
							header("Location: $xurlback");
						}
						exit;
					}else{
						header("Location: $xurlback");
						exit;
					}
				//}

		  }
 		  //if ($debug)  exit;

		}//end if
  }

  function epoint_ewno_exist($xew_no, $xurl, $xmodule_name, $xurlback) {
		global $db, $debug, $opnm;
// 		if ($opnm=='hima')
		$debug=1;
    sleep(30);
// 		$xsql=
// 			"select a.trcd, a.trdt, a.code, a.trtype, a.trtm
// 			from newsctrh a, scpayment b
// 			where a.trcd=b.trcd
// 			and (b.paynote1 in ('$xew_no') or b.paynote2 in ('$xew_no') or b.paynote3 in ('$xew_no') or b.paynote4 in ('$xew_no') or b.paynote5 in ('$xew_no'))
// 			union all
// 			select a.trivcd, a.trdt, a.code, a.trtype, a.trtm
// 			from newmsivtrh a, mspayment b
// 			where a.trivcd=b.trivcd
// 			and (b.paynote1 in ('$xew_no') or b.paynote2 in ('$xew_no') or b.paynote3 in ('$xew_no') or b.paynote4 in ('$xew_no') or b.paynote5 in ('$xew_no'))
// 			union all
// 			select a.trcd, a.trdt, a.code, a.trtype, a.trtm
// 			from newmstrh a, mspayment b
// 			where a.trcd=b.trcd
// 			and (b.paynote1 in ('$xew_no') or b.paynote2 in ('$xew_no') or b.paynote3 in ('$xew_no') or b.paynote4 in ('$xew_no') or b.paynote5 in ('$xew_no'));";
// 		$xres=pg_query($db, $xsql);
		if ($debug) {
		  //echo "debug=$debug $xsql ".pg_num_rows($xres)."<br/><br/>";
		  print_r($_SESSION);
		  echo "<br/>\n";
		  exit;
		}
// 		if (pg_num_rows($xres)>0) {

		if ($_SESSION['saved_brcb']!='') {
		  //$xrow=pg_fetch_row($xres, 0);
			$xurl.='&trxno='.$_SESSION['saved_brcb'];
		  if ($debug) {
				echo("Location: $xurl");
				exit;
		  }else{
				header("Location: $xurl");
				exit;
			}
		}else{
		  $xepoint_msg_error=$xurlback;
			fepoint_rollback2($_SESSION['ewno'], $_SESSION['amt'], $_SESSION['memcode'], $xepoint_msg_error, 2);
		  if ($debug) {
				echo("Location: $xurlback");
				exit;
		  }else{
				unset($_SESSION['amt']);
				unset($_SESSION['memcode']);
				unset($_SESSION['ewno']);
				header("Location: $xurlback");
				exit;
			}
		}
  }

  function flogdebug($xmodule, $xvar) {
    global $opnm;
		$xarr_log=
			array(
				'fdate'=>'now()',
				'ftime'=>'now()',
				'fopnm'=>$opnm,
				'fmodule'=>$xmodule,
				'flog'=>$xvar,
			);
		db_insert('tlogdebug', $xarr_log);

  }

	function fzip_process($xfilezip, $xfilename, $xbasefilename) {
	  global $debug;
	  if ($debug) echo "fzip_process($xfilezip, $xfilename, $xbasefilename);<br/>\n";
		$zip = new ZipArchive();
		if ($zip->open($xfilezip, ZipArchive::CREATE)!==TRUE) {
				exit("cannot open <$xfilezip>\n");
		}
		$zip->addFile($xfilename, $xbasefilename);
		//echo "numfiles: " . $zip->numFiles . "\n";
		//echo "status:" . $zip->status . "\n";
		$zip->close();
	}

	function fshipheader_rn($xshiphead) {
		$xshiphead=str_replace('\\r\\n',"<br/>",$xshiphead);
		$xshiphead=str_replace('\r\n',"<br/>",$xshiphead);
		$xshiphead=str_replace("\r\n","<br/>",$xshiphead);
		$xshiphead=nl2br($xshiphead);
		return $xshiphead;
	}

function fget_ic_number($xcode, $xtype=0) {
  global $debug;
	//xtype=0 msmemb, $xtype=1 staff
	if ($xtype==0) {
		$xsql="select a.code, b.id_number, b.new_ic from msmemb a, msmemb_extra b where a.code=b.code and a.code='$xcode' ";
		$xrow=fpg_exec4($xsql, 1);
		$xresult = '';
		if ($xrow['code']!='') {
			if ($xrow['new_ic']!='') {
				$xresult=$xrow['new_ic'];
			}else{
				if ($xrow['id_number']!='') {
					$xresult=$xrow['id_number'];
				}
			}

		}
	}elseif ($xtype==1){
		$xsql="select a.staff_id, a.staff_ic from staff a where a.staff_id='$xcode' ";
		//if ($debug) echo "$xsql<br/>\n";
		$xrow=fpg_exec4($xsql, 1);
		$xresult = '';
		if ($xrow['staff_id']!='') {
			$xresult=$xrow['staff_ic'];
		}

	}
  return $xresult;
}


function fgen_sc_range_date_input($xform, $txtTgl1, $txtTgl2, $xmodel=1) {
	global $db, $year1, $month1, $date1;
	$xsql1=
		"select
			month_allow
		from month_allow_setup ";

 	$xstr1_click="gfPop.fPopCalendar($xform.txtTgl1);return false;";
 	$xstr2_click="gfPop.fPopCalendar($xform.txtTgl2);return false;";
 	$txt_month_allow=0;
	$xres1=pg_query($db, $xsql1);
	if (pg_num_rows($xres1)>0) {
		$xrow1=pg_fetch_assoc($xres1, 0);
		$txt_month_allow=$xrow1['month_allow'];
	}
	$xreadonly='';
	$xparam_extra='';
	if ($txt_month_allow>0) {
// 		$xreadonly='readonly';
// 		$mktime1=mktime(0,0,0, $month1-$txt_month_allow, 1, $year1);
// 		$date_allow_start = date("[Y,m,d]",$mktime1);
// 		$mktime2=mktime(0,0,0,$month1,$date1,$year1);
// 		$date_allow_end = date("[Y,m,d]",$mktime2);
// 		$month_allow_month = date("[Y,m]",$mktime2);
// 		$xparam_extra=',['.$date_allow_start.','.$date_allow_end.','.$month_allow_month.']';
// 		$xstr1_click="gfPop.fPopCalendar($xform.txtTgl1 $xparam_extra);return false;";
// 		$xstr2_click="gfPop.fPopCalendar($xform.txtTgl2 $xparam_extra);return false;";
	}

	if ($xmodel==1) {
?>
					<tr bgcolor="White">
						<td align="left" valign="middle">&nbsp;<? print mxlang("211","");?></td>
						<td align="left" width="20%">
							<input type="text" <?=$xreadonly?> id="txtTgl1" name="txtTgl1" value="<?if($txtTgl1=="") echo date("d/m/Y"); else echo $txtTgl1;?>" size="12" maxlength="10">
							<a href="javascript:void(0)" onClick="<?=$xstr1_click?>" HIDEFOCUS>
								<img src="../images/cal_show.gif" border="0">
							</a>
						</td>

						<td align="left" valign="middle" width="5%">&nbsp;<? print mxlang("212","");?></td>
						<td align="left">
							<input type="text" <?=$xreadonly?> id="txtTgl2" name="txtTgl2" value="<?if($txtTgl2=="") echo date("d/m/Y"); else echo $txtTgl2;?>" size="12" maxlength="10">
							<a href="javascript:void(0)" onClick="<?=$xstr2_click?>" HIDEFOCUS>
								<img src="../images/cal_show.gif" border="0">
							</a>
						</td>
					</tr>
<?
	}elseif ($xmodel==2) {
?>
				<tr bgcolor="White">
					<td align="left" valign="middle">&nbsp;<?=mxlang("211","")?></td>
					<td align="left">
						<input type="text" <?=$xreadonly?> id="txtTgl1" name="txtTgl1" value="<?if($txtTgl1=="") echo ("$date1/$month1/$year1"); else echo $txtTgl1;?>" size="12" maxlength="10">
						<a href="javascript:void(0)" onClick="<?=$xstr1_click?>" HIDEFOCUS>
							<img src="../images/cal_show.gif" border="0">
						</a>
					</td>
				</tr>
				<tr bgcolor="White">

					<td align="left" valign="middle">&nbsp;<?=mxlang("693","")?></td>
					<td align="left">
						<input type="text" <?=$xreadonly?> id="txtTgl2" name="txtTgl2" value="<?if($txtTgl2=="") echo ("$date1/$month1/$year1"); else echo $txtTgl2;?>" size="12" maxlength="10">
						<a href="javascript:void(0)" onClick="<?=$xstr2_click?>" HIDEFOCUS>
							<img src="../images/cal_show.gif" border="0">
						</a>
					</td>
				</tr>
<?
	}elseif ($xmodel==3) {
?>
					<td align="left">
						<input type="text" <?=$xreadonly?> id="txtTgl1" name="txtTgl1" value="<?if($txtTgl1=="") echo ("$date1/$month1/$year1"); else echo $txtTgl1;?>" size="12" maxlength="10">
						<a href="javascript:void(0)" onClick="<?=$xstr1_click?>" HIDEFOCUS>
							<img src="../images/cal_show.gif" border="0">
						</a>
					</td>
<?
  }else if ($xmodel==4) {
    ?>
              <tr bgcolor="White">
                <td align="left" valign="middle">&nbsp;Join Date</td>
                <td align="left" width="20%">
                  <input type="text" <?=$xreadonly?> id="txtTgl1" name="txtTgl1" value="<?if($txtTgl1=="") echo date("d/m/Y"); else echo $txtTgl1;?>" size="12" maxlength="10">
                  <a href="javascript:void(0)" onClick="<?=$xstr1_click?>" HIDEFOCUS>
                    <img src="../images/cal_show.gif" border="0">
                  </a>
                </td>
    
                <td align="left" valign="middle" width="5%">&nbsp;<? print mxlang("212","");?></td>
                <td align="left">
                  <input type="text" <?=$xreadonly?> id="txtTgl2" name="txtTgl2" value="<?if($txtTgl2=="") echo date("d/m/Y"); else echo $txtTgl2;?>" size="12" maxlength="10">
                  <a href="javascript:void(0)" onClick="<?=$xstr2_click?>" HIDEFOCUS>
                    <img src="../images/cal_show.gif" border="0">
                  </a>
                </td>
              </tr>
<?

	}//xmodel==3
}

function sel_stockist4_sccom($xstr_name,$xobj_name, $xtype) {
  // 0 --> main stockist
  // 1 --> stockist
  // 2 --> main or stockist
  // 3 --> display --All-- option
  global $db,$priv,$opnm,$debug;
  $xsql="select sccode from users_extra where uname='$opnm'";
  $xres=pg_exec($db, $xsql);
  $xrow=pg_fetch_row($xres,0);
  if ($xrow[0]=='1') {
    echo "<select name='$xstr_name'> \n";
    echo "<option value='x'>--".mxlang("934","")."--</option>\n";
    $xsql_st ="select a.code,a.sub_name,c.name from sub_mssc a left join msmemb c on a.code=c.code left join sub_mssc_extra b on a.code=b.scname ";
    if ($xtype=='MS') $xsql_st.=" where  b.stockist='0' ";
    $xsql_st.="order by a.sub_name";
    $xres_st=pg_exec($db, $xsql_st);
    for ($i=0;$i<pg_num_rows($xres_st);$i++) {
      $xrow_st=pg_fetch_row($xres_st, $i);
      $xsel = ($xobj_name==$xrow_st[0])?'selected':'';
      echo "<option value='$xrow_st[0]' $xsel>$xrow_st[1]-".stripslashes($xrow_st[2])."</option>\n";
    }
    echo "</select> \n";
  }else{
    echo "<select name='$xstr_name'> \n";
    $xsql_st ="select a.code,a.sub_name,c.name from sub_mssc a left join msmemb c on a.code=c.code left join sub_mssc_extra b on a.code=b.scname where a.code='$xrow[0]' ";
    if ($rdo_type=='MS') $xsql_st.=" and  b.stockist='0' ";
    $xsql_st.="order by a.sub_name";
    $xres_st=pg_exec($db, $xsql_st);
    for ($i=0;$i<pg_num_rows($xres_st);$i++) {
      $xrow_st=pg_fetch_row($xres_st, $i);
      $xsel = ($xobj_name==$xrow_st[0])?'selected':'';
      echo "<option value='$xrow_st[0]' $xsel>$xrow_st[1]-".stripslashes($xrow_st[2])."</option>\n";
    }
    echo "</select> \n";
  }
}//end function

function print_the_value($int){
  return ($int < 0)?sprintf('(%d)', abs($int)) : $int;
//   money_format("%= (#1.2n",$xsc_row['rounding'])
}

function fredirect_get2post($mystring) {
  $xidtanya = strpos($mystring, '?');

  $xfilename_get=substr($mystring, 0, $xidtanya);
  $xparam_get   =substr($mystring, $xidtanya+1);
  // echo $xidtanya;
  // echo "<br/>";
  // echo $xfilename_get;
  // echo "<br/>";
  // echo $xparam_get;
  // echo "<br/>";
  $xarr_param_get=preg_split('/\&/', $xparam_get);
  // print_r($xarr_param_get);
  // echo "<br/>";
  $xstr="<form name=\"frm_post_data\" id=\"frm_post_data\" action=\"$xfilename_get\" method=\"post\">\n";
  for ($ig=0; $ig<count($xarr_param_get); $ig++) {
    $xarr_data_get=preg_split('/\=/', $xarr_param_get[$ig]);
    //print_r($xarr_data_get);
    //echo "<br/>";
    $xvalue = $xarr_data_get[1];
    if (preg_match('/\%/',$xvalue)) {
      $xvalue=urldecode($xvalue);
    }
    $xstr.="<input type=\"hidden\" name=\"{$xarr_data_get[0]}\" value=\"{$xvalue}\">\n";
  }
  $xstr.="</form>\n";

  $xstr.="<script>\n";
  $xstr.="document.getElementById('frm_post_data').submit();\n";
  $xstr.="</script>\n";
  echo $xstr;
}

function fcopy_table($xtable_dest, $xtable_from, $xarr_fields, $xwhere_sql) {
  $xsql =
    "insert into $xtable_dest ";

  $xfield_name ='';
  $xfield_value='';

  foreach($xarr_fields as $xkey=>$xvalue){
    if ($xfield_name!='')
      $xfield_name.=',';
    $xfield_name.=$xkey;
    if ($xfield_value!='')
      $xfield_value.=',';
    $xfield_value.=$xvalue;
  }
  $xsql.="($xfield_name) \n";

  $xsql.="select $xfield_value \nfrom $xtable_from ";
  $xsql.=$xwhere_sql;
  return $xsql;
}

function fcheck_foreign_member($xcode) {
  global $default_cnt, $db, $xparam_dbioc, $debug;
  if ($debug) echo KITX." fcheck_foreign_member($xcode);<br/>\n";
  $xforeign_member=false;
  if (substr($xcode, 0, 2)==KITX) {
    $xarr_msmemb = db_select_record('cn_id', 'msmemb', "where code='$xcode' ");
    if ($debug){
      print_r($xarr_msmemb);
      echo "*<br/>\n";
    }
  }else{
    if ($debug) echo "xparam_dbioc=$xparam_dbioc<br/>\n";
    //$db5=pg_connect($xparam_dbioc);
    $db5=fpg_connection('central');
//     $xarr_msmemb = db_select_record('cn_id', 'member.msmemb', "where code='$xcode' ", $db5);
    $xarr_msmemb = db_select_record('cn_id', 'public.msmemb', "where code='$xcode' ", $db5);
    if ($debug){
      print_r($xarr_msmemb);
      echo "**<br/>\n";
    }
  }

  if ($xarr_msmemb['cn_id']!=''){
    if ($xarr_msmemb['cn_id']!=$default_cnt)
      $xforeign_member=true;
      if ($debug){
        echo "xforeign_member=$xforeign_member<br/>\n";
      }
  }

  return $xforeign_member;
}

function fget_maxmskt($xtablename, $xnstart, $xthe_msid) {
  global $year1, $month1, $date1, $debug, $default_cnt, $db;
  $str_date=date('ymd');//"$year1$month1$date1";
  if ($debug)
    echo "$str_date<br/>";
  $xarr_prefix=fpg_exec4("select cb_prefix from company where country_id='$default_cnt';");
  $xprefix = $xarr_prefix[0];
  $digitcb="KT".$xprefix.$str_date;
  $xsql="select max(trcd) as maxtrcd from newmstrh where trtype='5' and loccd='$xthe_msid' and trcd like '$digitcb%' ";
  if ($debug)
    echo "$xsql<br/>";
  $xarr_maxid=fpg_exec4($xsql);
  if ($debug)
    print_r($xarr_maxid);
  $xmax1=$xarr_maxid[0];
// 		$xmax1='KTMC170504000011';
  if ($xmax1=='') {
    echo "i am here<br/>";
    $xmax1=0;
  }else{
    echo "digitcb=$digitcb<br/>";
    $xmax1=str_replace($digitcb, '', $xmax1);
// 			$xmax1=str_replace('KTMC170504', '', $xmax1);
    $xmax1=ltrim($xmax1, '0');
  }
  $xmax1+=1;
  if ($debug)
    echo ("xmax1=$xmax1<br/>\n");

  $xnsql="select nvalue from $xtablename where nstart='$xnstart' and loccd='$xthe_msid' order by nvalue desc limit 1;";
  if ($debug) echo "$xnsql<br/>";
  $dresult=pg_exec($db, $xnsql);
  if (pg_numrows($dresult)>0) {
    list($xnewtrx)=pg_fetch_row($dresult,0);
    $xnewtrx=$xnewtrx+1;
  }else{
// 			$xpsql="insert into $xtablename values('$xnstart',1,'$xthe_msid');";
// 			$xnewtrx=1;
  }
  $xmax2=$xnewtrx;
  if ($debug)
    print_r($xmax2);
  $xmax=$xmax1;
  if ($xmax2>$xmax1)
    $xmax=$xmax2;

  $xceksql="select loccd from $xtablename
    where nstart='$xmax' and loccd='$xthe_msid';";
  $xcekres=pg_exec($db, $xceksql);
  if (pg_num_rows($xcekres)>0) {
    $xpsql="update $xtablename set nvalue=$xmax
      where nstart='$xnstart' and loccd='$xthe_msid' ";
  }else{
    $xpsql="insert into $xtablename values('$xnstart',$xmax,'$xthe_msid'); ";
  }

  pg_query($db, $xpsql);

  if ($debug)
    print_r($xpsql);

  return $xmax;
}

function fget_maxmskt2($xtablename, $xnstart, $xthe_msid) {
  global $year1, $month1, $date1, $debug, $default_cnt, $db;
  $str_date=date('ymd');//"$year1$month1$date1";
  if ($debug)
    echo "$str_date<br/>";

  $xnsql="select nvalue from $xtablename where nstart='$xnstart' and loccd='$xthe_msid';";
  if ($debug) echo "$xnsql<br/>";
  $dresult=pg_exec($db, $xnsql);
  if (pg_numrows($dresult)>0) {
    list($xnewtrx)=pg_fetch_row($dresult,0);
    $xnewtrx=$xnewtrx+1;
    $xpsql="update $xtablename set nvalue=$xnewtrx where nstart='$xnstart' and loccd='$xthe_msid' ";
  }else{
    $xpsql="insert into $xtablename values('$xnstart',1,'$xthe_msid');";
    $xnewtrx=1;
  }
  $xmax=$xnewtrx;
  if ($debug)
    echo("xpsql=".$xpsql);
  pg_query($db, $xpsql);

  return $xmax;
}//end if fget_maxmskt2

function fget_maxsckt($xtablename, $xnstart, $xthe_msid) {
  global $year1, $month1, $date1, $debug, $default_cnt, $db;
  $str_date=date('ymd');//"$year1$month1$date1";
  if ($debug)
    echo "$str_date<br/>";
  /*
  $xarr_prefix=fpg_exec4("select sccb_prefix from company where country_id='$default_cnt';");
  $xprefix = $xarr_prefix[0];
  $digitcb="KT".$xprefix.$str_date;
  $xsql="select max(trcd) as maxtrcd from newsctrh where trtype='5' and trcd like '$digitcb%' ";
  if ($debug)
    echo "$xsql<br/>";
  $xarr_maxid=fpg_exec4($xsql);
  if ($debug)
    print_r($xarr_maxid);
  $xmax1=$xarr_maxid[0];
// 		$xmax1='KTMC170504000011';
  if ($xmax1=='')
    $xmax1=0;
  else{
    $xmax1=str_replace($digitcb, '', $xmax1);
// 			$xmax1=str_replace('KTMC170504', '', $xmax1);
    $xmax1=ltrim($xmax1, '0');
  }
  $xmax1+=1;
  if ($debug)
    echo ("xmax1=$xmax1<br/>\n");
  */

  $xnsql="select nvalue from $xtablename where nstart='$xnstart' and loccd='$xthe_msid';";
  if ($debug) echo "$xnsql<br/>";
  $dresult=pg_exec($db, $xnsql);
  if (pg_numrows($dresult)>0) {
    list($xnewtrx)=pg_fetch_row($dresult,0);
    $xnewtrx=$xnewtrx+1;
    $xpsql="update $xtablename set nvalue=$xnewtrx where nstart='$xnstart' and loccd='$xthe_msid' ";
  }else{
    $xpsql="insert into $xtablename values('$xnstart',1,'$xthe_msid');";
    $xnewtrx=1;
  }
  $xmax=$xnewtrx;
  /*
  $xmax2=$xnewtrx;
  if ($debug)
    print_r($xmax2);
  $xmax=$xmax1;
  if ($xmax2>$xmax1)
    $xmax=$xmax2;
    if ($debug) echo ("xmax2=$xmax2<br/>\n");
  */
  /*
  $xceksql="select loccd from $xtablename where nstart='$xmax' and loccd='$xthe_msid';";
  $xcekres=pg_exec($db, $xceksql);
  if (pg_num_rows($xcekres)>0) {
    $xpsql="update $xtablename set nvalue=$xmax where nstart='$xnstart' and loccd='$xthe_msid' ";
  }else{
    $xpsql="insert into $xtablename values('$xnstart',$xmax,'$xthe_msid'); ";
  }
  */
  if ($debug)
    echo("xpsql=".$xpsql);
  pg_query($db, $xpsql);


  return $xmax;
}

	function fsend_email_sc_online_order($xtrxno, $xto=''){
		global $db, $dbc1, $debug, $xemail_cc, $ref,
		$xDBHOST_EWORLD, $xDBNAME_EWORLD, $xDBPORT_EWORLD, $xDBUSER_EWORLD, $xDBPASS_EWORLD;

		Logger::debug("fsend_email_sc_online_order($xtrxno)");
		$xtable1='newmsivtrh';
		$xfieldname='trivcd';
		$xtable2='msivship';
		$xtable3='mspayment';

		$xrow_trx=$dbc1->db_select_record(
			"a.$xfieldname, a.trdt, a.frmsys, a.code, a.seller_has_tax, a.loccd, a.bc_id, b.ship_email, a.totpay,
			c.totpay1, c.paytype1, c.totpay2, c.paytype2, c.totpay3, c.paytype3,
			c.totpay4, c.paytype5, c.totpay5, c.paytype5, b.shipheader,
			a.tpv, a.tbv, b.cost, a.tdp, a.pdisc, b.ftype ",
			"$xtable1 a
			left join $xtable2 b on a.$xfieldname=b.$xfieldname
			left join $xtable3 c on a.$xfieldname=c.$xfieldname ",
			"where a.$xfieldname='$xtrxno' "
		);

		if ($debug) {
			Logger::debug($xrow_trx);
// 			exit();
		}

		$email_ship=strtolower($xrow_trx['ship_email']);
		$xcode		=$xrow_trx['code'];
		$xname 		=get_memname($xcode);
		$xloccd		=$xrow_trx['loccd'];

// 		$xrow_branch=db_select_record('br_email', 'msms_new', "where br_code='$xloccd'");
// 		$xbr_email=$xrow_branch['br_email'];

		$xrow_notification=
			db_select_record('email_to, email_cc, email_bcc', 'email_notification', "where bc_id='$xloccd' and module='SC Online Order'");

		if ($email_ship!=''){
			Logger::debug("here");

// 			$xparam_eworld=
// 				"host=$xDBHOST_EWORLD dbname=$xDBNAME_EWORLD port=$xDBPORT_EWORLD user=$xDBUSER_EWORLD password=$xDBPASS_EWORLD";
// 			$db
// 			$db_eworld=pg_connect($xparam_eworld);
			pg_query($db, 'begin transaction');

			$xmessage_email='';
			$xmessage_email.=
				"DXN SC Online Purchase Order No $xtrxno \n".
				"Dear $xname ($xcode), \n".

				"Thank you for shopping DXN product(s) with us! \n".
				"The following is your reference that shows about summary payment of this $xtrxno \n".

				"Purchase Order No : $xtrxno	\n".
				"Date Created : {$xrow_trx['trdt']} \n".
				"Member ID : $xcode \n".
				"Member Name : $xname \n ";

			$xmessage_email.="\n";

			for ($ij=1; $ij<=5; $ij++){
				if ($xrow_trx['totpay'.$ij]>0){
					//select ptypename from pay_type where ptypeid='EW';
					$xrow_pay=db_select_record('ptypename', 'pay_type', "where ptypeid='".$xrow_trx['paytype'.$ij]."'");

					$xmessage_email.=
						"Payment Amount {$xrow_pay['ptypename']}: RM ".$xrow_trx['totpay'.$ij]."\n".
						"Transaction Remark: {$xrow_pay['ptypename']} \n";
				}//end if
			}//end for

			if ($xrow_trx['ftype']=='1'){
				$xmessage_email.=
					"Please ship the item to the following address : ".
					$xrow_trx['shipheader'];
				$xmessage_email.=
					"E-Mail : {$xrow_trx['ship_email']} \n";
			}
			$xmessage_email.="\n";

			$xsql2="
				select a.prdcd, a.qty, a.dp, a.pv,a.bv, b.prdnm
				from newmsivtrd a
				left join msprd b on b.prdcd=a.prdcd
				where a.trivcd='$xtrxno'
				order by a.prdcd";
			$xres2=pg_query($db, $xsql2);

			$xmessage_email.=
				"Order Item(s) : \n";
			for ($ix=0; $ix<pg_num_rows($xres2); $ix++) {
				$xrow2=pg_fetch_assoc($xres2, $ix);
				$xmessage_email.=($ix+1).". {$xrow2['prdcd']} \n";
				$xmessage_email.="{$xrow2['prdnm']} \n";
				$xmessage_email.="Unit Price excl. (RM) : ".number_format($xrow2['dp'],2)."\n";
				//$xmessage_email.="(RM) : 0.00 \n";
				$xmessage_email.="Unit Price incl. (RM) : ".number_format($xrow2['dp'],2)."\n";
				$xmessage_email.="PV : ".number_format($xrow2['pv'],2)."\n";
				$xmessage_email.="SV : ".number_format($xrow2['bv'],2)."\n";
				$xmessage_email.="Quantity : {$xrow2['qty']} "."\n";
				$xmessage_email.="Amount excl. (RM) : ".number_format($xrow2['dp']*$xrow2['qty'],2)."\n";
				//$xmessage_email.="Amount (RM) : 0.00 \n";
				$xmessage_email.="Amount incl. (RM) : ".number_format($xrow2['dp']*$xrow2['qty'],2)."\n\n";

			}//end for

			$xmessage_email.="\n";
			$xmessage_email.="Total PV : ".number_format($xrow_trx['tpv'],2)."\n";
			$xmessage_email.="Total SV : ".number_format($xrow_trx['tbv'],2)."\n";
			$xmessage_email.="Subtotal (RM) : ".number_format($xrow_trx['tdp'],2)."\n";
			$xmessage_email.="Discount (RM) : ".number_format($xrow_trx['pdisc'],2)."\n";
			$xmessage_email.="Shipping Fee (RM) : ".number_format($xrow_trx['cost'],2)."\n";
			$xmessage_email.="Total Amount (RM) : ".number_format($xrow_trx['totpay'],2)."\n";

			$xmessage_email.=
				"\n".
				"For further concerns or enquiries about this purchase order, you can check the status of your order anytime on List Stockist Order or please feel free to contact your nearer branch. \n
				Thank you for using DXN Online Billing System.";


			$xurl=
				"https://{$ref[0]}/{$ref[1]}/product_ordering/view_invoice_so_inv_gst_prnx.php?".
				"theMSID=$xloccd&trxno=$xtrxno&xpage=list_so.php&cp=1&newepoint=&nob=1";

			$xfile_local=$xtrxno.'.html';
			$xfullname='../upload/'.$xfile_local;
			if (file_exists($xfullname)){
				unlink($xfullname);
			}

			//file_put_contents($xfullname, file_get_contents($xurl));

			$ch = curl_init();
			//if($_SERVER["SERVER_NAME"]!="192.168.101.130")
			curl_setopt($ch, CURLOPT_REFERER, 'https://obs1.dxn2u.com');
			curl_setopt($ch, CURLOPT_PORT , '443');
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_URL, $xurl);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			$xfile_str=curl_exec($ch);
			curl_close($ch);

			$myfile = fopen($xfullname, "w") or die("Unable to open file!");
			fwrite($myfile, $xfile_str);
			fclose($myfile);


			Logger::debug("save into $xfullname");

			//email_spooler

			$data = file_get_contents($xfullname);
			$escaped = pg_escape_bytea($data);

			$xsubject="DXN OBS - SC Online Purchase Order $xtrxno";



			$mysql =
				"INSERT INTO email_spooler ".
				"(\"trcd\", \"email_to\", \"email_bcc\", \"email_cc\", \"subject\", \"message\", \"attachment\",
				retry, success, created_time) ".
				"VALUES ('".$xtrxno."', '$email_ship', null, null,
				'". pg_escape_string($xsubject) . "', '" . pg_escape_string($xmessage_email) . "','$escaped',
				'0', false, now());";
			Logger::debug("$mysql");
			pg_query($db, $mysql);

			if ($xrow_notification['email_to']!=''){
				$mysql =
					"INSERT INTO email_spooler ".
					"(\"trcd\",
					\"email_to\", \"email_cc\", \"email_bcc\", \"subject\", \"message\", \"attachment\",
					retry, success, created_time) ".
					"VALUES ('".$xtrxno."',
					'".strtolower($xrow_notification['email_to'])."', '".strtolower($xrow_notification['email_cc'])."', '".
					strtolower($xrow_notification['email_bcc'])."',
					'". pg_escape_string($xsubject) . "', '" . pg_escape_string($xmessage_email) . "','$escaped',
					'0', false, now());";
				Logger::debug("$mysql");
				pg_query($db, $mysql);
			}
			if ($debug){
				pg_query($db, 'rollback transaction');
			}else{
				pg_query($db, 'commit transaction');
			}

		}//end if


	}//end function

?>