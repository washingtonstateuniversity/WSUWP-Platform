/**
 * Manage portions of the user add and user edit screens to enforce our password and
 * authentication policies.
 *
 * Props to https://github.com/gyrus/Force-Strong-Passwords for the approach used on capturing password strength.
 */
(function($){
	var pass_strength = $('#pass-strength-result' ),
		role = $('#role');

	/**
	 * Pass password strength information along with the form submission when changing passwords.
	 */
	pass_strength.parents('form' ).on('submit', function() {
		$(this ).append('<input type="hidden" name="wsuwp_pass_strength" value="' + pass_strength.text() + '" />' );
	});

	/**
	 * Add warning text when changing roles in a user edit screen.
	 */
	role.on('change', function() {
		var new_role = jQuery('#role option:selected' ).text();

		if ( 'Administrator' === new_role ) {
			role.parent().append('<p id="wsuwp-role-helper" class="description indicator-hint"><strong>Warning:</strong> Selecting a role of Administrator will reset any current password for this user and prevent future basic authentication. Administrators are required to use WSU Network ID authentication only.</p>');
		} else {
			$('#wsuwp-role-helper' ).remove();
		}
	});
}(jQuery));