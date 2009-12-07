<?php
  $im = imagecreatefromjpeg('../maps/map_images/195.thumbnail.jpg');
  imagerotate($im, 20, imagecolorallocatealpha($im, 255, 255, 255, 0));
  
  ImageJpeg($im);
  
  ImageDestroy ($im);
?>