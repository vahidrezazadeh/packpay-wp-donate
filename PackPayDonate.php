<?php
/*
Plugin Name: PackPay Donate - حمایت مالی
Plugin URI: http://pakpay.ir
Description: افزونه حمایت مالی از وبسایت ها -- برای استفاده تنها کافی است کد زیر را درون بخشی از برگه یا نوشته خود قرار دهید  [PayPackDonate]
Version: 1.0
Author:  vahid rezazadeh
Author URI: https://vahidrezazadeh.ir
*/

defined('ABSPATH') or die('Access denied!');
define('PPDonate', plugin_dir_path(__FILE__));
define('LIBDIR', PPDonate . '/lib');
define('TABLE_DONATE', 'pp_donate');

require_once ABSPATH . 'wp-admin/includes/upgrade.php';

if (is_admin()) {
    add_action('admin_menu', 'PP_AdminMenuItem');
    function PP_AdminMenuItem()
    {
        add_menu_page('تنظیمات افزونه حمایت مالی - پکپی', 'حمات مالی', 'administrator', 'PP_MenuItem', 'PP_MainPageHTML', /*plugins_url( 'myplugin/images/icon.png' )*/
            '', 6);
        add_submenu_page('PP_MenuItem', 'نمایش حامیان مالی', 'نمایش حامیان مالی', 'administrator', 'PP_Hamian', 'PP_HamianHTML');
    }
}

function PP_MainPageHTML()
{
    include('PP_AdminPage.php');
}

function PP_HamianHTML()
{
    include('PP_Hamian.php');
}

function PPgetDate($date)
{
    if (function_exists('jdate')) {
        return jdate('Y-m-d H:i', $date);
    }
    if (function_exists('parsidate')) {
        return parsidate('Y-m-d H:i', $date);
    }
    return $date;
}

add_action('init', 'PackPayDonateShortcode');
function PackPayDonateShortcode()
{
    add_shortcode('PayPackDonate', 'PPDonateForm');
}

function post2http($fields_arr, $url, $headers, $userPass = '')
{

    $fields_string = '';
    //url-ify the data for the POST

    $fields_string = json_encode($fields_arr);

    //open connection
    $ch = curl_init();

    //set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if (count($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    if ($userPass) {
        curl_setopt($ch, CURLOPT_USERPWD, $userPass);

    }


    //execute post
    $res = curl_exec($ch);

    //close connection
    curl_close($ch);


    return $res;
}

function getToken()
{
    $_ref_token = get_option('PP_TOKEN');
    $_client_ID = get_option('PP_ClientID');
    $_client_Secret = get_option('PP_ClientSecret');
    if ($_ref_token == '' || $_client_ID == '' || $_client_Secret == '') {
        $error = 'لطفا اطلاعات را در مدیریت وارد کنید.' . "<br>\r\n";
    }


    $url = "https://dashboard.packpay.ir/oauth/token?grant_type=refresh_token&refresh_token=$_ref_token";

    $headers = [
    ];

    $body = [
        'refresh_token' => $_ref_token
    ];
    $userPass = "$_client_ID:$_client_Secret";
    $result = post2http($body, $url, $headers, $userPass);
    return json_decode($result);
}

function getHttpReq($url, $headers)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result);
}

function PPDonateForm()
{
    $out = '';
    $error = '';
    $message = '';


    $PP_Unit = get_option('PP_Unit');

    $Amount = '';
    $Description = '';
    $Name = '';
    $Mobile = '';
    $Email = '';

    //////////////////////////////////////////////////////////
    //            REQUEST
    if (isset($_POST['submit']) && $_POST['submit'] == 'پرداخت') {
        $_ref_token = get_option('PP_TOKEN');
        $_client_ID = get_option('PP_ClientID');
        $_client_Secret = get_option('PP_ClientSecret');
        if ($_ref_token == '' || $_client_ID == '' || $_client_Secret == '') {
            $error = 'لطفا اطلاعات را در مدیریت وارد کنید.' . "<br>\r\n";
        } else {

            $Amount = filter_input(INPUT_POST, 'PP_Amount', FILTER_SANITIZE_SPECIAL_CHARS);


            if (is_numeric($Amount) != false) {
                //Amount will be based on Toman  - Required
                if ($PP_Unit == 'ریال')
                    $SendAmount = $Amount;
                else
                    $SendAmount = $Amount * 10;
            } else {
                $error .= 'مبلغ به درستی وارد نشده است' . "<br>\r\n";
            }
            $Description = filter_input(INPUT_POST, 'PP_Description', FILTER_SANITIZE_SPECIAL_CHARS);  // Required
            $Name = filter_input(INPUT_POST, 'PP_Name', FILTER_SANITIZE_SPECIAL_CHARS);  // Required
            $Mobile = filter_input(INPUT_POST, 'mobile', FILTER_SANITIZE_SPECIAL_CHARS); // Optional
            $Email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_SPECIAL_CHARS); // Optional

            if ($Email && !filter_var($Email, FILTER_VALIDATE_EMAIL)) {
                $error .= 'ایمیل معتبر نیست' . "<br>\r\n";
            }
            if ($SendAmount < 1000) {
                $error .= 'مبلغ نباید کمتر از ۱۰۰ تومان باشد' . "<br>\r\n";
            }

            $SendDescription = $Name . ' | ' . $Mobile . ' | ' . $Email . ' | ' . $Description;

            if ($error == '') // اگر خطایی نباشد
            {
                $CallbackURL = PP_GetCallBackURL();  // Required
                //     $CallbackURL = "https://vahidrezazadeh.ir/%d8%aa%d8%b3%d8%aa-%d8%ad%d9%85%d8%a7%db%8c%d8%aa-%d9%85%d8%a7%d9%84%db%8c";
                $result = getToken();
                if (isset($result->access_token) && $result->access_token) {

                    $url = "https://dashboard.packpay.ir/developers/bank/api/v1/purchase?amount=$SendAmount&callback_url=$CallbackURL&verify_on_request=true";

                    $headers = array();
                    $headers[] = 'Accept: application/json';
                    $headers[] = 'Authorization: Bearer ' . $result->access_token;

                    $body = [
                        "amount" => $SendAmount,
                        'callback_url' => $CallbackURL,
                        'payer_name' => $Name,
                        'payer_id' => $Mobile
                    ];
                    $result = post2http($body, $url, $headers);
                    $result = json_decode($result);
                    if ($result->status == 0) {

                        PP_AddDonate(array(
                            'Authority' => $result->reference_code,
                            'Name' => $Name,
                            'AmountTomaan' => $SendAmount,
                            'Mobile' => $Mobile,
                            'Email' => $Email,
                            'InputDate' => current_time('mysql'),
                            'Description' => $Description,
                            'Status' => 'SEND'
                        ), array(
                            '%s',
                            '%s',
                            '%d',
                            '%s',
                            '%s',
                            '%s',
                            '%s',
                            '%s'
                        ));
                        $Location = "https://dashboard.packpay.ir/bank/purchase/send?RefId=" . $result->reference_code;

                        return "<script>document.location = '${Location}'</script><center>در صورتی که به صورت خودکار به درگاه بانک منتقل نشدید <a href='${Location}'>اینجا</a> را کلیک کنید.</center>";

                    } else {
                        $error .= $result->message . "<br>\r\n";
                    }


                } else {
                    $error .= $result->error_description . "<br>\r\n";
                }

            }
        }
    }
    //// END REQUEST


    ////////////////////////////////////////////////////
    ///             RESPONSE
    if (isset($_GET['reference_code'])) {
        $refCode = $_GET['reference_code'];

        $Record = PP_GetDonate($refCode);
        if ($Record === false) {
            $error .= 'چنین تراکنشی در سایت ثبت نشده است' . "<br>\r\n";
        } else {
            $headers = array();
            $token = getToken();
            $headers[] = 'Accept: application/json;charset=UTF-8';
            $url = "https://dashboard.packpay.ir/developers/bank/api/v1/purchase/verify?reference_code=" . $refCode;

            $headers[] = 'Authorization: Bearer ' . $token->access_token;
            $body = [
                "reference_code" => $refCode
            ];
            $result = post2http($body, $url, $headers);
            $result = json_decode($result);
            if ($result->status == 0 && $result->message == 'successful') {
                PP_ChangeStatus($refCode, 'OK');
                $message .= get_option('PP_IsOk');
                $message = str_replace('[refcode]', $refCode, $message);
                //   $message .= 'کد پیگیری تراکنش:' . $refCode . "<br>\r\n";
                $PP_TotalAmount = get_option("PP_TotalAmount");
                update_option("PP_TotalAmount", $PP_TotalAmount + $Record['AmountTomaan']);
            } else {
                PP_ChangeStatus($refCode, 'ERROR');
                $error .= get_option('PP_IsError') . "<br>\r\n";
            }


        }

    } else {
        //  $error .= 'پرداخت انجام نشده.' . "<br>\r\n";
    }
    ///     END RESPONSE

    $style = '';

    if (get_option('PP_UseCustomStyle') == 'true') {
        $style = get_option('PP_CustomStyle');
    } else {
        $style = '#PP_MainForm {  width: 400px;  height: auto;  margin: 0 auto;  direction: rtl; }  #PP_Form {  width: 96%;  height: auto;  float: right;  padding: 10px 2%; }  #PP_Message,#PP_Error {  width: 90%;  margin-top: 10px;  margin-right: 2%;  float: right;  padding: 5px 2%;  border-right: 2px solid #006704;  background-color: #e7ffc5;  color: #00581f; }  #PP_Error {  border-right: 2px solid #790000;  background-color: #ffc9c5;  color: #580a00; }  .PP_FormItem {  width: 90%;  margin-top: 10px;  margin-right: 2%;  float: right;  padding: 5px 2%; }    .PP_FormLabel {  width: 35%;  float: right;  padding: 3px 0; }  .PP_ItemInput {  width: 64%;  float: left; }  .PP_ItemInput input {  width: 90%;  float: right;  border-radius: 3px;  box-shadow: 0 0 2px #00c4ff;  border: 0px solid #c0fff0;  font-family: inherit;  font-size: inherit;  padding: 3px 5px; }  .PP_ItemInput input:focus {  box-shadow: 0 0 4px #0099d1; }  .PP_ItemInput input.error {  box-shadow: 0 0 4px #ef0d1e; }  input.PP_Submit {  background: none repeat scroll 0 0 #2ea2cc;  border-color: #0074a2;  box-shadow: 0 1px 0 rgba(120, 200, 230, 0.5) inset, 0 1px 0 rgba(0, 0, 0, 0.15);  color: #fff;  text-decoration: none;  border-radius: 3px;  border-style: solid;  border-width: 1px;  box-sizing: border-box;  cursor: pointer;  display: inline-block;  font-size: 13px;  line-height: 26px;  margin: 0;  padding: 0 10px 1px;  margin: 10px auto;  width: 50%;  font: inherit;  float: right;  margin-right: 24%; }';
    }


    $out = '
  <style>
    ' . $style . '
  </style>
      <div style="clear:both;width:100%;float:right;">
	        <div id="PP_MainForm">
          <div id="PP_Form">';

    if ($message != '') {
        $out .= "<div id=\"PP_Message\">
    ${message}
            </div>";
    }

    if ($error != '') {
        $out .= "<div id=\"PP_Error\">
    ${error}
            </div>";
    }

    $out .= '<form method="post">
              <div class="PP_FormItem">
                <label class="PP_FormLabel">مبلغ :</label>
                <div class="PP_ItemInput">
                  <input style="width:60%" type="text" name="PP_Amount" value="' . $Amount . '" />
                  <span style="margin-right:10px;">' . $PP_Unit . '</span>
                </div>
              </div>
              
              <div class="PP_FormItem">
                <label class="PP_FormLabel">نام و نام خانوادگی :</label>
                <div class="PP_ItemInput"><input type="text" name="PP_Name" value="' . $Name . '" /></div>
              </div>
              
              <div class="PP_FormItem">
                <label class="PP_FormLabel">تلفن همراه :</label>
                <div class="PP_ItemInput"><input type="text" name="mobile" value="' . $Mobile . '" /></div>
              </div>
              
              <div class="PP_FormItem">
                <label class="PP_FormLabel">ایمیل :</label>
                <div class="PP_ItemInput"><input type="text" name="email" style="direction:ltr;text-align:left;" value="' . $Email . '" /></div>
              </div>
              
              <div class="PP_FormItem">
                <label class="PP_FormLabel">توضیحات :</label>
                <div class="PP_ItemInput"><input type="text" name="PP_Description" value="' . $Description . '" /></div>
              </div>
              
              <div class="PP_FormItem">
                <input type="submit" name="submit" value="پرداخت" class="PP_Submit" />
              </div>
              
            </form>
          </div>
        </div>
      </div>
	';

    return $out;
}

/////////////////////////////////////////////////
// تنظیمات اولیه در هنگام اجرا شدن افزونه.
register_activation_hook(__FILE__, 'PackPayDonate_install');
function PackPayDonate_install()
{
    PP_CreateDatabaseTables();
}

function PP_CreateDatabaseTables()
{
    global $wpdb;
    $ppDonateTable = $wpdb->prefix . TABLE_DONATE;
    // Creat table
    $nazrezohoor = "CREATE TABLE IF NOT EXISTS `$ppDonateTable` (
					  `DonateID` int(11) NOT NULL AUTO_INCREMENT,
					  `Authority` varchar(50) NOT NULL,
					  `Name` varchar(50) CHARACTER SET utf8 COLLATE utf8_persian_ci NOT NULL,
					  `AmountTomaan` int(11) NOT NULL,
					  `Mobile` varchar(11) ,
					  `Email` varchar(50),
					  `InputDate` varchar(20),
					  `Description` varchar(100) CHARACTER SET utf8 COLLATE utf8_persian_ci,
					  `Status` varchar(5),
					  PRIMARY KEY (`DonateID`),
					  KEY `DonateID` (`DonateID`)
					) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
    dbDelta($nazrezohoor);
    // Other Options
    add_option("PP_TotalAmount", 0, '', 'yes');
    add_option("PP_TotalPayment", 0, '', 'yes');
    add_option("PP_IsOK", 'با تشکر پرداخت شما به درستی انجام شد.', '', 'yes');
    add_option("PP_IsError", 'متاسفانه پرداخت انجام نشد.', '', 'yes');

    $style = '#PP_MainForm {
 
  height: auto;
  
  direction: rtl;
}

#PP_Form {
  width: 96%;
  height: auto;
  float: right;
  padding: 10px 2%;
}

#PP_Message,#PP_Error {
  width: 90%;
  margin-top: 10px;
  margin-right: 2%;
  float: right;
  padding: 5px 2%;
  border-right: 2px solid #006704;
  background-color: #e7ffc5;
  color: #00581f;
}

#PP_Error {
  border-right: 2px solid #790000;
  background-color: #ffc9c5;
  color: #580a00;
}

.PP_FormItem {
  width: 90%;
  margin-top: 10px;
  margin-right: 2%;
  float: right;
  padding: 5px 2%;
}

.PP_FormLabel {
  width: 35%;
  float: right;
  padding: 3px 0;
}

.PP_ItemInput {
  width: 64%;
  float: left;
}

.PP_ItemInput input {
  width: 90%;
  float: right;
  border-radius: 3px;
  box-shadow: 0 0 2px #00c4ff;
  border: 0px solid #c0fff0;
  font-family: inherit;
  font-size: inherit;
  padding: 3px 5px;
}

.PP_ItemInput input:focus {
  box-shadow: 0 0 4px #0099d1;
}

.PP_ItemInput input.error {
  box-shadow: 0 0 4px #ef0d1e;
}

input.PP_Submit {
  background: none repeat scroll 0 0 #2ea2cc;
  border-color: #0074a2;
  box-shadow: 0 1px 0 rgba(120, 200, 230, 0.5) inset, 0 1px 0 rgba(0, 0, 0, 0.15);
  color: #fff;
  text-decoration: none;
  border-radius: 3px;
  border-style: solid;
  border-width: 1px;
  box-sizing: border-box;
  cursor: pointer;
  display: inline-block;
  font-size: 13px;
  line-height: 26px;
  margin: 0;
  padding: 0 10px 1px;
  margin: 10px auto;
  width: 50%;
  font: inherit;
  float: right;
  margin-right: 24%;
}';
    add_option("PP_CustomStyle", $style, '', 'yes');
    add_option("PP_UseCustomStyle", 'false', '', 'yes');
}

function PP_GetDonate($Authority)
{
    global $wpdb;
    $Authority = strip_tags($wpdb->escape($Authority));

    if ($Authority == '')
        return false;

    $tbl = $wpdb->prefix . TABLE_DONATE;

    $res = $wpdb->get_results("SELECT * FROM " . $tbl . " WHERE Authority = '${Authority}' LIMIT 1", ARRAY_A);

    if (count($res) == 0)
        return false;

    return $res[0];
}

function PP_AddDonate($Data, $Format)
{
    global $wpdb;

    if (!is_array($Data))
        return false;

    $tbl = $wpdb->prefix . TABLE_DONATE;

    $res = $wpdb->insert($tbl, $Data, $Format);

    if ($res == 1) {
        $totalPay = get_option('PP_TotalPayment');
        $totalPay += 1;
        update_option('PP_TotalPayment', $totalPay);
    }

    return $res;
}

function PP_ChangeStatus($Authority, $Status)
{
    global $wpdb;
    $Authority = strip_tags($wpdb->escape($Authority));
    $Status = strip_tags($wpdb->escape($Status));

    if ($Authority == '' || $Status == '')
        return false;

    $tbl = $wpdb->prefix . TABLE_DONATE;

    $res = $wpdb->query("UPDATE {$tbl} SET `Status` = '${Status}' WHERE `Authority` = '${Authority}'");

    return $res;
}


function PP_GetCallBackURL()
{
    $pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";

    $ServerName = ($_SERVER["SERVER_NAME"]);
    //$ServerPort = htmlspecialchars($_SERVER["SERVER_PORT"], ENT_QUOTES, "utf-8");
    $ServerRequestUri = ($_SERVER["REQUEST_URI"]);

    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $ServerName . $_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $ServerName . $ServerRequestUri;
    }
    return urlencode($pageURL);
}

?>