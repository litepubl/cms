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
        self.addvote(button.attr("data-vote"), button.closest(".poll-opened"));
        return false;
      });
    },

    addvote: function(vote, holder) {
      var idpoll = holder.attr("data-idpoll");
      if ($.inArray(idpoll, this.voted) >= 0) {
        return this.error(lang.poll.voted);
      }

      this.voted.push(idpoll);
      this.changestars(holder, vote);

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
            holder.find(".tooltip-toggle, .tooltip-ready").tooltip("destroy");
            holder.html(r.html);
            holder.find("tooltip-toggle").removeClass("tooltip-toggle");
            self.changestars(holder, vote);
          },

          error: function(message, code) {
            $.errorbox(message);
          }
        }
      });
    },

    changestars: function(holder, vote) {
      if (holder.hasClass("poll-stars")) {
        holder.find("poll-star").each(function() {
          if (vote >= $(this).attr("data-vote")) {
            $("fa", this).removeClass("fa-star-o").addClass("fa-star");
          }
        });
      }
    }

  });

  $(document).ready(function() {
    litepubl.polls = new litepubl.Polls();
  });

}(jQuery, document, litepubl));