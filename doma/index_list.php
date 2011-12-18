<?php
  foreach($vd["Maps"] as $map)
  {
    $mapInfo = $vd["MapInfo"][$map->ID];
    ?>
 
  <div class="map">
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
      <div class="discipline">
      <?php
        $atoms = array();
        if(__("SHOW_DISCIPLINE"))
        {
          $atoms[] = $map->Discipline . (__("SHOW_RELAY_LEG") && $map->RelayLeg ? ', '. __("RELAY_LEG_LOWERCASE") .' '. $map->RelayLeg : "");          
        }
        if(__("SHOW_RESULT_LIST_URL") && $map->CreateResultListUrl())
        {
          $atoms[] = '<a href="'. $map->CreateResultListUrl() .'">' . __("RESULTS") .'</a>';
        }
        print join('<span class="separator">|</span>', $atoms);
      ?>
      </div>
      <?php
        if($map->IsGeocoded) 
        {
          ?>
          <div class="listOverviewMapLink">
            <input type="hidden" value="<?php print $map->ID; ?>" />
            <a class="showOverviewMap" href="#"><?php print __("SHOW_OVERVIEW_MAP"); ?></a>
            <a class="hideOverviewMap" href="#"><?php print __("HIDE_OVERVIEW_MAP"); ?></a>
            <span class="separator">|</span> 
            <a href="export_kml.php?id=<?php print $map->ID; ?>" title="<?php print __("KMZ_TOOLTIP"); ?>"><?php print __("KMZ"); ?></a>
          </div>
          <?php
        }
      ?>

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
    <?php if($map->IsGeocoded) { ?>
      <div class="clear"></div>
      <div class="googleMapsContainer"></div>
    <?php } ?>      
    <div class="clear"></div>
  </div>

<?php
  }
?>