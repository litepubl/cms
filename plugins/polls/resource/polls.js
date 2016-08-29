/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.06
  */

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
        })
        .on("show.bs.tooltip.poll", ".poll-star", function(e) {
          var button = $(e.target);
          self.changestars(button.attr("data-vote"), button.closest(".poll-opened"));
        })
        .on("hide.bs.tooltip.poll", ".poll-star", function(e) {
          var button = $(e.target);
          button.closest(".poll-opened").find(".fa-star").removeClass("fa-star").addClass("fa-star-o");
        });
    },

    addvote: function(vote, holder) {
      var idpoll = holder.attr("data-idpoll");
      if ($.inArray(idpoll, this.voted) >= 0) {
        return $.errorbox(lang.poll.voted);
      }

      this.voted.push(idpoll);
      this.changestars(vote, holder);

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
            holder = $(r.html).replaceAll(holder);
            holder.find(".tooltip-toggle").removeClass("tooltip-toggle");
            self.changestars(vote, holder);
          },

          error: function(message, code) {
            $.errorbox(message);
          }
        }
      });
    },

    changestars: function(vote, holder) {
      if (holder.hasClass("poll-stars")) {
        holder.find(".poll-star").each(function() {
          if (vote >= $(this).attr("data-vote")) {
            $(".fa-star-o", this).removeClass("fa-star-o").addClass("fa-star");
          }
        });
      }
    }

  });

  $(function() {
    litepubl.polls = new litepubl.Polls();
  });

}(jQuery, document, litepubl));