<?php
  include_once("../include/helper.php");
?>
$(document).ready(function() 
{
  $(".toggleComment").click(function() 
  {
    toggleComment($(this).parent().parent());
  });

  $(".comment div").click(function() 
  {
    toggleComment($(this).parent().parent());
  });

  $("#categoryID").change(function() { submitForm(); });
  $("#year").change(function() { submitForm()});
  $("#displayMode").change(function() { submitForm(); });
  $(".listOverviewMapLink a").click(function() { showListOverviewMap(this); });
 
  <?php if($_GET["displayMode"] == "overviewMap") { ?>
    initOverviewMap();
  <?php } ?>
});

$(window).unload( function () { GUnload(); } );


function toggleComment(baseElement)
{
  $(".longComment", baseElement).toggleClass('hidden');
  $(".shortComment", baseElement).toggleClass('hidden');
}

function submitForm()
{
  $("form").submit();
}

function initOverviewMap()
{
  if (GBrowserIsCompatible())
  {
    var map = new GMap2($("#overviewMap").get(0));

    // set map properties
    map.addControl(new GLargeMapControl());
    map.addControl(new GMapTypeControl());
    map.addControl(new GOverviewMapControl());
    map.enableScrollWheelZoom();

    var mapBounds = new GLatLngBounds();
    
    for(var i in overviewMapData)
    {
      var data = overviewMapData[i];
      var vertices =         
      [
        new GLatLng(data.Corners[0].Latitude, data.Corners[0].Longitude),
        new GLatLng(data.Corners[1].Latitude, data.Corners[1].Longitude),
        new GLatLng(data.Corners[2].Latitude, data.Corners[2].Longitude),
        new GLatLng(data.Corners[3].Latitude, data.Corners[3].Longitude),
        new GLatLng(data.Corners[0].Latitude, data.Corners[0].Longitude)
      ];

      mapBounds.extend(vertices[0]);
      mapBounds.extend(vertices[1]);
      mapBounds.extend(vertices[2]);
      mapBounds.extend(vertices[3]);
      
      var polygon = new GPolygon(
        vertices,
        data.BorderColor, 
        data.BorderWidth,
        data.BorderOpacity,
        data.FillColor, 
        data.FillOpacity
      );
      map.addOverlay(polygon);

		var icon_x = new GIcon(G_DEFAULT_ICON);
		icon_x.iconSize = new GSize(12, 12);
		icon_x.iconAnchor = new GPoint(0, 12);
		icon_x.image = "<?php print(Helper::GlobalPath("gfx/o-sign.png"));?>";
		icon_x.shadow = null;
		markerOptions_x = { icon:icon_x };

		var point_x = new GLatLng(data.Corners[1].Latitude, data.Corners[1].Longitude);
		var marker_x = new GMarker(point_x, markerOptions_x);
		map.addOverlay(marker_x);
		marker_x.Map = map;
		marker_x.Data = data;

      
      polygon.Map = map;
      polygon.Data = data;
      
      for(var j in data.RouteSegments)
      {
        var latlngs = new Array();
        for(var k in data.RouteSegments[j])
        {
          latlngs[k] = new GLatLng(data.RouteSegments[j][k][1], data.RouteSegments[j][k][0]);
        }
        var routeSegmentLine = new GPolyline(latlngs, '#ff0000', 2, 0.8);
        map.addOverlay(routeSegmentLine);
      }
      
      GEvent.addListener(
        marker_x, 
        "click", 
        function() 
        { 
          var tabs = new Array();
          tabs[0] = new GInfoWindowTab(this.Data.MapThumbnailImageCaption, this.Data.MapThumbnailImage);
          tabs[1] = new GInfoWindowTab(this.Data.MapInfoCaption, this.Data.MapInfo);
          var center = new GLatLng(this.Data.MapCenter.Latitude, this.Data.MapCenter.Longitude)
          this.Map.openInfoWindowTabsHtml(center, tabs, { maxWidth: 800 } ); 
        }
      );
    }
    
    map.setCenter(mapBounds.getCenter());
    map.setZoom(map.getBoundsZoomLevel(mapBounds)); // need to set zoom after center is set
  }
}

/*
Vid klick på "+ Översiktskarta"-länken skapas dynamiskt en div där en google maps-karta visas. 
  - använd en eventhandler för länkklicket i vilken en div som kopplats till google maps skapas.
  - hämta koordinater för kartan samt koordinater för rutten mha ajax / json
Denna div läggs under thumbnailen och övrig kartinfo, även kommentaren. 
- kör en $...append() 
Diven ska gå att dölja med klick på samma länk (som nu heter "- Översiktskarta"). 
- länken ska ha koll på vilken state den är i (collapsed, not loaded | collapsed, loaded | expanded)


*/
function showListOverviewMap(obj)
{
  var id = $(obj).next().val();
  
  if($(obj).siblings().is("div"))
  {
    $(obj).next().next().remove();
  }
  else
  {
    if (GBrowserIsCompatible())
    {

      $.getJSON('ajax_server.php', 
                { action: 'getMapCornerPositionsAndRouteCoordinates', id: id }, 
                function(data)
                { 

      var mapDiv = $('<div class="singleOverviewMap"></div>');
      
                  $('input[value=' + id + ']').after(mapDiv);
      var map = new GMap2(mapDiv.get(0));
                  map.addControl(new GLargeMapControl());
                  map.addControl(new GMapTypeControl());
                  map.addControl(new GOverviewMapControl());
                  map.enableScrollWheelZoom();

                  var mapBounds = new GLatLngBounds();
                  var corners = data.MapCornerPositions;
                  var vertices =         
                  [
                    new GLatLng(corners.SW.Latitude, corners.SW.Longitude),
                    new GLatLng(corners.NW.Latitude, corners.NW.Longitude),
                    new GLatLng(corners.NE.Latitude, corners.NE.Longitude),
                    new GLatLng(corners.SE.Latitude, corners.SE.Longitude),
                    new GLatLng(corners.SW.Latitude, corners.SW.Longitude)
                  ];
                  
                  mapBounds.extend(vertices[0]);
                  mapBounds.extend(vertices[1]);
                  mapBounds.extend(vertices[2]);
                  mapBounds.extend(vertices[3]);
                              
                  var polygon = new GPolygon(
                    vertices,
                    data.BorderColor, 
                    data.BorderWidth,
                    data.BorderOpacity,
                    data.FillColor, 
                    data.FillOpacity
                  );
                  map.addOverlay(polygon);                  
                  
                  map.setCenter(mapBounds.getCenter());
                  map.setZoom(map.getBoundsZoomLevel(mapBounds)); // need to set zoom after center is set                
                } 
             );
    }
  }  
}
