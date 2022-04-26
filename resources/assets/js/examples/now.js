function getNow() {
    const nowDate = new Date();
    document.getElementById('report_date').value=nowDate.toLocaleString();
}
