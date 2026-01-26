<?php $this->load->view('templates/customer/header');?>

	 <div class="content content-fixed content-wrapper" >
      <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        

        <div class="row">
          <div class="col-md-3 col-sm-12">

			  <div class="card card-widget card-contacts">
				  <div class="card-header">
					  <h6 class="card-title mg-b-0"><i class="fa fa-tags"></i>&nbsp;KB Categories</h6>
					  <nav class="nav">

					  </nav>
				  </div><!-- card-header -->
				  <ul class="list-group list-group-flush">
					  <?php
					   foreach ($cats as $cat){
					  ?>
					  <li class="list-group-item">
						  <?php echo $cat['cat_title'];?>&nbsp;<span class="badge rounded-pill bg-secondary float-right"><?=$cat['total_kb']?></span>
					  </li>
					  <?php }?>

				  </ul>
			  </div>


            <?php $this->load->view('templates/customer/support_nav');?>


        </div>



        <div class="col-md-9 col-sm-12">
			<h3>Announcements</h3>
			<hr class="mg-5" />
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb breadcrumb-style1 mg-b-0">
					<li class="breadcrumb-item"><a href="<?=base_url()?>clientarea">Portal home</a></li>
					<li class="breadcrumb-item"><a href="#">Supports</a></li>
					<li class="breadcrumb-item active"><a href="<?=base_url()?>supports/announcements">Announcements</a></li>
					<li class="breadcrumb-item active"><a href="<?=base_url()?>supports/view_announcement/<?=$details['id']?>/<?=$details['slug']?>"><?=$details['title']?></a></li>
				</ol>
			</nav>
          <div data-label="Example" class="df-example demo-table mg-t-25">

				<?= sanitize_html($details['description'] ?? '') ?>

          </div>
        </div>
      </div>
		
    </div><!-- container -->
  </div><!-- content -->


<?php $this->load->view('templates/customer/footer');?>
<?php $this->load->view('templates/customer/footer_script');?>
<script>
	<?php if ($this->session->flashdata('alert_success')) { ?>
		toastSuccess(<?= json_encode($this->session->flashdata('alert_success')) ?>);
	<?php } ?>
	<?php if ($this->session->flashdata('alert_error')) { ?>
		toastError(<?= json_encode($this->session->flashdata('alert_error')) ?>);
	<?php } ?>
</script>
