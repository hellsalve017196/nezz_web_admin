<?php
// Start Session
session_start();
date_default_timezone_set('UTC');

// Include Config
require('config.php');

// Include if secured page
include('templates/secure.php');

require('classes/Database.php');
require('classes/Messages.php');

$database = new Database;

$post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
$get = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);

$pageTitle = "Sales";

$list = "";

switch (strtoupper($get["a"])) {      
  default:  
    break;
} 
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <?php if(!@include('templates/header.php')) throw new Exception("Failed to include 'header'"); ?>
    <title>Subway Talent | <?php echo strtoupper($pageTitle); ?></title>
  </head>
	<body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <div class="col-md-3 left_col">
					<?php if(!@include('templates/sidebar.php')) throw new Exception("Failed to include 'sidebar'"); ?>
        </div>
        <?php if(!@include('templates/topbar.php')) throw new Exception("Failed to include 'topbar'"); ?>
        <div class="right_col" role="main">
          <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
              <div class="x_panel">
                <div class="row x_title">
                  <div class="col-md-6">
                    <h2><i class="fa fa-calendar"></i>&nbsp;<?php echo $pageTitle; ?>&nbsp;Report</h2>
                  </div>                  
                  <div class="col-md-2" style="text-align: right;">                    
                    <select id="type" name="type" required="required" class="form-control col-md-2 col-xs-12">
                      <option value="Paid">Paid</option>
                      <option value="Pending">Pending</option>
                    </select>                    
                  </div>               
                  <div class="col-md-4" style="text-align: right;">                    
                    <input type="text" id="daterange" name="daterange" required="required" class="form-control col-md-4 col-xs-12" />
                  </div>
                </div>
                <div class="row x_content">
                  <div id="echart_line-sales" style="height:450px;"></div>
                </div>
                <div class="clearfix"></div>
              </div>
            </div>
          </div>
          <br />
        </div>
        <footer>
          <div class="pull-right">
            <i class="glyphicon glyphicon-cog"></i> Subway Talent Administration. &copy;2017 All Rights Reserved. Privacy and Terms.
          </div>
          <div class="clearfix"></div>
        </footer>
      </div>
    </div>
	<?php if(!@include('templates/footer.php')) throw new Exception("Failed to include 'footer'"); ?>    
  <script type="text/javascript">
  $(document).ready(function() {
    var start = moment().subtract(29, 'days');
    var end = moment();

    function cb(start, end) {
        $('#daterange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    }

    $('#daterange').daterangepicker({
        startDate: start,
        endDate: end,
        ranges: {
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, cb);

    cb(start, end);
  });
  $("#type").change(function() {
    loadChart();
  });
  $("#daterange").change(function() {
    loadChart();
  });
  function loadChart() {
    var theme = { 
      color: ['#26B99A', '#34495E', '#BDC3C7', '#3498DB', '#9B59B6', '#8abb6f', '#759c6a', '#bfd3b7'],
      title: {
        itemGap: 8,
        textStyle: {
          fontWeight: 'normal',
          color: '#408829'
        }
      },
      dataRange: {
        color: ['#1f610a', '#97b58d']
      },
      toolbox: {
        color: ['#408829', '#408829', '#408829', '#408829']
      },
      tooltip: {
        backgroundColor: 'rgba(0,0,0,0.5)',
        axisPointer: {
          type: 'line',
          lineStyle: {
            color: '#408829',
            type: 'dashed'
          },
          crossStyle: {
            color: '#408829'
          },
          shadowStyle: {
            color: 'rgba(200,200,200,0.3)'
          }
        }
      },
      dataZoom: {
        dataBackgroundColor: '#eee',
        fillerColor: 'rgba(64,136,41,0.2)',
        handleColor: '#408829'
      },
      grid: {
        borderWidth: 1
      },
      categoryAxis: {
        axisLine: {
          lineStyle: {
            color: '#408829'
          }
        },
        splitLine: {
          lineStyle: {
            color: ['#eee']
          }
        }
      },
      valueAxis: {
        axisLine: {
          lineStyle: {
            color: '#408829'
          }
        },
        splitArea: {
          show: true,
          areaStyle: {
            color: ['rgba(250,250,250,0.1)', 'rgba(200,200,200,0.1)']
          }
        },
        splitLine: {
          lineStyle: {
            color: ['#eee']
          }
        }
      },
      timeline: {
        lineStyle: {
          color: '#408829'
        },
        controlStyle: {
          normal: {color: '#408829'},
          emphasis: {color: '#408829'}
        }
      },
      k: {
        itemStyle: {
          normal: {
            color: '#68a54a',
            color0: '#a9cba2',
            lineStyle: {
              width: 1,
              color: '#408829',
              color0: '#86b379'
            }
          }
        }
      },
      map: {
        itemStyle: {
          normal: {
            areaStyle: {
              color: '#ddd'
            },
            label: {
              textStyle: {
                color: '#c12e34'
              }
            }
          },
          emphasis: {
            areaStyle: {
              color: '#99d2dd'
            },
            label: {
              textStyle: {
                color: '#c12e34'
              }
            }
          }
        }
      },
      force: {
        itemStyle: {
          normal: {
            linkStyle: {
              strokeColor: '#408829'
            }
          }
        }
      },
      chord: {
        padding: 4,
        itemStyle: {
          normal: {
            lineStyle: {
              width: 1,
              color: 'rgba(128, 128, 128, 0.5)'
            },
            chordStyle: {
              lineStyle: {
                width: 1,
                color: 'rgba(128, 128, 128, 0.5)'
              }
            }
          },
          emphasis: {
            lineStyle: {
              width: 1,
              color: 'rgba(128, 128, 128, 0.5)'
            },
            chordStyle: {
              lineStyle: {
                width: 1,
                color: 'rgba(128, 128, 128, 0.5)'
              }
            }
          }
        }
      },
      gauge: {
        startAngle: 225,
        endAngle: -45,
        axisLine: {
          show: true,
          lineStyle: {
            color: [[0.2, '#86b379'], [0.8, '#68a54a'], [1, '#408829']],
            width: 8
          }
        },
        axisTick: {
          splitNumber: 10,
          length: 12,
          lineStyle: {
            color: 'auto'
          }
        },
        axisLabel: {
          textStyle: {
            color: 'auto'
          }
        },
        splitLine: {
          length: 18,
          lineStyle: {
            color: 'auto'
          }
        },
        pointer: {
          length: '90%',
          color: 'auto'
        },
        title: {
          textStyle: {
            color: '#333'
          }
        },
        detail: {
          textStyle: {
            color: 'auto'
          }
        }
      },
      textStyle: {
        fontFamily: 'Arial, Verdana, sans-serif'
      }
    };

    if ($('#echart_line-sales ').length) { 
        
      var echartLine = echarts.init(document.getElementById('echart_line-sales'), theme);

          $.ajax({
          url: "services/sales.php",
          method: "POST",
          data: {
            "type": $("#type").val(), 
            "range": $("#daterange").val()
          },
          success: function(data) {          
            var response = JSON.parse(data);

            console.log(response);

            if (response.status == "success") {              
              echartLine.setOption({
                title: {
                  text: 'Payment Status: ' + $("#type option:selected").text() + ', Range: ' + $("#daterange").val(),
                  subtext: 'Hover over the chart to view more details'
                },
                tooltip: {
                  trigger: 'axis'
                },
                legend: {
                  x: 220,
                  y: 40,
                  data: response.data.legend
                },
                toolbox: {
                  show: true,
                  feature: {
                  magicType: {
                    show: true,
                    title: {
                    line: 'Line',
                    bar: 'Bar',
                    stack: 'Stack',
                    tiled: 'Tiled'
                    },
                    type: ['line', 'bar', 'stack', 'tiled']
                  },
                  restore: {
                    show: true,
                    title: "Restore"
                  },
                  saveAsImage: {
                    show: true,
                    title: "Save Image"
                  }
                  }
                },
                calculable: true,
                xAxis: [{
                  type: 'category',
                  boundaryGap: false,
                  data: response.data.xAxisData
                }],
                yAxis: [{
                  type: 'value'
                }],
                series: response.data.data
                });
            }
            else alert("ERR: " + response.message);
          },
          error: function(data) {
            console.log(data);
          }
        });      
      }
    }    
  </script>
	</body>
</html>
