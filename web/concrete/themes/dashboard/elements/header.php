<? defined('C5_EXECUTE') or die("Access Denied."); ?>
<? 
if ($_GET['_ccm_dashboard_external']) {
	return;
}

if (!defined('WHITE_LABEL_DASHBOARD_BACKGROUND_SRC')) {
	Config::getOrDefine('WHITE_LABEL_DASHBOARD_BACKGROUND_SRC', false);
}

if (!defined('WHITE_LABEL_DASHBOARD_BACKGROUND_CAPTION')) {
	Config::getOrDefine('WHITE_LABEL_DASHBOARD_BACKGROUND_CAPTION', false);
}

if (!defined('WHITE_LABEL_DASHBOARD_BACKGROUND_FEED')) {
	Config::getOrDefine('WHITE_LABEL_DASHBOARD_BACKGROUND_FEED', false);
}

Loader::block('autonav');
$nh = Loader::helper('navigation');
$dashboard = Page::getByPath("/dashboard");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<?
Loader::library('3rdparty/mobile_detect');
$md = new Mobile_Detect();

$html = Loader::helper('html');
$v = View::getInstance();

$logouttoken = Loader::helper('validation/token')->generate('logout');

$req = Request::get();
$req->requireAsset('dashboard');
// Required JavaScript
/*$md = new Mobile_Detect();
if ($md->isMobile()) {
	$this->addFooterItem($html->javascript('jquery.ui.touch-punch.js'));
}*/

$v->addFooterItem('<script type="text/javascript" src="' . REL_DIR_FILES_TOOLS_REQUIRED . '/i18n_js"></script>'); 
$v->addFooterItem('<script type="text/javascript">$(function() { CCMToolbar.start(); });</script>');

if (ENABLE_PROGRESSIVE_PAGE_REINDEX && Config::get('DO_PAGE_REINDEX_CHECK')) {
	$v->addFooterItem('<script type="text/javascript">$(function() { ccm_doPageReindexing(); });</script>');
}

if (LANGUAGE != 'en') {
	$v->addFooterItem($html->javascript('i18n/ui.datepicker-'.LANGUAGE.'.js'));
}

// Require CSS
/*if ($md->isMobile() == true) {
	$v->addHeaderItem($html->css('ccm.app.mobile.css')); ?>
	<?		
}
*/
$valt = Loader::helper('validation/token');
$disp = '<script type="text/javascript">'."\n";
$disp .=  "var CCM_SECURITY_TOKEN = '" . $valt->generate() . "';"."\n";
$disp .= "</script>"."\n";
//require(DIR_FILES_ELEMENTS_CORE . '/header_required.php'); 
$v->addHeaderItem($disp);
Loader::element('header_required', array('disableTrackingCode' => true));
$backgroundImage = Loader::helper('concrete/dashboard')->getDashboardBackgroundImage();

$options = '{';
if ($backgroundImage->image) {
	if (!$_SESSION['dashboardHasSeenImage']) {
		$options .= 'imagetime: 750, ';
	} else {
		$options .= 'imagetime: false, ';
	}
	if ($backgroundImage->checkData) {
		$options .= 'filename: \'' . $backgroundImage->filename . '\', ';
		if ($backgroundImage->displayCaption) {
			$options .= 'displayCaption: true, ';
		} else {
			$options .= 'displayCaption: false, ';
		}
	}
	$options .= 'image: \'' . $backgroundImage->image . '\'';

} else {
	$options .= 'image: false';
}

$options .= '}';
$v->addFooterItem('<script type="text/javascript">$(function() { CCMDashboard.start(' . $options . '); });</script>');

?>

</head>
<body>

<div id="ccm-dashboard-page">


<? if (!$_SESSION['dashboardHasSeenImage']) { 
	$_SESSION['dashboardHasSeenImage'] = true;
} ?>

<? if (isset($backgroundImage->caption) && $backgroundImage->caption) { ?>
	<div id="ccm-dashboard-background-caption" class="ccm-ui"><div id="ccm-dashboard-background-caption-inner"><? if ($backgroundImage->url) { ?><a target="_blank" href="<?=$backgroundImage->url?>"><? } ?><?=$backgroundImage->caption?><? if ($backgroundImage->url) { ?></a><? } ?></div></div>
<? } 

$u = new User();
$frontendPageID = $u->getPreviousFrontendPageID();
if (!$frontendPageID) {
	$backLink = DIR_REL . '/';
} else {
	$backLink = DIR_REL . '/' . DISPATCHER_FILENAME . '?cID=' . $frontendPageID;
}
?>

<div class="ccm-ui">

<div id="ccm-toolbar">
	<ul>
		<li class="ccm-logo pull-left"><span><?=Loader::helper('concrete/interface')->getToolbarLogoSRC()?></span></li>

		<li class="ccm-toolbar-account pull-left"><a href="<?=$backLink?>"><i class="icon-arrow-left"></i></a>
		<li class="ccm-toolbar-account pull-right"><a href="#" data-toggle="ccm-toolbar-hover-menu" data-toggle-menu="#ccm-toolbar-menu-user"><i class="icon-user"></i></a>
		
		<ul id="ccm-toolbar-menu-user" class="ccm-toolbar-hover-menu dropdown-menu">
		  <li><a href="<?=$this->url('/account')?>"><?=t('Account')?></a></li>
		  <li><a href="<?=$this->url('/account/messages/inbox')?>"><?=t('Inbox')?></a></li>
		  <li><a href="<?=$this->url('/login', 'logout', $logouttoken)?>">Sign Out</a></li>
		</ul>

		</li>
		<li class="ccm-toolbar-dashboard pull-right"><a href="<?=$this->url('/dashboard')?>" data-toggle="ccm-toolbar-hover-menu" data-toggle-menu="#ccm-toolbar-menu-dashboard"><i class="icon-th-large"></i></a>

		<?
		$dh = Loader::helper('concrete/dashboard');
		print $dh->getIntelligentSearchMenu();
		?>

		</li>

		<li class="ccm-toolbar-search pull-right"><i class="icon-search"></i> <input type="search" id="ccm-nav-intelligent-search" tabindex="1" /></li>

	</ul>

</div>


<div id="ccm-dashboard-content">

	<div class="container">


	<? if (isset($error)) { ?>
		<? 
		if ($error instanceof Exception) {
			$_error[] = $error->getMessage();
		} else if ($error instanceof ValidationErrorHelper) {
			$_error = array();
			if ($error->has()) {
				$_error = $error->getList();
			}
		} else {
			$_error = $error;
		}
		
		if (count($_error) > 0) {
			?>
			<div class="ccm-ui"  id="ccm-dashboard-result-message">
				<?php Loader::element('system_errors', array('format' => 'block', 'error' => $_error)); ?>
			</div>
		<? 
		}
	}
	
	if (isset($message)) { ?>
		<div class="ccm-ui" id="ccm-dashboard-result-message">
			<div class="alert alert-info"><button type="button" class="close" data-dismiss="alert">×</button><?=nl2br(Loader::helper('text')->entities($message))?></div>
		</div>
	<? 
	} else if (isset($success)) { ?>
		<div class="ccm-ui" id="ccm-dashboard-result-message">
			<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button><?=nl2br(Loader::helper('text')->entities($success))?></div>
		</div>
	<? } ?>