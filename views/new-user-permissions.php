<script type="text/javascript">
	jQuery(document).ready(function($){
		$('.form-table #role').change(function () {
	        if( this.value == 'local' ) {
	        	$('#permission-field').show();
	        } else {
	        	$('#permission-field').hide();
	        }
	    });
	});
</script>

<table class="form-table" id="permission-field" style="display:none;">
	<tbody>
		<tr class="form-field">
			<th scope="row"><label for="permission">Begränsa till sida </label></th>
			<td>
				<?php wp_dropdown_pages(['name' => 'permission', 'id' => 'permission']); ?>
				<p class="description">Ange en sida som denna användare kan administrera. Användaren kommer även kunna lägga till undersidor.</p>
			</td>
		</tr>
	</tbody>
</table>