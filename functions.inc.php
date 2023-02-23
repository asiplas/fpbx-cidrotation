<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
/*
 *	License for all code of this FreePBX module can be found in the license file inside the module directory
 *	Copyright (c) 2023 Andrew Siplas.
 */

function cidrotation_hookGet_config($engine) {
	global $db;
	global $ext;
	global $asterisk_conf;
	global $astman;

	$context = 'sub-cidrotation';
	$mutex = 'cidrotation_mutex';

	$exts_db = $db->prepare('SELECT ext FROM cidrotation_ext ORDER BY ext ASC');
	$exts_db->execute();
	$exts = join('-', $exts_db->fetchAll(\PDO::FETCH_COLUMN));

	$cids_db = $db->prepare('SELECT cid FROM cidrotation_cid ORDER BY cid ASC');
	$cids_db->execute();
	$cids_arr = $cids_db->fetchAll(\PDO::FETCH_COLUMN);
	$cids_len = sizeof($cids_arr);
	$cids = join('-', $cids_arr);

	switch ($engine) {
		case "asterisk":
			// bail don't touch emergency or intra-company trunk cid
			$ext->add($context, 's', '', new ext_execif('$["${EMERGENCYROUTE}" = "YES" | "${INTRACOMPANYROUTE}" = "YES"]', 'Return'));

			// bail if not on list of extensions using this
			$ext->add($context, 's', '', new ext_setvar('cidrotation_ext', $exts));
			$ext->add($context, 's', '', new ext_execif('$[${FIELDNUM(cidrotation_ext,-,${AMPUSER})} = 0]', 'Return'));

			// lock mutex, get common index for round-robin, increment (wrap int32 max), unlock
			$ext->add($context, 's', '', new ext_execif('$[${LOCK('.$mutex.')}=0]',
									'Verbose', "3,Failed to acquire mutex '$mutex'"));
			$ext->add($context, 's', '', new ext_dbget('cidrotation_idx', 'cidrotation/idx'));
			$ext->add($context, 's', '', new ext_dbput('cidrotation/idx', '$[${cidrotation_idx} % '.$cids_len.' + 1]')); // CUT param below is one-indexed
			$ext->add($context, 's', '', new ext_noop('Released ${UNLOCK('.$mutex.')} mutex'));

			$ext->add($context, 's', '', new ext_setvar('cidrotation_cid_list', $cids));
			$ext->add($context, 's', '', new ext_setvar('cidrotation_cid', '${CUT(cidrotation_cid_list,-,$[${cidrotation_idx} % '.$cids_len.' + 1])}')); // CUT param is one-indexed

			$ext->add($context, 's', '', new ext_setvar('CALLERID(all)', '${cidrotation_cid}'));
			$ext->add($context, 's', '', new ext_setvar('CDR(outbound_cnam)', '${CALLERID(name)}'));
			$ext->add($context, 's', '', new ext_setvar('CDR(outbound_cnum)', '${CALLERID(num)}'));
			$ext->add($context, 's', '', new ext_noop('Set CallerID to ${CALLERID(all)}'));
			$ext->add($context, 's', '', new ext_return());

			$ext->splice('macro-dialout-trunk', 's', 'skipoutcid', new ext_gosub('1', 's', $context));
		break;
	}
}

