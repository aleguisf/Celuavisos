<?php
defined('_SECURE_') or die('Forbidden');
if(!auth_isvalid()){auth_block();};

$gpid = $_REQUEST['gpid'];
$pid = $_REQUEST['pid'];
$tid = $_REQUEST['tid'];

if ($tid = $_REQUEST['tid']) {
	if (! ($tid = dba_valid(_DB_PREF_.'_featureMsgtemplate', 'tid', $tid))) {
		auth_block();
	}
}

switch (_OP_) {
	case "list":
		$fm_name = "fm_smstemp";
		$content = "
			<h2>"._('Plantilla de mensaje')."</h2>
			<form id=$fm_name name=$fm_name action='index.php?app=main&inc=feature_msgtemplate&op=actions' method=POST>
			"._CSRF_FORM_."
			<input type=hidden name=go value=delete>
			<div class=actions_box>
			<div class=pull-left><a href='"._u('index.php?app=main&inc=feature_msgtemplate&op=add')."'>".$icon_config['add']."</a></div>
			<div class=pull-right>
				<a href='#' onClick=\"return SubmitConfirm('"._('¿Seguro que quieres eliminar los elementos seleccionados?')."', '".$fm_name."');\">".$icon_config['delete']."</a>
			</div>
			</div>
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead><tr>
				<th width=15%>"._('Código')."</th>
				<th width=40%>"._('Nombre')."</th>
				<th width=70%>"._('Mensaje')."</th>
				<th width=5%><input type=checkbox onclick=CheckUncheckAll(document.".$fm_name.")></th>
			</tr></thead>
			<tbody>";
		$db_query = "SELECT * FROM "._DB_PREF_."_featureMsgtemplate WHERE uid='".$user_config['uid']."' ORDER BY t_title";
		$db_result = dba_query($db_query);
		$i = 0;
		while ($db_row = dba_fetch_array($db_result)) {
			$tid = $db_row['tid'];
			$temp_title = $db_row['t_title'];
			$temp_text = $db_row['t_text'];
			$i++;
			$content .= "
				<tr>
					<td>$tid</td>
					<td><a href='"._u('index.php?app=main&inc=feature_msgtemplate&op=edit&tid='.$tid)."'>".$temp_title."</a></td>
					<td>$temp_text</td>
					<td><input type=checkbox name=chkid".$i."></td>
					<input type=hidden name=chkid_value".$i." value='".$db_row['tid']."'>
				</tr>";
		}
		$content .= "
			</tbody>
			</table>
			</div>
			<input type='hidden' name='item_count' value='$i'>
			</form>
			<div class=text-info>
				<p>"._('Notas')."</p>
				<ul>
					<li>#NAME# "._('será reemplazado por el nombre del destinatario.')."</li>
					<li>#NUM# "._('será reemplazado por el número de cuenta.')."</li>
				</ul>
			</div>
		";
		if ($err = TRUE) {
			_p(_dialog());
		}
		_p($content);
		break;
	case "add":
		if ($err = TRUE) {
			$content = _dialog();
		}
		$content .= "
			<h2>"._('Plantilla de mensaje')."</h2>
			<h3>"._('Añadir plantilla de mensaje')."</h3>
			<form action='index.php?app=main&inc=feature_msgtemplate&op=actions&go=add' method=POST>
			"._CSRF_FORM_."
			<table class=playsms-table>
			<tr>
				<td class=label-sizer>"._('Nombre de la plantilla')."</td><td><input type=text maxlength=100 name=t_title></td>
			</tr>
			<tr>
				<td>"._('Contenido del mensaje')."</td><td><input type=text name=t_text></td>
			</tr>	
			</table>	
			<p><input type='submit' class='button' value='"._('Guardar')."'></p>
			</form>
			"._back('index.php?app=main&inc=feature_msgtemplate&op=list');
			_p($content);
		break;
	case "edit":
		$db_query = "SELECT * FROM "._DB_PREF_."_featureMsgtemplate WHERE tid='$tid'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		if ($err = TRUE) {
			$content = _dialog();
		}
		$content .= "
			<h2>"._('Plantilla de mensaje')."</h2>
			<h3>"._('Editar plantilla de mensaje')."</h3>
			<form action='index.php?app=main&inc=feature_msgtemplate&op=actions&go=edit' method=POST>
			"._CSRF_FORM_."
			<input type=hidden name=item_count value='".$i."'>
			<input type=hidden name=tid value='".$tid."'>
			<table class=playsms-table>
			<tr>
				<td class=label-sizer>"._('Nombre de la plantilla')."</td><td><input type=text maxlength=100 name=t_title value='".$db_row['t_title']."'></td>
			</tr>
			<tr>
				<td>"._('Contenido del mensaje')."</td><td><input type=text name=t_text value='".$db_row['t_text']."'></td>
			</tr>
			</table>
			<input type='hidden' name='item_count' value='$i'>
			<p><input type='submit' class='button' value='"._('Guardar')."'></p>
			</form>
			"._back('index.php?app=main&inc=feature_msgtemplate&op=list');
		_p($content);
		break;
	case "actions":
		$go = $_REQUEST['go'];
		switch ($go) {
			case "add":
				$t_title = $_POST['t_title'];
				$t_text = $_POST['t_text'];
				if ($t_title && $t_text) {
					$db_query = "INSERT INTO "._DB_PREF_."_featureMsgtemplate (uid,t_title,t_text) VALUES ('".$user_config['uid']."','$t_title','$t_text')";
					$db_result = dba_insert_id($db_query);
					if ($db_result > 0) {
						$_SESSION['dialog']['info'][] = _('Plantilla de mensaje se ha guardado');
					} else {
						$_SESSION['dialog']['info'][] = _('Error al añadir plantilla de mensaje');
					}
				} else {
					$_SESSION['dialog']['info'][] = _('Debe llenar todos los campos');
				}
				header("Location: "._u('index.php?app=main&inc=feature_msgtemplate&op=add'));
				exit();
				break;
			case "edit":
				$t_title = $_POST['t_title'];
				$t_text = $_POST['t_text'];
				if ($t_title && $t_text) {
					$db_query = "UPDATE "._DB_PREF_."_featureMsgtemplate SET c_timestamp='".mktime()."',t_title='$t_title', t_text='$t_text' WHERE tid='$tid'";
					$db_result = dba_affected_rows($db_query);
					if ($db_result > 0) {
						$_SESSION['dialog']['info'][] = _('Plantilla de mensajes ha sido editado');
					} else {
						$_SESSION['dialog']['info'][] = _('Error al editar plantilla de mensaje');
					}
				} else {
					$_SESSION['dialog']['info'][] = _('Debe llenar todos los campos');
				}
				header("Location: "._u('index.php?app=main&inc=feature_msgtemplate&op=list'));
				exit();
				break;
			case "delete":
				$item_count = $_POST['item_count'];
				for ($i=1;$i<=$item_count;$i++) {
					$chkid[$i] = $_POST['chkid'.$i];
					$chkid_value[$i] = $_POST['chkid_value'.$i];
				}
				for ($i=1;$i<=$item_count;$i++) {
					if (($chkid[$i] == 'on') && $chkid_value[$i]) {
						$db_query = "DELETE FROM "._DB_PREF_."_featureMsgtemplate WHERE tid='".$chkid_value[$i]."'";
						$db_result = dba_affected_rows($db_query);
					}
				}
				$_SESSION['dialog']['info'][] = _('la(s) Plantilla(s) de mensaje seleccionado(s) fueron borrado(s)');
				header("Location: "._u('index.php?app=main&inc=feature_msgtemplate&op=list'));
				exit();
				break;
		}
}
