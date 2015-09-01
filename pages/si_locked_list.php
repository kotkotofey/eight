﻿
<form class="heads" method="POST" action="<?php echo $_SERVER['PHP_SELF']."?menu_marker=si_locked_list" ?>">
<?php
$time=time();
@$_GET['sortcolumn']=($_GET['sortcolumn'])?$_GET['sortcolumn']:"ФИО";
@$_GET['sorttype']=($_GET['sorttype'])?$_GET['sorttype']:"ASC";

// Определяем какой атрибут будем использовать в качестве формирования ФИО сотрудника
//-------------------------------------------------------------------------------------------------------------
if($USE_DISPLAY_NAME)
	$DisplayName=$DISPLAY_NAME_FIELD;
else
	$DisplayName=$LDAP_NAME_FIELD;
//-------------------------------------------------------------------------------------------------------------

$sort_order=(! empty($_GET['sort_order'])) ? $_GET['sort_order'] : 'desc';
$sort_field=(! empty($_GET['sort_field'])) ? $_GET['sort_field'] : $LDAP_CHANGED_DATE_FIELD;
?>

<div class="heads">

<?php
if($BLOCK_VIS[$menu_marker]['birthdays'])
	include("./libs/birth_locked.php");
if($BLOCK_VIS[$menu_marker]['search'])	
	include("./libs/search.php");
if($BLOCK_VIS[$menu_marker]['profile'])	
	include("./libs/profile.php");

?>

</div>

</form>

<?php

$LdapListAttrs = array($LDAP_DISTINGUISHEDNAME_FIELD, $DisplayName,
  		$LDAP_MAIL_FIELD, 
  		$LDAP_INTERNAL_PHONE_FIELD,
  		$LDAP_CITY_PHONE_FIELD,
		$LDAP_BIRTH_FIELD,
  		$LDAP_ST_DATE_VACATION_FIELD,
  		$LDAP_END_DATE_VACATION_FIELD,
  		$LDAP_TITLE_FIELD,
  		$LDAP_DEPARTMENT_FIELD,
  		$LDAP_CELL_PHONE_FIELD,
  		$LDAP_MANAGER_FIELD,
  		$LDAP_COMPUTER_FIELD,
  		$LDAP_DEPUTY_FIELD,
  		$LDAP_GUID_FIELD,
  		$LDAP_USERPRINCIPALNAME_FIELD,
  		$LDAP_CHANGED_DATE_FIELD);


// Делаем фильтр для выборки сотрудников
//-------------------------------------------------------------------------------------------------------------
$CompanyNameLdapFilter=Application::getCompanyNameLdapFilter();
if(! empty($Name))
	$SearchFilter=Application::getSearchFilter($Name, $LdapListAttrs);

//-------------------------------------------------------------------------------------------------------------	
//Получаем правильно отсортированных сотрудников с необходимыми атрибутами LDAP, учитывая настроки сортировки из конфига
$Staff=$ldap->getArray($OU,
 	"(&".$SearchFilter." ".$CompanyNameLdapFilter."(".$LDAP_CN_FIELD."=*)(|(useraccountcontrol:1.2.840.113556.1.4.803:=2)(useraccountcontrol:1.2.840.113556.1.4.803:=16)))",
	$LdapListAttrs,
  	array($sort_field, array($sort_field =>"ad_def_full_name")), $sort_order);

if(is_array($Staff))
{
	// Шапка таблицы
	//-------------------------------------------------------------------------------------------------------------
	echo "
		<table class=\"sqltable\" cellpadding=\"4\">";

	$url_vars=array('name' => $Name, 'only_bookmark' => $only_bookmark, 'bookmark_attr' => $bookmark_attr, 'bookmark_name' => $bookmark_name);

	echo Application::getCollTitle($L->l('full_name'), 
									array(
										'sort' => array(
													    'field' => $DisplayName,
													    'order' => $sort_order,
													    'sorted_field' => $sort_field,
													    'url_vars' => $url_vars
													    ),
										 ) );
	echo Application::getCollTitle($L->l('position'), 
									array(
										'sort' => array(
													    'field' => $LDAP_TITLE_FIELD,
													    'order' => $sort_order,
													    'sorted_field' => $sort_field,
													    'url_vars' => $url_vars
													    ),
										 ) );
	echo Application::getCollTitle($L->l('locked_date'), 
									array(
										'sort' => array(
													    'field' => $LDAP_CHANGED_DATE_FIELD,
													    'order' => $sort_order,
													    'sorted_field' => $sort_field,
													    'url_vars' => $url_vars
													    ),
										 ));
if(!$HIDE_ROOM_NUMBER)
	echo "<th><div>".$L->l('room_number')."</div></th>";

	echo Application::getCollTitle($L->l('birthday'), 
									array(
										'sort' => array(
													    'field' => $$LDAP_BIRTH_FIELD,
													    'order' => $sort_order,
													    'sorted_field' => $sort_field,
													    'url_vars' => $url_vars
													    ),
										 ));
	echo Application::getCollTitle($L->l('intrenal_phone'), 
									array(
										'sort' => array(
													    'field' => $LDAP_INTERNAL_PHONE_FIELD,
													    'order' => $sort_order,
													    'sorted_field' => $sort_field,
													    'url_vars' => $url_vars
													    ),
										 ) );	
	if(!$HIDE_CITY_PHONE_FIELD)
		echo Application::getCollTitle($L->l('city_phone'), 
										array(
											'sort' => array(
														    'field' => $LDAP_CITY_PHONE_FIELD,
														    'order' => $sort_order,
														    'sorted_field' => $sort_field,
														    'url_vars' => $url_vars
														    ),
											 ) );
if(!$HIDE_HOMEPHONE_FIELD)
	echo Application::getCollTitle($L->l('home_phone'), 
		array(
		'sort' => array(
			    'field' => $LDAP_HOMEPHONE_FIELD,
			    'order' => $sort_order,
			    'sorted_field' => $sort_field,
			    'url_vars' => $url_vars
						    ),
						 ) );

	if(!$HIDE_CELL_PHONE_FIELD)
		echo Application::getCollTitle($L->l('cell_phone'), 
										array(
											'sort' => array(
														    'field' => $LDAP_CELL_PHONE_FIELD,
														    'order' => $sort_order,
														    'sorted_field' => $sort_field,
														    'url_vars' => $url_vars
														    ),
											 ) );										
	echo Application::getCollTitle($L->l('email'), 
									array(
										'sort' => array(
													    'field' => $LDAP_MAIL_FIELD,
													    'order' => $sort_order,
													    'sorted_field' => $sort_field,
													    'url_vars' => $url_vars
													    ),
										 ) );	

	 		

	if(Staff::showComputerName($Login)) //Если сотрудник является администратором справочника
		echo Application::getCollTitle("Компьютер", 
										array(
											'sort' => array(
														    'field' => $LDAP_COMPUTER_FIELD,
														    'order' => $sort_order,
														    'sorted_field' => $sort_field,
														    'url_vars' => $url_vars
														    ),
											 ) );
	if($GLOBALS['XMPP_ENABLE'] && $GLOBALS['XMPP_MESSAGE_LISTS_ENABLE'] && !empty($_COOKIE['dn']))	
		echo Application::getCollTitle("");
	if($FAVOURITE_CONTACTS && $_COOKIE['dn'])
		echo Application::getCollTitle("");

	if(empty($_COOKIE['dn']) && $ENABLE_DANGEROUS_AUTH)
		echo Application::getCollTitle();
	//-------------------------------------------------------------------------------------------------------------
	


	$row=0;	// переменная, используемая для нумерации строк таблицы
	foreach($Staff[$LDAP_DISTINGUISHEDNAME_FIELD] AS $key=>$value)
	{
				
		$Vars['row_css']=($row%2) ? "even" : "odd";
		$Vars['current_login']=$Login;
		$Vars['display_name']=$DisplayName;
		$Vars['ldap_conection']=$ldap;
		$Vars['data_parent_id']=false;
		$Vars['id']=true;
		$Vars['locked_date']=true;
		if($Name!='*')
			$Vars['search_str']=$Name;
		Staff::printUserTableRow($Staff, $key, $Vars);

		$row++;
	}
	echo"</table>";	
}

?>