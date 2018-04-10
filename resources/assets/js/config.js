function changeTwigDirs(selectedPrefix) {
    var goTo = location.origin+'/manager/config/ajax/twig_dirs/';
    console.log(selectedPrefix.id.length);
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
                .attr("value", v.td_id).text(v.td_name))
            ;
        });
    });
}
