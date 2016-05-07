var  result = [];
$(".navbar-nav:first").find("a").each(function() {
result.push($(this).attr("href"));
});

return result;