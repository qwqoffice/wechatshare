<?php     
/*
Plugin Name: WX Custom Share
Plugin URI: http://www.qwqoffice.com
Description: Custom the icon in Wechat share link.
Version: 1.3
Author: QwqOffice
Author URI: http://www.qwqoffice.com
License: GPL
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//获取所有自定义类型
function wxcs_get_all_post_types(){
	$args = array('public' => true, '_builtin' => false);
	$builtInArgs = array('public' => true, '_builtin' => true);
	$output = 'objects';
	$operator = 'and';
	
	$builtin_post_types = get_post_types( $builtInArgs, $output, $operator );
	$custom_post_types = get_post_types( $args, $output, $operator );
	$all_post_type = array_merge( $builtin_post_types, $custom_post_types );
	foreach( $all_post_type as $type ){
		$types[ $type->name ] = $type->label;
	}
	return $types;
}

//设置按钮
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wxcs_add_action_links');

function wxcs_add_action_links( $links ) {
	$mylinks = array(
		'<a href="' . admin_url('options-general.php?page=ws-settings') . '">'. __('Settings','wxshare'). '</a>'
	);
	return array_merge( $mylinks, $links );
}

//设置菜单
if( is_admin() ) add_action( 'admin_menu', 'wxcs_menu' );

function wxcs_menu(){
	//页名称，菜单名称，访问级别，菜单别名，点击该菜单时的回调函数（用以显示设置页面）
	add_options_page( __('Wechat Share Settings','wxshare'), __('Wechat Share','wxshare'), 'administrator', 'ws-settings', 'wxcs_html_page' );
}

//后台设置页面
function wxcs_html_page(){
?>
    <div class="wrap">
    	<h2><?php _e('Wechat Share Settings','wxshare') ?></h2>
        <form method="post" action="options.php">
        	<?php //下面这行代码用来保存表单中内容到数据库?>
            <?php wp_nonce_field('update-options');?>
        	<table class="form-table">
            <tr><th><?php _e('Post types to custom','wxshare') ?></th>
			<td>
            <?php $ws_settings = get_option('ws_settings');?>
            <?php foreach( wxcs_get_all_post_types() as $k => $v ): ?>
                <p>
                	<input type="checkbox" id="ws_settings[ws_display_types][<?php echo $k ?>]" name="ws_settings[ws_display_types][<?php echo $k ?>]" <?php checked(isset($ws_settings['ws_display_types'][$k]))?>>
                    <label for="ws_settings[ws_display_types][<?php echo $k ?>]"><?php echo $v ?> (<?php echo $k ?>)</label>
                </p>
            <?php endforeach; ?>
            </td></tr>
            <tr><th><?php _e('Other','wxshare') ?></th>
            <td>
            	<p>
                	<input type="checkbox" id="ws_settings[ws_del_data]" name="ws_settings[ws_del_data]" <?php checked(isset($ws_settings['ws_del_data']))?>>
                    <label for="ws_settings[ws_del_data]"><?php _e('Clear plugin data when uninstall','wxshare') ?></label>
                </p>
            </td>
            </tr>
            </table>
            <p class="submit">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="page_options" value="ws_settings">
                <input type="submit" value="<?php _e('Save','wxshare') ?>" class="button-primary">
            </p>
        </form>
    </div>
<?php   
}

//添加图片meta box
if( is_admin() ) add_action( 'add_meta_boxes', 'wxcs_add_box' );

function wxcs_add_box(){
	$ws_meta_box = array(
		'id' => 'ws-meta-box',
		'title' => __('Wechat Share','wxshare'),
		'context' => 'normal',
		'priority' => 'low'
	);
	$ws_settings = get_option('ws_settings');
	if( '' !== $ws_settings['ws_display_types'] && array_key_exists( get_post_type(), $ws_settings['ws_display_types'] ) ){
		add_meta_box( $ws_meta_box['id'], $ws_meta_box['title'], 'wxcs_show_box', get_post_type(), $ws_meta_box['context'], $ws_meta_box['priority'] );
	}
}

function wxcs_show_box() {
    global $ws_meta_box, $post;
	$pngdata = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAJYAAACVCAYAAAC6lQNMAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAXCSURBVHhe7dwNS+NMGIXh/f8/U0SsIloRUUS6e8RANzyT5mNO80xyD1zwrpvWhd5MJunk/XNzc3MCaiMsWBAWLAgLFoQFC8KCBWHBgrBgQViwICxYEBYsCAsWhAULwoIFYcGCsGBBWLAgLFgQFiwICxaEBQvCggVhwYKwYEFYsCAsWBAWLAgLFoQFC8KCBWHBgrBgQViwICxYEBYsCAsWhAULwoIFYV3R/f396fHx8XQ8Hv/z9PR0OhwO4WtaRVhmDw8Pp9fX19PX19dpzHh/f/8J7fb2Nny/VhCWiWanj4+P31ymD4WowKL3bgFhGWiGqjUUmCKNfk9mhFWRTl9LZqnS+P7+bm72IqxKNKtcWkcpEK2hXl5eTs/Pzz8Lef23ZrjPz8/fo8pDr4l+d0aEVYGiUjSloWgUUfTac3d3dz+hDY1WZi7CWmgoKv18ziyjwDSzRUPv2cKai7AWuBTV0gBKFwE65UbHZ0JYM7mj6pTi0ikzOj4LwprhWlF1otOifk/mm6iENdG1oxIFFP3OzFeJhDXBGlF1oqtF3TOLjs2AsEZaMyrRrBWNrKdDwhph7ag60V39MffH1kBYF2SJSqLTYdarQ8IakCkq0V33/iCsxmSLSrS3qz90KyI6dm2EFcgYlURhvb29hceujbB6skYlum/VH5wKG5A5Kom+3iGs5HQ/qLSfKkNUEv37uN2QXGnnZ5aotJUmGtGxGRDWP6UdBFmikij8rFeEsvuwFE40MkUVXQ1qZN5NuvuwopkgU1SlnQ3ZN/vtOqzSTJBlO4qiKj1kkXXR3tl1WNHaSh9kdOy1DUWVebtMZ9dhZb18H4pKP9ffR6/LZLdhRYt2rWWiY8eqsS4biirT2u+S3Yalmak/lly+dzsPllypbSUq2W1YNfc2nW9nmRvAlqISwjobc64Goz1SU28FbC0qIayzMWfhvvS5vy1GJYR1NuaeCpc89xet9TRajkp2G1Z0CpsblgJSCP0x9tTa/7e0HpXsNqzorrtOSdGxY0Qz4JQbmV1cW4hKNhOWPgytd6K/K4mGtqdEx16iWSsaY06HHcW1hahkE2Hpw+hORVP2gEdro7mnQ4m+0M7+nZ5L82GdR9WNsXFF6yy919xZq+YFQeuaDiuKqhtjTos6TUXfF869A1/zgqB1zYY1FJV+PnatEsWgMXW9Ji099+fWZFi1oupEs9ac5/Vaeu7Prbmwakcl/fecG0NLz/25NRWWI6pOd0pcMsNEX+8QVnKlhbbG0qg6OpVFPx8r68bBNTQTVnSPSKNWVEu19tyfWxNhlXYQZIlKovD3ekUo6cNSONHIFFXpaR+t26Lj9yB9WNFMkCmq0s6GqZv9tiZ1WKWZgOf+8ksdVrS2WrK1paahqKZsl9mq1GFlvXwfiko/199Hr9uTtGFFi3atZaJjr2koqkxrv7WlDSvaC7725TtRjZc2rGx7m4hqmqbCWutqkKimayqsNRbuRDUPp8IBRDVf2rDW3uZLVMukDSu6664POjq2NqJaLm1YEo25T9CMRVR1pA6r9nN/lxBVPanDitZZ+oAdsxZR1ZU6LH3Y0feFte/AE1V9qcOSms/9RYjKI31YUnqI4ng8hsePRVQ+TYSlD1gfdDR0WlQg0euG6HZG6T2JarkmwpLSKVFDIWj2GhOYFv6lJ340iKqOZsKS6Enj/lA0ikwhHg6HH3qd1mSlU2o3iKqepsISBVM6hS0ZWmsRVT3NhSUK4NLsM2VolpuzTkNZk2F1dBd+yeylOPf+NI1L02GJZhqtoYYW5P2hK0mdUqP3Qx3Nh3VOkSkYzWSigPR/j+n+zOx0PZsKC3kQFiwICxaEBQvCggVhwYKwYEFYsCAsWBAWLAgLFoQFC8KCBWHBgrBgQViwICxYEBYsCAsWhAULwoIFYcGCsGBBWLAgLFgQFiwICxaEBQvCggVhwYKwYEFYsCAsWBAWLAgLFoQFg5vTX+aGnVDy4FXBAAAAAElFTkSuQmCC';
	$meta = get_post_meta( $post->ID, 'ws_url', true );
	$meta_url = admin_url( 'admin-ajax.php' ) . '?action=resizeimg&s=';
?>
    
    <input type="hidden" name="ws_meta_box_nonce" value="<?php echo wp_create_nonce(basename(__FILE__)) ?>">
    <table class="form-table">
        <tr>
        	<th style="width:20%"><label for="ws-url"><?php _e('Icon','wxshare') ?></label></th>
            <td>
            	<input type="text" name="ws-url" id="ws-url" value="<?php echo $meta ? $meta : '' ?>" size="30" style="width:80%" autocomplete="off">
                <button type="button" id="ws_upload_btn" class="button insert-media add_media"><?php _e('Media','wxshare') ?></button>
                <p class="description" style="font-size:12px">
                	<?php _e('Enter a URL, Upload or choose from media library. And the image will be resized to 300*300. Please make sure the image you selected is square.','wxshare') ?>
                    <br>
                    <?php _e('Support png, jpg, gif and wbmp','wxshare') ?>
                </p>
            </td>
            <td></td>
        </tr>
        <tr>
        	<th style="20%"><label><?php _e('Preview','wxshare') ?></label></th>
            <td>
                <div class="ws-preview">
                	<p class="description"><?php _e('Timeline','wxshare') ?></p>
                	<div class="ws-timeline clearfix">
                    	<div class="ws-timeline-left">
                        	<img width="50" height="50" style="background-color:#CCC">
                        </div>
                        <div class="ws-timeline-right">
                        	<div class="ws-timeline-name"></div>
                            <div class="ws-timeline-content"></div>
                            <a href="<?php echo get_permalink($post) ?>" target="_blank">
                				<div class="ws-timeline-link"><table><tr>
                    			<td><img class="ws-link-img" src="<?php echo $meta ? $meta_url.urlencode($meta) : $pngdata ?>" alt="<?php _e('cannot get the image','wxshare') ?>"></td>
                    			<td><div class="ws-timeline-title-div">
                        			<span class="ws-timeline-title"><?php echo $post->post_title .' - '. get_bloginfo('name') ?></span>
                        		</div></td>
                    			</tr></table></div>
                            </a>
                            <div class="ws-timeline-meta clearfix">
                            	<span class="ws-timeline-time" style="color:#AAAAAA;font-size:12px"><?php _e('1 min ago','wxshare') ?></span>
                                <div class="ws-comment-btn">
                                	<div class="ws-comment-triangle-div"><div class="ws-comment-triangle"></div></div>
                                	<div class="ws-comment-circle"></div>
                                	<div class="ws-comment-circle"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <p class="description"><?php _e('Chat','wxshare') ?></p>
                    <div class="ws-chat clearfix">
                    	<div><img width="50" height="50" style="background-color:#CCC"></div>
                        <a href="<?php echo get_permalink($post) ?>" target="_blank">
						<div class="ws-chat-link">
                        <div class="ws-chat-triangle-div">
                        	<div class="ws-chat-triangle"></div>
                            <div class="ws-chat-triangle-shade"></div>
                        </div>
                    	<div class="ws-chat-main">
                        	<p class="ws-chat-title"><?php echo $post->post_title .' - '. get_bloginfo('name') ?></p>
                            <div class="ws-chat-desc-div">
                            	<div class="ws-chat-desc"><?php echo get_permalink($post) ?></div>
                            	<img class="ws-link-img" src="<?php echo $meta ? $meta_url.urlencode($meta) : $pngdata ?>" alt="<?php _e('cannot get the image','wxshare') ?>" style="float:right;">
                            </div>
                        </div>
                        </div>
						</a>
                    </div>
                </div>
            </td>
            <td></td>
        </tr>
    </table>
<style>
.ws-preview *{
	font-family:Microsoft YaHei;
}
.ws-preview > .description{
	font-style: normal;
}
.ws-preview{
	width:90%;
}
.ws-preview div{
	box-sizing:border-box;
}
.ws-timeline{
	background-color:#F8F8F8;
	margin-bottom:15px;
	padding:10px;
}
.ws-timeline > div{
	float:left;
}
.ws-timeline-left{
	width:50px;
}
.ws-timeline-right{
	padding-left:10px;
	width:calc(100% - 50px);
}
.ws-timeline-right > a{
	display:block;
	text-decoration:none;
}
.ws-timeline-name,
.ws-timeline-content{
	margin:7px 0;
	border-radius:8px;
	height:15px;
}
.ws-timeline-name{
	width:20%;
	background-color:#8599C1;
}
.ws-timeline-content{
	width:100%;
	background-color:#A2A2A2;
}
.ws-timeline td{
	display:table-cell;
	padding:0;
}
.ws-timeline-link{
	margin:7px 0;
	padding:4px;
	background-color:#ECECEC;
}
.ws-timeline-link:hover{
	cursor:pointer;
	background-color:#D0D0D0;
}
.ws-timeline-link .ws-link-img,
.ws-chat-main .ws-link-img{
	vertical-align:middle;
	width:50px;
	height:50px;
}
.ws-timeline-title-div{
	height:50px;
	padding-left:8px;
	overflow:hidden;
}
.ws-timeline-title-div .ws-timeline-title{
	color:#000;
	line-height:50px;
	word-break:break-all;
	display:inline-block;
	vertical-align:middle;
	display:-webkit-box;
	-webkit-box-orient:vertical;
	-webkit-line-clamp:1;
}
.ws-timeline-meta{
	margin-top:15px;
}
.ws-timeline-meta .ws-timeline-time{
	display:inline-block;
	float:left;
}
.ws-timeline-meta .ws-comment-btn{
	float:right;
	position:relative;
	width:20px;
	height:16px;
	background-color:#8694B1;
}
.ws-timeline-meta .ws-comment-circle{
	width:4px;
	height:4px;
	border-radius:4px;
	background-color:#FFF;
	float:left;
	margin-top:6px;
	margin-left:4px;
}
.ws-timeline-meta .ws-comment-triangle-div{
	width:6px;
	height:100%;
	position:absolute;
	left:-6px;
	overflow:hidden;
}
.ws-timeline-meta .ws-comment-triangle{
	width:10px;
	height:10px;
	position:absolute;
	top:3px;
	left:4px;
	background-color:#8694B1;
	transform:rotate(45deg);
	-ms-transform:rotate(45deg); 	/* IE 9 */
	-moz-transform:rotate(45deg); 	/* Firefox */
	-webkit-transform:rotate(45deg); /* Safari 和 Chrome */
	-o-transform:rotate(45deg); 
}
.vertical-middle{
	height:100%;
	vertical-align:middle;
	display:inline-block;
}
.clearfix:after{
	content:" ";
	display:block;
	clear:both;
	height:0;
}

.ws-chat{
	padding:10px;
	background-color:#EBEBEB;
}
.ws-chat div{
	float:left;
}
.ws-chat > a{
	display:inline-block;
}
.ws-chat .ws-chat-main{
	background-color:#FFF;
	padding:8px;
	border:1px solid #CECECE;
	border-radius:5px;
}
.ws-chat-main .ws-chat-title{
	width:250px;
	max-height:40px;
	line-height:20px;
	overflow:hidden;
	display:-webkit-box;
	-webkit-box-orient:vertical;
	-webkit-line-clamp:2;
	word-break:break-all;
	margin-top:0;
	font-size:16px;
	color:#353535;
}
.ws-chat-link:hover .ws-chat-triangle-div .ws-chat-triangle-shade,
.ws-chat-link:hover .ws-chat-triangle-div .ws-chat-triangle,
.ws-chat-link:hover .ws-chat-main{
	cursor:pointer;
	background-color:#F7F7F7;
}
.ws-chat-main .ws-chat-desc-div{
	margin-top:5px;
}
.ws-chat-desc-div .ws-chat-desc{
	width:200px;
	max-height:48px;
	line-height:16px;
	overflow:hidden;
	display:-webkit-box;
	-webkit-box-orient:vertical;
	-webkit-line-clamp:3;
	font-size:12px;
	color:#999999;
	word-break:break-all;
	padding-right:10px;
	float:left;
}
.ws-chat .ws-chat-triangle-div{
	width:10px;
	height:100%;
	position:relative;
	top:20px;
	left:1px;
	overflow:hidden;
}
.ws-chat .ws-chat-triangle-div .ws-chat-triangle{
	width:10px;
	height:10px;
	position:relative;
	top:0;
	left:5px;
	background-color:#FFF;
	border:1px solid #CECECE;
	transform:rotate(45deg);
	-ms-transform:rotate(45deg); 	/* IE 9 */
	-moz-transform:rotate(45deg); 	/* Firefox */
	-webkit-transform:rotate(45deg); /* Safari 和 Chrome */
	-o-transform:rotate(45deg); 
}
.ws-chat .ws-chat-triangle-div .ws-chat-triangle-shade{
	width:1px;
	height:10px;
	background-color:#FFF;
	position:absolute;
	top:0;
	right:0;
}
</style>
<script>
	jQuery('#ws_upload_btn').click(function() {
		var send_attachment_bkp = wp.media.editor.send.attachment;
		wp.media.editor.send.attachment = function(props, attachment) {
			jQuery('.ws-link-img').attr('src', '<?php echo $meta_url;?>' + encodeURIComponent(attachment.url));
			jQuery('#ws-url').val(attachment.url);
			//jQuery('.custom_media_id').html(attachment.id);
			wp.media.editor.send.attachment = send_attachment_bkp;
		}
		wp.media.editor.open();
		return false;
	});
	jQuery('#ws-url').bind("input propertychange", function() {
        if('' !== jQuery('#ws-url').val()){
			jQuery('.ws-link-img').attr('src', '<?php echo $meta_url ?>' + encodeURIComponent(jQuery('#ws-url').val()));
		}else{
			jQuery('.ws-link-img').attr('src', '<?php echo $pngdata ?>');
		}
    });
</script>
<?php
}

//数据保存
add_action( 'save_post', 'wxcs_save_data' );

function wxcs_save_data($post_id){
	if( ! isset($_POST['ws_meta_box_nonce']) ){
		return $post_id;
	}
	
    //验证
    if( ! wp_verify_nonce($_POST['ws_meta_box_nonce'], basename(__FILE__)) ){
        return $post_id;
    }

    //自动保存检查
    if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ){
        return $post_id;
    }

    //检查权限
    if( 'page' == $_POST['post_type'] ){
        if (!current_user_can('edit_page', $post_id) ){
            return $post_id;
        }
    }
	elseif( ! current_user_can('edit_post', $post_id) ){
        return $post_id;
    }

	$old = get_post_meta( $post_id, 'ws_url', true );
	$new = $_POST['ws-url'];
	if( $new && $new != $old ){
		update_post_meta( $post_id, 'ws_url', $new );
	}
}

//处理图片请求
add_action( 'wp_ajax_nopriv_resizeimg', 'wxcs_resize_img' );
add_action( 'wp_ajax_resizeimg', 'wxcs_resize_img' );

function wxcs_resize_img(){
	if( isset($_GET['s']) ){
		$src = urldecode( $_GET['s'] );
	}
	else{
		exit;
	}
	$arr = getimagesize($src);
	$width = 300;
	$height = 300;
	
	ob_get_clean();
	ob_clean();
	
	header("Content-type: image/png");
	$ext = pathinfo( $src, PATHINFO_EXTENSION );
	switch( strtolower($ext) ){
		case 'jpg':
		case 'jpeg':
			$src = imagecreatefromjpeg($src);
			break;
		case 'gif':
			$src = imagecreatefromgif($src);
			break;
		case 'png':
			$src = imagecreatefrompng($src);
			break;
		case 'wbmp':
			$src = imagecreatefromwbmp($src);
			break;
		default:
			exit;
	}
	//$src = imagecreatefromjpeg($src);
	$image = imagecreatetruecolor($width, $height);
	$bg = imagecolorallocate($image, 255, 255, 255);
	imagefill($image, 0, 0, $bg);
	imagecopyresampled($image, $src, 0, 0, 0, 0,$width,$height,$arr[0], $arr[1]);
	imagepng($image);
	imagedestroy($image);
	
	exit;
}

//前端输出图片
add_action( 'wp_head', 'wxcs_show', 1 );

function wxcs_show(){
	if( is_singular() ):
		$ws_settings = get_option('ws_settings');
		if( '' !== $ws_settings['ws_display_types'] && array_key_exists( get_post_type(), $ws_settings['ws_display_types'] ) ):
			$meta = get_post_meta( get_the_ID(), 'ws_url', true );
			if( $meta ):
				$ws_url = admin_url( 'admin-ajax.php' ) . '?action=resizeimg&s=' . urlencode($meta);
?>
				<div class="ws-img" style="display:none;">
            		<img src="<?php echo $ws_url ?>">
            	</div>
<?php
			endif;
		endif;
	endif;
}

//本地化
add_action( 'init', 'wxcs_load_textdomain' );

function wxcs_load_textdomain(){
  load_plugin_textdomain( 'wxshare', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

//激活插件
register_activation_hook( __FILE__,'wxcs_activation' );

//停用插件
register_deactivation_hook( __FILE__,'wxcs_deactivation' );

//删除插件
register_uninstall_hook( __FILE__,'wxcs_uninstall' );

function wxcs_activation(){
	add_option( 'ws_settings', array('ws_display_types' => array('post' => 'on', 'page' => 'on', 'attachment' => 'on')) );
}

function wxcs_deactivation(){
	$ws_settings = get_option('ws_settings');
	if(isset($ws_settings['ws_del_data'])){
		global $wpdb;
		$wpdb->query( "delete from $wpdb->postmeta where meta_key = 'ws_url'" );
		delete_option('wxcs_settings');
	}
}

function wxcs_uninstall(){
	
}
?>