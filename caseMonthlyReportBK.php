<?php
session_start();
$type = $_SESSION['usertype'];
if ($type == 'Master') {
    include("header.php");
    include("menumaster.php");
} else {
    include("logout.php");
}
include("dbConnection.php");

/* ----------------- Calendar generator (layout preserved) ----------------- */
function generate_calendar($year, $month, $days = array(), $day_name_length = 3, $month_href = NULL, $first_day = 0, $pn = array(), $data)
{
    $first_of_month = gmmktime(0, 0, 0, $month, 1, $year);

    $day_names = array();
    for ($n = 0, $t = (3 + $first_day) * 86400; $n < 7; $n++, $t += 86400)
        $day_names[$n] = ucfirst(gmstrftime('%A', $t));

    @list($month, $year, $month_name, $weekday) = explode(',', gmstrftime('%m, %Y, %B, %w', $first_of_month));
    $weekday = ($weekday + 7 - $first_day) % 7;
    $title   = htmlentities(ucfirst($month_name)) . ' ' . $year;

    // caption prev/next placeholders (we handle nav outside)
    @list($p, $pl) = key($pn);
    @list($n, $nl) = key($pn);
    if ($p) $p = '<span class="calendar-prev">' . ($pl ? '<a href="' . htmlspecialchars($pl) . '">' . $p . '</a>' : $p) . '</span>&nbsp;';
    if ($n) $n = '&nbsp;<span class="calendar-next">' . ($nl ? '<a href="' . htmlspecialchars($nl) . '">' . $n . '</a>' : $n) . '</span>';

    $calendar = "<table class='emp-calendar-table table table-bordered'>\n" .
        '<caption class="calendar-month">' .
        $p . ($month_href ? '<a href="' . htmlspecialchars($month_href) . '">' . $title . '</a>' : $title) . $n .
        "</caption>\n<tr style='background-color:#eaeaea;'>";

    if ($day_name_length) {
        foreach ($day_names as $d)
            $calendar .= '<th class="text-success" abbr="' . htmlentities($d) . '">' .
                         htmlentities($day_name_length < 4 ? substr($d, 0, $day_name_length) : $d) .
                         '</th>';
        $calendar .= "</tr>\n<tr>";
    }

    if ($weekday > 0) {
        for ($i = 0; $i < $weekday; $i++) {
            $calendar .= '<td>&nbsp;</td>';
        }
    }

    for ($day = 1, $days_in_month = gmdate('t', $first_of_month); $day <= $days_in_month; $day++, $weekday++) {
        if ($weekday == 7) {
            $weekday  = 0;
            $calendar .= "</tr>\n<tr>";
        }

        if (isset($days[$day]) and is_array($days[$day])) {
            @list($link, $classes, $content) = $days[$day];
            if (is_null($content)) $content = $day;
            $calendar .= '<td' . ($classes ? ' class="' . htmlspecialchars($classes) . '">' : '>') .
                         ($link ? '<a href="' . htmlspecialchars($link) . '">' . $content . '</a>' : $content) . '</td>';
        } else {
            $filtered = array_filter($data, function ($v) use ($day) {
                return (int)($v['day']) == $day;
            });
            $maped = implode('', array_map("getData", $filtered));
            $calendar .= "<td>$day" . $maped . "</td>";
        }
    }
    if ($weekday != 7) $calendar .= '<td id="emptydays" colspan="' . (7 - $weekday) . '">&nbsp;</td>';

    return $calendar . "</tr>\n</table>";
}

/* ----------------- Tag renderer with Status color logic ----------------- */
function getData($v)
{
    $name      = htmlspecialchars($v['name']     ?? '', ENT_QUOTES, 'UTF-8');
    $content   = htmlspecialchars($v['content']  ?? '', ENT_QUOTES, 'UTF-8');
    $remarks   = isset($v['remarks']) && $v['remarks'] !== null ? htmlspecialchars($v['remarks'], ENT_QUOTES, 'UTF-8') : '';
    $statusRaw = trim($v['status'] ?? '');
    $status    = ($statusRaw === 'Closed') ? 'Closed' : 'Open';

    $cls = '';
    try {
        $today    = new DateTime('today');
        $soon     = (clone $today)->modify('+7 days');
        $fullDate = isset($v['full_date']) ? new DateTime($v['full_date']) : null;

        if ($status === 'Closed') {
            $cls = 'tag-closed';
        } elseif ($fullDate instanceof DateTime) {
            if ($fullDate < $today) {
                $cls = 'tag-open-past';
            } elseif ($fullDate >= $today && $fullDate <= $soon) {
                $cls = 'tag-open-soon';
            }
        }
    } catch (Exception $e) { /* ignore */ }

    $html  = "<div class='col-sm-12 emp-tags {$cls}'>";
    $html .= "  <div class='col-sm-12'><strong>{$name}</strong> <span class='emp-status-pill'>{$status}</span></div>";
    $html .= "  <div class='col-sm-12'>{$content}</div>";
    if ($remarks !== '') {
        $html .= "  <div class='col-sm-12 emp-remarks'><strong>Remarks:</strong> {$remarks}</div>";
    }
    $html .= "</div>";
    return $html;
}

/* ----------------- Month selection, navigation & data ----------------- */
$selectedYm = isset($_GET['caseMonth']) && preg_match('/^\d{4}-\d{2}$/', $_GET['caseMonth'])
    ? $_GET['caseMonth']
    : date('Y-m');

try {
    $cur  = new DateTime($selectedYm . '-01');
} catch (Exception $e) {
    $cur  = new DateTime('first day of this month');
    $selectedYm = $cur->format('Y-m');
}
$prev = (clone $cur)->modify('-1 month');
$next = (clone $cur)->modify('+1 month');

$prevYm = $prev->format('Y-m');
$nextYm = $next->format('Y-m');

$time   = $cur->getTimestamp();
$year   = (int)$cur->format('Y');
$month  = (int)$cur->format('n');

$result = [];
$safeYm = mysqli_real_escape_string($con, $selectedYm);
$sql = mysqli_query($con, "SELECT
        id,
        DATE_FORMAT(date,'%d')        AS day,
        DATE_FORMAT(date,'%Y-%m-%d')  AS full_date,
        name,
        content,
        remarks,
        status
    FROM cases
    WHERE DATE_FORMAT(date,'%Y-%m') = '$safeYm'");
if ($sql) $result = mysqli_fetch_all($sql, MYSQLI_ASSOC);
?>
<style>
    #wrapper h3{ text-transform:uppercase; font-weight:600; font-size:18px; color:#123C69; }
    .text-success{ color:#123C69; text-transform:uppercase; font-weight:bold; font-size:12px; }
    .btn-primary{ background-color:#123C69; }
    .btn-success{
        display:inline-block; padding:0.7em 1.4em; margin:0 0.3em 0.3em 0; border-radius:0.15em; box-sizing:border-box;
        text-decoration:none; font-size:10px; font-family:'Roboto',sans-serif; text-transform:uppercase; color:#fffafa;
        background-color:#123C69; box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17); text-align:center; position:relative;
    }
    .fa_Icon{ color:#ffd700; }
    .fa_icon{ color:#990000; }
    .hpanel .panel-body{ box-shadow:10px 15px 15px #999; border-radius:3px; padding:20px; background-color:#f5f5f5; }
    .emp-calendar-table td{ width:14%; height:150px; color:#6A6C6F; font-weight:600; vertical-align:top; }
    .calendar-month{ font-size:15px; font-weight:600; text-align:center; color:#990000; margin-bottom:5px; }
    .emp-tags{ color:#ffffff; background-color:#34495e; margin:3px; border-radius:3px; font-size:10px; padding:2px; }
    .emp-remarks{ margin-top:2px; font-size:10px; opacity:0.95; }
    .emp-tags:hover{ transform:scale(2); transition:0.5s ease; z-index:1; }
    .emp-status-pill{ display:inline-block; margin-top:2px; padding:0 6px; border-radius:10px; font-size:9px; font-weight:700; background:#fff; color:#333; }

    /* tag colors */
    .emp-tags.tag-closed{ background-color:#2e7d32; }
    .emp-tags.tag-open-past{ background-color:#b71c1c; }
    .emp-tags.tag-open-soon{ background-color:#b08b00; }

    /* Legend + Nav */
    .case-toolbar{ display:flex; flex-wrap:wrap; align-items:flex-end; gap:10px; margin:6px 0 12px 0; }
    .case-legend{ display:flex; flex-wrap:wrap; gap:18px; align-items:center; font-size:12px; color:#333; }
    .case-legend .legend-item{ display:flex; align-items:center; gap:6px; }
    .case-legend .legend-swatch{ display:inline-block; width:12px; height:12px; border-radius:2px; border:1px solid rgba(0,0,0,0.08); }
    .toolbar-spacer{ flex:1 1 auto; } /* push legend left if space tight */
    .panel-body{ overflow:visible; } /* ensure legend/buttons are never clipped */
</style>

<div id="wrapper">
  <div class="row content">
    <div class="col-lg-12">
      <div class="hpanel">
        <div class="panel-heading">
          <div class="row" style="margin:0;">
            <div class="col-lg-6">
              <h3 style="margin-top:30px"><i class="fa_icon fa fa-address-book-o"></i> Monthly Case Report</h3>
            </div>
          </div>
        </div>

        <div class="panel-body">
          <!-- Toolbar: Prev / Month / Next + Legend -->
          <div class="case-toolbar">
            <a class="btn btn-default" href="?caseMonth=<?php echo htmlspecialchars($prevYm,ENT_QUOTES,'UTF-8'); ?>" title="Previous Month">&laquo; Prev</a>

            <form action="" method="GET" style="display:flex; gap:10px; margin:0;">
              <div>
                <label class="text-success" style="display:block; margin:0 0 4px;">Month</label>
                <input type="month" class="form-control" name="caseMonth"
                       value="<?php echo htmlspecialchars($selectedYm, ENT_QUOTES, 'UTF-8'); ?>" required>
              </div>
              <div style="align-self:flex-end;">
                <button type="submit" class="btn btn-success" name="caseReport" value="1">
                  <span class="fa_Icon fa fa-search"></span> Go
                </button>
              </div>
            </form>

            <a class="btn btn-default" href="?caseMonth=<?php echo htmlspecialchars($nextYm,ENT_QUOTES,'UTF-8'); ?>" title="Next Month">Next &raquo;</a>

            <span class="toolbar-spacer"></span>

            <div class="case-legend">
              <span class="legend-item"><span class="legend-swatch" style="background:#b71c1c;"></span> Open &amp; Past Due</span>
              <span class="legend-item"><span class="legend-swatch" style="background:#b08b00;"></span> Open &amp; Due in 7 Days</span>
              <span class="legend-item"><span class="legend-swatch" style="background:#2e7d32;"></span> Closed</span>
            </div>
          </div>

          <?php
            echo generate_calendar($year, $month, array(), 3, null, 0, array(), $result);
          ?>
        </div>
      </div>
    </div>
  </div>
  <?php include("footer.php"); ?>
</div>

<!-- Optional keyboard nav -->
<script>
document.addEventListener('keydown', function(e){
  if (e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA')) return;
  if (e.key === 'ArrowLeft')  window.location.href = "?caseMonth=<?php echo $prevYm; ?>";
  if (e.key === 'ArrowRight') window.location.href = "?caseMonth=<?php echo $nextYm; ?>";
});
</script>

