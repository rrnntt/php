var IE = 'no';
if (navigator.appName == 'Microsoft Internet Explorer')
{
var IE = 'yes';
}

function encode(text)
{
	var encoded = text.replace(/\+/g,"%2B");
	encoded = encoded.replace(/\ /g,"%20");
	return encoded;
}

function setWindowSize(winW,winH)
{
if (document.body && document.body.offsetWidth) 
{
 winW = document.body.offsetWidth;
 winH = document.body.offsetHeight;
}
if (document.compatMode=='CSS1Compat' &&
    document.documentElement &&
    document.documentElement.offsetWidth ) {
 winW = document.documentElement.offsetWidth;
 winH = document.documentElement.offsetHeight;
}
if (window.innerWidth && window.innerHeight) {
 winW = window.innerWidth;
 winH = window.innerHeight;
}
}

function windowWidth()
{
  if (document.body && document.body.offsetWidth) 
  {
    return document.body.offsetWidth;
  }
  if (document.compatMode=='CSS1Compat' &&
    document.documentElement &&
    document.documentElement.offsetWidth ) 
  {
   return document.documentElement.offsetWidth;
   }
  if (window.innerWidth) 
  {
    return window.innerWidth;
  }
}

/**
 * Browser independent window height
 */
function windowHeight()
{
  if (document.body && document.body.offsetHeight) 
  {
    return document.body.offsetHeight;
  }
  if (document.compatMode=='CSS1Compat' &&
    document.documentElement &&
    document.documentElement.offsetHeight ) 
  {
   return document.documentElement.offsetHeight;
   }
  if (window.innerHeight) 
  {
    return window.innerHeight;
  }
}

/**
 * Construct array of n doubles in interval [xmin,xmax]
 */
function range(xmin,xmax,n)
{
	if (n == 1) return [xmin,xmax];
	var x = [];
	var dx = (xmax - xmin) / (n - 1);
	for(var i = 0; i < n; ++i)
	{
		x.push(xmin + dx * i);
	}
	return x;
}
