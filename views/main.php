<div class="container-fluid">
	<h1><?php echo _("CallerID Rotation")?></h1>
	<div class="well well-info">This module is used to rotate outbound CallerID on a round-robin per-call basis. From selected extensions each call will show the next number in order (remembering where the last extension left off) then will loop back to beginning and start again.</div>
	<div class="row">
		<div class="col-sm-12">
			<div class="fpbx-container">
				<div class="display full-border">
					<form class="popover-form fpbx-submit" id="cidrotation-form" name="cidrotation" method="post" role="form">
						<div class="element-container">
							<div class="row">
								<div class="col-md-12">
									<div class="row">
										<div class="form-group">
											<div class="col-md-3">
												<label class="control-label" for="ext-list">Extensions with Rotating CallerID</label>
												<i class="fa fa-question-circle fpbx-help-icon" data-for="ext-list"></i>
											</div>
											<div class="col-md-9">
												<textarea id="ext-list" class="form-control" cols="18" rows="8" name="ext-list"><?php foreach($exts as $val){ echo $val['ext']."\n"; } ?></textarea>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<span id="ext-list-help" class="help-block fpbx-help-block">Enter extensions, one per line.</span>
								</div>
							</div>
						</div>
						<div class="element-container">
							<div class="row">
								<div class="col-md-12">
									<div class="row">
										<div class="form-group">
											<div class="col-md-3">
												<label class="control-label" for="cid-list">CallerID Numbers for Rotation</label>
												<i class="fa fa-question-circle fpbx-help-icon" data-for="cid-list"></i>
											</div>
											<div class="col-md-9">
												<textarea id="cid-list" class="form-control" cols="18" rows="24" name="cid-list"><?php foreach($cids as $val){ echo $val['cid']."\n"; } ?></textarea>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<span id="cid-list-help" class="help-block fpbx-help-block">Enter CallerID numbers to be rotated for outbound calling, one per line.</span>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
