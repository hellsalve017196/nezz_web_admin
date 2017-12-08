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
           <?php  if(isset($_GET['a_id'])){ if($_GET["a_id"]) {

               $a_id = $_GET["a_id"];

               $database->query("SELECT a_id,a_title,a_contenturl,a_imageurl,a_source FROM articles WHERE a_id = ".$a_id);

               $article = $database->results();

               if(count($article) > 0)
               {
                   $article = $article[0];

               ?>
            <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="x_panel">
                        <div class="row x_content">
                            <h1>Edit Article</h1><p><a href="article_list.php">back</a></p>

                            <div id="res" style="color: green">
                            </div>

                            <div class="row">
                                <div>
                                    title: <input type="text" class="form-control" id="title" value="<? echo $article->a_title; ?>"/>
                                </div>
                                <div>
                                    ImageUrl: <input type="text" class="form-control" id="imageUrl" value="<? echo $article->a_imageurl; ?>"/>
                                </div>
                                <div>
                                    ContentUrl: <input type="text" value="<? echo $article->a_contenturl; ?>" class="form-control" id="contentUrl"/>
                                </div>
                                <div>
                                    Source: <input type="text" class="form-control" id="source" value="<? echo $article->a_source; ?>"/>
                                </div>
                                <br<
                                <div>
                                    <button class="btn btn-primary" id="edit_article">Edit</button>
                                    <a class="btn btn-danger" id="edit_article" href="new_features.php?a_id=<?echo $_GET['a_id']?>">Delete</a>
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
    $("#edit_article").on("click",function() {
        title = $("#title").val();
        imageurl = $("#imageUrl").val();
        contenturl = $("#contentUrl").val();
        sourcee = $("#source").val();

        if(($.trim(title) != '') && ($.trim(imageurl) != '') && ($.trim(contenturl) != '') && ($.trim(sourcee) != ''))
        {
            $.post("<?php echo constant("ROOT_URL"); ?>"+"new_features.php",{"edit_article":1,"edit_title":title,"edit_imageurl":imageurl,"edit_contenturl":contenturl
                ,"edit_sourcee":sourcee,"a_id":<? echo $_GET["a_id"] ?>},function (data) {
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
