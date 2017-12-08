<!DOCTYPE html>
<html lang="en">
<head>

    <?php

        require 'config.php';

    ?>

    <?php if(!@include('templates/header.php')) throw new Exception("Failed to include 'header'"); ?>
    <title>NAZZ | <?php echo strtoupper($pageTitle); ?></title>
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
                        <h1>Add Article</h1>

                        <div id="res" style="color: green">
                        </div>

                        <div class="row">
                            <div>
                                title: <input type="text" class="form-control" id="title"/>
                            </div>
                            <div>
                                ImageUrl: <input type="text" class="form-control" id="imageUrl"/>
                            </div>
                            <div>
                                ContentUrl: <input type="text" class="form-control" id="contentUrl"/>
                            </div>
                            <div>
                                Source: <input type="text" class="form-control" id="source"/>
                            </div>

                            <div>
                                <button class="btn btn-primary" id="add_article">Add Article</button>
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
    $("#add_article").on("click",function() {
        title = $("#title").val();
        imageurl = $("#imageUrl").val();
        contenturl = $("#contentUrl").val();
        sourcee = $("#source").val();

        if(($.trim(title) != '') && ($.trim(imageurl) != '') && ($.trim(contenturl) != '') && ($.trim(sourcee) != ''))
        {
            $.post("<?php echo constant("ROOT_URL"); ?>"+"new_features.php",{"article":1,"title":title,"imageurl":imageurl,"contenturl":contenturl
                ,"sourcee":sourcee},function (data) {
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
