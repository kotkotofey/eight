<?php
require_once('../libs/MPDF60/mpdf.php');
require_once('../config.php');
require_once("../libs/forms.php");
require_once("../libs/staff.php");
require_once("../libs/phones.php");
require_once("../libs/pdf.php");
if($ENABLE_PDF_EXPORT)
	{
	Application::makeLdapConfigAttrLowercase();
	$menu_marker="si_export_pdf_department";
	@$BOOKMARK_NAME=($_POST['bookmark_name'])?$_POST['bookmark_name']:(($_GET['bookmark_name'])?$_GET['bookmark_name']:current(array_keys($BOOKMARK_NAMES[current(array_keys($BOOKMARK_NAMES))])) );
	@$bookmark_attr=($_POST['bookmark_attr'])?$_POST['bookmark_attr']:(($_GET['bookmark_attr'])?$_GET['bookmark_attr']:current(array_keys($BOOKMARK_NAMES)));

	$html.=PDF::get_pdf_head();
//создается таблица
	$html.="
	<table cellpadding='0' border='0' cellspacing='0' class='staff'>
	";

	$ldap=new LDAP($LDAPServer, $LDAPUser, $LDAPPassword);
	$CompanyNameLdapFilter=Application::getCompanyNameLdapFilter();
		
	if($USE_DISPLAY_NAME)
		$DisplayName=$DISPLAY_NAME_FIELD;
	else
		$DisplayName=$LDAP_NAME_FIELD;	
		
	$Staff=$ldap->getArray($OU, "(&".$CompanyNameLdapFilter."(".$LDAP_CN_FIELD."=*)".$DIS_USERS_COND.")", array($DisplayName, $LDAP_MAIL_FIELD, $LDAP_INTERNAL_PHONE_FIELD, $LDAP_CITY_PHONE_FIELD, $LDAP_TITLE_FIELD, $LDAP_DEPARTMENT_FIELD, $LDAP_CELL_PHONE_FIELD, $LDAP_MANAGER_FIELD, $LDAP_BIRTH_FIELD,$LDAP_HOMEPHONE_FIELD), array($LDAP_DEPARTMENT_FIELD, $DEP_SORT_ORDER, $LDAP_TITLE_FIELD, $STAFF_SORT_ORDER, $DisplayName));
//$LDAP_BIRTH_FIELD
	if(is_array($Staff))
		{
		$SizeOf=sizeof($Staff[$DisplayName]);	

		for($i=0; $i<$SizeOf; $i++)
			{
			if(!($PDF_HIDE_STAFF_WITHOUT_PHONES&&(!$Staff[$LDAP_INTERNAL_PHONE_FIELD][$i])&&(!$Staff[$HIDE_CITY_PHONE_FIELD][$i])&&(!$Staff[$LDAP_CELL_PHONE_FIELD][$i])))
				{
				$n=$i+1;
						
				$FIO=explode(" ", $Staff[$DisplayName][$i]);	
				
				$Surname=$Staff[$DisplayName][$i];
				$Name="";
				$Patronymic="";
				
				if(preg_match("/[ІЇЄЁA-ZА-Я]{1}[`іїєёa-zа-я-]+[\s]{1}[ІЇЄЁA-ZА-Я]{1}[іїє`ёa-zа-я-]+[\s]{1}[ІЇЄЁA-ZА-Я]{1}[ії`єёa-zа-я-]+/u", $Staff[$DisplayName][$i]))
					{
					$Surname=$FIO[0];
					$Name=$FIO[1];
					$Patronymic=$FIO[2];
					}
				if(preg_match("/[ІЇЄЁA-ZА-Я]{1}[іїєёa-zа-я-]+[\s]{1}[ЁA-ZА-Я]{1}[.]{1}[\s]{1}[ІЇЄЁA-ZА-Я]{1}[іїєёa-zа-я-]+/u", $Staff[$DisplayName][$i]))
					{
					$Surname=$FIO[2];
					$Name=$FIO[0];
					$Patronymic=$FIO[1];			
					}		
				
				$Department=$Staff[$LDAP_DEPARTMENT_FIELD][$i]; 
				$colspan=Staff::getNumStaffTableColls();

				if($PrevDepartment!=$Department)
					{
					if(strpos($Department, $InclusionDep)===0)
							$css="department";
						else
							{
							$css="division";
							$InclusionDep=$Department;
							}
/////	<span id=\"dep_".$row."\">".Staff::makeDepartment($Staff[$LDAP_DEPARTMENT_FIELD][$key], true)."</span>					
//					$html.="<tr><td colspan=\"".$colspan."\" class=\"department\"><div class=\"".$css."\">".Staff::makeDepartment($Department)."</div><img src=\"../skins/".$CURRENT_SKIN."/images/pdf/pixel_black.png\" vspace=\"1\" width=\"100%\" height=\"1px\"></td></tr>";
					$html.="<tr><td colspan=\"".$colspan."\" class=\"department\"><div class=\"".$css."\">".Staff::makeDepartment($Department)."</div><img src=\"../skins/".$CURRENT_SKIN."/images/pdf/pixel_black.png\" vspace=\"1\" width=\"100%\" height=\"1px\"></td></tr>";

					$PrevDepartment=$Department;
					}
				else
					$html.="<tr><td colspan=\"".$colspan."\"><img src=\"../skins/".$CURRENT_SKIN."/images/pdf/divider.gif\" vspace=\"0\" width=\"100%\" height=\"1\"></td></tr>";
///печатается таблица справочника, первое -имя
				$html.="<tr>
				<td class=\"name\"><span class=\"surname\">".$Surname."</span><br><span class=\"patronymic\">".$Name." ".$Patronymic."</span></td>";
///теперь посада
				$html.="
				<td class=\"position\">".Staff::makeTitle($Staff[$LDAP_TITLE_FIELD][$i])."
				</tr>";

//				$html.="
//				<td class=\"position\">".Staff::makeTitle($Staff[$LDAP_TITLE_FIELD][$i])."</td>
//				</tr>";
				$html.="
				<td class=\"birth\">".Staff::makeBirth($Staff[$LDAP_BIRTH_FIELD][$i])."</td>
				</tr>";
$html.="
			<td class=\"internal_phone\">".Staff::makeInternalPhone($Staff[$LDAP_INTERNAL_PHONE_FIELD][$i], false)."</td>
				</tr>";

				if(!$HIDE_CITY_PHONE_FIELD)		
					$html.="<td class=\"city_phone\">".Staff::makeCityPhone($Staff[$LDAP_CITY_PHONE_FIELD][$i], false)."</td>";

				if(!$HIDE_HOMEPHONE_FIELD)		
					$html.="<td class=\"home_phone\">".Staff::makeCityPhone($Staff[$LDAP_HOMEPHONE_FIELD][$i], false)."</td>";

				if(!$HIDE_CELL_PHONE_FIELD)	
					$html.="<td class=\"cell_phone\">".Staff::makeCellPhone($Staff[$LDAP_CELL_PHONE_FIELD][$i], false)."</td>";


////// $username = preg_replace( "/^([^@]+)(@.*)$/", "$1", $LDAP_MAIL_FIELD);
/////        print "<b>Found username \"$username\"</b>";


$parts = explode("@", $Staff[$LDAP_MAIL_FIELD][$i]);///добавлено для переноса емайлов при печати в пдф
if (count($parts) == 2)
{
$usernamemail = $parts[0] ."<br/>@" . $parts[1];
}
else
{
$usernamemail = "x";
}
////дальше идет вівод емайла в лист				
				$html.="
				<td class=\"mail\">$usernamemail </td>
				</tr>
				";
//оригинал				$html.="
//				<td class=\"mail\">".$Staff[$LDAP_MAIL_FIELD][$i]."</td>
//				</tr>
//				";




				}
			}
		}

	$html.="</table>";

	$mpdf=new mPDF(false, $PDF_LANDSCAPE?"A4-L":"A4", false, 'Arial', $PDF_MARGIN_LEFT, $PDF_MARGIN_RIGHT, $PDF_MARGIN_TOP, $PDF_MARGIN_BOTTOM);

	$stylesheet = file_get_contents("../skins/".$CURRENT_SKIN."/css/pdf.css");
	$mpdf->WriteHTML($stylesheet, 1);

	$mpdf->WriteHTML($html, 2);
	$mpdf->Output('pdf_departments.pdf', 'I');
	}
?>