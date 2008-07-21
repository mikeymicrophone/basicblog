<?php
ini_set('error_reporting',E_ALL);
require_once dirname(__FILE__).'/LumineReverse.php';

$lembrar = sprintf('%d', @$_POST['lembrar']);
$prefix = '_lumine_reverse_';

if($lembrar == 1) {
	foreach($_POST as $key => $value) {
		if($key != 'acao') {
			setcookie($prefix . $key, $value, time() + 3600 * 24 * 3);
		}
	}
	setcookie('lumine_lembrar', '1', time() + 3600 * 24 * 3);
} else if($lembrar == 0 && $_SERVER['REQUEST_METHOD'] == 'POST') {
	foreach($_POST as $key => $value) {
		if($key != 'acao') {
			setcookie($prefix . $key, '', time() - 3600);
		}
	}
	setcookie('lumine_lembrar', '0', time() - 3600);

} else if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_COOKIE['lumine_lembrar']) && $_COOKIE['lumine_lembrar'] == 1) {
	foreach($_COOKIE as $key => $value) {
		if(strpos($key, $prefix) !== false && str_replace($prefix, '', $key) != 'acao') {
			$_POST[str_replace($prefix, '', $key)] = $value;
		}
	}
	if(!isset($_COOKIE[$prefix . 'password'])) {
		$_POST['password'] = '';
	}
}

if(!isset($_POST['crypt-fields'])) {
	$_POST['crypt-fields'] = '*.senha, *.password';
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>LumineReverse</title>
<style type="text/css">
<!--
td, th, input, select, textarea, body {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 11px;
}
body {
	background-color: #D4D0C8;
}
.bordaTabela {
	border: 1px solid #666666;
}
.result {
	background-color: #FFFFFF;
	height: 150px;
	width: 600px;
	margin-right: auto;
	margin-left: auto;
	overflow: auto;
	padding: 3px;
	border: 1px solid #666666;
}
-->
</style>
</head>

<body>
<form action="" method="post" enctype="multipart/form-data" name="form1" id="form1">
	<table width="600" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#FFFFFF" class="bordaTabela">
		<tr>
			<td colspan="2" align="center"><img src="lumine.gif" width="156" height="45" /></td>
		</tr>
		<tr>
			<td colspan="2" align="center"><strong>Engenharia reversa </strong></td>
		</tr>
		<tr>
			<td colspan="2" align="center">&nbsp;</td>
		</tr>
		<tr>
			<td width="316" align="right">Class-Path:</td>
			<td width="271"><input name="class-path" type="text" id="class-path" value="<?php echo @$_POST['class-path']; ?>" /></td>
		</tr>
		<tr>
			<td align="right">Host:</td>
			<td><input name="host" type="text" id="host" value="<?php echo @$_POST['host']; ?>" /></td>
		</tr>
		<tr>
			<td align="right">Banco de dados:</td>
			<td><input name="database" type="text" id="database" value="<?php echo @$_POST['database']; ?>" /></td>
		</tr>
		<tr>
			<td align="right">Dialeto:</td>
			<td><select name="dialect">
				<?php
	$dh = dir(LUMINE_INCLUDE_PATH.'adodb/drivers');
	while(($file = $dh->read()) !== false) {
		if($file != '.' && $file != '..') {
			$drv = preg_replace('@adodb-(.*?)\.inc\.php@i','$1',$file);
			echo '<option value="'.$drv.'"'.(@$_POST['dialect'] == $drv ? 'selected': '').'>'.$drv.'</option>';
		}
	}
	$dh->close();
	?>
			</select></td>
		</tr>
		<tr>
			<td align="right">Porta:</td>
			<td><input name="port" type="text" id="port" value="<?php echo @$_POST['port']; ?>" /></td>
		</tr>
		<tr>
			<td align="right">Usu&aacute;rio:</td>
			<td><input name="user" type="text" id="user" value="<?php echo @$_POST['user']; ?>" /></td>
		</tr>
		<tr>
			<td align="right">Senha:</td>
			<td><input name="password" type="text" id="password" value="<?php echo @$_POST['password']; ?>" /></td>
		</tr>
		<tr>
			<td align="right">Pacote:</td>
			<td><input name="package" type="text" id="package" value="<?php echo @$_POST['package']; ?>" /></td>
		</tr>
		<tr>
			<td align="right">Mapeamentos:</td>
			<td><input name="maps" type="text" id="maps" value="<?php echo @$_POST['maps']; ?>" /></td>
		</tr>
		<tr>
			<td align="right">Nome do arquivo de cache<br />
			<em> (em branco para n&atilde;o utilizar cache)</em></td>
			<td><input name="use-cache" type="text" id="use-cache" value="<?php echo @$_POST['use-cache']; ?>" /></td>
		</tr>
		<tr>
			<td align="right">Remover prefixo das tabelas: </td>
			<td><input name="remove_prefix" type="text" id="remove_prefix" value="<?php echo @$_POST['remove_prefix']; ?>" /></td>
		</tr>
		<tr>
			<td align="right">Tipo de arquivo de configura&ccedil;&atilde;o:</td>
			<td><select name="file-type" id="file-type">
				<option value="PHP" <?php echo @$_POST['file-type'] == 'PHP' ? 'checked' : '' ?>>PHP</option>
				<option value="XML" <?php echo @$_POST['file-type'] == 'XML' ? 'checked' : '' ?>>XML</option>
			</select></td>
		</tr>
		<tr>
			<td align="right">Senha de criptografia<br />
			<em> (deixe em branco para n&atilde;o usar):</em> </td>
			<td><input name="crypt-pass" type="text" id="crypt-pass" value="<?php echo @$_POST['crypt-pass']; ?>" /></td>
		</tr>
		<tr>
			<td align="right">Campos que ser&atilde;o criptografados<br />
			(separe por v&iacute;rgula no formato  <strong>tabela.campo </strong>ou <strong>*.campo</strong> para mudar em todas as tabelas que tenha o campo desejado): </td>
			<td><textarea name="crypt-fields" cols="40" rows="5" id="crypt-fields"><?php echo @$_POST['crypt-fields']; ?></textarea></td>
		</tr>
		<tr>
			<td align="right"> Criar controles b&aacute;sicos?</td>
			<td><input name="create-controls" type="checkbox" id="create-controls" value="1" <?php echo isset($_POST['create-controls']) ? 'checked' : '' ?> /></td>
		</tr>
		<tr>
			<td align="right">Gerar getters &amp; setters? </td>
			<td><input name="generate-accessors" type="checkbox" id="generate-accessors" value="1" <?php echo isset($_POST['generate-accessors']) ? 'checked' : '' ?> /></td>
		</tr>
		<tr>
			<td align="right">Adicionar nome do campo nas consultas? </td>
			<td><input name="join-add-database-name" type="checkbox" id="join-add-database-name" value="1" <?php echo isset($_POST['join-add-database-name']) ? 'checked' : '' ?> /></td>
		</tr>
		<tr>
			<td align="right">Lembrar dados: </td>
			<td><input name="lembrar" type="checkbox" id="lembrar" value="1" <?php echo isset($_POST['lembrar']) ? 'checked' : '' ?> /></td>
		</tr>
		<tr>
			<td align="right">&nbsp;</td>
			<td><input name="acao" type="submit" id="acao" value="Iniciar" />
				<input name="create-classes" type="hidden" id="create-classes" value="1" />
				<input name="create-maps" type="hidden" id="create-maps" value="1" />
				<input name="escape" type="hidden" id="escape" value="1" />
				<input name="empty-as-null" type="hidden" id="empty-as-null" value="1" /></td>
		</tr>
	</table>
</form>
	<?php

if(@$_POST['acao'] == 'Iniciar') {
	echo '<div class="result">';
	LumineLog::setLevel(4);
	LumineLog::setOutput();
	
	if($_POST['use-cache'] != '') {
		$_POST['use-cache'] = $_POST['class-path'] . '/' . $_POST['use-cache'];
	} else {
		unset($_POST['use-cache']);
	}
	$obj = new LumineReverse($_POST);
	$obj->doReverse();
	echo '</div>';
}

?>

</body>
</html>
