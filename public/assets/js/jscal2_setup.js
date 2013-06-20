Calendar.setup({
    weekNumbers   : false,
    selectionType : Calendar.SEL_SINGLE,
    selection     : Calendar.dateToInt(new Date()),
    showTime      : 12,
    minuteStep    : 5,
    trigger       : "report_date_cal",
    inputField    : "report_date",
    dateFormat    : "%o/%e/%Y %l:%M %p",
    fdow          : 0,
    onSelect      : function() {
        this.hide();
    },
});
/*
Calendar.setup({
    weekNumbers   : false,
    selectionType : Calendar.SEL_SINGLE,
    selection     : Calendar.dateToInt(new Date()),
    showTime      : 12,
    minuteStep    : 1,
    trigger       : "last_checked_cal",
    inputField    : "last_checked",
    dateFormat    : "%o/%e/%Y %l:%M %p",
    fdow          : 0,
    onSelect      : function() {
        this.hide();
    },
});
*/
