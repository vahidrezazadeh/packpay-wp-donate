<?php

defined('ABSPATH') or die('Access denied!');

if ($_POST) {

    if (isset($_POST['PP_TOKEN'])) {
        update_option('PP_TOKEN', $_POST['PP_TOKEN']);
    }
    if (isset($_POST['PP_Password'])) {
        update_option('PP_Password', $_POST['PP_Password']);
    }
    if (isset($_POST['PP_ClientID'])) {
        update_option('PP_ClientID', $_POST['PP_ClientID']);
    }
    if (isset($_POST['PP_ClientSecret'])) {
        update_option('PP_ClientSecret', $_POST['PP_ClientSecret']);
    }

    if (isset($_POST['PP_IsOK'])) {
        update_option('PP_IsOK', $_POST['PP_IsOK']);
    }

    if (isset($_POST['PP_IsError'])) {
        update_option('PP_IsError', $_POST['PP_IsError']);
    }

    if (isset($_POST['PP_Unit'])) {
        update_option('PP_Unit', $_POST['PP_Unit']);
    }

    if (isset($_POST['PP_UseCustomStyle'])) {
        update_option('PP_UseCustomStyle', 'true');

        if (isset($_POST['PP_CustomStyle'])) {
            update_option('PP_CustomStyle', strip_tags($_POST['PP_CustomStyle']));
        }

    } else {
        update_option('PP_UseCustomStyle', 'false');
    }

    echo '<div class="updated" id="message"><p><strong>تنظیمات ذخیره شد</strong>.</p></div>';

}

?>
<h2 id="add-new-user">تنظیمات افزونه حمایت مالی - پکپی</h2>
<h2 id="add-new-user">جمع تمام پرداخت ها : <?php echo get_option("PP_TotalAmount"); ?> تومان</h2>
<form method="post">
    <table class="form-table">
        <tbody>
        <tr class="">
            <th><label for="PP_TOKEN">رفرش توکن</label></th>
            <td>
                <input type="text" class="regular-text" value="<?php echo get_option('PP_TOKEN'); ?>"
                       id="PP_TOKEN" name="PP_TOKEN">
            </td>
        </tr>
      
        <tr class="">
            <th><label for="PP_ClientID">Client ID</label></th>
            <td>
                <input type="text" class="regular-text" value="<?php echo get_option('PP_ClientID'); ?>"
                       id="PP_ClientID" name="PP_ClientID">
            </td>
        </tr>
        <tr class="">
            <th><label for="PP_ClientSecret">Client Secret</label></th>
            <td>
                <input type="text" class="regular-text" value="<?php echo get_option('PP_ClientSecret'); ?>"
                       id="PP_ClientSecret" name="PP_ClientSecret">
            </td>
        </tr>
        <tr>
            <th><label for="PP_IsOK">پرداخت صحیح</label></th>
            <td><textarea type="text" class="regular-text" id="PP_IsOK"
                       name="PP_IsOK"><?php echo get_option('PP_IsOK'); ?></textarea>
                       <br>
                        <span>
برای نمایش کد پیگیری در متن از کد [refcode] استفاده کنید.
                       </span>
                       </td>
        </tr>
        <tr>
            <th><label for="PP_IsError">خطا در پرداخت</label></th>
            <td><textarea type="text" class="regular-text" id="PP_IsError"
                       name="PP_IsError"><?php echo get_option('PP_IsError'); ?></textarea></td>
        </tr>

        <tr class="">
            <th><label for="PP_Unit">واحد پول</label></th>
            <td>
                <?php $PP_Unit = get_option('PP_Unit'); ?>
                <select id="PP_Unit" name="PP_Unit">
                    <option <?php if ($PP_Unit == 'تومان') echo 'selected="selected"' ?>>تومان</option>
                    <option <?php if ($PP_Unit == 'ریال') echo 'selected="selected"' ?>>ریال</option>
                </select>
            </td>
        </tr>

        <tr class="">
            <th>استفاده از استایل سفارشی</th>
            <td>
                <?php $PP_UseCustomStyle = get_option('PP_UseCustomStyle') == 'true' ? 'checked="checked"' : ''; ?>
                <input type="checkbox" name="PP_UseCustomStyle" id="PP_UseCustomStyle"
                       value="true" <?php echo $PP_UseCustomStyle ?> /><label for="PP_UseCustomStyle">استفاده از استایل
                    سفارشی برای فرم</label><br>
            </td>
        </tr>


        <tr class=""
            id="PP_CustomStyleBox" <?php if (get_option('PP_UseCustomStyle') != 'true') echo 'style="display:none"'; ?>>
            <th>استایل سفارشی</th>
            <td>
                <textarea style="width: 90%;min-height: 400px;direction:ltr;" name="PP_CustomStyle"
                          id="PP_CustomStyle"><?php echo get_option('PP_CustomStyle') ?></textarea><br>
            </td>
        </tr>

        </tbody>
    </table>
    <p class="submit"><input type="submit" value="به روز رسانی تنظیمات" class="button button-primary" id="submit"
                             name="submit"></p>
</form>

<script>
    if (typeof jQuery == 'function') {
        jQuery("#PP_UseCustomStyle").change(function () {
            if (jQuery("#PP_UseCustomStyle").prop('checked') == true)
                jQuery("#PP_CustomStyleBox").show(500);
            else
                jQuery("#PP_CustomStyleBox").hide(500);
        });
    }
</script>

