<!-- sidebar: style can be found in sidebar.less -->
<section class="sidebar">
  <ul class="sidebar-menu">
    <li class="header">MAIN NAVIGATION</li>
    <li class="treeview"> 
        <a href="<?php echo WEB_ROOT; ?>views/?v=DB"><i class="fa fa-calendar"></i><span>Conference timeframes</span></a>
    </li>
    <li class="treeview"> 
        <a href="<?php echo WEB_ROOT; ?>views/?v=LIST"><i class="fa fa-newspaper-o"></i><span>Conference setup</span></a>
    </li>
    <li class="treeview"> 
        <a href="<?php echo WEB_ROOT; ?>views/?v=USERS"><i class="fa fa-users"></i><span>Speaker Details</span></a>
    </li>
    <?php 
    $type = $_SESSION['calendar_fd_user']['type'];
    if ($type == 'admin') {
    ?>
    <li class="treeview"> 
        <a href="<?php echo WEB_ROOT; ?>views/?v=HOLY"><i class="fa fa-plane"></i><span>Holidays</span></a>
    </li>
    <?php
    }
    ?>
    <?php 
    if ($type == 'teacher') { ?>
        <li class="treeview"> 
            <a href="<?php echo WEB_ROOT; ?>views/?v=PARTICIPANTS"><i class="fa fa-file"></i><span>Participants</span></a>
        </li>
    <?php } ?>

    <!-- Add My Conferences for students -->
    <?php 
    if ($type == 'student') { ?>
        <li class="treeview"> 
            <a href="<?php echo WEB_ROOT; ?>views/?v=MY_CONFERENCES"><i class="fa fa-list"></i><span>My Conferences</span></a>
        </li>
    <?php } ?>
  </ul>
</section>
<!-- /.sidebar -->
