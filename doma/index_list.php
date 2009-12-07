<?php
  foreach($vd["Maps"] as $map)
  {
    $mapInfo = $vd["MapInfo"][$map->ID];
    ?>
 
  <div class="map">
    <div class="inner">
      <div class="thumbnail">
        <?php 
          print $mapInfo["MapThumbnailHtml"];
        ?>
      </div>
      <div class="info">
        <div class="date">
          <?php print Helper::DateToLongString(Helper::StringToTime($map->Date, true)); ?>
        </div>
        <div class="name">
          <?php print Helper::EncapsulateLink($map->Name, $mapInfo["URL"]); ?>
        </div>
        <?php if(__("SHOW_MAP_AREA_NAME") || __("SHOW_ORGANISER") || __("SHOW_COUNTRY")) { ?>
        <?php if($vd["SearchCriteria"]["selectedCategoryID"] == 0) print '<div class="category">'. __("CATEGORY") .": ". $vd["Categories"][$map->CategoryID]->Name .'</div>'; ?>
        <div class="organiser">
          <?php print $mapInfo["MapAreaOrganiserCountry"]; ?>
        </div>
        <?php } ?>
        <?php if(__("SHOW_DISCIPLINE")) { ?>
        <div class="discipline">
          <?php print $map->Discipline; ?><?php if(__("SHOW_RELAY_LEG") && $map->RelayLeg) print ', '. __("RELAY_LEG_LOWERCASE") .' '. $map->RelayLeg; ?>
        </div>
        <?php } ?>
        <?php if(__("SHOW_RESULT_LIST_URL") && $map->CreateResultListUrl()) { ?>
        <div class="resultListUrl">
          <a href="<?php print $map->CreateResultListUrl()?>"><?php print __("RESULTS")?></a>
        </div>
        <?php } ?>
        <?php if(Helper::IsLoggedInUser() && Helper::GetLoggedInUser()->ID == getUser()->ID) { ?>
          <div class="admin">
            <?php print $map->Views?> 
            <?php print __("VIEWS")?> 
            <span class="separator">|</span> 
            <a href="edit_map.php?<?php print Helper::CreateQuerystring(getUser(), $map->ID)?>"><?php print __("EDIT_MAP"); ?></a>
          </div>
        <?php } ?>
      </div>
      <?php 
        if(__("SHOW_COMMENT") && $map->Comment) 
        {
          if(!$mapInfo["IsExpandableComment"])
          {
            ?>
            <div class="comment"><?php print $map->Comment; ?></div>
            <?php
          }
          else
          {
            ?>
            <div>
              <div class="comment shortComment">
                <img src="gfx/plus.png" class="button toggleComment" alt="" />
                <div class="indent"><?php print $mapInfo["ContractedComment"]; ?></div>
              </div>
              <div class="comment longComment hidden">
                <img src="gfx/minus.png" class="button toggleComment" alt="" />
                <div class="indent"><?php print nl2br($map->Comment); ?></div>
              </div>
            </div>
            <?php
          }
        }
        ?>
      <?php if($map->IsGeocoded) print '<div class="listOverviewMapLink"><a href="#">Översiktskarta</a><input type="hidden" value="'. $map->ID .'" /></div>'; ?> 
      <?php if($map->IsGeocoded) print '<div><a href="export_kml.php?id='. $map->ID .'">KML</a></div>'; ?> 
      <div class="clear"></div>
    </div>
  </div>

<?php
  }
?>