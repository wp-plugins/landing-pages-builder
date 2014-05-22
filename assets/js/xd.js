// See: http://www.onlineaspect.com/2010/01/15/backwards-compatible-postmessage/
//
// ----- Parent page needs this JS -----
//
// // pass the URL of the current parent page to the iframe using location.hash
// src = 'http://joshfraser.com/code/postmessage/child.html#' + encodeURIComponent(document.location.href);
// document.getElementById("xd_frame").src = src;
// function send(msg) {
//     XD.postMessage(msg, src, frames[0]);
//     return false;
// }
// // setup a callback to handle the dispatched MessageEvent. if window.postMessage is supported the passed
// // event will have .data, .origin and .source properties. otherwise, it will only have the .data property.
// XD.receiveMessage(function(message){
//     window.alert(message.data + " received on "+window.location.host);
// }, '//www.wishpond.com');
//
//
//
// ----- Child page needs this JS -----
//
// // Get the parent page URL as it was passed in, for browsers that don't support
// // window.postMessage (this URL could be hard-coded).
// var parent_url = decodeURIComponent(document.location.hash.replace(/^#/, ''));
// function send(msg) {
//     XD.postMessage(msg, parent_url, parent);
//     return false;
// }
// XD.receiveMessage(function(message){
//     window.alert(message.data + " received on "+window.location.host);
// }, '//[their domain name]');
//
//
//
// everything is wrapped in the XD function to reduce namespace collisions

var XD = function(){

  var interval_id,
  last_hash,
  cache_bust = 1,
  attached_callback,
  window = this;

  return {
    postMessage : function(message, target_url, target) {
      if (!target_url) {
        return;
      }
      target = target || parent;  // default to parent
      if (window['postMessage']) {
        // the browser supports window.postMessage, so call it with a targetOrigin
        // set appropriately, based on the target_url parameter.
        target['postMessage'](message, target_url.replace( /([^:]+:\/\/[^\/]+).*/, '$1'));
      } else if (target_url) {
        // the browser does not support window.postMessage, so use the window.location.hash fragment hack
        target.location = target_url.replace(/#.*$/, '') + '#' + (+new Date) + (cache_bust++) + '&' + message;
      }
    },
    receiveMessage : function(callback, source_origin) {
      // browser supports window.postMessage
      if (window['postMessage']) {
        // bind the callback to the actual event associated with window.postMessage
        if (callback) {
          attached_callback = function(e) {
            if ((typeof source_origin === 'string' && e.origin !== source_origin)
            || (Object.prototype.toString.call(source_origin) === "[object Function]" && source_origin(e.origin) === !1)) {
               return !1;
             }
             callback(e);
           };
         }
         if (window['addEventListener']) {
           window[callback ? 'addEventListener' : 'removeEventListener']('message', attached_callback, !1);
         } else {
           window[callback ? 'attachEvent' : 'detachEvent']('onmessage', attached_callback);
         }
       } else {
         // a polling loop is started & callback is called whenever the location.hash changes
         interval_id && clearInterval(interval_id);
         interval_id = null;
         if (callback) {
           interval_id = setInterval(function() {
             var hash = document.location.hash,
             re = /^#?\d+&/;
             if (hash !== last_hash && re.test(hash)) {
               last_hash = hash;
               callback({data: hash.replace(re, '')});
             }
           }, 100);
         }
       }
     }
  };
}();
