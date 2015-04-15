/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document, window) {
  'use strict';
  
  $.pollclient = {
    enabled: true,
    voted : [],
    
    init: function () {
      $(".pollitem").click(function() {
        var self =$(this);
        $.pollclient.clickvote(self.data("idpoll"), self.data("index"), self.closest(".activepoll"));
        return false;
      });
      
      $(".submit-radio-poll").click(function() {
        var self =$(this);
        var owner = self.closest(".activepoll");
        var vote = $("input:radio:checked", owner).val();
        $.pollclient.clickvote(self.data("idpoll"), vote, owner);
        return false;
      });
    },
    
    clickvote: function(idpoll, vote, holder) {
      if ($.inArray(idpoll, this.voted) >= 0) return this.error(lang.poll.voted);
      //single request
      if (!this.enabled) return false;
      this.setenabled(false);
      this.voted.push(idpoll);
      $.jsonrpc({
        type: 'get',
        method: "polls_sendvote",
      params: {idpoll: idpoll, vote: vote},
        callback: function(r) {
          if (r.code == "error") return $.pollclient.error(r.message);
          $.pollclient.setenabled(true);
          //update results
          var pollresult = holder.next(".poll-result");
          $(".votes", pollresult).text(r.total);
          $(".average", pollresult).text(r.rate);
          $(".poll-votes", pollresult).each(function() {
            var index = $(this).data("index");
            if (index in r.votes) $(this).text(r.votes[index]);
          });
        },
        
        error: $.proxy($.pollclient.error, $.pollclient)
      });
    },
    
    error: function(mesg) {
      $.pollclient.setenabled(true);
      $.messagebox(lang.dialog.error, mesg);
    },
    
    setenabled: function(value) {
      if (value== this.enabled) return;
      this.enabled = value;
      if(value) {
        $(":input", ".activepoll").removeAttr("disabled");
      } else {
        $(":input", ".activepoll").attr("disabled", "disabled");
      }
    }
    
  };
  
  $(document).ready(function() {
    //only logged users
    if (litepubl.getuser().id) $.pollclient.init();
  });
  
}(jQuery, document, window));