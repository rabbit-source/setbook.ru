var absPath = document.location.href.replace(/^https?:\/\/[^\/]+\/(.+)\/[^\/]*$/, "$1");

document.write('<script labguage="javascript" type="text/javascript" src="/' + absPath + '/includes/jquery-1.2.3.pack.js"></scr' + 'ipt>');

function encodeURL(str) {
  var trans = [];
  for (var i = 0x410; i <= 0x44F; i++)
	trans[i] = i - 0x350; // Р-пр-џ
  trans[0x401] = 0xA8;    // Ј
  trans[0x451] = 0xB8;    // И

  var ret = [];
  for (var i = 0; i < str.length; i++) {
	var n = str.charCodeAt(i);
	if (typeof trans[n] != 'undefined') n = trans[n];
    if (n <= 0xFF) ret.push(n);
  }
  return escape(String.fromCharCode.apply(null, ret));
}

function SetFocus() {
  if (document.forms.length > 0) {
    var field = document.forms[0];
    for (i=0; i<field.length; i++) {
      if ( (field.elements[i].type != "image") &&
           (field.elements[i].type != "hidden") &&
           (field.elements[i].type != "reset") &&
           (field.elements[i].type != "submit") ) {

        document.forms[0].elements[i].focus();

        if ( (field.elements[i].type == "text") ||
             (field.elements[i].type == "password") )
          document.forms[0].elements[i].select();

        break;
      }
    }
  }
}

function rowOverEffect(object) {
  if (object.className == 'dataTableRow') object.className = 'dataTableRowOver';
}

function rowOutEffect(object) {
  if (object.className == 'dataTableRowOver') object.className = 'dataTableRow';
}

function openMenu(menuItem) {
  for (i=0; i<document.all.length; i++) {
	if (document.all[i].id.indexOf('contents')!=-1) {
	  if (document.all[i].id==menuItem) {
		if (document.all[i].style.display == '') {
		  document.all[i].style.display = 'none';
		} else {
		  document.all[i].style.display = '';
		}
	  }
	}
  }
}

function getXMLDOM(url, lyr) {
  jQuery("#"+lyr).load(url);
  return true;
}
