var myLightboxChart;
var myFeatherBox;
var songArray = new Array();

//var sampletags = ["Jesus the Name High Over All", "Come Thou Fount (Traditional)", "Come Thou Fount", "Amazing Grace", "Amazing Grace (My Chains are Gone)", "In Christ Alone", "Bless the Lord Oh My Soul", "How Deep", "How Deep the Father's Love for Us", "some bad name"];
$( document ).ready(function() {
    var jqxhr = $.post( "#", {"task": "getSongs"}) 
        .done(function(data) {
            songArray = data;
        })
        .fail(function(e) {
            console.log( "error: no songArray " );
            console.dir(e);
        });

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
                });

            //TODO: clear the stuff once it's submitted
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
        });
}
function songAnchorClicked(el)
{
    var song_name = $(el).html();
    var jqxhr = $.post( "#", {"task": "songdata", "song_name": song_name})
        .done(function(data) {
            try {myLightboxChart.destroy(); }catch(e){console.log("something's gone wrong with the chart stuff: ");console.log(e);}
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
            try {myLightboxChart.destroy(); }catch(e){console.log("something's gone wrong with the chart stuff: ");console.log(e);}
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
        });
}
function addnewAnchorClicked(thingToAdd)
{
    var jqxhr = $.post( "#", {"task": "create", "action" : "dialog", "type" : thingToAdd})
        .done(function(data) {
            myFeatherBox = $.featherlight(data);
            $("form#frm_create").validate();
            $("form#frm_create").on("submit", function(e) {
                e.preventDefault();
                createAnchorClicked(thingToAdd);
            });
            $("#frm_create input")[0].focus();
        })
        .fail(function(e) {
            console.log( "error" );
            console.dir(e);
        });
}
function createAnchorClicked(thingToAdd)
{
    if (!$("form#frm_create").valid())
        return;

    var formValues = {};
    $("form#frm_create").serializeArray().map(function(x){ formValues[x.name] = x.value; });
    var jqxhr = $.post( "#", {"task": "create", "action" : "submit", "type" : thingToAdd, "values" : formValues})
        .done(function(data) {
            $.featherlight.close();
            switch (data.type)
            {
                case "leader":
                    $("select[name=leader]").append("<option value=\"" + data.newLeaderID + "\">" + data.newLeaderName + "</option>");
                    $("select[name=leader]").selectmenu("refresh");
                    break;
                case "service_type":
                    $("select[name=service_type]").append("<option value=\"" + data.newServiceTypeID + "\">" + data.newServiceType + "</option>");
                    $("select[name=service_type]").selectmenu("refresh");
                    break;
                case "song":
                    songArray.push(data.newSong);
                    break;
            }
        })
        .fail(function(e) {
            console.log( "error" );
            console.dir(e);
        });
}