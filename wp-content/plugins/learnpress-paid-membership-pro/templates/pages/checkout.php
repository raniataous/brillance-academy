<?php
/**
 * Template: Checkout
 * Version: 2.0.2
 *
 * @version 2.0.2
 *
 * @author Paid Memberships Pro
 */
global $gateway, $pmpro_review, $skip_account_fields, $pmpro_paypal_token, $wpdb, $current_user, $pmpro_msg, $pmpro_msgt, $pmpro_requirebilling, $pmpro_level, $pmpro_levels, $tospage, $pmpro_show_discount_code, $pmpro_error_fields;
global $discount_code, $username, $password, $password2, $bfirstname, $blastname, $baddress1, $baddress2, $bcity, $bstate, $bzipcode, $bcountry, $bphone, $bemail, $bconfirmemail, $CardType, $AccountNumber, $ExpirationMonth, $ExpirationYear;
/**
 * Filter to set if PMPro uses email or text as the type for email field inputs.
 *
 * @param bool $use_email_type , true to use email type, false to use text type
 *
 * @since 1.8.4.5
 *
 */
$pmpro_email_field_type = apply_filters( 'pmpro_email_field_type', true );
?>

<div id="pmpro_level-<?php echo $pmpro_level->id; ?>" class="pmpro_level-<?php echo $pmpro_level->id; ?>">
	<form id="pmpro_form" class="pmpro_form" action="
	<?php
	if ( ! empty( $_REQUEST['review'] ) ) {
		echo pmpro_url( 'checkout', '?level=' . $pmpro_level->id );
	}
	?>
	" method="post">

		<input type="hidden" id="level" name="level" value="<?php echo esc_attr( $pmpro_level->id ); ?>"/>
		<input type="hidden" id="checkjavascript" name="checkjavascript" value="1"/>

		<?php if ( $pmpro_msg ) : ?>
			<div id="pmpro_message" class="pmpro_message <?php echo $pmpro_msgt; ?>"><?php echo $pmpro_msg; ?></div>
		<?php else : ?>
			<div id="pmpro_message" class="pmpro_message" style="display: none;"></div>
		<?php endif; ?>

		<?php if ( $pmpro_review ) : ?>
			<p><?php _e( 'Almost done. Review the membership information and pricing below then <strong>click the "Complete Payment" button</strong> to finish your order.', 'learnpress-paid-membership-pro' ); ?></p>
		<?php endif; ?>

		<table id="pmpro_pricing_fields" class="pmpro_checkout" width="100%" cellpadding="0" cellspacing="0" border="0">
			<thead>
			<tr>
				<th>
					<span
						class="pmpro_thead-name"><?php esc_html_e( 'Membership Level', 'learnpress-paid-membership-pro' ); ?></span>
					<?php if ( count( $pmpro_levels ) > 1 ) : ?>
						<span class="pmpro_thead-msg">
								<a href="<?php echo pmpro_url( 'levels' ); ?>"><?php _e( 'change', 'learnpress-paid-membership-pro' ); ?></a>
							</span>
					<?php endif; ?>
				</th>
			</tr>
			</thead>

			<tbody>
			<tr>
				<td>
					<p>
						<?php printf( __( 'You have selected the <strong>%s</strong> membership level.', 'learnpress-paid-membership-pro' ), $pmpro_level->name ); ?>
					</p>

					<?php
					/**
					 * All devs to filter the level description at checkout.
					 * We also have a function in includes/filters.php that applies the the_content filters to this description.
					 *
					 * @param string $description The level description.
					 * @param object $pmpro_level The PMPro Level object.
					 */
					$level_description = apply_filters( 'pmpro_level_description', $pmpro_level->description, $pmpro_level );

					if ( ! empty( $level_description ) ) {
						echo $level_description;
					}
					?>

					<div id="pmpro_level_cost">
						<?php if ( $discount_code && pmpro_checkDiscountCode( $discount_code ) ) : ?>
							<?php printf( __( '<p class="pmpro_level_discount_applied">The <strong>%s</strong> code has been applied to your order.</p>', 'learnpress-paid-membership-pro' ), $discount_code ); ?>
						<?php endif; ?>
						<?php echo wpautop( pmpro_getLevelCost( $pmpro_level ) ); ?>
						<?php echo wpautop( pmpro_getLevelExpiration( $pmpro_level ) ); ?>
					</div>

					<?php do_action( 'pmpro_checkout_after_level_cost' ); ?>

					<?php if ( $pmpro_show_discount_code ) { ?>
						<?php if ( $discount_code && ! $pmpro_review ) { ?>
							<p id="other_discount_code_p" class="pmpro_small">
								<a id="other_discount_code_a"
								   href="#discount_code"><?php esc_html_e( 'Click here to change your discount code', 'learnpress-paid-membership-pro' ); ?></a>.
							</p>
						<?php } elseif ( ! $pmpro_review ) { ?>
							<p id="other_discount_code_p"
							   class="pmpro_small"><?php esc_html_e( 'Do you have a discount code?', 'learnpress-paid-membership-pro' ); ?>
								<a id="other_discount_code_a"
								   href="#discount_code"><?php esc_html_e( 'Click here to enter your discount code', 'learnpress-paid-membership-pro' ); ?></a>.
							</p>
						<?php } elseif ( $pmpro_review && $discount_code ) { ?>
							<p>
								<strong><?php esc_html_e( 'Discount Code', 'learnpress-paid-membership-pro' ); ?>
									:</strong> <?php echo $discount_code; ?>
							</p>
						<?php } ?>

					<?php } ?>
				</td>
			</tr>
			<?php if ( $pmpro_show_discount_code ) : ?>
				<tr id="other_discount_code_tr" style="display: none;">
					<td>
						<div>
							<label
								for="other_discount_code"><?php esc_html_e( 'Discount Code', 'learnpress-paid-membership-pro' ); ?></label>
							<input id="other_discount_code" name="other_discount_code" type="text"
								   class="input <?php echo pmpro_getClassForField( 'other_discount_code' ); ?>"
								   size="20" value="<?php echo esc_attr( $discount_code ); ?>"/>
							<input type="button" name="other_discount_code_button" id="other_discount_code_button"
								   value="<?php esc_html_e( 'Apply', 'learnpress-paid-membership-pro' ); ?>"/>
						</div>
					</td>
				</tr>
			<?php endif; ?>
			</tbody>
		</table>

		<?php if ( $pmpro_show_discount_code ) { ?>
			<script>
				<!--
				//update discount code link to show field at top of form
				jQuery('#other_discount_code_a').attr('href', 'javascript:void(0);');
				jQuery('#other_discount_code_a').on('click', function () {
					jQuery('#other_discount_code_tr').show();
					jQuery('#other_discount_code_p').hide();
					jQuery('#other_discount_code').trigger('focus');
				});

				//update real discount code field as the other discount code field is updated
				jQuery('#other_discount_code').on('keyup', function () {
					jQuery('#discount_code').val(jQuery('#other_discount_code').val());
				});
				jQuery('#other_discount_code').on('blur', function () {
					jQuery('#discount_code').val(jQuery('#other_discount_code').val());
				});

				//update other discount code field as the real discount code field is updated
				jQuery('#discount_code').on('keyup', function () {
					jQuery('#other_discount_code').val(jQuery('#discount_code').val());
				});
				jQuery('#discount_code').on('blur', function () {
					jQuery('#other_discount_code').val(jQuery('#discount_code').val());
				});

				//applying a discount code
				jQuery('#other_discount_code_button').on('click', function () {
					var code = jQuery('#other_discount_code').val();
					var level_id = jQuery('#level').val();

					if (code) {
						//hide any previous message
						jQuery('.pmpro_discount_code_msg').hide();

						//disable the apply button
						jQuery('#other_discount_code_button').attr('disabled', 'disabled');

						jQuery.ajax({
							url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
							type: 'GET',
							timeout:<?php echo apply_filters( 'pmpro_ajax_timeout', 5000, 'applydiscountcode' ); ?>,
							dataType: 'html',
							data: "action=applydiscountcode&code=" + code + "&level=" + level_id + "&msgfield=pmpro_message",
							error: function (xml) {
								alert('Error applying discount code [1]');

								//enable apply button
								jQuery('#other_discount_code_button').removeAttr('disabled');
							},
							success: function (responseHTML) {
								if (responseHTML == 'error') {
									alert('Error applying discount code [2]');
								} else {
									jQuery('#pmpro_message').html(responseHTML);
								}

								//enable invite button
								jQuery('#other_discount_code_button').removeAttr('disabled');
							}
						});
					}
				});
				-->
			</script>
		<?php } ?>

		<?php
		do_action( 'pmpro_checkout_after_pricing_fields' );
		?>

		<?php if ( ! $skip_account_fields && ! $pmpro_review ) { ?>
			<table id="pmpro_user_fields" class="pmpro_checkout" width="100%" cellpadding="0" cellspacing="0"
				   border="0">
				<thead>
				<tr>
					<th>
						<span
							class="pmpro_thead-name"><?php esc_html_e( 'Account Information', 'learnpress-paid-membership-pro' ); ?></span>
						<span
							class="pmpro_thead-msg"><?php esc_html_e( 'Already have an account?', 'learnpress-paid-membership-pro' ); ?>
								<a href="<?php echo wp_login_url( pmpro_url( 'checkout', '?level=' . $pmpro_level->id ) ); ?>"><?php esc_html_e( 'Log in here', 'learnpress-paid-membership-pro' ); ?></a></span>
					</th>
				</tr>
				</thead>

				<tbody>
				<tr>
					<td>
						<div>
							<label
								for="username"><?php esc_html_e( 'Username', 'learnpress-paid-membership-pro' ); ?></label>
							<input id="username" name="username" type="text"
								   class="input <?php echo pmpro_getClassForField( 'username' ); ?>" size="30"
								   value="<?php echo esc_attr( $username ); ?>"/>
						</div>

						<?php
						do_action( 'pmpro_checkout_after_username' );
						?>

						<div>
							<label
								for="password"><?php esc_html_e( 'Password', 'learnpress-paid-membership-pro' ); ?></label>
							<input id="password" name="password" type="password"
								   class="input <?php echo pmpro_getClassForField( 'password' ); ?>" size="30"
								   value="<?php echo esc_attr( $password ); ?>"/>
						</div>
						<?php
						$pmpro_checkout_confirm_password = apply_filters( 'pmpro_checkout_confirm_password', true );
						if ( $pmpro_checkout_confirm_password ) {
							?>
							<div>
								<label
									for="password2"><?php esc_html_e( 'Confirm Password', 'learnpress-paid-membership-pro' ); ?></label>
								<input id="password2" name="password2" type="password"
									   class="input <?php echo pmpro_getClassForField( 'password2' ); ?>" size="30"
									   value="<?php echo esc_attr( $password2 ); ?>"/>
							</div>
							<?php
						} else {
							?>
							<input type="hidden" name="password2_copy" value="1"/>
							<?php
						}
						?>

						<?php
						do_action( 'pmpro_checkout_after_password' );
						?>

						<div>
							<label
								for="bemail"><?php esc_html_e( 'E-mail Address', 'learnpress-paid-membership-pro' ); ?></label>
							<input id="bemail" name="bemail"
								   type="<?php echo( $pmpro_email_field_type ? 'email' : 'text' ); ?>"
								   class="input <?php echo pmpro_getClassForField( 'bemail' ); ?>" size="30"
								   value="<?php echo esc_attr( $bemail ); ?>"/>
						</div>
						<?php
						$pmpro_checkout_confirm_email = apply_filters( 'pmpro_checkout_confirm_email', true );
						if ( $pmpro_checkout_confirm_email ) {
							?>
							<div>
								<label
									for="bconfirmemail"><?php esc_html_e( 'Confirm E-mail Address', 'learnpress-paid-membership-pro' ); ?></label>
								<input id="bconfirmemail" name="bconfirmemail"
									   type="<?php echo( $pmpro_email_field_type ? 'email' : 'text' ); ?>"
									   class="input <?php echo pmpro_getClassForField( 'bconfirmemail' ); ?>" size="30"
									   value="<?php echo esc_attr( $bconfirmemail ); ?>"/>

							</div>
							<?php
						} else {
							?>
							<input type="hidden" name="bconfirmemail_copy" value="1"/>
							<?php
						}
						?>

						<?php do_action( 'pmpro_checkout_after_email' ); ?>

						<div class="pmpro_hidden">
							<label
								for="fullname"><?php esc_html_e( 'Full Name', 'learnpress-paid-membership-pro' ); ?></label>
							<input id="fullname" name="fullname" type="text"
								   class="input <?php echo pmpro_getClassForField( 'fullname' ); ?>" size="30"
								   value=""/>
							<strong><?php esc_html_e( 'LEAVE THIS BLANK', 'learnpress-paid-membership-pro' ); ?></strong>
						</div>
					</td>
				</tr>
				</tbody>
			</table>
		<?php } elseif ( $current_user->ID && ! $pmpro_review ) { ?>
			<p id="pmpro_account_loggedin">
				<?php printf( __( 'You are logged in as <strong>%1$s</strong>. If you would like to use a different account for this membership, <a href="%2$s">log out now</a>.', 'learnpress-paid-membership-pro' ), $current_user->user_login, wp_logout_url( $_SERVER['REQUEST_URI'] ) ); ?>
			</p>
		<?php } ?>

		<?php do_action( 'pmpro_checkout_after_user_fields' ); ?>

		<?php do_action( 'pmpro_checkout_boxes' ); ?>

		<?php if ( pmpro_getGateway() == 'paypal' && empty( $pmpro_review ) && true == apply_filters( 'pmpro_include_payment_option_for_paypal', true ) ) { ?>
			<table id="pmpro_payment_method" class="pmpro_checkout top1em" width="100%" cellpadding="0" cellspacing="0"
				   border="0"
				<?php if ( ! $pmpro_requirebilling ) { ?>
					style="display: none;"<?php } ?>>
				<thead>
				<tr>
					<th><?php esc_html_e( 'Choose your Payment Method', 'learnpress-paid-membership-pro' ); ?></th>
				</tr>
				</thead>

				<tbody>
				<tr>
					<td>
						<div>
						<span class="gateway_paypal">
							<input type="radio" name="gateway" value="paypal"
							<?php
							if ( ! $gateway || $gateway == 'paypal' ) {
								?>
								checked="checked"<?php } ?> />
							<a href="javascript:void(0);"
							   class="pmpro_radio"><?php esc_html_e( 'Check Out with a Credit Card Here', 'learnpress-paid-membership-pro' ); ?></a>
						</span>
							<span class="gateway_paypalexpress">
							<input type="radio" name="gateway" value="paypalexpress"
							<?php
							if ( $gateway == 'paypalexpress' ) {
								?>
								checked="checked"<?php } ?> />
							<a href="javascript:void(0);"
							   class="pmpro_radio"><?php esc_html_e( 'Check Out with PayPal', 'learnpress-paid-membership-pro' ); ?></a>
						</span>
						</div>
					</td>
				</tr>
				</tbody>
			</table>
		<?php } ?>

		<?php
		$pmpro_include_billing_address_fields = apply_filters( 'pmpro_include_billing_address_fields', true );
		if ( $pmpro_include_billing_address_fields ) {
			?>
			<table id="pmpro_billing_address_fields" class="pmpro_checkout top1em" width="100%" cellpadding="0"
				   cellspacing="0" border="0"
				<?php
				if ( ! $pmpro_requirebilling || apply_filters( 'pmpro_hide_billing_address_fields', false ) ) {
					?>
					style="display: none;"<?php } ?>>
				<thead>
				<tr>
					<th><?php esc_html_e( 'Billing Address', 'learnpress-paid-membership-pro' ); ?></th>
				</tr>
				</thead>

				<tbody>
				<tr>
					<td>
						<div>
							<label
								for="bfirstname"><?php esc_html_e( 'First Name', 'learnpress-paid-membership-pro' ); ?></label>
							<input id="bfirstname" name="bfirstname" type="text"
								   class="input <?php echo pmpro_getClassForField( 'bfirstname' ); ?>" size="30"
								   value="<?php echo esc_attr( $bfirstname ); ?>"/>
						</div>
						<div>
							<label
								for="blastname"><?php esc_html_e( 'Last Name', 'learnpress-paid-membership-pro' ); ?></label>
							<input id="blastname" name="blastname" type="text"
								   class="input <?php echo pmpro_getClassForField( 'blastname' ); ?>" size="30"
								   value="<?php echo esc_attr( $blastname ); ?>"/>
						</div>
						<div>
							<label
								for="baddress1"><?php esc_html_e( 'Address 1', 'learnpress-paid-membership-pro' ); ?></label>
							<input id="baddress1" name="baddress1" type="text"
								   class="input <?php echo pmpro_getClassForField( 'baddress1' ); ?>" size="30"
								   value="<?php echo esc_attr( $baddress1 ); ?>"/>
						</div>
						<div>
							<label
								for="baddress2"><?php esc_html_e( 'Address 2', 'learnpress-paid-membership-pro' ); ?></label>
							<input id="baddress2" name="baddress2" type="text"
								   class="input <?php echo pmpro_getClassForField( 'baddress2' ); ?>" size="30"
								   value="<?php echo esc_attr( $baddress2 ); ?>"/>
						</div>

						<?php
						$longform_address = apply_filters( 'pmpro_longform_address', true );

						if ( $longform_address ) {
							?>
							<div>
								<label
									for="bcity"><?php esc_html_e( 'City', 'learnpress-paid-membership-pro' ); ?></label>
								<input id="bcity" name="bcity" type="text"
									   class="input <?php echo pmpro_getClassForField( 'bcity' ); ?>" size="30"
									   value="<?php echo esc_attr( $bcity ); ?>"/>
							</div>
							<div>
								<label
									for="bstate"><?php esc_html_e( 'State', 'learnpress-paid-membership-pro' ); ?></label>
								<input id="bstate" name="bstate" type="text"
									   class="input <?php echo pmpro_getClassForField( 'bstate' ); ?>" size="30"
									   value="<?php echo esc_attr( $bstate ); ?>"/>
							</div>
							<div>
								<label
									for="bzipcode"><?php esc_html_e( 'Postal Code', 'learnpress-paid-membership-pro' ); ?></label>
								<input id="bzipcode" name="bzipcode" type="text"
									   class="input <?php echo pmpro_getClassForField( 'bzipcode' ); ?>" size="30"
									   value="<?php echo esc_attr( $bzipcode ); ?>"/>
							</div>

						<?php } else { ?>

							<div>
								<label
									for="bcity_state_zip"><?php esc_html_e( 'City, State Zip', 'learnpress-paid-membership-pro' ); ?></label>
								<input id="bcity" name="bcity" type="text"
									   class="input <?php echo pmpro_getClassForField( 'bcity' ); ?>" size="14"
									   value="<?php echo esc_attr( $bcity ); ?>"/>,
								<?php
								$state_dropdowns = apply_filters( 'pmpro_state_dropdowns', false );

								if ( $state_dropdowns === true || $state_dropdowns == 'names' ) {
									global $pmpro_states;
									?>

									<select name="bstate" class=" <?php echo pmpro_getClassForField( 'bstate' ); ?>">
										<option value="">--</option>
										<?php foreach ( $pmpro_states as $ab => $st ) : ?>
											<option
												value="<?php echo esc_attr( $ab ); ?>" <?php echo ( $ab == $bstate ) ? 'selected="selected"' : ''; ?>><?php echo $st; ?></option>
										<?php endforeach; ?>
									</select>

									<?php
								} elseif ( $state_dropdowns == 'abbreviations' ) {
									global $pmpro_states_abbreviations;
									?>

									<select name="bstate" class=" <?php echo pmpro_getClassForField( 'bstate' ); ?>">
										<option value="">--</option>
										<?php foreach ( $pmpro_states_abbreviations as $ab ) : ?>
											<option
												value="<?php echo esc_attr( $ab ); ?>"<?php echo ( $ab == $bstate ) ? 'selected="selected"' : ''; ?>><?php echo $ab; ?></option>
										<?php endforeach; ?>
									</select>

								<?php } else { ?>
									<input id="bstate" name="bstate" type="text"
										   class="input <?php echo pmpro_getClassForField( 'bstate' ); ?>" size="2"
										   value="<?php echo esc_attr( $bstate ); ?>"/>
								<?php } ?>
								<input id="bzipcode" name="bzipcode" type="text"
									   class="input <?php echo pmpro_getClassForField( 'bzipcode' ); ?>" size="5"
									   value="<?php echo esc_attr( $bzipcode ); ?>"/>
							</div>
						<?php } ?>

						<?php
						$show_country = apply_filters( 'pmpro_international_addresses', true );
						if ( $show_country ) {
							?>
							<div>
								<label
									for="bcountry"><?php esc_html_e( 'Country', 'learnpress-paid-membership-pro' ); ?></label>
								<select name="bcountry" class=" <?php echo pmpro_getClassForField( 'bcountry' ); ?>">
									<?php
									global $pmpro_countries, $pmpro_default_country;

									if ( ! $bcountry ) {
										$bcountry = $pmpro_default_country;
									}

									foreach ( $pmpro_countries as $abbr => $country ) {
										?>
										<option
											value="<?php echo esc_attr( $abbr ); ?>"<?php echo ( $abbr == $bcountry ) ? ' selected="selected"' : ''; ?>><?php echo $country; ?></option>
										<?php
									}
									?>
								</select>
							</div>
						<?php } else { ?>
							<input type="hidden" name="bcountry" value="US"/>
						<?php } ?>

						<div>
							<label
								for="bphone"><?php esc_html_e( 'Phone', 'learnpress-paid-membership-pro' ); ?></label>
							<input id="bphone" name="bphone" type="text"
								   class="input <?php echo pmpro_getClassForField( 'bphone' ); ?>" size="30"
								   value="<?php echo esc_attr( formatPhone( $bphone ) ); ?>"/>
						</div>

						<?php if ( $skip_account_fields ) { ?>
							<?php
							if ( $current_user->ID ) {
								if ( ! $bemail && $current_user->user_email ) {
									$bemail = $current_user->user_email;
								}
								if ( ! $bconfirmemail && $current_user->user_email ) {
									$bconfirmemail = $current_user->user_email;
								}
							}
							?>
							<div>
								<label
									for="bemail"><?php esc_html_e( 'E-mail Address', 'learnpress-paid-membership-pro' ); ?></label>
								<input id="bemail" name="bemail"
									   type="<?php echo( $pmpro_email_field_type ? 'email' : 'text' ); ?>"
									   class="input <?php echo pmpro_getClassForField( 'bemail' ); ?>" size="30"
									   value="<?php echo esc_attr( $bemail ); ?>"/>
							</div>

							<?php
							$pmpro_checkout_confirm_email = apply_filters( 'pmpro_checkout_confirm_email', true );

							if ( $pmpro_checkout_confirm_email ) {
								?>
								<div>
									<label
										for="bconfirmemail"><?php esc_html_e( 'Confirm E-mail', 'learnpress-paid-membership-pro' ); ?></label>
									<input id="bconfirmemail" name="bconfirmemail"
										   type="<?php echo( $pmpro_email_field_type ? 'email' : 'text' ); ?>"
										   class="input <?php echo pmpro_getClassForField( 'bconfirmemail' ); ?>"
										   size="30" value="<?php echo esc_attr( $bconfirmemail ); ?>"/>

								</div>
							<?php } else { ?>
								<input type="hidden" name="bconfirmemail_copy" value="1"/>
							<?php } ?>
						<?php } ?>
					</td>
				</tr>
				</tbody>
			</table>
		<?php } ?>

		<?php do_action( 'pmpro_checkout_after_billing_fields' ); ?>

		<?php
		$pmpro_accepted_credit_cards        = pmpro_getOption( 'accepted_credit_cards' );
		$pmpro_accepted_credit_cards        = explode( ',', $pmpro_accepted_credit_cards );
		$pmpro_accepted_credit_cards_string = pmpro_implodeToEnglish( $pmpro_accepted_credit_cards );
		?>

		<?php
		$pmpro_include_payment_information_fields = apply_filters( 'pmpro_include_payment_information_fields', true );

		if ( $pmpro_include_payment_information_fields ) {
			?>
			<table id="pmpro_payment_information_fields" class="pmpro_checkout top1em" width="100%" cellpadding="0"
				   cellspacing="0" border="0"
				<?php
				if ( ! $pmpro_requirebilling || apply_filters( 'pmpro_hide_payment_information_fields', false ) ) {
					?>
					style="display: none;"<?php } ?>>
				<thead>
				<tr>
					<th>
						<span
							class="pmpro_thead-name"><?php esc_html_e( 'Payment Information', 'learnpress-paid-membership-pro' ); ?></span>
						<span
							class="pmpro_thead-msg"><?php printf( __( 'We Accept %s', 'learnpress-paid-membership-pro' ), $pmpro_accepted_credit_cards_string ); ?></span>
					</th>
				</tr>
				</thead>
				<tbody>
				<tr valign="top">
					<td>
						<?php
						$sslseal = pmpro_getOption( 'sslseal' );

						if ( $sslseal ) {
							?>
							<div class="pmpro_sslseal"><?php echo stripslashes( $sslseal ); ?></div>
							<?php
						}
						?>

						<?php
						$pmpro_include_cardtype_field = apply_filters( 'pmpro_include_cardtype_field', false );

						if ( $pmpro_include_cardtype_field ) {
							?>
							<div class="pmpro_payment-card-type">
								<label
									for="CardType"><?php esc_html_e( 'Card Type', 'learnpress-paid-membership-pro' ); ?></label>
								<select id="CardType" name="CardType"
										class=" <?php echo pmpro_getClassForField( 'CardType' ); ?>">
									<?php foreach ( $pmpro_accepted_credit_cards as $cc ) : ?>
										<option
											value="<?php echo $cc; ?>"<?php echo ( $CardType == $cc ) ? ' selected="selected"' : ''; ?>><?php echo $cc; ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						<?php } else { ?>
						<input type="hidden" id="CardType" name="CardType"
							   value="<?php echo esc_attr( $CardType ); ?>"/>
							<script>
								<!--
								jQuery(document).ready(function () {
									jQuery('#AccountNumber').validateCreditCard(function (result) {
										var cardtypenames = {
											"amex": "American Express",
											"diners_club_carte_blanche": "Diners Club Carte Blanche",
											"diners_club_international": "Diners Club International",
											"discover": "Discover",
											"jcb": "JCB",
											"laser": "Laser",
											"maestro": "Maestro",
											"mastercard": "Mastercard",
											"visa": "Visa",
											"visa_electron": "Visa Electron"
										};

										if (result.card_type)
											jQuery('#CardType').val(cardtypenames[result.card_type.name]);
										else
											jQuery('#CardType').val('Unknown Card Type');
									});
								});
								-->
							</script>
							<?php
						}
						?>

						<div class="pmpro_payment-account-number">
							<label
								for="AccountNumber"><?php esc_html_e( 'Card Number', 'learnpress-paid-membership-pro' ); ?></label>
							<input id="AccountNumber" name="AccountNumber"
								   class="input <?php echo pmpro_getClassForField( 'AccountNumber' ); ?>" type="text"
								   size="25" value="<?php echo esc_attr( $AccountNumber ); ?>"
								   data-encrypted-name="number" autocomplete="off"/>
						</div>

						<div class="pmpro_payment-expiration">
							<label
								for="ExpirationMonth"><?php esc_html_e( 'Expiration Date', 'learnpress-paid-membership-pro' ); ?></label>
							<select id="ExpirationMonth" name="ExpirationMonth"
									class=" <?php echo pmpro_getClassForField( 'ExpirationMonth' ); ?>">
								<option value="01"
									<?php
									if ( $ExpirationMonth == '01' ) {
										?>
										selected="selected"<?php } ?>>01
								</option>
								<option value="02"
									<?php
									if ( $ExpirationMonth == '02' ) {
										?>
										selected="selected"<?php } ?>>02
								</option>
								<option value="03"
									<?php
									if ( $ExpirationMonth == '03' ) {
										?>
										selected="selected"<?php } ?>>03
								</option>
								<option value="04"
									<?php
									if ( $ExpirationMonth == '04' ) {
										?>
										selected="selected"<?php } ?>>04
								</option>
								<option value="05"
									<?php
									if ( $ExpirationMonth == '05' ) {
										?>
										selected="selected"<?php } ?>>05
								</option>
								<option value="06"
									<?php
									if ( $ExpirationMonth == '06' ) {
										?>
										selected="selected"<?php } ?>>06
								</option>
								<option value="07"
									<?php
									if ( $ExpirationMonth == '07' ) {
										?>
										selected="selected"<?php } ?>>07
								</option>
								<option value="08"
									<?php
									if ( $ExpirationMonth == '08' ) {
										?>
										selected="selected"<?php } ?>>08
								</option>
								<option value="09"
									<?php
									if ( $ExpirationMonth == '09' ) {
										?>
										selected="selected"<?php } ?>>09
								</option>
								<option value="10"
									<?php
									if ( $ExpirationMonth == '10' ) {
										?>
										selected="selected"<?php } ?>>10
								</option>
								<option value="11"
									<?php
									if ( $ExpirationMonth == '11' ) {
										?>
										selected="selected"<?php } ?>>11
								</option>
								<option value="12"
									<?php
									if ( $ExpirationMonth == '12' ) {
										?>
										selected="selected"<?php } ?>>12
								</option>
							</select>/<select id="ExpirationYear" name="ExpirationYear"
											  class=" <?php echo pmpro_getClassForField( 'ExpirationYear' ); ?>">
								<?php
								for ( $i = date_i18n( 'Y' ); $i < intval( date_i18n( 'Y' ) ) + 10; $i ++ ) {
									?>
									<option
										value="<?php echo $i; ?>"<?php echo ( $ExpirationYear == $i ) ? ' selected="selected"' : ''; ?>><?php echo $i; ?></option>
									<?php
								}
								?>
							</select>
						</div>

						<?php
						$pmpro_show_cvv = apply_filters( 'pmpro_show_cvv', true );
						if ( $pmpro_show_cvv ) {
							?>
							<div class="pmpro_payment-cvv">
								<label for="CVV"><?php esc_html_e( 'CVV', 'learnpress-paid-membership-pro' ); ?></label>
								<input class="input" id="CVV" name="CVV" type="text" size="4" value="
								<?php
								if ( ! empty( $_REQUEST['CVV'] ) ) {
									echo esc_attr( $_REQUEST['CVV'] );
								}
								?>
								" class="<?php echo pmpro_getClassForField( 'CVV' ); ?>"/>
								<small>(<a href="javascript:void(0);"
										   onclick="javascript:window.open('<?php echo pmpro_https_filter( PMPRO_URL ); ?>/pages/popup-cvv.html','cvv','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=600, height=475');"><?php esc_html_e( "what's this?", 'learnpress-paid-membership-pro' ); ?></a>)
								</small>
							</div>
						<?php } ?>

						<?php if ( $pmpro_show_discount_code ) : ?>
							<div class="pmpro_payment-discount-code">
								<label
									for="discount_code"><?php esc_html_e( 'Discount Code', 'learnpress-paid-membership-pro' ); ?></label>
								<input class="input <?php echo pmpro_getClassForField( 'discount_code' ); ?>"
									   id="discount_code" name="discount_code" type="text" size="20"
									   value="<?php echo esc_attr( $discount_code ); ?>"/>
								<input type="button" id="discount_code_button" name="discount_code_button"
									   value="<?php esc_html_e( 'Apply', 'learnpress-paid-membership-pro' ); ?>"/>
								<p id="discount_code_message" class="pmpro_message" style="display: none;"></p>
							</div>
						<?php endif; ?>

					</td>
				</tr>
				</tbody>
			</table>
		<?php } ?>
		<script>
			<!--
			//checking a discount code
			jQuery('#discount_code_button').on('click', function () {
				var code = jQuery('#discount_code').val();
				var level_id = jQuery('#level').val();

				if (code) {
					//hide any previous message
					jQuery('.pmpro_discount_code_msg').hide();

					//disable the apply button
					jQuery('#discount_code_button').attr('disabled', 'disabled');

					jQuery.ajax({
						url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
						type: 'GET',
						timeout:<?php echo apply_filters( 'pmpro_ajax_timeout', 5000, 'applydiscountcode' ); ?>,
						dataType: 'html',
						data: "action=applydiscountcode&code=" + code + "&level=" + level_id + "&msgfield=discount_code_message",
						error: function (xml) {
							alert('Error applying discount code [1]');

							//enable apply button
							jQuery('#discount_code_button').removeAttr('disabled');
						},
						success: function (responseHTML) {
							if (responseHTML == 'error') {
								alert('Error applying discount code [2]');
							} else {
								jQuery('#discount_code_message').html(responseHTML);
							}

							//enable invite button
							jQuery('#discount_code_button').removeAttr('disabled');
						}
					});
				}
			});
			-->
		</script>

		<?php do_action( 'pmpro_checkout_after_payment_information_fields' ); ?>

		<?php if ( $tospage && ! $pmpro_review ) { ?>
			<table id="pmpro_tos_fields" class="pmpro_checkout top1em" width="100%" cellpadding="0" cellspacing="0"
				   border="0">
				<thead>
				<tr>
					<th><?php echo $tospage->post_title; ?></th>
				</tr>
				</thead>

				<tbody>
				<tr class="odd">
					<td>
						<div id="pmpro_license">
							<?php echo wpautop( do_shortcode( $tospage->post_content ) ); ?>
						</div>
						<input type="checkbox" name="tos" value="1" id="tos"/>
						<label class="pmpro_normal pmpro_clickable"
							   for="tos"><?php printf( __( 'I agree to the %s', 'learnpress-paid-membership-pro' ), $tospage->post_title ); ?></label>
					</td>
				</tr>
				</tbody>
			</table>
		<?php } ?>

		<?php do_action( 'pmpro_checkout_after_tos_fields' ); ?>

		<div class="<?php echo pmpro_get_element_class( 'pmpro_checkout-field pmpro_captcha', 'pmpro_captcha' ); ?>">
			<?php
			global $recaptcha, $recaptcha_publickey;
			if ( $recaptcha == 2 || ( $recaptcha == 1 && pmpro_isLevelFree( $pmpro_level ) ) ) {
				echo pmpro_recaptcha_get_html($recaptcha_publickey, NULL, true);
			}
			?>
		</div> <!-- end pmpro_captcha -->

		<?php
		do_action( 'pmpro_checkout_after_captcha' );
		?>

		<?php do_action( 'pmpro_checkout_before_submit_button' ); ?>

		<div class="pmpro_submit">
			<?php if ( $pmpro_review ) { ?>
				<span id="pmpro_submit_span">
					<input type="hidden" name="confirm" value="1"/>
					<input type="hidden" name="token" value="<?php echo esc_attr( $pmpro_paypal_token ); ?>"/>
					<input type="hidden" name="gateway" value="<?php echo esc_attr( $gateway ); ?>"/>
					<input type="submit" id="pmpro_btn-submit" class="pmpro_btn pmpro_btn-submit-checkout"
						   value="<?php esc_html_e( 'Complete Payment', 'learnpress-paid-membership-pro' ); ?> &raquo;"/>
				</span>
			<?php } else { ?>
				<?php
				$pmpro_checkout_default_submit_button = apply_filters( 'pmpro_checkout_default_submit_button', true );

				if ( $pmpro_checkout_default_submit_button ) {
					?>
					<span id="pmpro_submit_span">
						<input type="hidden" name="submit-checkout" value="1"/>
						<input type="submit" id="pmpro_btn-submit" class="pmpro_btn pmpro_btn-submit-checkout" value="
						<?php
						if ( $pmpro_requirebilling ) {
							esc_attr_e( 'Submit and Check Out', 'learnpress-paid-membership-pro' );
						} else {
							esc_attr_e( 'Submit and Confirm', 'learnpress-paid-membership-pro' );
						}
						?>
						&raquo;"/>
					</span>
				<?php } ?>
			<?php } ?>

			<span id="pmpro_processing_message" style="visibility: hidden;">
				<?php echo apply_filters( 'pmpro_processing_message', esc_html__( 'Processing...', 'learnpress-paid-membership-pro' ) ); ?>
			</span>
		</div>

	</form>

	<?php do_action( 'pmpro_checkout_after_form' ); ?>

</div> <!-- end pmpro_level-ID -->

<script>
	<!--
	// Find ALL <form> tags on your page
	jQuery('form').on('submit', function () {
		// On submit disable its submit button
		jQuery('input[type=submit]', this).attr('disabled', 'disabled');
		jQuery('input[type=image]', this).attr('disabled', 'disabled');
		jQuery('#pmpro_processing_message').css('visibility', 'visible');
	});

	//iOS Safari fix (see: http://stackoverflow.com/questions/20210093/stop-safari-on-ios7-prompting-to-save-card-data)
	var userAgent = window.navigator.userAgent;
	if (userAgent.match(/iPad/i) || userAgent.match(/iPhone/i)) {
		jQuery('input[type=submit]').on('click', function () {
			try {
				jQuery("input[type=password]").attr("type", "hidden");
			} catch (ex) {
				try {
					jQuery("input[type=password]").prop("type", "hidden");
				} catch (ex) {
				}
			}
		});
	}

	//add required to required fields
	jQuery('.pmpro_required').after('<span class="pmpro_asterisk"> <abbr title="Required Field">*</abbr></span>');

	//unhighlight error fields when the user edits them
	jQuery('.pmpro_error').on("change keyup input", function () {
		jQuery(this).removeClass('pmpro_error');
	});

	//click apply button on enter in discount code box
	jQuery('#discount_code').on('keydown', function (e) {
		if (e.keyCode == 13) {
			e.preventDefault();
			jQuery('#discount_code_button').trigger('click');
		}
	});

	//hide apply button if a discount code was passed in
	<?php if ( ! empty( $_REQUEST['discount_code'] ) ) { ?>
	jQuery('#discount_code_button').hide();
	jQuery('#discount_code').on('change keyup', function () {
		jQuery('#discount_code_button').show();
	});
	<?php } ?>

	//click apply button on enter in *other* discount code box
	jQuery('#other_discount_code').on('keydown', function (e) {
		if (e.keyCode == 13) {
			e.preventDefault();
			jQuery('#other_discount_code_button').click();
		}
	});
	-->
</script>
<script>
	<!--
	//add javascriptok hidden field to checkout
	jQuery("input[name=submit-checkout]").after('<input type="hidden" name="javascriptok" value="1" />');
	-->
</script>
