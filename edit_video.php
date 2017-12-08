<!DOCTYPE html>
<html lang="en">
<head>

    <?php



    require 'config.php';
    require_once 'DB.php';


    $database = DB::getInstance();




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
            <?php  if(isset($_GET['v_id'])){ if($_GET["v_id"]) {

                $v_id = $_GET["v_id"];

                $database->query("SELECT v_id,v_title,v_src FROM videos WHERE v_id = ".$v_id);

                $video = $database->results();

                if(count($video) > 0)
                {
                    $video = $video[0];

                    ?>
                    <div class="row">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="x_panel">
                                <div class="row x_content">
                                    <h1>Edit Video</h1><p><a href="video_list.php">back</a></p>

                                    <div id="res" style="color: green">
                                    </div>

                                    <div class="row">
                                        <div>
                                            title: <input type="text" class="form-control" id="title" value="<? echo $video->v_title ?>"/>
                                        </div>
                                        <div>
                                            videoUrl: <input type="text" class="form-control" id="videoUrl" value="<? echo $video->v_src ?>"/>
                                        </div>
                                        <div>
                                            <button class="btn btn-primary" id="edit_video">Edit Video</button>
                                            <a class="btn btn-danger" href="new_features.php?delete_video_id=<? echo $video->v_id ?>">Delete Video</a>
                                        </div>
                                    </div>

                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                    </div>
                <?php } }} ?>
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
    $("#edit_video").on("click",function() {
        title = $("#title").val();
        videoUrl = $("#videoUrl").val();


        if(($.trim(title) != '') && ($.trim(videoUrl) != ''))
        {
            $.post("<?php echo constant("ROOT_URL"); ?>"+"new_features.php",{"edit_v_title":title,"edit_v_src":videoUrl,"v_id":<? echo $_GET["v_id"] ?>},function (data) {
                $("#res").html(data);
            });
        }
        else {
            alert("empty field");
        }
    });
</script>





</body>
</html>
