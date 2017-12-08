<!DOCTYPE html>
<html lang="en">
<head>

    <?php

    require 'config.php';

    ?>

    <?php if(!@include('templates/header.php')) throw new Exception("Failed to include 'header'"); ?>
    <title>Nezz</title>
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
                        <div class="row x_content">
                            <h1>Add Video</h1>

                            <div id="res" style="color: green">
                            </div>

                            <div class="row">
                                <div>
                                    title: <input type="text" class="form-control" id="title"/>
                                </div>
                                <div>
                                    videoUrl: <input type="text" class="form-control" id="videoUrl"/>
                                </div>
                                <div>
                                    <button class="btn btn-primary" id="add_video">Add Video</button>
                                </div>
                            </div>

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
    $("#add_video").on("click",function() {
        title = $("#title").val();
        videourl = $("#videoUrl").val();

        if(($.trim(title) != '') && ($.trim(videourl) != ''))
        {
            $.post("<?php echo constant("ROOT_URL"); ?>"+"new_features.php",{"v_title":title,"videourl":videourl},function (data) {
                $("#res").html(data);
                $("input[type=text]").val('');
            });
        }
        else {
            alert("empty field");
        }
    });
</script>





</body>
</html>
