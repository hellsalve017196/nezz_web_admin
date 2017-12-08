<!DOCTYPE html>
<html lang="en">
<head>

    <?php

    require 'config.php';
    require_once 'DB.php';

    $Database = DB::getInstance();

    $Database->query("SELECT a_id,a_title FROM articles ORDER BY a_id DESC");

    $articles = $Database->results();

    $Database->reseting();

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
                            <h1>Article List</h1>

                            <?php  if(count($articles) > 0)  {  ?>

                            <div class="row">

                                <table class="table table-striped table-bordered" id="article_list">
                                    <thead>

                                    <tr>
                                        <td>
                                            Id
                                        </td>

                                        <td>
                                            Title
                                        </td>
                                        <td>
                                            Edit
                                        </td>

                                        <td>
                                            Move
                                        </td>

                                    </tr>

                                    </thead>

                                    <tbody>

                                    <?php
                                    foreach($articles as $article)
                                    {
                                        ?>
                                        <tr>
                                            <td>
                                                <?php  echo $article->a_id;  ?>
                                            </td>
                                            <td>
                                                <?php  echo $article->a_title;  ?>
                                            </td>
                                            <td>
                                                <a class="btn btn-sm btn-primary" href="edit_article.php?a_id=<?php  echo $article->a_id;  ?>">Edit</a>
                                            </td>
                                            <td>
                                                <input type="checkbox" value="text" class="move" id="<? echo $article->a_id;  ?>"/>
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
            responsive: false,
            "bPaginate": false
        });



        var article_swap = [];

        var swap = function (list) {
            $.post("<?php echo constant("ROOT_URL"); ?>"+"new_features.php",{"swap_1":list[0],"swap_2":list[1]},function (data) {
                    location.reload();
            });
        };

        $("input[type='checkbox']").change(function() {

            if(this.checked)
            {
                if(article_swap.length == 1)
                {
                    article_swap.push(this.id);

                    // send request
                    swap(article_swap);

                }
                else if(article_swap.length == 0)
                {
                    article_swap.push(this.id);
                }
            }
            else {

            }

        });



    });
</script>





</body>
</html>
