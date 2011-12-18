(function($) {
  $.fn.overviewMap = function(options)
  {
    $(this).each(function() {
      var mapElement = $(this);
      var mapBounds = new google.maps.LatLngBounds();
      var markers = [];
      var borderPolygons = [];
      var routePolylines = [];
      var mapOptions = 
      {
        panControl: false,
        zoomControl: true,
        scaleControl: true,
        overviewMapControl: true,
        scrollwheel: true,
        mapTypeId: google.maps.MapTypeId.TERRAIN
      };
      var map = new google.maps.Map(mapElement.get(0), mapOptions);
      google.maps.event.addListener(map, 'zoom_changed', function() { zoomChanged(); });
      var lastZoom = -1;
      var zoomLimit = 10;
      
      // get bounds of maps
      for(var i in options.data)
      {
        var data = options.data[i];
        
        // the map borders for large scale overview map
        var vertices =         
        [
          new google.maps.LatLng(data.Corners[0].Latitude, data.Corners[0].Longitude),
          new google.maps.LatLng(data.Corners[1].Latitude, data.Corners[1].Longitude),
          new google.maps.LatLng(data.Corners[2].Latitude, data.Corners[2].Longitude),
          new google.maps.LatLng(data.Corners[3].Latitude, data.Corners[3].Longitude),
          new google.maps.LatLng(data.Corners[0].Latitude, data.Corners[0].Longitude)
        ];

        var borderPolygon = new google.maps.Polygon({
          paths: vertices,
          strokeColor: data.BorderColor, 
          strokeWeight: data.BorderWidth,
          strokeOpacity: data.BorderOpacity,
          fillColor: data.FillColor, 
          fillOpacity: data.FillOpacity
        });
        borderPolygon.Data = data;
        google.maps.event.addListener(borderPolygon, 'mouseover', function() {
          this.setOptions({strokeColor: "#0000ff"});
        });
        google.maps.event.addListener(borderPolygon, 'mouseout', function() {
          this.setOptions({strokeColor: "#ff0000"});
        });
        borderPolygons.push(borderPolygon);
        
        // the map as an icon for small scale overview map
        var icon = new google.maps.MarkerImage("gfx/control_flag.png", new google.maps.Size(16, 16), new google.maps.Point(0, 0), new google.maps.Point(8, 8));
        var position = new google.maps.LatLng(data.MapCenter.Latitude, data.MapCenter.Longitude);
        var title = data.Name + " (" + data.Date + ")";
        var marker = new google.maps.Marker({ icon: icon, position: position });
        marker.title = title;
        markers.push(marker);
        
        // the route lines (if data.RouteCoordinates is present)
        if(data.RouteCoordinates != null)
        {
          for(var i in data.RouteCoordinates)
          {
            var points = new Array(data.RouteCoordinates[i].length);
            for(var j in data.RouteCoordinates[i])
            {
              var vertex = data.RouteCoordinates[i][j];
              points[j] = new google.maps.LatLng(vertex[1], vertex[0]);
            }
            var polyline = new google.maps.Polyline({ path: points, strokeColor: data.RouteColor, strokeWeight: data.RouteWidth, strokeOpacity: data.RouteOpacity });
            routePolylines.push(polyline);
          }
        }
        
        // make sure all maps fits in overview map
        mapBounds.extend(vertices[0]);
        mapBounds.extend(vertices[1]);
        mapBounds.extend(vertices[2]);
        mapBounds.extend(vertices[3]);
      }
      
      map.fitBounds(mapBounds);

      function zoomChanged()
      {
        var zoom = map.getZoom();
        
        if(zoom < zoomLimit && (lastZoom >= zoomLimit || lastZoom == -1))
        {
          for(var i in borderPolygons)
          {
            borderPolygons[i].setMap(null);
          }
          for(var i in routePolylines)
          {
            routePolylines[i].setMap(null);
          }
          for(var i in markers)
          {
            markers[i].setMap(map);
          }
        }
        if(zoom >= zoomLimit && (lastZoom < zoomLimit || lastZoom == -1))
        {
          for(var i in markers)
          {
            markers[i].setMap(null);
          }
          for(var i in borderPolygons)
          {
            borderPolygons[i].setMap(map);
          }
          for(var i in routePolylines)
          {
            routePolylines[i].setMap(map);
          }
        }
        lastZoom = zoom;
      }    
    });
  };
})(jQuery);
