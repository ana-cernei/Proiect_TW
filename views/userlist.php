<?php

$records = getUserRecords();
$utype = '';
$type = $_SESSION['calendar_fd_user']['type'];
if ($type == 'admin' || $type == 'teacher') {
    $utype = 'on';
}
?>

<div class="col-md-12">
  <div class="box">
    <div class="box-header with-border">
      <h3 class="box-title">User details</h3>
    </div>
    <div class="box-body">
      <table class="table table-bordered">
        <tr>
          <th style="width: 10px">#</th>
          <th>Name</th>
          <th>Email</th>
          <th>Phone</th>
         
          <th style="width: 100px">Status</th>
          <?php if ($utype == 'on') { ?>
          <th>Action</th>
          <?php } ?>
        </tr>
        <?php
        $idx = 1;
        foreach ($records as $rec) {
            $user_id = $rec['id'];
            $user_name = $rec['name'];
            $user_email = $rec['email'];
            $user_phone = $rec['phone'];
            $user_type = $rec['type'];
            $user_status = $rec['status'];

            $stat = '';
            if ($user_status == "active") {
                $stat = 'success';
            } else if ($user_status == "lock" || $user_status == "inactive") {
                $stat = 'warning';
            } else if ($user_status == "delete") {
                $stat = 'danger';
            }
        ?>
        <tr>
          <td><?php echo $idx++; ?></td>
          <td><a href="<?php echo WEB_ROOT; ?>views/?v=USER&ID=<?php echo $user_id; ?>"><?php echo strtoupper($user_name); ?></a></td>
          <td><?php echo $user_email; ?></td>
          <td><?php echo $user_phone; ?></td>
          
          <td><span class="label label-<?php echo $stat; ?>"><?php echo strtoupper($user_status); ?></span></td>
          <?php if ($utype == 'on') { ?>
          <td>
            <?php if ($user_status == "active") { ?>
            <a href="javascript:status('<?php echo $user_id; ?>', 'inactive');">Inactive</a>&nbsp;/&nbsp;
            <a href="javascript:status('<?php echo $user_id; ?>', 'lock');">Account Lock</a>&nbsp;/&nbsp;
            <a href="javascript:status('<?php echo $user_id; ?>', 'delete');">Delete</a>
            <?php } else { ?>
            <a href="javascript:status('<?php echo $user_id; ?>', 'active');">Active</a>
            <?php } ?>
          </td>
          <?php } ?>
        </tr>
        <?php } ?>
      </table>
    </div>
    <div class="box-footer clearfix">
      <?php if ($type == 'admin') { ?>
      <button type="button" class="btn btn-info" onclick="javascript:createUserForm();"><i class="fa fa-user-plus" aria-hidden="true"></i>&nbsp;Create a new User</button>
      <?php } ?>
      <?php echo generatePagination('tbl_users'); ?>
    </div>
  </div>
</div>

<script language="javascript">
function createUserForm() {
    window.location.href = '<?php echo WEB_ROOT; ?>views/?v=CREATE';
}
function status(userId, status) {
    if (confirm('Are you sure you want to ' + status + ' it?')) {
        window.location.href = '<?php echo WEB_ROOT; ?>views/process.php?cmd=change&action=' + status + '&userId=' + userId;
    }
}
</script>

?>
