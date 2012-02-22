<?php /** @version $Id: inc.header.php 1516 2012-01-11 14:21:41Z xagero $ */?>
<div id="thethefly">
  <div class="wrap">
    <h2 id="thethefly-panel-title"> <span id="thethefly-panel-icon" class="icon48">&nbsp;</span><?php print $this->_config['meta']['Name'];?></h2>
    <div id="thethefly-panel-frame">
      <div id="menu-management-liquid">
        <div id="menu-management"> 
          <!-- tabs -->
          <div class="nav-tabs-wrapper">
            <div class="nav-tabs">
<?php
$view = $this->getCurrentViewIndex();
if (is_array($this->viewIndexAll)) foreach ($this->viewIndexAll as $key => $dataView) {
	if ($view == $key) {
		printf ('<span class="nav-tab nav-tab-active">%s</span>',$dataView['title-tab']);
	} else {
		printf("<a href='%s' class='nav-tab hide-if-no-js'>%s</a>",$this->getTabURL($key),$dataView['title-tab']);
	}
}
?>
            </div>
          </div>
          <!-- /tabs -->
          <div class='menu-edit tab-overview'>
            <div id='nav-menu-header'>
              <div class='major-publishing-actions'> <span><?php print $this->viewIndexAll[$view]['title']; ?></span>
                <div class="sep">&nbsp;</div>
              </div>
              <!-- END .major-publishing-actions --> 
            </div>
            <!-- END #nav-menu-header -->
            <div id='post-body'>
              <div id='post-body-content'>