<?php
/**
 * @version		$Id: class.posts-comments-widget.php 1530 2012-01-13 11:13:58Z dmitriy.f $
 * @author		xagero
 */
class PluginPostsCommentsWidget extends PluginPostsCommentsWidget_Abstract
{
	// }}}
	// {{{ init

	/**
	 * (non-PHPdoc)
	 * @see PluginAbstract::init()
	 */
	
	public function init()
	{
		parent::init();
		$this->viewIndexAll = array(
				'overview' => array(
						'title-tab' => 'Overview',
						'title' => $this->_config['meta']['Name'] . '&nbsp;Overview'
				),
				'settings' => array(
						'title-tab' => 'Settings',
						'title' => $this->_config['meta']['Name'] . '&nbsp;Settings'
				)
		);	

		add_action( 'widgets_init', create_function( '', 'register_widget("PluginPostsWidget");' ) );
		add_action( 'widgets_init', create_function( '', 'register_widget("PluginCommentWidget");' ) );
		add_shortcode( 'thethe-posts', array($this, 'thethe_posts_shortcode') );
		add_shortcode( 'thethe-comments', array($this, 'thethe_comments_shortcode') );
		
		wp_deregister_style('thethe-comments');
		wp_register_style('thethe-comments' ,$this->_config['meta']['wp_plugin_dir_url'] . 'style/css/thethe-comments.css');
		wp_enqueue_style('thethe-comments');
		
		wp_deregister_style('thethe-posts');
		wp_enqueue_style('thethe-posts', $this->_config['meta']['wp_plugin_dir_url'] . 'style/css/thethe-posts.css');
		wp_enqueue_style('thethe-posts');
	} // end func init
	
	// {{{ _settingsView
	
	/**
	 * Function _settingsView
	 */
	public function _settingsView()
	{
		if (isset($_POST['data']) && isset($_POST['submit'])) {
			$dataValid = $this->_settingsValidate($_POST['data']);
			if ($dataValid) {
				update_option('_ttf-' . $this->_config['shortname'], $dataValid);
			}
		} elseif (isset($_POST['reset'])) {
			update_option('_ttf-' . $this->_config['shortname'],$this->_config['options']['default']);
		}
		parent::_defaultView();
	} // end func _settingsView
	
	// }}}
	// {{{ _settingsValidate
	
	/**
	 * Function _settingsValidate
	 * @param array $data
	 */
	public function _settingsValidate($data)
	{
		if (!is_array($data)) return false;
		foreach (($dataValid = array(
				'enable-custom-css' => null,
				'custom-css' =>null
		)
		) as $k=>$v ) {
			$dataValid[$k] = trim($data[$k]);
		}
		return $dataValid;
	} // end func _settingsValidate
				
	public function _hook_wp_head() {
		$config = $this->config();
		if ($config['enable-custom-css']){
			echo '<style type="text/css">';
				echo stripslashes($config['custom-css']);
			echo '</style>';
		}
	}
	
	public function _hook_admin_footer() {
		?>
	<script type="text/javascript">
	jQuery(document).ready(function(){
		 jQuery('.datepicker').live("mouseover", function(){
		  jQuery(this).datepicker({
		   dateFormat : 'yy-mm-dd'
		  });
		 });
		});
	</script>
	<?php
	}
	
	public function thethe_posts_shortcode($atts) {	
		$config = $this->config('posts-widget');
		$instance = shortcode_atts( $config , $atts );
		$args= array();
		$forms = new PluginPostsWidget();
		return $forms->posts_widget( $args, $instance );
	}
	
	public function thethe_comments_shortcode($atts) {
		$config = $this->config('comments-widget');
		$instance = shortcode_atts( $config , $atts );
		$args= array();
		$forms = new PluginCommentWidget();
		return $forms->comments_widget( $args, $instance );
	}
	
	

} // end class PluginPostsWidget

