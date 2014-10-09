<?php
include ("controller.php");

$task = empty($_POST["task"]) ? false : $_POST["task"];

switch ($task) {

	case 'store':
        $service = new service_object(
            $_POST['date'],
            $_POST['service_type'],
            $_POST['leader'],
            $_POST['songs']
        );
        $controller->storeService($service);
        $controller->echoService($service);
		break;


	case 'update':
		break;

    case 'songusage':
        echo $controller->getSongUsageTable();
        break;


    case 'songdata':
        header('Content-Type: application/json');
        echo $controller->getSongDataJSON($_POST["song_name"]);
        break;


    case 'leaderdata':
        header('Content-Type: application/json');
        echo $controller->getLeaderDataJSON($_POST["leader_name"]);
        break;


	case false:
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

        <!--<link rel="stylesheet" href="css/style.css">
        <link rel="author" href="humans.txt">-->
		<link href="css/jquery-ui.min.css" rel="stylesheet">
		<link href="css/jquery.tagit.css" rel="stylesheet">
		<link href="css/tagit.ui-zendesk.css" rel="stylesheet">
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <link href="css/featherlight.min.css" rel="stylesheet" />
		
        <style type="text/css">
        .ui-datepicker-trigger {
            float: left;
            padding: 1px;
            margin: 0 2px;
        }
        .ui-autocomplete-input {
            min-width: 0 !important;
        }
        .tagit-autocomplete.ui-menu {
            border-radius: 4px;
            padding: 2px;
            padding-right: 8px;
        }
        .tagit-autocomplete.ui-menu .ui-menu-item {
            border-radius: 3px;
            margin: 1px;
        }
        select {
            padding: 1.1em;
        }
        #completed {
            margin: 10px;
        }
        #completed span {
            padding: 2px 10px;
        }
        a.borderedanchor {
            border: 1px solid #ddd;
        }
        a.borderedanchor, a.unborderedanchor, a.songusageanchor {
            padding: 3px 5px;
            margin: 0 2px;
            border-radius: 5px;
        }
        a.borderedanchor:hover, a.unborderedanchor:hover {
            cursor: pointer;
            background-color: #ddf;
            text-decoration: none;
        }
        a.songusageanchor:hover {
            cursor: pointer;
            text-decoration: none;
        }

        .breakdown_header:before {
            content: "INFO: ";
            font-size: 70%;
            font-weight: normal;
        }
        .breakdown_header {
            text-align: center;
            font-size: 200%;
            font-weight: bold;
        }
        h1 {
            margin: 20px 30px 0px 40px;
            font-family: "quaver sans";
            font-size: 300%;
        }
        .spacer {
            display: inline-block;
            width: 4px;
        }
        .icontip {
            margin: 2px;
        }
        </style>

        <script type="text/javascript">

        var myLightboxChart;
        var myFeatherBox;
        var songArray = <?php echo json_encode($controller->getSongs()); ?>;
        
        //var sampletags = ["Jesus the Name High Over All", "Come Thou Fount (Traditional)", "Come Thou Fount", "Amazing Grace", "Amazing Grace (My Chains are Gone)", "In Christ Alone", "Bless the Lord Oh My Soul", "How Deep", "How Deep the Father's Love for Us", "some bad name"];
        $( document ).ready(function() {
    		$("#songList").tagit({
    			availableTags: songArray,
				allowSpaces: true,
                allowNewTags: false,
                onlyAvailableTags : true,
                removeConfirmation: true,
        		beforeTagAdded: function (event, ui) {
        		    if ($.inArray(ui.tagLabel, songArray) == -1) {
        		        return false;//$("#songList").tagit("removeTagByLabel", ui.tagLabel);
        		    }
        		},
                afterTagAdded: function ()
                {
                    $(".ui-autocomplete-input").autoGrow(8);
                },
                autocomplete: {
                    delay: 0,
                    source: function(request, response) {
                        /* Remove the dashes from the search term: */
                        var term = request.term.replace(/\ /g, '');
                        var matcher = new RegExp($.ui.autocomplete.escapeRegex(term), "i");
                
                        response($.map(songArray, function(el) {
                            /* Remove dashes from the source and compare with the term: */
                            if (matcher.test(el.replace(/\ /g, ''))) {
                                return el;
                            }
                        }));
                    }
                },
                onSubmit: function() {
                    var jqxhr = $.post( "#", $("form").serialize())
                        .done(function(data) {
                            $("#completed").prepend(data);
                        })
                        .fail(function() {
                            alert( "error" );
                        })
                        .always(function() {
                        });

                    console.log("watch out, I could be empty (and you should clear the stuff once it's submitted)");
                },
                fieldName: 'songs[]',
                singleField: false,
    		});
	
			$("#dp").datepicker({
            	constrainInput: true,
            	showOn: 'button',
            	buttonText: 'Pick Date', //TODO: set default
                dateFormat: 'yy-mm-dd',
  				onSelect: function(selectedDate) {
  					$( ".ui-datepicker-trigger" ).button( "option", "label", selectedDate );
  				}
        	});

            $(".ui-autocomplete-input").autoGrow(8);
            $("select").selectmenu();
            $(".ui-datepicker-trigger").button();

		});

        function songUsageAnchorClicked()
        {
            var jqxhr = $.post( "#", {"task": "songusage"})
                .done(function(data) {
                    myFeatherBox = $.featherlight("<div class='breakdown_header'>Usage Summary</div><div id='feather'>" + data + "</div>");
                })
                .fail(function(e) {
                    console.log( "error" );
                    console.dir(e);
                })
                .always(function() {
                });
        }
        function songAnchorClicked(el)
        {
            var song_name = $(el).html();
            var jqxhr = $.post( "#", {"task": "songdata", "song_name": song_name})
                .done(function(data) {
                    console.dir(data);
                    try {myLightboxChart.destroy(); console.log("destroyed")}catch(e){console.log("not destroyed")}
                    var mc = $("<canvas width=600 height=300>");
                    var ctx = mc.get(0).getContext("2d");
                    myLightboxChart = new Chart(ctx).Doughnut(data.chartdata, {
                        animationSteps : 50
                    });
                    var tabledata = "";
                    for (var key in data.misc) {
                        if (!data.misc.hasOwnProperty(key))
                            continue;

                        tabledata += "<tr><td>" + key + "</td><td>" + data.misc[key] + "</td></tr>";
                    }
                    $.featherlight.close();
                    $.featherlight("<div class='breakdown_header'>" + song_name + " (" + data.details.tally + ")</div>" +
                        "<div id='feathersac'></div>" + 
                        "<div><table class='table table-condensed table-striped'>" +
                        tabledata +
                        "</table></div>");
                    $("#feathersac").html($(mc));
                })
                .fail(function(e) {
                    console.log( "error" );
                    console.dir(e);
                })
                .always(function() {
                });
        }
        function leaderAnchorClicked(el)
        {
            var leader_name = $(el).html();
            var jqxhr = $.post( "#", {"task": "leaderdata", "leader_name": leader_name})
                .done(function(data) {
                    var arrlabel = new Array();
                    var arrdata1 = new Array();
                    var arrdata2 = new Array();
                    for (d in data)
                    {
                        arrlabel.push(data[d].song_name);
                        arrdata1.push(data[d].tally);
                        arrdata2.push(data[d].total);
                    }
                    var completedata = {
                        labels: arrlabel,
                        datasets: [
                            {
                                label: "My First dataset",
                                fillColor: "rgba(220,220,220,0.5)",
                                strokeColor: "rgba(220,220,220,0.8)",
                                highlightFill: "rgba(220,220,220,0.75)",
                                highlightStroke: "rgba(220,220,220,1)",
                                data: arrdata2
                            },
                            {
                                label: "My Second dataset",
                                fillColor: "rgba(151,187,205,0.5)",
                                strokeColor: "rgba(151,187,205,0.8)",
                                highlightFill: "rgba(151,187,205,0.75)",
                                highlightStroke: "rgba(151,187,205,1)",
                                data: arrdata1
                            }
                        ]
                    };
                    try {myLightboxChart.destroy(); console.log("destroyed")}catch(e){console.log("not destroyed")}
                    var mc = $("<canvas width=600 height=400>");
                    var ctx = mc.get(0).getContext("2d");
                    myLightboxChart = new Chart(ctx).Bar(completedata);
                    $.featherlight("<div class='breakdown_header'>" + leader_name + "</div><div id='feather'></div>");
                    $("#feather").html($(mc));
                    //$.featherlight("<div>" + data + "</div>");
                })
                .fail(function(e) {
                    console.log( "error" );
                    console.dir(e);
                })
                .always(function() {
                });
        }
        </script>
    </head>
    <body>
        <h1>songs<span class="spacer"></span>we<span class="spacer"></span>sing</h1>
        <form><div style="margin: 20px 20px 5px 20px;">
            <input type="hidden" name="task" value="store">
            <table class="table table-bordered table-condensed table-hover table-striped">
                <thead>
                    <th>Date</th>
                    <th>Service</th>
                    <th>Leader</th>
                    <th>Songs <a class="songusageanchor" onclick="songUsageAnchorClicked()">usage summary</a></th>
                </thead>
                <tbody id="completed">
                    <?php $controller->echoServices(); ?>
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
</html><?php

}

?>