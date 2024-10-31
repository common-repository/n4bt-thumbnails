<?php
/*
Plugin Name: DFOXT Thumbnails
Plugin URI:  https://nnnn.blog/dfoxt-thumbnails.html
Description: 拓展 WordPress 分类标签及文章 图像功能
Version:     1.1
Author:      hoythan
Author URI:  https://nnnn.blog/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

DFOXT Thumbnails is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
DFOXT Thumbnails is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with DFOXT Thumbnails. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

define('DFOXT_PLUGIN_URL', plugins_url('', __FILE__));
define('DFOXT_PLUGIN_DIR', plugin_dir_path(__FILE__));
function dfoxt_load_assets(){
	wp_enqueue_style("dfoxt_thumbnails",DFOXT_PLUGIN_URL.'/css/dfoxt.thumbnails.min.css',array());
	wp_enqueue_script("dfoxt_thumbnails",DFOXT_PLUGIN_URL.'/js/dfoxt.thumbnails.min.js',array('jquery'),'',true);
	wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'dfoxt_load_assets');
/*
	给{taxonomy} 添加新的特色图像
*/
class DFOXT_ThumbnailsTaxonomy {
	public $options = array();
	public $defaults = array(
		'name' => '',
		'labelname'	=> '特色图像',
		'mediaform' => array(
			'title'	=> '选择或上传图像',
			'botton'=> '确定选择'
		),
		'taxonomy'	=> 'category',
		'number'	=> 1,
		'multiple'	=> false
	);

	function __construct($argument = array()) {
		$this->options = wp_parse_args($argument, $this->defaults);
		$this->options['multiple'] = $this->options['number'] == 1 || $this->options['number'] == '' ? false : true;
		if($this->options['name'] != ''){
			$this->init();
		}
	}

	public function init() {
		if($this->options['taxonomy'] != ''){
			$this->hook($this->options['taxonomy']);
		}else{
			foreach (get_taxonomies() as $taxonomy) {
				$this->hook($taxonomy);
			}
		}
	}

	private function hook($taxonomy){
		add_action("{$taxonomy}_edit_form_fields",array($this,'add_boxed'),10,1);
		add_action("{$taxonomy}_add_form_fields",array($this,'add_boxed'),10,1);
		add_action("created_{$taxonomy}",array($this,'save'),10,2);
		add_action("edited_{$taxonomy}",array($this,'save'),10,2);
	}

	public function add_boxed($taxonomy){
		ob_start();
		if(!$this->options['multiple']){
			$value = '';$attachment = '';
			if(is_object($taxonomy)){
				$value = dfoxt_get_thumbnails($taxonomy->term_id,$this->options['name']);
				$attachment = wp_get_attachment_metadata($value);
				$attachment = array_merge((array)$attachment,array('url' => wp_get_attachment_url($value)));
			}
		?>
		<div id="dfoxt-thumbnails" class="dfoxt_maxw" data-options='<?php echo json_encode($this->options); ?>' <?php if($attachment != ''){ echo "data-attachment='".json_encode($attachment)."'";} ?>>
        	<h2>轻击这里,上传或选择一张图片</h2>
        	<div class="dfoxt-image dfoxt-mask dfoxt-upload">
        		<div class="dfoxt-close"><span class="dashicons dashicons-trash"></span></div>
        	</div>
        	<input type="hidden" name="<?php echo $this->options['name']; ?>" value="<?php echo esc_attr($value);?>">
        </div>
		<?php }else{
			$value = '';$attachment = [];
			if(is_object($taxonomy)){
				$value = dfoxt_get_thumbnails($taxonomy->term_id,$this->options['name']);
				foreach ($value as $id) {
					$attachment[] = array_merge(wp_get_attachment_metadata($id),array('url' => wp_get_attachment_url($id),'id' => $id));
				}
			}
		?>
		<div id="dfoxt-thumbnails" data-options='<?php echo json_encode($this->options); ?>' <?php if($attachment != ''){ echo "data-attachment='".json_encode($attachment)."'";} ?>>
        	<h2>轻击这里,上传一些图片</h2>
        	<div class="dfoxt-images dfoxt-mask dfoxt-upload dfoxt-gridly">
        		
        	</div>
        	<input type="hidden" name="<?php echo $this->options['name']; ?>" value="<?php echo esc_attr($this->arrayTostring($value));?>">
        </div>
		<?php }
        $output = ob_get_contents();
        ob_end_clean();
		echo is_object($taxonomy) ? "
		<tr class='form-field term-description-wrap'>
			<th scope='row'>
				<label for='description'>{$this->options['labelname']}</label>
			</th>
			<td>{$output}</td>
		</tr>
		":"
		<div class='form-field'>
			<label for='tag-description'>{$this->options['labelname']}</label>
			{$output}
		</div>
		";
	}
	public function save($term_id,$tt_id){
		if(isset($_POST[$this->options['name']]) && isset($this->options['multiple'])){
			$options 		= sanitize_text_field(trim($_POST[$this->options['name']]));
			$option_name 	= sanitize_text_field($this->options['name']);
			$key			= sanitize_key('dfoxt_'.$term_id.'_'.$option_name);
			if(!$this->options['multiple']){
				update_option($key,$options);
			}else{
				$value = explode(',',$options);
				update_option($key,$value);
			}
		}else{
			return false;
		}
	}
	/*
		数组转为字符串拼接
	*/
	public function arrayTostring($array){
		$string = '';
		for ($i=0; $i < count($array); $i++) {
			if($i == 0){
				$string = $array[$i];
			}else{
				$string .= ','.$array[$i];
			}
		}
		return $string;
	}

	// public function activehook(){

	// }
}

/*
	增删改查接口
	* 提交 term_id 查询所有该 term 下的 特色图像
	* 提交 name 查询所有设置了该 name 的 term_id
	* 提交 term_id,name 查询该 term 下 name 的特色图像
*/
function dfoxt_get_thumbnails($key,$name = ''){
	if($name == ''){
		if(gettype($key) == 'integer'){
			
		}else{

		}
	}else{
		$value = get_option('dfoxt_'.$key.'_'.$name);
	}

	return $value;

}
/*
	返回的内容必然都是 数组
*/
function dfoxt_get_thumbnail_urls($key,$name = ''){
	$value = dfoxt_get_thumbnails($key,$name);
	$urls = array();
	if(is_array($value)){
		foreach ($value as $id) {
			$urls[] = wp_get_attachment_url($id);
		}
	}else{
		$urls = wp_get_attachment_url($value);
	}
	return $urls;
}
?>