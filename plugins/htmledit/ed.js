/*****************************************/
// Name: Javascript Textarea HTML Editor
// Author: Balakrishnan
// URL: http://www.corpocrat.com
//
// Modified by sartas
// URL: http://sartas.ru
/* updated by Vladmir Yushko
http://litepublisher.ru/
*/

var textarea;
var content;

function edToolbar(obj) {
	if (obj == 'all') {
		textarea = document.getElementsByTagName('textarea');
 		for(var i=0;i<textarea.length;i++) {
			var toolbar = document.createElement('div');
			toolbar.className = 'toolbar';
			toolbar.innerHTML = buttons(textarea[i].id);
			textarea[i].parentNode.insertBefore(toolbar, textarea[i]);
		}
	} else {
		var toolbar = document.createElement('div');
		toolbar.className = 'toolbar';
		toolbar.innerHTML = buttons(obj);
		textarea = document.getElementById(obj);
		textarea.parentNode.insertBefore(toolbar, textarea);
	}
}

function addbutton(click, tag, image, obj) {
return '<a onClick="' + click + '(\'<' + tag  + ">', '</" + tag + ">', '" + obj + "')\">" +
'<img class="htmlbutton" src="' + ltoptions.files + '/plugins/htmledit/images/' + image + '" name="btn' + tag + '" alt="' + tag + '" title="' + tag + '" /></a>';
}

function addbuttonclick(click, title, image, obj) {
return '<a onClick="' + click + "('" + obj + "');\">" +
'<img class="htmlbutton" src="' + ltoptions.files + '/plugins/htmledit/images/' + image + '" name="btn' + title + '" alt="' + title + '" title="' + title + '" /></a>';
}

function buttons(obj) {
var buttons = addbutton('doAddTags', 'strong', 'bold.gif', obj) +
addbutton('doAddTags', 'em', 'italic.gif', obj) +
addbutton('doAddTags', 'u', 'underline.gif', obj) +
addbuttonclick('doURL', 'link', 'link.gif', obj) +
addbuttonclick('doImage', 'picture', 'image.gif', obj) +
addbutton('doList', 'ul', 'unordered.gif', obj) +
addbutton('doList', 'ol', 'ordered.gif', obj) +
addbutton('doAddTags', 'blockquote', 'quote.gif', obj) +
addbutton('doAddTags', 'code', 'code.gif', obj);
		return buttons;
}
	
function doImage(obj) {
	textarea = document.getElementById(obj);
	var url = prompt(IMGpromt,'http://');
	var scrollTop = textarea.scrollTop;
	var scrollLeft = textarea.scrollLeft;
		if (url != '' && url != null) 	{
		if (document.selection) {
			textarea.focus();
			var sel = document.selection.createRange();
			sel.text = '<img src=\"' + url + '\" alt=\"\" />';
		} else {
			var len = textarea.value.length;
			var start = textarea.selectionStart;
			var end = textarea.selectionEnd;
			var sel = textarea.value.substring(start, end);
			var rep = '<img src=\"' + url + '\" alt=\"\" />';
			textarea.value =  textarea.value.substring(0,start) + rep + textarea.value.substring(end,len);
			textarea.scrollTop = scrollTop;
			textarea.scrollLeft = scrollLeft;
		}
	 }
}

function doURL(obj) {
	textarea = document.getElementById(obj);
	var url = prompt(URLpromt,'http://');
	var scrollTop = textarea.scrollTop;
	var scrollLeft = textarea.scrollLeft;
		if (url != '' && url != null) {
		if (document.selection)  {//IE
			textarea.focus();
			var sel = document.selection.createRange();
							if(sel.text=="") {
				sel.text = '<a href=\"' + url + '\">' + url + '</a>';
			}  else  {
				sel.text = '<a href=\"' + url + '\">' + sel.text + '</a>';
			}				
		} else  {//Firefox
			var len = textarea.value.length;
			var start = textarea.selectionStart;
			var end = textarea.selectionEnd;
			var sel = textarea.value.substring(start, end);
						if(sel=="") {
				var rep = '<a href=\"' + url + '\">' + url + '</a>';
			} else {
				var rep = '<a href=\"' + url + '\">' + sel.text + '</a>';
			}

			textarea.value = textarea.value.substring(0,start) + rep + textarea.value.substring(end,len);;
			textarea.scrollTop = scrollTop;
			textarea.scrollLeft = scrollLeft;
		}
	 }
}
	
function doAddTags(tag1,tag2,obj) {
	textarea = document.getElementById(obj);
	if (document.selection)  {//IE
		textarea.focus();
		var sel = document.selection.createRange();
		sel.text = tag1 + sel.text + tag2;
	} else {//Firefox
		var len = textarea.value.length;
		var start = textarea.selectionStart;
		var end = textarea.selectionEnd;
		var scrollTop = textarea.scrollTop;
		var scrollLeft = textarea.scrollLeft;
		var sel = textarea.value.substring(start, end);
		var rep = tag1 + sel + tag2;
		textarea.value = textarea.value.substring(0,start) + rep + textarea.value.substring(end,len);
		textarea.scrollTop = scrollTop;
		textarea.scrollLeft = scrollLeft;
	}
}

function doList(tag1,tag2,obj) {
textarea = document.getElementById(obj);
	if (document.selection) {//IE
		textarea.focus();
		var sel = document.selection.createRange();
		var list = sel.text.split('\n');
		var lenList = list.length - 1;
		for(var i=0;i<lenList;i++) {
			list[i] = ' <li>' + list[i].substr(0,list[i].length-1) + '</li>';
		}
		list[lenList] = ' <li>' + list[lenList] + '</li>';
		sel.text = tag1 + '\n' + list.join("\n") + '\n' + tag2;
	} else {//Firefox
		var len = textarea.value.length;
		var start = textarea.selectionStart;
		var end = textarea.selectionEnd;
		var scrollTop = textarea.scrollTop;
		var scrollLeft = textarea.scrollLeft;
		var sel = textarea.value.substring(start, end);
		var list = sel.split('\n');
		var lenList = list.length - 1
			for(var i=0;i<lenList;i++) {
			list[i] = ' <li>' + list[i].substr(0,list[i].length-1) + '</li>';
		}
		list[lenList] = ' <li>' + list[lenList] + '</li>';
		var rep = tag1 + '\n' + list.join("\n") + '\n' +tag2;
		textarea.value = textarea.value.substring(0,start) + rep + textarea.value.substring(end,len);
		textarea.scrollTop = scrollTop;
		textarea.scrollLeft = scrollLeft;
	}
}

$(function() {
var areas = document.getElementsByTagName('textarea');
 if(areas.length > 0) edToolbar("all");
});
