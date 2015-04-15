(function( window){
  'use strict';
  
window.classes = {
items: {},

getitem: function(name) {
if (this.items[name]) return this.items[name];
return false;
},

addevent: function(eventname, name, callback) {
if (this.items[name]) {
var item = this.items[name];
if (item[eventname]) {
item[eventname].push(callback);
} else {
item[eventname] = [callback];
}
} else {
item = {name: name};
item[eventname] = [callback];
this.items[name] = item;
}
},

fire: function(item, eventname, args) {
if (!item || !item[eventname]) return false;
var events = item[eventname];
for (var i = 0, l = events.length; i < l; i++) {
var callback = events[i];
callback(args);
}

delete item[eventname];
},

getargs: function(args) {
if (typeof args[0] == "string") {
return {
name: args[0],
fn: args[1]
};
}

 if (typeof args[0] === "object") {
for (var name in args[0]) {
return {
name: name,
fn: args[0][name]
};
}
}

return false;
},

    create: function() {
var args = this.getargs(arguments);
if (!args|| !args.name || !args.fn) return false;

var item = this.getitem(args.name);
if (item) {
this.fire(item, "onbefore", args);
} else {
item = this.items[args.name] = {};
}

      item.instance = new args.fn();
this.fire(item, "oninit", item.instance );
      return item.instance ;
    },
    
    onbefore: function() {
var args = this.getargs(arguments);
if (args && args.name) this.addevent("onbefore", args.name, args.fn);
    },
    
    oninit: function() {
var args = this.getargs(arguments);
if (!args || !args.name) return false;
var item = this.items[args.name];
if (item && item.instance) return args.fn(item.instance);

this.addevent("oninit", args.name, args.fn);
    }
    
};//class

}(window));