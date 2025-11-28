<?php
session_start();
error_reporting(E_ERROR | E_PARSE);

$type = $_SESSION['usertype'];
if ($type == 'Branch') {	
    include("header.php");
    include("menu.php");
} else {
    include("logout.php");
    exit;
}
include("dbConnection.php");

// Branch identifier from session (could be agpl001 / AGPL001 / Agpl001 etc.)
$username = $_SESSION['username'] ?? '';
// Normalized version for case-insensitive match
$branchKey = strtolower($username);
?>
<style>
    #wrapper h3 { text-transform: uppercase; font-weight: 600; font-size: 16px; color: #123C69; }
    .text-success { color: #123C69; text-transform: uppercase; font-weight: 600; }
    .btn-primary { background-color: #123C69; }
    .fa_Icon { color:#990000; }
    #wrapper .panel-body { box-shadow:10px 15px 15px #999; background-color:#f5f5f5; border-radius:3px; padding:15px; }
    .theadRow { text-transform: uppercase; background-color: #123C69 !important; color: #f2f2f2; font-size: 11px; }
    .thumb-img { max-width: 60px; max-height: 60px; border-radius: 3px; display:block; margin:0 auto 3px; }
</style>

<div id="wrapper">
    <div class="row content">

        <div class="col-lg-12">
            <div class="hpanel">
                <div class="card">
                    <div class="card-header">
                        <h3 class="font-light m-b-xs text-success">
                            <b><i class="fa_Icon fa fa-institution"></i> APPROVED RELEASE REQUESTS</b>
                        </h3>
                    </div>
                    <div class="card-body container-fluid" style="margin-top:24px;padding:0px;">
                        <div class="col-lg-12">
                            <div class="alert alert-info" style="margin-top:10px;">
                                Showing approved requests for Branch :
                                <b><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></b>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-body">
                    <div class="table-responsive">
                        <table id="example1" class="table table-hover table-bordered">
                            <thead>
                                <tr class="theadRow">
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>Location</th>
                                    <th>Metal</th>
                                    <th>Grams</th>
                                    <th>Branch</th>
                                    <th>Business Type</th>
                                    <th>Release Place</th>
                                    <th>Release Doc</th>
                                    <th>Gold Image</th>
                                    <th>Customer Id</th>
                                    <th>Approved At</th>
                                    <th>Approved By</th>
                                </tr>
                            </thead>
                            <tbody>
<?php
$i = 1;

// small helper to check image extensions
function isImageFile($path) {
    $ext = strtolower(pathinfo((string)$path, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg','jpeg','png','gif','webp','bmp']);
}

if ($branchKey !== '') {
    $sql = "SELECT id, name, contact_number, location, metal_type, grams, branch,
                   business_type, release_place, release_doc, gold_image, customer_id_doc,
                   approved_at, approved_by
            FROM release_requests
            WHERE status = 'Approved'
              AND LOWER(branch) = ?
            ORDER BY approved_at DESC, created_at DESC";

    if ($stmt = mysqli_prepare($con, $sql)) {
        // use the normalized lowercase key
        mysqli_stmt_bind_param($stmt, "s", $branchKey);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($res)) {
            $name         = htmlspecialchars($row['name'] ?? '', ENT_QUOTES, 'UTF-8');
            $contact      = htmlspecialchars($row['contact_number'] ?? '', ENT_QUOTES, 'UTF-8');
            $location     = htmlspecialchars($row['location'] ?? '', ENT_QUOTES, 'UTF-8');
            $metalType    = htmlspecialchars($row['metal_type'] ?? '', ENT_QUOTES, 'UTF-8');
            $grams        = htmlspecialchars($row['grams'] ?? '', ENT_QUOTES, 'UTF-8');
            $branch       = htmlspecialchars($row['branch'] ?? '', ENT_QUOTES, 'UTF-8');
            $businessType = htmlspecialchars($row['business_type'] ?? '', ENT_QUOTES, 'UTF-8');
            $releasePlace = htmlspecialchars($row['release_place'] ?? '', ENT_QUOTES, 'UTF-8');
            $releaseDoc   = $row['release_doc'] ?? '';
            $goldImage    = $row['gold_image'] ?? '';
            $customerId   = $row['customer_id_doc'] ?? '';
            $approvedAt   = htmlspecialchars($row['approved_at'] ?? '', ENT_QUOTES, 'UTF-8');
            $approvedBy   = htmlspecialchars($row['approved_by'] ?? '', ENT_QUOTES, 'UTF-8');

            echo "<tr>";
            echo "<td>{$i}</td>";
            echo "<td>{$name}</td>";
            echo "<td>{$contact}</td>";
            echo "<td>{$location}</td>";
            echo "<td>{$metalType}</td>";
            echo "<td>{$grams}</td>";
            echo "<td>{$branch}</td>";
            echo "<td>{$businessType}</td>";
            echo "<td>{$releasePlace}</td>";

            // Release Doc
            echo "<td style='text-align:center;'>";
            if (!empty($releaseDoc)) {
                $safePath = htmlspecialchars($releaseDoc, ENT_QUOTES, 'UTF-8');
                if (isImageFile($releaseDoc)) {
                    echo "<a href='{$safePath}' target='_blank' title='View Release Doc'>";
                    echo "<img src='{$safePath}' alt='Release Doc' class='thumb-img'>";
                    echo "</a>";
                } else {
                    echo "<a href='{$safePath}' target='_blank' class='btn btn-primary btn-xs'>View</a>";
                }
            } else {
                echo "-";
            }
            echo "</td>";

            // Gold Image
            echo "<td style='text-align:center;'>";
            if (!empty($goldImage)) {
                $safePath = htmlspecialchars($goldImage, ENT_QUOTES, 'UTF-8');
                echo "<a href='{$safePath}' target='_blank' title='View Gold Image'>";
                echo "<img src='{$safePath}' alt='Gold Image' class='thumb-img'>";
                echo "</a>";
            } else {
                echo "-";
            }
            echo "</td>";

            // Customer Id
            echo "<td style='text-align:center;'>";
            if (!empty($customerId)) {
                $safePath = htmlspecialchars($customerId, ENT_QUOTES, 'UTF-8');
                if (isImageFile($customerId)) {
                    echo "<a href='{$safePath}' target='_blank' title='View Customer Id'>";
                    echo "<img src='{$safePath}' alt='Customer Id' class='thumb-img'>";
                    echo "</a>";
                } else {
                    echo "<a href='{$safePath}' target='_blank' class='btn btn-primary btn-xs'>View</a>";
                }
            } else {
                echo "-";
            }
            echo "</td>";

            echo "<td>{$approvedAt}</td>";
            echo "<td>{$approvedBy}</td>";
            echo "</tr>";
            $i++;
        }

        mysqli_stmt_close($stmt);
    }
}
?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /.row -->
    <?php include("footer.php"); ?>
</div>

