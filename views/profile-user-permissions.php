<table class="form-table" id="permission-field">
	<tbody>
		<tr class="form-field">
			<th scope="row"><label for="permission">Begränsa till sida </label></th>
			<td>
				<?php 
					wp_dropdown_pages([
					    'selected' 			=> get_user_meta( $user->ID, 'permission', true), 
						'name' 			    => 'permission', 
						'id' 			    => 'permission',
						'show_option_none'  => 'Ingen sida vald',
						'option_none_value'	=> false
					]); ?>
				<p class="description">Ange en sida som denna användare kan administrera. Användaren kommer även kunna lägga till undersidor.</p>
			</td>
		</tr>
	</tbody>
</table>