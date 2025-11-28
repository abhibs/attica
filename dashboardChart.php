<?php
error_reporting(E_ERROR | E_PARSE);
session_start();
$type = $_SESSION['usertype'];
if ($type == 'Master') {
  include("header.php");
  include("menumaster.php");
} else {
  include("logout.php");
}
include("dbConnection.php");
date_default_timezone_set("Asia/Kolkata");
?>
<style>
  #wrapper { background:#f5f5f5; }
  #wrapper h3 { text-transform:uppercase; font-weight:600; font-size:20px; color:#123C69; }
  #wrapper .panel-body {
    box-shadow:0 .5rem 1rem rgba(0,0,0,.15);
    border-radius:7px; background-color:#ffffff; padding:20px; margin-bottom:20px;
  }
  .form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control { background-color:#fffafa; }
  .text-success { color:#123C69; text-transform:uppercase; font-weight:bold; font-size:12px; }
  .btn-success {
    display:inline-block; padding:0.7em 1.4em; margin:0 0.3em 0.3em 0; border-radius:0.15em; box-sizing:border-box;
    text-decoration:none; font-size:10px; font-family:'Roboto',sans-serif; text-transform:uppercase;
    color:#fffafa; background-color:#123C69; box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17); text-align:center; position:relative;
  }
  .table-bordered { margin-bottom:5px; }
  #month-ID > button:active { background-color:#34495E; }
  input[type="radio"] { display:none; }
  input[type="radio"]:checked + label { color:#123C69; font-size:20px; transition:.3s ease-out; }
</style>

<!-- notranslate wrapper helps suppress Chrome's translate prompt -->
<div id="wrapper" class="notranslate">
  <div class="content row">
    <div class="col-lg-12">
      <div class="hpanel hblue">
        <div class="panel-body">
          <div class="panel-header">
            <div class="col-lg-2"><h3>Graph</h3></div>
            <div class="col-lg-3">
              <select class="form-control m-b" id="selType">
                <option value="All Branches" selected="true">All Branches</option>
                <option value="Bangalore">Bangalore</option>
                <option value="Karnataka">Karnataka</option>
                <option value="Chennai">Chennai</option>
                <option value="Tamilnadu">Tamilnadu</option>
                <option value="Hyderabad">Hyderabad</option>
                <option value="AP-TS">AP-TS</option>
                <option value="Pondicherry">Pondicherry</option>
                <option value="" disabled="disabled"><br></option>
                <?php
                  $branchData = mysqli_query($con, "SELECT branchId,branchName FROM branch WHERE Status=1 ORDER BY branchName");
                  while ($branchList = mysqli_fetch_array($branchData)) { ?>
                    <option value="<?php echo $branchList['branchId']; ?>"><?php echo $branchList['branchName']; ?></option>
                <?php } ?>
              </select>
            </div>
            <div class="col-lg-3" style="padding-left:30px;text-align:center">
              <input type="radio" name="monthlyORdaily" id="monthly_ID" value="MONTH-WISE" checked>
              <label for="monthly_ID">MONTHLY</label>
              <input type="radio" name="monthlyORdaily" id="daily_ID" value="DAY-WISE">
              <label for="daily_ID">DAILY</label>
            </div>
            <div class="col-lg-3">
              <div class="input-group">
                <input type="range" min="1" max="400" value="30" class="form-control" id="selDMnum">
                <span class="input-group-addon text-success" id="DMnumValue" style="border-radius:100px">30</span>
              </div>
            </div>
            <div class="col-sm-1">
              <button class="btn btn-success" id="go" style="margin-top:1px"><span class="fa fa-play"></span></button>
            </div>
          </div>
        </div>

        <div class="panel-body" style="padding:0px">
          <div id="chartDiv" style="padding-top:10px">
            <canvas id="singleBarOptions" height="120"></canvas>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Load libs FIRST: jQuery -> Chart.js -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="vendor/chartjs/Chart36.min.js"></script>

  <!-- Your code AFTER libs -->
  <script type="text/javascript">
    $(function () {
      var myChart;

      // initial render
      barChartGrossWMonthly('All Branches', 30);

      // live range bubble
      $(document).on('input change', '#selDMnum', function() {
        $('#DMnumValue').text($(this).val());
      });

      // GO button
      $('#go').on('click', function() {
        const branchId = $('#selType').val();
        const mod = $('input[name="monthlyORdaily"]:checked').val();
        const modNum = $('#selDMnum').val();
        if (mod === 'MONTH-WISE') {
          barChartGrossWMonthly(branchId, modNum);
        } else {
          barChartGrossWDaily(branchId, modNum);
        }
      });

      // branch change
      $('#selType').on('change', function(e) {
        const branchId = e.target.value;
        const mod = $('input[name="monthlyORdaily"]:checked').val();
        const modNum = $('#selDMnum').val();
        if (mod === 'MONTH-WISE') {
          barChartGrossWMonthly(branchId, modNum);
        } else {
          barChartGrossWDaily(branchId, modNum);
        }
      });

      function barChartGrossWDaily(branchId, days) {
        $.ajax({
          url: "chartData.php",
          type: "POST",
          data: { type: 'Daily', branchId: branchId, days: days },
          dataType: 'JSON'
        }).done(function(e){
          var labels = [], grossW = [], rate = [], enquiryGwt = [];
          for (var i=0; i<e.length; i++) {
            labels.push(e[i][0]);    // date
            grossW.push(e[i][1]);    // trans grossW
            rate.push(e[i][2]);      // placeholder rate
            enquiryGwt.push(e[i][3]); // walkin enquiry grossW
          }

          const data = {
            labels: labels,
            datasets: [
              {
                label: 'GrossW',
                data: grossW,
                borderColor: 'rgba(18, 60, 105)',
                backgroundColor: 'rgba(18, 60, 105)',
                borderWidth: 0.90,
                pointStyle: 'rectRot',
                radius: 2,
                yAxisID: 'y'
              },
              {
                label: 'Rate',
                data: rate,
                borderColor: 'rgba(153, 0, 0)',
                backgroundColor: 'rgba(153, 0, 0)',
                borderWidth: 0.80,
                pointStyle: 'star',
                radius: 2,
                yAxisID: 'y1',
                borderDash: [4, 4]
              },
              {
                label: 'Enquiry GrossW',
                data: enquiryGwt,
                borderColor: 'rgba(0, 102, 204)',
                backgroundColor: 'rgba(0, 102, 204)',
                borderWidth: 0.90,
                pointStyle: 'circle',
                radius: 2,
                yAxisID: 'y'
              }
            ]
          };

          const config = {
            type: 'line',
            data: data,
            options: {
              responsive: true,
              interaction: { mode: 'index', intersect: false },
              stacked: false,
              plugins: { legend: { position: 'top' } },
              scales: {
                x: {
                  title: { display: true, text: 'DATE', color: '#000', font: { size: 12, weight: 'bold' } }
                },
                y: {
                  type: 'linear', display: true, position: 'left',
                  title: { display: true, text: 'GROSS WEIGHT', color:'rgba(18,60,105)', font:{ size:12, weight:'bold' } }
                },
                y1: {
                  type: 'linear', display: true, position: 'right',
                  title: { display: true, text: 'GOLD RATE', color:'rgba(153,0,0)', font:{ size:12, weight:'bold' } },
                  grid: { drawOnChartArea: false }
                }
              }
            }
          };

          const ctx = document.getElementById("singleBarOptions").getContext("2d");
          if (myChart) myChart.destroy();
          myChart = new Chart(ctx, config);
        }).fail(function(){
          $('#chartDiv').html("<h4 style='text-align:center' class='text-success'><b>Oops, something went wrong :)</b></h4>");
        });
      }

      function barChartGrossWMonthly(branchId, months) {
        $.ajax({
          url: "chartData.php",
          type: "POST",
          data: { type: 'Monthly', branchId: branchId, months: months },
          dataType: 'JSON'
        }).done(function(e){
          var labels = [], grossW = [], enquiryGwt = [];
          for (var i=0; i<e.length; i++) {
            labels.push(e[i][0]);     // Mon-YYYY
            grossW.push(e[i][1]);     // trans grossW
            // NEW: the endpoint also returns enquiry_gwt for monthly (we added earlier)
            // If your chartData.php returns only 2 columns, keep this guarded:
            enquiryGwt.push(e[i][2] !== undefined ? e[i][2] : 0);
          }

          const data = {
            labels: labels,
            datasets: [
              {
                label: 'GrossW',
                data: grossW,
                backgroundColor: 'rgba(153, 0, 0)',
                borderColor: 'rgba(153, 0, 0)',
                borderWidth: 0.80,
                pointStyle: 'rect',
                radius: 3,
                type: 'line'
              },
              {
                label: 'Enquiry GrossW',
                data: enquiryGwt,
                backgroundColor: 'rgba(0, 102, 204)',
                borderColor: 'rgba(0, 102, 204)',
                borderWidth: 0.80,
                pointStyle: 'circle',
                radius: 3,
                type: 'line'
              }
            ]
          };

          const config = {
            type: 'line',
            data: data,
            options: {
              responsive: true,
              plugins: { legend: { position: 'top' } },
              scales: {
                x: {
                  title: { display: true, text: 'MONTH-YEAR', color:'rgba(18,60,105)', font:{ size:12, weight:'bold' } }
                },
                y: {
                  title: { display: true, text: 'GROSS WEIGHT', color:'rgba(153,0,0)', font:{ size:12, weight:'bold' } }
                }
              }
            }
          };

          const ctx = document.getElementById("singleBarOptions").getContext("2d");
          if (myChart) myChart.destroy();
          myChart = new Chart(ctx, config);
        }).fail(function(){
          $('#chartDiv').html("<h4 style='text-align:center' class='text-success'><b>Oops, something went wrong :)</b></h4>");
        });
      }

    });
  </script>

  <?php include("footer.php"); ?>
</div>

