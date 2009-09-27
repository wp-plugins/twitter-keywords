<?php
/*
Plugin Name: Twitter Keywords
Plugin URI: http://www.josellinares.com/wordpress-plugins/twitter-keywords-wordpress-plugin/
Description: Place on your blog what Twitter is saying about certain keyword.
Version: 1.0
Author: Jose Llinares
Author URI: http://www.josellinares.com
*/

//activate plugin WP function
register_activation_hook( __FILE__, 'initializeTwitterKeywords' );

//activat plugin WP function
register_deactivation_hook( __FILE__, 'deactivateTwitterKeywords' );

//set initial values when the plugin is activated
function initializeTwitterKeywords()
{
	$twitter_keywords=new twitterKeywords();
	$twitter_keywords->initializeTwitterKeywords();
}
//delete DB options when the plugin is activated
function deactivateTwitterKeywords() {
	delete_option("twitterKeywords_options");
}

class twitterKeywords
{
	public $keyword;
	public $username;
	public $tweetN;
	public $widgetText;
	public $lang;
	var $error;
	
	function __construct()
	{
		$this->keyword = 'Twitter';
		$this->username = '';
		$this->tweetN = 5;
		$this->lang = 'all';
		$this->widgetText = 'What Twitter says about: ';
	}
	
	//this function is executed when the plugin is activated
	//set up default values
	//create menu option	
	function initializeTwitterKeywords()
	{
		$initializeOptions = array(
		"keyword"      => $this->keyword,
		"username"     => $this->username,
		"tweetN"       => $this->tweetN,
		"lang" 			=>$this->lang,
		"widgetText" => $this->widgetText
		);
		add_option("twitterKeywords_options", $initializeOptions, '', 'yes');
	}
	
	//set twitterKeyword class values
	function setTwitterKeywordValues($keyword,$username,$tweetN,$lang,$widgetText)
	{
		$this->keyword=$keyword;
		$this->username=$username;
		$this->tweetN=$tweetN;
		$this->lang=$lang;
		$this->widgetText=$widgetText;	
	}
	
	//update twitterKeyword class values and DB options
	function updateTwitterKeywords()
	{		
		$updatedOptions = array(
		"keyword"      => $this->keyword,
		"username"     => $this->username,
		"tweetN"       => $this->tweetN,
		"lang" 			=>$this->lang,
		"widgetText" => $this->widgetText
		);		
		
		update_option("twitterKeywords_options", $updatedOptions, '', 'yes');
		
		return __("Options Saved Correctly");
	}
	
	//get DB options for twitterKeyword attributes
	function getTwitterKeywordsOptions()
	{
		$myOptions = get_option('twitterKeywords_options');
		$this->keyword=$myOptions['keyword'];
		$this->username=$myOptions['username'];
		$this->tweetN=$myOptions['tweetN'];
		$this->lang=$myOptions['lang'];
		$this->widgetText=$myOptions['widgetText'];		
	}
	
	//read Twitter Search XML and create the output	
	function readXMLTwitterSearch()
	{
		$this->getTwitterKeywordsOptions();
		
		$url = "http://search.twitter.com/search.atom?";
		if($this->username!='')
			$url .="from=".$this->username;
		if($this->tweetN>0)
			$url .="&rpp=".$this->tweetN;
		if($this->keyword!='')
			$url .="&q=".$this->keyword;
		if($this->lang!='')
			$url .="&lang=".$this->lang;		
			
		$curl = curl_init();   
		curl_setopt ($curl, CURLOPT_URL, $url);   
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);   
		  
		$result_xml = curl_exec ($curl); 		
			
		$xmlObjTwitter = simplexml_load_string($result_xml);
		$html='<div id="twitter-keywords">';
		$html.='<h2>'.$this->widgetText.' '.$this->keyword.'</h2>';
		$html.='<ul>';
		if($xmlObjTwitter)
		{
			foreach($xmlObjTwitter->entry  as $item )
			{	
				$html.="<li>";
				foreach($item -> author as $author)
				{
					$name_array=explode("(",$author->name);
					$twitter_user=$name_array[0];
				}
				$html.='<a href="http://twitter.com/'.$twitter_user.'" rel="nofollow" class="twitter_author">@'.$twitter_user.'</a>';
				$html.= $item -> content;
				$html.="</li>";	
			}
		}
		else
		{
			$html.="<li>Ooops... it seems we can't show anything</li>";	
		}
		$html.='</ul>';
		$html.='<div id="twitter-keyword-footer"><p style="text-align:right"><small>WP plugin by <a href="http://www.josellinares.com" title="Marketing Digital Barcelona" target="_blank">Marketing Digital</a></small></p></div>';
		$html.='</div>';//fin twitter keywords div
		//print the output;
		echo $html;
	}
}
//end class


//create Settings Section to configure plugin values
if (is_admin() ){ // admin actions
	add_action('set_twitter_keyword_values','set_twitter_keyword');
	add_action('admin_menu','admin_setTwitterKeywords');
	add_action('admin_init','TwitterKeywordSettings' );
} else {
  // non-admin enqueues, actions, and filters
}
function admin_setTwitterKeywords() {
	add_options_page('Twitter Keywords Administration', 'Twitter Keyword', 8,__FILE__, 'twitterKeywordOptions');
}

//register form fields
function TwitterKeywordSettings() { // whitelist options
	register_setting('twitter-keyword-options', 'keywords', 'wp_filter_nohtml_kses');
	register_setting('twitter-keyword-options', 'username', 'checkValueisInt'); 
	register_setting('twitter-keyword-options', 'tweetN', 'wp_filter_nohtml_kses'); 
	register_setting('twitter-keyword-options', 'widgetText', 'wp_filter_nohtml_kses');
	register_setting('twitter-keyword-options', 'lang', 'wp_filter_nohtml_kses');  
}


//Update twitterKeyword options with form values. 
function updateTwitterKeywordForm($array)
{
	//setTwitterKeywordValues($keyword,$username,$tweetN,$widgetText);
	$message='';
	$twitterKewyords=new twitterKeywords();
	
	//check values before inserting into DB
	$message=checkKeywords($array['keywords']);
	$message.=checkTweetN($array['tweetN']);
	$message.=checkwidgetText($array['widgetText']);
	
	if($message!='')
		return $message;	
	
	if($message=='')	
	{
		$twitterKewyords->setTwitterKeywordValues($array['keywords'],$array['username'],$array['tweetN'],$array['lang'],$array['widgetText']);
		$twitterKewyords->updateTwitterKeywords();
		return '';	
	}
	
}

//checking form fields functions
function checkKeywords($keywords)
{

	if(strlen($keywords)>120)
		return __("You are not allowed to include more than 120 characters in the keywords field<br />");
	else
		return "";
}
function checkTweetN($tweetN)
{	
	if(!intval( $tweetN ))
		return __("Number of tweets has to be numeric and smaller than 100<br />");
	elseif($tweetN>100)
		return __("Number of tweets has to be a number smaller than 100<br />");
	else
		return "";
}

function checkwidgetText($widgetText)
{
	if(strlen($widgetText)>120)
	{
		return __("You are not allowed to include more than 120 characters in the Widget Text<br />");
	}	
	return "";
}
//end checking form fields functions

//create adming->settings page
function twitterKeywordOptions() 
{	
	$html= '<div class="wrap">';
	$html= '<form method="post">';
	settings_fields('twitter-keyword-options');
	$html.= '<h2>'. __("Twitter Keyword Plugin: Manage Options").'</h2>';
	//print_r($_POST);
	if($_POST['type-submit']=='Y')
	{
		$message=updateTwitterKeywordForm($_POST);
		if($message!='')
			$html.= '<div class="error"><p><strong>'.$message.'</strong></p></div>';
		else
			$html.= '<div class="updated"><p><strong>'.__("Options Saved").'</strong></p></div>';
		$myOptions=get_option('twitterKeywords_options');
	}
	else
		$myOptions=get_option('twitterKeywords_options');
			
	$html.= '<label for="newpost-edited-text">'.__('Keywords you want to look for in Twitter').'</label><br /><br />';
	$html.= '<input type="text" name="keywords" size="40" maxlength="150" value="'.$myOptions['keyword'].'" /><br /><br />';
	$html.= '<label for="newpost-edited-text">'.__('Twitter User (you can leave it empty and it will display all users):').'</label><br /><br />';
	$html.= '<input type="text" name="username" size="40" maxlength="150" value="'.$myOptions['username'].'" /><br /><br />';
	$html.= '<label for="newpost-edited-text">'.__('Number of Tweets you want to display (Max 100)').'</label><br /><br />';
	$html.= '<input type="text" name="tweetN" size="40" maxlength="150" value="'.$myOptions['tweetN'].'" /><br /><br />';
	$html.= '<label for="newpost-edited-text">'.__('Tweets Language:').'</label><br /><br />';
	$html.= '<select name="lang">';
	$html.= '<option value="all" '.checkSelected('all',$myOptions['lang']).'>'.__('Any Language:').'</option>';
	$html.= '<option value="ar" '.checkSelected('ar',$myOptions['lang']).'>Arabic </option>';
	$html.= '<option value="da" '.checkSelected('da',$myOptions['lang']).'>Danish (dansk)</option>';
	$html.= '<option value="nl" '.checkSelected('nl',$myOptions['lang']).'>Dutch (Nederlands)</option>';
	$html.= '<option value="en" '.checkSelected('en',$myOptions['lang']).'>English</option>';
	$html.= '<option value="fi" '.checkSelected('fi',$myOptions['lang']).'>Finnish (suomen kieli)</option>';
	$html.= utf8_encode('<option value="fr" '.checkSelected('fr',$myOptions['lang']).'>French (français)</option>');
	$html.= '<option value="de" '.checkSelected('de',$myOptions['lang']).'>German (Deutsch)</option>';
	$html.= '<option value="hu" '.checkSelected('hu',$myOptions['lang']).'>Hungarian (Magyar)</option>';
	$html.= utf8_encode('<option value="is" '.checkSelected('is',$myOptions['lang']).'>Icelandic (Íslenska)</option>');
	$html.= '<option value="it" '.checkSelected('it',$myOptions['lang']).'>Italian (Italiano)</option>';
	$html.= '<option value="ja" '.checkSelected('ja',$myOptions['lang']).'>Japanese</option>';
	$html.= '<option value="no" '.checkSelected('no',$myOptions['lang']).'>Norwegian (Norsk)</option>';
	$html.= '<option value="pl" '.checkSelected('pl',$myOptions['lang']).'>Polish (polski)</option>';
	$html.= utf8_encode('<option value="pt" '.checkSelected('pt',$myOptions['lang']).'>Portuguese (Português)</option>');
	$html.= '<option value="ru" '.checkSelected('ru',$myOptions['lang']).'>Russian </option>';
	$html.= utf8_encode('<option value="es" '.checkSelected('es',$myOptions['lang']).'>Spanish (español)</option>');
	$html.= '<option value="sv" '.checkSelected('sv',$myOptions['lang']).'>Swedish (Svenska)</option>';
	$html.= '<option value="th" '.checkSelected('th',$myOptions['lang']).'>Thai </option>';
	$html.= '</select><br /><br />';
	$html.= '<label for="newpost-edited-text">Head Widget text</label><br /><br />';
	$html.= '<input type="text" name="widgetText" size="40" maxlength="150" value="'.$myOptions['widgetText'].'" /><br /><br />';
	$html.= '<input type="hidden" name="type-submit" value="Y">';
	$html.= '<input type="submit" class="button-primary" value="'.__('Save Options').'" />';
	$html.= '</form>';
	$html.= '</div>';
	
	echo $html;
}
//check the selected value, for the language field
function checkSelected($lang,$existing_lang)
{
	if($lang==$existing_lang)
		return 'selected="selected"';
	else
		return '';
}

//function to initailize the class. Called from sidebar.php
function callTwitterKeywords()
{
	$twitter_keywords=new twitterKeywords();
	$twitter_keywords->readXMLTwitterSearch();
}


//Widgetizing the plugin functions
function setTwitterKeywordsPlugin()
{
  register_sidebar_widget(__('Twitter Keywords'), 'callTwitterKeywords'); 
  register_widget_control(__('Twitter Keywords'), 'TwitterKeywordsControl', 200, 200 );
}
add_action("plugins_loaded", "setTwitterKeywordsPlugin");

function TwitterKeywordsControl()
{
  echo '<p><label for="myHelloWorld-WidgetTitle">To configure options go to "Settings > Twitter Keywords" in this admin panel</label></p>';
}
//end widgetizing functions;
?>