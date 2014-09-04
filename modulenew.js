M.filter_chemdoodle = {}
M.filter_chemdoodle = {

 file_get_contents: function(Y, url, flags, context, offset, maxLen) {


//function file_get_contents (url, flags, context, offset, maxLen) {
  // Read the entire file into a string
  //
  // version: 906.111
  // discuss at: http://phpjs.org/functions/file_get_contents
  // +   original by: Legaev Andrey
  // +      input by: Jani Hartikainen
  // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   improved by: Brett Zamir (http://brett-zamir.me)
  // +   input by: Raphael (Ao) RUDLER
  // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
  // %        note 1: This function uses XmlHttpRequest and cannot retrieve resource from different domain without modifications.
  // %        note 2: Synchronous by default (as in PHP) so may lock up browser. Can
  // %        note 2: get async by setting a custom "phpjs.async" property to true and "notification" for an
  // %        note 2: optional callback (both as context params, with responseText, and other JS-specific
  // %        note 2: request properties available via 'this'). Note that file_get_contents() will not return the text
  // %        note 2: in such a case (use this.responseText within the callback). Or, consider using
  // %        note 2: jQuery's: $('#divId').load('http://url') instead.
  // %        note 3: The context argument is only implemented for http, and only partially (see below for
  // %        note 3: "Presently unimplemented HTTP context options"); also the arguments passed to
  // %        note 3: notification are incomplete
  // *     example 1: file_get_contents('http://kevin.vanzonneveld.net/pj_test_supportfile_1.htm');
  // *     returns 1: '123'
  // Note: could also be made to optionally add to global $http_response_header as per http://php.net/manual/en/reserved.variables.httpresponseheader.php
  var tmp, headers = [],
    newTmp = [],
    k = 0,
    i = 0,
    href = '',
    pathPos = -1,
    flagNames = 0,
    content = null,
    http_stream = false;
  var func = function (value) {
    return value.substring(1) !== '';
  };

  // BEGIN REDUNDANT
  this.php_js = this.php_js || {};
  this.php_js.ini = this.php_js.ini || {};
  // END REDUNDANT
  var ini = this.php_js.ini;
  context = context || this.php_js.default_streams_context || null;

  if (!flags) {
    flags = 0;
  }
  var OPTS = {
    FILE_USE_INCLUDE_PATH: 1,
    FILE_TEXT: 32,
    FILE_BINARY: 64
  };
  if (typeof flags === 'number') { // Allow for a single string or an array of string flags
    flagNames = flags;
  } else {
    flags = [].concat(flags);
    for (i = 0; i < flags.length; i++) {
      if (OPTS[flags[i]]) {
        flagNames = flagNames | OPTS[flags[i]];
      }
    }
  }

  if (flagNames & OPTS.FILE_BINARY && (flagNames & OPTS.FILE_TEXT)) { // These flags shouldn't be together
    throw 'You cannot pass both FILE_BINARY and FILE_TEXT to file_get_contents()';
  }

  if ((flagNames & OPTS.FILE_USE_INCLUDE_PATH) && ini.include_path && ini.include_path.local_value) {
    var slash = ini.include_path.local_value.indexOf('/') !== -1 ? '/' : '\\';
    url = ini.include_path.local_value + slash + url;
  } else if (!/^(https?|file):/.test(url)) {
    href = this.window.location.href;
    pathPos = url.indexOf('/') === 0 ? href.indexOf('/', 8) - 1 : href.lastIndexOf('/');
    url = href.slice(0, pathPos + 1) + url;
  }

  if (context) {
    var http_options = context.stream_options && context.stream_options.http;
    http_stream = !! http_options;
  }

  if (!context || http_stream) {
    var req = this.window.ActiveXObject ? new ActiveXObject('Microsoft.XMLHTTP') : new XMLHttpRequest();
    if (!req) {
      throw new Error('XMLHttpRequest not supported');
    }

    var method = http_stream ? http_options.method : 'GET';
    var async = !! (context && context.stream_params && context.stream_params['phpjs.async']);

    if (ini['phpjs.ajaxBypassCache'] && ini['phpjs.ajaxBypassCache'].local_value) {
      url += (url.match(/\?/) == null ? "?" : "&") + (new Date()).getTime(); // Give optional means of forcing bypass of cache
    }

    req.open(method, url, async);
    if (async) {
      var notification = context.stream_params.notification;
      if (typeof notification === 'function') {
        // Fix: make work with req.addEventListener if available: https://developer.mozilla.org/En/Using_XMLHttpRequest
        if (0 && req.addEventListener) { // Unimplemented so don't allow to get here
        } else {
          req.onreadystatechange = function (aEvt) { // aEvt has stopPropagation(), preventDefault(); see https://developer.mozilla.org/en/
            var objContext = {
              responseText: req.responseText,
              responseXML: req.responseXML,
              status: req.status,
              statusText: req.statusText,
              readyState: req.readyState,
              evt: aEvt
            }; // properties are not available in PHP, but offered on notification via 'this' for convenience

            var bytes_transferred;
            switch (req.readyState) {
            case 0:
              //     UNINITIALIZED     open() has not been called yet.
              notification.call(objContext, 0, 0, '', 0, 0, 0);
              break;
            case 1:
              //     LOADING     send() has not been called yet.
              notification.call(objContext, 0, 0, '', 0, 0, 0);
              break;
            case 2:
              //     LOADED     send() has been called, and headers and status are available.
              notification.call(objContext, 0, 0, '', 0, 0, 0);
              break;
            case 3:
              //     INTERACTIVE     Downloading; responseText holds partial data.
              bytes_transferred = req.responseText.length * 2; // One character is two bytes
              notification.call(objContext, 7, 0, '', 0, bytes_transferred, 0);
              break;
            case 4:
              //     COMPLETED     The operation is complete.
              if (req.status >= 200 && req.status < 400) {
                bytes_transferred = req.responseText.length * 2; // One character is two bytes
                notification.call(objContext, 8, 0, '', req.status, bytes_transferred, 0);
              } else if (req.status === 403) { // Fix: These two are finished except for message
                notification.call(objContext, 10, 2, '', req.status, 0, 0);
              } else { // Errors
                notification.call(objContext, 9, 2, '', req.status, 0, 0);
              }
              break;
            default:
              throw 'Unrecognized ready state for file_get_contents()';
            }
          }
        }
      }
    }

    if (http_stream) {
      var sendHeaders = http_options.header && http_options.header.split(/\r?\n/);
      var userAgentSent = false;
      for (i = 0; i < sendHeaders.length; i++) {
        var sendHeader = sendHeaders[i];
        var breakPos = sendHeader.search(/:\s*/);
        var sendHeaderName = sendHeader.substring(0, breakPos);
        req.setRequestHeader(sendHeaderName, sendHeader.substring(breakPos + 1));
        if (sendHeaderName === 'User-Agent') {
          userAgentSent = true;
        }
      }
      if (!userAgentSent) {
        var user_agent = http_options.user_agent || (ini.user_agent && ini.user_agent.local_value);
        if (user_agent) {
          req.setRequestHeader('User-Agent', user_agent);
        }
      }
      content = http_options.content || null;
    }

    if (flagNames & OPTS.FILE_TEXT) { // Overrides how encoding is treated (regardless of what is returned from the server)
      var content_type = 'text/html';
      if (http_options && http_options['phpjs.override']) { // Fix: Could allow for non-HTTP as well
        content_type = http_options['phpjs.override']; // We use this, e.g., in gettext-related functions if character set
        //   overridden earlier by bind_textdomain_codeset()
      } else {
        var encoding = (ini['unicode.stream_encoding'] && ini['unicode.stream_encoding'].local_value) || 'UTF-8';
        if (http_options && http_options.header && (/^content-type:/im).test(http_options.header)) { // We'll assume a content-type expects its own specified encoding if present
          content_type = http_options.header.match(/^content-type:\s*(.*)$/im)[1]; // We let any header encoding stand
        }
        if (!(/;\s*charset=/).test(content_type)) { // If no encoding
          content_type += '; charset=' + encoding;
        }
      }
      req.overrideMimeType(content_type);
    }
    else if (flagNames & OPTS.FILE_BINARY) { // Trick at https://developer.mozilla.org/En/Using_XMLHttpRequest to get binary
      req.overrideMimeType('text/plain; charset=x-user-defined');
    }

    if (http_options && http_options['phpjs.sendAsBinary']) { // For content sent in a POST or PUT request (use with file_put_contents()?)
      req.sendAsBinary(content); // In Firefox, only available FF3+
    } else {
      req.send(content);
    }

    tmp = req.getAllResponseHeaders();
    if (tmp) {
      tmp = tmp.split('\n');
      for (k = 0; k < tmp.length; k++) {
        if (func(tmp[k])) {
          newTmp.push(tmp[k]);
        }
      }
      tmp = newTmp;
      for (i = 0; i < tmp.length; i++) {
        headers[i] = tmp[i];
      }
      this.$http_response_header = headers; // see http://php.net/manual/en/reserved.variables.httpresponseheader.php
    }

    if (offset || maxLen) {
      if (maxLen) {
        return req.responseText.substr(offset || 0, maxLen);
      }
      return req.responseText.substr(offset);
    }
    return req.responseText;
  }
  return false;
}
