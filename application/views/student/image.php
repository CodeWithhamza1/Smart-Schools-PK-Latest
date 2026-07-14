<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1>
            <i class="fa fa-user-plus"></i> <?php echo $this->lang->line('student_information'); ?> <small><?php echo $this->lang->line('student1'); ?></small></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info" style="padding:5px;">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                        <div class="pull-right box-tools">
                            <a href="<?php echo site_url('student/exportformat') ?>">
                                <button class="btn btn-primary btn-sm"><i class="fa fa-download"></i> <?php echo $this->lang->line('dl_sample_import'); ?></button>
                            </a>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="card" style="background-color: #F5F5F5;
                                border: none;">
                            <img class="card-img-top student-profile-img-card-top"
                                 src=""
                                 alt="Card image cap" id="profileImage">

                            <div class="card-footer p-0">
                                <input class="btn text-white text-center btn-block" type="file" id="pImage" hidden>
                                <button class="btn  text-center btn-block"
                                        style=" background-color: #EEBA30;cursor: context-menu;">
                                    <p style="display: inline"
                                       class="pl-2 text-white"></p>
                                </button>
                                <button class="btn text-white text-center btn-block changeImage" style=" background-color: #AE0001;">Change
                                    Photo
                                </button>

                            </div>
                        </div>
                </div>
    </section>
</div>

<script type="text/javascript">
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                //$('#profileImage').attr('src', e.target.result);
                console.log(e.target.result);
                //uploading image on server
                var base_url = '<?php echo base_url() ?>';
                $.ajax({
                    url: base_url + "student/getImage",
                    method: 'post',
                    data: {
                        "profileImage": e.target.result
                    },
                    success: function (response) {
                        if (response.Result === true) {
                            $.toast({
                                heading: 'Success',
                                text: 'Profile Image Updated Successfully',
                                showHideTransition: 'slide',
                                icon: 'success'
                            });
                        }
                        else {
                            $.toast({
                                heading: 'Error',
                                text: 'Something Went Wrong',
                                showHideTransition: 'slide',
                                icon: 'error'
                            });
                        }
                    }
                });
            }

            reader.readAsDataURL(input.files[0]);

        }
    }

    $("#pImage").change(function(){
        readURL(this);
    });
    $('.changeImage').click( function (){
        $('#pImage').click();
    });
</script>