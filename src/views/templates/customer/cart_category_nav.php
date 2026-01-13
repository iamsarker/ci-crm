<div class="card card-widget card-contacts">
	<div class="card-header">
		<h6 class="card-title mg-b-0"><i class="fa fa-cart-plus"></i>&nbsp;Categories</h6>
		<nav class="nav">

		</nav>
	</div><!-- card-header -->
	<ul class="list-group list-group-flush">
		<?php foreach ($services as $row){?>
		<li class="list-group-item <?=($type == $row['id'])?'selected-li':'';?> ">
			<a href="<?=base_url()?>cart/services/<?=$row['id']?>/<?=rawurlencode($row['group_name'])?>"><?=$row['group_name'];?></a>
		</li>
		<?php }?>
	</ul>
</div>
