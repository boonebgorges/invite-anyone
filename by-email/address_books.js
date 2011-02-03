/* This javascript was written by the folks at Cloudsponge with a few changes by Boone */
/* See https://api.cloudsponge.com/address_books.js */

addJavascript('https://csponge-production.s3.amazonaws.com/javascripts/easyXDM/easyXDM.min.js?1296510503', 'head');
addJavascript('https://csponge-production.s3.amazonaws.com/javascripts/address_books/floatbox.js?1296510503', 'head');
addCss('https://csponge-production.s3.amazonaws.com/javascripts/address_books/floatbox.css?1296510503', 'head');

fbPageOptions = {afterFBLoaded:includeFBOptions};
var socket = null;
var csOptions = null;

defaultOptions = {include:['name','email']};

/* Public Entrypoint */
function csInit(options) {
  csOptions = options;
  if (!csOptions.include)
    csOptions.include = defaultOptions.include;
  addJavascript('https://api.cloudsponge.com/ab.js?' + optionsToQueryString(csOptions), 'head');
}

/* Floatbox Callback Handlers */
function includeFBOptions() {
  addJavascript('https://csponge-production.s3.amazonaws.com/javascripts/address_books/options.js?1296510503', 'head');
  checkEasyXDMReady();
}

function brandFloatbox() {
    /*@cc_on
        var appVer = navigator.appVersion;
        if (parseInt(appVer.substr(appVer.indexOf('MSIE') + 5), 10) < 7) return;
    @*/
    var div = document.createElement('div');
    div.id = 'brand';
    fb.setInnerHTML(div, '<a href="http://www.cloudsponge.com/" onclick="window.open(this.href);return false;" style="float:right;margin-top:20px;border:none;border-width:0;"><img alt=\"Powered_by-grey\" onmouseout=\"this.src=\'https://csponge-production.s3.amazonaws.com/images/address_books/powered_by-grey.png?1296510503\'\" onmouseover=\"this.src=\'https://csponge-production.s3.amazonaws.com/images/address_books/powered_by-color.png?1296510503\'\" src=\"https://csponge-production.s3.amazonaws.com/images/address_books/powered_by-grey.png?1296510503\" /></a><span style="float:right;color:white;font-size:10px;font-family:verdana;margin-top:23px;margin-right:10px">powered by</span>');
    fb.fbBox.appendChild(div);
}

function unbrandFloatbox() {
    /*@cc_on
        var appVer = navigator.appVersion;
        if (parseInt(appVer.substr(appVer.indexOf('MSIE') + 5), 10) < 7) return;
    @*/
    fb$('brand').style.display = 'none';
}

function loadFloatbox() {
  fb$('cs_container').innerHTML = '<iframe name="cs_container_frame" src="https://api.cloudsponge.com/address_books?domain_key='+ csOptions["domain_key"] + '" style="width:100%;height:100%" frameborder="0" onload="resizeIframe(this);"></iframe>';
  return true;
};

function resizeIframe(f) {
  f.style.height = fb$('fbContentWrapper').style.height;
}

function unloadFloatbox() {
  document.getElementById('cs_container').innerHTML = "";
  return true;
};

function endingFloatbox() {
  if (socket) socket.postMessage('ending');
}

/* Setup Function */
function easyXDMReady() {
  link_visible = false;
  // create the socket before enabling any widget links
  socket = new easyXDM.Socket({
    remote: 'https://api.cloudsponge.com/address_books/provider?domain_key='+ csOptions["domain_key"], // the path to the provider
    onMessage:function(message, origin) {
      fb.end();
      
      message = eval(message);
      // determine if the include option excludes 'name'
      var email_only = (csOptions.include.toString().indexOf('name') < 0);
      contacts = [];
      for (i = 0; i < message.length; i++) {
        if (email_only)
          contacts[i] = message[i].email;
        else
          contacts[i] = formatRecipient(message[i].name, message[i].email);
      }
      response_value = contacts.join("\n");
      
      // Return the contacts string to the list_elememt_id if it is defined and exists:
      assignValue('textarea_id', response_value);
      
      // Pass the email addresses as a string back to the customer page:
      // populate the email address text box with the contents
      if (typeof(onImportComplete) != "undefined") {
        onImportComplete(response_value);
      }
    }
  });
  
  // Older version supporting div elements
  var elements = document.getElementsByTagName('div');
  for (i = 0; i < elements.length; i++) {
    var element = elements[i];
    if (element.className == "cs_import" && element.getAttribute('data-cs-init') == null) {
      link_visible = true;
      var a = document.createElement('a');
      setCSAnchorAttributes(a);
      a.innerHTML = "Add from Address Book";
      element.appendChild(a);
    }
  }
  
  // the new way, supporting anchor elements with class='cs_import'
  var elements = document.getElementsByTagName("a");
  for (i = 0; i < elements.length; i++) {
    var element = elements[i];
    if (element.className == "cs_import" && element.getAttribute('data-cs-init') == null) {
      link_visible = true;
      setCSAnchorAttributes(element);
    }
  }

  // always add the hidden div so that the widget can be launched from js, even if no links exist on the page.
  var b = document.getElementsByTagName('body')[0];
  var d = document.createElement('div');
  d.setAttribute('id', 'cs_container');
  d.style.display = "none";
  b.appendChild(d);
  
  // always create a link to use for launching the floatbox
  // FIXME: unable to launch the floatbox with fb.start('#cs_container'), like I want to
  var a = document.createElement('a');
  setCSAnchorAttributes(a);
  a.id = 'cs_link';
  a.style.display = "none";
  b.appendChild(a);
  
  tryCallback('afterInit');
};


function tryCallback(callback, arguments) {
  if(typeof(csOptions[callback]) != "undefined") {
    try { csOptions[callback](arguments); }
    catch(e) { csWarn('Attempt to invoke callback [' + callback + '] failed: ' + e); }
  }
}

/* Utility Functions */
function assignValue(id, value) {
  list_element = fb$(csOptions[id]);
  var a = list_element.value;
  value = a + "\n" + value;
  if (list_element) list_element.value = value;
}

function setCSAnchorAttributes(a) {
  a.setAttribute('data-cs-init', true);
  a.setAttribute('href', '#cs_container');
  a.onclick = function() {
    return csLaunch();
  };
}

function csLaunchWithTracking() {
  try {
    var myTracker=_gat._getTrackerByName();
    _gaq.push(['myTracker._trackEvent', 'widget', 'launch']);
    setTimeout(csLaunch, 100);
  } catch(err) {}
}

function csLaunch() {
  fb.start(fb$("cs_link"), "innerBorder:0 type:iframe outsideClickCloses:false afterItemStart:`loadFloatbox();brandFloatbox();` padding:20 enableKeyboardNav:false beforeBoxEnd:`endingFloatbox();unbrandFloatbox();` controlsPos:tr width:410 afterBoxEnd:`unloadFloatbox();` scrolling:no resizeDuration:2.5 height:487 outerBorder:1 startAtClick:false disableScroll:true");
  return false;
}

function optionsToQueryString(hashOfOptions) {
  var params = [];
  var i = 0;
  for (key in hashOfOptions) {
    params[i++] = key + "=" + escape(hashOfOptions[key]);
  }
  return params.join("&");
}

function addJavascript(jsname, pos) {
  var th = document.getElementsByTagName(pos)[0];
  var s = document.createElement('script');
  s.setAttribute('type', 'text/javascript');
  s.setAttribute('src', jsname);
  th.appendChild(s);
}

function addCss(cssname, pos) {
  var th = document.getElementsByTagName(pos)[0];
  var s = document.createElement('link');
  s.setAttribute('type', 'text/css');
  s.setAttribute('rel', 'stylesheet');
  s.setAttribute('media', 'screen');
  s.setAttribute('href', cssname);
  th.appendChild(s);
}

function formatRecipient(name, email) { 
//  if (name && name.trim().length > 0)
  if (name && name.replace(/^\s+/, "").replace(/\s+$/, "").length > 0)
    return '"' + name.replace('"', "&quot;") + '" <' + email + '>';
  else
    return email;
}



var totalWaitTime = 0, waitInterval = 250;
function checkEasyXDMReady() {
  if (typeof(easyXDM) != 'undefined') {
    easyXDMReady();
  } else {
    if (totalWaitTime++ < 20)
      setTimeout(checkEasyXDMReady, 250);
    else
      csWarn('Unable to load easyXDM, cannot initialize widget.');
  }
}

function csWarn(log_txt) {
  if (window.console != undefined) {
    console.log(log_txt);
  }
}