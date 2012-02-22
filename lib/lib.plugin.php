<?php
class PluginPostsCommentsWidget_Abstract
{
	protected $_config;
	public $viewIndexAll;
	public $viewIndex;
	public $pluginHook = null;
	
	public function __construct()
	{
		$this->_config = array();
	} // end func __construct
	
	public function configure($config)
	{
		$this->_config = $config;
	} // end func confugure
	
	// }}}
	// {{{ init
	
	/**
	 * init
	 */
	public function init()
	{
		$hook = $this->_config['plugin-hook'];
		register_activation_hook($hook, array($this,'_hook_activate'));
		register_deactivation_hook($hook, array($this,'_hook_deactivate'));
		
		add_filter( 'plugin_row_meta', array($this,'_hook_plugin_action_links'),10,2);
		add_filter( 'query_vars', array($this,'_hook_query_vars'));
		add_filter( 'rewrite_rules_array', array($this,'_hook_rewrite_rules_array'));
		add_filter( 'init', array($this,'_hook_init'));
		add_filter( 'widgets_init', array($this,'_hook_widgets_init'));
		add_filter( 'save_post', array($this,'_hook_save_post'));
		add_filter( 'contextual_help', array($this,'_hook_contextual_help'),10,3);
		add_filter( 'wp_enqueue_scripts', array($this,'_hook_wp_enqueue_scripts'));
		add_filter( 'wp_print_styles', array($this,'_hook_wp_print_style'));
		add_filter( 'wp_head', array($this, '_hook_wp_head'));
		add_filter( 'wp_footer', array($this, '_hook_wp_footer'));
		add_filter( 'wp_loaded', array($this, '_hook_wp_loaded'));
		if (is_admin()) {
			add_action('admin_init', array($this,'_hook_admin_init'), 1);  
			add_action('admin_footer', array($this,'_hook_admin_footer'));
			add_filter('add_meta_boxes', array($this,'_hook_add_meta_boxes'));
			add_filter('admin_menu', array($this,'_hook_admin_menu'));
		}
	} // end func init

	// }}}
	// {{{ manage_options

	/**
	 * display
	 */
	public function display()
	{
		if (!is_admin()) return false;
		if (!current_user_can('manage_options')) {
			wp_die('You do not have sufficient permissions to access this page.');
		}
		$view = (isset($_REQUEST['view']) ? $_REQUEST['view'] : 'default');
		$view = str_replace(' ','',ucwords(str_replace('-',' ',$view)));
		$methodName = '_'.$view.'View';
		if (method_exists($this,$methodName)) {
			return call_user_method($methodName,$this);
		} else {
			return $this->_defaultView();
		}
	} // end func display
	
	// }}}
	// {{{ displayAboutClub

	/**
	 * Func displayAboutClub
	 */
	public function displayAboutClub()
	{
		include_once ($this->_config['meta']['wp_plugin_dir'] . '/inc/view-about-us.php');
	} // end func displayAboutClub
	
	// }}}
	// {{{ config
	
	/**
	 * Config
	 * @return array|mixed
	 */
	public function config($ns = 'default')
	{	
		if (($ns != 'default') && $ns) {
			return stripslashes_deep(get_option(
				'_ttf-' . $this->_config['shortname'] . '-' . $ns,
				$this->_config['options'][$ns]
			));
		} else {
			return stripslashes_deep(get_option(
				'_ttf-' . $this->_config['shortname'],
				$this->_config['options']['default']
			));
		}
	} // end func config 
	
	// }}}
	// {{{ _displayRSS

	/**
	 * _displayRSS
	 * 
	 * @param string $url
	 * @param int $num_items
	 */
	protected function _displayRSS( $url, $num_items = -1 )
	{
		$rss = new SimplePie();
		$rss->strip_htmltags(array_diff($rss->strip_htmltags,array('style')));
		$rss->strip_attributes(array_diff($rss->strip_attributes,array('style','class','id')));
		$rss->set_feed_url($url);
		$rss->set_cache_class('WP_Feed_Cache');
		$rss->set_file_class('WP_SimplePie_File');
		$rss->set_cache_duration(apply_filters('wp_feed_cache_transient_lifetime', 43200, $url));
		do_action_ref_array( 'wp_feed_options', array( &$rss, $url ) );
		$rss->init();
		$rss->handle_content_type();

		if ( !$rss->error() ) {
			$maxitems = $rss->get_item_quantity(25);
			$rss_items = $rss->get_items(0, $maxitems);
			echo '<ul>';
			if ( $num_items !== -1 ) {
				$rss_items = array_slice( $rss_items, 0, $num_items );
			}
			if ($rss_items){
				foreach ( (array) $rss_items as $item ) {
					printf(
						'<li><div class="date">%4$s</div><div class="thethefly-news-item">%2$s</div></li>',
						esc_url( $item->get_permalink() ),
						$item->get_description(),
						esc_html( $item->get_title() ),
						$item->get_date('D, d M Y')
					);
				}
			} else {				
				_e( '<li>Unfortunately the news channel is temporarily closed</li>','thethefly');
			}
			echo '</ul>';
		} else {
			_e( 'An error has occurred, which probably means the feed is down. Try again later.','thethefly' );
		}
	} // end func _displayRSS
	
	// }}}
	// {{{ _hook_activate

	/**
	 * _hook_activate
	 */
	public function _hook_activate()
	{
		if (isset($this->_config['options'])) {
			if (is_array($data = $this->_config['options'])) {
				$suffix = '_ttf-' . $this->_config['shortname'];
				foreach ($data as $key => $config) {
					if ($key && ($key != 'default')) {
						$name = $suffix . '-' . $key;
					} else {
						$name = $suffix;
					}
					update_option(mb_strtolower($name),$this->_config['options'][$key]);
				}
			}
		}
	} // end func _hook_activate
	
	// }}}
	// {{{ _hook_admin_menu
	
	/**
	 * _hook_admin_menu
	 */
	public function _hook_admin_menu()
	{
		global $menu;

		$flag['makebox'] = true;
		if (is_array($menu)) foreach ($menu as $e) {
			if (isset($e[0]) && (in_array($e[0], array('TheThe Fly','TheTheFly')))) {
				$flag['makebox'] = false;
				break;
			}
		}
		
		if ($flag['makebox']) {
			$icon_url = $title = $this->_config['meta']['wp_plugin_dir_url'] . 'style/admin/images/favicon.ico';
			add_menu_page('TheThe Fly', 'TheThe Fly', 'edit_theme_options', 'thethefly', 'TheThe_makeAdminPage', $icon_url, 63);
			$hook = add_submenu_page('thethefly', 'TheThe Fly: About the Club', 'About the Club', 'manage_options', 'thethefly', 'TheThe_makeAdminPage'); 
			add_filter( 'admin_print_styles-' . $hook, array($this,'_hook_admin_print_styles')); 
		}
		
		$title = $this->_config['meta']['Name'];
		$title = trim(str_replace('TheThe', null, $title));
		$shortname = $this->_config['shortname'];
		$this->pluginHook = add_submenu_page('thethefly', $title,$title,'manage_options',$shortname,array($this,'display'));
		add_filter( 'admin_print_styles-' . $this->pluginHook , array($this,'_hook_admin_print_styles')); 
	} // end func _hook_admin_menu
	
	// }}}
	// {{{ _hook_admin_print_styles
	
	/**
	 * _hook_admin_print_styles
	 */
	public function _hook_admin_print_styles()
	{
		wp_admin_css( 'nav-menu' );
		$interface_css = $this->_config['meta']['wp_plugin_dir_url'] . 'style/admin/interface.css';
		wp_enqueue_style( 'thethefly-plugin-panel-interface', $interface_css );
		wp_enqueue_script( 'postbox' );
		wp_enqueue_script( 'post' );
	} // end func _hook_admin_print_styles
	
	// }}}
	
	public function _hook_admin_init() 
	{
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-datepicker', $this->_config['meta']['wp_plugin_dir_url'] . 'style/admin/js/jquery.ui.datepicker.min.js', array('jquery', 'jquery-ui-core') );
		wp_enqueue_style('jquery.ui.theme', $this->_config['meta']['wp_plugin_dir_url'] . 'style/admin/smoothness/jquery-ui-1.8.17.custom.css');
		$interface_css = $this->_config['meta']['wp_plugin_dir_url'] . 'style/admin/interface.css';
		wp_enqueue_style( 'thethefly-plugin-panel-interface', $interface_css );		
	}
	
	/**
	 * _hook_plugin_action_links
	 * @param array $links
	 * @param string $file
	 */
	public function _hook_plugin_action_links($links, $file)
	{
		if ($file == $this->_config['plugin-hook']) {
			$links[] = '<a href="admin.php?page='.$this->_config['shortname'].'&view=settings">' . __('Settings') . '</a>';
			$links[] = '<a href="http://thethefly.com/support/forum/">' . __('Support') . '</a>';
			$links[] = '<a href="http://thethefly.com/themes/">' . __('Themes') . '</a>';
			$links[] = '<a href="http://thethefly.com/wordpress-tips-tricks-hacks-newsletter/">' . __('Tips & Tricks') . '</a>';			
			$links[] = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=U2DR7CUBZLPFG">' . __('Donate') . '</a>';
		}
		return $links;
	} // end func _hook_plugin_action_links
	
	// }}}
	// {{{ _hook_contextual_help
	
	/**
	 * _hook_contextual_help
	 * @param mixed $contextual_help
	 * @param mixed $screen_id
	 * @param mixed $screen
	 */
	public function _hook_contextual_help($contextual_help, $screen_id, $screen)
	{
		if ($this->pluginHook == $screen_id) {
			$file = $this->_config['meta']['wp_plugin_dir'] . '/inc/inc.contextual-help.php';
			if (file_exists($file)) {
				ob_start();
				include $file;
				$contextual_help = ob_get_clean();
			}
		}
		return $contextual_help;
	} // end func _hook_contextual_help
	
	// }}}
	// {{{ _defaultView
	
	/**
	 * _defaultView
	 */
	public function _defaultView()
	{
		$viewIndex = $this->getCurrentViewIndex();
		$dir = $this->_config['meta']['wp_plugin_dir'];
		$viewFileName = $dir . '/inc/view.' . $viewIndex . '.php';
		if (isset($this->viewIndexAll[$viewIndex]['file'])) {
			$file = $this->viewIndexAll[$viewIndex]['file'];
			if (file_exists($dir . '/inc/' . $file)) {
				$viewFileName = $dir . '/inc/' . $file;
			}
		}
		include $dir . '/inc/inc.header.php';
		include $viewFileName;
		include $dir . '/inc/inc.footer.php';
	} // end func _defaultView
	
	// }}}
	// {{{ getCurrentViewIndex
	
	/**
	 * Function getCurrentViewIndex
	 */
	public function getCurrentViewIndex()
	{
		$this->viewIndex = (isset($_REQUEST['view']) && isset($this->viewIndexAll[$_REQUEST['view']]))
			? $_REQUEST['view'] : 'overview';
		return $this->viewIndex;
	} // end func getCurrentViewIndex
	
	// }}}
	// {{{ getTabURL
	
	/**
	 * Function getTabURL
	 * @param string $viewIndex
	 * @return string
	 */
	public function getTabURL($viewIndex = null)
	{
		if (!$viewIndex) $viewIndex = 'overview';
		return get_admin_url() . 'admin.php?page=' . $this->_config['shortname'] . '&amp;view=' . $viewIndex;
	} // end func getTabURL
	
	// }}}
	// {{{ printTabsURL
	
	/**
	 * Function printTabsURL
	 * @param string $viewIndex
	 */
	public function printTabsURL($viewIndex = null)
	{
		print $this->getTabURL($viewIndex);
	} // end func printTabsURL
	
	// }}}
	
	public function _hook_wp_head() {}
	public function _hook_wp_footer() {}
	public function _hook_init() {}
	public function _hook_wp_enqueue_scripts($post) {}
	public function _hook_wp_print_style() {}
	public function _hook_wp_loaded() {}
	public function _hook_widgets_init() {}
	public function _hook_query_vars($args) { return $args; }
	public function _hook_rewrite_rules_array($rules) { return $rules; }
	public function _hook_save_post($post_id) {}
	public function _hook_add_meta_boxes() {}
	public function _hook_deactivate() {}
	public function _hook_admin_footer() {}
	
	
} // end class PluginPostsWidget_Abstract