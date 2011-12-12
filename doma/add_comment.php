<?php
  include_once(dirname(__FILE__) ."/add_comment.controller.php");
  
  $controller = new AddCommentController();
  $vd = $controller->Execute();
  $comment = $vd["Comment"];
  //print_r($comment);
  if($comment->ID != 0) 
  {
    ?>
  <div class="commentPanel" id="commentPanel-<?php print $comment->ID ?>" align="left" >
    <label class="postedComments">
    <span class="commentName">
      <?php if($comment->Email != "") 
        {
        ?><a href="mailto:<?php print $comment->Email ?>"><?php print $comment->Name ?></a>
        <?php
        } else {
        print $comment->Name;
        }
        ?>
    </span>
    <span>
      <?php print Helper::ClickableLink($comment->Comment) ?> 
    </span>
    </label>
    <br clear="all" />
    <label class="postedTime">
    <abbr class="timeago" title="<?php print $comment->DateCreated?>"></abbr>
    </label>
    <?php
    $userip = $_SERVER['REMOTE_ADDR'];
    if(($comment->UserIP == $userip)||($map->UserID == Helper::GetLoggedInUser()->ID))
      {?>
      &nbsp;&nbsp;<a href="#" id="CID-<?php  echo $comment->ID;?>" class="c_delete"><?php print __("DELETE") ?></a>
      <?php
      }?>
  </div>
  <?php
  }
  ?>
  
