<?php
  include_once(dirname(__FILE__) ."/config.php");
  include_once(dirname(__FILE__) ."/include/definitions.php");
?>

<?php print '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>Upgrade DOMA to version 3.0.1</title>
  <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
  <link rel="icon" type="image/png" href="gfx/favicon.png" />
  <link rel="stylesheet" href="style.css" type="text/css" />  
  <script src="js/jquery/jquery-1.7.1.min.js" type="text/javascript"></script>
  <script type="text/javascript" src="js/jquery/jquery.livequery.js"></script>
  <script type="text/javascript">
    $(document).ready(function() 
    {
      $('a.upgrade').livequery("click", function(e){
        if(confirm('Do you want to add geocoding and waypoints to database? \n\nDo it only after you already upgraded to version 3.0.1 and later. \n\n(This action will not affect your current core data. It will just add/update gps data from maps to db)')==false)
        return false;
        e.preventDefault();
        $('#total').text(0);
        var c=0;
        var t="";
        $("#maps").children().each(function() {
          var id = $(this).attr('id');
          if(c==1) 
            {
            t = ", ";
            };
          $.ajax({
            type: 'get',
            url: '_upgrade_301_engine.php?id='+ id, 
            data: '',
            beforeSend: function(){
            },
            success: function(){
              $('#total').text(parseInt($('#total').text())+1);
            }
          });
          c=1;
        });
      });
    });
  </script>
</head>
<body>
<div id="wrapper">
 <div id="topbar">
  <div class="inner">
    <div class="left">
       Admin tool: Upgrade DOMA to version 3.0.1
    </div>
    <div class="right">
    <a href="users.php">Back to DOMA homepage</a>
    </div>
    <div class="clear"></div>
  </div>
  </div>

<div id="content">
<form>

<h3>DOMA database version: <?php print DataAccess::GetSetting("DATABASE_VERSION", "0.0") ?></h3>

<h3>List of available maps:</h3> 
<div id="maps">
<?php
 $c=0;
 $mp = DataAccess::GetAllMaps(0);
  if(count($mp) > 0)
  {
    foreach($mp as $map)
    {
      print "<span id='".$map->ID."'>".$map->ID."</span>, ";
      $c++;
    }
  }
?>
</div>
<div clear="both"></div>
<?php print '<h3>Total maps: '.$c.'</h3>'; ?>

<div clear="both"></div>
<h3>Processed maps: <span id="total" class="required">0</span></h3>
<div clear="both"></div>
<br/>
<a href="#" class="upgrade">UPGRADE DATA IN DATABASE</a>

</form>
</div>
</div>
</body>
</html>