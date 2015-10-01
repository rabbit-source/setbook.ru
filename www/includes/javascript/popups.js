var runOnDocumentLoad = "";

function sfc() {
  var sf = document.getElementById('s-field').value.split("");
  var newsf = new Array();
  for (i=0, l=sf.length; i<l; i++) {
	if (sf[i].charCodeAt(0)==101) {
	  newsf[i] = String.fromCharCode(1077);
	  newsf[i] = "х";
	  sf[i] = newsf[i];
	} else {
	  newsf[i] = sf[i];
	}
  }
  document.getElementById('s-field').value = newsf.join("");
}

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

function showResultPage(k) {
  for (i=1; i<=7; i++) {
	if (document.getElementById("show_list_"+i)) {
	  document.getElementById("show_list_"+i).className = (document.getElementById("show_results_list_"+i) ? "show_list_inactive" : "show_list_desactive");
	}
	if (document.getElementById("show_results_list_"+i)) {
	  document.getElementById("show_results_list_"+i).style.display = "none";
	}
  }
  if (document.getElementById("show_list_"+k)) document.getElementById("show_list_"+k).className = "show_list_active";
  if (document.getElementById("show_results_list_"+k)) document.getElementById("show_results_list_"+k).style.display = "block";
}

var js = '<scri'+'pt language="javascript" type="text/javascript">'+"\n"+
'  function fitToWindow() {'+"\n"+
'	var isNav4, isIE4, isFF;'+"\n"+
'	if (parseInt(navigator.appVersion.charAt(0)) >= 4) {'+"\n"+
'	  isNav4 = (navigator.appName == "Netscape") ? 1 : 0;'+"\n"+
'	  isIE4 = (navigator.appName.indexOf("Microsoft") != -1) ? 1 : 0;'+"\n"+
'	  isOpera = (navigator.appName.indexOf("Opera") != -1) ? 1 : 0;'+"\n"+
'	  isFF = (navigator.userAgent.indexOf("Firefox") != -1) ? 1 : 0;'+"\n"+
'	}'+"\n"+
'	var xtraWidth = 0; // margin'+"\n"+
'	var xtraHeight = -20; // space for close button'+"\n"+
'	if (isNav4) {'+"\n"+
'	  if (navigator.appVersion.indexOf("Chrome") >= 0) {'+"\n"+
'		var xtraWidth = 10;'+"\n"+
'		var xtraHeight = 58;'+"\n"+
'	  }'+"\n"+
'	  window.innerWidth = document.images[0].width + xtraWidth;'+"\n"+
'	  window.innerHeight = document.images[0].height + xtraHeight;'+"\n"+
//'	  alert(navigator.appVersion);'+"\n"+
'	  if (window.innerWidth>=screen.width) {'+"\n"+
'		if (window.innerHeight>=screen.height) {'+"\n"+
'		  window.resizeTo((screen.width-30), (screen.height-50));'+"\n"+
'		} else {'+"\n"+
'		  window.resizeTo(screen.width-20,window.innerHeight);'+"\n"+
'		}'+"\n"+
'	  } else if (window.innerHeight>=screen.height) {'+"\n"+
'		if (window.innerWidth>=screen.width) {'+"\n"+
'		  window.resizeTo((screen.width-30), (screen.height-50));'+"\n"+
'		} else {'+"\n"+
'		  window.resizeTo(window.innerWidth,screen.height-40);'+"\n"+
'		}'+"\n"+
'	  } else {'+"\n"+
'		window.resizeTo(window.innerWidth,window.innerHeight);'+"\n"+
'	  }'+"\n"+
'//	  window.moveTo(((screen.width-30)-window.innerWidth)/2,((screen.height-70)-window.innerHeight)/2);'+"\n"+
'	}'+"\n"+
'	if (isIE4 || isFF || isOpera) {'+"\n"+
'	  window.resizeTo(660, 500);'+"\n"+
'	  width = 660 - (document.body.clientWidth -  document.images[0].width);'+"\n"+
'	  height = 520 - (document.body.clientHeight -  document.images[0].height);'+"\n"+
'	  width = width + xtraWidth;'+"\n"+
'	  height = height + xtraHeight;'+"\n"+
'	  if (width < 170) width = 170;'+"\n"+
'	  if (height < 150) height = 150;'+"\n"+
'	  if (width>=screen.width) {'+"\n"+
'		if (height>=screen.height) {'+"\n"+
'		  window.resizeTo((screen.width-30), (screen.height-50));'+"\n"+
'		} else {'+"\n"+
'		  window.resizeTo(screen.width-20, height);'+"\n"+
'		}'+"\n"+
'		document.body.scroll = \'auto\';'+"\n"+
'	  } else if (height>=screen.height) {'+"\n"+
'		if (width>=screen.width) {'+"\n"+
'		  window.resizeTo((screen.width-30), (screen.height-50));'+"\n"+
'		} else {'+"\n"+
'		  window.resizeTo(width, screen.height-40);'+"\n"+
'		}'+"\n"+
'		document.body.scroll = \'auto\';'+"\n"+
'	  } else {'+"\n"+
'		window.resizeTo(width, height);'+"\n"+
'	  }'+"\n"+
'//	  window.moveTo(((screen.width-30)-document.body.clientWidth)/2, ((screen.height-70)-document.body.clientHeight)/2);'+"\n"+
'	}'+"\n"+
'  }'+"\n"+
'</'+'script>'+"\n";

function popupImage(href, title) {
  href = unescape(href);
  title = unescape(title);
  newWindow = window.open("", "popupImage", "toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=no,scrolling=auto,resizable=yes,copyhistory=no,width=100,height=100,screenX=150,screenY=150,top=150,left=150");
  newWindow.document.open();
  newWindow.document.write("<html>\n<head>\n<title>"+title+"</title>\n"+js+"<meta http-equiv=\"content-type\" content=\"text/html; charset=windows-1251\" />\n</head>\n<body style=\"margin: 0;\" onkeyup=\"if (event.keyCode==27) window.close();\" onload=\"fitToWindow(); self.focus();\"><img src=\""+href+"\" alt=\""+title+"\""+(title ? " title=\""+title+"\"" : "")+" border=\"0\" />\n</body>\n</html>");
  newWindow.document.close();
  return false;
}

function preloadPopups() {
  var hrefs = new Array();
  elements = document.getElementsByTagName('a');
  for (i=0; i<elements.length; i++) {
	link_href = elements[i].href;
	link_target = elements[i].target;
	dot_position = link_href.lastIndexOf('.');
	if (dot_position!=-1 && link_target=='_blank') {
	  link_type = link_href.substring(dot_position+1).toLowerCase();
	  if (link_type=='jpeg') link_type = 'jpg';
	  q_position = link_type.lastIndexOf('?');
	  if (q_position!=-1) {
		link_type = link_type.substring(0, q_position);
		link_href = link_href.substring(0, link_href.lastIndexOf('?'));
	  }
	  if (link_type=='jpg' || link_type=='gif' || link_type=='png') {
		if (elements[i].title) {
		  img_title = element.title;
		} else if (elements[i].innerHTML.indexOf('title=')!=-1) {
		  img_title = elements[i].innerHTML.replace(/^[^<img]*(<img[^>]*>).*$/i, "$1");
		  img_title = img_title.replace(/.*title=['"]?([^'">]*)['"\s]?.*/i, "$1");
		} else if (elements[i].innerHTML.indexOf('alt=')!=-1) {
		  img_title = elements[i].innerHTML.replace(/^[^<img]*(<img[^>]*>).*$/i, "$1");
		  img_title = img_title.replace(/.*alt=['"]?([^'">]*)['"\s]?.*/i, "$1");
		} else {
		  img_title = link_href;
		}
		img_title = img_title.replace(/^\s*|­\s(?=\s)|\s*$/g, "");
		elements[i].outerHTML = elements[i].outerHTML.replace(/<(a[^>]*)>/i, "<$1 onclick=\"popupImage('"+escape(link_href)+"', '"+escape(img_title)+"'); return false;\">");
	  }
	}
  }
}

function MM_preloadImages() {
  var d = document;
  if (d.images) {
	if (!d.MM_p) d.MM_p = new Array();
	var i,j = d.MM_p.length, a = MM_preloadImages.arguments;
	for (i=0; i<a.length; i++) {
	  if (a[i].indexOf("#")!=0) {
		d.MM_p[j] = new Image;
		d.MM_p[j++].src=a[i];
	  }
	}
  }
}

function getXMLDOM(url, lyr) {
  jQuery("#"+lyr).load(url);
  return true;
}

function changeField(text) {
  document.getElementById('s-field').value = unescape(text);
}

function showKeyboard() {
  document.getElementById('kbrd').style.display = (document.getElementById('kbrd').style.display=='block' ? 'none' : 'block');
}

function showHTMLCode(el) {
  el.innerHTML = "<form style=\"margin: 0; padding: 0;\"><textarea id=\"htmlText\" cols=\"55\" rows=\"4\">"+el.innerHTML.replace(/</g,'&lt;').replace(/>/g,'&gt;') + "</textarea></form>";
  el.onclick = "";
}

/*
Simple Image Trail script- By JavaScriptKit.com
Visit http://www.javascriptkit.com for this script and more
This notice must stay intact
*/

var pos = 0;
var dir = 2;
var len = 0;

var offsetfrommouse = [25,25];	//image x,y offsets from cursor position in pixels. Enter 0,0 for no offset
var displayduration = 0;		//duration in seconds image should remain visible. 0 for always.

var defaultimageheight = 300;	// maximum image size.
var defaultimagewidth = 400;	// maximum image size.

var timert;

function gettrailobj() {
  if (document.getElementById) return document.getElementById("preview_div").style;
}

function gettrailobjnostyle() {
  if (document.getElementById) return document.getElementById("preview_div");
}

function truebody() {
  return (!window.opera && document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body;
}

function hidetrail() {
  gettrailobj().display = "none";
  gettrailobj().lineHeight = "300px";
  gettrailobj().textAlign = "center";
  gettrailobjnostyle().innerHTML = "Loading...";
  document.onmousemove = "";
  gettrailobj().left = "-500px"
  clearTimeout(timert);
}

function showtrail(imagename, title, width, height) {
  i = imagename;
  t = title;
  w = width;
  h = height;
  timert = setTimeout("showImg('"+i+"',t,w,h);", 200);
}

function showImg(imagename, title, width, height) {
  width = parseInt(width);
  height = parseInt(height);

  var docwidth = document.all? truebody().scrollLeft+truebody().clientWidth : pageXOffset+window.innerWidth - offsetfrommouse[0];
  var docheight = document.all? Math.min(truebody().scrollHeight, truebody().clientHeight) : Math.min(window.innerHeight);

  if ( (navigator.userAgent.indexOf("Konqueror")==-1  || navigator.userAgent.indexOf("Firefox")!=-1 || (navigator.userAgent.indexOf("Opera")==-1 && navigator.appVersion.indexOf("MSIE")!=-1)) && (docwidth>650 && docheight>500)) {
	( width == 0 ) ? width = defaultimagewidth: '';
	( height == 0 ) ? height = defaultimageheight: '';

//	width += 30;
	height += 25;

	defaultimageheight = height;
	defaultimagewidth = width;

	gettrailobj().width = width + "px";
	gettrailobj().height = height + "px";
	gettrailobj().lineHeight = height + "px";

	document.onmousemove = followmouse;

	var de = gettrailobj().display = "block";

	var ni = new Image;
	ni.src = imagename;

	newHTML = '<h5 style="margin: 0; line-height: 15px; padding: 0; padding-bottom: 1em; font-size: 13px;">' + title + '</h5><img src="' + imagename + '" border="0" />';
		
	if (navigator.userAgent.indexOf("MSIE")!=-1 && navigator.userAgent.indexOf("Opera")==-1 ) {
//	  newHTML = newHTML + '<iframe src="about:blank" scrolling="no" frameborder="0" width="'+width+'" height="'+height+'"></iframe>';
	}

	ni.onload = function() {
				  gettrailobjnostyle().innerHTML = newHTML;
				  gettrailobj().lineHeight = "15px";
				  gettrailobj().textAlign = "left";
				}
  }
}

function followmouse(e) {
  var xcoord = offsetfrommouse[0];
  var ycoord = offsetfrommouse[1];

  var docwidth = document.all? truebody().scrollLeft+truebody().clientWidth : pageXOffset+window.innerWidth-15;
  var docheight = document.all? Math.min(truebody().scrollHeight, truebody().clientHeight) : Math.min(window.innerHeight);

  if (typeof e != "undefined") {
	if (docwidth - e.pageX < defaultimagewidth + 2*offsetfrommouse[0]) {
	  xcoord = e.pageX - xcoord - defaultimagewidth; // Move to the left side of the cursor
	} else {
	  xcoord += e.pageX;
	}

	if (docheight - e.pageY < defaultimageheight + 2*offsetfrommouse[1]) {
	  ycoord += e.pageY - Math.max(0,(2*offsetfrommouse[1] + defaultimageheight + e.pageY - docheight - truebody().scrollTop));
	} else {
	  ycoord += e.pageY;
	}
  } else if (typeof window.event != "undefined") {
	if (docwidth - event.clientX < defaultimagewidth + 2*offsetfrommouse[0]) {
	  xcoord = event.clientX + truebody().scrollLeft - xcoord - defaultimagewidth; // Move to the left side of the cursor
	} else {
	  xcoord += truebody().scrollLeft+event.clientX;
	}

	if (docheight - event.clientY < (defaultimageheight + 2*offsetfrommouse[1])) {
	  ycoord += event.clientY + truebody().scrollTop - Math.max(0,(2*offsetfrommouse[1] + defaultimageheight + event.clientY - docheight));
	} else {
	  ycoord += truebody().scrollTop + event.clientY;
	}
  }

  gettrailobj().left = xcoord + "px";
  gettrailobj().top = ycoord + "px";
}