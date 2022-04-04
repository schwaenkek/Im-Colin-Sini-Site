<?php
// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}
?>
<script>window.jQuery || document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.12.4/jquery.min.js">\x3C/script>')</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script>
    jQuery(function(){
        jQuery('.titletip').tooltip();
    });
</script>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css" integrity="sha384-DNOHZ68U8hZfKXOrtjWvjxusGo9WQnrNx2sqG0tfsghAvtVlRW3tvkXWZh58N9jp" crossorigin="anonymous">
<link href="https://fonts.googleapis.com/css?family=Heebo|Roboto" rel="stylesheet">
<div class="webarx-secure">
    <div class="webarx-navigation">
        <img src="<?php echo $this->plugin->url; ?>assets/images/logo.svg" alt="">
    </div>
    <div class="webarx-content">
        <div class="webarx-attacks-blocked">
            <i id="webarx-attacks-icon" class="fas fa-chart-bar"></i>
            <p id="webarx-attacks-number"><?php echo($countAttacks[0]->num); ?> attacks blocked</p>
        </div>
        <div style="background-color: <?php echo $backColor; ?>; color: <?php echo $textColor; ?> !important" class="webarx-message">
            <a href="<?php echo $link ?>">
                <p class="webarx-message-<?php echo $icon ?>"><?php echo $message; ?> <img src="<?php echo $this->plugin->url; ?>/assets/images/<?php echo $icon; ?>.svg" style="color: <?php echo $textColor; ?>; position: relative; top: 6px; left: 5px;" id="webarx-icon" class=""></p>
            </a>
        </div>
    </div>
    <div class="webarx-chart-panel">
        <div class="webarx-header">
            <p>Attacks Blocked (last 7 days) <a href="<?php echo admin_url('admin.php?page=webarx&tab=logs'); ?>">View logs</a></p>
        </div>
        <div style="position: relative; height: 200px; margin-bottom: 40px;">
            <canvas id="myChart" style="width: 100%" height="220px"></canvas>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.min.js"></script>
<script>
    var ctx = document.getElementById("myChart").getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['<?php echo implode("','", array_reverse($weekDates)); ?>'],
            datasets: [{
                data: [<?php echo implode(',', $attacks); ?>],
                borderColor: 'transparent',
                backgroundColor: 'rgba(178, 214, 117, 0.3)',
                hoverBackgroundColor: '#b2d675'
            }]
        },
        options: {
            responsive: true,
            maintainAspectratio: false,
            legend: {
                display: false
            },

            scales: {
                xAxes: [{
                    gridLines: {
                        display: false,
                    },
                    ticks: {
                        fontColor: '#acacac',
                        fontStyle: '300',
                        fontFamily: 'Roboto',
                    },
                    barThickness: 27
                }],
                yAxes: [{
                    gridLines: {
                        color: 'rgba(170, 189, 215, 0.1)',
                    },
                    ticks: {
                        fontColor: '#acacac',
                        fontStyle: '300',
                        fontFamily: 'Roboto',
                        min: 0,
                        beginAtZero: true,
                        callback: function(value, index, values) {
                            if(Math.floor(value) === value){
                                return value;
                            }
                        }
                    },
                }],
            },
            tooltips: {
                callbacks: {
                    title: function(tooltipItems, data) {
                        var tooltipItem = tooltipItems[0];
                        return tooltipItem.yLabel + ' attacks blocked';
                    },
                    labelColor: function(tooltipItem, chart) {
                        return null;
                    },
                    labelTextColor:function(tooltipItem, chart){
                        return '#543453';
                    },
                    label: function(tooltipItem, data) {
                        return null;
                    }
                }

            }
        }

    });
</script>