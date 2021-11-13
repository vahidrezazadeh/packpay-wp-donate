<?php defined('ABSPATH') or die('&lt;h3&gt;Access denied!'); ?>

<div class="wrap">
<div id="icon-edit" class="icon32 icon32-posts-post"><br /></div><h2>حامیان مالی</h2>

<?php 
$arrStatus=[
    'OK'=>'موفق',
    'SEND'=>'ارسال به درگاه',
    'ERROR'=>'نا موفق'];
/*
<ul class='subsubsub'>
	<li class='all'><a href='edit.php?post_type=post' class="current">همه <span class="count">(13)</span></a> |</li>
	<li class='publish'><a href='edit.php?post_status=publish&amp;post_type=post'>منتشرشده <span class="count">(12)</span></a> |</li>
	<li class='draft'><a href='edit.php?post_status=draft&amp;post_type=post'>پیش‌نویس <span class="count">(1)</span></a></li>
</ul>
*/
?>
<form id="posts-filter" action="<?php echo PP_GetCallBackURL(); ?>" method="post">


<input type="hidden" id="_wpnonce" name="_wpnonce" value="8aa9aa1697" /><input type="hidden" name="_wp_http_referer" value="/Project/wp-admin/edit.php" />	<div class="tablenav top">

<div class='tablenav-pages one-page'><span class="displaying-num">مبلغ کل حمایت شده :<?php echo number_format(get_option("PP_TotalAmount"));?> ریال</span>
</div>
</div>
<input type="hidden" name="mode" value="list" />

		<br class="clear" />
	</div>
<table class="wp-list-table widefat fixed posts" cellspacing="0">
	<thead>
	<tr>
		<th scope='col' id='title' class='manage-column column-title sortable desc'  style="">
		<span>نام و نام خانوادگی</span><span class="sorting-indicator"></span></th>
		<th scope='col' id='author' class='manage-column column-author'  style="">مبلغ (ریال)</th>
			<th scope='col' id='author' class='manage-column column-author'  style="">شماره پیگیری</th>
		<th scope='col' id='categories' class='manage-column column-categories'  style="">موبایل</th>
		<th scope='col' id='tags' class='manage-column column-tags'  style="">ایمیل</th>
		<th scope='col' id='comments' class='manage-column column-tags'  style="">توضیحات</th>
		<th scope='col' id='date' class='manage-column column-date sortable asc'  style=""><span>تاریخ</span><span class="sorting-indicator"></span></th>
	</tr>
	</thead>

	<tfoot>
  <tr>
		<th scope='col' id='title' class='manage-column column-title sortable desc'  style="">
		<span>نام و نام خانوادگی</span><span class="sorting-indicator"></span></th>
		<th scope='col' id='author' class='manage-column column-author'  style="">مبلغ (ریال)</th>
		<th scope='col' id='author' class='manage-column column-author'  style="">شماره پیگیری</th>
		<th scope='col' id='categories' class='manage-column column-categories'  style="">موبایل</th>
		<th scope='col' id='tags' class='manage-column column-tags'  style="">ایمیل</th>
		<th scope='col' id='comments' class='manage-column column-tags'  style="">توضیحات</th>
		<th scope='col' id='date' class='manage-column column-date sortable asc'  style=""><span>تاریخ</span><span class="sorting-indicator"></span></th>
	</tr>
	</tfoot>

	<tbody id="the-list">
	<?php
	////////////Page
	$pagenum = isset($_GET['pagenum']) ? absint($_GET['pagenum']) : 1;

    $limit = 15; // number of rows in page

    $offset = ($pagenum - 1) * $limit;



	///
  global $wpdb;
	$tbnPP = $wpdb->prefix . TABLE_DONATE;
	$total = $wpdb->get_var("SELECT COUNT(`DonateID`) FROM {$tbnPP}");

$num_of_pages = ceil($total / $limit);
	if(isset($_REQUEST['searchbyname']) && $_REQUEST['searchbyname'] != '')
	{
		$SearchName = htmlspecialchars(strip_tags(trim($_REQUEST['searchbyname'])), ENT_QUOTES);
		$result = $wpdb->get_results( "SELECT * FROM `$tbnPP` where `Name` LIKE '%$SearchName%' ORDER BY DonateID DESC LIMIT $offset, $limit");
	}
	else
	{
		$result = $wpdb->get_results( "SELECT * FROM `$tbnPP` ORDER BY DonateID DESC LIMIT $offset, $limit");
	}
	foreach($result as $row) :
	?>
			
	
		<tr id="post-109" style="" class="post-109 type-post status-draft format-standard hentry category-news alternate iedit author-self" valign="top">
			
			<td class="post-title page-title column-title"><strong><?php echo $row->Name; ?></strong> <small><?php echo $arrStatus[$row->Status]; ?></small>
			<div class="locked-info"><span class="locked-avatar"></span> <span class="locked-text"></span></div>
		</td>
		<td class="author column-author"><?php echo number_format($row->AmountTomaan); ?></td>
			<td class="tags column-tags"><?php echo $row->Authority; ?></td>
		<td class="categories column-categories"><?php echo $row->Mobile; ?></td>
		<td class="tags column-tags"><?php echo $row->Email; ?></td>
		<td class="tags column-tags"><?php echo $row->Description; ?></td>
		<td class="date column-date"><?php echo PPgetDate($row->InputDate); ?></td>
	</tr>
<?php
endforeach;
?>
	</tbody>
</table>
	<div class="tablenav bottom">

		<div class="alignleft actions">
    <?php 
     
$page_links = paginate_links(array(

    'base' => add_query_arg('pagenum', '%#%'),

    'format' => '',

    'prev_text' => __('&laquo;', 'text-domain'),

    'next_text' => __('&raquo;', 'text-domain'),

    'total' => $num_of_pages,

    'current' => $pagenum

));


if ($page_links) {

    echo '<div class="tablenav"><div class="tablenav-pages alignleft actions bulkactions" style="margin: 1em 0">' . $page_links . '</div></div></div>';

}
  ?>

		</div>
		<br class="clear" />
	</div>
	<br class="clear" />
</form>	<br class="clear" />

<div id="ajax-response"></div>
<br class="clear" />

<style>
    #wpfooter{
        display:none;
    }
</style>
