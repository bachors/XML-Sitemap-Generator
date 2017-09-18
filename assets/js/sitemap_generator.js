/****************************************
* #### Sitemap Generator v1.0 ####
* Coded by Ican Bachors 2016.
* http://bachors.com/
*****************************************/

$.fn.sitemap_generator = function(extract) {
	
    var m = CodeMirror.fromTextArea(document.getElementById('sitemapxml'), {
        mode: 'text/xml',
        lineNumbers: true,
        lineWrapping: true,
        theme: "material",
        readOnly: true
    });
	
	var sl = ($(this).attr('id') != null && $(this).attr('id') != undefined ? '#' + $(this).attr('id') : '.' + $(this).attr('class'));
	
    $(sl + ' #generet').click(function() {
        var c = $(sl + ' #url').val();
        if (c != '') {
            $.ajax({
                type: "POST",
                url: extract,
                dataType: 'json',
                data: {
                    url: c,
                    www: 'yes'
                }
            }).done(function(b) {
                if (b.status == 'success') {
                    var a = $(sl + ' #freq').val(),
                        prio = $(sl + ' #prio').val(),
                        mod = $(sl + ' #mod').val(),
                        bb = b.data;
                    sendRequest(bb, a, prio, mod)
                } else {
                    alert(b.message)
                }
            })
        } else {
            alert('Not empty!')
        }
        return false
    });
	
    $(sl + ' #downloadxml').click(function() {
        downloadFile();
        return false
    });

    function sendRequest(k, d, f, g) {
        var j = $.map(k, function(a, b) {
            return [a]
        });
        var l = j.length;
        if (l > 0) {
            var t = new XMLHttpRequest(),
                tampung = [],
                xml = '',
                s = 0;
            t.onreadystatechange = function() {
                if (t.readyState == 4 && t.status == 200) {
                    var a = JSON.parse(t.responseText);
                    if (a.status == 'success') {
                        var h = $.map(a.data, function(a, b) {
                            return [a]
                        });
                        $.each(h, function(i, e) {
                            tampung.push(e)
                        })
                    }
                    s++;
                    if (s < l) {
                        var b = (s / (l - 1)) * 100;
                        $(sl + " #persen").css("display", 'block');
                        $(sl + " #persen").html(b.toFixed() + '%');
                        t.open("POST", extract, true);
                        t.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                        t.send("url=" + j[s])
                    } else {
                        $(sl + " #persen").css("display", 'none');
                        $(sl + ' #downloadxml').show();
                        var c = unique(tampung),
                            fr = '',
                            pr = '',
                            md = '';
                        site = '';
                        site += "<\?xml version=\"1.0\" encoding=\"UTF-8\"?\>\n";
                        site += "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\">\n\n";
                        if (d != '') {
                            fr = '        <changefreq>' + d + '</changefreq>\n'
                        }
                        if (f != '') {
                            pr = '        <priority>' + f + '</priority>\n'
                        }
                        if (g != '') {
                            md = '        <lastmod>' + g + '</lastmod>\n'
                        }
                        xml += '\n' + site + '    <url>\n        <loc>';
                        xml += c.join('</loc>\n' + md + fr + pr + '    </url>\n    <url>\n        <loc>');
                        xml += '</loc>\n' + md + fr + pr + '    </url>\n';
                        $(sl + " #urlcount").css("display", 'inline');
                        $(sl + " #urlcount").html(c.length);
                        m.setValue($.trim(xml.replace(/\&/g, "&amp;") + '\n</urlset>'));
                    }
                }
            };
            t.open("POST", extract, true);
            t.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            t.send("url=" + j[s])
        } else {
            alert('Host not found or please include www. or subdomain.')
        }
    }

    function unique(a) {
        var b = [];
        $.each(a, function(i, e) {
            if ($.inArray(e, b) == -1) b.push(e)
        });
        return b
    }

    function downloadFile() {
        var a = document.createElement("a");
        document.body.appendChild(a);
        a.style = "display: none";
        var b = m.getValue(),
            blob = new Blob([b], {
                type: 'octet/stream'
            }),
            url;
        if (window.navigator.msSaveBlob !== undefined) {
            url = window.navigator.msSaveBlob(blob, 'sitemap.xml')
        } else {
            url = window.URL.createObjectURL(blob)
        }
        a.href = url;
        a.target = '_blank';
        a.download = 'sitemap.xml';
        a.click();
    }
	
}