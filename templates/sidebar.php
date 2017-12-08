<div class="left_col scroll-view">
  <div class="navbar nav_title" style="border: 0;">
    <a href="index.php" class="site_title"><i class="fa fa-home"></i> <span>NEZZ</span></a>
  </div>

  <div class="clearfix"></div>

  <!-- menu profile quick info -->
  <div class="profile clearfix">
    <div class="profile_pic">      
      <img src="<?php echo $_SESSION['user_data']['profilePic']; ?>" alt="profile pic" class="img-circle profile_img" />      
    </div>
    <div class="profile_info">
      <span>Welcome,</span>
      <h2><?php echo $_SESSION['user_data']['firstName']." ".$_SESSION['user_data']['lastName'] ?></h2>
    </div>
  </div>
  <!-- /menu profile quick info -->

  <br />

  <!-- sidebar menu -->
  <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
    <div class="menu_section">
      <h3>Administration</h3>
      <ul class="nav side-menu">
        <li><a><i class="fa fa-user"></i> User <span class="fa fa-chevron-down"></span></a>
          <ul class="nav child_menu">
            <li><a href="user.php">Web Admin Logins</a></li>                        
          </ul>
        </li>

          <li><a><i class="fa fa-user"></i> Push Notification <span class="fa fa-chevron-down"></span></a>
              <ul class="nav child_menu">
                  <li><a href="send_push_notification.php">Send Push notification</a></li>
                 =
              </ul>
          </li>

          <li><a><i class="fa fa-user"></i> Articles <span class="fa fa-chevron-down"></span></a>
              <ul class="nav child_menu">
                  <li><a href="add_articles.php">Add Articles</a></li>
                  <li><a href="article_list.php">Article list</a></li>
              </ul>
          </li>

          <li><a><i class="fa fa-user"></i> Videos <span class="fa fa-chevron-down"></span></a>
              <ul class="nav child_menu">
                  <li><a href="add_videos.php">Add Videos</a></li>
                  <li><a href="video_list.php">Video list</a></li>
              </ul>
          </li>


          <li><a href="user_profile.php?type=P"><i class="fa fa-user"></i> Planner list</a></li>
<!--        <li><a><i class="fa fa-calendar"></i> Events <span class="fa fa-chevron-down"></span></a>-->
<!--          <ul class="nav child_menu">            -->
<!--            <li><a href="event_location_freq.php">Location Frequency</a></li>-->
<!--            <li><a href="event_list.php">Event List</a></li>-->
<!--            <li><a href="event_type.php">Manage Event Type</a></li>-->
<!--            <li><a href="event_genre_preference.php">Manage Genre Preference</a></li>-->
<!--            <li><a href="event_skill_preference.php">Manage Skills Preference</a></li>                        -->
<!--          </ul>-->
<!--        </li>-->
<!--        <li><a><i class="fa fa-tasks"></i> Reports <span class="fa fa-chevron-down"></span></a>-->
<!--          <ul class="nav child_menu">-->
<!--            <li><a href="report_events.php">Events</a></li>-->
<!--            <li><a href="javascript:(0);">Status <span class="fa fa-chevron-down"></span></a>-->
<!--              <ul class="nav child_menu">-->
<!--                <li class="sub_menu"><a href="report_status.php?type=P">Planner</a></li>-->
<!--                <li><a href="report_status.php?type=T">Talent</a></li>                -->
<!--              </ul>-->
<!--            </li>            -->
<!--            <li><a href="report_top_rated_talents.php">Top Rated Talents</a></li>-->
<!--            <li><a href="report_merchant_income.php">Merchant Income</a></li>            -->
<!--            <li><a href="report_sales.php">Sales</a></li>-->
<!--          </ul>-->
<!--        </li>-->
        <li><a><i class="fa fa-th"></i> General <span class="fa fa-chevron-down"></span></a>
          <ul class="nav child_menu">
            <li><a href="email_notification.php">Email Notification</a></li>
<!--            <li><a href="terms_and_conditions.php">Terms and Conditions</a></li>-->
<!--            <li><a href="privacy_policy.php">Privacy Policy</a></li>-->
<!--            <li><a href="merchant.php">Manage Merchant</a></li>-->
<!--            <li><a href="settings.php">Manage Settings</a></li>-->
          </ul>
        </li>
      </ul>
    </div>
  </div>
  <!-- /sidebar menu -->
</div>
