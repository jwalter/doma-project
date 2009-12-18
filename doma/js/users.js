$(document).ready(function() 
{
  $(".thumbnailHoverLink").mouseover(function() 
  {
    var x = $(".hoverThumbnail", $(this).parent()).removeClass('hidden');
  });

  $(".thumbnailHoverLink").mouseout(function() 
  {
    $(".hoverThumbnail", $(this).parent()).addClass('hidden');
  });
});