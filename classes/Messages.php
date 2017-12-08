<?php
class Messages {
	public static function set($text, $type = "error") {
		if ($type == 'error') {
			$_SESSION['errorMsg'] = $text;
		} else {
			$_SESSION['successMsg'] = $text;
		}
	}

	public static function display() {
		$msgSession = "";
		if (isset($_SESSION['errorMsg'])) $msgSession = "errorMsg";
		if (isset($_SESSION['successMsg'])) $msgSession = "successMsg";
		$msgStyle = ($msgSession == "errorMsg" ? "danger" : ($msgSession == "successMsg" ? "success" : "info"));

		if ($msgSession == "") return;

		echo '<div class="alert alert-'.$msgStyle.' alert-dismissible fade in" role="alert" style="text-align: left;">';
			echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>';
			echo '</button>';
			echo '<strong>'.ucfirst($msgStyle).'!</strong> '.$_SESSION[$msgSession].'!';
		echo '</div>';
		unset($_SESSION[$msgSession]);
	}

	public static function hasError() {
		return isset($_SESSION["errorMsg"]);
	}

	public static function text() {
		if (isset($_SESSION["errorMsg"])) {
			return $_SESSION["errorMsg"];
		}
		if (isset($_SESSION["successMsg"])) {
			return $_SESSION["successMsg"];
		}
	}
	
}
