<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>{$PAGE_TITLE}</title>
    <link href="build/assets/common/img/favicon.144x144.png" rel="apple-touch-icon" type="image/png" sizes="144x144">
    <link href="build/assets/common/img/favicon.114x114.png" rel="apple-touch-icon" type="image/png" sizes="114x114">
    <link href="build/assets/common/img/favicon.72x72.png" rel="apple-touch-icon" type="image/png" sizes="72x72">
    <link href="build/assets/common/img/favicon.57x57.png" rel="apple-touch-icon" type="image/png">
    <link href="build/assets/common/img/favicon.png" rel="icon" type="image/png">
    <link href="favicon.ico" rel="shortcut icon">
    <style type="text/css">
        .iconRight { position:absolute; right:8px; top:10px;}
    </style>
    <!-- HTML5 shim and Respond.js for < IE9 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <!-- Vendors Styles -->
    <!-- v1.0.0 -->
    <link rel="stylesheet" type="text/css" href="build/assets/vendors/bootstrap/dist/css/bootstrap.min.css">
    <!-- <link rel="stylesheet" type="text/css" href="build/assets/vendors/jscrollpane/style/jquery.jscrollpane.css"> -->
    <!-- <link rel="stylesheet" type="text/css" href="build/assets/vendors/ladda/dist/ladda-themeless.min.css"> -->
    <!-- <link rel="stylesheet" type="text/css" href="build/assets/vendors/select2/dist/css/select2.min.css"> -->
    <!-- <link rel="stylesheet" type="text/css" href="build/assets/vendors/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css"> -->
    <link rel="stylesheet" type="text/css" href="build/assets/vendors/fullcalendar/dist/fullcalendar.min.css">
    <link rel="stylesheet" type="text/css" href="build/assets/vendors/fullcalendar/dist/fullcalendar.css">
    <!-- <link rel="stylesheet" type="text/css" href="build/assets/vendors/cleanhtmlaudioplayer/src/player.css"> -->
    <!-- <link rel="stylesheet" type="text/css" href="build/assets/vendors/cleanhtmlvideoplayer/src/player.css"> -->
    <!-- <link rel="stylesheet" type="text/css" href="build/assets/vendors/bootstrap-sweetalert/dist/sweetalert.css"> -->
    <!-- <link rel="stylesheet" type="text/css" href="build/assets/vendors/summernote/dist/summernote.css"> -->
    <!-- <link rel="stylesheet" type="text/css" href="build/assets/vendors/owl.carousel/dist/assets/owl.carousel.min.css"> -->
    <!-- <link rel="stylesheet" type="text/css" href="build/assets/vendors/ionrangeslider/css/ion.rangeSlider.css"> -->
    <!-- <link rel="stylesheet" type="text/css" href="build/assets/vendors/datatables/media/css/dataTables.bootstrap4.min.css"> -->
    <!-- <link rel="stylesheet" type="text/css" href="build/assets/vendors/c3/c3.min.css"> -->
    <!-- <link rel="stylesheet" type="text/css" href="build/assets/vendors/chartist/dist/chartist.min.css"> -->
    <!-- v1.4.0 -->
    <link rel="stylesheet" type="text/css" href="build/assets/vendors/nprogress/nprogress.css">
    <!-- <link rel="stylesheet" type="text/css" href="build/assets/vendors/jquery-steps/demo/css/jquery.steps.css"> -->
    <!-- v1.4.2 -->
    <!-- <link rel="stylesheet" type="text/css" href="build/assets/vendors/bootstrap-select/dist/css/bootstrap-select.min.css"> -->
    <!-- v1.7.0 -->
    <!-- <link rel="stylesheet" type="text/css" href="build/assets/vendors/dropify/dist/css/dropify.min.css"> -->
    <!-- Clean UI Styles -->
    <link rel="stylesheet" type="text/css" href="build/assets/common/css/main.min.css">
    <!-- <link rel="stylesheet" type="text/css" href="build/assets/common/css/source/main.css"> -->
    <style type="text/css">
    .fc-time{
       display : none;
    }
    </style>
</head>
<body class="theme-default">
    {*include file='inc.sidebar.tpl'*}
    {include file='inc.header.tpl'}         
    <section class="page-content">
        <section class="page-content-inner">
            <!-- Default Panel -->
            <section class="panel panel-with-borders">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-6 col-sm-6 col-md-7 col-lg-9">
                            <h3><i class="fa fa-user" aria-hidden="true"></i> Profile</h3>
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-5 col-lg-3">
                            {include file='datefilter.tpl'}
                        </div>    
                    </div>
                </div>
            </section>
            <!-- End Default Panel -->
            <!-- Panel with Borders -->
            <div class="row">
                <div class="col-md-12">
                    <section class="panel">
                        <div class="panel-heading clearfix" style="box-shadow: none;">  
                    <!-- <div class="pull-left">
                        <span class="h4 t-mention-count" id="tMentionCount">0</span> mentions
                    </div> -->
                    <div class="pull-right hidden">
                        <div class="btn-group margin-inline">
                            <button type="button" class="btn btn-sm btn-rounded btn-primary-outline pagi pagi-newer " data-limit="0">Newer</button>
                            <button type="button" class="btn btn-sm btn-rounded btn-primary-outline pagi pagi-older" data-limit="0">Older</button>
                        </div>
                    </div>
                    <div class="btn-group pull-right margin-right-10 hidden">
                        <button type="button" class="btn btn-sm dropdown-toggle perpage-txt" data-toggle="dropdown" data-perpage="10" aria-expanded="false">10 Mentions</button>
                        <ul class="dropdown-menu">                    
                            <a class="dropdown-item perpage" data-perpage="10" href="javascript: void(0);">10 Mentions</a>
                            <a class="dropdown-item perpage" data-perpage="25" href="javascript: void(0);">25 Mentions</a>
                            <a class="dropdown-item perpage" data-perpage="50" href="javascript: void(0);">50 Mentions</a>
                            <a class="dropdown-item perpage" data-perpage="100" href="javascript: void(0);">100 Mentions </a>
                            <a class="dropdown-item perpage" data-perpage="200" href="javascript: void(0);">200 Mentions</a>
                            <li class="dropdown-divider"> </li>
                            <a class="dropdown-item perpage" data-perpage="500" href="javascript: void(0);">500 Mentions</a>
                        </ul>
                    </div> 
                    <div class="pull-right hidden">
                        <div class="btn-group margin-inline" aria-label="" role="group">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-primary btn-rounded dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                    Download
                                </button>
                                <ul class="dropdown-menu">
                                    <a class="dropdown-item" href="javascript: void(0);">As .CSV</a>
                                    <a class="dropdown-item" href="javascript: void(0);">As .PDF</a>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <!-- cfbm-no-results -->
                        <div class="margin-top-10 text-center hidden" id="cfbm-noresults">
                          <div class="col-md-6 col-md-offset-3 widget-holder">
                            <i class="fa fa-compass text-warning fa-5x fa-spin"></i>
                            <h1 class="page-header">No Mentions Here.</h1>
                            <p> 
                              Check your filters, or, wait while our crawlers scurry across the internets, seeking out conversations. 
                          </p>
                      </div>
                  </div>
                  <!-- cfbm-no-results -->
                  <div class="col-lg-12" id="cbfm-resultset"> 
                    <!-- myaccount -->
                    <div class="row">
                        <div class="col-xl-4 col-lg-12">
                            <div class="widget widget-one">
                                <div class="widget-header height-300" style="background-image: url(build/assets/common/img/temp/photos/6a847eca5ee389cb84bd0e6166842f8f.jpg)">
                                    <h2 class="color-white">
                                        Greetings
                                    </h2>
                                </div>
                                <div class="widget-body clearfix">
                                    <div class="s1">
                                        <a class="avatar" href="javascript:void(0);">
                                            <img src="build/assets/common/img/temp/avatars/0.svg" alt="Alternative text to the image">
                                        </a>
                                        <br>
                                        <strong>{$user_name}</strong>
                                    </div>
                                    <div class="s2">
                                        <div class="widget-info">
                                            <strong>Last login</strong><br />
                                            {*26 March 2017 06:45 IST*} 
                                            {$access_log.0.insert_time|date_format:"%d %B %Y %H:%M"} IST
                                        </div>
                                    </div>
                                    {if $smarty.session.usr_type != 2}
                                    <div class="s3">
                                        <div class="widget-info">
                                            <strong> Tagged Count</strong><br />
                                            <div id="profile_tagged_count">{$tagged_mention_count}</div>
                                        </div>
                                    </div>
                                    <div class="s4">
                                        <div class="widget-info">
                                            <strong>Ignored Count</strong><br />
                                            <div id="profile_ignored_count">{$ignored_mention_count}</div>
                                        </div>
                                    </div>
                                    {/if}
                                </div>
                            </div>
                            {if $smarty.session.usr_type != 2}
                            <section class="widget panel">
                                <div class="panel-body">
                                    <div class="profile-user-skills" id="select-client-div">
                                        <h6>My Clients</h6>
                                        {if count($user_clients) gt 0}
                                        {foreach from=$user_clients item=info_client name=clientName key=cKey}
                                        <div class="row margin-bottom-10">
                                            <div class="col-xs-4 col-sm-4 col-md-4">
                                                <a href="load.php?i=select-client&client_id={$info_client.client_id}"><img src="assets/common/img/logo-ac-{$info_client.client_db}.jpg" width="100px" style="border:1px solid #cfcfcf;"/></a>
                                            </div>
                                            <div class="col-xs-8 col-sm-8 col-md-8">
                                                <br /><a href="load.php?i=select-client&client_id={$info_client.client_id}"><strong>{$info_client.client_name}</strong></a><br /><br />
                                                {assign var="fav_class" value="btn-primary-outline"}
                                                
                                                {if $info_client.is_favourite eq 1 }
                                                	{assign var="fav_class" value="btn-primary"}	
                                                {else}
                                                	{assign var="fav_class" value="btn-primary-outline"}
                                                {/if}
                                                
                                                <button type="button" class="btn btn-xs {$fav_class} set_client_favourite margin-inline" id="set_fav_client_{$info_client.user_id}_{$info_client.client_id}">
                                                    <i class="fa fa-heart-o" aria-hidden="true"></i> set as favourite
                                                </button>

                                            </div>
                                        </div>
                                        {/foreach}
                                        {/if}
                                                <!-- <div class="row margin-bottom-10">
                                                    <div class="col-xs-4 col-sm-4 col-md-4">
                                                        <img src="http://confab.dev.pinstorm.com/assets/common/img/logo-ac-nseit.jpg" width="100px" style="border:1px solid #cfcfcf;"/>
                                                    </div>
                                                    <div class="col-xs-8 col-sm-8 col-md-8">
                                                        <br /><strong>NSEIT</strong><br /><br />
                                                        <button type="button" class="btn btn-xs btn-primary-outline margin-inline">
                                                            <i class="fa fa-heart-o" aria-hidden="true"></i> set as favourite
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="row margin-bottom-10">
                                                    <div class="col-xs-4 col-sm-4 col-md-4">
                                                        <img src="http://confab.dev.pinstorm.com/assets/common/img/logo-ac-nseit.jpg" width="100px" style="border:1px solid #cfcfcf;"/>
                                                    </div>
                                                    <div class="col-xs-8 col-sm-8 col-md-8">
                                                        <br /><strong>NSEIT</strong><br /><br />
                                                        <button type="button" class="btn btn-xs btn-primary-outline margin-inline">
                                                            <i class="fa fa-heart-o" aria-hidden="true"></i> set as favourite
                                                        </button>
                                                    </div>
                                                </div> -->
                                            </div>
                                        </div>
                                    </section>
                                    {/if}
                                </div>
                                <div class="col-xl-8 col-lg-12">
                                    <section class="panel profile-user-content">
                                        <div class="panel-body">
                                            <div class="nav-tabs-horizontal">
                                                <ul class="nav nav-tabs" role="tablist">
                                                {if $smarty.session.usr_type != 2}
                                                    <li class="nav-item">
                                                        <a class="nav-link active" href="javascript: void(0);" data-toggle="tab" data-target="#posts" role="tab">
                                                            <i class="icmn-menu3"></i>
                                                            My Stats
                                                        </a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" href="javascript: void(0);" data-toggle="tab" data-target="#messaging" role="tab">
                                                            <i class="icmn-bubbles5"></i>
                                                            Messages
                                                        </a>
                                                    </li>
                                                {/if}
                                                    {if $is_user_admin.0.role eq 1}
                                                    {if $smarty.session.usr_type != 2}
                                                    <!-- <li class="nav-item">
                                                        <a class="nav-link" href="javascript: void(0);" data-toggle="tab" data-target="#settings" role="tab">
                                                            <i class="icmn-cog"></i>
                                                            Monitor
                                                        </a>
                                                    </li> -->
                                                    <li class="nav-item">
                                                        <a class="nav-link" href="javascript: void(0);" data-toggle="tab" data-target="#login_time_screen" role="tab" id="u_stats">
                                                           <i class="fa fa-users" aria-hidden="true"></i>
                                                            User Status 
                                                        </a>
                                                    </li>
                                                    {else}
                                                    <li class="nav-item">
                                                        <a class="nav-link active" href="javascript: void(0);" data-toggle="tab" data-target="#user-stats2" role="tab" id="client_access_log">
                                                           <i class="fa fa-users" aria-hidden="true"></i>
                                                            User logs 
                                                        </a>
                                                    </li> 
                                                    {/if}
                                                    {/if}

                                                    {if $is_user_admin.0.role eq 1}
                                                    {if $is_user_admin.0.client_id eq 1}
                                                        <li class="nav-item">
                                                            <a class="nav-link overview" href="javascript: void(0);" data-toggle="tab" data-target="#overview-tab" id="overview" role="tab">
                                                               <i class="fa fa-calendar" aria-hidden="true"></i>
                                                                Overview
                                                            </a>
                                                        </li>
                                                    {/if}
                                                    {/if}


                                                </ul>
                                                <div class="tab-content padding-vertical-20">
                                                {if $smarty.session.usr_type != 2}
                                                    <div class="tab-pane active" id="posts" role="tabpanel">
                                                        <div class="user-wall no-box-shadow">
                                                            <!-- company-stats -->
                                                            <table class="table" id="dimension_stats">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Company</th>
                                                                        
                                                                        <!-- <th class="text-right">Ignored</th> -->
                                                                        <th class="text-right">Pending</th>
                                                                        <th class="text-right">Tagged</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    
                                                                    
                                                                </tbody>
                                                            </table>
                                                            <!-- /company-stats -->    
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane" id="messaging" role="tabpanel">
                                                        <div class="conversation-block">
                                                            <div class="conversation-item">
                                                                <div class="s1">
                                                                    <a class="avatar" href="javascript:void(0);">
                                                                        <img src="https://openclipart.org/download/247319/abstract-user-flat-3.svg" alt="">
                                                                    </a>
                                                                </div>
                                                                <div class="s2">
                                                                    <strong>Nikhil Sheth</strong>
                                                                    <p>
                                                                        It's all about numbers - numbers without mistakes.
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>    
                                                    </div>
                                                    {else}
                                                    {if $is_user_admin.0.role eq 1}
                                                    <div class="tab-pane active" id="user-stats2" role="tabpanel">
                                                        <div class="conversation-block">
                                                            <div class="conversation-item" id="client_user_log_results">
                                                            </div>
                                                        </div>    
                                                    </div>
                                                    {/if}
                                                    {/if}


                                                    <div class="tab-pane" id="settings" role="tabpanel">
                                                        <!-- agent-stats -->
                                                        <table class="table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Avatar</th>
                                                                    <th>Agent</th>
                                                                    <th class="text-center">Last Seen</th>
                                                                    <th class="text-right">Tagged Count</th>
                                                                    <th class="text-right">Ignored Count</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                {if count($last_tag_data) gt 0}
                                                                {foreach from=$last_tag_data key=lastTaggedKeyKey item=lastTagged name=lName}
                                                                    <tr>
                                                                        <td>
                                                                            <a class="avatar" href="javascript:void(0);">
                                                                                <img src="https://openclipart.org/download/247319/abstract-user-flat-3.svg" alt="Alternative text to the image">
                                                                            </a>
                                                                        </td>
                                                                        <td>
                                                                            <a href="javascript: void(0);" class="link-underlined link-blue" data-toggle="tooltip" data-placement="right" title="" data-original-title="">
                                                                                {$lastTagged.user_name}
                                                                            </a>
                                                                        </td>
                                                                        <td class="text-center">{$lastTagged.tagged_time|date_format:"%H:%M %p, %e  %B %Y"}</td>
                                                                        <td class="text-right">
                                                                            {$lastTagged.tagged_mention_count}
                                                                        </td>
                                                                        <td class="text-right color-danger">
                                                                            <strong>{$lastTagged.ignored_mention_count}</strong>
                                                                        </td>
                                                                    </tr>
                                                                {/foreach}
                                                                {else}
                                                                    No Tagged today.
                                                                {/if}
                                                            </tbody>
                                                        </table>
                                                        <!-- /agent-stats --> 
                                                    </div>
                                                    <div class="tab-pane" id="login_time_screen" role="tabpanel">
                                                        <div class="conversation-block">
                                                            <div class="conversation-item" id="thumbnailcontainer" style="height: 450px;overflow: hidden;">
                                                                <!-- <iframe src="http://login.pinstorm.com/HRMS/AttendanceMonitor/?d=orm" height="100%" width="100%"></iframe> -->
                                                            </div>
                                                        </div>    
                                                    </div>
                                                    <div id="statscontainer">
                                                        
                                                    </div>

                                                    <div class="tab-pane active" id="overview-tab" role="tabpanel" style="visibility:hidden;">
                                                        <div class="conversation-block">
                                                            <div class="conversation-item" style="height: 594px;overflow: hidden;">
                                                                <h3 class="page-header"><i class="fa fa-calendar"></i> Overview <i class="fa fa-info-circle animated bounceInDown show-info"></i> </h3>
                                                                <div class="col-md-9">
                                                                  <div id='calendar'></div>
                                                                </div>
                                                                
                                                                <div class="col-md-3">
                                                                    <div id='external-events' style="height: 594px;overflow-y: scroll;">

                                                                       <table class="table" id="company-stats">
                                                                           <thead>
                                                                            <tr>This Month</tr>
                                                                               <tr>
                                                                                   <th>Company</th>
                                                                                   <th class="text-right">Pending</th>
                                                                                   <th class="text-right">Tagged</th>
                                                                               </tr>
                                                                           </thead>
                                                                           <tbody>
                                                                               
                                                                               
                                                                           </tbody>
                                                                       </table>

                                                                       <table class="table" id="user-tagged-stats">
                                                                           <thead>
                                                                           <tr>
                                                                               
                                                                           </tr>
                                                                            
                                                                               <tr>
                                                                                   <th>User</th>
                                                                                   <th class="text-right">Tagged Count</th>
                                                                               </tr>
                                                                           </thead>
                                                                           </thead>
                                                                           <tbody>
                                                                               
                                                                               
                                                                           </tbody>
                                                                       </table>
                                                                    </div>
                                                                    <div style="display:none;">

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>    
                                                    </div>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    </section>
                                </div>
                            </div>
                            <!-- /myaccount -->
                        </div>
                    </div>
                </div>
                <!-- static Mention structure -->
                <div id="cfbm-tag-static" class="hidden"> 
                    {include file='partial.profile.tpl'}                
                </div>  
                <div id="cfbm-tag-static1" class="hidden"> 
                    {include file='partial.profile.side.tpl'}                
                </div> 
                <div id="click" class="hidden"></div> 
                <!-- /static Mention structure -->
            </section>
        </div>
    </div>
    <!-- End Panel with Borders -->
</section>
</section>
<div class="cwt__footer visible-footer">
    {include file='inc.footer.tpl'}             
</div>
</div>
<div class="main-backdrop"><!-- --></div>
<!-- Vendors Scripts -->
<!-- v1.0.0 -->
<script src="build/assets/vendors/jquery/jquery.min.js"></script>
<script src="build/assets/vendors/tether/dist/js/tether.min.js"></script>
<script src="build/assets/vendors/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- <script src="build/assets/vendors/jquery-mousewheel/jquery.mousewheel.min.js"></script> -->
<!-- <script src="build/assets/vendors/jscrollpane/script/jquery.jscrollpane.min.js"></script> -->
<!-- <script src="build/assets/vendors/spin.js/spin.js"></script> -->
<!-- <script src="build/assets/vendors/ladda/dist/ladda.min.js"></script> -->
<!-- <script src="build/assets/vendors/select2/dist/js/select2.full.min.js"></script> -->
<!-- <script src="build/assets/vendors/html5-form-validation/dist/jquery.validation.min.js"></script> -->
<!-- <script src="build/assets/vendors/jquery-typeahead/dist/jquery.typeahead.min.js"></script> -->
<!-- <script src="build/assets/vendors/jquery-mask-plugin/dist/jquery.mask.min.js"></script> -->
<!-- <script src="build/assets/vendors/autosize/dist/autosize.min.js"></script> -->
<!-- <script src="build/assets/vendors/bootstrap-show-password/bootstrap-show-password.min.js"></script> -->
<script src="build/assets/vendors/moment/min/moment.min.js"></script>
<!-- <script src="build/assets/vendors/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script> -->
<script src="build/assets/vendors/fullcalendar/dist/fullcalendar.min.js"></script>
<!-- <script src="build/assets/vendors/cleanhtmlaudioplayer/src/jquery.cleanaudioplayer.js"></script> -->
<!-- <script src="build/assets/vendors/cleanhtmlvideoplayer/src/jquery.cleanvideoplayer.js"></script> -->
<!-- <script src="build/assets/vendors/bootstrap-sweetalert/dist/sweetalert.min.js"></script> -->
<!-- <script src="build/assets/vendors/remarkable-bootstrap-notify/dist/bootstrap-notify.min.js"></script> -->
<!-- <script src="build/assets/vendors/summernote/dist/summernote.min.js"></script> -->
<!-- <script src="build/assets/vendors/owl.carousel/dist/owl.carousel.min.js"></script> -->
<!-- <script src="build/assets/vendors/ionrangeslider/js/ion.rangeSlider.min.js"></script> -->
<!-- <script src="build/assets/vendors/nestable/jquery.nestable.js"></script> -->
<!-- <script src="build/assets/vendors/datatables/media/js/jquery.dataTables.min.js"></script> -->
<!-- <script src="build/assets/vendors/datatables/media/js/dataTables.bootstrap4.min.js"></script> -->
<!-- <script src="build/assets/vendors/datatables-fixedcolumns/js/dataTables.fixedColumns.js"></script> -->
<!-- <script src="build/assets/vendors/datatables-responsive/js/dataTables.responsive.js"></script> -->
<!-- <script src="build/assets/vendors/editable-table/mindmup-editabletable.js"></script> -->
<!-- <script src="build/assets/vendors/d3/d3.min.js"></script> -->
<!-- <script src="build/assets/vendors/c3/c3.min.js"></script> -->
<!-- <script src="build/assets/vendors/chartist/dist/chartist.min.js"></script> -->
<script src="build/assets/vendors/peity/jquery.peity.min.js"></script>
<!-- v1.0.1 -->
<!-- <script src="build/assets/vendors/chartist-plugin-tooltip/dist/chartist-plugin-tooltip.min.js"></script> -->
<!-- v1.1.1 -->
<!-- <script src="build/assets/vendors/gsap/src/minified/TweenMax.min.js"></script> -->
<!-- <script src="build/assets/vendors/hackertyper/hackertyper.js"></script> -->
<!-- <script src="build/assets/vendors/jquery-countTo/jquery.countTo.js"></script> -->
<!-- v1.4.0 -->
<script src="build/assets/vendors/nprogress/nprogress.js"></script>
<!-- <script src="build/assets/vendors/jquery-steps/build/jquery.steps.min.js"></script> -->
<!-- v1.4.2 -->
<!-- <script src="build/assets/vendors/bootstrap-select/dist/js/bootstrap-select.min.js"></script> -->
<!-- <script src="build/assets/vendors/chart.js/src/Chart.bundle.min.js"></script> -->
<!-- v1.7.0 -->
<!-- <script src="build/assets/vendors/dropify/dist/js/dropify.min.js"></script> -->
<!-- Daterangepicker -->
<script src="build/assets/vendors/bootstrap-daterangepicker/daterangepicker.js"></script>
<!-- /Daterangepicker -->
<!-- Chosen -->
<script src="build/assets/vendors/chosen/chosen.jquery.js"></script>
<!-- /Chosen -->
<!-- flip -->
<!-- <script src="build/assets/vendors/flip/dist/jquery.flip.min.js"></script> -->
<!-- /flip -->
<!-- Clean UI Scripts -->
<script src="build/assets/common/js/common.js"></script>
<script src="build/assets/common/js/confab.js"></script>
<!-- <script src="build/assets/common/js/demo.temp.js"></script> -->

{literal}
<script type="text/javascript">
    
    $(document).ready(function(){

        

            $('#calendar').fullCalendar({

                eventStartEditable: false,
                eventClick: function(calEvent, jsEvent, view) {

                          var mydate=calEvent.start;

                          
                          var ndate = new Date(mydate);
                          
                          $('#user-tagged-stats tbody').html('');
                          var url = "load.php?i=ajax";
                          var data={"action":"cal_day_data","day":ndate};
                          CONFAB.ajaxCaller(url,data,display_day_data,"json");

                },
                eventMouseover : function(data, event, view) {
                    $(".fc-event").css('cursor', 'pointer');
                
                 }
                  
              
        });

        function display_day_data(response){
            var tpl_content = $("#struct_tagged_stats_tbody").html();
            var html = "";
            // $("#company-stats").hide();
            $("#company-stats").addClass("hidden");
            $('#user-tagged-stats').removeClass("hidden");
             

             // console.log(tpl_content);return false;
             $.each(response.payload,function(index,value){

                   
                    var optHtml = '';
                    optHtml = tpl_content;                    
                    optHtml = optHtml.replace("STR_USER_NAME",value.email);
                    optHtml = optHtml.replace("STR_TAGGED_COUNT",value.count);
                    
                    html +=optHtml;
                    
                });
            // console.log(html);
                $(html).appendTo('#user-tagged-stats tbody').show('slow');

                $("#user-tagged-stats thead tr:first").html(response.day);

        }


        $("#overview-tab").removeClass("active");
        $("#overview-tab").css("visibility", "visible");


        $('#client_access_log').on("click",function(){
            NProgress.start();
            var url = "load.php?i=ajax";
            var cfbToken = $("#cfbToken").val();
            var datefilter = $("#datefilter").val();
            var dateParts = datefilter.split(" - ");    
            var data = {"action":"client-user-stats-load","startdate":dateParts[0],"enddate":dateParts[1],"cfbToken":cfbToken};
            CONFAB.ajaxCaller(url,data,display_client_stats,"json");
            NProgress.done();
        });
        function display_client_stats(response){
            // console.log(response);
            //CONFAB.console(response);
            if(response.code==200){
                var str = '<div class="accordion" id="accordion">';
                var ii=1;
                $('#client_user_log_results').html('');

                $.each(response.payload,function(index,value){
                    // console.log(index+'=='+value);
                    $.each(value,function(i,val){
                        // console.log(i+'=='+val.user_name);
                        if(i==0){
                            str += '<div class="card"><div aria-controls="collapse'+ii+'" aria-expanded="false" class="card-header collapsed" data-parent="#accordion" data-target="#collapse'+ii+'" data-toggle="collapse" id="heading'+ii+'" role="tab"><div class="card-title"><span class="accordion-indicator pull-right color-primary"><i class="plus fa fa-plus"></i><i class="minus fa fa-minus"></i></span><a><span style="padding-left:10px;">'+val.user_name+'</span><span style="float:right;padding-right:15px;">'+val.login_time+'</span></a></div></div><div aria-labelledby="heading'+ii+'" class="card-collapse collapse" id="collapse'+ii+'" role="tabcard" aria-expanded="false"><div class="card-block"><span>Login History</span>';
                        } else {
                            str += '<span style="float:right;font-size: 13px;margin-right: 30px;">'+val.login_time+'</span><br>';
                        }
                        ii++;
                    });
                    str +='</div></div></div>';
                });
                str+="</div>";
            }
            $('#client_user_log_results').append(str);
            
            
            
        }
        $("#date-filter-change").on("click",function(){
            // event.preventDefault();
            console.log("inside date filter function");
            var url = "load.php?i=ajax";                       
            var cfbToken = $("#cfbToken").val();
            var datefilter = $("#datefilter").val();
            var dateParts = datefilter.split(" - ");           
            var data = {"action":"get-my-stats","startdate":dateParts[0],"enddate":dateParts[1],"cfbToken":cfbToken};
            CONFAB.ajaxCaller(url,data,display_my_stats,"json");
            //progress bar start
            NProgress.start();
            $('#dimension_stats tbody').html('');

            // FOR USER LOGS
            var url = "load.php?i=ajax";
            var data = {"action":"client-user-stats-load","startdate":dateParts[0],"enddate":dateParts[1]};
            CONFAB.ajaxCaller(url,data,display_client_stats,"json");

        });
        function display_my_stats(response){
            //console.log(response);
            if(response.code==200){
                var tpl_content = $("#cfbm-tag-static div#struct_dimension_stats table tbody").html();
                var html = "";
                
                $("#profile_tagged_count").html(response.payload.tagged_mentions);        
                $("#profile_ignored_count").html(response.payload.ignored_mentions);        

                $.each(response.payload.dimension_stats,function(index,value){
                    var optHtml = '';
                    optHtml += tpl_content;
                    var dimension_url = "load.php?i=pending&comp_id="+value.dimension_id;
                    optHtml = optHtml.replace("STR_DIMENSION_URL",dimension_url);
                    optHtml = optHtml.replace("STR_DIMENSION_PENDING_URL",dimension_url);
                    optHtml = optHtml.replace("STR_DIMENSION_NAME",index);
                    optHtml = optHtml.replace("STR_DIMENSION_TAGGED_MENTIONS",value.tagged_count);
                    optHtml = optHtml.replace("STR_DIMENSION_PENDING_MENTIONS",value.pending_count);
                    html +=optHtml;
                    
                });
                $(html).appendTo('#dimension_stats tbody').show('slow');
            }
            NProgress.done();
        }

        // $('.fc-prev-button , .fc-next-button').on('click',function(){
           
           
        // });


        


        $(".overview, .fc-prev-button , .fc-next-button, .fc-today-button").on("click",function(){

            $('#user-tagged-stats').addClass("hidden");
            $('#company-stats').removeClass("hidden");
            $('#statscontainer').html('');

            var startdate=new Date($("#calendar").fullCalendar('getView').intervalStart);

            var enddate= new Date($('#calendar').fullCalendar('getView').intervalEnd);
            
            var url = "load.php?i=ajax";                       
            var cfbToken = $("#cfbToken").val();
            var datefilter = $("#datefilter").val();
            var dateParts = datefilter.split(" - ");           
            var data = {"action":"get_overview","startdate":startdate,"enddate":enddate,"cfbToken":cfbToken};
            CONFAB.ajaxCaller(url,data,display_overview,"json");

            $("#overview").removeClass("overview");
            //progress bar start
            NProgress.start(); 
            $('#company-stats tbody').html(''); 
        });
        function display_overview(response){
                   

                var date = new Date();
                var d = date.getDate();
                var m = date.getMonth();
                var y = date.getFullYear();
                var events= new Array();
                $.each(response.total,function(index,value){
        
                            event=new Object();
                            event.title="Pending:"+value.Pending;
                            event.start=index;
                            event.color="red";
                            event.allDay=false;



                            events.push(event);

                            event1=new Object();
                            
                            event1.title="Tagged:"+value.Tagged;
                            event1.start=index;
                            event1.color="green";
                            event1.allDay=false;
                            events.push(event1);
                  
                    



                });
                    



                
                $('#calendar').fullCalendar('removeEventSources'); 
                $('#calendar').fullCalendar('addEventSource',events);

                // $("#company-stats").html('');
                if(response.code==200){
                    var tpl_content = $("#cfbm-tag-static div#struct_dimension_stats table tbody").html();
                    var html = "";
                    
                    $("#profile_tagged_count").html(response.payload.tagged_mentions);        
                    $("#profile_ignored_count").html(response.payload.ignored_mentions);        

                    $.each(response.payload.dimension_stats,function(index,value){
                        var optHtml = '';
                        optHtml += tpl_content;
                        var dimension_url = "load.php?i=pending&comp_id="+value.dimension_id;
                        optHtml = optHtml.replace("STR_DIMENSION_URL",dimension_url);
                        optHtml = optHtml.replace("STR_DIMENSION_PENDING_URL",dimension_url);
                        optHtml = optHtml.replace("STR_DIMENSION_NAME",index);
                        optHtml = optHtml.replace("STR_DIMENSION_TAGGED_MENTIONS",value.tagged_count);
                        optHtml = optHtml.replace("STR_DIMENSION_PENDING_MENTIONS",value.pending_count);
                        html +=optHtml;
                        
                    });
                    $(html).appendTo('#company-stats tbody').show('slow');
                }

                NProgress.done();
                
        }


        $("#u_stats").click(function(){
            var url="load.php?i=ajax";
            var data ={"action":"orm_users"};
            CONFAB.ajaxCaller(url,data,display_user,"json");
        });

        function display_user(response){
            var pinString=''; 
            $.each(response.payload,function(index,value){
                var photo = value.Photo;
                var name = value.Name;
                
                var status = value.status;
                var logintime = value.LoginTime;
                var logouttime = value.LogoutTime;


                var className = value.className;
                var workinghour = value.WorkingHour;
                var imgclass = '';
                var settimecolor='';
                var seticon='';
                var email=value.email;
                
               
                                                    
                
                var time = '&nbsp;';
                                                    
                 if(typeof logintime === 'undefined' && typeof logouttime === 'undefined' ){
                     time='&nbsp;';
                        workinghour='00:00';
                     }                                    
                
                  else if(logintime != '' &&  logouttime == '0' ){
                    var res = logintime.split(" "); // if only login then show it
                    var remove_second =  res[1].split(":");
                    time = remove_second[0]+':'+remove_second[1];
                  }
                 else{
                    var res = logouttime.split(" "); // if only login then show it
                    var remove_second =  res[1].split(":");
                    time = remove_second[0]+':'+remove_second[1];
                  }
     
                
                if(status=='at-home'){
                    imgclass = 'img-circle img-responsive shadowEffectRed notIn';
                    settimecolor='';
                    seticon='';
                }
                else if(status=='login-office'){
                    imgclass = 'img-circle img-responsive shadowEffect';
                    settimecolor='colorgreen';
                    seticon='<img src="account/nseit/assets/common/img/green.png" class="iconRight"  height="30" >';
                }
                else if(status=='logout-office'){
                    imgclass = 'img-circle img-responsive shadowEffect notIn';
                    settimecolor='colorred';
                    seticon='<img src="account/nseit/assets/common/img/red.png" class="iconRight "  height="30" >';
                }
                else if(status=='login-home'){
                    imgclass = 'img-circle img-responsive shadowEffectHome';
                    settimecolor='colorgreen';
                    seticon='<img src="account/nseit/assets/common/img/home.png" class="iconRight"  height="30" >';
                }
                else if(status=='logout-home'){
                    imgclass = 'img-circle img-responsive shadowEffectHome notIn';
                    settimecolor='colorred';
                    seticon='<img src="account/nseit/assets/common/img/red.png" class="iconRight "  height="30" >';
                }
                else if(status=='on-leave'){
                    imgclass = 'img-circle img-responsive shadowEffectOrange notIn';
                    settimecolor='colorOrange';
                    seticon='<img src="account/nseit/assets/common/img/orange.png" class="iconRight"  height="30" >';
                    time='On Leave';
                }
                
                if(className!=''){
                    imgclass=imgclass+' '+className;
                }
               
                
                if(photo=='blankprofile.gif'){
                    photo = 'blankprofile2.gif';
                }
                
                
                
                pinString = pinString+'<div class="col-sm-1" align="center"><div class="setbox"><img width="100%" src="account/nseit/assets/common/img/'+photo+'" class="'+imgclass+'"><h5 class="personName" >'+name+'</h5><div class="setTime '+settimecolor+'">'+time+'<br>('+workinghour+')</div>'+seticon+'<span id="el" style="visibility:hidden;">'+email+'</span><span id="lgo" style="visibility:hidden;">'+value.outtime+'</span><span id="lgi" style="visibility:hidden;">'+value.intime+'</span></div></div>';
                
            });
            $('#thumbnailcontainer').html(pinString);
            
        }

        $(document).on('click', '.setbox', function(){
            var in_time=$(this).find('#lgi').text();
            var out_time=$(this).find('#lgo').text();
            // console.log(in_time+" ###"+out_time);return false;

            var email_id=$(this).find('#el').text();
            var url="load.php?i=ajax";
            var data ={"action":"user_stats","mail_id":email_id,"intime":in_time,"outtime":out_time};
            NProgress.start();
            CONFAB.ajaxCaller(url,data,display_user_stats,"json");
        });
        function display_user_stats(response){
            var tpl_content = $("#struct_tagged_stats").html();
            var optHtml='';
            

            if($.isEmptyObject(response.assigned)){
                optHtml+="Counting......No Records";
                $("#statscontainer").html(optHtml);
                NProgress.done();
              }else{
                    

                    optHtml+="<table class='table'><thead><tr><tr><td><b>"+response.email+"</b></td><td>Last Seen : "+response.lastseen+"</td><td>In:"+response.intime+"</td><td> Out:"+response.outtime+"</td></td></tr><td>Company Name</td><td>Tagged Count</td><td>Qc count</td><td>Ignore Count</td></tr></thead><tbody>"
                    $.each(response.assigned,function(index,value){
                    
                    // optHtml=tpl_content;
                    // optHtml = optHtml.replace("STR_USER_NAME","Tagged");
                    // optHtml = optHtml.replace("STR_TAGGED_COUNT",value);
                    
                    // html +=optHtml;
                    // console.log(index+'='+value);
                 optHtml+='<tr><td>'+index+'</td><td>'+value.tagged_count+'</td><td>'+value.qc_count+'</td><td>'+value.ignore_count+'</td></tr>';
                });
                   $.each(response.unassigned,function(indexi,valuei){
                    optHtml+='<tr><td>*'+indexi+'</td><td>'+valuei.tagged+'</td><td>'+valuei.qc+'</td><td>'+valuei.ignore+'</td></tr>';
                }); 
                optHtml+="</tbody></table>"; 
               $("#statscontainer").html(optHtml);
               NProgress.done();
            }
            
            
        }
        


    
    $("#date-filter-change").click();
    $("#click").click();

});
</script>
{/literal}
</body>
</html>