$( ".closeX" ).click(function() {
    $.ajax({
        url: 'remover.php',
        type: 'POST',
        data: 'id=' + $( this ).parent().attr("id")});
    $( this ).fadeOut(400, function() { $( this ).parent().remove() });
});
