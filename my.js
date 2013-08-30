$( ".closeX" ).click(function() {
    var id = $( this ).parent().attr("id");
    var markup = [
        '<div class="gray-out">',
        '<div class="confirmBox">',
        '<p>Are you sure you want to remove this user?</p>',
        '<div class="button" id="yes"><p>Yes</p></div>',
        '<div class="button" id="no"><p>No</p></div>',
        '</div></div>'].join('');
        
    $( markup ).hide().appendTo('body');
    $( "#" + id ).clone().appendTo(".confirmBox").children('.closeX').hide();
    $( ".gray-out" ).fadeIn();

    $( "#yes.button" ).click(function(){
        $.ajax({
            url: 'remover.php',
            type: 'POST',
            data: 'id=' + id});
        $( this ).parent().fadeOut(400, function() { 
            $( this ).parent().remove(); 
            $( "#" + id).fadeOut(400); 
        })
    });

    $( "#no.button" ).click(function(){
        $( this ).parent().fadeOut(400, function() { 
            $( this ).parent().remove(); 
        })
    });
});
