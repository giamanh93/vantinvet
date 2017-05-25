<?php
/**
 * @package		mod_qlform
 * @copyright	Copyright (C) 2013 ql.de All rights reserved.
 * @author 		Mareike Riegel mareike.riegel@ql.de
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
//jimport('joomla.application.component.modelform');

require_once(dirname(__FILE__).'/helper.php');

/*Params: Filling params to variables*/
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
$action = htmlspecialchars($params->get('action'));
$submit = htmlspecialchars($params->get('submit'));
$messageType = $params->get('messageType');
$message=$params->get('message');
$showpretext=$params->get('showpretext');
$pretext=$params->get('pretext');
$emailcloak=$params->get('emailcloak');
$location= $params->get('location');
$showDatabaseFormError=$params->get('showDatabaseFormError');
$showDatabaseexternalFormError=$params->get('showDatabaseexternalFormError');
$showCaptacha=$params->get('captcha');

$paramsDatabaseExternal=array('driver','host','user','password','database','prefix',);
foreach ($paramsDatabaseExternal as $k=>$v) $paramsDatabaseExternal[$v]=$params->get('databaseexternal'.$v);

$fieldModuleId=$params->get('fieldModuleId');
$moduleId=$module->id;

/*Xml: getting xml string from params*/
$obj_helper= new modQlFormHelper;
$str_xml = $obj_helper->transformText($params->get('xml'));
/*adding fields*/
$arrFields=array('user_id', 'user_email','article_id','article_title',);
foreach ($arrFields as $k=>$v) if (1==$params->get($v)) $obj_helper->generateArrayField($v,'hidden');

if (1==$params->get('sendcopy'))
{
	$obj_helper->generateArrayField('sendcopy','checkbox',JText::_('MOD_QLFORM_SENDCOPY_LABEL'),1);
	$obj_helper->arrFields[]=array('name'=>'sendcopy','value'=>1,'type'=>'checkbox','label'=>JText::_('MOD_QLFORM_SENDCOPY_LABEL'));
}
if (isset($obj_helper->arrFields) AND is_array($obj_helper->arrFields)) $str_xml = $obj_helper->addFieldsToXml($str_xml,$obj_helper->arrFields,'additionalFields');

/*transform xml to form object*/
$form=$obj_helper->getForm($str_xml,$moduleId);

/*initiate captcha*/
if (1==$showCaptacha) $obj_helper->initiateCaptcha($params);
if (1==$params->get('todoDatabase')) 
{
	$obj_helper->connectToDatabase();
    $checkDatabase=$obj_helper->checkDatabase($obj_helper->obj_database,$params->get('databasetable'),$str_xml,$showDatabaseFormError,$params->get('databaseaddcreated'));
}
if (1==$params->get('todoDatabaseExternal'))
{
    $obj_helper->connectToDatabase($paramsDatabaseExternal);
    $checkDatabaseExternal=$obj_helper->checkDatabase($obj_helper->obj_databaseexternal,$params->get('databaseexternaltable'),$str_xml,$showDatabaseexternalFormError,$params->get('databaseexternaladdcreated'));
}

/*validation server site*/
if 
(
    /*JabBerwOcky for anti spam*/
    isset($_POST['JabBerwOcky']) AND ''==$_POST['JabBerwOcky'] AND
    (
    (1==$fieldModuleId AND isset($_POST['moduleId']) AND $_POST['moduleId']==$moduleId AND isset($_POST['formSent']) AND 1==$_POST['formSent'] AND is_object($form))
	OR
	(0==$fieldModuleId AND isset($_POST['formSent']) AND 1==$_POST['formSent'] AND is_object($form))
    )
)
{
    $data=JFactory::getApplication()->input->getData('jform');
    if (1==$params->get('captchaadded') AND 1==$params->get('captcha') AND isset($_POST['captcha'])) $data['captcha']=$_POST['captcha'];
    $validatedForm=$obj_helper->validate($data);
    if (1==$showCaptacha) $validatedCaptcha=$obj_helper->checkCaptcha($_POST['captcha'],$module->id);
	if (1==$validatedForm AND (0==$showCaptacha OR (1==$showCaptacha AND 1==$validatedCaptcha))) $validated=true;
	else 
	{
        foreach ($data as $k=>$v) if (is_string($v))$data[$k]=strip_tags(html_entity_decode($v));
		$obj_helper->obj_form->fillForm($data);
		$validated=false;
	}
}

if (1==$params->get('addPostToForm')) 
{
    if (isset($_POST['jform'])) $array_posts['jform']=$obj_helper->subarrayToJson($_POST['jform']);
    if (isset($_POST['former'])) $array_posts['former']=$obj_helper->subarrayToJson($_POST['former']);
    if (isset($array_posts))
    {
        $array_posts=$obj_helper->stripQuotesInArrayValue($array_posts);
        $array_posts=$obj_helper->mergeSubarrays($array_posts,'former','jform');
    }
    if (isset($data) AND isset($array_posts) AND is_array($array_posts))
    {
        $data=array_merge($array_posts,$data);
    }
}

if (isset($validated) AND 1==$validated)
{
    if (1==$params->get('server_data'))
    {
        $data['server']=$obj_helper->getServerData($params->get('server_data_ip_anonymize'));
        if (1==$params->get('server_data_jsonify')) $data['server']=json_encode($data['server']);
    }
    //echo "<pre>"; print_r($data);die;
    $dataJsonified=$obj_helper->subarrayToJson($data);
    if (1==$params->get('show_data_sent'))
	{
		$obj_helper->arrMessages[]=array('str'=>'<strong>'.JText::_('MOD_QLFORM_SHOWDATASENT_LABEL').'</strong><br />'.$obj_helper->dump($data));
	}
	if (1==$params->get('todoEmail'))
	{
        $recipient=preg_split("?\n?",$params->get('emailrecipient'));
        $mailSent=array();
        foreach ($recipient as $k=>$v)
        {
            $mailSent[$k]=$obj_helper->mail($v,$params->get('emailsubject'),$dataJsonified,$params,$form,'','',$params->get('emaillabels',1));
        }
        while(list($k,$v)=each($mailSent)) if (1!=$v) unset($mailSent[$k]);
        if (count($mailSent)==count($recipient))$obj_helper->arrMessages[]=array('str'=>JText::_('MOD_QLFORM_MAIL_SENT'));
        else
        {
            $successful=count($mailSent);
            $failed=count($recipient)-count($mailSent);
            $obj_helper->arrMessages[]=array('warning'=>1, 'str'=>sprintf(JText::_('MOD_QLFORM_MAIL_SENT_ERROR_COUNT'),$successful,$failed));
        }
	}
	if (1==$params->get('todoDatabase') AND 1==$checkDatabase)
	{
        if (1==$params->get('databaseaddcreated')) $dataJsonified['created']=date("Y-m-d H:i:s");
        if (1==$params->get('showDataSavedToDatabase')) $obj_helper->arrMessages[]=array('str'=>'<strong>'.JText::_('MOD_QLFORM_SHOWDATASAVEDTODATABASE_LABEL').'</strong><br />'.$obj_helper->dump($dataJsonified,'foreachstring'));
        $obj_helper->saveToDatabase($params->get('databasetable'),$dataJsonified);
	}
    if (1==$params->get('todoDatabaseExternal') AND 1==$checkDatabaseExternal)
    {
        if (1==$params->get('databaseexternaladdcreated')) $dataJsonified['created']=date("Y-m-d H:i:s");
        if (1==$params->get('showDataSavedToDatabaseexternal')) $obj_helper->arrMessages[]=array('str'=>'<strong>'.JText::_('MOD_QLFORM_SHOWDATASAVEDTODATABASE_LABEL').'</strong><br />'.$obj_helper->dump($dataJsonified,'foreachstring'));
        $obj_helper->saveToDatabase($params->get('databaseexternaltable'),$dataJsonified,$paramsDatabaseExternal);
    }
	if (1==$params->get('todoSomethingElse'))
	{
		$obj_helper->doSomethingElse($data,$params,$module,$form);
	}
    if (1==$params->get('todoSomethingCompletelyDifferent'))
    {
        $obj_helper->doSomethingCompletelyDifferent($data,$params,$module,$form);
    }
    if (1==$params->get('todoSendAdminmessage'))
    {
        $obj_helper->sendAdminmessage($params->get('adminmessagerecipient',0),$params->get('adminmessagesender',0),$params->get('adminmessagesubject'),$dataJsonified,$params->get('adminmessagetemplateoverride',0));
    }
    if (1==$params->get('sendcopy') AND isset($_POST['jform']['sendcopy']) AND 1==$_POST['jform']['sendcopy'] AND !empty($data[$params->get('sendcopyfieldname')]))
    {
        $dataWithoutServer=$data;
        if (isset($dataWithoutServer['server'])) unset($dataWithoutServer['server']);
        //echo '<pre>'; print_r($data);print_r($dataWithoutServer); echo '</pre>';
        $dataJsonifiedWithoutServerData=$obj_helper->subarrayToJson($dataWithoutServer);
        $obj_helper->mail($data[$params->get('sendcopyfieldname')],'Copy: '.$params->get('emailsubject'),$dataJsonifiedWithoutServerData,$params,$form,$params->get('sendcopyemailreplyto'),$params->get('sendcopypretext'),$params->get('sendcopylabels',1));
    }
	if (1==$params->get('locationbool') AND !empty($location)) { header('location:'.$location); exit;}
    $obj_helper->arrMessages[]=array('str'=>$message);
}
//else echo "validation failed"; die;

/*Display messages*/
if (isset($obj_helper->arrMessages) AND is_array($obj_helper->arrMessages)) $messages=$obj_helper->displayMessages($messageType);

/*Captcha: initiale captcha*/
if (1==$showCaptacha) 
{
	$captcha=$obj_helper->generateCaptcha($moduleId);
    $image=$obj_helper->captcha;
}

require JModuleHelper::getLayoutPath('mod_qlform', $params->get('layout', 'default'));