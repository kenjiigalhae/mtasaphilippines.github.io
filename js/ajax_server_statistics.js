function ajax_load_server_statistics(){
    $.get("../ajax/ajax_server_stats.php", {
    }, function (data) {
        document.getElementById("server_stats").innerHTML = data;
    });
}