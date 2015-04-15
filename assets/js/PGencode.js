
/* OpenPGP encryption using RSA/AES
 * Copyright 2005-2006 Herbert Hanewinkel, www.haneWIN.de
 * version 2.0, check www.haneWIN.de for the latest version

 * This software is provided as-is, without express or implied warranty.  
 * Permission to use, copy, modify, distribute or sell this software, with or
 * without fee, for any purpose and by any individual or organization, is hereby
 * granted, provided that the above copyright notice and this paragraph appear 
 * in all copies. Distribution as a part of an application or binary must
 * include the above copyright notice in the documentation and/or other
 * materials provided with the application or distribution.
 */

/* We need an unpredictable session key of 128 bits ( = 2^128 possible keys).
 * If we generate the session key with a PRNG from a small seed we get only
 * a small number of session keys, e.g. 4 bytes seed => 2^32 keys, a brute
 * force attack could try all 2^32 session keys. 
 * (see RFC 1750 - Randomness Recommendations for Security.)
 *
 * Sources for randomness in Javascript are limited.
 * We have load, exec time, seed from random(), mouse movement events
 * and the timing from key press events.
 * But even here we have restrictions.
 * - A mailer will add a timestamp to the encrypted message, therefore
 *   only the msecs from the clock can be seen as unpredictable.
 * - Because the Windows timer is still based on the old DOS timer,
 *   the msecs jump under Windows in 18.2 msecs steps.
 * - Only a few bits from mouse mouvement event coordinates are unpredictable,
 *   if the same buttons are clicked on the screen.
 */

var rnArray = new Array(256);
var rnNext = 0;
var rnRead = 0;

function randomByte() { return Math.round(Math.random()*255)&255; }
function timeByte() { return ((new Date().getTime())>>>2)&255; }

function rnTimer()
{
 var t = timeByte(); // load time

 for(var i=0; i<256; i++)
 {
  t ^= randomByte();
  rnArray[(rnNext++)&255] ^= t;
 } 
 window.setTimeout("rnTimer()",randomByte()|128);
}

// rnTimer() and mouseMoveCollect() are started on page load.

rnTimer();
eventsCollect();

// ----------------------------------------

function randomString(len, nozero)
{
 var r = '';
 var t = timeByte(); // exec time

 for(var i=0; i<len;)
 {
   t ^= rnArray[(rnRead++)&255]^mouseByte()^keyByte();
   if(t==0 && nozero) continue;
   i++;

   r+=String.fromCharCode(t);
 }
 return r;
}

// ----------------------------------------

function hex2s(hex)
{
 var r='';
 if(hex.length%2) hex+='0';

 for(var i = 0; i<hex.length; i += 2)
   r += String.fromCharCode(parseInt(hex.slice(i, i+2), 16));
 return r;
}

function crc24(data)
{
 var crc = 0xb704ce;

 for(var n=0; n<data.length;n++)
 {
   crc ^=(data.charCodeAt(n)&255)<<16;
   for(i=0;i<8;i++)
   {
    crc<<=1;
    if(crc & 0x1000000) crc^=0x1864cfb;
   }       
 }
 return String.fromCharCode((crc>>16)&255)
        +String.fromCharCode((crc>>8)&255)
        +String.fromCharCode(crc&255);
}

// --------------------------------------
// GPG CFB symmetric encryption using AES

var bpbl   = 16;         // bytes per data block

function GPGencrypt(key, text)
{
 var i, n;
 var len = text.length;
 var lsk = key.length;
 var iblock = new Array(bpbl)
 var rblock = new Array(bpbl);
 var ct = new Array(bpbl+2);
 var expandedKey = new Array();
 
 var ciphertext = '';

 // append zero padding
 if(len%bpbl)
 {
  for(i=(len%bpbl); i<bpbl; i++) text+='\0';
 }
 
 expandedKey = keyExpansion(key);

 // set up initialisation vector and random byte vector
 for(i=0; i<bpbl; i++)
 {
  iblock[i] = 0;
  rblock[i] = randomByte();
 }

 iblock = AESencrypt(iblock, expandedKey);
 for(i=0; i<bpbl; i++)
 {
  ct[i] = (iblock[i] ^= rblock[i]);
 }

 iblock = AESencrypt(iblock, expandedKey);
 // append check octets
 ct[bpbl]   = (iblock[0] ^ rblock[bpbl-2]);
 ct[bpbl+1] = (iblock[1] ^ rblock[bpbl-1]);
 
 for(i = 0; i < bpbl+2; i++) ciphertext += String.fromCharCode(ct[i]);

 // resync
 iblock = ct.slice(2, bpbl+2);

 for(n = 0; n < text.length; n+=bpbl)
 {
  iblock = AESencrypt(iblock, expandedKey);
  for(i = 0; i < bpbl; i++)
  {
   iblock[i] ^= text.charCodeAt(n+i);
   ciphertext += String.fromCharCode(iblock[i]);
  }
 }
 return ciphertext.substr(0,len+bpbl+2);
}

// ------------------------------
// GPG packet header (old format)

function GPGpkt(tag, len)
{
 if(len>255) tag +=1;
 var h = String.fromCharCode(tag);
 if(len>255) h+=String.fromCharCode(len/256);
 h += String.fromCharCode(len%256);
 return h;
}

// ----------------------------------------------
// GPG public key encryted session key packet (1)

function GPGpkesk(keyId, keytyp, symAlgo, sessionkey, pkey)
{ 
 var el = [3,5,9,17,513,2049,4097,8193];
 var mod=new Array();
 var exp=new Array();
 var enc='';
 
 var s = r2s(pkey);
 var l = Math.floor((s.charCodeAt(0)*256 + s.charCodeAt(1)+7)/8);

 mod = mpi2b(s.substr(0,l+2));

 if(keytyp)
 {
  var grp=new Array();
  var y  =new Array();
  var B  =new Array();
  var C  =new Array();

  var l2 = Math.floor((s.charCodeAt(l+2)*256 + s.charCodeAt(l+3)+7)/8)+2;

  grp = mpi2b(s.substr(l+2,l2));
  y   = mpi2b(s.substr(l+2+l2));
  exp[0] = 9; //el[randomByte()&7];
  B = bmodexp(grp,exp,mod);
  C = bmodexp(y,exp,mod);
 }
 else
 {
  exp = mpi2b(s.substr(l+2));
 }

 var lsk = sessionkey.length;

 // calculate checksum of session key
 var c = 0;
 for(var i = 0; i < lsk; i++) c += sessionkey.charCodeAt(i);
 c &= 0xffff;

 // create MPI from session key using PKCS-1 block type 02
 var lm = (l-2)*8+2;
 var m = String.fromCharCode(lm/256)+String.fromCharCode(lm%256)
   +String.fromCharCode(2)         // skip leading 0 for MPI
   +randomString(l-lsk-6,1)+'\0'   // add random padding (non-zero)
   +String.fromCharCode(symAlgo)+sessionkey
   +String.fromCharCode(c/256)+String.fromCharCode(c&255);

 if(keytyp)
 {
  // add Elgamal encrypted mpi values
   enc = b2mpi(B)+b2mpi(bmod(bmul(mpi2b(m),C),mod));

  return GPGpkt(0x84,enc.length+10)
   +String.fromCharCode(3)+keyId+String.fromCharCode(16)+enc;
 }
 else
 {
  // rsa encrypt the result and convert into mpi
  enc = b2mpi(bmodexp(mpi2b(m),exp,mod));

  return GPGpkt(0x84,enc.length+10)
   +String.fromCharCode(3)+keyId+String.fromCharCode(1)+enc;
 }
}

// ------------------------------------------
// GPG literal data packet (11) for text file

function GPGld(text)
{
 if(text.indexOf('\r\n') == -1)
   text = text.replace(/\n/g,'\r\n');
 return GPGpkt(0xAC,text.length+10)+'t'
   +String.fromCharCode(4)+'file\0\0\0\0'+text;
}

// -------------------------------------------
// GPG symmetrically encrypted data packet (9)

function GPGsed(key, text)
{
 var enc = GPGencrypt(key, GPGld(text));
 return GPGpkt(0xA4,enc.length)+enc;
}

// ------------------------------------------------

function doEncrypt(keyId,keytyp,pkey,text)
{
 var symAlg = 7;          // AES=7, AES192=8, AES256=9
 var kSize  = [16,24,32]  // key length in bytes

 var keylen = kSize[symAlg-7];  // session key length in bytes

 var sesskey = randomString(keylen,0);
 keyId = hex2s(keyId);
 var cp = GPGpkesk(keyId,keytyp,symAlg,sesskey,pkey)+GPGsed(sesskey,text);

 return '-----BEGIN PGP MESSAGE-----\nVersion: BitWasp Javascript PGP v1.0\n\n'
        +s2r(cp)+'\n='+s2r(crc24(cp))+'\n-----END PGP MESSAGE-----\n';
}
