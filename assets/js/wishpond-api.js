var protocol_manager = {
  clear_protocol: function(url) {
    return url.replace(/.*?:\/\//g, "");
  },
  current_protocol: function(url) {
    return document.location.href.substr(0, document.location.href.indexOf("://"));
  },
  to_http: function(url) {
    url = this.clear_protocol(url);
    return "http://" + url;
  },
  to_https: function(url) {
    url = this.clear_protocol(url);
    return "http://" + url;
  },
  to_current_protocol: function(url) {
    return this.current_protocol() + "://" + this.clear_protocol(url);
  }
}

// Store what calls are currently supported
var wishpond_api_endpoints = {
  check_availability: "check_availability",
  publish_wishpond_page: "publish_wishpond_page",
  disable_guest_signup: "disable_guest_signup"
}

function make_wordpress_request(options) {
  wishpond_api.message.display("updated", "Processing request ...");
  jQuery.ajax({
    type: "POST",
    url: JS.ajaxurl,
    dataType: "json",
    data: {
      data: options,
      action: "wishpond_api",
      nonce : JS.global_nonce
    }
  }).done(function(response) {
    // Display the server message ?
    // console.log(response);
    if(typeof response != "undefined" && response != null && typeof response.message != "undefined") {
      wishpond_api.message.display(response.message.type, response.message.text);
    }
  });
}

// GUEST USER ONLY
if(JS.is_guest_signup_enabled)
{
  var iframe_url = protocol_manager.to_current_protocol( JS.WISHPOND_SITE_URL );
  var wishpond_iframe_src = iframe_url + "/central/merchant_signups/guest_user_status#" + encodeURIComponent(document.location.href);
  jQuery("#wishpond_guest_status_iframe").attr("src", wishpond_iframe_src);
}

// Messages to be executed directly in javascript
var client_side_messages = {
  allowed: {
    "scroll": [// accepted options
      "x",
      "y"
    ]
  },

  scroll: function(options) {
    window.scrollTo(options["x"], options["y"]);
  }
}

var wishpond_api = {

  init_listener: function() {
    XD.receiveMessage(function(response){

      if( typeof response.data == 'undefined' )
      {
        return false;
      }

      // Handle disabling the guest user, which uses another system
      if( response.data.guest_user === false
          && response.data.logged_in === true ) {
        disable_guest_signup();
        return;
      }

      if( response.data.client_side === true && 
          typeof client_side_messages.allowed[response.data.endpoint] != "undefined" ) {
        // If message is to be executed on the client side
        client_side_messages[response.data.endpoint](response.data.options);
      }
      else {
        // handle other messages from the iFrame, on wordpress server
        make_wordpress_request(response.data);
      }
    }, protocol_manager.to_current_protocol( JS.WISHPOND_SITE_URL ));
  },
  message: {
    id: "wishpond_message",
    timeout_handler: null,
    display: function(message_type, message_text) {
      this.clear();
      // message_type can be updated or error
      jQuery("#wishpond_landing_pages_iframe").before("<div id=" + this.id + " class='" + message_type + "'><p>" + message_text + "</p></div>");
      this.timeout_handler = setTimeout(function(){
        jQuery("#" + wishpond_api.message.id).remove();
      }, 15000);
    },
    clear: function() {
      clearTimeout(this.timeout_handler);
      this.timeout_handler = null;
      jQuery("#" + this.id).remove();
    }
  }
}

function disable_guest_signup() {
  var options = {};
  options["endpoint"] = 'disable_guest_signup';
  make_wordpress_request(options);
}

wishpond_api.init_listener();
