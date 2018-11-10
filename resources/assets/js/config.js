$('#page_up').datetimepicker({
    format:'Y-m-d H:i',
    lazyInit: true,
    step: 15,
    defaultDate: new Date(),
    defaultTime: '00:00',
    formatTime: 'H:i',
    mask: true
});

$('#page_down').datetimepicker({
    format:'Y-m-d H:i',
    lazyInit: true,
    step: 15,
    defaultDate: '9999-12-31',
    defaultTime: '23:59',
    formatTime: 'H:i',
    mask: true
});

function changeTwigDirs(selectedPrefix) {
    var goTo = location.origin+'/manager/config/ajax/twig_dirs/';
    var howLong = selectedPrefix.id.length - 1;
    var whichOne = selectedPrefix.id.substring(howLong);
    var replaceIn = '#directory'+whichOne;
    var posted = {"prefix_id":selectedPrefix.value};
    $.post(goTo, posted, function(jsonStr) {
        if (jsonStr.includes("<pre>")) {
            jsonStr = jsonStr.replace('<pre></pre>','');
        }
        var resp = JSON.parse(jsonStr);
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
    $.post(goTo, posted, function(jsonStr) {
        // console.log(jsonStr);
        if (jsonStr.includes("<pre>")) {
            jsonStr = jsonStr.replace('<pre></pre>','');
        }
        var resp = JSON.parse(jsonStr);
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
            theStuff += '<form action="/manager/config/twig/" method="post">';
            theStuff += '<div id="dirRow'+k+'" class="row '+theClass+'">';
            theStuff += '<div id="dirCol1-'+k+'" class="col-lg-2">'+v.tp_prefix+'</div>';
            theStuff += '<div id="dirCol2-'+k+'" class="col-lg-4">';
            theStuff += '<label for="td_name'+k+'" class="d-none"></label>';
            theStuff += '<input type="text" name="td_name" id="td_name'+k+'" value="'+v.td_name+'" class="form-control colorful">';
            theStuff += '</div>';
            theStuff += '<div id="dirCol3-'+k+'" class="col-lg-3 offset-lg-3">';
            theStuff += '<input type="hidden" name="td_id" value="'+v.td_id+'">';
            theStuff += '<input type="hidden" name="tp_id" value="'+v.tp_id+'">';
            theStuff += '<button type="submit" name="submit" value="update_dir" class="btn btn-green btn-xs margin-right-half">Update</button>';
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
        }
        var parsedJson = JSON.parse(jsonStr);
        $("#url_id").empty();
        $(parsedJson).each(function(k, v) {
            $("#url_id").append($("<option></option>").attr("value", v.url_id).text(v.url_text));
        });
    })
}

function switchPageDirsForPrefix(selectedPrefix) {
    var goTo = location.origin+'/manager/config/ajax/page_prefix_dirs/';
    var posted = {'prefix_id':selectedPrefix.value};
    var dirSelectName = '#td_id';
    var tplSelectName = '#tpl_id';
    $.post(goTo, posted, function(jsonStr) {
        if (jsonStr.includes("<pre>")) {
            jsonStr = jsonStr.replace('<pre></pre>','');
        }
        var resp = JSON.parse(jsonStr);
        $(dirSelectName).empty();
        var dirSelect = $(dirSelectName);
        dirSelect
            .append($("<option></option>")
                .attr("value", "")
                .text("-Select Directory-")
            );
        $(resp).each(function(k, v) {
            dirSelect
                .append($("<option></option>")
                    .attr("value", v.td_id)
                    .text(v.td_name)
                );
        });
        $(tplSelectName).empty();
        var tplSelect = $(tplSelectName);
        if (Object.keys(resp).length > 1) {
            tplSelect.append($("<option></option>")
                .attr("value", "")
                .text("-Select Dir First-")
            );
        }
        else {
            var goToTwo = location.origin+'/manager/config/ajax/page_dirs_tpls/';
            var postedTwo = {'td_id':resp[0].td_id};
            $.post(goToTwo, postedTwo, function(jsonStrTwo) {
                if (jsonStrTwo.includes("<pre>")) {
                    jsonStrTwo = jsonStrTwo.replace('<pre></pre>','');
                }
                var respTwo = JSON.parse(jsonStrTwo);
                tplSelect.append($("<option></option>")
                    .attr("value", "")
                    .text("-Select Template-")
                );
                $(respTwo).each(function(k2, v2) {
                    tplSelect.append($("<option></option>")
                        .attr("value", v2.tpl_id)
                        .text(v2.tpl_name)
                    );
                });
            });
        }
    });
}

function switchTplForDir(selectedDir) {
    var goTo = location.origin+'/manager/config/ajax/page_dirs_tpls/';
    var posted = {'td_id':selectedDir.value};
    var replaceIn = '#tpl_id';
    $.post(goTo, posted, function(jsonStr) {
        if (jsonStr.includes("<pre>")) {
            jsonStr = jsonStr.replace('<pre></pre>','');
        }
        var resp = JSON.parse(jsonStr);
        $(replaceIn).empty();
        var tplSelect = $(replaceIn);
        tplSelect
            .append($("<option></option>")
                .attr("value", "")
                .text("-Select Template-")
            );
        $(resp).each(function(k, v) {
            tplSelect
                .append($("<option></option>")
                    .attr("value", v.tpl_id)
                    .text(v.tpl_name)
                );
        });
    });

}

function updateContentVCS() {
    console.error('In the function.');
    var goTo = location.origin+'/manager/config/ajax/content_vcs/';
    $.ajax({url:goTo});
}
