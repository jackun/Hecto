window.addEventListener('load', function () {
    chrome.tabs.getSelected(null, function (tab) {
        var tabUrl = tab.url,
            host = get_host(),
            bkey = get_bkey();
        if (!bkey) {
            alert('Please check your settings!');
            chrome.tabs.create({
                url: 'options.html'
            });
            return false;
        }
        $.ajax({
            type: "GET",
            url: "http://" + host + "/",
            data: {
                plugin: true,
                bookmark: tabUrl,
                pluginkey: bkey,
                version: get_version()
            },
            success: function (transport) {
                var resp = JSON.parse(transport),
                    pref = "",
                    suff = "";
                if (resp.watch) {
                    pref = "<a href='http://" + host + "/#" + resp.watch + "' target=_blank>";
                    suff = "</a>";
                }
                yourlist = "<a href='http://" + host + "/?bkey=" + bkey + 
                           "' target=_blank>Your playlist is here!</a>";
                $('#status').html(pref + resp.msg + suff + " | " + yourlist);
            }
        });
    });
});