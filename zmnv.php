<?php
session_start();
$type = $_SESSION['usertype'];

if ($type === 'Master') {
    include("header.php");
    include("menumaster.php");
} else if ($type === 'VM-AD') {
    include("header.php");
    include("menuvmadd.php");
}
else if($type == 'VM-WAIT') {
    include("header.php");
    include("menuvmWait.php");
}
else if ($type === 'SundayUser') {
    include("header.php");
    include("menuSundayUser.php");
} else if ($type === 'Zonal') {
    include("header.php");
    include("menuZonal.php");
} else if ($type === 'SubZonal') {
    include("header.php");
    include("menuSubZonal.php");
} else if ($type === 'ZonalMaster') {
    include("header.php");
    include("menuzonalMaster.php");
} else {
    include("logout.php");
}

include("dbConnection.php");

// Handle final save
if (isset($_POST['uploadvm'])) {
    $agentId  = $_POST['agentId'];
    $branches = trim($_POST['branches'], ", ");
    $branchArray = $branches === ''
        ? []
        : array_filter(array_map('trim', explode(',', $branches)), fn($b)=> $b!=='');
    // remove from others
    foreach ($branchArray as $b) {
        $rs = mysqli_query($con,
            "SELECT agentId, branch
               FROM vmagent
              WHERE agentId!='$agentId'
                AND FIND_IN_SET('$b',branch)"
        );
        while ($row = mysqli_fetch_assoc($rs)) {
            $other = $row['agentId'];
            $their = array_filter(array_map('trim', explode(',', $row['branch'])), fn($x)=> $x!==$b);
            mysqli_query($con,
                "UPDATE vmagent
                    SET branch='".implode(',', $their)."'
                  WHERE agentId='$other'"
            );
        }
    }
    // assign to this agent
    mysqli_query($con,
        "UPDATE vmagent SET branch='$branches' WHERE agentId='$agentId'"
    );
    echo "<script>alert('Agent $agentId now has branches: $branches');</script>";
    // clear session so we go back to step1 next time
    unset($_SESSION['selectedAgent'], $_SESSION['msn']);
}

// Handle Next → select VM
if (isset($_POST['updatevm'])) {
    $_SESSION['selectedAgent'] = $_POST['vmid'];
    $_SESSION['msn']           = $_POST['branchA'];
} else {
    // on a fresh load, clear both
    unset($_SESSION['selectedAgent'], $_SESSION['msn']);
}

// Fetch current branches if we have an agent selected
$currentBranches = '';
if (!empty($_SESSION['selectedAgent'])) {
    $aid = mysqli_real_escape_string($con, $_SESSION['selectedAgent']);
    $r = mysqli_fetch_assoc(mysqli_query($con,
        "SELECT branch FROM vmagent WHERE agentId='$aid' LIMIT 1"
    ));
    $currentBranches = $r['branch'] ?? '';
}
?>
<link rel="stylesheet" href="styles/amsify.suggestags.css">
<script src="https://code.jquery.com/jquery-3.2.1.min.js"
 integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
 crossorigin="anonymous"></script>
<script src="scripts/jquery.amsify.suggestags.js"></script>
<script>
function getvm(val){
  $.post('zvmn.php',{state_id2:val},d=>$('#getvm2').html(d));
}
function get_branches(val){
  $.ajax({
    url:'zvmn.php',type:'POST',
    data:{state_id:val},dataType:'json'
  }).done(r=>{
    $('#br1').html(r.branch_a);
    $('#br2').html(r.branch_b);
    $('#br3').html(r.branch_c);
    $('#br4').html(r.branch_d);
    $('#br5').html(r.branch_e);
    $('#vmnameaccess').html(r.vmnameaccess);
  });
}
</script>

<div id="wrapper">
  <div class="content">
    <div class="row-content">
      <div class="col-lg-12">

        <?php if (empty($_SESSION['selectedAgent'])): ?>
          <!-- STEP 1: Choose VM -->
          <div class="hpanel">
            <div class="panel-heading">
              <h3 class="text-success">
                <i class="fa fa-dashboard" style="color:#990000"></i> Branch Access
              </h3>
            </div>
            <div class="panel-body">
              <div class="col-sm-6">
                <label class="text-success">Language</label>
                <div class="input-group">
                  <span class="input-group-addon">
                    <i class="fa fa-sort-alpha-asc" style="color:#990000"></i>
                  </span>
                  <select class="form-control" onchange="getvm(this.value)">
                    <option disabled selected value="">Select Language</option>
                    <?php
                    $ql = mysqli_query($con,"SELECT id,language FROM language");
                    while($r=mysqli_fetch_assoc($ql)){
                      echo "<option value=\"{$r['id']}\">{$r['language']}</option>";
                    }
                    ?>
                  </select>
                </div>
              </div>
              <form method="post">
                <div class="col-sm-6">
                  <label class="text-success">VM Name</label>
                  <div class="input-group">
                    <span class="input-group-addon">
                      <i class="fa fa-sort-alpha-asc" style="color:#990000"></i>
                    </span>
                    <select name="vmid" id="getvm2" class="form-control" onchange="get_branches(this.value)">
                      <option disabled selected value="">Select VM</option>
                    </select>
                  </div>
                </div>
                <div class="row" style="margin-left:10px;">
                  <div class="col" id="br1">
                    <input type="hidden" name="branchA" id="branch_a" class="form-control">
                  </div>
                </div>
                <div style="margin:30px; float:left">
                  <button type="submit" name="updatevm" class="btn btn-success">Next</button>
                </div>
              </form>
            </div>
          </div>

        <?php else: ?>
          <!-- STEP 2: Assign Branches (always visible) -->
          <div class="hpanel">
            <div class="panel-heading">
              <a href="" class="btn btn-success" style="float:right">
                <i class="fa fa-arrow-left" style="color:#ffcf40"></i> Change VM
              </a>
              <h3 class="text-success">
                <i class="fa fa-dashboard" style="color:#990000"></i> Access Branches
              </h3>
            </div>
            <div class="panel-body">
              <form method="post">
                <?php
                  $aid = mysqli_real_escape_string($con, $_SESSION['selectedAgent']);
                  $emp = mysqli_fetch_assoc(mysqli_query(
                          $con,
                          "SELECT name FROM employee WHERE empId='$aid' LIMIT 1"
                        ));
                ?>
                <div class="row" style="margin:15px;">
                  <div class="col-lg-6">
                    <label class="text-success">VM Name</label>
                    <input type="text" readonly class="form-control" value="<?=htmlspecialchars($emp['name'])?>">
                  </div>
                </div>
                <input type="hidden" name="agentId" value="<?=htmlspecialchars($aid)?>">
                <div class="row" style="margin-left:10px;">
                  <div class="col-lg-12">
                    <label class="text-success">Branches Accessing</label>
                    <input type="text"
                           name="branches"
                           class="form-control"
                           value="<?=htmlspecialchars($currentBranches)?>">
                  </div>
                </div>
                <div style="margin:30px; float:left">
                  <button type="submit" name="uploadvm" class="btn btn-success">Save</button>
                </div>
              </form>
            </div>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </div>
</div>

<script>
$('input[name="branches"]').amsifySuggestags({
  type:'amsify',
  suggestions:[
    <?php
      $B = mysqli_query($con,"SELECT branchId,branchName FROM branch");
      while($b=mysqli_fetch_assoc($B)) {
        echo "{tag:'".addslashes($b['branchName'])."',value:'".addslashes($b['branchId'])."'},";
      }
    ?>
  ]
});
</script>

<div class="hidden">
  <?php include("footer.php"); ?>
</div>

