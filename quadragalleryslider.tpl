<div id="quadragalleryslider">
	<script type="text/javascript" src="{$base_dir}modules/quadragalleryslider/class.horinaja.query.js"></script>
	<link rel="stylesheet" type="text/css" href="{$base_dir}modules/quadragalleryslider/horinaja.css" />

         <!--FancyBox -->
         <script type="text/javascript" src="{$base_dir}js/jquery/jquery.easing.1.3.js"></script>

	<script>
	  $(document).ready(function(){
		$('#homeslide').Horinaja({
		capture:'homeslide',delai:0.9,
		duree:4,pagination:true});
		});
		
	</script>

		<div id="homeslide" class="horinaja" {if $v_display eq 1}style="height:{$box_height}px;"{else} {if $thumbs neq ""} style="height:{$image_height+$v_height*3/2}px;"{/if} {/if}>
			<!-- div id="big_picture" style="display:none;"></div-->
			<ul id="" class="">
				{foreach from=$thumbs key=idx item=curThumb}
					<li {if $v_display eq 1} style="margin-top:{($box_height-$image_height)/2}px;"{else}style="margin-top:{$v_height}px;margin-bottom:{$v_height}px;"{/if}>
		                <a title="{$titles[$idx]|escape:'htmlall':'UTF-8'|stripslashes}" href="{$links[$idx]}" id="big_img_{$idx}">
		                	<img src="{$imgs[$idx]}" class="detail_big_image" alt="" id="bigpic"/>
		                </a>
					</li>
				{/foreach}
			</ul>
			<ol class="horinaja_pagination" id="horinaja_pag" {if $v_display eq 1}style="width:100px;"{/if}>
				{foreach from=$thumbs key=idx item=curThumb}
					<li style="height:{$v_height}px;">
		                <a title="{$titles[$idx]|escape:'htmlall':'UTF-8'|stripslashes}" id="small_img_{$idx}" rel="minigallery">
		                	<img src="{$curThumb}" class="detail_image" alt="" id="smallpic"/>
		                </a><br/>
					</li>
				{/foreach}
			</ol>
		</div>
		<div class="clear"></div>
	<!-- /div-->
	<div id="description"></div>
</div>

