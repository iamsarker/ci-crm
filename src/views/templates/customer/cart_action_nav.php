<div class="card card-widget card-contacts mt-3">
  <div class="card-header">
    <h6 class="card-title mg-b-0"><i class="fa fa-plus"></i> &nbsp;Action</h6>
    <nav class="nav">
      
    </nav>
  </div><!-- card-header -->
  <ul class="list-group list-group-flush">
    <li class="list-group-item <?=($type == 'register')?'selected-li':'';?>">
      <a href="<?=base_url()?>cart/domain/register" class="nav-sub-link"><i data-feather="globe"></i>&nbsp;Register domain</a>
    </li>
    <li class="list-group-item <?=($type == 'transfer')?'selected-li':'';?>">
      <a href="<?=base_url()?>cart/domain/transfer" class="nav-sub-link"><i data-feather="repeat"></i>&nbsp;Transfer in a domain</a>
    </li>
    <li class="list-group-item <?=($type == 'view')?'selected-li':'';?>">
      <a href="<?=base_url()?>cart/view" class="nav-sub-link"><i data-feather="shopping-cart"></i>&nbsp;View cart</a>
    </li>
  </ul>
</div>


<div class="card card-widget card-contacts mt-3">
	<div class="card-header">
		<h6 class="card-title mg-b-0"><i class="fa fa-money"></i> &nbsp;Choose currency</h6>
		<nav class="nav">

		</nav>
	</div><!-- card-header -->
	<ul class="list-group list-group-flush">
		<li class="list-group-item">
			<select class="form-select currency">
				<?php
					$slt = getCurrencyId();
					foreach ($currency as $row){?>
					<option value="<?=$row['id']."-".$row['code']?>"  <?php echo ($slt == $row['id']) ? "selected":""?> ><?=$row['symbol'].' - '.$row['code']?></option>
				<?php }?>
			</select>
		</li>
	</ul>
</div>
