var div = document.createElement('div');
var body = document.getElementsByTagName('body')[0];
div.className += " snippet-main-content ";



div.innerHTML += '<div id="callburn-snippet-content" class="snippet-bordered-box snippet-centering snippet-text-color"><div><br><img src="images/click_to_call_icons/callerid-icon.svg" class="snippet-img-centering"></div><br><p id="information-text-1" class="snippet-text-center snippet-font-Raleway-Medium">Want to talk with us?<br>Let’s do it <strong>now</strong>, totally <strong>free</strong> of charge!</p><p id="information-text-2" class="snippet-text-center snippet-font-Raleway-Medium snippet-font-size-14px">Tell us your phonenumber and we will<br><strong>#ring</strong> you just immediately.</p><br><div id="snippet-scheduled-content"></div><div class=""><div class="snippet-input-group"><div class=" dropbtn-clicked snippet-input-group-addon snippet-float-left snippet-position-relative snippet-width-80 "><div class="dropdown dropbtn-clicked" ><div onclick="openSelect('+"'DropdownOpen'" + ')" class="dropbtn dropbtn-clicked" id="countries-dropdown-selected"><img class="selected-image dropbtn-clicked" src="images/flags/it.svg "><span id="selected-text" class="snippet-text-color dropbtn-clicked">+(39)</span></div></div></div></div><div id="DropdownOpen" class="dropdown-content"></div></div><div class="snippet-float-left"><input id="snippet-phonenumber" class="snippet-form-control snippet-widt-145 snippet-input-width" placeholder="Insert your phonenumber"></div><br><br><br><button id="clickToCallButton" data-schedule="0" class="snippet-btn snippet-btn-block snippet-btn-success snippet-min_height_30">Call me now</button><div class="snippet-text-center"><p class="snippet-small snippet-font-Raleway-Regular snippet-font-size-10px">By clicking above button you agree to the<a href="" class="snippet-blue">Terms & Conditions</a></p></div><p id="snippetFooterText" class="snippet-text-center snippet-font-Raleway-Bold snippet-font-size-14px">Cannot answer now?<br>Try to<a href="" class="snippet-blue">schedule this call later! </a></p><div class="snippet-pull-right "><p class="snippet-font-Raleway-Regular snippet-font-size-14px">Powered with ♥ by<img src="images/call-burn-l-o-g-o.svg"></p></div><div class="snippet-clear-fix"></div></div>';

var scheduleDiv = '<div class="snippet-text-center"><div class="snippet-form-inline"><div class="snippet-form-group snippet-width-290px snippet-centering"><span class="snippet-text-center snippet-font-Raleway-Medium"><div class="snippet-line-height-34px"><label for="phone_number" class="snippet-float-left snippet-vertical-align">Call me </label><div class=" dropbtn-clicked snippet-input-group-addon snippet-form-control snippet-width-35 snippet-float-left snippet-vertical-align snippet-margin-horiz-2px snippet-display-inline"> <div class="dropdown dropbtn-clicked"> <div onclick="openSelect('+ "'DropdownOpenWeekdays'" +')" class="dropbtn dropbtn-clicked" id="weekdays-dropdown-selected"> <span id="selected-text-weekdays" class="snippet-text-color dropbtn-clicked"></span> </div><div id="DropdownOpenWeekdays" class="dropdown-content"> </div></div></div><label for="at_input_hours" class="snippet-float-left snippet-vertical-align"> at </label><input  class="snippet-form-control snippet-width-45px snippet-float-left snippet-vertical-align snippet-margin-horiz-2px snippet-display-inline" placeholder="10" id="at_input_hours"><span class="snippet-float-left snippet-vertical-align"> : </span><input class="snippet-form-control snippet-width-45px snippet-vertical-align snippet-margin-horiz-2px snippet-display-inline" placeholder="00" id="at_input_minutes"></div></span></div><br><span class="snippet-text-center"><p class="snippet-small snippet-font-Raleway-Regular snippet-font-size-12px snippet-clear-fix">At this moment, your time should be 12:03<br>based on your timezone GMT +2<br>Isn’t correct? <a href="" class="snippet-blue">Click here to change</a></p></span><br></div></div>';

body.append(div);



var images = document.querySelectorAll('#callburn-snippet-content img');

images.forEach(function (img) {
    var src = img.getAttribute('src');
    img.setAttribute('src', imagePath + '/' +src);
});


window.onclick = function(event) {

    if (event.target.className.indexOf('dropbtn-clicked') == -1) {

        var dropdowns = document.getElementsByClassName("dropdown-content");
        var i;
        for (i = 0; i < dropdowns.length; i++) {
            var openDropdown = dropdowns[i];
            if (openDropdown.classList.contains('show')) {
                openDropdown.classList.remove('show');
            }
        }
    }
};

function openSelect(id) {
    document.getElementById(id).classList.toggle("show");
}


var selectedCountry = document.getElementById('countries-dropdown-selected');

selectedCountry.getElementsByTagName('span')[0].innerText = '(+' + countries[0].phonenumber_prefix + ')';
selectedCountry.getElementsByTagName('span')[0].setAttribute("data-phonenumber", countries[0].phonenumber_prefix);
selectedCountry.getElementsByTagName('img')[0].setAttribute('src', baseUrl + '/assets/callburn/images/lang-flags/' + countries[0].code + '.svg');

var dropdownContent = document.getElementById('DropdownOpen');

countries.forEach(function (item) {
    var div = document.createElement('div');
    var img = document.createElement('img');
    img.setAttribute('src', baseUrl + '/assets/callburn/images/lang-flags/' + item.code + '.svg');
    img.className += " dropdown-image";
    var span = document.createElement('span');
    span.innerText = '(+' + item.phonenumber_prefix + ')';
    span.setAttribute("data-phonenumber", item.phonenumber_prefix);
    span.className += " dropdown-text";
    div.className += " country-name";
    div.append(img);
    div.append(span);
    dropdownContent.append(div);

    div.addEventListener('click', function (event) {
        selectedCountry.getElementsByTagName('span')[0].innerText = this.getElementsByTagName('span')[0].innerText;
        selectedCountry.getElementsByTagName('span')[0].setAttribute("data-phonenumber", this.getElementsByTagName('span')[0].getAttribute('data-phonenumber'));
        selectedCountry.getElementsByTagName('img')[0].setAttribute('src',  this.getElementsByTagName('img')[0].src);
    });

});

snippet.allowed_date_times = JSON.parse(snippet.allowed_date_times);



var fromDate = snippet.allowed_date_times.dateRangeStart.split(':');
var toDate = snippet.allowed_date_times.dateRangeEnd.split(':');

var start = moment().set({hour:parseInt(fromDate[0]),minute:parseInt(fromDate[1])}),
    end = moment().set({hour:parseInt(toDate[0]),minute:parseInt(toDate[1])});


var pSuccess1Html  = document.getElementById('information-text-1').innerHTML;
var pSuccess2Html  = document.getElementById('information-text-2').innerHTML;
var pFooterHtml  = document.getElementById('snippetFooterText').innerHTML;

var checkSnippetTime = function (start, end) {

    if (! snippet.allowed_date_times.weekDays.indexOf(moment().format("dddd")) && start.valueOf() < moment().valueOf() && moment().valueOf() < end.valueOf()) {


        var button = document.getElementById('clickToCallButton');
        var p1 = document.getElementById('information-text-1');
        var p2 = document.getElementById('information-text-2');
        var pFooter = document.getElementById('snippetFooterText');
        document.getElementById('snippet-scheduled-content').innerHTML = '';

        button.classList.remove("snippet-btn-warning");
        button.classList.add('snippet-btn-success');
        button.innerText = 'Call me now';
        button.setAttribute("data-schedule", 0);

        p1.innerHTML = pSuccess1Html;
        p2.innerHTML = pSuccess2Html;
        pFooter.innerHTML = pFooterHtml;

    } else {

        if(document.getElementById('clickToCallButton').getAttribute('data-schedule') == 0) {

            document.getElementById('snippet-scheduled-content').innerHTML = scheduleDiv;

            var selectedWeekdaysContent = document.getElementById('DropdownOpenWeekdays');

            snippet.allowed_date_times.weekDays.forEach(function (item) {

                var div = document.createElement('div');
                div.className += " weekday-name";
                var span = document.createElement('span');
                span.className += " dropdown-text-weekday";
                span.innerText = item;
                div.append(span);
                selectedWeekdaysContent.append(div);
                div.addEventListener('click', function (event) {
                    document.getElementById('weekdays-dropdown-selected').getElementsByTagName('span')[0].innerText = this.getElementsByTagName('span')[0].innerText;
                });


            });


            var button = document.getElementById('clickToCallButton');
            button.classList.remove('snippet-btn-success');
            button.classList.add("snippet-btn-warning");
            button.innerText = 'Schedule a call later';
            button.setAttribute("data-schedule", 1);

            var p1 = document.getElementById('information-text-1');
            var p2 = document.getElementById('information-text-2');
            var pFooter = document.getElementById('snippetFooterText');

            p1.innerHTML = 'Want to talk with us?<br>We are not available now,<br>but we can call you <strong>later</strong>';

            p2.innerHTML = '';

            pFooter.innerHTML = '';
            document.getElementById('selected-text-weekdays').innerText = snippet.allowed_date_times.weekDays[0];
        }


    }

};

checkSnippetTime (start, end);
setInterval (function () {
    checkSnippetTime(start, end);
},30000);


var submit = document.getElementById('clickToCallButton');

submit.onclick = function() {
    var data = {};
    data.token = token;
    data.recipient = document.getElementById('selected-text').getAttribute('data-phonenumber') + document.getElementById('snippet-phonenumber').value;

    if(submit.getAttribute('data-schedule')) {
        data.callburn_week_day = document.getElementById('selected-text-weekdays').innerText;
        data.callburn_hour = document.getElementById('at_input_hours').value;
        data.callburn_minute = document.getElementById('at_input_minutes').value;
        data.status = 'scheduled';
    } else {
        data.status = 'saved';
    }

    var request = new XMLHttpRequest();
    request.onreadystatechange = function()
    {
        if (this.readyState == 4 && this.status == 200){

            var response = JSON.parse(request.responseText);

        }
    };
    request.open('POST', baseUrl + '/ctc/create-message');
    request.setRequestHeader("Content-Type", "application/json");
    request.send(JSON.stringify(data));

};