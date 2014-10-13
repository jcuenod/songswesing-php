<?php
if (!isset($drawindex) || !$drawindex)
	exit();

?><!DOCTYPE HTML>
<html lang="en-GB">
    <head>
        <meta http-equiv="content-type" content="text/html;charset=utf-8" />
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Untitled</title>

        <script src="js/jquery-2.1.1.min.js"></script>
        <script src="js/jquery-ui.min.js"></script>
        <script src="js/tag-it.js"></script>
        <script src="js/jquery-autogrow.min.js"></script>
        <script src="js/Chart.min.js"></script>
        <script src="js/featherlight.min.js"></script>
        <script src="js/jquery.validate.min.js"></script>

        <script src="js/init.js"></script>

        <!--<link rel="author" href="humans.txt">-->
		<link href="css/jquery-ui.min.css" rel="stylesheet">
		<link href="css/jquery.tagit.css" rel="stylesheet">
		<link href="css/tagit.ui-zendesk.css" rel="stylesheet">
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <link href="css/featherlight.min.css" rel="stylesheet" />
		
        <link href="css/style.css" rel="stylesheet" />

    </head>
    <body>
        <h1>songs<span class="spacer"></span>we<span class="spacer"></span>sing</h1>
        <form><div style="margin: 20px 20px 5px 20px;">
            <input type="hidden" name="task" value="store">
            <table class="table table-bordered table-condensed table-hover table-striped">
                <thead>
                    <th>Date</th>
                    <th>Service<a class="glyphicon glyphicon-plus-sign addnew" onclick="addnewAnchorClicked('service_type')"></a></th>
                    <th>Leader<a class="glyphicon glyphicon-plus-sign addnew" onclick="addnewAnchorClicked('leader')"></a></th>
                    <th>
                        Songs
                        <a class="songusageanchor" onclick="songUsageAnchorClicked()">usage summary</a>
                        <a class="glyphicon glyphicon-plus-sign addnew" onclick="addnewAnchorClicked('song')"></a>
                    </th>
                </thead>
                <tbody id="completed">
                    <?php echo $controller->getServicesString(); ?>
                    <tr>
                        <td><input name="date" type="hidden" id="dp" style="float: left" /></td>
                        <td>
                            <select name="service_type" style="float: left">
                                <?php
                                foreach ($controller->getServiceTypes() as $service_type)
                                {
                                    echo "<option value='".$service_type["id"]."'>".$service_type["service_type"]."</option>";
                                }
                                ?>
                            </select>
                        </td>
                        <td>
                            <select name="leader" style="float: left">
                                <?php
                                foreach ($controller->getLeaders() as $leader)
                                {
                                    echo "<option value='".$leader["id"]."'>".$leader["leader_name"]."</option>";
                                }
                                ?>
                            </select>
                        </td>
                        <td><ul name="songs[]" id="songList"></ul></td>
                    </tr>
                </tbody>
            </table>
        </div></form>
    </body>
</html>