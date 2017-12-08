<!DOCTYPE html>
<html lang="en">
<head>

    <?php

    require 'config.php';
    require_once 'DB.php';

    $Database = DB::getInstance();

    $Database->query("SELECT v_id,v_title FROM videos ORDER BY v_id DESC");

    $videos = $Database->results();

    $Database->reseting();

    ?>

    <?php if(!@include('templates/header.php')) throw new Exception("Failed to include 'header'"); ?>
    <title>NAZZ</title>
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
                            <h1>Article List</h1>

                            <?php  if(count($videos) > 0)  {  ?>

                                <div class="row">
                                    <table class="table table-striped table-bordered" id="article_list">
                                        <thead>

                                        <tr>
                                            <td>
                                                Title
                                            </td>
                                            <td>
                                                Edit
                                            </td>
                                        </tr>

                                        </thead>

                                        <tbody>

                                        <?php
                                        foreach($videos as $vidoe)
                                        {
                                            ?>
                                            <tr>
                                                <td>
                                                    <?php  echo $vidoe->v_title;  ?>
                                                </td>
                                                <td>
                                                    <a class="btn btn-sm btn-primary" href="edit_video.php?v_id=<?php  echo $vidoe->v_id;  ?>">Edit</a>
                                                </td>
                                            </tr>
                                            <?
                                        }
                                        ?>


                                        </tbody>


                                    </table>
                                </div>

                            <?php  }  ?>

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
    $(document).ready(function () {
        table = $("#article_list").DataTable({
            destroy: true,
            dom: "Blfrtip",
            lengthMenu: [ [10, 25, 50, -1], [10, 25, 50, 'All'] ],
            buttons: [
                {
                    extend: "copy",
                    className: "btn-sm"
                },
                {
                    extend: "csv",
                    className: "btn-sm"
                }
            ],
            responsive: true
        });
    });
</script>





</body>
</html>
