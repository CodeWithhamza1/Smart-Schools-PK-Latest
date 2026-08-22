</section></div>
<script>$(document).on('submit','.fw-process-form',function(){var f=$(this),b=f.find('[type=submit]');if(f.data('busy'))return false;f.data('busy',1).addClass('fw-processing');b.prop('disabled',true);var old=b.html();b.data('old',old).html('<i class="fa fa-spinner fa-spin"></i> Processing...');});</script>
