<?php
//Logger::debug("act:doAddNewSCOrderPayment shfee:$shfee");
?>

<table width="790" bgcolor="#cccccc" align="center" border="0" cellspacing="1" cellpadding="2">
    <tr valign="middle" bgcolor="#E6E6E6">
        <th rowspan="2" width="5%"><?= txtmxlang("Product Code") ?></th>
        <th rowspan="2" width="15%"><?= txtmxlang("Product Name") ?></th>
        <th colspan="2" ><?= txtmxlang("Price") ?></th>
        <th rowspan="2" width="5%"><?= txtmxlang("QTY") ?></th>
        <th rowspan="2" width="5%"><?= txtmxlang("Amount") ?></th>
        <th rowspan="2" width="5%"><?= txtmxlang("Taxable Amount") ?></th>
<!--        <th rowspan="2" width="5%"><?= txtmxlang("PV") ?></th>
        <th rowspan="2" width="5%"><?= txtmxlang("SV") ?></th>-->
    </tr>
    <tr valign="middle" bgcolor="#E6E6E6">
        <th width="5%"><?= txtmxlang("Member") ?></th>
        <th width="5%"><?= txtmxlang("Retail") ?></th>
    </tr>
    <?php
    $shfee = $_POST[shfee];
    $itm = explode(",", $itmlst);
    $qty = 0;
    $totamt = 0;
    $tottxamt = 0;
    $totpv = 0;
    $totsv = 0;
    $non_taxable = false;
    $no_shipping_charge = false;
    $non_taxable_price = 0;
    $non_taxable_price_title = '';
    $no_shipping_charge_price = 0;
    $no_shipping_charge_title = "";
//    Logger::debug("act:no_shipping_charge:$no_shipping_charge doAddNewSCOrderPayment shfee:$shfee");
    $totItem = 0;
    $totItemNoShippingCharge = 0;

    $sc_info = $db2->doQuery("select * from sub_mssc_ext where sccode='$sccode'")->getFirstRow();
    $scadd_default = $sc_info['badd_default'];
    // $scadd_default = $db2->doQuery("select * from scadd_default where sccode='$sccode'")->getFirstRow();
    // if ($opnm=='charles'){
    //     var_dump($sc_info);
    //     echo('<br>');
    //     var_dump($scadd_default);
    // }	

    foreach ($itm as $it) {
        Logger::debug("act:doAddNewSCOrderPayment it=$it");
        $msprd = $db2->doQuery("select * from msprd WHERE prdcd='$it' order by prdnm")->getFirstRow();
        $promopack = $db2->doQuery("select * from promopack WHERE pcprdcd='$it' order by pcprdnm")->getFirstRow();
        Logger::debug('msprd:', $msprd);
        Logger::debug('promopack:', $promopack);
        $amt = ($_POST["dp_$it"] * $_POST["qty_$it"]);
        $txamt = ($_POST["cp_$it"] * $_POST["qty_$it"]);
        $pv = ($_POST["pv_$it"] * $_POST["qty_$it"]);
        $it_search = $it;
        $bv = ($_POST["bv_$it"] * $_POST["qty_$it"]);
        if (empty($msprd) AND empty($promopack)) {
//            $txamt = 0;
            $itar = explode("_", $it);
            Logger::debug("$it.itar:", $itar);
            $msprdpromo = $db2->doQuery("select * from msprd WHERE prdcd='" . $itar[sizeof($itar) - 1] . "' order by prdnm")->getFirstRow();
            Logger::debug("$it.msprdpromo:", $msprdpromo);
            $it_search = $itar[sizeof($itar) - 1];
        }


        if (($_POST["qty_$it"] * 1) != 0) {
            $totItem++;
            
//            $msprd_condition = $db2->doQuery("select * from msprd_condition WHERE prdcd='$it' AND prdgroup_id='$prdgroup_id_con' ")->getFirstRow();
            $msprd_condition = $db2->doQuery("select * from msprd_condition WHERE prdcd='$it_search' AND prdgroup_id IN ($sql_prdgroup_id_con) ")->getFirstRow();
            Logger::debug('msprd_condition:', $msprd_condition);
            if ($msprd_condition[non_taxable] == "t") {
                $txamt = 0;
//                $non_taxable = true;
//                $tax = 0;
            } else {
                $non_taxable_price+=$txamt;
                if ($non_taxable_price_title == "") {
                    $non_taxable_price_title = "$it:[$txamt]";
                } else {
                    $non_taxable_price_title .= "+$it:[$txamt]";
                }
            }
            if ($msprd_condition[no_shipping_charge] == "t") {
                $totItemNoShippingCharge++;
                $no_shipping_charge = true;
                Logger::debug("act:no_shipping_charge:$no_shipping_charge doAddNewSCOrderPayment prdcd:$it,prdgroup_id=$prdgroup_id_con");
            }
            if ($msprd_condition[extra_shipping_charge] == "t") {
                $no_shipping_charge_price+=$msprd_condition[shipping_fee];
                if ($no_shipping_charge_title == "") {
                    $no_shipping_charge_title = "$it:$msprd_condition[shipping_fee]";
                } else {
                    $no_shipping_charge_title .= "+$it:$msprd_condition[shipping_fee]";
                }
                //$shfee += ($msprd_condition[shipping_fee] == "" ? 0 : $msprd_condition[shipping_fee]);
            }
        }
        ?>
        <tr valign="top" bgcolor="<?= ($msprd[prdcd] != "" ? "#FFFFFF" : ($promopack[pcprdcd] != "" ? "#FFFFFF" : "#ebebe0")) ?>" <?= (($_POST["qty_$it"] * 1) == 0 ? " hidden " : "") ?> >
            <td align="<?= ($msprd[prdcd] != "" ? "LEFT" : ($promopack[pcprdcd] != "" ? "LEFT" : "RIGHT")) ?>"><?= ($msprd[prdcd] != "" ? $msprd[prdcd] : ($promopack[pcprdcd] != "" ? "$promopack[pcprdcd]" : $msprdpromo[prdcd])) ?></td>
            <td ><?= ($msprd[prdnm] != "" ? $msprd[prdnm] : ($promopack[pcprdnm] != "" ? "$promopack[pcprdnm]" : $msprdpromo[prdnm]) . ($promopack[pcdesc] == "" ? "" : "<br><font color=\"blue\" size=\"1\">$promopack[pcdesc]</font>")) ?></td>
            <td align="right" style="color: black"><?= number_format($_POST["dp_$it"] == "" ? 0 : $_POST["dp_$it"], 2) ?></td>
            <td align="right" style="color: black"><?= number_format($_POST["cp_$it"] == "" ? 0 : $_POST["cp_$it"], 2) ?></td>
            <td align="right" style="color: black"><?= number_format($_POST["qty_$it"] == "" ? 0 : $_POST["qty_$it"], 0) ?></td>
            <td align="right" style="color: black"><?= number_format($amt, 2) ?></td>
            <td align="right" style="color: black"><?= (number_format($txamt, 2)) ?></td>
<!--            <td align="right"><?= number_format($pv, 2) ?></td>
            <td align="right"><?= number_format($bv, 2) ?></td>-->
        </tr>
        <?php
        if (!empty($promopack)) {
            $promopack_item = $db2->doQuery("SELECT DISTINCT * FROM promopack_item WHERE pcprdcd='$it'  order by pcprdcd")->toArray();
            foreach ($promopack_item as $pi) {
                Logger::debug("&nbsp;&nbsp;&nbsp;&nbsp;promopack_item:", $pi);
            }
        }
        $totamt +=$amt;
        $tottxamt +=$txamt;
        $totpv +=$pv;
        $totsv +=$bv;
    }
    $non_taxable_price_title .="[$tottxamt]";
    /*
      if ($no_shipping_charge) {
      if (true) {//true requrst Sam 20180103
      if ($no_shipping_charge_title == "") {
      $no_shipping_charge_title = "ManualShipping:$shfee";
      } else {
      $no_shipping_charge_title .= "+ManualShipping:$shfee";
      }
      $shfee += $no_shipping_charge_price;
      } else {
      $shfee = $no_shipping_charge_price;
      }
      Logger::debug("1.act:no_shipping_charge:$no_shipping_charge doAddNewSCOrderPayment shfee:$shfee");
      } else {
      if ($no_shipping_charge_title == "") {
      $no_shipping_charge_title = "onlinecharge:$shfee";
      } else {
      $no_shipping_charge_title .= "+onlinecharge:$shfee";
      }
      $shfee = $shfee + $no_shipping_charge_price;
      Logger::debug("2.act:no_shipping_charge:$no_shipping_charge doAddNewSCOrderPayment shfee:$shfee");
      }

      $shfee = ($shfee == "" ? "0.00" : $shfee);
      Logger::debug("act:totItem:$totItem, totItemNoShippingCharge:$totItemNoShippingCharge");
      if ($totItem==$totItemNoShippingCharge){
      $no_shipping_charge_title="All item have No Shipping Charge";
      $shfee=0.00;
      }
     */
    $sql = "SELECT * FROM reseller_permit WHERE code='$sccode' AND effective_date <= CAST(now() as date) AND expired_date >= CAST(now() as date) AND st_id='$shipping_branch_setup[st_id]' ;";
    $reseller_permit = $db2->doQuery($sql)->getFirstRow();
    Logger::debug("tax:$tax,reseller_permit:", $reseller_permit);
    if (!empty($reseller_permit)) {
        $tax = 0;
    }

    $ship_tax = 0;
    if($db2->doQuery("select * from shipping_vat where cn_id='US' and st_id='$shipping_branch_setup[st_id]'")->isFound())
        $ship_tax = $shfee;
    
    $non_taxable_price += $ship_tax;
    $txt_total_taxes = ($non_taxable_price * $tax);
    $non_taxable_price_title .="=($non_taxable_price * $tax):[$txt_total_taxes]";
    Logger::debug("act:doAddNewSCOrderPayment tax:$txt_total_taxes = ($non_taxable_price * $tax)");
    $total = round($totamt + $txt_total_taxes + $shfee, 2);
    Logger::debug("act:doAddNewSCOrderPayment shfee:$shfee");

    Logger::debug("act:doAddNewSCOrderPayment non_taxable_price_title:$non_taxable_price_title");
    Logger::debug("act:doAddNewSCOrderPayment no_shipping_charge_title:$no_shipping_charge_title");
    if (!$debug) {
        $non_taxable_price_title = "";
        $no_shipping_charge_title = "";
    }
    ?>
    <tr valign="middle" bgcolor="#FFFFFF">
        <td colspan="5" align="right"><b>Subtotal</b></td>
        <td align="right"><table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td align="right"></td><td align="right" width="50%"><b>USD&nbsp;<?= number_format($totamt, 2) ?></b></td></tr></table></td>
        <td align="right"><table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td align="right"></td><td align="right" width="50%"><b>USD&nbsp;<?= number_format($non_taxable_price-$ship_tax, 2) ?></b></td></tr></table></td>
<!--        <td align="right"><b><?= number_format($totpv, 2) ?></b></td>
        <td align="right"><b><?= number_format($totsv, 2) ?></b></td>-->
    </tr>
    
    <tr valign="middle" bgcolor="#FFFFFF" <?= ($scship == "delivery" ? "" : "") ?>>
        <td colspan="5" align="right"><b>Shipping Fee</b></td>
        <td align="right" title="<?= $no_shipping_charge_title ?>" ><table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td align="right">USD&nbsp;</td><td align="right" width="1%"><input class="num" type="text" id="shfeeinfo" name="shfeeinfo" size="10" value="<?= number_format($shfee, 2) ?>" style="text-align: right" readonly></td></tr></table></td> 
        <td align="right">USD&nbsp;<?=number_format($ship_tax,2)?></td>
        <!--<td colspan="2" align="right"></td>-->
    </tr>
    
    <tr valign="top" bgcolor="#FFFFFF" >
        <td colspan="5" align="right"><b>Sales Tax</b><br>(Taxable Amount is based on Retail Price)</td>
        <td align="right"><table width="100%" border="0" cellspacing="0" cellpadding="0"><tr valign="top"><td align="right">USD&nbsp;</td><td align="right" width="1%"><input class="num" type="text" id="salestax" name="salestax" size="10" value="<?= number_format($txt_total_taxes, 2) ?>" style="text-align: right" readonly></td></tr></table></td>
        <td colspan="3" title="<?= $non_taxable_price_title ?>"><b>Tax Rate = <?= number_format(($tax * 100), 3) ?>%</b></td>
    </tr>
    
    <tr valign="middle" bgcolor="#FFFFFF">
        <td colspan="5" align="right"><b>Total</b></td>
        <td align="right"><table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td align="right">USD&nbsp;</td><td align="right" width="1%"><input class="num" type="text" id="total" name="total" size="10" value="<?= number_format($total, 2) ?>" style="text-align: right" readonly></td></tr></table></td>
        <td colspan="3" align="right"></td>
    </tr>
    <tr valign="top" bgcolor="#E6E6E6">
        <th colspan="9" align="left">&nbsp;&nbsp;Payment Information</th>
    </tr>
    <tr valign="top" bgcolor="#FFFFFF">
        <td colspan="9">
            <input name="txt_subtotal" type="hidden" id="txt_subtotal" value="<?= $totamt ?>" >
            <input name="txt_subtotal2" type="hidden" id="txt_subtotal2" value="<?= $totamt ?>">
            <input name="txt_total_taxes" type="hidden" id="txt_total_taxes" value="<?= $txt_total_taxes ?>">
	    <input name="txt_disc_amount" type="hidden" id="txt_disc_amount" value="0">
            <input name="txt_shcost" type="hidden" id="txt_shcost" value="<?= $shfee ?>">
            <input name="inclusive_tax" type="hidden" id="inclusive_tax" value="<?= $inclusive_tax ?>">
            <input name="addtax_flag" type="hidden" id="addtax_flag" value="<?= $addtax_flag ?>">
            <input name="txt_grandtotal" type="hidden" id="txt_grandtotal" value="<?= $total ?>">
            <input name="grand_total_txt" type="hidden" id="grand_total_txt" value="<?= $total ?>">
            <input type="hidden" id="ptax" name="ptax" value="<?= $tax == "" ? 0 : $tax ?>">
            <!--<input name="txt_grandtotal" type="hidden" id="txt_grandtotal" value="<?= $total ?>">-->
            <input type="hidden" id="t" name="t" value="">
            <input type="hidden" id="b" name="b" value="Continue">
            <?php
            $txt_grandtotal = $total;
            if($scnew_sc){
                $pquerr1 = "select ptypeid,ptypename from pay_type_sc_inv where id_cnt='$cbo_country' order by orderx,ptypename";
            } else {
                $pquerr1 = "select ptypeid,ptypename from pay_type_br_inv where id_cnt='$cbo_country' order by orderx,ptypename";
            }
            $presult1 = pg_exec($db, $pquerr1);
            $show_ccd = 1;
            $xloccd = $shipping_branch_setup[loccd];
            $txt_key = $sub_mssc[code];
            $old_credit_flag = $credit_flag;
            $creditflag = ($credit_flag == "t" ? 1 : 0);
            $credit_flag = ($credit_flag == "t" ? 1 : 0);
            Logger::debug("creditflag:$creditflag, credit_flag:$credit_flag, frm_payment_transaction():$pquerr1");
            	
            frm_payment_transaction();
            
            pg_free_result($presult1);
            Logger::debug("txt_grandtotal:$txt_grandtotal,xloccd:$xloccd");
            ?>
        </td>
    </tr>
    <tr valign="top" bgcolor="#FFFFFF">
        <td colspan="9">
            <table width="650px" bgcolor="#FFFFFF" align="left" border="0" cellspacing="1" cellpadding="2">
                <tr valign="top" bgcolor="#FFFFFF">
                    <td width="50%">
                        Notes :<br>
                        <textarea id="txtnote" name="txtnote" rows="2" cols="40"><?= $txtnote ?></textarea>
                    </td>
                    <td width="1%">
                        <input type="checkbox" id="chkshippack" name="chkshippack" value="<?= $chkshippack ?>" <?= $chkshippack == "1" ? " checked " : "" ?> onclick="if (this.checked) {
                                    this.value = 1;
                                    jq('#txtshippack').val('');
                                    jq('#txtshippack').attr('readonly', false);
                                    jq('#txtshippack').focus();
                                } else {
                                    this.value = 0;
                                    jq('#txtshippack').val('');
                                    jq('#txtshippack').attr('readonly', true);
                                }">
                    </td>
                    <td>
                        <span>
                            Ship with Packing List ONLY, no Invoice.<br>(Optional) Message Shown on Packing List:<br>
                            <input type="text" id="txtshippack" name="txtshippack" value="<?= $txtshippack ?>" size="40" <?= $chkshippack == "1" ? "" : " readOnly " ?> >
                        </span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="10">
            <input type="button" value="<?= txtmxlang("Back") ?>" onClick="doAddNewSCOrder()" tabindex="6" <?= $action == "rollback" ? "hidden" : "" ?>>
            <input type="button" value="<?= txtmxlang("Generate Sales Order") ?>" onClick="doCheckpayment()" tabindex="7">
            <input type="button" onClick="doCancel();" value="<?= txtmxlang("Cancel") ?>" tabindex="8">
        </td>
    </tr>
</table>
<input type="hidden" id="non_taxable" name="non_taxable" value="<?= $non_taxable ?>">
<input type="hidden" id="no_shipping_charge" name="no_shipping_charge" value="<?= $no_shipping_charge ?>">
<input type="hidden" id="trxno" name="trxno" value="<?= $trxno ?>">
<script>
    jq("#b_return_txt").empty().html("<?= mxlang(1952)?>:");
    function doCheckpayment() {
        var count_pay = jq("[name=count_pay]").val();
        var ccd1_flag = jq("[name=ccd1_flag]").val();
        var return_total = jq("[name=return_total]").val();
        var total = jq("[name=total]").val();
        var rst = ((total * 1) + (return_total * 1));
        var chkReturnPay = true;
        var chkReturnPayErr = "";
        if (rst == 0) {
            var msg = "Can not process for empty payment!";
            alert(msg);
            jq("[name=cbo_payinput_0]").focus();
            return false;
        } else if (return_total<0) {
            //var msg = "Payment amount should be equal to Total amount!";
            var msg = "Payment should not less than amount !";
            alert(msg);
            jq("[name=cbo_payinput_0]").focus();
            return false;
        }
        for (var i = 0; i < (count_pay * 1); i++) {


            var cbo_paytype = jq("[name=cbo_paytype_" + i + "]").val();
            var cbo_payinput = jq("[name=cbo_payinput_" + i + "]").val();
            var ccd_exp = jq("[name=ccd_exp_" + i + "]").val();
            jq("#span_ccd_no_" + i).empty().html("");
            jq("#span_ccd_name_" + i).empty().html("");
            jq("#span_ccd_exp_" + i).empty().html("");
            jq("#span_ccd_type_" + i).empty().html("");
            jq("#span_ccd_autho_" + i).empty().html("");
            jq("#span_txt_bill_addr_" + i).empty().html("");
            jq("#span_txt_bill_postcd_" + i).empty().html("");
            jq("#span_txt_bill_city_" + i).empty().html("");
            jq("#span_txt_bill_state_" + i).empty().html("");
            jq("#span_txt_bill_country_" + i).empty().html("");
            jq("#txt_txt_bill_tel_" + i).empty().html("");
            jq("#span_txt_bill_addr_" + i).empty().html("");
            jq("#span_txt_bill_addr_" + i).empty().html("");
            if ((cbo_paytype != "CSH") && ((cbo_payinput * 1) > 0)) {
                jq("[name=cbo_payinput_" + i + "]").focus();
                chkReturnPay = false;
            }
            if (cbo_paytype == "CCD") {
                var expdate = ccd_exp.split("/");
                var expdatenum = expdate.length;
                console.log(expdate);
                console.log("expdatenum:" + expdatenum);
                console.log("ccd1_flag:" + ccd1_flag);
                if (cbo_payinput > 0) {
                    if (jq("[name=ccd_no_" + i + "]").val() == "") {
                        var msg = "<ul style=\"margin: 0pt; padding-left: 15px; color: red;\"><li><?= mxlang(4383) ?></li></ul>";
                        jq("#span_ccd_no_" + i).empty().html(msg);
                        jq("[name=ccd_no_" + i + "]").focus();
                        return false;
                    }
                    if (jq("[name=ccd_name_" + i + "]").val() == "") {
                        var msg = "<ul style=\"margin: 0pt; padding-left: 15px; color: red;\"><li><?= mxlang(4383) ?></li></ul>";
                        jq("#span_ccd_name_" + i).empty().html(msg);
                        jq("[name=ccd_name_" + i + "]").focus();
                        return false;
                    }
                    if (jq("[name=ccd_exp_" + i + "]").val() == "") {
                        var msg = "<ul style=\"margin: 0pt; padding-left: 15px; color: red;\"><li><?= mxlang(4383) ?></li></ul>";
                        jq("#span_ccd_exp_" + i).empty().html(msg);
                        jq("[name=ccd_exp_" + i + "]").focus();
                        return false;
                    }
                    if (expdatenum == 1) {
                        var msg = "<ul style=\"margin: 0pt; padding-left: 15px; color: red;\"><li><?= mxlang(4383) ?></li></ul>";
                        jq("#span_ccd_exp_" + i).empty().html(msg);
                        jq("[name=ccd_exp_" + i + "]").focus();
                        return false;
                    }
                    if (expdatenum == 2) {
                        if ((expdate[0] * 1) > 12) {
                            var msg = "<ul style=\"margin: 0pt; padding-left: 15px; color: red;\"><li><?= txtmxlang("Please enter a valid date!") ?></li></ul>";
                            jq("#span_ccd_exp_" + i).empty().html(msg);
                            jq("[name=ccd_exp_" + i + "]").focus();
                            return false;
                        } else if ((expdate[1] * 1) < <?= date("Y") ?>) {
                            var msg = "<ul style=\"margin: 0pt; padding-left: 15px; color: red;\"><li><?= txtmxlang("Please enter a valid date!") ?></li></ul>";
                            jq("#span_ccd_exp_" + i).empty().html(msg);
                            jq("[name=ccd_exp_" + i + "]").focus();
                            return false;
                        }
                    }
                    if (jq("[name=ccd_type_" + i + "]").val() == "") {
                        var msg = "<ul style=\"margin: 0pt; padding-left: 15px; color: red;\"><li><?= mxlang(4383) ?></li></ul>";
                        jq("#span_ccd_type_" + i).empty().html(msg);
                        jq("[name=ccd_type_" + i + "]").focus();
                        return false;
                    }
                    if (jq("[name=ccd_autho_" + i + "]").val() == "") {
                        var msg = "<ul style=\"margin: 0pt; padding-left: 15px; color: red;\"><li><?= mxlang(4383) ?></li></ul>";
                        jq("#span_ccd_autho_" + i).empty().html(msg);
                        jq("[name=ccd_autho_" + i + "]").focus();
                        return false;
                    }
                    if (!(jq("[name=ccd1_flag]").prop('checked'))) {
//                    if (jq("[name=ccd1_flag]").val() == "") {
                        if (jq("[name=txt_bill_addr_" + i + "]").val() == "") {
                            var msg = "<ul style=\"margin: 0pt; padding-left: 15px; color: red;\"><li><?= mxlang(4383) ?></li></ul>";
                            jq("#span_txt_bill_addr_" + i).empty().html(msg);
                            jq("[name=txt_bill_addr_" + i + "]").focus();
                            return false;
                        }
                        if (jq("[name=txt_bill_postcd_" + i + "]").val() == "") {
                            var msg = "<ul style=\"margin: 0pt; padding-left: 15px; color: red;\"><li><?= mxlang(4383) ?></li></ul>";
                            jq("#span_txt_bill_postcd_" + i).empty().html(msg);
                            jq("[name=txt_bill_postcd_" + i + "]").focus();
                            return false;
                        }
                        if (jq("[name=txt_bill_city_" + i + "]").val() == "") {
                            var msg = "<ul style=\"margin: 0pt; padding-left: 15px; color: red;\"><li><?= mxlang(4383) ?></li></ul>";
                            jq("#span_txt_bill_city_" + i).empty().html(msg);
                            jq("[name=txt_bill_city_" + i + "]").focus();
                            return false;
                        }
                        if (jq("[name=txt_bill_state_" + i + "]").val() == "") {
                            var msg = "<ul style=\"margin: 0pt; padding-left: 15px; color: red;\"><li><?= mxlang(4383) ?></li></ul>";
                            jq("#span_txt_bill_state_" + i).empty().html(msg);
                            jq("[name=txt_bill_state_" + i + "]").focus();
                            return false;
                        }
                        if (jq("[name=txt_bill_country_" + i + "]").val() == "") {
                            var msg = "<ul style=\"margin: 0pt; padding-left: 15px; color: red;\"><li><?= mxlang(4383) ?></li></ul>";
                            jq("#span_txt_bill_country_" + i).empty().html(msg);
                            jq("[name=txt_bill_country_" + i + "]").focus();
                            return false;
                        }
                        if (jq("[name=txt_bill_tel_" + i + "]").val() == "") {
                            var msg = "<ul style=\"margin: 0pt; padding-left: 15px; color: red;\"><li><?= mxlang(4383) ?></li></ul>";
                            jq("#span_txt_bill_tel_" + i).empty().html(msg);
                            jq("[name=txt_txt_bill_tel_" + i + "]").focus();
                            return false;
                        }
                    }
                }
            }
            
            if (jq("[name=ccd1_flag]").prop('checked')) {
                if(jq('#ccd1_ref').val()=="") {
                    alert('please fill in the reference number');
                    jq("#ccd1_ref").focus();
                    return false;
                }
            }
            
            if (jq("[name=ccd2_flag]").prop('checked')) {
                if(jq('#ccd2_ref').val()=="") {
                    alert('please fill in the reference number');
                    jq("#ccd2_ref").focus();
                    return false;
                }
            }
            
            if (chkReturnPay == false && ((return_total * 1) > 0)) {
                //alert("You have Total Return "+return_total+" this only allow for Cash payment method !");
                alert("Sorry, return allow for Cash payment method only!");
                return false;
            }
        }

        doAddNewSCOrderPaymentGen();
    }
    function doAddNewSCOrderPaymentGen() {
        var sccode = jq("#sccode").val();
        var count_pay = jq("[name=count_pay]").val();
        var ccno = "";
        var cbo_paytype_ccno = "";
        var cbo_payinput_ccno = 0;
        for (var i = 0; i < (count_pay * 1); i++) {
            var cbo_paytype = jq("[name=cbo_paytype_" + i + "]").val();
            var cbo_payinput = jq("[name=cbo_payinput_" + i + "]").val();
            console.log(i+".cbo_paytype:"+cbo_paytype);
            if (cbo_paytype == "CCD") {
                ccno = jq("[name=ccd_no_" + i + "]").val();
                cbo_paytype_ccno=cbo_paytype;
                cbo_payinput_ccno = cbo_payinput * 1;
            }
        }
        jq("#div_load").show();
        jq.post("scnew_pop.php",
                {
                    act: "doCheckCreditCard", 
                    sccode: sccode,
                    ccno: ccno,
                    cbo_paytype:cbo_paytype_ccno,
                    cbo_payinput:cbo_payinput_ccno,
                },
                function (data) {
                    data = jq.parseJSON( data );
                    jq("#div_load").hide();
                    console.log("doCheckCreditCard:" + data);
                    if(data.status=='error' && <?= $scnew_sc ? "1" : "0" ?>){
                        alert(data.msg);
                        return false;
                    }
//                    var chk = confirm("Are you sure to process transaction ?");
//                    var chk = confirm(data);
                    var chk = confirm(data.msg + "Are you sure to process transaction ?");
                    if (!chk) {
                        return false;
                    }
//                    return;
                    jq("#div_load").show();
                    jq("#act").val("doAddNewSCOrderPaymentGen");
                    jq("#action").val("generate");
                    jq("#msg").val("");
//        jq('#frm_invcnt_fltr').prop('action', 'scnew_submit.php');
                    jq('#frm_invcnt_fltr').submit();
                }
        );

    }
    jq(function () {
        jq('.num').keyup(function () {
            this.value = this.value.toUpperCase().replace(/[^0-9.]/g, '');
            var valA = this.value.indexOf(".");
            var valB = this.value.indexOf(".", valA + 1);
            if ((valA >= 0) && (valB >= 0)) {
                this.value = this.value.substring(0, valB);
            }
            if ((this.value.length >= 2) && (this.value.substring(0, 1) == ".")) {
                this.value = this.value * 1;
            }
            if (this.value == ".") {
                this.value = "0."
            }
        });
        jq('.num').change(function () {
//            this.value = this.value.toFixed(2);
        });
        jq('.numdec').keyup(function () {
            var maxqty = jq("#" + this.id).attr("maxqty");
            this.value = this.value.toUpperCase().replace(/[^0-9]/g, '');
            if ((this.value * 1) > (maxqty * 1)) {
                this.value = maxqty;
            }
            doTotInvTemp();
        });

        jq('input[name*=ccd_no]').keyup(function () {
            this.value = this.value.toUpperCase().replace(/[^0-9]/g, '');
        });
        jq('input[name*=ccd2_no]').keyup(function () {
            this.value = this.value.toUpperCase().replace(/[^0-9]/g, '');
        });
        jq('input[name*=ccd_autho_2]').keyup(function () {
            this.value = this.value.toUpperCase().replace(/[^0-9]/g, '');
        });
        jq('input[name*=ccd2_autho]').keyup(function () {
            this.value = this.value.toUpperCase().replace(/[^0-9]/g, '');
        });
        jq('input[name*=cbo_payinput]').keyup(function () {
            this.value = this.value.toUpperCase().replace(/[^0-9.]/g, '');
        });
        <?php
        if($scnew_sc){
        ?>
            jq('#ccd1_flag').parent().hide();
            jq('#ccd2_flag').parent().hide();
        <?php
            }
        ?>
    });

    doTotInvTemp();
    <?php
    
        if ($scadd_default=='0'){
    ?>
        jq('[name*=txt_bill_addr]').val("<?= $sc_info[addr1] . ($sc_info[addr2] == "" ? "" : "\\n$sc_info[addr2]") . ($sc_info[addr3] == "" ? "" : "\\n$sc_info[addr3]") ?>");
        jq('[name*=txt_bill_postcd]').val("<?= $sc_info[postcd] ?>");
        jq('[name*=txt_bill_tel]').val("<?= $sc_info[hmphone] ?>");
        setTimeout("doRundoGetShipToCityTown('<?= "$sc_info[city]" ?>');", 3000);   
    <?php
        } else {
    ?>
        jq('[name*=txt_bill_addr]').val("<?= $sc_info[saddr1] . ($sc_info[saddr2] == "" ? "" : "\\n$sc_info[saddr2]") . ($sc_info[saddr3] == "" ? "" : "\\n$sc_info[saddr3]") ?>");
        jq('[name*=txt_bill_postcd]').val("<?= $sc_info[spostcd] ?>");
        jq('[name*=txt_bill_tel]').val("<?= $sc_info[stel_no] ?>");
        setTimeout("doRundoGetShipToCityTown('<?= "$sc_info[scity]" ?>');", 3000);   
    <?php
        }
    ?>
    // jq('[name*=txt_bill_addr]').val("<?= $sub_mssc_ext[saddr1] . ($sub_mssc_ext[saddr2] == "" ? "" : "\\n$sub_mssc_ext[saddr2]") . ($sub_mssc_ext[saddr3] == "" ? "" : "\\n$sub_mssc_ext[saddr3]") ?>");
    // jq('[name*=txt_bill_postcd]').val("<?= $sub_mssc_ext[spostcd] ?>");
    
    // jq('[name*=txt_bill_tel]').val("<?= $sub_mssc_ext[stel_no] ?>");
    jq('.OTH').val('<?= ($creditflag == 0 ? 0 : (($credit_history >= $total) ? $total : $credit_history)) ?>');
<?= ($creditflag == 0 ? "jq(\".trOTH\").hide();" : (($credit_history > 0) ? "" : "jq(\".trOTH\").hide();")) ?>
	//setTimeout("doRundoGetShipToCity();",1000); 
    // setTimeout("doRundoGetShipToCityTown('<?= "$sub_mssc_ext[scity]" ?>');", 3000);    
    
    calc_tot_pay();
</script>
