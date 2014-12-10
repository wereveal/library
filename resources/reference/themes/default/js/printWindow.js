// (C) 2000 www.CodeLifter.com
// http://www.codelifter.com
// Free for all users, but leave in this  header

function printWindow(){
   bV = parseInt(navigator.appVersion)
   if (bV >= 4) window.print()
}
function delayedPrintWindow(){
   setTimeout("printWindow();",100);
}
