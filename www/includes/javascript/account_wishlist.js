function checkChilds(el) {
  var fname = document.account_wishlist;
  for (i=0; i<fname.elements.length; i++) {
	if (fname.elements[i].type=='checkbox') {
	  if (fname.elements[i].name.indexOf("categories_"+el+"_")>=0) {
		if (document.getElementById("wls"+el).style.display=="none") {
		  fname.elements[i].checked = false;
		} else {
		  fname.elements[i].checked = document.getElementById("wlc"+el).checked;
		}
	  }
	}
  }
//  if (document.getElementById("wlc"+el).checked) document.getElementById("wlc"+el).disabled = true;
}

function loadLevel(url, el, level) {
  if (document.getElementById("wls"+el).style.display=="none") {
	document.getElementById("wls"+el).style.display = "block";
	if (document.getElementById("wls"+el).innerHTML=="") {
	  getXMLDOM(url, "wls"+el);
	}
	document.getElementById("wlh"+el).innerHTML = "&nbsp;&ndash;&nbsp;";
  } else {
	document.getElementById("wls"+el).style.display = "none";
	document.getElementById("wlh"+el).innerHTML = "&nbsp;+&nbsp;";
  }
  checkChilds(el, document.getElementById("wlc"+el).checked);
}

