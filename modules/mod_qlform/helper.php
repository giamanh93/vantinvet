<?php
/**
 * @package		mod_qlform
 * @copyright	Copyright (C) 2013 ql.de All rights reserved.
 * @author 		Mareike Riegel mareike.riegel@ql.de
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

jimport( 'joomla.form.form' );

$arr_files=array('modelQlForm','modQlFormCaptcha','modQlFormCaptcha','modQlFormMailer','modQlFormDatabase','modQlFormDatabaseExternal','modQlFormMessager','modQlFormSomethingElse','modQlFormSomethingCompletelyDifferent',);
foreach ($arr_files as $k=>$v) if (!class_exists($v) AND file_exists($file=dirname(__FILE__).'/classes/'.$v.'.php')) require_once($file);

class modQlFormHelper
{

	/**
	 * method to do something else, 
	 * that the developer of this module could never have guessed 
	 * 
	 * @param mixed $variable at your service 
	 */  
    function doSomethingElse($data,$params,$module,$form)
    {
       if (!class_exists('modQlFormSomethingElse')) {$this->arrMessages[]=array('warning'=>1, 'str'=>JTEXT::_('MOD_QLFORM_SOMETHINGELSENOTFOUND')); return false;}
       $obj=new modQlFormSomethingElse($data,$params,$module,$form);
       if (1==$obj->doSomethingElse()) $this->arrMessages[]=array('warning'=>0, 'str'=>JTEXT::_('MOD_QLFORM_SOMETHINGELSEWORKEDOUTFINE'));
       else $this->arrMessages[]=array('warning'=>1, 'str'=>JTEXT::_('MOD_QLFORM_SOMETHINGELSEDIDNOTWORK'));
    }
	/**
	 * method to do something completely different,
	 *
	 * @param mixed $variable at your service
	 */
    function doSomethingCompletelyDifferent($data,$params,$module,$form)
    {
       if (!class_exists('modQlFormSomethingCompletelyDifferent')) {$this->arrMessages[]=array('warning'=>1, 'str'=>JTEXT::_('MOD_QLFORM_SOMETHINGCOMPLETELYDIFFERENTNOTFOUND')); return false;}
       $obj=new modQlFormSomethingCompletelyDifferent($data,$params,$module,$form);
       if (1==$obj->doSomethingCompletelyDifferent()) $this->arrMessages[]=array('warning'=>0, 'str'=>JTEXT::_('MOD_QLFORM_SOMETHINGCOMPLETELYDIFFERENTWORKEDOUTFINE'));
       else $this->arrMessages[]=array('warning'=>1, 'str'=>JTEXT::_('MOD_QLFORM_SOMETHINGCOMPLETELYDIFFERENTDIDNOTWORK'));
    }

    /**
     * method to turn content vor array or object to string
     * that the developer of this module could never have guessed
     *
     * @param mixed $variable at your service
     */
    function dump($data,$type='var_dump')
    {
        if ('var_dump'==$type)
        {
            ob_start();
            var_dump($data);
            $str_data=ob_get_contents();
            ob_end_clean();
        }
        elseif ('foreachstring'==$type)
        {
            $str_data="";
            foreach ($data as $k=>$v)
            {
                $str_data.='col['.$k.']=>'.$v.'<br />';
            }
        }
        return $str_data;
    }
	/**
	 * method to transform string with [ and ] to xml with < and >
	 *
	 * @param string $str_content of param cell
	 * @return string $str_xml
	 */
    function transformText($str_content)
    {
        $str_xml=$str_content;
    	$str_xml=preg_replace("/\[/","<",$str_xml);
        $str_xml=preg_replace("/\]/",">",$str_xml);
        return $str_xml;
    }
	/**
	 * method to generate an array for a field
	 *
	 * @param string $field field name
	 * @param string $type type of field
	 * @param string $label of field, if empty gets value or fieldname 
	 * @return string 
	 */
    
    function generateArrayField($field,$type,$label='',$value=false)
    {
		$arr=array();
    	$arr['name']=$field;
		$arr['type']=$type;
		$arr['label']=$label;
		if ('user_id'==$field) $arr['value']=$this->getUserData('id');
		elseif ('user_email'==$field) $arr['value']=$this->getUserData('email');
		elseif ('article_id'==$field) $arr['value']=$this->getArticleData('id');
		elseif ('article_title'==$field) $arr['value']=$this->getArticleData('title');
        else $arr['value']=$value;
		$this->arrFields[]=$arr;
    }
    /**
     * method to get and manipulize server data
     */
    function getServerData($ipSecure)
    {
        if(1!=$ipSecure) return $_SERVER;
        $this->arrServer=$_SERVER;
        $arrIp=preg_split('/\./',$_SERVER['REMOTE_ADDR']);
        unset($arrIp[count($arrIp)-1]);
        unset($arrIp[count($arrIp)-1]);
        $this->arrServer['REMOTE_ADDR']=(implode('.',$arrIp).'.x.x');
        return $this->arrServer;
    }

	/**
	 * method to add field to xml
	 *
	 * @param string $str_xml
	 * @param string $arrFields array of fields to add
	 * @param string $class string of fieldset class
	 * @return string $str_xml
	 */
    function addFieldsToXml($str_xml,$arrFields,$class='')
    {
        if (is_array($arrFields))
        {
	    	$formCloseTag='</form>';
	    	$str_xml=str_replace($formCloseTag, '<fieldset class="'.$class.'" name="qlform'.md5(rand(0,100)).'">'.$formCloseTag,$str_xml);
	    	foreach ($arrFields as $k=>$v)
	        {
	        	$str_fieldtag='<field name="'.$v['name'].'" value="'.$v['value'].'" default="'.$v['value'].'" type="'.$v['type'].'" label="'.$v['label'].'" />'."\n";
	        	$str_xml=str_replace($formCloseTag, $str_fieldtag.$formCloseTag,$str_xml);
	        	$str_tag="";
	        }
	        $str_xml=str_replace($formCloseTag, '</fieldset>'.$formCloseTag,$str_xml);
        }
        return $str_xml;
    }
    /**
     * method to get fields from current article
     * @param string $field
     * @return result field value
     * see http://forum.joomla.org/viewtopic.php?t=525350
     */
    function getArticleData($field)
    {
    	$app=JFactory::getApplication();
        $option=$app->input->getData('option');
        $view = $app->input->getData('view');
        $article_id = (string)$app->input->getData('id');
    	if ($option=='com_content' && $view=='article')
    	{
    		$article=JTable::getInstance('content');
    		$article->load($article_id);
    		return $article->get($field);
    	}
    	else return false;
    }

    /**
     * method to get fields from current user
     * @param string $field
     * @return result field value
     */
    function getUserData($field)
    {
    	$user=JFactory::getUser();
    	if (""!=$user->get($field)) return $user->get($field);
    	else return false;
    }
    
    /** 
     * method to transform xml string to xml object 
     * @param string $str_xml
     * @return object xml
     */
    function getXml($str_xml)
    {
        return simplexml_load_string($str_xml); 
    }
	/**
	 * Method to get the form based on str_xml.
	 *
	 * The base form is loaded from XML and then an event is fired
	 * for users plugins to extend the form with extra fields.
	 *
	 * @param	array	$str_xml		An optional array of data for the form to interogate.
	 * @return	mixed  	$form		JForm object on success, false on failure
	 * @since	1.6
	 */
    public function getForm($str_xml,$id)
	{
		$this->obj_form=new modelQlForm();
		$this->obj_form->form_name='qlform'.$id;
		$this->obj_form->str_xml=$str_xml;
		$form=$this->obj_form->getForm();
		if (is_object($form)) return $form; 
		else $this->arrMessages[]=array('warning'=>1, 'str'=>JTEXT::_('MOD_QLFORM_NO_FORM_GIVEN'));
		return false;
	}

	/**
	 * Method to get the form based on str_xml.
	 *
	 * The base form is loaded from XML and then an event is fired
	 * for users plugins to extend the form with extra fields.
	 *
	 * @param	array	$data		An array of data (post data) to be validated
	 * @return	mixed  	$form		JForm object on success, false on failure
	 * @since	1.6
	 */
    public function validate($data)
	{
		$validated=$this->obj_form->check($data);
		if (0==$validated) $this->arrMessages[]=array('warning'=>1,'str'=>JTEXT::_('MOD_QLFORM_VALIDATION_FAILED'));
		$this->raiseFormErrors();
		return $validated;
	}

	/**
	 * Method to raise Errors
	 *
	 * @param	int	$type		type of error displayed, either via joomla or text displayes
	 * @since	1.6
	 */
    public function raiseFormErrors()
	{
		if (isset($this->obj_form->formErrors) AND is_array($this->obj_form->formErrors))
		foreach ($this->obj_form->formErrors as $k=>$v)
        $this->arrMessages[]=array('warning'=>1, 'str'=>$v->getMessage());
	}

	/**
	 * Method to raise Errors
	 *
	 * @param	int	$type		type of error displayed, either via joomla or text displayes
	 * @since	1.6
	 */
    public function displayMessages($type)
    {
		$obj_messager = new modQlFormMessager($this->arrMessages,$type);
		if (isset($obj_messager->message) AND !empty($obj_messager->message)) return $obj_messager->message; 
	}
	/**
	 * Method for checking database     
	 *
	 * @param   string  $table      Name of table to save data in
	 * @param   array  	$data 		Array of data to compare to tabel cols
	 *
	 * @return  mixed 	array with table cols on success, false on failure
	 *
	 */	
	public function connectToDatabase($paramsDatabaseExternal=array())
	{
		if (0==count($paramsDatabaseExternal))$this->obj_database=new modQlFormDatabase();
        else $this->obj_databaseexternal=new modQlFormDatabaseExternal($paramsDatabaseExternal);
	}
	/**
	 * Method for saving data in database  
	 *
	 * @param   string  $database  database name 
	 * @param   string  $table      Name of table to save data in
	 * @param   bool  	$showErrors If to raise Errors or let be
	 *
	 * @return  bool 	true on success, false on failure
	 *
	 */	
	public function saveToDatabase($table,$data,$paramsDatabaseExternal=array())
	{
		//echo "<pre>";print_r($paramsDatabaseExternal);die;print_r($this->arrTableFields);die;
        $data=array_intersect_key($data,$this->arrTableFields);
        if (0==count($paramsDatabaseExternal))$this->obj_database->save($table,$data);
        else $this->obj_databaseexternal->save($table,$data);;
		return true;
	}
	/**
	 * Method for checking database     
	 *
	 * @param   string  $table      Name of table to save data in
	 * @param   array  	$data 		Array of data to compare to tabel cols
	 *
	 * @return  mixed 	array with table cols on success, false on failure
	 *
	 */	
	public function checkDatabase($obj_database,$table,$str_xml,$showErrors,$fieldCreated)
	{
		//echo "<pre>"; print_r($obj_database->getDatabaseName());
        $checkTable=$this->checkTableExists($obj_database,$table,$showErrors);
		$this->compareTableToData($this->arrTableFields,$str_xml,$showErrors,$table,$fieldCreated,$obj_database->getDatabaseName());
		return $checkTable;
	}
	/**
	 * Method for checking if table exists    
	 *
	 * @param   string  $table      Name of table to save data in
	 * @param   bool  	$showErrors If to raise Errors or let be
	 * 
	 * @return  bool 	truewith table cols on success, false on failure
	 *
	 */	
	public function checkTableExists($obj_database,$table,$showErrors)
	{
		$strDatabase=$obj_database->getDatabaseName();
		$table=$obj_database->getTableName($table);

		$tableExists=$obj_database->tableExists($strDatabase,$table);
		$this->arrTableFields=array();
		if (false==$tableExists AND 1==$showErrors) 
		{
			$this->arrMessages[]=array('warning'=>1, 'str'=>sprintf(JText::_('MOD_QLFORM_DBNOTFOUND_FORM'),$table,$strDatabase));
		}
        if (false==$tableExists) return false;
		$arrDatabase=$obj_database->getDatabaseFields($strDatabase,$table);
		$this->arrTableFields=$obj_database->databaseFieldsObjectToArray($arrDatabase);
		return true;
	}
	/**
	 * Method for checking if table exists    
	 *
	 * @param   string  $table      Name of table to save data in
	 * @param   bool  	$showErrors If to raise Errors or let be
	 *
	 * @return  bool 	true on success, false on failure
	 *
	 */	
	public function compareTableToData($arrTableFields,$str_xml,$showErrors,$table,$fieldCreated,$strDatabase)
	{
		$xml=$this->getXML($str_xml);
		$arrFormFields=array_flip($this->getFields($xml));
		if (1==$fieldCreated)$arrFormFields['created']=0;
		$arrDifference1=array_diff_key($arrFormFields,$arrTableFields);
		$arrDifference2=array_diff_key($arrTableFields,$arrFormFields);
		$this->arrClean=array_intersect_key ($arrFormFields,$arrTableFields);
		if (1==$showErrors)
		{ 
			if (1<=count($arrDifference1) OR 1<=count($arrDifference2)) $this->arrMessages[]=array('warning'=>1, 'str'=>JText::_('MOD_QLFORM_DBFORM_ERROR_TITLE'));
			foreach ($arrDifference1 as $k=>$v) $this->arrMessages[]=array('warning'=>1, 'str'=>sprintf(JText::_('MOD_QLFORM_DBFORM_ERROR_DATABASE'),$k,$table,$strDatabase));
			foreach ($arrDifference2 as $k=>$v) $this->arrMessages[]=array('warning'=>1, 'str'=>sprintf(JText::_('MOD_QLFORM_DBFORM_ERROR_FORM'),$k,$table,$strDatabase));
			if (1<=count($arrDifference1) OR 1<=count($arrDifference2)) $this->arrMessages[]=array('warning'=>1, 'str'=>JText::_('MOD_QLFORM_DBFORM_ERROR_GENERAL'));
		}
		return true;
	}
	
	/**
	 * Method for checking database     
	 *
	 * @param   string  $table      Name of table to save data in
	 * @param   array  	$data 		Array of data to compare to tabel cols
	 *
	 * @return  mixed 	array with table cols on success, false on failure
	 *
	 */	
	public function getFields($xml)
	{
		//print_r($xml);die;
		if (is_object($xml->fieldset)) foreach ($xml->fieldset as $k=>$v)
		{
			if (is_object($v->field)) foreach ($v->field as $k2=>$v2)
			{
				if (isset($v2['type']) AND 'spacer'!=$v2['type']) $arr[]=(string) $v2['name'];
			}
		}
		return $arr;
	}
	/**
	 * Method to mail
	 *
	 * @param	string	$recipient email address of recipient
	 * @param	string	$subject of email
	 * @param 	array   $data array of post data to be sent
	 * @since	1.6
	 */	
	public function mail($recipient,$subject,$data,$params,$form,$replyTo='',$pretext='',$labels=1)
	{
        $paramsMail=$this->mailPrepareParams($data,$params,$replyTo);
        $data=$this->prepareDataWithXml($data,$form,$labels);
        $obj_mailer=new modQlFormMailer();

        if (2==$params->get('emailseparator','1')) $obj_mailer->separator="\n";
        if (2==$params->get('emailseparator2','1')) $obj_mailer->separator2="\n\n";

        /*generate subject with additional data of field entries*/
        if (''!=trim($paramsMail['emailsubject2']))
        {
            $arrFieldValues=preg_split('/,/',$paramsMail['emailsubject2']);
            if (is_array($arrFieldValues)) foreach ($arrFieldValues as $k=>$v) if (isset($data[trim($v)]) AND isset($data[trim($v)]['data'])) $subject.=' :: '.(string)$data[trim($v)]['data'];
        }
        if(''!=trim($pretext))$pretext=$this->preparePretext($pretext,$data);
        $mailSent=$obj_mailer->mail($recipient,$subject,$data,$paramsMail,$pretext);
        if (1==$mailSent) return true;
        else
        {
            $this->arrMessages[]=array('warning'=>1, 'str'=>JText::_('MOD_QLFORM_MAIL_SENT_ERROR'));
            return false;
        }
	}

    public function prepareDataWithXml($data,$form,$labels=1)
    {
        $dataWithLabel=array();
        foreach ($data as $k=>$v)
        {
            $label=$k;
            if (''!=$form->getLabel($k) AND 1==$labels) $label=str_replace('&#160;','',htmlspecialchars_decode(strip_tags($form->getLabel($k))));
            $dataWithLabel[$k]['label']=$label;
            $dataWithLabel[$k]['data']=$v;
        }
        return $dataWithLabel;
    }
    public function mailPrepareParams($data,$params,$replyTo)
    {
        $config=new JConfig();
        $arrConfig=JArrayHelper::fromObject($config);
        $arrMailParams=array();

        $arrMailParams['emailrecipient']=$params->get('emailrecipient',$arrConfig['mailfrom']);
        $arrMailParams['emailsubject']=$params->get('emailsubject',$arrConfig['sitename']);

        /*generate subject from field values*/
        $subjectGenerated=$params->get('emailsubject2','');
        if (''!=trim($subjectGenerated))
        {
            $arrSubjectGenerated=explode(',',$subjectGenerated);
            $subjectGenerated='';
            foreach ($arrSubjectGenerated as $k=>$v) if (isset($data[trim($v)]))$subjectGenerated.=' :: '.$data[trim($v)];
        }
        else $subjectGenerated='';
        $arrMailParams['emailsubject2']=$subjectGenerated;

        /*set e-mail sender*/
        $emailSender=$params->get('emailsender','');
        if (''!=trim($emailSender) AND isset($data[trim($emailSender)]) AND true==$this->checkEmail($data[$emailSender])) $emailSender=$data[$emailSender];
        else $emailSender=$arrConfig['mailfrom'];
        $arrMailParams['emailsender']=$emailSender;

        /*set replyTo*/
        $emailReplyTo=$params->get('emailreplyto','');
        if (''!=trim($emailReplyTo) AND isset($data[$emailReplyTo]) AND true==$this->checkEmail($data[$emailReplyTo])) $emailReplyTo=$data[$emailReplyTo];
        else $emailReplyTo=$arrConfig['mailfrom'];
        $arrMailParams['emailreplyto']=$emailReplyTo;

        return $arrMailParams;
    }

	/**
	 * Method to initiate captcha
	 *
	 * @param	int	$type		type of error displayed, either via joomla or text displayes
	 * @since	1.6
	 */	
	public function initiateCaptcha($params)
	{
		$this->checkTmpQlform('tmp/qlform');
        $obj_captcha=new modQlFormCaptcha('modules/mod_qlform/captcha/fonts/LS-Bold.otf','tmp/qlform');
        $array=array('intTextLenght','intFontSize','intIMGWidth','intIMGHeight','arrTextColor','arrBGColor','intFontAngel');
        foreach ($array as $v)
        {
            if ('arrTextColor'!=$v AND 'arrBGColor'!=$v) $obj_captcha->$v=$params->get('captcha_'.$v);
            else
            {
                $obj_captcha->$v=$this->hexcolor($params->get('captcha_'.$v));
            }
        }
		$this->obj_captcha=$obj_captcha;
	}
    /**
     * Method to check if folder exists and generates it eventually
     */
    public function checkTmpQlform($folder)
    {
        if (!is_dir($folder))mkdir($folder);
        if (!is_file($folder.'/index.html'))touch($folder.'/index.html');
        $this->checkTmpQlformFiles($folder);
    }
    /**
     * Method to check for old files and to remove them
     */
    public function checkTmpQlformFiles($folder)
    {
        $handle=opendir($folder);
        while($file=readdir($handle))
        {
            if ('.'!=$file AND '..'!=$file AND 'index.html'!=$file)
            {
                $arr=preg_split('?_?',$file);
                $dateFile=substr(array_pop($arr),0,6);
                if ($dateFile+2<date('ymd') AND file_exists($folder.'/'.$file))unlink($folder.'/'.$file);
            }
        }
        closedir($handle);
    }

    public function hexcolor($hex)
    {
        $hex=preg_replace('/#/','', $hex);
        $color=array();
        if(3==strlen($hex))
        {
            $color[]=hexdec(substr($hex, 0, 1).substr($hex, 0, 1));
            $color[]=hexdec(substr($hex, 1, 1).substr($hex, 1, 1));
            $color[]=hexdec(substr($hex, 2, 1).substr($hex, 2, 1));
        }
        else if(6==strlen($hex))
        {
            $color[]=hexdec(substr($hex, 0, 2));
            $color[]=hexdec(substr($hex, 2, 2));
            $color[]=hexdec(substr($hex, 4, 2));
        }
        return $color;
    }

	/**
	 * Method to generate captcha
	 *
	 * @param	int	$type		type of error displayed, either via joomla or text displayes
	 * @since	1.6
	 */	
	public function generateCaptcha($moduleId)
	{
		$this->captcha=$this->obj_captcha->getFilename($moduleId);
        $this->obj_captcha->generateCaptcha();
		$this->obj_captcha->setSession($moduleId);
	}
	/**
	 * Method to check captcha
	 *
	 * @param	int	$type		type of error displayed, either via joomla or text displayes
	 * @since	1.6
	 */	
	public function checkCaptcha($strCaptcha,$moduleId)
	{
		$validated=$this->obj_captcha->checkCaptcha($strCaptcha,$moduleId);
		if (0==$validated) $this->arrMessages[]=array('warning'=>1,'str'=>JTEXT::_('MOD_QLFORM_CAPTCHA_FAILED'));
		return $validated;
	}

	/**
	 * method to merge two subarray given 
	 * 
	 * @param array $array multidimensional array 
	 * @param string $index1 index of subarray to be merged
	 * @param string $index2 index of second subarray to be merged
	 * @return mixed $mergedArray array containing elements of former subarray on success, false on failure
	 */  
	function mergeSubarrays($array,$index1,$index2)
	{
		if (isset($array[$index1]) AND is_array($array[$index1]) AND isset($array[$index2]) AND is_array($array[$index2])) $mergedArray=array_merge($array[$index1],$array[$index2]);
		else if (isset($array[$index1]) AND is_array($array[$index1])) $mergedArray=$array[$index1];
		else if (isset($array[$index2]) AND is_array($array[$index2])) $mergedArray=$array[$index2];
		else return false;
		return $mergedArray;
	}
    /**
     * method to turn subarray into string via json_encode
     *
     * @param array $array multidimensional array
     * @return array $array array containing subarray as jsonified strings
     */
    function subarrayToJson($array)
    {
        if (is_array($array)) while (list($k,$v)=each($array)) if (is_array($v)) $array[$k]=json_encode($v);
        return $array;
    }
    /**
     * method to turn subarray into string via json_encode
     *
     * @param array $array multidimensional array
     * @return array $array array containing subarray as jsonified strings
     */
    function subarrayOffJson($array)
    {
        if (is_array($array)) while (list($k,$v)=each($array)) $array[$k]=json_decode($v);
        return $array;
    }
    /**
     * method to strip quotes in values of array
     *
     * @param array $array array whose values has quotes
     * @return array $array array containing subarray as jsonified strings
     */
    function stripQuotesInArrayValue($array)
    {
        //if (is_array($array)) while (list($k,$v)=each($array)) $array[$k]=preg_replace("/\"/","'",$v);
        if (is_array($array)) foreach ($array as $k=>$v) $array[$k]=preg_replace("/\"/","'",$v);
        return $array;
    }
    /**
     * method to strip quotes in values of array
     *
     * @param array $array array whose values has quotes
     * @return array $array array containing subarray as jsonified strings
     */
    function sendAdminmessage($recipient,$sender,$subject,$dataJsonified,$intUseHtml)
    {
        $message='';
        if (1==$intUseHtml) foreach ($dataJsonified as $k=>$v) $message.='  ++<br /><strong>'.$k.'</strong>: <br />'.$v.'<br />++  '."<br /><br />";
        else foreach ($dataJsonified as $k=>$v) $message.='  ++'.$k.': '.$v.'++  ';
        $db=new modQlFormDatabase();
        $data=array();
        if (0==$sender OR 0==$recipient)
        {
            if (0==$sender)$this->arrMessages[]=array('warning'=>1, 'str'=>JTEXT::_('MOD_QLFORM_ADMINMESSAGENOSENDER'));
            if (0==$recipient)$this->arrMessages[]=array('warning'=>1, 'str'=>JTEXT::_('MOD_QLFORM_ADMINMESSAGENORECIPIENT'));
            return false;
        }
        $data['user_id_from']=$sender;
        $data['user_id_to']=$recipient;
        $data['subject']=$subject;
        $data['message']=$message;
        $data['state']=0;
        $data['date_time']=date('Y-m-d H:i:s');
        $data['priority']=0;
        //print_r($data);die;
        $db->save('#__messages',$data);
        return true;
    }
    /**
     * method to prepare pretext by addingparagraphs and replace data value for placeholders
     *
     * @param string $str given as message
     * @return array $data is array containing the data
     */
    function preparePretext($str,$data)
    {
        $str.="\n\n";
        if (!isset($data) OR !is_array($data)) return $str;

        foreach ($data as $k=>$v)
        {
            if (isset($v['data'])) $str=str_replace('{*'.$k.'*}',(string)$v['data'],$str);

        }
        return $str;
    }
    /**
     * Method to check validation of e-mail address
     *
     * @param	string	$str wouldbe-email address
     * @return  bool    true on success; false on failure
     */
    public function checkEmail($str)
    {
        $obj_mailer=new modQlFormMailer();
        return $obj_mailer->checkEmail($str);

    }
}