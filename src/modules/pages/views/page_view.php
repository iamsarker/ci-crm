<?php $this->load->view('templates/customer/header'); ?>

<style>
.page-content-wrapper {
	min-height: calc(100vh - 200px);
	padding: 40px 0;
	background: #f8f9fa;
}
.page-content-card {
	background: #fff;
	border-radius: 12px;
	box-shadow: 0 2px 15px rgba(0,0,0,0.08);
	padding: 40px;
}
.page-title {
	font-size: 28px;
	font-weight: 600;
	color: #1c273c;
	margin-bottom: 10px;
	padding-bottom: 15px;
	border-bottom: 2px solid #00897B;
}
.page-meta {
	font-size: 13px;
	color: #6c757d;
	margin-bottom: 25px;
}
.page-body {
	font-size: 15px;
	line-height: 1.8;
	color: #495057;
}
.page-body h1, .page-body h2, .page-body h3, .page-body h4, .page-body h5, .page-body h6 {
	color: #1c273c;
	margin-top: 25px;
	margin-bottom: 15px;
}
.page-body p {
	margin-bottom: 15px;
}
.page-body ul, .page-body ol {
	padding-left: 25px;
	margin-bottom: 15px;
}
.page-body li {
	margin-bottom: 8px;
}
.page-body a {
	color: #00897B;
}
.page-body a:hover {
	color: #00695C;
	text-decoration: underline;
}
.page-breadcrumb {
	margin-bottom: 20px;
}
.page-breadcrumb a {
	color: #00897B;
	text-decoration: none;
}
.page-breadcrumb a:hover {
	text-decoration: underline;
}
</style>

<div class="content content-fixed content-auth-alt">
	<div class="page-content-wrapper">
		<div class="container">
			<div class="row justify-content-center">
				<div class="col-lg-10 col-xl-8">

					<nav class="page-breadcrumb" aria-label="breadcrumb">
						<a href="<?=base_url()?>"><i class="fa fa-home me-1"></i> Home</a>
						<span class="mx-2">/</span>
						<span class="text-muted"><?= htmlspecialchars($page['page_title']) ?></span>
					</nav>

					<div class="page-content-card">
						<h1 class="page-title"><?= htmlspecialchars($page['page_title']) ?></h1>

						<?php if (!empty($page['updated_on'])): ?>
						<div class="page-meta">
							<i class="fa fa-calendar-alt me-1"></i> Last updated: <?= date('F d, Y', strtotime($page['updated_on'])) ?>
						</div>
						<?php endif; ?>

						<div class="page-body">
							<?= $page['page_content'] ?>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>
</div>

<?php $this->load->view('templates/customer/footer_script'); ?>
<?php $this->load->view('templates/customer/footer'); ?>
