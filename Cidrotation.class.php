<?php
namespace FreePBX\modules;

class Cidrotation extends \FreePBX_Helpers implements \BMO {
	public function __construct($freepbx = null) {
		if ($freepbx == null) {
			throw new Exception("Not given a FreePBX Object");
		}
		$this->FreePBX = $freepbx;
		$this->db = $freepbx->Database;
	}
	public function install() {
		$this->astman->database_put('cidrotation', 'idx', '1');
	}
	public function uninstall() {
		$this->astman->database_deltree('cidrotation');
	}
	//Not yet implemented
	public function backup() {}
	//not yet implimented
	public function restore($backup) {}
	//process form
	public function doConfigPageInit($page) {
		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			$exts = explode("\r\n", trim($this->getReq('ext-list')));
			$this->db->beginTransaction();
			$exts_db = $this->db->prepare('DELETE FROM cidrotation_ext');
			$exts_db->execute();
			$exts_db = $this->db->prepare('INSERT INTO cidrotation_ext(ext) VALUES(:ext)');
			$added = array();
			foreach($exts as $ext) {
				$ext = preg_replace("/[^0-9]/", "", $ext);
				if (strlen($ext) < 1 || in_array($ext, $added)) {
					continue;
				}
				$exts_db->execute(array('ext' => $ext));
				$added[] = $ext;
			}

			$cids = explode("\r\n", trim($this->getReq('cid-list')));
			$cids_db = $this->db->prepare('DELETE FROM cidrotation_cid');
			$cids_db->execute();
			$cids_db = $this->db->prepare('INSERT INTO cidrotation_cid(cid) VALUES(:cid)');
			$added = array();
			$id = 0;
			foreach($cids as $cid) {
				$cid = preg_replace("/[^0-9]/", "", $cid);
				if (strlen($cid) < 1 || in_array($cid, $added)) {
					continue;
				}
				$cids_db->execute(array('cid' => $cid));
				$added[] = $cid;
			}
			$this->db->commit();
			needreload();
		}
	}
	//This shows the submit buttons
	public function getActionBar($request) {
		$buttons = array();
		switch($_GET['display']) {
			case 'cidrotation':
				$buttons = array(
					'reset' => array(
						'name' => 'reset',
						'id' => 'reset',
						'value' => _('Reset')
					),
					'submit' => array(
						'name' => 'submit',
						'id' => 'submit',
						'value' => _('Submit')
					)
				);
			break;
		}
		return $buttons;
	}
	public function getVars(){
		$exts_db = $this->db->prepare('SELECT ext FROM cidrotation_ext');
		$exts_db->execute();
		$exts = $exts_db->fetchAll(\PDO::FETCH_ASSOC);

		$cids_db = $this->db->prepare('SELECT cid FROM cidrotation_cid');
		$cids_db->execute();
		$cids = $cids_db->fetchAll(\PDO::FETCH_ASSOC);

		return array(
			'exts' => $exts,
			'cids' => $cids
		);
	}
	public function showPage(){
		$vars = $this->getVars();
		return load_view(__DIR__.'/views/main.php',$vars);
	}
	public static function myGuiHooks() {
		return array('core');
	}
	public function doGuiHook(&$cc) {
		if ($this->getReq('display') == "extensions") {
			$ext = $this->getReq('extdisplay');
			if (!$ext) {
				return;
			}
			$exts_db = $this->db->prepare('SELECT ext FROM cidrotation_ext ORDER BY ext ASC');
			$exts_db->execute();
			$active_exts = $exts_db->fetchAll(\PDO::FETCH_COLUMN);
			if (in_array($ext, $active_exts)) {
				$cc->addguielem("_top", new \gui_html('', '<div class="alert alert-warning"><h2>CallerID on this page is not effective!</h2>Extension '.$ext.' is using the <a href="/admin/config.php?display=cidrotation">CallerID Rotation</a> module which overrides ID for all outside calls <strong>except</strong> emergency and intra-company trunk calls.</div>', false));
				$cc->addguielem("_top", new \gui_html('', '<script>$(document).ready(()=>{
					const cid = $("#outboundcid");
					warnInvalid(cid, "Disable CALLER*ID ROTATION to use CID value on this page.", "warning");
				});</script>', false));
			}
		}
	}
}
