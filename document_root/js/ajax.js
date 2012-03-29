/* Vol�n� AJAXu u v�ech odkaz� s t��dou ajax */

$("a.ajax.delete").live("click", function (event) {
    var url = this.href;
    $( "#dialog-confirm" ).dialog({
        resizable: false,
        height:140,
        modal: true,
        buttons: {
                "Ano" : function() {
                        $.get(url);
                        $( this ).dialog( "close" );
                },
                "Ne" : function() {
                        $( this ).dialog( "close" );
                }
        }
    });
    event.preventDefault();
});

$("a.ajax:not(.delete)").live("click", function (event) {
        event.preventDefault();
        $.get(this.href);
});
        
/* AJAXov� odesl�n� formul��� */
$("form.ajax").live("submit", function (event) {
    $(this).ajaxSubmit();
    $('#frmpridatZbozi-zbozi').focus();
    $('#frmpridatZbozi-zbozi').val("");
		$("#snippet--simpleForm").dialog( "destroy" ); // po submitnuti zavreme dialog

    return false;
});
$("#frm-filtrZakaznici :submit").live("click", function (event) {
    $("#snippet--strankyLong").fadeTo("slow", 0.20);
    $(this).ajaxSubmit();
    return false;
});
  
$("#frm-novaObjednavka :submit").live("click", function (event) {
    $(this).ajaxSubmit();
    return false;
});

$("#frm-editObjednavka :submit").live("click", function (event) {
    $(this).ajaxSubmit();
    return false;
});

$(".paginator a").live("click", function (event) {
    event.preventDefault();
    $.get(this.href);
});

jQuery.nette.updateSnippet = function (id, html) {
    $("#snippet--strankyLong").html(html).fadeTo("fast", 1);
    $("#" + id).not("#snippet--strankyLong").fadeTo("fast", 0.01, function () {
        $(this).html(html).fadeTo("fast", 1);
    });
};

$(document).ready(function(){
	$('.back').click(function(){
		parent.history.back();
		return false;
	});
	$('.print').click(function(){
		window.print();
		return false;
	});
});
