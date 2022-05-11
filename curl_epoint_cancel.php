<?
  include("../module/global.inc");

//  		$epoint_server = "neweworld.dxn2u.com";
// 		$epoint_dir    = "web/eppayPH";
// 		//$epoint_server = "eworld2.dxn2u.com";
// 		//$epoint_dir    = "ecom-live/web/eppayPH";
// 		$epoint_port   = 443;
  $debug = 0;
  
//   $path1 = "https://eworld.dxn2u.com/web/eppayPH/payment_confirm.php?ewno=PO1911100001380-4590&cbno=011439550-SC191113001403&cbDate=2019-11-10+16%3A20%3A53&epMemCode=011471881&amt=4.7&ewno_enc=PO1911100001381-2792&amt_enc=811&cbno_enc=011439550-SE191113000107";
//   $path1 = "https://eworld.dxn2u.com/web/eppayPH/payment_cancel.php?ewno=PO2001270000157-6939&amt=149.80&memcode=011545685&rollback_flag=1";
//   $path1 = "https://eworld.dxn2u.com/web/eppayPH/payment_confirm.php?ewno=PO2002090000769-7651&cbno=010002606-SC200210002332&cbDate=2020-02-09+13%3A09%3A41&epMemCode=011609242&amt=196.2&ewno_enc=PO2002090000770-0198&amt_enc=56&cbno_enc=010002606-SE200210000360";
/*
  //payment_confirm
  $xloccd = '000000020';
  $xcode = '011184788';
  $xtrdt = '2020-05-14 13:24:58';
  $xamt = '246.95';
  $xewtrxno = 'PO2005140000804-1221';
  $xcbno ='SLW-MI200514002141';
  
  $path1 = "https://eworld.dxn2u.com/web/eppayPH/payment_confirm.php?ewno=".
    "$xewtrxno&cbno=$xcbno&".
    "cbDate=".urlencode($xtrdt)."&epMemCode=$xcode&amt=$xamt".
    "&ewno_enc=&amt_enc=&cbno_enc=";
*/

  //payment_cancel
  $xloccd = '064000871';
  $xcode = '065748603';
  //$xtrdt = '2020-06-11 10:55:14';
  $xamt = '87';
  $xewtrxno = 'PO2006120000178-4097';
  //$xcbno ='011159127-SC200307005026';
  
//   $path1 = "https://eworld.dxn2u.com/web/eppayPH/payment_cancel.php?ewno=".
  $path1 = "https://eworld.dxn2u.com/eppayPH/payment_cancel.php?ewno=".
    "$xewtrxno&memcode=$xcode&amt=$xamt&rollback_flag=1";

  //$path1 = "https://eworld.dxn2u.com/web/eppayPH/payment_confirm.php?ewno=PO2002260000177-1337&cbno=010002606-SC200228003278&cbDate=2020-02-26+13%3A39%3A57&epMemCode=011635941&amt=4.5&ewno_enc=PO2002260000179-8082&amt_enc=1&cbno_enc=010002606-SE200228000457";
//   $path1 = "https://eworld.dxn2u.com/web/eppayPH/payment_confirm.php?ewno=PO2002290000653-1737&cbno=011133014-SC200229005329&cbDate=2020-02-29+18%3A50%3A46&epMemCode=011384064&amt=31.35&ewno_enc=&amt_enc=0&cbno_enc=";
//   $path1 = "https://eworld.dxn2u.com/ecom-live/web/eppayPH/payment_cancel.php?ewno=PO2002290000437-5220&amt=92.4&memcode=011426373&rollback_flag=1";

//   $path1 = "https://eworld.dxn2u.com/web/eppayPH/payment_confirm.php?ewno=PO2003090000230-9686&cbno=010002606-SC200311003998&cbDate=2020-03-09+10%3A47%3A52&epMemCode=011568071&amt=4.5&ewno_enc=PO2003090000231-0156&amt_enc=15&cbno_enc=010002606-SE200311000594";

//   $path1 = "https://ew-train.dxn2u.com/web/eppayPH/payment_checkbal.php?po_no=PO2003200000029-5758&member_code=010001231&camount=2";
//   $path1 = "https://eworld.dxn2u.com/web/eppayPH/payment_confirm.php?ewno=PO2004120000208-9515&cbno=PP-MC200412001302&cbDate=2020-04-12+12%3A52%3A34&epMemCode=011467493&amt=106.35&ewno_enc=&amt_enc=0&cbno_enc=";

  if ($debug==1) {
    echo "$path1<br/>\n";
  }else{
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
    //flogepoint($xdeltrtype, $xepoint_result.'<--'.$path1, $xmemcode, $xrowh["loccd"], 8);
    curl_close($ch);
    echo "$path1<br/>xepoint_result=$xepoint_result<br/>";
  }
?>
