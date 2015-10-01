var divs = new Array('info', 'cognitive', 'composition');
function mouseOverEffect(object) {
  for (i=0, n=divs.length; i<divs.length; i++, n--) {
	document.getElementById('title_'+divs[i]).className = 'bset';
	document.getElementById('title_'+divs[i]).style.zIndex = n;
  }
  object.style.zIndex = 10;
  object.className = 'bset_active';
}

function mouseOutEffect(object, zIndex) {
  for (i=0, n=divs.length; i<divs.length; i++, n--) {
	if (document.getElementById(divs[i]).style.display=='none') {
	  document.getElementById('title_'+divs[i]).style.zIndex = n;
	  document.getElementById('title_'+divs[i]).className = 'bset';
	} else {
	  if (divs[i]=='info' && (document.getElementById('cognitive').style.display!='none' || document.getElementById('composition').style.display!='none') ) {
	  } else {
		document.getElementById('title_'+divs[i]).style.zIndex = 10;
		document.getElementById('title_'+divs[i]).className = 'bset_active';
	  }
	}
  }
}

function mouseClickEffect(object, divName) {
  for (i=0, n=divs.length; i<divs.length; i++, n--) {
	if (divs[i]!='info') document.getElementById(divs[i]).style.display = 'none';
	document.getElementById('title_'+divs[i]).className = 'bset';
	document.getElementById('title_'+divs[i]).style.zIndex = n;
  }
  object.style.zIndex = 10;
  if (document.getElementById(divName)) {
	document.getElementById(divName).style.display = 'block';
	object.className = (document.getElementById(divName).style.display=='none' ? 'bset' : 'bset_active');
  }
}