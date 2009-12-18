$(document).ready(function() 
{
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