<?php
/*
Plugin Name: Twitter Keywords
Plugin URI: http://www.josellinares.com/wordpress-plugins
Description: Place on your blog what Twitter is saying about certain keywords.
Version: 1.0.1
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
	public $activeCSS;
	public $widgetText;
	
	var $error;
	
	function __construct()
	{
		$this->keyword      = 'SEO';
		$this->username    = 'josellinares';
		$this->tweetN    = 5;
		$this->activeCSS = 'default';
		$this->widgetText = 'Que dice Twitter sobre ';
	}
	
	function initializeTwitterKeywords()
	{
		$initializeOptions = array(
		"keyword"      => $this->keyword,
		"username"     => $this->username,
		"tweetN"       => $this->tweetN,
		"useCss"   => $this->activeCSS,
		"widgetText" => $this->widgetText
		);
		add_option("twitterKeywords_options", $initializeOptions, '', 'yes');
	}
	
	function setTwitterKeywordValues($keyword,$username,$tweetN,$activeCSS,$widgetText)
	{
		$this->keyword=$keyword;
		$this->username=$username;
		$this->tweetN=$tweetN;
		$this->activeCSS=$activeCSS;
		$this->widgetText=$widgetText;	
	}
	
	function updateTwitterKeywords()
	{
		//setTwitterKeywordValues($keyword,$username,$tweetN,$activeCSS,$widgetText);	
		
		$updatedOptions = array(
		"keyword"      => $this->keyword,
		"username"     => $this->username,
		"tweetN"       => $this->tweetN,
		"useCss"   => $this->activeCSS,
		"widgetText" => $this->widgetText
		);		
		
		update_option("twitterKeywords_options", $updatedOptions, '', 'yes');
		
		return "Options Saved Correctly";
	}
	
	function getTwitterKeywordsOptions()
	{
		$myOptions = get_option('twitterKeywords_options');
		$this->keyword=$myOptions['keyword'];
		$this->username=$myOptions['username'];
		$this->tweetN=$myOptions['tweetN'];
		$this->activeCSS=$myOptions['activeCSS'];
		$this->widgetText=$myOptions['widgetText'];		
	}
	
	/*keyword could contain several keywords separated by coma.
	function extractKeywords()
	{
		$chunk_keywords=explode(trim($this->keyword));
		
		if(is_array($chunk_keywords))//we have several keywords
			return $chunk_keywords;
		else
			return false;
		
	}*/
	
			
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
		
		$curl = curl_init();   
		curl_setopt ($curl, CURLOPT_URL, $url);   
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);   
		  
		$result_xml = curl_exec ($curl); 		
			
		$xmlObjTwitter = simplexml_load_string($result_xml);
		$html='<div id="twitter-keywords">';
		$html.='<h2>'.$this->widgetText.' '.$this->keyword.'</h6>';
		$html.='<ul>';
		foreach($xmlObjTwitter->entry  as $item )
		{	
		
				$html.="<li>";
				
				foreach($item -> author as $author)
				{
					//$name_user=;
					$name_array=explode("(",$author->name);
					$twitter_user=$name_array[0];
				}
				$html.='<a href="http://twitter.com/'.$twitter_user.'" rel="nofollow">@'.$twitter_user.'</a>';
				$html.= $item -> content;	
				
				$html.="</li>";
			/*foreach($item as $index =>$value)
			{
			
				$html.="<li>";
				
				$html.= $item -> author;
				
				if($index=='content')
					$html.= $item -> content;
				
			}*/
		}
		$html.='</ul>';
		$html.='<div id="twitter-keyword-footer"><p>WP plugin by <a href="http://www.josellinares.com" title="Estrategia Digital">Estrategia Digital</a></p></div>';
		echo $html;
	}
}


	
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

function TwitterKeywordSettings() { // whitelist options
	register_setting('twitter-keyword-options', 'keywords', 'wp_filter_nohtml_kses');
	register_setting('twitter-keyword-options', 'username', 'checkValueisInt'); 
	register_setting('twitter-keyword-options', 'tweetN', 'wp_filter_nohtml_kses'); 
	register_setting('twitter-keyword-options', 'widgetText', 'wp_filter_nohtml_kses'); 
}


//save_post function. 
function updateTwitterKeywordForm($array)
{
	//setTwitterKeywordValues($keyword,$username,$tweetN,$activeCSS,$widgetText);
	$message='';
	$twitterKewyords=new twitterKeywords();
	
	$message=checkKeywords($array['keywords']);
	$message.=checkTweetN($array['tweetN']);
	$message.=checkwidgetText($array['widgetText']);
	if($message!='')
		return $message;	
	
	if($message=='')	
	{
		$twitterKewyords->setTwitterKeywordValues($array['keywords'],$array['username'],$array['tweetN'],$array['activeCSS'],$array['widgetText']);
		$twitterKewyords->updateTwitterKeywords();
		return '';	
	}
	
}
//checking form fields functions
function checkKeywords($keywords)
{
	$chunks=explode(',',$keywords);
	if(count($chunks)>3)
		return "You are not allowed to include more than 3 keywords in the keywords field<br />";
	else
	{
		if(strlen($keywords)>120)
			return "You are not allowed to include more than 120 characters in the keywords field<br />";
	}
	return "";
}
function checkTweetN($tweetN)
{	
	if(!intval( $tweetN ))
		return "Number of tweets has to be numeric and smaller than 11<br />";
	elseif($tweetN>10)
		return "Number of tweets has to be a number smaller than 11<br />";
	else
		return "";
}

function checkwidgetText($widgetText)
{
		if(strlen($widgetText)>120)
			return "You are not allowed to include more than 120 characters in the Widget Text<br />";
			
		return "";
}
//end checking form fields functions
function twitterKeywordOptions() 
{	
	$html= '<div class="wrap">';
	$html= '<form method="post">';
	settings_fields('twitter-keyword-options');
	$html.= '<h2>Twitter Keyword Options.</h2>';
	//print_r($_POST);
	if($_POST['type-submit']=='Y')
	{
		$message=updateTwitterKeywordForm($_POST);
		if($message!='')
			$html.= '<div class="error"><p><strong>'.$message.'</strong></p></div>';
		else
			$html.= '<div class="updated"><p><strong>Options Saved</strong></p></div>';
		$myOptions=get_option('twitterKeywords_options');
	}
	else
		$myOptions=get_option('twitterKeywords_options');
			
	$html.= '<label for="newpost-edited-text">Keywords you want to look for in Twitter (if more than one separate by coma)</label><br /><br />';
	$html.= '<input type="text" name="keywords" size="40" maxlength="150" value="'.$myOptions['keyword'].'" /><br /><br />';
	$html.= '<label for="newpost-edited-text">If you want to limit Twitters just from a user, fill username:</label><br /><br />';
	$html.= '<input type="text" name="username" size="40" maxlength="150" value="'.$myOptions['username'].'" /><br /><br />';
	$html.= '<label for="newpost-edited-text">Number of Tweets you want to display</label><br /><br />';
	$html.= '<input type="text" name="tweetN" size="40" maxlength="150" value="'.$myOptions['tweetN'].'" /><br /><br />';
	$html.= '<label for="newpost-edited-text">Head Widget text</label><br /><br />';
	$html.= '<input type="text" name="widgetText" size="40" maxlength="150" value="'.$myOptions['widgetText'].'" /><br /><br />';
	$html.= '<input type="hidden" name="type-submit" value="Y">';
	$html.= '<input type="submit" class="button-primary" value="save options" />';
	$html.= '</form>';
	$html.= '</div>';
	
	echo $html;
}


//
/*function checkFormValues($keyword,$username,$tweetN,$activeCSS,$widgetText)
{
	$message="";
	if(is_int($_POST['tweetN']))
		$message="El número de tweets tiene que ser numérico";
	
	if($message=='')
	{
		setTwitterKeywordValues($keyword,$username,$tweetN,$activeCSS,$widgetText);

		$updatedOptions = array(
		"keyword"      => $keyword,
		"username"     => $username,
		"tweetN"       => $tweetN,
		"useCss"   => $activeCSS,
		"widgetText" => $widgetText
		);
				
		update_option("twitterKeywords_options", $updatedOptions, '', 'yes');	
		
		$message="Todo en orden";
	}
	
	return $message;
		
}

//checking that the form has been submitted
if($_POST['submit-type'] == 'options')
{
	checkFormValues($_POST['keyword'],$_POST['username'],$_POST['tweetN'],$_POST['activeCSS'],$_POST['widgetText']);
}*/

?>