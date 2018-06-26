function changeTwigDirs(selectedPrefix) {
    var goTo = location.origin+'/manager/config/ajax/twig_dirs/';
    var howLong = selectedPrefix.id.length - 1;
    var whichOne = selectedPrefix.id.substring(howLong);
    var replaceIn = '#directory'+whichOne;
    var posted = {"prefix_id":selectedPrefix.value};
    $.post(goTo, posted, function(response) {
        var resp = JSON.parse(response);
        $(replaceIn).empty();
        var dirSelect = $(replaceIn);
        $(resp).each(function(k, v) {
            dirSelect
                .append($("<option></option>")
                   .attr("value", v.td_id)
                   .text(v.td_name)
                );
        });
    });
}

function displayDirectories(selectedPrefix) {
    var goTo = location.origin+'/manager/config/ajax/for_directories/';
    var posted = {"prefix_id":selectedPrefix.value};
    $.post(goTo, posted, function(response) {
        var resp = JSON.parse(response);
        var theClass = 'odd';
        var theDiv = $('#forDirectories');
        theDiv.empty();
        $(resp).each(function(k, v) {
            var theStuff = '';
            if (k % 2) {
                theClass = 'even';
            }
            else {
                theClass = 'odd';
            }
            theStuff += '<form action="{{ public_dir }}/manager/config/twig/" method="post">';
            theStuff += '<div id="dirRow'+k+'" class="row '+theClass+'">';
            theStuff += '<div id="dirCol1-'+k+'" class="col-lg-2">'+v.tp_prefix+'</div>';
            theStuff += '<div id="dirCol2-'+k+'" class="col-lg-2">';
            theStuff += '<label for="td_name'+k+'" class="d-none"></label>';
            theStuff += '<input type="text" name="td_name" id="td_name'+k+'" value="'+v.td_name+'">';
            theStuff += '</div>';
            theStuff += '<div id="dirCol3-'+k+'" class="col-lg-2 offset-lg-6">';
            theStuff += '<input type="hidden" name="td_id" value="'+v.td_id+'">';
            theStuff += '<button type="submit" name="submit" value="update_dir" class="btn btn-green btn-xs">Update</button>';
            theStuff += '<button type="submit" name="submit" value="verify_delete_dir" class="btn btn-outline-red btn-xs">Delete</button>';
            theStuff += '<input type="hidden" name="tolken" value="'+v.tolken+'">';
            theStuff += '<input type="hidden" name="form_ts" value="'+v.form_ts+'">';
            theStuff += '<label for="hobbit" class="hobbit">Humans do not enter anything here:</label>';
            theStuff += '<input type="text" name="hobbit" id="hobbit" value="" size="2" maxlength="2" class="hobbit">';
            theStuff += '</div>';
            theStuff += '</div>';
            theStuff += '</form>';
            theDiv.append(theStuff);
        });
    });
}

function urlsForNavgroup(navgroup) {
    var goTo = location.origin+'/manager/config/ajax/urls_available/';
    var posted = {'navgroup_id':navgroup.value};
    $.post(goTo, posted, function(jsonStr) {
        if (jsonStr.includes("<pre>")) {
            jsonStr = jsonStr.replace('<pre></pre>','');
            console.error("jsonStr includes the pre tag.");
        }
        var parsedJson = JSON.parse(jsonStr);
        $("#url_id").empty();
        $(parsedJson).each(function(k, v) {
            $("#url_id").append($("<option></option>").attr("value", v.url_id).text(v.url_text));
        });
    })
}