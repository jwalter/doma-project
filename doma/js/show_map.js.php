<?php
  include_once(dirname(__FILE__) ."/../include/main.php");
?>
$(document).ready(function() 
{
  $("#comments_count").text( $("#CommentPosted > div").size());
  
  <?php if(!__("COLLAPSE_VISITOR_COMMENTS")) print '$("#CommentPosted").show();' ?>
  
  $("#zoomIn").click(function() 
  {
    ZoomIn();
  });
  
  $("#zoomOut").click(function() 
  {
    ZoomOut();
  });

  $("#mapImage").click(function() 
  {
    ToggleImage();
  });
  
  //showCommentBox
  $('a.showCommentBox').livequery("click", function(e){
    $("#CommentPosted").show();
    $("#commentBox").css('display','');
    $("#commentMark").focus();
    $("#commentBox").children("a#SubmitComment").show();		
  });	
  
  //hideCommentPosted
  $('a.hideCommentPosted').livequery("click", function(e){
   $("#CommentPosted").hide();
   $("#commentBox").hide();
  });	

  //showCommentPosted
  $('a.showCommentPosted').livequery("click", function(e){
    $("#CommentPosted").show();
  });	
  
  jQuery("abbr.timeago").timeago();
  
  //SubmitComment
  $('a.comment').livequery("click", function(e){
    var map_id =  $("#map_id").val();	
    var comment_text = $("#commentMark").val();
    var user_name = $("#user_name").val();
    var user_email = $("#user_email").val();
    var map_user = $("#map_user").val();
    var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
    var passed = "no";
    if((comment_text == "")||(user_name == ""))
    {
      alert("<?php print __("MISSING_COMMENT")?>");
    }
    else
    {
      passed = "yes";
    }
    if((user_email != "")&&(!emailReg.test(user_email)))
    {
      alert("<?php print __("INVALID_EMAIL")?>");
      passed = "no";
    }
    if(passed=="yes")
    {
      $.post("add_comment.php?comment_text="+encodeURIComponent(comment_text)+"&map_id="+map_id+"&user_name="+encodeURIComponent(user_name)+"&user_email="+user_email+"&user="+map_user, {

      }, function(response){
        
        $('#CommentPosted').append($(response).fadeIn('slow'));
        jQuery("abbr.timeago").timeago();
        $("#comments_count").text( $("#CommentPosted > div").size());
        $("#commentMark").val("");
      });
    }
    
  });
  
  	//deleteComment
		$('a.c_delete').livequery("click", function(e){
			if(confirm('<?php print __("COMMENT_DELETE_CONFIRMATION")?>')==false)
			return false;
			e.preventDefault();
			var parent  = $('a.c_delete').parent();
			var c_id =  $(this).attr('id').replace('CID-','');	
			$.ajax({
				type: 'get',
				url: 'delete_comment.php?cid='+ c_id,
				data: '',
				beforeSend: function(){
				},
				success: function(){
					$('#commentPanel-'+c_id).fadeOut(200,function(){
						$('#commentPanel-'+c_id).remove();
					});
          $("#comments_count").text( $("#comments_count").text() - 1);
				}
			});
		});
  
});

var zoom = 1;

function ZoomIn()
{
  zoom *= 1.25;
  $("#mapImage").get(0).width = zoom * $("#imageWidth").val();
  $("#mapImage").get(0).height = zoom * $("#imageHeight").val();
}

function ZoomOut()
{
  zoom /= 1.25;
  $("#mapImage").get(0).width = zoom * $("#imageWidth").val();
  $("#mapImage").get(0).height = zoom * $("#imageHeight").val();
}

function ToggleImage()
{
  var mapImage = $("#mapImage").get(0).src;
  var hiddenMapImageControl = $("#hiddenMapImage");
  
  if(hiddenMapImageControl.length > 0)
  {
    var hiddenMapImage = hiddenMapImageControl.get(0).src;
    $("#mapImage").get(0).src = hiddenMapImage;
    $("#hiddenMapImage").get(0).src = mapImage;
  }
}

