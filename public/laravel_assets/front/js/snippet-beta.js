var type = parseInt("1"),
    once1 = !0,
    once2 = !0;
request = new XMLHttpRequest, request.onreadystatechange = function() {
    if (4 === this.readyState && 200 === this.status) {
        var e = JSON.parse(request.responseText),
            t = document.getElementsByTagName("head")[0],
            s = document.createElement("script"),
            a = document.createElement("style");
        s.setAttribute("id", "callburn-script"), a.setAttribute("rel", "stylesheet"), a.setAttribute("type", "text/css"), s.type = "text/javascript", s.append(e.script), a.append(e.styles), t.appendChild(s), t.appendChild(a)
    } else if (403 === this.status) {
        if (request.responseText && once2) {
            var r = JSON.parse(request.responseText);
            alert(r.resource.error.text), once2 = !1
        }
    } else 405 === this.status && once1 && (alert("This domain is not authorized. Add it on callburn.com to give the ability to this snippet to work properly"), once1 = !1)
}, request.open("POST", window.base_url + "/main-js/" + window.ctc_token + "/" + (new Date).getTimezoneOffset() / 60), request.setRequestHeader("Content-type", "application/json; charset=utf-8");
var data = JSON.stringify({
	local: true,
    site_language: window.locale,
    type: type,
    token: window.ctc_token,
    language: (navigator.language || navigator.userLanguage).split("-")[0]
});
request.send(data);

console.log('beta')
/*----------------------------------------*/
//# sourceMappingURL=snippet-beta.js.map
