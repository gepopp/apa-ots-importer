<h1>Einstellungen</h1>
<?php
$session_errors = get_transient('ots_settings_errors');
$errors = [];
foreach ( $session_errors ?? [] as $session_error ) {
    $errors[$session_error['setting']] = $session_error['message'];
}

echo var_dump($errors);
?>


<form method="post" action="<?php echo admin_url( 'admin-post.php' ) ?>">
	<input type="hidden" name="action" value="update_apa_ots_key">
	<?php echo wp_nonce_field( 'update_apa_ots_key', 'update_apa_ots_key' ) ?>
	<table role="presentation" class="form-table">
		<tr>
			<th for="ots_api_key" scope="row">OTS API Schlüssel</th>
			<td>
				<input type="password" value="<?php echo get_option( 'apa_ots_key' ) ?>" name="ots_api_key" class="regular-text" required/>
                <p class="description">Bitte geben Sie die OTS API Schlüssel ein. Sollten Sie noch keinen Schlüssel haben, können Sie
                <a href="https://api.ots.at/api-key-anfordern/" target="_blank">hier bei der APA</a> einen anfordern.</p>
			</td>
		</tr>
		<?php $categories = get_categories(); ?>
		<tr>
			<th for="ots_api_default_category" scope="row">Standart Kategorie</th>
			<td>
				<select name="ots_api_default_category">
					<option>Bitte wählen...</option>
					<?php  foreach ($categories as $category ): ?>
						<option value="<?php echo $category->term_id ?>"
								<?php echo get_option('apa_ots_default_category') == $category->term_id ? 'selected="selected"' : '' ?>><?php echo $category->name ?></option>
					<?php endforeach; ?>
				</select>
                <?php if (array_key_exists('ots_api_default_category', $errors)): ?>
                    <p class="error-message"><?php echo $errors['ots_api_default_category'] ?></p>
                <?php endif; ?>
                <p class="description">Diese Kategorie wird beim Import bei allen Artikeln gesetzt.</p>

			</td>
		</tr>
        <?php
        $authors = get_users([
	        'fields'  => ['ID', 'display_name'],
	        'role'    => 'author',
	        'orderby' => 'display_name',
        ]);
        ?>
        <tr>
            <th for="ots_api_default_category" scope="row">Standart Autor</th>
            <td>
                <select name="ots_api_default_author">
                    <option>Der aktuell eingeloggte Nutzer</option>
					<?php  foreach ($authors as $author ): ?>
                        <option value="<?php echo $author->ID ?>"
							<?php echo get_option('apa_ots_default_author') == $author->ID ? 'selected="selected"' : '' ?>><?php echo $author->display_name ?></option>
					<?php endforeach; ?>
                </select>
	            <?php if (array_key_exists('ots_api_default_author', $errors)): ?>
                    <p class="error-message"><?php echo $errors['ots_api_default_author'] ?></p>
	            <?php endif; ?>
                <p class="description">Dieser Autor wird beim Import bei allen Artikeln gesetzt.</p>
            </td>
        </tr>
	</table>
	<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Änderungen speichern"></p>
</form>
