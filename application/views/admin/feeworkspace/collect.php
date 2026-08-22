<?php $this->load->view('admin/feeworkspace/_header'); ?>
<div class="fw-card"><div class="fw-card-head"><h3><i class="fa fa-search"></i> Find a student</h3></div><div class="fw-card-body">
<form id="fw-student-search"><div class="fw-toolbar">
<div><label class="fw-label">Admission No., student name or phone</label><input class="form-control" id="fw-term" placeholder="Start typing student details"></div>
<div><label class="fw-label">Class</label><select class="form-control" id="fw-class"><option value="">All classes</option><?php foreach($classlist as $c){ ?><option value="<?php echo (int)$c['id']; ?>"><?php echo html_escape($c['class']); ?></option><?php } ?></select></div>
<div><label class="fw-label">Section</label><select class="form-control" id="fw-section"><option value="">All sections</option></select></div>
<button class="fw-btn fw-btn-primary" type="submit"><i class="fa fa-search"></i> Find Student</button>
</div></form></div></div>
<div class="fw-card"><div class="fw-card-head"><h3>Students <span class="fw-pill" id="fw-count">0 found</span></h3></div><div class="fw-card-body table-responsive" id="fw-results"><div class="fw-empty"><i class="fa fa-user fa-2x"></i><p>Search once, then open the student's fee account.</p></div></div></div>
<div class="fw-note"><i class="fa fa-shield"></i> The final payment still uses the protected receipt, notification and Chart of Accounts operation.</div>
<script>
(function($){
$('#fw-class').on('change',function(){var s=$('#fw-section').html('<option value="">All sections</option>'),id=this.value;if(!id)return;$.get('<?php echo site_url('sections/getByClass'); ?>',{class_id:id},function(r){$.each(typeof r==='string'?JSON.parse(r):r,function(_,x){s.append($('<option>').val(x.section_id||x.id).text(x.section));});});});
$('#fw-student-search').on('submit',function(e){e.preventDefault();var b=$(this).find('button'),box=$('#fw-results');b.prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i> Searching');box.html('<div class="fw-empty">Loading students...</div>');$.ajax({url:'<?php echo site_url('admin/feeworkspace/searchStudents'); ?>',type:'POST',dataType:'json',data:{term:$('#fw-term').val(),class_id:$('#fw-class').val(),section_id:$('#fw-section').val()}}).done(function(r){var a=r.students||[];$('#fw-count').text(a.length+' found');if(!a.length){box.html('<div class="fw-empty">No active students matched this search.</div>');return;}var h='<table class="table table-hover fw-table"><thead><tr><th>Admission No.</th><th>Student</th><th>Class</th><th>Guardian</th><th></th></tr></thead><tbody>';$.each(a,function(_,x){var name=[x.firstname,x.middlename,x.lastname].filter(Boolean).join(' ');h+='<tr><td>'+esc(x.admission_no)+'</td><td><strong>'+esc(name)+'</strong></td><td>'+esc(x.class)+' / '+esc(x.section)+'</td><td>'+esc(x.guardian_name||'')+'<br><small>'+esc(x.guardian_phone||'')+'</small></td><td class="text-right"><a class="btn btn-success btn-sm" href="<?php echo site_url('admin/feeworkspace/student'); ?>/'+x.student_session_id+'">Collect Fee <i class="fa fa-arrow-right"></i></a></td></tr>';});box.html(h+'</tbody></table>');}).fail(function(x){var m=(x.responseJSON&&x.responseJSON.message)||'Unable to search students.';box.html('<div class="alert alert-danger">'+esc(m)+'</div>');}).always(function(){b.prop('disabled',false).html('<i class="fa fa-search"></i> Find Student');});});
function esc(v){return $('<div>').text(v==null?'':v).html();}
})(jQuery);
</script>
<?php $this->load->view('admin/feeworkspace/_footer'); ?>
