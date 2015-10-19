<?php

# Define a class for creating an online Bulletin system
require_once ('frontControllerApplication.php');
class bulletin extends frontControllerApplication
{
	# Function to assign defaults additional to the general application defaults
	public function defaults ()
	{
		# Specify available arguments as defaults or as NULL (to represent a required argument)
		$defaults = array (
			
			# Database credentials
			'hostname' => 'localhost',
			'username' => NULL,
			'password' => NULL,
			'database' => 'bulletin',
			'table' => 'submissions',
			'administrators' => 'administrators',
			
			# API access
			'apiUsername' => false,
			
			# GUI
			'applicationName' => 'Bulletin',
			'organisationName' => NULL,	// e.g. 'Placeford SU'
			'div' => 'bulletin',
			'richtextEditorAreaCSS' => false,
			
			# E-mail addresses
			'administratorEmail' => $_SERVER['SERVER_ADMIN'],
			'notificationsRecipient' => NULL,	// e.g. 'president@placefordsu.example.com'
			'from' => NULL,		// e.g. 'president@placefordsu.example.com'
			'fromName' => NULL,	// e.g. 'Sam Smith - Placeford SU President'
			'replyTo' => NULL,	// e.g. 'bulletin@placefordsu.example.com'
			'replyToName' => 'Bulletin',
			'emailDomain' => NULL,	// e.g. 'example.com'
			
			# Years to go back
			'listPattern' => 'placefordsu-ugrads%s@lists.example.com',       // %s will get the year filled in
			'yearsBack' => 5,       // E.g. if the current academic year is 2014, then this will use lists: placefordsu-ugrads14, placefordsu-ugrads13, placefordsu-ugrads12, placefordsu-ugrads11, placefordsu-ugrads10 (plus the grad list)
			'otherLists' => 'placefordsu-pgrads@lists.example.com',  // String or an array
			
			# General
			'textMaxlength' => 1000,
			'textMaxlengthAdmin' => 2000,
			'academicYearThresholdMonth' => 9,	// i.e. New academic year starts in September
			'farTooManyItems' => 15,	// Point at which a warning about too many items should appear
			
			# Types editing
			'useEditing' => true,
			
			# Templating
			'useTemplating' => true,	// Whether to enable templating
			'templatesDirectory' => '%applicationRoot/template/',
		);
		
		# Return the defaults
		return $defaults;
	}
	
	
	# Function to assign supported actions
	public function actions ()
	{
		# Define available tasks
		$actions = array (
			'archive' => array (
				'description' => 'Bulletin archive',
				'url' => 'archive/',
				'tab' => 'Archive of past bulletins',
				'icon' => 'application_view_list',
			),
			'submit' => array (
				'description' => 'Submit an item',
				'url' => 'submit/',
				'tab' => 'Submit an item',
				'icon' => 'add',
				'authentication' => true,
			),
			'submissions' => array (
				'description' => 'View/edit submissions',
				'url' => 'submissions/',
				'tab' => 'Prepare Bulletin',
				'icon' => 'pencil',
				'administrator' => true,
			),
			'editsubmission' => array (
				'description' => 'Edit a submission',
				'url' => 'submissions/%s/',
				'usetab' => 'submissions',
				'administrator' => true,
			),
			'selection' => array (
				'description' => 'Choose items to include',
				'url' => 'submissions/selection.html',
				'usetab' => 'submissions',
				'administrator' => true,
			),
			'ordering' => array (
				'description' => 'Item ordering',
				'url' => 'submissions/ordering.html',
				'usetab' => 'submissions',
				'administrator' => true,
			),
			'setmessage' => array (
				'description' => 'Set message',
				'url' => 'submissions/setmessage.html',
				'usetab' => 'submissions',
				'administrator' => true,
			),
			'finalise' => array (
				'description' => 'Finalise Bulletin',
				'url' => 'submissions/finalise.html',
				'usetab' => 'submissions',
				'administrator' => true,
			),
			
			# Redefined tabs
			'home' => array (
				'description' => false,
				'url' => '',
				'tab' => 'Home',
				'icon' => 'house',
			),
			'feedback' => array (
				'description' => 'Feedback/contact form',
				'url' => 'feedback.html',
				'tab' => 'Feedback',
				'icon' => 'email',
			),
			'templates' => array (
				'description' => 'E-mail template',
				'url' => 'templates/',
				'parent' => 'admin',
				'subtab' => 'E-mail template',
				'icon' => 'tag',
				'administrator' => true,
			),
			'editing' => array (
				'description' => 'Edit types',
				'url' => 'types/',
				'parent' => 'admin',
				'subtab' => 'Edit types',
				'icon' => 'text_list_bullets',
				'administrator' => true,
			),
		);
		
		# Return the actions
		return $actions;
	}
	
	
	# Define the database structure
	private function databaseStructure ()
	{
		#!# Need to generalise some fields (username label, term and week labels)
		return $sql = "
		
		-- Administrators table
		CREATE TABLE `administrators` (
		  `crsid` varchar(10) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Username (CRSID)',
		  `active` enum('Y','N') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y' COMMENT 'Active?',
		  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name',
		  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'E-mail address',
		  PRIMARY KEY (`crsid`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Administrators';
		
		-- Archive of posted bulletins
		CREATE TABLE `archive` (
		  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Automatic key',
		  `academicYear` varchar(9) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Academic year',
		  `term` enum('Michaelmas term','Lent term','Easter term') COLLATE utf8_unicode_ci NOT NULL COMMENT 'Term',
		  `week` enum('week 0','week 1','week 2','week 3','week 4','week 5','week 6','week 7','week 8','week 9') COLLATE utf8_unicode_ci NOT NULL COMMENT 'Week of term',
		  `introductoryHtml` TEXT COLLATE utf8_unicode_ci NOT NULL COMMENT 'Introductory text (HTML)',
		  `introductoryText` TEXT COLLATE utf8_unicode_ci NOT NULL COMMENT 'Introductory text (plain text equivalent)',
		  `bulletinText` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Text of the Bulletin (now ignored)',
		  `dateIssued` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date issued',
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Bulletin archive';
		
		-- Cache of message
		CREATE TABLE `message` (
		  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique key',
		  `term` enum('','Michaelmas term','Lent term','Easter term') COLLATE utf8_unicode_ci NOT NULL COMMENT 'Term',
		  `week` enum('','week 0','week 1','week 2','week 3','week 4','week 5','week 6','week 7','week 8','week 9') COLLATE utf8_unicode_ci NOT NULL COMMENT 'Week of term',
		  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Bulletin' COMMENT 'Subject',
		  `messageHtml` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Introduction text',
		  `signature` text COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT 'Signature (if not already included in main block)',
		  `lastupdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last updated',
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Message text';
		
		-- Item submissions
		CREATE TABLE `submissions` (
		  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Submission ID',
		  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Type',
		  `title` varchar(70) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Title (max 70 chars)',
		  `date` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Date (where relevant)',
		  `time` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Time(s) (where relevant)',
		  `location` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Location (where relevant)',
		  `text` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Text of entry',
		  `webpage` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Webpage giving further details (optional)',
		  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'E-mail address that people can contact for more info',
		  `accessibility` enum('','Not applicable (not an event)','Venue is disabled accessible','Venue is not disabled accessible') COLLATE utf8_unicode_ci NOT NULL COMMENT 'Disabled accessible?',
		  `submitter` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Submitted by',
		  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Automatic timestamp',
		  `bulletinId` int(11) DEFAULT NULL COMMENT 'Include/refused for Bulletin ID',
		  `include` enum('','Yes','No') COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Include this item?',
		  `position` int(11) DEFAULT NULL COMMENT 'Position (1=first)',
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Submissions for consideration';
		
		CREATE TABLE `settings` (
		  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Automatic key',
		  `listAddress` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'List address (if not using auto per-year lists)',
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Settings' AUTO_INCREMENT=2 ;
		INSERT INTO `settings` (`id`, `listAddress`) VALUES (1, NULL);
		
		-- Submission types
		CREATE TABLE `types` (
		  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Internal name',
		  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Type',
		  `ordering` tinyint(2) DEFAULT '5' COMMENT 'Ordering (10 = nearest top of list)',
		  `available` int(1) NOT NULL DEFAULT '1' COMMENT 'Available for new submissions?',
		  `colour` varchar(7) COLLATE utf8_unicode_ci NOT NULL COMMENT 'HTML colour value',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='List of event types';
		";
	}
	
	
	# Additional initialisation, pre- actions phase
	protected function mainPreActions ()
	{
		# Construct the application name (e.g. 'Placeford SU Bulletin')
		$this->settings['applicationName'] = $this->settings['organisationName'] . ' ' . $this->settings['applicationName'];
	}
	
	
	
	# Home page
	public function home ()
	{
		# Define the HTML
		#!# Need to generalise text
		$html  = "\n" . '<p>The ' . $this->settings['applicationName'] . ' is issued to all undergraduates and post-graduates each week during Term.</p>';
		
		$html .= "\n<h2>Submit an item</h2>";
		$html .= "\n<ul class=\"main actions left\">";
		$html .= "\n\t<li><a href=\"{$this->baseUrl}/submit/\"><img src=\"/images/icons/add.png\" alt=\"\" class=\"icon\" /> Submit an item for the next Bulletin</a></li>";
		$html .= "\n</ul>";
		
		$html .= "\n<h2>Archive of past Bulletins</h2>";
		$html .= $this->showListing ();
		
		# Show the HTML
		echo $html;
	}
	
	
	# Home page
	public function archive ()
	{
		# Show the listing or bulletin
		$html  = $this->showListing ();
		
		# Show the HTML
		echo $html;
	}
	
	
	# Function to create an index of sent bulletins
	private function showListing ()
	{
		# Start the HTML
		$html  = '';
		
		# Define the term labels
		$terms = array (
			'Michaelmas term'	=> 'michaelmas',
			'Lent term'			=> 'lent',
			'Easter term'		=> 'easter'
		);
		
		# Determine the supplied parameters
		$year = (isSet ($_GET['year']) && preg_match ('/^20[0-9][0-9]-20[0-9][0-9]$/', $_GET['year']) ? $_GET['year'] : false);
		$term = (isSet ($_GET['term']) && in_array ($_GET['term'], $terms) ? $_GET['term'] : false);
		$week = (isSet ($_GET['week']) && preg_match ('/^[0-9]$/', $_GET['week']) ? $_GET['week'] : false);
		
		# If in listing mode, avoid fetching the large text fields to avoid maxing out memory as the archive grows
		$columns = array ();
		if (!strlen ($week)) {	// Strlen used as week can be '0', which evaluates to false
			$columns = array ('id', 'term', 'week', 'academicYear');
		}
		
		# Get the data
		$data = $this->databaseConnection->select ($this->settings['database'], 'archive', array (), $columns, true, $orderBy = 'academicYear DESC,term DESC,week DESC,id');
		
		# Organise the data by academicYear->term->week
		$bulletins = array ();
		foreach ($data as $index => $bulletin) {
			$termMoniker = str_replace (' term', '', strtolower ($bulletin['term']));
			$weekMoniker = str_replace ('week ', '', $bulletin['week']);	// NB The use of 'week 0' etc as an ENUM rather than '0' avoids the problem of ambiguous reference ('0' would be index 1; see: http://dev.mysql.com/doc/refman/5.0/en/enum.html
			$bulletins[$bulletin['academicYear']][$termMoniker][$weekMoniker] = $bulletin;
		}
		
		# If no year,term,week supplied, show the index
		if (!$year && !$term && !strlen ($week)) {	// Week can be '0' which equates to false, so strlen(week) is used
			
			# Introduction
			$html .= "\n" . '<p>Here you can view the archive of past Bulletins, most recent first.</p>';
			
			# End if none
			if (!$data) {
				$html .= "\n" . "\n<p><em>There are no Bulletins so far.</em></p>";
				return $html;
			}
			
			# Loop through each, showing most recent years first
			krsort ($bulletins);
			foreach ($bulletins as $year => $bulletinsByYear) {
				$html .= "\n<h3>Bulletins for {$year}</h3>";
				$html .= $this->showListingForYear ($bulletinsByYear, $year, 4);
			}
			
			# Return the HTML
			return $html;
		}
		
		# If only a year supplied, show the listing for that year
		if ($year && !$term && !strlen ($week)) {
			if (isSet ($bulletins[$year])) {
				$html .= $this->archiveBreadcrumbTrail ($year);
				$html .= $this->showListingForYear ($bulletins[$year], $year, 3);
				return $html;
			}
		}
		
		# If only a year and term supplied, show the listing for that term
		if ($year && $term && !strlen ($week)) {
			if (isSet ($bulletins[$year]) && isSet ($bulletins[$year][$term])) {
				$html .= $this->archiveBreadcrumbTrail ($year, $term);
				$html .= $this->showListingForTerm ($bulletins[$year][$term], $year, $term, 3);
				return $html;
			}
		}
		
		# If all supplied, show the selected bulletin
		if ($year && $term && strlen ($week)) {
			if (isSet ($bulletins[$year]) && isSet ($bulletins[$year][$term]) && isSet ($bulletins[$year][$term][$week])) {
				$html .= $this->archiveBreadcrumbTrail ($year, $term, $week);
				$html .= "\n<h3>{$this->settings['applicationName']}: Week {$week}, " . ucfirst ($termMoniker) . " {$year}</h3>";
				$html .= "\n<p><em>Sent at: " . date ('g:i a, jS F Y', strtotime ($bulletins[$year][$term][$week]['dateIssued'])) . '.</em></p>';
				$html .= "\n<div class=\"graybox\">";
				$html .= "\n" . $this->archivedBulletinText ($bulletins[$year][$term][$week]);
				$html .= "\n</div>";
				return $html;
			}
		}
		
		# Throw 404 if no match found
		application::sendHeader (404);
		include ('sitetech/404.html');
		echo $html;
		return false;
	}
	
	
	# Function to assemble the bulletin text from the archive
	private function archivedBulletinText ($bulletinData)
	{
		// # Return the text
		// # NB No longer used as it is better to create this dynamically so we can have different output types
		// # This might be useful if importing old bulletins that were manually assembled
		// return $bulletinData['bulletinText'];	#!# Code would need to be upgraded to HTML processing
		
		# Get the entries
		$articles = $this->getArticles ($bulletinData['id'], $errorHtml);
		
		# For legacy archive bulletins, emulate (upscale) the stored introductoryText as HTML
		if (!$bulletinData['introductoryHtml']) {
			$bulletinData['introductoryHtml'] = application::formatTextBlock ($this->formatText ($bulletinData['introductoryText'], $asHtml = true));
		}
		
		# Assemble the bulletin
		$text = $this->compileBulletin ($articles, $bulletinData['introductoryHtml'], $asHtml = true);
		
		# Return the text
		return $text;
	}
	
	
	# Function to create a breadcrumb trail for the bulletin archive
	private function archiveBreadcrumbTrail ($year = false, $term = false, $week = false)
	{
		# Create a list of entries
		$entries = array ();
		
		# Start with home
		$entries[] = "<a href=\"{$this->baseUrl}/archive/\">Bulletin archive</a>";
		
		# Add the year
		if ($year) {
			$entries[] = ($term ? "<a href=\"{$this->baseUrl}/{$year}/\">{$year}</a>" : $year);
		}
		
		# Add the term
		if ($term) {
			$termName = ucfirst ($term) . ' term';
			$entries[] = (strlen ($week) ? "<a href=\"{$this->baseUrl}/{$year}/{$term}/\">" . $termName . '</a>' : $termName);
		}
		
		# Add the week
		if (strlen ($week)) {
			$entries[] = "Week {$week}";
		}
		
		# Compile the HTML
		$html = "\n<p>" . implode (' &raquo; ', $entries) . '</p>';
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to show a bulletin listing for a year
	private function showListingForYear ($bulletinsByYear, $year, $headingLevel)
	{
		# Build the HTML
		$html  = '';
		foreach ($bulletinsByYear as $termMoniker => $bulletinsByTerm) {
			$html .= $this->showListingForTerm ($bulletinsByTerm, $year, $termMoniker, $headingLevel);
		}
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to show a bulletin listing for a year
	private function showListingForTerm ($bulletinsByTerm, $year, $termMoniker, $headingLevel)
	{
		# Build the HTML
		$html  = "\n<h{$headingLevel}>" . ucfirst ($termMoniker) . ", {$year}</h{$headingLevel}>";
		$list = array ();
		foreach ($bulletinsByTerm as $weekMoniker => $bulletin) {
			$list[] = "<a href=\"{$this->baseUrl}/{$year}/{$termMoniker}/week{$weekMoniker}/\">" . ucfirst ($bulletin['week']) . ", {$bulletin['term']} {$year}</a>";
		}
		$html .= "\n" . application::htmlUl ($list);
		
		# Return the HTML
		return $html;
	}
	
	
	# Home page
	public function submit ()
	{
		# Start the HTML
		$html  = "\n" . '<p>Here you can submit an item for the Bulletin.</p>';
		$html .= "\n" . '<p>Note that we receive more submissions than there is space, so submission here is <strong>not</strong> a guarantee that your item will be included.</p>';
		$html .= "\n" . '<p>Items are <strong>more likely to be included</strong> if they are of relatively wide interest, are succinct, and are genuinely student activities.</p>';
		
		# Show the form
		if (!$result = $this->submissionForm ($html)) {
			echo $html;
			return false;
		}
		
		# Add to the database
		$this->databaseConnection->insert ($this->settings['database'], $this->settings['table'], $result);
		
		# Confirm
		$html  = "\n<div class=\"graybox\"><p><img src=\"/images/icons/tick.png\" alt=\"Tick\" class=\"icon\" /> <strong>Thank you for your submission. It will be considered for the next {$this->settings['applicationName']}.</strong></p></div>";
		
		# Show the HTML
		echo $html;
	}
	
	
	# Submission form (submit/edit)
	private function submissionForm (&$html, $data = array ())
	{
		# Submission form, binded against the database structure
		$form = new form (array (
			'formCompleteText' => false,
			'databaseConnection' => $this->databaseConnection,
			'display' => 'paragraphs',
			'displayRestrictions' => false,
		));
		$form->dataBinding (array (
			'database' => $this->settings['database'],
			'table' => $this->settings['table'],
			'intelligence' => true,
			'exclude' => array ('submittedby', 'bulletinId', 'include', 'position', ),
			'size' => 55,
			'data' => $data,
			'attributes' => array (
				'type' => array ('type' => 'select', 'values' => $this->getTypes (), ),
				'title' => array ('size' => 70, ),
				'text' => array ('rows' => ($this->userIsAdministrator ? 14 : 9), 'cols' => 90, 'maxlength' => ($this->userIsAdministrator ? $this->settings['textMaxlengthAdmin'] : $this->settings['textMaxlength']), 'title' => 'Text of entry (max ' . number_format ($this->userIsAdministrator ? $this->settings['textMaxlengthAdmin'] : $this->settings['textMaxlength']) . ' characters' . ($this->userIsAdministrator ? " (NB: {$this->settings['textMaxlength']} for ordinary non-admin users)" : '') . '; shorter entries are more likely to be read)', 'description' => 'Make sure you include mention of what organisation this relates to.<br />Plain text only - HTML will not be shown. Use an extra line-break between paragraphs.'),
				'webpage' => array ('regexp' => '(http|https)://', 'description' => 'Starts with http://', ),
				'submitter' => array ('default' => $this->user . '@' . $this->settings['emailDomain'], 'editable' => false, ),
				'date' => array ('description' => 'Please enter like:&nbsp; Monday 1 January'),
				'time' => array ('description' => 'Please enter like:&nbsp; 6.30pm'),
			),
		));
		
		# Stop titles being in ALL CAPS
		if ($unfinalisedData = $form->getUnfinalisedData ()) {
			if ($unfinalisedData['title']) {
				if ($unfinalisedData['title'] == strtoupper ($unfinalisedData['title'])) {
					$form->registerProblem ('allcaps', 'The title must not be in ALL CAPS. Normal sentence case should be used instead.');
				}
			}
		}
		
		# Set to e-mail if not editing
		if (!$data) {
			$form->setOutputEmail ($this->settings['notificationsRecipient'], $this->settings['administratorEmail'], $subjectTitle = $this->settings['applicationName'] . ' submission', $chosenElementSuffix = NULL, $replyToField = 'submitter', $displayUnsubmitted = true);
		}
		
		# Process the form
		$result = $form->process ($html);
		
		# Return the result
		return $result;
	}
	
	
	# Types
	private function getTypes ()
	{
		# Get the types
		$query = "SELECT id,type FROM {$this->settings['database']}.types WHERE available = 1 ORDER BY ordering,type;";
		$data = $this->databaseConnection->getPairs ($query);
		
		# Customise the organisation field
		$data['organisation'] = $this->settings['organisationName'];
		
		# Return the data
		return $data;
	}
	
	
	# Function to get an individual submission
	private function getSubmission ($id)
	{
		# Get the data
		return $data = $this->databaseConnection->selectOne ($this->settings['database'], 'submissions', array ('id' => $id));
	}
	
	
	# Function to get the submissions
	private function getSubmissions ($fields = '*', $where = false, $orderby = 'timestamp')
	{
		# Get the submissions
		$query = "SELECT {$fields} FROM {$this->settings['database']}.{$this->settings['table']}" . ($where ? " WHERE {$where}" : '') . " ORDER BY {$orderby};";
		$data = $this->databaseConnection->getData ($query, "{$this->settings['database']}.{$this->settings['table']}");
		
		# Return the data
		return $data;
	}
	
	
	# Function to show submissions
	public function submissions ()
	{
		# Start the HTML
		$html  = '';
		
		# Show the progress bar
		$html .= $this->showProgressBar ();
		
		# Get the submissions
		if (!$data = $this->getSubmissions ("*, DATE_FORMAT(timestamp,'%W, %D %M %Y') AS timestampFormatted", 'bulletinId IS NULL')) {
			$html .= "\n<p>There are no submissions.</p>";
			echo $html;
			return false;
		}
		
		# Instructions
		$html .= "\n<p>1: Review each of the submissions (" . count ($data) . '), e.g. to clean up any wording:</p>';
		
		# Get the headings
		$keySubstitutions = $this->databaseConnection->getHeadings ($this->settings['database'], $this->settings['table']);
		$keySubstitutions['webpage']			= 'Webpage giving more detail';
		$keySubstitutions['email']				= 'E-mail that people can contact';
		$keySubstitutions['timestampFormatted']	= 'Submitted at';
		
		# Assemble as a table
		foreach ($data as $submission) {
			unset ($submission['bulletinId'], $submission['include'], $submission['position'], $submission['timestamp']);
			$html .= "\n\n\n<div class=\"graybox\" id=\"id{$submission['id']}\">";
			$html .= "\n<ul class=\"actions\">\n\t<li><a href=\"{$this->baseUrl}/submissions/{$submission['id']}/\"><img src=\"/images/icons/pencil.png\" alt=\"Edit\" class=\"icon\" /> Edit #{$submission['id']}</a></li>\n</ul>";
			$html .= "\n<h2>" . htmlspecialchars ($submission['title']) . '</h2>';
			$html .= application::htmlTableKeyed ($submission, $keySubstitutions, $omitEmpty = false, $class = 'reviewbox lines compressed regulated', false, true, $addRowKeyClasses = true);
			$html .= "\n</div>";
			$html .= "<p class=\"small comment signature\"><a href=\"#\">^ Top</a></p>";
			if ($submission['webpage']) {$html = str_replace ("<td class=\"value\">{$submission['webpage']}</td>", "<td class=\"value\"><a href=\"{$submission['webpage']}\" target=\"_blank\">{$submission['webpage']}</a></td>", $html);}
		}
		
		# Surround the whole thing in a div for styling purposes
		$html  = "\n<div id=\"submissions\">" . $html . "</div>";
		
		# Show the HTML
		echo $html;
	}
	
	
	# Function to edit an individual submission
	public function editsubmission ($id = false)
	{
		# Start the HTML
		$html  = '';
		
		# Show the progress bar
		$html .= $this->showProgressBar ();
		
		# Ensure the ID is supplied and numeric (this should always be the case unless mod_rewrite has been bypassed) and retrieve it from the database
		if (!$id || (!ctype_digit ($id)) || (!$data = $this->getSubmission ($id))) {
			application::sendHeader (404);
			$html .= "\n<p>There is no such submission. Please check the URL and try again.</p>";
			echo $html;
			return false;
		}
		
		# Show the form, prefilled with the data
		if (!$result = $this->submissionForm ($html, $data)) {
			echo $html;
			return false;
		}
		
		# Update the database
		$this->databaseConnection->update ($this->settings['database'], $this->settings['table'], $result, array ('id' => $id));
		
		# Confirm
		$html  = "\n<div class=\"graybox\"><p><img src=\"/images/icons/tick.png\" alt=\"Tick\" class=\"icon\" /> <strong>The submission has been updated. ";
		if (isSet ($_GET['returnto']) && ($_GET['returnto'] == 'ordering')) {
			$html .= "<a href=\"{$this->baseUrl}/submissions/ordering.html\">Return to the item ordering page.</a>";
		} else {
			$html .= "<a href=\"{$this->baseUrl}/submissions/#id{$id}\">Return to the list of submissions.</a>";
		}
		$html .= "</strong></p></div>";
		
		# Show the HTML
		echo $html;
	}
	
	
	# Function to select the entries
	public function selection ()
	{
		# Start the HTML
		$html  = '';
		
		# Show the progress bar
		$html .= $this->showProgressBar ();
		
		# Get the submissions
		if (!$data = $this->getSubmissions ('*', 'bulletinId IS NULL')) {
			$html .= "\n<p>There are no submissions.</p>";
			echo $html;
			return false;
		}
		
		# Instructions
		$html .= "\n<p>2: Below are all the submitted items (" . count ($data) . ') that have not previously been included/rejected. Decide which to use for this new Bulletin:</p>';
		
		# Create the template
		$table = array ();
		$table['<strong>Include in this bulletin?</strong>'] = '<strong>Main text of entry</strong>';	// Header
		$widgetNames = array ();
		foreach ($data as $id => $entry) {
			$widgetNames[$id] = 'id' . $id;
			$key = '{' . $widgetNames[$id] . '}';
			$table[$key]  = "\n<p class=\"small right\"><a href=\"{$this->baseUrl}/submissions/{$id}/\" title=\"Link opens in a new window\" target=\"_blank\">Full info &hellip;</a></p>";
			$table[$key] .= "\n<h3>" . htmlspecialchars ($entry['title']) . '</h3>';
			$table[$key] .= "\n" . substr ($entry['text'], 0, 220) . '&hellip;';
		}
		$table = application::htmlTableKeyed ($table, array (), false, 'lines entries', $allowHtml = true, $showColons = false);
		
		# Create the template
		$template  = "\n\n" . '{[[PROBLEMS]]}';
		$template .= $table;
		$template .= "\n\n" . '{[[SUBMIT]]}';
		
		# Create the form
		$form = new form (array (
			'name' => false,	// Essential, to avoid ID prefixing
			'formCompleteText' => false,
			'display' => 'template',
			'displayTemplate' => $template,
			'nullText' => 'Leave for later',
			'unsavedDataProtection' => true,
		));
		$i = 0;
		foreach ($data as $id => $entry) {
			$form->select (array (
				'name'			=> $widgetNames[$id],
				'title'			=> "Entry #{$id}",	// Only would ever be seen by the user in the {PROBLEMS} box
				'required'		=> false,
				'values'		=> array ('', 'Yes', 'No'),
				'default'		=> $entry['include'],
				'tabindex'		=> ++$i,	// Add this so the user can tab between entries without the 'Full info' links being in the way each time
			));
			/* Radiobuttons equivalent - not such a nice interface
			$form->radiobuttons (array (
				'name'			=> $widgetNames[$id],
				'title'			=> "Entry #{$id}",	// Only would ever be seen by the user in the {PROBLEMS} box
				'required'		=> true,
				'values'		=> array ('' => 'Leave for later', 'Yes', 'No'),
				'default'		=> $entry['include'],
				'nullText'		=> 'Leave for later',
			));
			*/
		}
		
		# Process the form
		if (!$result = $form->process ($html)) {
			echo $html;
			return false;
		}
		
		# Arrange as id=>result
		$updates = array ();
		foreach ($result as $fieldname => $value) {
			$id = str_replace ('id', '', $fieldname);
			$updates[$id] = $value;
		}
		
		# Count the number off 'Yes' entries
		$yesEntries = 0;
		foreach ($updates as $id => $value) {
			if ($value == 'Yes') {
				$yesEntries++;
			}
		}
		
		# Update the data
		foreach ($updates as $id => $include) {
			$update = array ('include' => $include);
			if ($include == 'No') {$update['position'] = NULL;}
			$this->databaseConnection->update ($this->settings['database'], $this->settings['table'], $update, array ('id' => $id), $emptyToNull = false);
		}
		
		# Confirm, showing the total number of entries
		$html .= "\n<div class=\"graybox\">";
		$html .= "\n<p><img src=\"/images/icons/tick.png\" alt=\"Tick\" class=\"icon\" /> <strong>The choices have been saved. <a href=\"{$this->baseUrl}/submissions/ordering.html\">Now determine the ordering &raquo;</a></strong></p>";
		$html .= "\n<p>It has " . $this->reportTotalEntries ($yesEntries) . ".</p>";
		$html .= '</div>';
		
		# Show the HTML
		echo $html;
	}
	
	
	# Function to report the number of chosen entries
	private function reportTotalEntries ($total)
	{
		# Return the text
		return "<strong>" . ($total == 1 ? 'one item' : "{$total} items" . (($total >= $this->settings['farTooManyItems'] ? " (which is really <span class=\"warning\">too many for an e-mail</span> - ideally please <a href=\"{$this->baseUrl}/submissions/selection.html\">go back</a> and be more more selective)" : ''))) . '</strong>';
	}
	
	
	# Function to order the entries
	public function ordering ()
	{
		# Start the HTML
		$html  = '';
		
		# Show the progress bar
		$html .= $this->showProgressBar ();
		
		# Get the submissions
		$query = "SELECT
			submissions.id,submissions.title,submissions.position,submissions.type,types.ordering AS typeOrdered
			FROM {$this->settings['database']}.{$this->settings['table']}
			LEFT JOIN types ON submissions.type = types.id
			WHERE bulletinId IS NULL AND include = 'Yes'
			ORDER BY typeOrdered,position,id	/* NB Position may not exist */
		;";
		if (!$data = $this->databaseConnection->getData ($query, "{$this->settings['database']}.{$this->settings['table']}")) {
			$html .= "\n<p>There are no chosen items.</p>";
			echo $html;
			return false;
		}
		
		# Ensure there are no titles in ALL CAPS
		$allCaps = array ();
		foreach ($data as $id => $entry) {
			if ($entry['title'] == strtoupper ($entry['title'])) {
				$allCaps[$id] = "<a href=\"{$this->baseUrl}/submissions/{$id}/?returnto=ordering\">#{$id}</a>";
			}
		}
		if ($allCaps) {
			$total = count ($allCaps);
			$linkList =  '(' . implode (', ', $allCaps) . ')';
			$html .= "\n<p>" . ($total > 1 ? "Some of the chosen items {$linkList} have titles" : "One of the chosen items {$linkList} has a title") . ' all in UPPER CASE, which is bad for readability. Please fix.</p>';
			echo $html;
			return false;
		}
		
		# Instructions
		$html .= "\n<p>3: Select the order you want the items (" . count ($data) . ") to appear. Titles should be self-explanatory; if not, return to stage 1 and edit them.</p>";
		
		# Show confirmation if required
		$cookieName = 'confirmation';
		if (isSet ($_COOKIE[$cookieName])) {
			$html .= "\n<div class=\"graybox\"><p><img src=\"/images/icons/tick.png\" alt=\"Tick\" class=\"icon\" /> <strong>The ordering has been saved. <a href=\"{$this->baseUrl}/submissions/setmessage.html\">Now set the introductory message &raquo;</a></strong></p></div>";
			setcookie ($cookieName, '', time () - 3600);	// Remove (expire) the cookie
		}
		
		# Get the types
		#!# Refactor to use main getTypes() function
		$query = "SELECT * FROM {$this->settings['database']}.types WHERE available = 1 ORDER BY ordering,type;";
		$types = $this->databaseConnection->getData ($query, "{$this->settings['database']}.types");
		$types['organisation']['type'] = $this->settings['organisationName'];
		
		# Show the required ordering, and construct an associative array of the colours
		$ordering = array ();
		$colours = array ();
		foreach ($types as $id => $type) {
			$colours[$id] = $type['colour'];
			$ordering[] = "<span style=\"background-color: {$type['colour']};\">{$type['type']}</span>";
		}
		$html .= "\n<p id=\"orderingkey\">Ordering required: " . implode (', ', $ordering) . '</p>';
		
		# Create the sortable list
		if (!$orderings = $this->sortableList ($html, $data, $colours)) {
			echo $html;
			return false;
		}
		
		# Insert the new positions
		foreach ($orderings as $id => $position) {
			$this->databaseConnection->update ($this->settings['database'], $this->settings['table'], array ('position' => $position), array ('id' => $id));
		}
		
		# Set a cookie confirming success
		setcookie ($cookieName, '1');
		
		# Redirect to the current page so that the new positions are shown
		application::sendHeader ('refresh');
		
		# Show the HTML
		echo $html;
	}
	
	
	# Function to create a DHTML sortable list
	private function sortableList (&$html, $data, $colours)
	{
		/*
		# $data must look like the following, ordered by 'position'; in this implementation, type is also supplied to obtain the colour
		Array (
		    [2] => Array (
		            [id] => 2				// Actually the 'id' within each entry is optional
		            [title] => Foobar 2
		            [position] => 1
		        )
		    [1] => Array (
		            [id] => 1
		            [title] => Foobar 1
		            [position] => 2
		        )
		    [3] => Array (
		            [id] => 3
		            [title] => Foobar 3
		            [position] => 3
		        )
		)
		*/
		
		# Define sortability DHTML; based on http://media.smashingmagazine.com/cdn_smash/images/progressive-enhancement/navigation-3.html
		#!# Refactor to use $this->settings['jQuery']
		$jQueryLibrary = '<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js"></script>';
		$jQueryCode = "
			$(document).ready(function() {
				$('li input').hide();
				$('<img src=\"/images/icons/text_align_justify.png\" alt=\"Drag\" class=\"icon handle\" />').prependTo('ol.sortable li');
				// $('input[type=submit]').hide();
				var result = $('form').serialize();
				$('ol.sortable').sortable({items: 'li',
					update: function(event, ui) {
						// Convert the ordered list into an array
						var new_order = $('ol.sortable').sortable('toArray');
						// Loop through the array and assign the input that matches the li id the new position value
						$.each(new_order, function(i, element) {
		                    $('input[name='+element+']').attr('value', i+1);
						});
						
						result = $('form').serialize();
					}
				});
			});
		";
		
		/*
		# Static example:
		$html .= '
			<form action="' . $this->baseUrl . '/submissions/ordering.html" method="post">
				<ol class="sortable">
					<li id="position1"><label for="id1">Order: </label><input type="text" name="position1" id="id1" value="1" size="3" /> Homepage</li>
					<li id="position2"><label for="id2">Order: </label><input type="text" name="position2" id="id2" value="2" size="3" /> Contact us</li>
					<li id="position3"><label for="id3">Order: </label><input type="text" name="position3" id="id3" value="3" size="3" /> About us</li>
					<li id="position4"><label for="id4">Order: </label><input type="text" name="position4" id="id4" value="4" size="3" /> Latest News</li>
				</ol>
				<p><input type="submit" value="Save this ordering" /></p>
			</form>
		';
		*/
		
		# Create the template
		$prefix = 'position';
		$template  = "\n\n" . '{[[PROBLEMS]]}';
		$template .= "\n<ol class=\"sortable\">";
		$maxlength = strlen (count ($data));
		$position = 0;
		$widgetNames = array ();
		foreach ($data as $id => $entry) {
			$position++;
			$data[$id]['position'] = $position;	// i.e. just ignore any existing position, and reindex from 1 to end
			$widgetNames[$id] = $prefix . str_pad ($id, $maxlength, '0', STR_PAD_LEFT);	// e.g. position01, position02, ..
			$template .= "\n\t<li id=\"{$widgetNames[$id]}\" style=\"background-color: #{$colours[$entry['type']]};\"><label for=\"{$widgetNames[$id]}\">Order: </label>{" . $widgetNames[$id] . "} " . htmlspecialchars ($entry['title']) . '</li>';
		}
		$template .= "\n</ol>";
		$template .= "\n\n" . '{[[SUBMIT]]}';
		
		# Determine possible ordering values
		$possibleOrderingValuesRegex = '^(' . implode ('|', range (1, count ($data))) . ')$';	// e.g. ^(1|2|3)$
		
		# Create the form
		$form = new form (array (
			'name' => false,	// Essential, to avoid ID prefixing
			'formCompleteText' => false,
			'display' => 'template',
			'displayTemplate' => $template,
			'div' => false,		// Only to avoid slightly odd label alignment when Javascript turned off
			#!# unsavedDataProtection doesn't seem to work when changed dynamically via sortability
			'unsavedDataProtection' => true,
			'submitButtonText' => 'Save this ordering!',
			'submitButtonAccesskey' => false,
		));
		foreach ($data as $id => $entry) {
			$form->input (array (
				'name'			=> $widgetNames[$id],
				'title'			=> "Order ({$id})",	// Only would ever be seen by the user in the {PROBLEMS} box
				'required'		=> true,
				'regexp'		=> $possibleOrderingValuesRegex,
				'size'			=> $maxlength,
				'maxlength'		=> $maxlength,
				'default'		=> $entry['position'],
			));
		}
		
		# Add in the jQuery library and code
		$form->addJQueryLibrary ('sortable', $jQueryLibrary);
		$form->addJQueryCode    ('sortable', $jQueryCode);
		
		# Require uniqueness
		if (count ($data) > 1) {
			$form->validation ('different', $widgetNames);
		}
		
		# Process the form
		if (!$result = $form->process ($html)) {return false;}
		
		# Remove the prefix, so that the result is organised as the position for each supplied ID value, i.e. array(id=>position,id=>position,id=>position)
		$orderings = array ();
		foreach ($result as $id => $position) {
			$id = str_replace ($prefix, '', $id);
			$orderings[$id] = $position;
		}
		ksort ($orderings);
		
		# Return the result
		return $orderings;
	}
	
	
	# Function to finalise the Bulletin
	public function setmessage ()
	{
		# Start the HTML
		$html  = '';
		
		# Show the progress bar
		$html .= $this->showProgressBar ();
		
		# Instructions
		$html .= "\n<p>4: Now set the details and introductory message:</p>";
		
		# Get the current message
		$message = $this->databaseConnection->selectOne ($this->settings['database'], 'message', array ('id' => 1));
		
		# Put the message in a form
		$form = new form (array (
			#!# Reappear with a formCompleteText seems to put the confirmation at the end, not above the form
			// 'reappear' => true,
			'unsavedDataProtection' => true,
			'formCompleteText' => "\n<div class=\"graybox\"><p><img src=\"/images/icons/tick.png\" alt=\"Tick\" class=\"icon\" /> <strong>The message has been saved. <a href=\"{$this->baseUrl}/submissions/finalise.html\">Now finalise the Bulletin &raquo;</a></strong></p></div>",
			'databaseConnection' => $this->databaseConnection,
			'nullText' => '',
			'richtextEditorAreaCSS' => $this->settings['richtextEditorAreaCSS'],
		));
		$form->dataBinding (array (
			'database' => $this->settings['database'],
			'table' => 'message',
			'intelligence' => true,
			'exclude' => array ('id', ),
			'data' => $message,
			'attributes' => array (
				'messageHtml' => array ('editorToolbarSet' => 'BasicImage', 'editorFileBrowserStartupPath' => $this->baseUrl . '/images/', 'imageAlignmentByClass' => false, 'width' => 650, ),
				'subject' => array ('size' => 60, 'maxlength' => 76, ),
				'signature' => array ('cols' => 40, 'rows' => 3, ),
			),
		));
		
		# Process the form
		if ($result = $form->process ($html)) {
			
			# Update the message (always entry 1, the only one in the table)
			$this->databaseConnection->update ($this->settings['database'], 'message', $result, array ('id' => 1));
		}
		
		# Show the HTML
		echo $html;
	}
	
	
	# Function to finalise the Bulletin
	public function finalise ()
	{
		# Start the HTML
		$html  = '';
		
		# Show the progress bar
		$html .= $this->showProgressBar ();
		
		# Assemble the introductory message
		$messageEntry = $this->databaseConnection->selectOne ($this->settings['database'], 'message', array ('id' => 1));
		$introductoryHtml = $messageEntry['messageHtml'];
		if ($messageEntry['signature']) {
			$introductoryHtml .= "\n<br />\n<p>" . nl2br ($messageEntry['signature']) . '</p>';
		}
		
		# Get the articles, if any
		$articles = $this->getArticles (false, $errorHtml);
		
		# Assemble the text of the bulletin
		$textVersion = $this->compileBulletin ($articles, $introductoryHtml);
		
		# Determine the sender and the Reply-To
		$from    = 'From: '     . (strstr (PHP_OS, 'WIN') ? $this->settings['from']    : '"' . $this->settings['fromName']    . '" <' . $this->settings['from']    . '>');
		$replyTo = 'Reply-To: ' . (strstr (PHP_OS, 'WIN') ? $this->settings['replyTo'] : '"' . $this->settings['replyToName'] . '" <' . $this->settings['replyTo'] . '>');
		
		# Determine the lists to send to
		$to = $this->recipientAddresses ($this->settings['listPattern'], $this->settings['yearsBack'], $this->settings['otherLists']);
		
		# Construct a block showing the headers for confirmation display purposes
		$headersPreview  = $from . "\n";
		$headersPreview .= $replyTo . "\n";
		$headersPreview .= 'To: ' . $to . "\n";
		$headersPreview .= 'Subject: ' . $messageEntry['subject'] . "\n";
		$headersPreview = wordwrap ($headersPreview);
		
		# Count length
		$lines = count (explode ("\n", $textVersion));
		
		# Create the HTML version
		$htmlVersion = $this->compileBulletin ($articles, $introductoryHtml, $asHtml = true, $htmlVersionEmailOptimised = true);
		
		# Show the text
		$html .= "\n<p>Here is the proposed Bulletin. It is <strong>{$lines} lines</strong> long</strong>.</p>\n<p>Please check it over carefully, and amend it via the previous steps if necessary. Submit the button at the end to send it.</p>";
		$html .= "\n<p><img src=\"/images/icons/exclamation.png\" class=\"icon\" alt=\"!\" /> Do <strong>not </strong>send the mailshots during the working day while Hermes is busy: before 8am or after 6pm is best.</a></p>";
		
		# Give a preview of the HTML version
		$html .= "\n<h3>HTML version:</h3>";
		$html .= "\n<div class=\"graybox\">";
		$html .= '<pre>' . htmlspecialchars ($headersPreview) . '</pre>' . '<br />';
		$html .= $htmlVersion;
		$html .= "\n</div>";
		
		# Give a preview of the text version
		$html .= "\n<h3>Text version:</h3>";
		$html .= "\n<div class=\"graybox\">";
		$html .= '<pre>' . htmlspecialchars ($headersPreview) . '</pre>' . '<br />';
		$html .= '<pre>' . htmlspecialchars (wordwrap ($textVersion)) . '</pre>';
		$html .= "\n</div>";
		
		# Sending form
		$form = new form (array (
			'databaseConnection' => $this->databaseConnection,
			'displayRestrictions' => false,
			'formCompleteText' => false,
			'requiredFieldIndicator' => false,
			'display' => 'paragraphs',
			'div' => 'graybox sendnow',
			'submitButtonText' => 'Send the Bulletin now! &raquo;',
			'submitButtonAccesskey' => false,
		));
		$form->checkboxes (array (
			'name' => 'send',
			'title' => 'Send now?',
			'required' => false,
			'values' => array ('Yes, the Bulletin is now complete!'),
		));
		if ($result = $form->process ($html)) {
			
			# Compile the message parts
			$message = array (
				'text' => $textVersion,
				'html' => $htmlVersion,
			);
			
			# Mail the Bulletin
			application::utf8mail ($to, $messageEntry['subject'], $message, $from . "\n" . $replyTo);
			
			# Confirm sending, resetting all HTML
			$html  = "\n<div class=\"graybox\"><p><img src=\"/images/icons/tick.png\" alt=\"Tick\" class=\"icon\" /> <strong>The Bulletin has now been sent! <a href=\"{$this->baseUrl}/archive/\">It will now be in the archive.</a></strong></p></div>";
			
			# Determine the current academic year, e.g. 2013-2014
			$year = date ('Y');
			$month = date ('n');
			$currentAcademicYear = ($month < $this->settings['academicYearThresholdMonth'] ? $year - 1 : $year);
			$academicYearString = $currentAcademicYear . '-' . ($currentAcademicYear + 1);
			
			# Add the Bulletin to the archive
			$insert = array (
				'academicYear' => $academicYearString,
				'term' => $messageEntry['term'],
				'week' => $messageEntry['week'],
				'introductoryHtml' => $introductoryHtml,
				'introductoryText' => $this->htmlToText ($introductoryHtml),
				'bulletinText' => $textVersion,		// Not actually used any more
			);
			$this->databaseConnection->insert ($this->settings['database'], 'archive', $insert, false, $emptyToNull = false);
			
			# Set the IDs considered (i.e. included OR refused) for this bulletin, so that they are removed from the consideration list
			$archiveId = $this->databaseConnection->getLatestId ();
			$query = "UPDATE {$this->settings['database']}.{$this->settings['table']} SET bulletinId = '{$archiveId}' WHERE bulletinId IS NULL AND include IN('Yes','No');";
			$this->databaseConnection->execute ($query);
			
			# Reset the template details
			$nextTerm = array ('Michaelmas term' => 'Lent term', 'Lent term' => 'Easter term', 'Easter term' => 'Michaelmas term');	#!# This would ideally be self-referential from the database but it's not worth the bother
			$nextWeek = 'week ' . ($messageEntry['week'] == 'week 9' ? '0' : str_replace ('week ', '', $messageEntry['week']) + 1);
			$update = array (
				'term'			=> ($messageEntry['week'] == 'week 9' ? $nextTerm[$messageEntry['term']] : $messageEntry['term']),
				'week'			=> $nextWeek,
				'messageHtml'	=> "<p>Check out our website at {$_SERVER['SERVER_NAME']}</p>\n<p>Dear all,</p>\n<p>Text of message goes here</p>",
				'subject'		=> $this->settings['applicationName'] . ': ' . ucfirst ($nextWeek),
				// Signature is maintained, as it is unlikely to change much from week to week
			);
			$this->databaseConnection->update ($this->settings['database'], 'message', $update, array ('id' => 1));
		}
		
		# Show the HTML
		echo $html;
	}
	
	
	# Function to get the data for a bulletin
	private function getArticles ($bulletinId = false, &$errorHtml = false)
	{
		# Determine the retrieval conditions
		$conditions = 'bulletinId ' . ($bulletinId ? "= {$bulletinId}" : 'IS NULL') . " AND include = 'Yes'";
		
		# Get the submissions, if any
		$data = $this->getSubmissions ('*', $conditions, 'type DESC,position');
		
		// # Cache the total number of items
		// $total = count ($data);
		
		# Ensure all have a position (items that have been subsequently added after reordering can cause this to happen)
		foreach ($data as $entry) {
			if (!$entry['position']) {
				$exclamationMark = '<img src="/images/icons/exclamation.png" alt="!" class="icon" />';
				$errorHtml = "\n<div class=\"graybox\"><p>{$exclamationMark} <strong>Not all items have had an ordering position set. Please <a href=\"{$this->baseUrl}/submissions/ordering.html\">review and save the ordering</a>.</strong></p></div>";
				return false;
			}
		}
		
		# Regroup by type
		$data = application::regroup ($data, 'type');
		
		# Ensure any entries for the organisation itself are at the start
		if (isSet ($data['organisation'])) {
			$organisationSection = $data['organisation'];
			unset ($data['organisation']);
			$data = array_merge (array ('organisation' => $organisationSection), $data);
		}
		
		# Return the data
		return $data;
	}
	
	
	# Function to assemble the text
	private function compileBulletin ($articlesByGroup, $introductoryHtml, $asHtml = false, $htmlVersionEmailOptimised = false)
	{
		# Get the types, which are used for the labels
		$types = $this->getTypes ();
		
		# Format the introductory text if required
		if ($asHtml) {
			$introduction = $introductoryHtml;
		} else {
			$introduction = $this->htmlToText ($introductoryHtml);
		}
		
		# Assemble the jumplist
		$jumplist = $this->assembleJumplist ($articlesByGroup, $types, $asHtml, $htmlVersionEmailOptimised);
		
		# Assemble the listing
		$listing = $this->assembleListing ($articlesByGroup, $types, $asHtml, $htmlVersionEmailOptimised);
		
		# If HTML, combine the values into the template
		if ($asHtml) {
			
			# Populate the template
			$this->template['content'] = $introductoryHtml;
			$this->template['listing'] = /* $jumplist . */ $listing;
			
			# Process the template
			$html = $this->templatise ('email.tpl');
			
			# Convert CSS to inline styles; see: https://github.com/jjriv/emogrifier and https://www.campaignmonitor.com/css/
			require_once ('emogrifier/Classes/Emogrifier.php');
			$emogrifier = new \Pelago\Emogrifier ($html, $css = '');
			$html = $emogrifier->emogrify ();
			
			# Convert images to absolute URL
			#!# This needs to be handled better in ultimateForm
			#!# No support yet for protocol-less (i.e. //...) URLs
			$html = str_replace (' src="/', " src=\"{$_SERVER['_SITE_URL']}/", $html);
			
			# Return the HTML
			return $html;
			
		# Or as text, compile manually
		} else {
			
			# Assemble the text
			$text  = $introduction;
			
			# Add listing if any items
			if ($listing) {
				$text .= "\n__\n";	// Separator
				$text .= $jumplist;
				$text .= $listing;
			}
			
			# Wordwrap
			$text = wordwrap ($text);
			
			# Return the text
			return $text;
		}
	}
	
	
	# Function to assemble the jumplist
	private function assembleJumplist ($articlesByGroup, $types, $asHtml, $htmlVersionEmailOptimised = false)
	{
		# Loop through each article group to create the jump list
		$jumplist = '';
		$groups = array ();
		foreach ($articlesByGroup as $group => $articles) {
			
			# Add the group title
			$groupTitle = $this->articleGroupTitle ($types[$group]);
			if ($asHtml) {
				$groups[$group] = '<strong>' . htmlspecialchars ($groupTitle) . '</strong>:';
			} else {
				$jumplist .= "\n\n" . strtoupper ($groupTitle) . "\n";
			}
			
			# Loop through each article to make a list
			$i = 0;
			$maxlength = strlen (count ($articles));
			$titles = array ();
			foreach ($articles as $article) {
				$i++;
				if ($asHtml) {
					$titles[] = ($htmlVersionEmailOptimised ? "<a name=\"{$group}{$i}link\"></a><a" : "<a id=\"{$group}{$i}link\"") . " href=\"#{$group}{$i}\">" . htmlspecialchars (trim ($article['title'])) . '</a>';
				} else {
					$titles[] = str_pad ($i, $maxlength, ' ', STR_PAD_LEFT) . '. ' . $article['title'];
				}
			}
			
			# Compile the list into HTML
			if ($asHtml) {
				$groups[$group] .= application::htmlOl ($titles, 1, 'normal small');
			} else {
				$jumplist .= "\n" . implode ("\n", $titles);
			}
		}
		
		# Compile the groups list in the HTML version
		if ($asHtml) {
			$jumplist .= application::htmlUl ($groups, 0, 'spaced');
		}
		
		# Return the HTML
		return $jumplist;
	}
	
	
	# Function to assemble the main text
	private function assembleListing ($articlesByGroup, $types, $asHtml, $htmlVersionEmailOptimised = false)
	{
		# Loop through each article group to create the listing
		$listing = '';
		foreach ($articlesByGroup as $group => $articles) {
			
			# Add the group title
			$groupTitle = $this->articleGroupTitle ($types[$group]);
			if ($asHtml) {
				$listing .= "\n\n\n" . ($htmlVersionEmailOptimised ? "<h2><a name=\"{$group}\"></a>" : "<h2 id=\"{$group}\">" . "<a href=\"#{$group}\">#</a> ") . htmlspecialchars ($groupTitle) . "</h2>\n";
			} else {
				$listing .= "\n\n\n" . strtoupper ($groupTitle) . "\n" . str_repeat ('-', strlen ($groupTitle));
			}
			
			# Loop through each article
			$i = 0;
			$maxlength = strlen (count ($articles));
			foreach ($articles as $article) {
				
				# Add the article title
				$i++;
				if ($asHtml) {
					$listing .= "\n\n" . ($htmlVersionEmailOptimised ? "<h3><a name=\"{$group}{$i}\"></a>" : "<h3 id=\"{$group}{$i}\">" . "<a href=\"#{$group}{$i}\">#</a> ") . "{$i}. " . htmlspecialchars (trim ($article['title'])) . "</h3>\n";
				} else {
					$listing .= "\n\n" . str_pad ($i, $maxlength, ' ', STR_PAD_LEFT) . '. ' . strtoupper (trim ($article['title'])) . "\n";
				}
				
				# Article metadata
				$articleText = '';
				if ($article['date']) {$articleText .= $this->createTitle ('Date', 5, $asHtml) . $article['date'];}
				if ($article['time']) {$articleText .= $this->createTitle ('Time', 5, $asHtml) . $article['time'];}
				if ($article['location']) {
					$articleText .= $this->createTitle ('Where', 5, $asHtml) . $article['location'];
					if ($article['accessibility'] == 'Venue is not disabled accessible') {$articleText .= '  [NB: not disabled accessible]';}
				}
				if ($article['date'] || $article['time'] || $article['location']) {$articleText .= "\n";}
				
				# Main article text
				$articleText .= "\n" . $this->formatText ($article['text'], $asHtml, $htmlVersionEmailOptimised) . "\n";
				
				# More article metadata
				$articleText .= $this->createTitle ('Contact', 7, $asHtml) . $this->formatText ($article['email'], $asHtml, $htmlVersionEmailOptimised);
				if ($article['webpage']) {$articleText .= $this->createTitle ('Webpage', 7, $asHtml) . $this->formatText ($article['webpage'], $asHtml, $htmlVersionEmailOptimised);}
				$articleText .= "\n";
				
				# Convert to HTML if required
				if ($asHtml) {
					$listing .= "\n<div>";
					$listing .= nl2br (trim ($articleText));
					$listing .= "\n</div>";
					$listing .= "\n\n<p class=\"right small\"><a href=\"#{$group}{$i}link\">^ Top</a></p>";
				} else {
					$listing .= $articleText;
				}
			}
		}
		
		# Return the HTML
		return $listing;
	}
	
	
	# Function make titles bold if required
	private function createTitle ($string, $groupLongestWordLength, $asHtml)
	{
		# Make bold if required
		if ($asHtml) {$string = "<strong>{$string}</strong>";}
		
		# Append a colon
		$string .= ':';
		
		# Determine subsequent padding for the text version so that the labels align
		if (!$asHtml) {
			$groupLongestWordLength = $groupLongestWordLength + 1;	// Account for the colon
			$string = str_pad ($string, $groupLongestWordLength);
		}
		
		# Add a space to the end
		$string .= ' ';
		
		# Prepend a newline
		$string = "\n" . $string;
		
		# Return the result
		return $string;
	}
	
	
	# Format to downgrade HTML to plain text; this is effectively a reversal of application::formatTextBlock ()
	private function htmlToText ($string)
	{
		# Convert HTML special characters back to plain text
		#!# htmlspecialchars_decode may not be sufficient
		$string = htmlspecialchars_decode ($string);
		
		# Treat paragraphs as having a line break after
		$string = str_replace ('</p>', "\n", $string);
		
		# Treat list items as bullet-points
		$string = str_replace ("\t<li>", "* ", $string);
		
		# Strip tags
		$string = strip_tags ($string);
		
		# Return the plain text
		return $string;
	}
	
	
	# Function to format and hyperlink text
	private function formatText ($plainTextString, $asHtml, $htmlVersionEmailOptimised = false)
	{
		# Return the string unmodified if not the HTML version
		if (!$asHtml) {return $plainTextString;}
		
		# Make the core text entity-safe
		$html = htmlspecialchars ($plainTextString);
		
		# Add hyperlinks
		$html = application::makeClickableLinks ($html);
		
		# Encode e-mail addresses, unless for e-mail
		#!# Consider adding auto-mailto of addresses - not all mail clients (e.g. Hermes Webmail v1) will add this automatically
		if (!$htmlVersionEmailOptimised) {
			$html = application::encodeEmailAddress ($html);
		}
		
		# Return the processed string
		return $html;
	}
	
	
	# Function to assemble the article group title
	private function articleGroupTitle ($text)
	{
		return $text . ' news';
	}
	
	
	# Function to construct the recipient addresses
	private function recipientAddresses ($listPattern, $yearsBack, $otherLists)
	{
		# If the settings have a specified list, return that
		if ($this->settings['listAddress']) {
			return $this->settings['listAddress'];
		}
		
		# Start a list of addresses
		$addresses = array ();
		
		# Determine the current academic year, as a two-digit string
		$year = date ('y');
		$month = date ('n');
		$currentAcademicYear = ($month < $this->settings['academicYearThresholdMonth'] ? $year - 1 : $year);
		
		# Create an entry for each year
		while ($yearsBack) {
			$yearsBack--;
			$yearTwoDigits = str_pad ($currentAcademicYear - $yearsBack, 2, '0', STR_PAD_LEFT);	// Examples: 14, 13, 12
			$addresses[] = sprintf ($listPattern, $yearTwoDigits);
		}
		
		# Merge in other lists
		$otherListsArray = (is_string ($otherLists) ? array ($otherLists) : $otherLists);
		$addresses = array_merge ($addresses, $otherListsArray);
		
		# Assemble the addresses as a comma-separated list
		$string = implode (', ', $addresses);
		
		# Return the addresses
		return $string;
	}
	
	
	# Function to show a progress bar
	private function showProgressBar ()
	{
		# Define the stages
		$stages = array (
			1 => 'submissions',
			2 => 'selection',
			3 => 'ordering',
			4 => 'setmessage',
			5 => 'finalise',
		);
		
		# Determine the current action
		$currentAction = $this->action;
		if ($currentAction == 'editsubmission') {$currentAction = 'submissions';}	// editsubmission is effectively a sub-action of submissions
		
		# Assemble a progress bar
		$links = array ();
		foreach ($stages as $number => $stage) {
			$text = $number . ': ' . $this->actions[$stage]['description'];
			$url  = $this->baseUrl . '/' . $this->actions[$stage]['url'];
			$links[] = "<a href=\"{$url}\">" . (($currentAction == $stage) ? "<strong>{$text}</strong>" : $text) . '</a>';
		}
		$html = "\n<p>Progress: " . implode (' &raquo; ', $links) . '</p>';
		
		# Return the HTML
		return $html;
	}
	
	
	# API call for dashboard
	public function apiCall_dashboard ($username = NULL)
	{
		# Start the HTML
		$html = '';
		
		# State that the service is enabled
		$data['enabled'] = true;
		
		# Ensure a username is supplied
		if (!$username) {
			$data['error'] = 'No username was supplied.';
			return $data;
		}
		
		# Define description
		$data['descriptionHtml'] = "<p>The CUSU Bulletin is issued to all undergraduates and post-graduates each week during Term.</p>";
		
		# Add links
		$data['links']["{$this->baseUrl}/"] = '{icon:house} Bulletin home';
		$data['links']["{$this->baseUrl}/archive/"] = '{icon:application_view_list} View latest';
		if (isSet ($this->administrators[$username])) {
			$data['links']["{$this->baseUrl}/submissions/"] = '{icon:cog} Prepare Bulletin';
		}

		# Add link to submit an item for the Bulletin
		$html .= "<p><a href=\"{$this->baseUrl}/submit/\" class=\"actions\"><img src=\"/images/icons/add.png\" class=\"icon\" /> Submit an item for the next Bulletin</a></p>";

		# Register the HTML
		$data['html'] = $html;
		
		# Return the data
		return $data;
	}
	
	
	# Admin editing section, substantially delegated to the sinenomine editing component
	public function editing ($attributes = array (), $deny = false)
	{
		# Get the databinding attributes
		$dataBindingAttributes = array (
			'id' => array ('regexp' => '^[a-z]+$', ),
		);
		$sinenomineExtraSettings = array (
			'headingLevel' => false,
			'int1ToCheckbox' => true,
			'fieldFiltering' => false,
		);
		
		# Delegate to the standard function for editing
		echo $this->editingTable ('types', $dataBindingAttributes, 'graybox lines', false, $sinenomineExtraSettings);
	}
}

?>
