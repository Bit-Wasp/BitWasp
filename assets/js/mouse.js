    
/* Collect entropy from mouse motion and key press events
 * Note that this is coded to work with either DOM2 or Internet Explorer
 * style events.
 * We don't use every successive mouse movement event.
 * Instead, we use some bits from random() to determine how many
 * subsequent mouse movements we ignore before capturing the next one.
 * rc4 is used as a mixing function for the captured mouse events.  
 *
 * mouse motion event code originally from John Walker
 * key press timing code thanks to Nigel Johnstone
 */

var oldKeyHandler;    // For saving and restoring key press handler in IE4
var keyRead = 0;
var keyNext = 0;
var keyArray = new Array(256);
	
var mouseMoveSkip = 0; // Delay counter for mouse entropy collection
var oldMoveHandler;    // For saving and restoring mouse move handler in IE4
var mouseRead = 0;
var mouseNext = 0;
var mouseArray = new Array(256);

// ----------------------------------------

var s=new Array(256);
var x, y;

function rc4Init()
{
 var i, t;
 var key = new Array(256);

 for(i=0; i<256; i++)
 {
  s[i]=i;
  key[i] = randomByte()^timeByte();
 }

 y=0;
 for(i=0; i<2; i++)
 {
  for(x=0; x<256; x++)
  {
   y=(key[i] + s[x] + y) % 256;
   t=s[x]; s[x]=s[y]; s[y]=t;
  }
 }
 x=0;
 y=0;
}

function rc4Next(b)
{
 var t, x2;

 x=(x+1) & 255; 
 y=(s[x] + y) & 255;
 t=s[x]; s[x]=s[y]; s[y]=t;
 return (b ^ s[(s[x] + s[y]) % 256]) & 255; 
}

// ----------------------------------------
    
function keyByte() { return keyArray[(keyRead++)%keyNext]; }
function keyPressEntropy(e) { keyArray[(keyNext++)%256] ^= timeByte(); }

function mouseByte() { return mouseArray[(mouseRead++)%mouseNext]; }
function mouseMoveEntropy(e)
{
 var c;

 if (!e) { e = window.event; }	    // Internet Explorer event model

 if(mouseMoveSkip-- <= 0)
 {
  if(oldMoveHandler) { c = ((e.clientX << 4) | (e.clientY & 15)); }
  else { c = ((e.screenX << 4) | (e.screenY & 15)); }

  mouseArray[(mouseNext++)%256] ^= rc4Next(c&255);
  mouseArray[(mouseNext++)%256] ^= rc4Next(timeByte());
  mouseMoveSkip = randomByte() & 7;
 }
}

// ----------------------------------------

function eventsEnd()
{
 if(document.removeEventListener)
 {
  document.removeEventListener("mousemove", mouseMoveEntropy, false);
  document.removeEventListener("keypress", keyPressEntropy, false);
 }
 else if(document.detachEvent)
 {
  document.detachEvent("onmousemove", mouseMoveEntropy);
  document.detachEvent("onkeypress", keyPressEntropy);
 }
 else if(document.releaseEvents)
 {
  document.releaseEvents(EVENT.MOUSEMOVE); document.onMousemove = 0;
  document.releaseEvents(EVENT.KEYPRESS); document.onKeypress = 0;
 }
 else
 {
  document.onMousemove = oldMoveHandler;
  document.onKeypress = oldKeyHandler;
 }
}

// Start collection of entropy.
	
function eventsCollect()
{
 if((document.implementation.hasFeature("Events", "2.0"))
  && document.addEventListener) // Document Object Model (DOM) 2 events
 {
  document.addEventListener("mousemove", mouseMoveEntropy, false);
  document.addEventListener("keypress", keyPressEntropy, false);
 }
 else if(document.attachEvent) // IE 5 and above event model
 {
  document.attachEvent("onmousemove", mouseMoveEntropy);
  document.attachEvent("onkeypress", keyPressEntropy);
 }
 else if(document.captureEvents) // Netscape 4.0
 {
  document.captureEvents(Event.MOUSEMOVE);
  document.onMousemove = mouseMoveEntropy;
  document.captureEvents(Event.KEYPRESS);
  document.onMousemove = keyPressEntropy;
 }
 else // IE 4 event model
 {
  oldMoveHandler = document.onmousemove;
  document.onMousemove = mouseMoveEntropy;
  oldKeyHandler = document.onkeypress;
  document.onKeypress = keyPressEntropy;
 }

 rc4Init();
}
