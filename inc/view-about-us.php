<?php /** @version $Id: view-about-us.php 1516 2012-01-11 14:21:41Z xagero $ */ ?>
<div id="thethefly" class="about-us">
  <div class="wrap">
    <h2 id="thethefly-panel-title"> <span id="thethefly-panel-icon" class="icon48">&nbsp;</span> About TheThe Fly</h2>
    <div id="thethefly-panel-frame">
      <div id="menu-management-liquid">
        <div id="menu-management">
          <div class='menu-edit tab-overview'>
            <div id='nav-menu-header'>
              <div class='major-publishing-actions'> <span>About the Club</span>
                <div class="sep">&nbsp;</div>
              </div>
              <!-- END .major-publishing-actions --> 
            </div>
            <!-- END #nav-menu-header -->
            <div id='post-body'>
              <div id='post-body-content'>
                <div class="screenshot-wrap">
                  <div class="inner">
                    <div class='screenshot'><a href="http://thethefly.com/" title="TheTheFly.Com" target="_blank"><img src="<?php print $this->_config['meta']['wp_plugin_dir_url'];?>style/admin/images/thethefly.jpg" /></a></div>
                  </div>
                </div>
                <div class="info">
                  <p><em>Have WordPress but want more?</em></p>
                  <p align="justify">We fully believe in WP and its power to create state-of-the-art websites and blogs loaded with functionalities beyond imagination!</p>
                  <p align="justify">And that's the beauty of <strong>TheThe Fly</strong> - we provide a wide array of FREE and Premium WP Themes and Plugins that will help you do just anything under the sun on your WordPress Blog. </p>
                  <p align="justify"><strong>TheThe Fly</strong> WordPress Themes and Plugins are meant for businesses and individuals who are looking to offer extras to their visitors by adding more functionalities, better themes and want to get more than what is already out there.</p>                  
                  <p>To learn and download more, please visit our website at <a href="http://TheTheFly.com" target="_blank">TheTheFly.Com</a>.</p>
                </div>
                <div class="clear">&nbsp;</div>
                <div class="thethefly-latest-news">
                  <h4>From the News:</h4>
                  <?php $this->_displayRSS('http://news.thethefly.com/category/latest/feed/', 5);?>
                </div>
              </div>
              <!-- /#post-body-content --> 
            </div>
            <!-- /#post-body --> 
          </div>
          <!-- /.menu-edit --> 
        </div>
      </div>
      <!-- sidebar -->
      <div id="thethefly-admin-sidebar" class="metabox-holder">
        <div class="meta-box-sortables">
          <?php include 'inc.sidebar.newsletter.php';?>
          <?php include 'inc.sidebar.thethe-help.php';?>
          <?php include 'inc.sidebar.themes.php';?>
          <?php include 'inc.sidebar.plugins.php';?>
        </div>
      </div>
      <!-- /sidebar -->
      <div class="clear"></div>
    </div>
  </div>
</div>