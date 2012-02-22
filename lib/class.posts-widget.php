<?php
/**
 * @version		$Id: class.posts-widget.php 1649 2012-02-08 11:46:04Z dmitriy.f $
 * @author		xagero
 */

class PluginPostsWidget extends WP_Widget
{
	// }}}

	public function PluginPostsWidget(){
		parent::WP_Widget( /* Base ID */'thethe_post', /* Name */'TheThe Posts', array( 'description' => 'TheThe Posts' ) );
	}

	public function filter_where( $where = '') {
		global $disp_from, $disp_to;
		$where .= " AND post_date >= '".$disp_from."' AND post_date < '". $disp_to."'";
		return $where;
	}
	
	public function posts_widget( $args, $instance )
	{
		global $post;
		$post_old = $post;
		extract( $args );
		if( !$instance["title"] ) {
			$category_info = get_category($instance["cat"]);
			$instance["title"] = $category_info->name;
		}
		if (trim($instance["display_type"])!='')
		{
			$post_type=explode(",", $instance["display_type"]);
		}
		else
		{
			if( $instance["display_type_post"] ) $post_type[]='post';
			if( $instance["display_type_page"] ) $post_type[]='page';
			if( $instance["display_type_revision"] ) $post_type[]='revision';
			if( $instance["display_type_attachment"] ) $post_type[]='attachment';
		}
	
		if (trim($instance["display_status"])!='')
		{
			$post_status=explode(",", $instance["display_status"]);
		}
		else
		{
			if( $instance["display_type_published"] ) $post_status[]='publish';
			if( $instance["display_type_pending"] ) $post_status[]='pending';
			if( $instance["display_type_draft"] ) $post_status[]='draft';
			if( $instance["display_type_auto_draft"] ) $post_status[]='auto-draft';
			if( $instance["display_type_future"] ) $post_status[]='future';
			if( $instance["display_type_private"] ) $post_status[]='private';
			if( $instance["display_type_inherit"] ) $post_status[]='inherit';
			if( $instance["display_type_trash"] ) $post_status[]='trash';
		}
	
		if (trim($instance["display_format"])!='')
		{
			$post_formats=explode(",", $instance["display_format"]);
		}
		else
		{
			if( $instance["display_format_link"] ) $post_formats[]='post-format-link';
			if( $instance["display_format_image"] ) $post_formats[]='post-format-image';
			if( $instance["display_format_gallery"] ) $post_formats[]='post-format-gallery';
			if( $instance["display_format_video"] ) $post_formats[]='post-format-video';
			if( $instance["display_format_audio"] ) $post_formats[]='post-format-audio';
			if( $instance["display_format_aside"] ) $post_formats[]='post-format-aside';
			if( $instance["display_format_status"] ) $post_formats[]='post-format-status';
			if( $instance["display_format_quote"] ) $post_formats[]='post-format-quote';
		}
		$valid_sort_orders = array('ID', 'author', 'date', 'title', 'modified', 'parent', 'comment_count', 'rand', 'menu_order', 'meta_value', 'meta_value_num');
		if ($instance['enable_sort'])
		{
			if ( in_array($instance['orderby_post'], $valid_sort_orders) ) {
				$sort_by = $instance['orderby_post'];
				$sort_order = $instance['order_post'];
			} else {
				$sort_by = 'date';
				$sort_order = 'DESC';
			}
		}
		else
		{
			switch ($instance['post_loop_type']) {
				case 'recent':
					$sort_by = 'date';
					$sort_order = 'DESC';
					break;
				case 'random':
					$sort_by = 'rand';
					$sort_order = 'asc';
					break;
				case 'commented':
					$sort_by = 'comment_count';
					$sort_order = 'DESC';
					break;
			}
		}
		$args =	array(
				'posts_per_page' => $instance["quantity"],
				'ignore_sticky_posts' => 1,
				'post_type' => $post_type,
				'post_status' => $post_status,
				'orderby' => $sort_by,
				'order' => $sort_order,
		);
	
		if(count($post_formats)>0)
		{
			$args['tax_query']=
			array(
					array(
							'taxonomy' => 'post_format',
							'field'    => 'slug',
							'terms'    => $post_formats,
							'operator' => 'IN'
					)
			);
		}
	
		$excluded_posts = explode(",", $instance['excluded_posts']);
		if ( $instance['excluded_posts']) {
			if( $instance['ex_posts']=="include")
				$args['post__in']=$excluded_posts;
			elseif( $instance['ex_posts']=="exclude")
			$args['post__not_in']=$excluded_posts;
		}
		if ( $instance['excluded_author']) $args['author']=$instance['excluded_author'];
	
		if ( $instance['excluded_cat']) $args['cat']=$instance['excluded_cat'];
	
		if ( $instance['tag_str']) $args['tag']=$instance['tag_str'];
	
		if ( $instance['custom-key']) $args['meta_key']=$instance['custom-key'];
	
		if ( $instance['custom-value']) $args['meta_value']=$instance['custom-value'];
	
		if ( $instance['custom-op'] && $instance['custom-key'] && $instance['custom-value'] )
			$args['meta_compare']=$instance['custom-op'];
	
	
		if ($instance['display_published_from']!='' && $instance['display_published_to']!='') {
			global $disp_from, $disp_to;
			$disp_from=$instance['display_published_from'];
			$disp_to=$instance['display_published_to'];
			add_filter( 'posts_where', array($this,'filter_where'));
			$cat_posts = new WP_Query( $args );
			remove_filter( 'posts_where', array($this,'filter_where'));
		}
		else
			$cat_posts = new WP_Query( $args );
	
		// Excerpt length filter
		$length=$instance["excerpt_length"];
		$new_excerpt_length = create_function('$length', "return " . $length . ";");
		if ( $instance["excerpt_length"] > 0 )
			add_filter('excerpt_length', $new_excerpt_length, 99999);
		
		$html='';
		$html.= $before_widget;
	
		// Widget title
		$html.= $before_title;
		if( $instance["title_link"] )
			$html.= '<a href="' . get_category_link($instance["cat"]) . '">' . $instance["title"] . '</a>';
		else
			$html.= $instance["title"];
		$html.= $after_title;
		// Post list
		$html.= '<ul class="thethe-posts">';
		if ($cat_posts->have_posts()) {
			while ( $cat_posts->have_posts() )
			{
				$cat_posts->the_post();
				if ( $instance['use_template']=="default_template" ) {
	
					$html.= '<li class="thethe-post">';
					if ( function_exists('the_post_thumbnail') && current_theme_supports("post-thumbnails") &&
							$instance["display_featured_image"] &&	has_post_thumbnail()) {
						$html.= '<div class="post-thumbnail">';
						$html.= $this->thethe_image($instance['size_f_image']);
						$html.= '</div>';
					}
					$html.= '<div class="post-body">';
					if ( $instance['display_title'] )
						$html.= $this->thethe_title();
	
					if ( $instance['display_date'] )
						$html.= '<div class="post-date">'. $this->thethe_date() .'</div>';
	
					if ( $instance['display_excerpt'] && $this->thethe_excerpt($instance))
						$html.= '<div class="post-text">'.$this->thethe_excerpt($instance).'</div>';
					$html.= '<div class="clr"></div>';
					if ( $instance['display_categoris'] ) {
						$html.= '<div class="post-category">';
						$html.= 'Category: ';
						$html.= $this->thethe_category().'</div>';
					}
	
					if ( $instance['display_tags'] && trim($this->thethe_tags())!='' ) {
						$html.= '<div class="post-tags">';
						$html.= 'Tags: ';
						$html.= $this->thethe_tags().'</div>';
					}
	
					if ( $instance['display_author'] ) {
						$html.= '<div class="post-author" style="line-height:'.$instance['size_avatar'].'px;">';
						if ( $instance['display_author_avatar'] )
							$html.= '<span class="post-author-avatar">'.$this->thethe_avatar($instance['size_avatar']).'</span>';
						$html.= ' <span class="post-author-name">'.$this->thethe_author().'</span></div>';
					}
	
					if ( $instance['display_comments_number'] )
					{
						$html.= '<div class="comment-num">';
						$html.= $this->thethe_commentcount();
						$html.= '</div>';
					}
					$html.= '</div>';
					$html.= '</li>';
				}
				else
				{
					$html.= $this->output_template($instance);
				}
			}
		}
		else
		{
			$html.=  $instance['none_text'];
		}
	
		$html.= "</ul>\n";
	
		$html.= $after_widget;
	
		remove_filter('excerpt_length', $new_excerpt_length);
	
		$post = $post_old;
		return $html;
	}
	
	public function widget( $args, $instance ) 
	{
		echo $this->posts_widget( $args, $instance );
	}
	
	public function output_template($instance)
	{ 
		//$template="<li>{title}{avatar}{image}{commentcount}{excerpt}{author}{date}{tags}{category}</li>";
		
		$template=$instance['output_template'];
		
		preg_match_all('/{((?:[^{}]|{[^{}]*})*)}/', $template, $matches);
		foreach ($matches[1] as $match) {
			switch ($match) {
				case 'title':
					$replace=$this->thethe_title();
					break;
				case 'avatar':
					$replace=$this->thethe_avatar($instance['size_avatar']);
					break;
				case 'author':
					$replace=$this->thethe_author();
					break;
				case 'image':
					$replace=$this->thethe_image($instance['size_f_image']);
					break;
				case 'commentcount':
					$replace=$this->thethe_commentcount();
					break;
				case 'excerpt':
					$replace=$this->thethe_excerpt();
					break;
				case 'date':
					$replace=$this->thethe_date();
					break;
				case 'tags':
					$replace=$this->thethe_tags();
					break;
				case 'category':
					$replace=$this->thethe_category();
					break;
			}
			$translations['{'.$match.'}']=$replace;
		}
			$tmp = strtr($template, $translations)."\n";
			return $tmp;
	}
	
	/** @see WP_Widget::update */
	public function update( $new_instance, $old_instance ) {
		if ($old_instance)	$instance = $old_instance;
		
		foreach ($new_instance as $k => $v) {
			if ($k!="output_template")
				$instance[$k]= strip_tags($new_instance[$k]);
			else
				$instance[$k]= $new_instance[$k];
		}
		if (trim($instance['excluded_posts'])=="Example: 1,2,4,-3")
		{
			$instance['excluded_posts']='';
		}
		if (trim($instance['excluded_author'])=="Example: 1,2,4,-3") {
			$instance['excluded_author']='';
		}
		if (trim($instance['excluded_cat'])=="Example: 1,2,4,-3") {
			$instance['excluded_cat']='';
		}
		echo $instance['excluded_cat'];
		$instance['display_title']= strip_tags($new_instance['display_title']);
		$instance['display_excerpt']= strip_tags($new_instance['display_excerpt']);
		$instance['display_featured_image']= strip_tags($new_instance['display_featured_image']);
		$instance['display_comments_number']= strip_tags($new_instance['display_comments_number']);
		$instance['display_author']= strip_tags($new_instance['display_author']);
		$instance['display_author_avatar']= strip_tags($new_instance['display_author_avatar']);
		$instance['display_date']= strip_tags($new_instance['display_date']);
		$instance['display_tags']= strip_tags($new_instance['display_tags']);
		$instance['display_categoris']= strip_tags($new_instance['display_categoris']);
		
		$instance['display_type_post']= strip_tags($new_instance['display_type_post']);
		$instance['display_type_page']= strip_tags($new_instance['display_type_page']);
		$instance['display_type_revision']= strip_tags($new_instance['display_type_revision']);
		$instance['display_type_attachment']= strip_tags($new_instance['display_type_attachment']);
		
		$instance['display_type_published']= strip_tags($new_instance['display_type_published']);
		$instance['display_type_pending']= strip_tags($new_instance['display_type_pending']);
		$instance['display_type_draft']= strip_tags($new_instance['display_type_draft']);
		$instance['display_type_auto_draft']= strip_tags($new_instance['display_type_auto_draft']);
		$instance['display_type_future']= strip_tags($new_instance['display_type_future']);
		$instance['display_type_private']= strip_tags($new_instance['display_type_private']);
		$instance['display_type_inherit']= strip_tags($new_instance['display_type_inherit']);
		$instance['display_type_trash']= strip_tags($new_instance['display_type_trash']);
		
		$instance['display_format_image']= strip_tags($new_instance['display_format_image']);
		$instance['display_format_link']= strip_tags($new_instance['display_format_link']);
		$instance['display_format_gallery']= strip_tags($new_instance['display_format_gallery']);
		$instance['display_format_video']= strip_tags($new_instance['display_format_video']);
		$instance['display_format_audio']= strip_tags($new_instance['display_format_audio']);
		$instance['display_format_aside']= strip_tags($new_instance['display_format_aside']);
		$instance['display_format_status']= strip_tags($new_instance['display_format_status']);
		$instance['display_format_quote']= strip_tags($new_instance['display_format_quote']);
		$instance['ex_posts']= strip_tags($new_instance['ex_posts']);
		$instance['enable_sort']= strip_tags($new_instance['enable_sort']);
		return $instance;
	}
	
	/** @see WP_Widget::form */
	public function form( $instance ) 
	{
		global $wpdb, $wp_version, $_wp_theme_features;

		//filter the new instance and replace blanks with defaults
    	$defaults = array(
    		'post_loop_type' => 'recent',
    		'quantity' => '5',
    		'use_template' => 'default_template',
 
    		// What to Display
    		'display_title' => '1',
    		'display_excerpt' => '0',
    		'display_featured_image' => '0',
    		'display_comments_number' => '0',
    		'display_author' => '0',
    		'display_author_avatar' => '0',
    		'display_date' => '0',
    		'display_tags' => '0',
    		'display_categoris' => '0',
    		// How to Display
    		'excerpt_length' => '5',
			'excerpt_length_type' => 'words',
    		'size_f_image' => '50',
    		'size_avatar' => '24',
    		'none_text' => 'No Matches Found',
    		'output_template' =>'
<li class="thethe-post">
	<div class="post-thumbnail">{image}</div>					
	<div class="post-body">{title}
	<div class="post-date">{date}</div>
	<div class="post-text">{excerpt}</div>
	<div class="clr"></div>
	<div class="post-category">
		Category: {category}
	</div>
	<div class="post-tags">
		Tags:{tags}
	</div>
	<div class="post-author">
		<span class="post-author-avatar">{avatar}</span>
		<span class="post-author-name">{author}</span>
	</div>
	<div class="comment-num">
		{commentcount}
	</div>
	</div>				
</li>',
    		//Filter by Types & Formats
    			//Display Posts Types
    		'display_type_post' => '1',
    		'display_type_page' => '0',
    		'display_type_revision' => '0',
    		'display_type_attachment' => '0',
    			//Display Posts Status
    		'display_type_published' => '1',
    		'display_type_pending' => '0',
    		'display_type_draft' => '0',
    		'display_type_auto_draft' => '0',
    		'display_type_future' => '0',
    		'display_type_private' => '0',
    		'display_type_inherit' => '0',
    		'display_type_trash' => '0',
    			//Display Posts Formats
    		'display_format_image' => '0',
    		'display_format_gallery' => '0',
    		'display_format_link' => '0',
    		'display_format_video' => '0',
    		'display_format_audio' => '0',
    		'display_format_aside' => '0',
    		'display_format_status' => '0',
    		'display_format_quote' => '0',
    		//Sort Output
    		'enable_sort' => '0',
    		'order_post' => 'DESC',
    		'orderby_post' => 'date',
    		'ex_posts' => 'exclude'
    	);
		$instance = array_merge($defaults, $instance);
	?>

<div class="wrap">
  <p>
    <label for="<?php echo $this->get_field_id("title"); ?>">
      <?php _e( 'Widget Title' ); ?>: </label>
    <input class="widefat" id="<?php echo $this->get_field_id("title"); ?>" name="<?php echo $this->get_field_name("title"); ?>" type="text" value="<?php echo esc_attr($instance["title"]); ?>" />
  </p>
  <p>
    <label for="<?php echo $this->get_field_id("post_loop_type"); ?>">
      <?php _e( 'Post Loop Type', 'thethe-posts-widget'); ?>: </label>
    <select class="widefat" name="<?php echo $this->get_field_name("post_loop_type"); ?>" id="<?php echo $this->get_field_id("post_loop_type"); ?>" >
      <option <?php if($instance['post_loop_type'] == 'recent') { echo 'selected="selected"'; } ?> value="recent">Most Recent</option>
      <option <?php if($instance['post_loop_type'] == 'random') { echo 'selected="selected"'; } ?> value="random">Random</option>
      <option <?php if($instance['post_loop_type'] == 'commented') { echo 'selected="selected"'; } ?> value="commented">Most Commented</option>
    </select>
  </p>
  <p>
    <label><?php _e('Quantity of Posts to Show'); ?>:</label>
      <input style="text-align: center;" id="<?php echo $this->get_field_id("quantity"); ?>" name="<?php echo $this->get_field_name("quantity"); ?>" type="text" value="<?php echo absint($instance["quantity"]); ?>" size='3' />

  </p>
  <h3>
    <?php _e( 'What and How to Output', 'thethe-posts-widget'); ?>
  </h3>
  <p>
      <input id="<?php echo $this->get_field_id("use_template"); ?>" name="<?php echo $this->get_field_name("use_template"); ?>" type="radio" <?php if ( $instance['use_template']=="default_template" ) echo "checked"; ?> value="default_template" />
      Use Default Template
  </p>
  <p style="margin-left:5px">
    <label>
      <input name="<?php echo $this->get_field_name("display_title"); ?>" id="<?php echo $this->get_field_id("display_title"); ?>" class="str-field"  type="checkbox" <?php if ($instance['display_title']) echo 'checked="checked"';?> >
      <?php _e( 'Title' ); ?>
    </label>
    <br/>
   
    <input name="<?php echo $this->get_field_name("display_excerpt"); ?>" id="<?php echo $this->get_field_id("display_excerpt"); ?>" class="str-field"  type="checkbox" <?php if ($instance['display_excerpt']) echo 'checked="checked"';?> >
    <label><?php _e( 'Excerpt', 'thethe-posts-widget'); ?>    </label>
	<br/>
    <input style="text-align: center;" type="text" id="<?php echo $this->get_field_id("excerpt_length"); ?>" name="<?php echo $this->get_field_name("excerpt_length"); ?>" value="<?php echo $instance["excerpt_length"]; ?>" size="1" />
	<input  id="<?php echo $this->get_field_id("excerpt_length_words"); ?>" name="<?php echo $this->get_field_name("excerpt_length_type"); ?>" type="radio" <?php if ( $instance['excerpt_length_type']=="words" ) echo "checked"; ?> value="words" /><label><?php _e( 'Words', 'thethe-posts-widget'); ?></label>
	<input id="<?php echo $this->get_field_id("excerpt_length_char"); ?>" name="<?php echo $this->get_field_name("excerpt_length_type"); ?>" type="radio" <?php if ( $instance['excerpt_length_type']=="chars" ) echo "checked"; ?> value="chars" /><label><?php _e( 'Characters', 'thethe-posts-widget'); ?></label>
    <br/>
     <input name="<?php echo $this->get_field_name("display_featured_image"); ?>" id="<?php echo $this->get_field_id("display_featured_image"); ?>" class="str-field"  type="checkbox" <?php if ($instance['display_featured_image']) echo 'checked="checked"';?> >
     <label><?php _e( 'Featured Image'); ?></label>
    <input style="text-align: center;" name="<?php echo $this->get_field_name("size_f_image"); ?>" type="text" size='1' maxlength='3' id="<?php echo $this->get_field_id("size_f_image")?>" value="<?php echo htmlspecialchars(stripslashes($instance['size_f_image'])); ?>"/>
    px<br/>
    <label>
      <input name="<?php echo $this->get_field_name("display_comments_number"); ?>" id="<?php echo $this->get_field_id("display_comments_number"); ?>" class="str-field"  type="checkbox" <?php if ($instance['display_comments_number']) echo 'checked="checked"';?> >
      <?php _e( 'Comments Number', 'thethe-posts-widget'); ?>
    </label>
    <br/>
    <label>
      <input name="<?php echo $this->get_field_name("display_author"); ?>" id="<?php echo $this->get_field_id("display_author"); ?>" class="str-field"  type="checkbox" <?php if ($instance['display_author']) echo 'checked="checked"';?> >
      <?php _e( 'Author' ); ?>
    </label>
    <br/>
    <label>
      <input name="<?php echo $this->get_field_name("display_author_avatar"); ?>" id="<?php echo $this->get_field_id("display_author_avatar"); ?>" class="str-field"  type="checkbox" <?php if ($instance['display_author_avatar']) echo 'checked="checked"';?> >
      <?php _e( 'Author\'s Avatar', 'thethe-posts-widget'); ?>
    </label>
    <input style="text-align: center;" name="<?php echo $this->get_field_name("size_avatar"); ?>" type="text" size='1' maxlength='3' id="<?php echo $this->get_field_id("size_avatar")?>" value="<?php echo htmlspecialchars(stripslashes($instance['size_avatar'])); ?>"/>
    px<br/>
    <label>
      <input name="<?php echo $this->get_field_name("display_date"); ?>" id="<?php echo $this->get_field_id("display_date"); ?>" class="str-field"  type="checkbox" <?php if ($instance['display_date']) echo 'checked="checked"';?> >
      <?php _e( 'Date'); ?>
    </label>
    <br/>
    <label>
      <input name="<?php echo $this->get_field_name("display_tags"); ?>" id="<?php echo $this->get_field_id("display_tags"); ?>" class="str-field"  type="checkbox" <?php if ($instance['display_tags']) echo 'checked="checked"';?> >
      <?php _e( 'Tags' ); ?>
    </label>
    <br/>
    <label>
      <input name="<?php echo $this->get_field_name("display_categoris"); ?>" id="<?php echo $this->get_field_id("display_categoris"); ?>" class="str-field"  type="checkbox" <?php if ($instance['display_categoris']) echo 'checked="checked"';?> >
      <?php _e( 'Categories' ); ?>
    </label>
  </p>
  <p>
    <label>
      <input id="<?php echo $this->get_field_id("use_template"); ?>" name="<?php echo $this->get_field_name("use_template"); ?>" type="radio" <?php if ( $instance['use_template']=="custom_template" ) echo "checked"; ?> value="custom_template" />
      <?php _e('Custom Output Template', 'thethe-posts-widget') ?></label>
      <span class="thethe-tooltip"> ? <span>
      {title}<br />{avatar}<br />{image}<br />{commentcount}<br />{excerpt}<br />{author}<br />{date}<br />{tags}<br />{category}
      </span></span>
  </p>
  <p style="margin-left:5px">         
    <textarea name="<?php echo $this->get_field_name("output_template"); ?>" id="<?php echo $this->get_field_id("output_template")?>" rows="4" cols="30"><?php echo htmlspecialchars(stripslashes($instance['output_template'])); ?></textarea>
  </p>
  <p>
    <label>
      <?php _e('Default Display if no Matches:', 'thethe-posts-widget') ?>
      <input name="<?php echo $this->get_field_name("none_text"); ?>" type="text"  class="widefat" id="<?php echo $this->get_field_id("none_text")?>" value="<?php echo htmlspecialchars(stripslashes($instance['none_text'])); ?>"/>
    </label>
  </p>
  <h3>
    <?php _e( 'Filter by Types & Formats', 'thethe-posts-widget'); ?>
  </h3>
  <p>
    <?php _e( 'Display Posts Types', 'thethe-posts-widget'); ?>
    : </p>
  <ul class="cols-2">
    <li>
      <label>
        <input name="<?php echo $this->get_field_name("display_type_post"); ?>" id="<?php echo $this->get_field_id("display_type_post"); ?>" class="str-field"  type="checkbox" <?php if ($instance['display_type_post']) echo 'checked="checked"';?> >
        <?php _e( 'Post' ); ?>
      </label>
    </li>
    <li>
      <label>
        <input name="<?php echo $this->get_field_name("display_type_page"); ?>" id="<?php echo $this->get_field_id("display_type_page"); ?>" class="str-field"  type="checkbox" <?php if ($instance['display_type_page']) echo 'checked="checked"';?> >
        <?php _e( 'Page' ); ?>
      </label>
    </li>
    <li>
      <label>
        <input name="<?php echo $this->get_field_name("display_type_revision"); ?>" id="<?php echo $this->get_field_id("display_type_revision"); ?>" class="str-field"  type="checkbox" <?php if ($instance['display_type_revision']) echo 'checked="checked"';?> >
        <?php _e( 'Revision'); ?>
      </label>
    </li>
    <li>
      <label>
        <input name="<?php echo $this->get_field_name("display_type_attachment"); ?>" id="<?php echo $this->get_field_id("display_type_attachment"); ?>" class="str-field"  type="checkbox" <?php if ($instance['display_type_attachment']) echo 'checked="checked"';?> >
        <?php _e( 'Attachments' ); ?>
      </label>
    </li>
  </ul>
  <p>
    <?php _e( 'Display Posts Status', 'thethe-posts-widget'); ?>
    : </p>
  <ul class="cols-2">
    <li>
      <label>
        <input name="<?php echo $this->get_field_name("display_type_published"); ?>" id="<?php echo $this->get_field_id("display_type_published"); ?>" class="str-field"  type="checkbox" <?php if ($instance['display_type_published']) echo 'checked="checked"';?> >
        <?php _e( 'Published' ); ?>
      </label>
    </li>
    <li>
      <label>
        <input name="<?php echo $this->get_field_name("display_type_pending"); ?>" id="<?php echo $this->get_field_id("display_type_pending"); ?>" class="str-field"  type="checkbox" <?php if ($instance['display_type_pending']) echo 'checked="checked"';?> >
        <?php _e( 'Pending' ); ?>
      </label>
    </li>
    <li>
      <label>
        <input name="<?php echo $this->get_field_name("display_type_draft"); ?>" id="<?php echo $this->get_field_id("display_type_draft"); ?>" class="str-field"  type="checkbox" <?php if ($instance['display_type_draft']) echo 'checked="checked"';?> >
        <?php _e( 'Draft' ); ?>
      </label>
    </li>
    <li>
      <label>
        <input name="<?php echo $this->get_field_name("display_type_auto_draft"); ?>" id="<?php echo $this->get_field_id("display_type_auto_draft"); ?>" class="str-field"  type="checkbox" <?php if ($instance['display_type_auto_draft']) echo 'checked="checked"';?> >
        <?php _e( 'Auto-Draft' ); ?>
      </label>
    </li>
    <li>
      <label>
        <input name="<?php echo $this->get_field_name("display_type_future"); ?>" id="<?php echo $this->get_field_id("display_type_future"); ?>" class="str-field"  type="checkbox" <?php if ($instance['display_type_future']) echo 'checked="checked"';?> >
        <?php _e( 'Future', 'thethe-posts-widget' ); ?>
      </label>
    </li>
    <li>
      <label>
        <input name="<?php echo $this->get_field_name("display_type_private"); ?>" id="<?php echo $this->get_field_id("display_type_private"); ?>" class="str-field"  type="checkbox" <?php if ($instance['display_type_private']) echo 'checked="checked"';?> >
        <?php _e( 'Private' ); ?>
      </label>
    </li>
    <li>
      <label>
        <input name="<?php echo $this->get_field_name("display_type_inherit"); ?>" id="<?php echo $this->get_field_id("display_type_inherit"); ?>" class="str-field"  type="checkbox" <?php if ($instance['display_type_inherit']) echo 'checked="checked"';?> >
        <?php _e( 'Inherit'); ?>
      </label>
    </li>
    <li>
      <label>
        <input name="<?php echo $this->get_field_name("display_type_trash"); ?>" id="<?php echo $this->get_field_id("display_type_trash"); ?>" class="str-field"  type="checkbox" <?php if ($instance['display_type_trash']) echo 'checked="checked"';?> >
        <?php _e( 'Trash' ); ?>
      </label>
    </li>
  </ul>
  <?php if (get_theme_support('post-formats')) { ?>
  <p>
    <?php _e( 'Display Posts Formats', 'thethe-posts-widget'); ?>
    : </p>
  <ul class="cols-2">
    <?php 
						$post_formats=$_wp_theme_features['post-formats'][0];
						foreach ($post_formats as $value)
						{
							$checked='';		
							if ($instance['display_format_'.$value]) $checked='checked="checked"';
							echo '<li><label><input name="'.$this->get_field_name("display_format_$value").'" id="'.$this->get_field_id("display_format_$value").'" class="str-field"  type="checkbox" '.$checked.' > '.$value.'</label></li>';
						}		
					?>
  </ul>
  <?php } ?>
  <h3>
    <?php _e( 'Filter by Time Frame', 'thethe-posts-widget'); ?>
  </h3>
  <p>
    <?php _e('Display Published:', 'thethe-posts-widget') ?>
  </p>
  <table>
    <tr>
      <td><label for="<?php echo $this->get_field_id("display_published_from"); ?>">from:</label></td>
      <td><input name="<?php echo $this->get_field_name("display_published_from"); ?>" type="text"  class="datepicker" id="<?php echo $this->get_field_id("display_published_from")?>" value="<?php echo htmlspecialchars(stripslashes($instance['display_published_from'])); ?>"/></td>
    </tr>
    <tr>
      <td><label for="<?php echo $this->get_field_id("display_published_to"); ?>">to:</label></td>
      <td><input name="<?php echo $this->get_field_name("display_published_to"); ?>" type="text" class="datepicker" id="<?php echo $this->get_field_id("display_published_to")?>" value="<?php echo htmlspecialchars(stripslashes($instance['display_published_to'])); ?>"/></td>
    </tr>
  </table>
  <h3>
    <?php _e('Post IDs to Include & Exclude', 'thethe-posts-widget'); ?>
  </h3>
  <p>
    <label for="<?php echo $this->get_field_id("excluded_posts"); ?>">
      <?php _e('Post IDs to Include & Exclude:', 'thethe-posts-widget') ?>
    </label>
  </p>
  <p>
    <label>
      <input id="<?php echo $this->get_field_id("ex_posts"); ?>" name="<?php echo $this->get_field_name("ex_posts"); ?>" type="radio" <?php if ( $instance['ex_posts']=="exclude" ) echo 'checked="checked"'; ?> value="exclude" />
      Exclude</label>
    <label>
      <input id="<?php echo $this->get_field_id("in_posts"); ?>" name="<?php echo $this->get_field_name("ex_posts"); ?>" type="radio" <?php if ( $instance['ex_posts']=="include" ) echo 'checked="checked"'; ?> value="include" />
      Include</label>
  </p>
  <p>
    <input class="widefat" name="<?php echo $this->get_field_name("excluded_posts"); ?>" type="text" id="<?php echo $this->get_field_id("excluded_posts")?>" value="<?php if ($instance['excluded_posts']=='') echo "Example: 1,2,4,-3"; else echo $instance['excluded_posts']; ?>" size="30" onblur="if(this.value == '') { this.value='Example: 1,2,4,-3'}" onfocus="if (this.value == 'Example: 1,2,4,-3') {this.value=''}" />
    <small>
    <?php _e('comma-separated IDs', 'thethe-posts-widget'); ?>
    </small> </p>
  <p>
    <label for="<?php echo $this->get_field_id("excluded_author"); ?>">
      <?php _e('Authors IDs to Include & Exclude:', 'thethe-posts-widget') ?>
    </label>
    <input class="widefat" name="<?php echo $this->get_field_name("excluded_author"); ?>" type="text" id="<?php echo $this->get_field_id("excluded_author")?>" value="<?php if ($instance['excluded_author']=='') echo "Example: 1,2,4,-3"; else  echo $instance['excluded_author']; ?>" size="30"  onblur="if(this.value == '') { this.value='Example: 1,2,4,-3'}" onfocus="if (this.value == 'Example: 1,2,4,-3') {this.value=''}" />
    <small>
    <?php _e('comma-separated IDs', 'thethe-posts-widget'); ?>
    </small> </p>
  <p>
    <label for="<?php echo $this->get_field_id("excluded_cat"); ?>">
      <?php _e('Categories IDs to Include & Exclude:', 'thethe-posts-widget') ?>
    </label>    
      <input class="widefat" name="<?php echo $this->get_field_name("excluded_cat"); ?>" type="text" id="<?php echo $this->get_field_id("excluded_cat")?>" value="<?php if ($instance['excluded_cat']=='') echo "Example: 1,2,4,-3"; else echo $instance['excluded_cat']; ?>" size="30" onblur="if(this.value == '') { this.value='Example: 1,2,4,-3'}" onfocus="if (this.value == 'Example: 1,2,4,-3') {this.value=''}" /> 
    <small>
    <?php _e('comma-separated IDs', 'thethe-posts-widget'); ?>
    </small> </p>
  <p>
    <label for="<?php echo $this->get_field_id("tag_str"); ?>">
      <?php  _e('Match posts with tags:<br />(a,b matches posts with either tag, a+b only matches posts with both tags)', 'thethe-posts-widget') ?>
    </label>
    <input class="widefat" name="<?php echo $this->get_field_name("tag_str"); ?>" type="text" id="<?php echo $this->get_field_id("tag_str")?>" value="<?php echo $instance['tag_str']; ?>" size="30" <?php if ($wp_version < 2.3) echo 'disabled="true"'; ?> />
  </p>
  <p>
    <?php _e('Custom Fileds to Match:', 'thethe-posts-widget') ?>
  </p>
  <p>
    <label>Field Name:
      <input name="<?php echo $this->get_field_name("custom-key"); ?>" type="text" id="<?php echo $this->get_field_id("custom-key")?>" value="<?php echo $instance['custom-key']; ?>" size="20" />
    </label>
    <br/>
    <label>Compare:
      <select name="<?php echo $this->get_field_name("custom-op"); ?>" id="<?php echo $this->get_field_id("custom-op")?>">
        <option <?php if($instance['custom-op'] == '=') { echo 'selected="selected"'; } ?> value="=">=</option>
        <option <?php if($instance['custom-op'] == '!=') { echo 'selected="selected"'; } ?> value="!=">!=</option>
        <option <?php if($instance['custom-op'] == '>') { echo 'selected="selected"'; } ?> value=">">></option>
        <option <?php if($instance['custom-op'] == '>=') { echo 'selected="selected"'; } ?> value=">=">>=</option>
        <option <?php if($instance['custom-op'] == '<') { echo 'selected="selected"'; } ?> value="<"><</option>
        <option <?php if($instance['custom-op'] == '<=') { echo 'selected="selected"'; } ?> value="<="><=</option>
        <option <?php if($instance['custom-op'] == 'LIKE') { echo 'selected="selected"'; } ?> value="LIKE">LIKE</option>
        <option <?php if($instance['custom-op'] == 'NOT LIKE') { echo 'selected="selected"'; } ?> value="NOT LIKE">NOT LIKE</option>
        <option <?php if($instance['custom-op'] == 'IN') { echo 'selected="selected"'; } ?> value="IN">IN</option>
        <option <?php if($instance['custom-op'] == 'NOT IN') { echo 'selected="selected"'; } ?> value="NOT IN">NOT IN</option>
        <option <?php if($instance['custom-op'] == 'BETWEEN') { echo 'selected="selected"'; } ?> value="BETWEEN">BETWEEN</option>
        <option <?php if($instance['custom-op'] == 'NOT BETWEEN') { echo 'selected="selected"'; } ?> value="NOT BETWEEN">NOT BETWEEN</option>
      </select>
    </label>
    <br/>
    <label>Field Value:
      <input name="<?php echo $this->get_field_name("custom-value"); ?>" type="text" id="<?php echo $this->get_field_id("custom-value")?>" value="<?php echo $instance['custom-value']; ?>" size="20" />
    </label>
  </p>
  <h3>
    <?php _e('Sort Output', 'thethe-posts-widget'); ?>
  </h3>
  <p>
    <label>
      <input name="<?php echo $this->get_field_name("enable_sort"); ?>" id="<?php echo $this->get_field_id("enable_sort"); ?>" class="str-field"  type="checkbox" <?php if ($instance['enable_sort']) echo 'checked="checked"';?> >
      <?php _e( 'Enable Sort Output' ); ?>
      <br/>
    </label>
  </p>
  <p>
    <label> <?php _e('Order:', 'thethe-posts-widget') ?> </label>
      <select name="<?php echo $this->get_field_name("order_post"); ?>" id="<?php echo $this->get_field_id("order_post")?>">
        <option <?php if($instance['order_post'] == 'ASC') { echo 'selected="selected"'; } ?> value="ASC">ascending</option>
        <option <?php if($instance['order_post'] == 'DESC') { echo 'selected="selected"'; } ?> value="DESC">descending</option>
      </select>
   
  </p>
  <p>
    <label> <?php _e('Order by:', 'thethe-posts-widget') ?> </label>
      <select name="<?php echo $this->get_field_name("orderby_post"); ?>" id="<?php echo $this->get_field_id("orderby_post")?>">
        <option <?php if($instance['orderby_post'] == 'ID') { echo 'selected="selected"'; } ?> value="ID">ID</option>
        <option <?php if($instance['orderby_post'] == 'author') { echo 'selected="selected"'; } ?> value="author">author</option>
        <option <?php if($instance['orderby_post'] == 'title') { echo 'selected="selected"'; } ?> value="title">title</option>
        <option <?php if($instance['orderby_post'] == 'date') { echo 'selected="selected"'; } ?> value="date">date</option>
        <option <?php if($instance['orderby_post'] == 'parent') { echo 'selected="selected"'; } ?> value="parent">parent</option>
        <option <?php if($instance['orderby_post'] == 'rand') { echo 'selected="selected"'; } ?> value="rand">rand</option>
        <option <?php if($instance['orderby_post'] == 'comment_count') { echo 'selected="selected"'; } ?> value="comment_count">comment_count</option>
        <option <?php if($instance['orderby_post'] == 'menu_order') { echo 'selected="selected"'; } ?> value="menu_order">menu_order</option>
        <option <?php if($instance['orderby_post'] == 'meta_value') { echo 'selected="selected"'; } ?> value="meta_value">meta_value</option>
        <option <?php if($instance['orderby_post'] == 'meta_value_num') { echo 'selected="selected"'; } ?> value="meta_value_num">meta_value_num</option>
      </select>
  </p>
</div>
<?php 
	}
	
	public function thethe_title()
	{
		return '<h4 class="post-title"><a href="'.  get_permalink() .'" rel="bookmark" title="Permanent link to '. get_the_title() .'">'. get_the_title() .'</a></h4>';
	}
	
	public function thethe_author()
	{
		return  get_the_author();
	}
	
	public function thethe_commentcount()
	{
		$num_comments = get_comments_number(); 
		if ( comments_open() ){
			if($num_comments == 0){
				$comments = __('No Comments');
			}
			elseif($num_comments > 1){
				$comments = $num_comments. __(' Comments');
			}
			else{
				$comments ="1 Comment";
			}
			$write_comments = '<a href="' . get_comments_link() .'">'. $comments.'</a>';
		}
		else{$write_comments =  __('Comments are off for this post');
		}
		return $write_comments;
	}
	
	public function thethe_category()
	{
		if (get_the_category())
		{
			foreach((get_the_category()) as $category) {
				$result[]= '<a href="'. get_category_link($category->term_id) .'">'.  $category->cat_name  .'</a>';
			}
			return implode(", ",$result);
		}
	}
	
	public function thethe_date()
	{
		return get_the_time("j M Y");
	}
	
	public function thethe_excerpt($instance)
	{
		$content = strip_tags( get_the_excerpt() );
		if(trim($instance["excerpt_length_type"]) == 'words') {
		$words = explode(' ',$content);
	
		if(count($words) > trim($instance["excerpt_length"])) {
		array_splice($words, trim($instance["excerpt_length"]));
		$output = implode(' ', $words) . '...';
		} else {
			$output = $content;
		}
		} else if(trim($instance["excerpt_length_type"]) == "chars") {
			if(strlen($content) > trim($instance["excerpt_length"])) {
				$output = mb_substr($content,0,trim($instance["excerpt_length"])).'...';
			} else {
				$output = $content;
			}
		}
		return $output;
	}
	
	
	public function thethe_tags()
	{
		if (get_the_tags())
		{
			foreach(get_the_tags()as $tag) {
				$result[]= '<a href="'. get_tag_link($tag->term_id) .'">'. $tag->name .'</a>';
			}
			return implode(", ",$result);
		}
	}
	
	public function thethe_avatar($size_avatar)
	{
		return   get_avatar( get_the_author_meta('ID'),$size_avatar);
	}
	
	public function thethe_image($size_f_image)
	{
		return '<a href="'. get_permalink() .'" title="'. get_the_title(). '">
		'. get_the_post_thumbnail($post->ID,array($size_f_image,$size_f_image)) .'</a>';
	}
	

} // end class PluginPostsWidget



