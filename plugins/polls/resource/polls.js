/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 **/

(function($, document, litepubl) {
  'use strict';

  litepubl.Polls = Class.extend({
    voted: false,

    init: function() {
this.voted = [];

var self = this;
$(document).on("click.poll", ".poll-vote", function() {
        var button = $(this);
self.addvote(button.attr("data-vote"), button.closest(".poll-active"));
        return false;
      });
},

    addvote: function(vote, holder) {
var idpoll = holder.attr("data-idpoll");
      if ($.inArray(idpoll, this.voted) >= 0) {
return this.error(lang.poll.voted);
}

      this.voted.push(idpoll);
var self = this;
      litepubl.authdialog.check({
        rpc: {
        type: 'get',
        method: "polls_sendvote",
        params: {
          idpoll: idpoll,
          vote: vote
        },

        callback: function(r) {
self.enabled = true;
holder.html(r.html);
        },

        error: function(message, code) {
self.enabled = true;
$.errorbox(message);
}
}
      });
    }

  });

  $(document).ready(function() {
litepubl.polls = new litepubl.Polls();
  });

}(jQuery, document, litepubl));