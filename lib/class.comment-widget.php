<?php
/**
 * @version		$Id: class.comment-widget.php 1530 2012-01-13 11:13:58Z dmitriy.f $
 * @author		xagero
 */

class PluginCommentWidget extends WP_Widget
{
	// }}}
	
	public function PluginCommentWidget(){
		parent::WP_Widget( /* Base ID */'thethe_comment', /* Name */'TheThe Comments', array( 'description' => 'TheThe Recent Comments' ) );
	}
	
	
	public function comments_widget( $args, $instance )
	{
		global $post, $wpdb, $wp_query;
		extract( $args );
	
	
		if (trim($instance["display_type"])!='')
		{
			$post_type=explode(",", $instance["display_type"]);
		}
		else
		{
			if( $instance["display_type_post"] ) $post_type[]='post';
			if( $instance["display_type_page"] ) $post_type[]='page';
		}
	
		if(trim($instance['excluded_author']) != '') {
			$sql_commenter = "AND LOWER(c.user_id) NOT IN (".trim($instance["excluded_author"]).")";
		} else
			if(trim($instance['include_author']) != '') {
			$sql_commenter = "AND LOWER(c.user_id) IN (".trim($instance["include_author"]).")";
		} else {
			$sql_commenter = '';
		}
	
		if(count($post_type)>0) {
			$sql_post_type = "AND p.post_type IN ('".implode("','",$post_type)."')";
		} else {
			$sql_post_type = '';
		}
	
		if(trim($instance["excluded_posts"])) {
			$sql_ex_post = "AND p.ID NOT IN (".trim($instance["excluded_posts"]).")";
		} else {
			$sql_ex_post = '';
		}
	
		if(trim($instance["include_posts"])) {
			$sql_in_post = "AND p.ID IN (".trim($instance["include_posts"]).")";
		} else {
			$sql_in_post = '';
		}
	
		if((trim($instance["include_cat"]) == '') && (trim($instance["excluded_cat"])== '')) {
			$query = "SELECT 	c.comment_ID,
			c.comment_post_ID,
			c.comment_author,
			c.comment_author_email,
			c.comment_author_url,
			c.comment_content,
			UNIX_TIMESTAMP(p.post_date) AS `post_date`
			FROM $wpdb->comments c
			LEFT JOIN $wpdb->posts p
			ON c.comment_post_ID = p.ID
			WHERE c.comment_approved = '1'
			AND c.comment_type = ''
			$sql_commenter
			$sql_post_type
			$sql_ex_post
			$sql_in_post
			ORDER BY c.comment_date_gmt DESC
			LIMIT $instance[quantity]";
	
		} else {
			$sql_cat = '';
			if(trim($instance["include_cat"]) != '') {
				$sql_cat = "AND t.term_id IN (" . trim($instance["include_cat"]) . ")";
			}
		else if (trim($instance["excluded_cat"]) != '') {
			$sql_cat = "AND t.term_id NOT IN (" . trim($instance["excluded_cat"]) . ")";
		}
	
		$query = "SELECT 	c.comment_ID,
		c.comment_post_ID,
		c.comment_author,
		c.comment_author_email,
		c.comment_author_url,
		c.comment_content,
		UNIX_TIMESTAMP(p.post_date) AS `post_date`
		FROM $wpdb->comments c
		LEFT JOIN $wpdb->posts p
		ON c.comment_post_ID = p.ID
		LEFT JOIN $wpdb->term_relationships r
		ON p.ID = r.object_id
		LEFT JOIN $wpdb->term_taxonomy t
		ON r.term_taxonomy_id = t.term_taxonomy_id
		WHERE c.comment_approved = '1'
		AND c.comment_type = ''
		AND t.taxonomy = 'category'
		$sql_cat
		$sql_commenter
		$sql_post_type
		$sql_ex_post
		$sql_in_post
		ORDER BY c.comment_date_gmt DESC
		LIMIT $instance[quantity]";
		}
		$comments = $wpdb->get_results($query);
		$html='';
		if (!$comments) {
		$result = "none";
		}
		
		if($result == "none") {
		$html.= '<p>'. $instance["none_text"] .'</p>';
		}
		else {
		
		$html.= $before_widget;
		
		$html.= $before_title;
		if( !$instance["title"] ) {
		$category_info = get_category($instance["cat"]);
		$instance["title"] = $category_info->name;
		}
		if( $instance["title"] ) $html.= $instance["title"];
		$html.= $after_title;
		
		$html.= '<ul class="thethe-comments">';
		foreach ($comments as $com) {
		$replace_args = array (	$com->comment_ID,
				$com->comment_author,
				get_comment_date($instance['date_format'], $com->comment_ID),
				get_avatar( $com->comment_author_email , $instance['size_avatar'] ),
				get_permalink( $com->comment_post_ID ),
				get_the_title( $com->comment_post_ID ),
				$com->comment_content,
				$com->comment_author_url
				);
		
				if ( $instance['use_template']=="default_template" ) {
		
				$html.= '<li class="thethe-comment">';
		
			if ( $instance['display_author_avatar'] ) {
			$html.= '<div class="comment-author-avatar">';
			if ( $instance['display_link_author'] && $com->comment_author_url!='') $html.= '<a href="'. $com->comment_author_url.'">';
			if ( $instance['display_author_avatar'] ) 	$html.= get_avatar( $com->comment_author_email , $instance['size_avatar'] );
			if ( $instance['display_link_author'] && $com->comment_author_url!='') $html.= '</a>';
			$html.= '</div>';
		}
		if ( $instance['display_author'] || $instance['display_title_post']) {
			$html.= '<h4 class="comment-author comment-title">';
			if ( $instance['display_link_author'] && $com->comment_author_url!='') $html.= '<a href="'. $com->comment_author_url.'">';
			if ( $instance['display_author'] ) $html.= '<span class="comment-author">'.$com->comment_author.'</span>';
			if ( $instance['display_link_author'] && $com->comment_author_url!='') $html.= '</a>';
			if ( $instance['display_author'] &&  $instance['display_title_post'] ) $html.= ' on ';
			if ( $instance['display_title_post'] ){
			if ( $instance['display_link_post'] ) $html.= '<a href="'. get_permalink( $com->comment_post_ID ). '">';
			$html.= '<span class="post-title">'.get_the_title( $com->comment_post_ID ).'</span>';
			if ( $instance['display_link_post'] ) $html.= '</a>';
			}
			$html.= '</h4>';
			}
		
			if ( $instance['display_excerpt'] ) {
		
			$content = strip_tags( $com->comment_content );
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
			$html.= '<div class="comment-text">';
			if ( $instance['display_link_comment'] )
				$html.= '<a href="'. get_permalink( $com->comment_post_ID ).'#comment-'. $com->comment_ID .'">'. $output .'</a>';
				else
				$html.= $output;
				$html.= '</div>';
		}
		if ( $instance['display_date'] )
				$html.= '<div class="comment-meta">Posted on: '. get_comment_date($instance['date_format'], $com->comment_ID) .'</div>';
		
				$html.= "</li>";
		}
		else
		{
		$html.= $this->output_template($replace_args, $instance);
		}
		
		}
		$html.= "</ul>\n";
		}
		$html.= $after_widget;
		$post = $post_old;
		return $html;
	}
	
	public function widget( $args, $instance ) 
	{
		echo $this->comments_widget($args, $instance);
	}
	

	public function output_template($replace_args, $instance)
	{ 

		//$template="<li>{author}{authorlink}{avatar}{postlink}{posttitle}{commentlink}{excerpt}{commentdate}</li>";
		
		$template=$instance['output_template'];
		
		preg_match_all('/{((?:[^{}]|{[^{}]*})*)}/', $template, $matches);
		foreach ($matches[1] as $match) {
			switch ($match) {
				case 'avatar':
					$replace=$replace_args[3];
					break;
				case 'author':
					$replace=$replace_args[1];
					break;
				case 'authorlink':
					$replace=$replace_args[7];
					break;
				case 'commentlink':
					$replace=$replace_args[4]."#comment-".$replace_args[0];
					break;
				case 'postlink':
					$replace=$replace_args[4];
					break;
				case 'posttitle':
					$replace=$replace_args[5];
					break;
				case 'commentdate':
					$replace=$replace_args[2];
					break;
				case 'excerpt':
					$replace=$replace_args[6];
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
			if (trim($v)=="Example: 1,2,4") $instance[$k]='';
		}
		$instance['display_title_post']= strip_tags($new_instance['display_title_post']);
		$instance['display_link_post']= strip_tags($new_instance['display_link_post']);
		$instance['display_excerpt']= strip_tags($new_instance['display_excerpt']);
		$instance['display_link_comment']= strip_tags($new_instance['display_link_comment']);
		$instance['display_author']= strip_tags($new_instance['display_author']);
		$instance['display_link_author']= strip_tags($new_instance['display_link_author']);
		$instance['display_author_avatar']= strip_tags($new_instance['display_author_avatar']);
		$instance['display_date']= strip_tags($new_instance['display_date']);
		$instance['excerpt_length_type']= strip_tags($new_instance['excerpt_length_type']);
		
		$instance['display_type_post']= strip_tags($new_instance['display_type_post']);
		$instance['display_type_page']= strip_tags($new_instance['display_type_page']);
		
		return $instance;
	}
	
	/** @see WP_Widget::form */
	public function form( $instance ) 
	{
		global $wpdb, $wp_version, $_wp_theme_features;

		//filter the new instance and replace blanks with defaults
    	$defaults = array(
    		'quantity' => '5',
    		'use_template' => 'default_template',
 
    		// What to Display
    		'display_title_post' => '1',
    		'display_link_post' => '1',
    		'display_link_comment' => '1',
    		'display_link_author' => '1',
    		'display_excerpt' => '1',
    		'display_author' => '1',
    		'display_author_avatar' => '1',
    		'display_date' => '1',
    		// How to Display
    		'excerpt_length' => '10',
    		'excerpt_length_type' => 'words',
    		'size_avatar' => '32',
    		'none_text' => 'No Matches Found',
    		'output_template' => '<li class="thethe-comment">
<div class="comment-title">
<a href="{authorlink}">{avatar} {author}</a> on <a href="{postlink}">{posttitle}</a>
</div>
<div class="comment-body">
<p align="justify"><a href="{commentlink}">{excerpt}...</a></p>
</div>
<div class="comment-info">Posted on: {commentdate}</div>
</li>',
    		'date_format' => 'd-m-Y',
    		//Display Posts Types
    		'display_type_post' => '1',
    		'display_type_page' => '0'
    	);
		$instance = array_merge($defaults, $instance);
	?>
	<div class="wrap">
		<p>
			<label for="<?php echo $this->get_field_id("title"); ?>">
				<?php _e( 'Widget Title' ); ?>:
			</label>                
				<input class="widefat" id="<?php echo $this->get_field_id("title"); ?>" name="<?php echo $this->get_field_name("title"); ?>" type="text" value="<?php echo esc_attr($instance["title"]); ?>" />

		</p>
		<p>
			<label>
				<?php _e('Comments to Show'); ?>:
				<input style="text-align: center;" id="<?php echo $this->get_field_id("quantity"); ?>" name="<?php echo $this->get_field_name("quantity"); ?>" type="text" value="<?php echo absint($instance["quantity"]); ?>" size='3' />
			</label>
		</p>
		<h3><?php _e( 'What and How to Output', 'thethe-posts-widget'); ?></h3>        
		<p>
			<label>	<input  id="<?php echo $this->get_field_id("use_template"); ?>" name="<?php echo $this->get_field_name("use_template"); ?>" type="radio" <?php if ( $instance['use_template']=="default_template" ) echo "checked"; ?> value="default_template" /> <?php _e('Use Default Template', 'thethe-posts-widget') ?></label>
		</p>                    
			<ul style="margin-left:5px">						
						<li><label><input name="<?php echo $this->get_field_name("display_author"); ?>" id="<?php echo $this->get_field_id("display_author"); ?>" class="str-field"  type="checkbox" <?php if ($instance['display_author']) echo 'checked="checked"';?> > <?php _e( 'Author' ); ?> </label></li>
						<li><label><input name="<?php echo $this->get_field_name("display_link_author"); ?>" id="<?php echo $this->get_field_id("display_link_author"); ?>" class="str-field"  type="checkbox" <?php if ($instance['display_link_author']) echo 'checked="checked"';?> > <?php _e( 'Link to Author' ); ?></label></li>
<li>						<label><input name="<?php echo $this->get_field_name("display_author_avatar"); ?>" id="<?php echo $this->get_field_id("display_author_avatar"); ?>" class="str-field"  type="checkbox" <?php if ($instance['display_author_avatar']) echo 'checked="checked"';?> > <?php _e( 'Avatar', 'thethe-posts-widget'); ?></label>
							<input style="text-align: center;" name="<?php echo $this->get_field_name("size_avatar"); ?>" type="text" size='1' maxlength='3' id="<?php echo $this->get_field_id("size_avatar")?>" value="<?php echo htmlspecialchars(stripslashes($instance['size_avatar'])); ?>"/>px</li>
						
						
						<li><label><input name="<?php echo $this->get_field_name("display_date"); ?>" id="<?php echo $this->get_field_id("display_date"); ?>" class="str-field"  type="checkbox" <?php if ($instance['display_date']) echo 'checked="checked"';?> > <?php _e( 'Date'); ?></label></li>						
						<li><label><input name="<?php echo $this->get_field_name("display_excerpt"); ?>" id="<?php echo $this->get_field_id("display_excerpt"); ?>" class="str-field"  type="checkbox" <?php if ($instance['display_excerpt']) echo 'checked="checked"';?> > <?php _e( 'Excerpt', 'thethe-posts-widget'); ?></label></li>	
						<li><label><input name="<?php echo $this->get_field_name("display_link_comment"); ?>" id="<?php echo $this->get_field_id("display_link_comment"); ?>" class="str-field"  type="checkbox" <?php if ($instance['display_link_comment']) echo 'checked="checked"';?> > <?php _e( 'Link to Comment' ); ?></label></li>
                        <li>
						<input style="text-align: center;" type="text" id="<?php echo $this->get_field_id("excerpt_length"); ?>" name="<?php echo $this->get_field_name("excerpt_length"); ?>" value="<?php echo $instance["excerpt_length"]; ?>" size="1" />
					
							<label><input  id="<?php echo $this->get_field_id("excerpt_length_words"); ?>" name="<?php echo $this->get_field_name("excerpt_length_type"); ?>" type="radio" <?php if ( $instance['excerpt_length_type']=="words" ) echo "checked"; ?> value="words" /><?php _e( 'Words', 'thethe-posts-widget'); ?></label>
							<label><input id="<?php echo $this->get_field_id("excerpt_length_char"); ?>" name="<?php echo $this->get_field_name("excerpt_length_type"); ?>" type="radio" <?php if ( $instance['excerpt_length_type']=="chars" ) echo "checked"; ?> value="chars" /><?php _e( 'Сharacters', 'thethe-posts-widget'); ?></label>
						</li>

						<li><label><input name="<?php echo $this->get_field_name("display_title_post"); ?>" id="<?php echo $this->get_field_id("display_title_post"); ?>" class="str-field"  type="checkbox" <?php if ($instance['display_title_post']) echo 'checked="checked"';?> > <?php _e( 'Title Post' ); ?></label></li> 
						<li><label><input name="<?php echo $this->get_field_name("display_link_post"); ?>" id="<?php echo $this->get_field_id("display_link_post"); ?>" class="str-field"  type="checkbox" <?php if ($instance['display_link_post']) echo 'checked="checked"';?> > <?php _e( 'Link to Post' ); ?></label></li>
				</ul>
<p>					<label><input id="<?php echo $this->get_field_id("use_template"); ?>" name="<?php echo $this->get_field_name("use_template"); ?>" type="radio" <?php if ( $instance['use_template']=="custom_template" ) echo "checked"; ?> value="custom_template" /> <?php _e('Custom Output Template', 'thethe-posts-widget') ?></label>  
<span class="thethe-tooltip"> ? <span>
{author}<br />{authorlink}<br />{avatar}<br />{postlink}<br />{posttitle}<br />{commentlink}<br />{excerpt}<br />{commentdate}
</span></span></p>
<p>						<textarea name="<?php echo $this->get_field_name("output_template"); ?>" id="<?php echo $this->get_field_id("output_template")?>" rows="4" cols="30"><?php echo htmlspecialchars(stripslashes($instance['output_template'])); ?></textarea></p>
						


		<p>
			<label>
				<?php _e('Date Format:', 'thethe-posts-widget') ?>
				<input name="<?php echo $this->get_field_name("date_format"); ?>" type="text"  class="widefat" id="<?php echo $this->get_field_id("date_format")?>" value="<?php echo htmlspecialchars(stripslashes($instance['date_format'])); ?>"/>
			</label>
		</p>
		<p>
			<label>
				<?php _e('Default Display if no Matches:', 'thethe-posts-widget') ?>
				<input name="<?php echo $this->get_field_name("none_text"); ?>" type="text"  class="widefat" id="<?php echo $this->get_field_id("none_text")?>" value="<?php echo htmlspecialchars(stripslashes($instance['none_text'])); ?>"/>
			</label>
		</p>

		<h3><?php _e( 'Filter by Types', 'thethe-posts-widget'); ?></h3>
		<p>
			<?php _e( 'Display Posts Types', 'thethe-posts-widget'); ?>:
        </p>
		<p>
			<label><input name="<?php echo $this->get_field_name("display_type_post"); ?>" id="<?php echo $this->get_field_id("display_type_post"); ?>" class="str-field"  type="checkbox" <?php if ($instance['display_type_post']) echo 'checked="checked"';?> > <?php _e( 'Post' ); ?></label><br/>
			<label><input name="<?php echo $this->get_field_name("display_type_page"); ?>" id="<?php echo $this->get_field_id("display_type_page"); ?>" class="str-field"  type="checkbox" <?php if ($instance['display_type_page']) echo 'checked="checked"';?> > <?php _e( 'Page' ); ?></label>
		</p>

		<h3><?php _e('Item IDs to Include & Exclude', 'thethe-posts-widget'); ?></h3>	
			<p>
				<label>
					<?php _e('Post IDs to Include:', 'thethe-posts-widget') ?><br/>
					<input name="<?php echo $this->get_field_name("include_posts"); ?>" type="text" size="30" id="<?php echo $this->get_field_id("include_posts")?>"  value="<?php if ($instance['include_posts']=='') echo "Example: 1,2,4"; else echo $instance['include_posts']; ?>" onblur="if(this.value == '') { this.value='Example: 1,2,4'}" onfocus="if (this.value == 'Example: 1,2,4') {this.value=''}" /> 
				</label>
				<small><?php _e('comma-separated IDs', 'thethe-posts-widget'); ?></small>
			</p>
			<p>
				<label>
					<?php _e('Post IDs to Exclude:', 'thethe-posts-widget') ?><br/>
					<input name="<?php echo $this->get_field_name("excluded_posts"); ?>" type="text" size="30" id="<?php echo $this->get_field_id("excluded_posts")?>"  value="<?php if ($instance['excluded_posts']=='') echo "Example: 1,2,4"; else echo $instance['excluded_posts']; ?>" onblur="if(this.value == '') { this.value='Example: 1,2,4'}" onfocus="if (this.value == 'Example: 1,2,4') {this.value=''}" />
				</label>
				<small><?php _e('comma-separated IDs', 'thethe-posts-widget'); ?></small>                
			</p>
			<p>
				<label>
					<?php _e('Authors IDs to Include:', 'thethe-posts-widget') ?>
					<input name="<?php echo $this->get_field_name("include_author"); ?>" type="text" size="30" id="<?php echo $this->get_field_id("include_author")?>"  value="<?php if ($instance['include_author']=='') echo "Example: 1,2,4"; else echo $instance['include_author']; ?>" onblur="if(this.value == '') { this.value='Example: 1,2,4'}" onfocus="if (this.value == 'Example: 1,2,4') {this.value=''}" />
				</label>
				<small><?php _e('comma-separated IDs', 'thethe-posts-widget'); ?></small>
			</p>
			<p>
				<label>
					<?php _e('Authors IDs to Exclude:', 'thethe-posts-widget') ?>
					<input name="<?php echo $this->get_field_name("excluded_author"); ?>" type="text" size="30" id="<?php echo $this->get_field_id("excluded_author")?>" value="<?php if ($instance['excluded_author']=='') echo "Example: 1,2,4"; else echo $instance['excluded_author']; ?>" onblur="if(this.value == '') { this.value='Example: 1,2,4'}" onfocus="if (this.value == 'Example: 1,2,4') {this.value=''}" />
				</label>
				<small><?php _e('comma-separated IDs', 'thethe-posts-widget'); ?></small>                
			</p>
			<p>
				<label>
					<?php _e('Categories IDs to Include:', 'thethe-posts-widget') ?>
					<input name="<?php echo $this->get_field_name("include_cat"); ?>" type="text" size="30" id="<?php echo $this->get_field_id("include_cat")?>" value="<?php if ($instance['include_cat']=='') echo "Example: 1,2,4"; else echo $instance['include_cat']; ?>" onblur="if(this.value == '') { this.value='Example: 1,2,4'}" onfocus="if (this.value == 'Example: 1,2,4') {this.value=''}" />
				</label>
				<small><?php _e('comma-separated IDs', 'thethe-posts-widget'); ?></small>                
			</p>
			<p>
				<label>
					<?php _e('Categories IDs to Exclude:', 'thethe-posts-widget') ?>
					<?php echo $instance['excluded_cat']; ?>
					<input name="<?php echo $this->get_field_name("excluded_cat"); ?>" type="text" size="30" id="<?php echo $this->get_field_id("excluded_cat")?>"  value="<?php if ($instance['excluded_cat']=='') echo "Example: 1,2,4"; else echo $instance['excluded_cat']; ?>" onblur="if(this.value == '') { this.value='Example: 1,2,4'}" onfocus="if (this.value == 'Example: 1,2,4') {this.value=''}" />
				</label>
				<small><?php _e('comma-separated IDs', 'thethe-posts-widget'); ?></small>                
			</p>
		</div>
		<?php 
	}
} // end class PluginCommentWidget
