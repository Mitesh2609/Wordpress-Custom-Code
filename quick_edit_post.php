<?php

/**
 * Status and Attorney Name functionality of Quick Edit in Legal admin side
 */

function ask_legal_quick_edit($column_name, $post_type) {
    if ($post_type !== 'ask_legal') {
        return;
    }
    if ($column_name === 'status') {
        ?>
        <fieldset class="inline-edit-col-left">
            <div class="inline-edit-col">
                <label>
                    <span class="title">Legal Status</span>
                    <select name="status">
                        <option value="0">Pending</option>
                        <option value="1">Completed</option>
                    </select>
                </label>
            </div>
        </fieldset>
        <?php
    }
    if ($column_name === 'attorny_names') {
        $members = get_All_Attorney_plrb_lirb();
        ?>
        <fieldset class="inline-edit-col-left">
            <div class="inline-edit-col">
                <label>
                    <span class="title">Attorney</span>
                    <select name="attorney">
                        <option value="">Select</option>
                        <?php
                        if (!empty($members)) {
                            foreach ($members as $member) {
                                $user_id = $member->option_value;
                                $user = get_user_by('ID', $user_id);
                                if ($user) {
                                    ?>
                                    <option value="<?php echo esc_attr($user_id); ?>">
                                        <?php echo esc_html($user->user_email . ' (' . $user->display_name . ')'); ?>
                                    </option>
                                    <?php
                                }
                            }
                        }
                        ?>
                    </select>
                </label>
            </div>
        </fieldset>
        <?php
    }
}
add_action('quick_edit_custom_box', 'ask_legal_quick_edit', 10, 2);

function ask_legal_save_quick_edit_data($post_id)
{
    if (!isset($_POST['_inline_edit']) || !wp_verify_nonce($_POST['_inline_edit'], 'inlineeditnonce')) {
        return;
    }

    if (isset($_POST['status'])) {
        update_post_meta($post_id, 'status', sanitize_text_field($_POST['status']));
    }

    if (isset($_POST['attorney']))
    {
        $new_attorney_id = sanitize_text_field($_POST['attorney']);
        $current_attorney_id = get_field('field_6596592fd8cf7', $post_id); // Get the current attorney ID from ACF

        if ($new_attorney_id !== $current_attorney_id) {
            update_field('field_6596592fd8cf7', $new_attorney_id, $post_id);
            if ($new_attorney_id)
            {
                $user = getUserDetailByIDorEmail($new_attorney_id);
                if ($user && !empty($user['email_address'])) {
                    $submited_user_question = !empty(get_field('question',$post_id)) ? get_field('question',$post_id) : get_the_title($post_id);
                    $attorney_email_address = $user['email_address'];
                    $attorney_phone= $user['phone_number'];
                    $attorney_fullname = $user['first_name']." ".$user['last_name'];
                    $submited_by_user = get_field("question_from",$post_id);

                    $question_from = getUserDetailByIDorEmail($submited_by_user);
                    if ($question_from && !empty($question_from['email_address'])) {
                        $question_user_fullname = $question_from['first_name']." ".$question_from['last_name'];
                        $question_user_email = $question_from['email_address'];
                        $question_user_branch = $question_from['branch_name'];
                        $question_user_phone = $question_from['phone_number'];
                        $question_user_member_group = $question_from['member_group_id'];
                        $question_user_member_group_name = $question_from['member_group_name'];
                        $questionfull_address = $question_from['street_address'].", ".$question_from['city'].", ".$question_from['state'].", ".$question_from['zippostal_code'];
                    }

                    // Retrieve PLRB policy details
                    $plrb_policy_group = get_field("plrb_question_details", $post_id);
                    $select_plrb_policy = $plrb_policy_group['policy'] ?? " ";
                    $select_plrb_policy_name = $plrb_policy_group['policy_name'] ?? " ";
                    $select_plrb_policy_state = $plrb_policy_group['state'] ?? " ";
                    $select_plrb_policy_language = $plrb_policy_group['policy_language'] ?? " ";
                    $select_plrb_policy_relevant_facts = $plrb_policy_group['relevant_facts'] ?? " ";

                    // Retrieve LIRB policy details
                    $lirb_policy_group = get_field("lirb_question_details", $post_id);
                    $select_lirb_policy = $lirb_policy_group['policy'] ?? " ";
                    $select_lirb_policy_name = $lirb_policy_group['policy_name'] ?? " ";
                    $select_lirb_policy_state = $lirb_policy_group['state'] ?? " ";
                    $select_lirb_policy_language = $lirb_policy_group['policy_language'] ?? " ";
                    $select_lirb_policy_relevant_facts = $lirb_policy_group['relevant_facts'] ?? " ";

                    // Consolidate policy details
                    $selected_policy = $select_plrb_policy ?: $select_lirb_policy;
                    $selected_policy_name = $select_plrb_policy_name ?: $select_lirb_policy_name;
                    $selected_policy_state = $select_plrb_policy_state ?: $select_lirb_policy_state;
                    $selected_policy_language = $select_plrb_policy_language ?: $select_lirb_policy_language;
                    $selected_policy_relevant_facts = $select_plrb_policy_relevant_facts ?: $select_lirb_policy_relevant_facts;

                    $attachment_urls = get_field('attachment_url', $post_id);
                    if ($attachment_urls) {
                        $urls = explode(',', $attachment_urls);
                        $attachment_links = ''; // Initialize a variable to store all links
                        foreach ($urls as $url) {
                            $trimmed_url = esc_url(trim($url)); // Sanitize and trim the URL
                            $file_name = basename($trimmed_url); // Get the file name from the URL
                            $attachment_links .= '<a href="' . $trimmed_url . '" target="_blank">' . $file_name . '</a><br>'; // Append each link with filename
                        }
                    }

                    $to = $attorney_email_address;
                    $subject = !empty(get_field('email_subject', 'option')) ? get_field('email_subject', 'option') : ''; 
                    $from_name = !empty(get_field('from_name', 'option')) ? get_field('from_name', 'option') : '';  
                    $from_email = !empty(get_field('from_email_address', 'option')) ? get_field('from_email_address', 'option') : '';
                    $bcc_email = !empty(get_field('bcc_email_address', 'option')) ? get_field('bcc_email_address', 'option') : '';

                    $headers[] = "From: $from_name <$from_email>";
                    $headers[] = "Content-Type: text/html; charset=UTF-8";
                    $headers[] = "BCC: $bcc_email";

                    $message = '<html><body>';
                    $message .= '<p><strong>New question submitted By:</strong><br />'
                        .$question_user_fullname.'<br />'
                        .$question_user_email.'<br /> Company Name: '.$question_user_branch.'<br />'.$question_user_phone.'<br />'.$questionfull_address.'</p>';
                    $message .= '<p><strong>Submission Details:</strong></p>';
                    $message .= '<table width="99%" border="0" cellpadding="1" cellspacing="0" bgcolor="#EAEAEA">
									<tr>
										<td>
											<table width="100%" border="0" cellpadding="5" cellspacing="0" bgcolor="#FFFFFF">
												<tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>Select Policy</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$selected_policy.'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>Attach policy and supporting documentation electronically</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;"><ul><li>'.$attachment_links.'</li></ul></font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>Enter Policy Name (If No Policy Selected Above) - Please note, if we do not have the policy on file, we may need to request a copy of the policy. This may delay the response.</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$selected_policy_name.'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>State Where Insured Premises is Located</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$selected_policy_state.'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>Specific Policy Language at Issue</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$selected_policy_language.'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>Relevant Facts -- the facts of the loss that gave rise to the claim, to the extent they are relevant, with sufficient detail so as to provide background and context for your question.</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$selected_policy_relevant_facts.'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>Question</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$submited_user_question.'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>Attorney Email</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$attorney_email_address.'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>Attorney Phone No.</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$attorney_phone.'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>Attorney id</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$new_attorney_id.'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>Attorney Name</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$attorney_fullname.'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>User Contact No</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$question_user_phone.'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>User Member Group ID</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$question_user_member_group.'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>User Member Group Name</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$question_user_member_group_name.'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>User Member Company Name</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$question_user_branch.'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>User Street Address</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$question_from['street_address'].'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>User City</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$question_from['city'].'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>User State</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$question_from['state'].'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>User Zipcode</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$question_from['zippostal_code'].'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>is Loged in?</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$question_user_fullname.'</font></td></tr>
											</table>
										</td>
									</tr>
								</table>';
                    $message .= '</body></html>';
                    wp_mail($to, $subject, $message, $headers);
                }
            }
        }
    }
}
add_action('save_post', 'ask_legal_save_quick_edit_data');

add_action('admin_footer-edit.php', function() {
    ?>
    <script>
        jQuery(function($) {
            const wp_inline_edit_function = inlineEditPost.edit;
            inlineEditPost.edit = function(post_id) {
                wp_inline_edit_function.apply(this, arguments);

                const postId = typeof post_id === 'object' ? this.getId(post_id) : post_id;
                const editRow = $('#edit-' + postId);
                const postRow = $('#post-' + postId);

                const statusId = $('span.column-status', postRow).data('status-id');
                if( statusId !== undefined ) {
                    $('select[name="status"]', editRow).val(statusId);
                }

                const attorneyId = $('.column-attorney', postRow).data('attorney-id');
                if ( attorneyId !== undefined ) {
                    $('select[name="attorney"]', editRow).val(attorneyId);
                }
            };

        });
    </script>
    <?php
});

function ask_legal_custom_columns($column, $post_id) {
    if ($column === 'status') {
        $status = get_post_meta($post_id, 'status', true);
        $status = ($status !== '') ? $status : 0;
        $status_label = ($status == 1) ? 'Completed' : 'Pending';
        ?>
        <span class="column-status" data-status-id="<?php echo esc_attr($status); ?>">
            <?php echo $status_label; ?>
        </span>
        <?php
    }
    if ($column === 'attorny_names') {
        $attorney_id_acf = get_field('attorney_name', $post_id);
        $attorney_field = is_array( $attorney_id_acf ) ? $attorney_id_acf : [ $attorney_id_acf ];
        $attorney_id =  reset($attorney_field);
        $attorney_display = '-';
        if (!empty($attorney_id)) {
            $user_data = get_userdata($attorney_id);
            if ($user_data) {
                $profile_url = get_edit_user_link($attorney_id);
                $attorney_display = sprintf(
                    '%s %s<br><a target="_blank" href="%s">%s</a>',
                    esc_html($user_data->first_name),
                    esc_html($user_data->last_name),
                    esc_url($profile_url),
                    esc_html($user_data->user_email)
                );
            }
        }
        ?>
        <span class="column-attorney"
            <?php if (!empty($attorney_id)) : ?>
                data-attorney-id="<?php echo esc_attr($attorney_id); ?>"
            <?php endif; ?>>
            <?php echo $attorney_display; ?>
        </span>
        <?php
    }
}
add_action('manage_ask_legal_posts_custom_column', 'ask_legal_custom_columns', 10, 2);


/**
 * Ask Legal Post Edit Side Mail Sent Other Attorney
 */

function send_email_on_acf_update($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if ($post_id === 'options') {
        return;
    }

    if (get_post_type($post_id) !== 'ask_legal') {
        return; // Skip if the post type is not 'book'
    }

    $acf_field_name = 'attorney_name'; // Replace with your actual ACF field name or key
    $new_attorney_email = get_field($acf_field_name, $post_id);

    $old_attorney_email = get_post_meta($post_id, '_previous_' . $acf_field_name, true);

    if (  $new_attorney_email !== $old_attorney_email ) {
        update_post_meta($post_id, '_previous_' . $acf_field_name, $new_attorney_email);
        if (!empty( $old_attorney_email ) && $new_attorney_email)
        {
            $user = getUserDetailByIDorEmail($new_attorney_email);
            if ($user && !empty($user['email_address'])) {
                $submited_user_question = !empty(get_field('question',$post_id)) ? get_field('question',$post_id) : get_the_title($post_id);
                $attorney_email_address = $user['email_address'];
                $attorney_phone= $user['phone_number'];
                $attorney_fullname = $user['first_name']." ".$user['last_name'];
                $submited_by_user = get_field("question_from",$post_id);

                $question_from = getUserDetailByIDorEmail($submited_by_user);
                if ($question_from && !empty($question_from['email_address'])) {
                    $question_user_fullname = $question_from['first_name']." ".$question_from['last_name'];
                    $question_user_email = $question_from['email_address'];
                    $question_user_branch = $question_from['branch_name'];
                    $question_user_phone = $question_from['phone_number'];
                    $question_user_member_group = $question_from['member_group_id'];
                    $question_user_member_group_name = $question_from['member_group_name'];
                    $questionfull_address = $question_from['street_address'].", ".$question_from['city'].", ".$question_from['state'].", ".$question_from['zippostal_code'];
                }

                // Retrieve PLRB policy details
                $plrb_policy_group = get_field("plrb_question_details", $post_id);
                $select_plrb_policy = $plrb_policy_group['policy'] ?? " ";
                $select_plrb_policy_name = $plrb_policy_group['policy_name'] ?? " ";
                $select_plrb_policy_state = $plrb_policy_group['state'] ?? " ";
                $select_plrb_policy_language = $plrb_policy_group['policy_language'] ?? " ";
                $select_plrb_policy_relevant_facts = $plrb_policy_group['relevant_facts'] ?? " ";

                // Retrieve LIRB policy details
                $lirb_policy_group = get_field("lirb_question_details", $post_id);
                $select_lirb_policy = $lirb_policy_group['policy'] ?? " ";
                $select_lirb_policy_name = $lirb_policy_group['policy_name'] ?? " ";
                $select_lirb_policy_state = $lirb_policy_group['state'] ?? " ";
                $select_lirb_policy_language = $lirb_policy_group['policy_language'] ?? " ";
                $select_lirb_policy_relevant_facts = $lirb_policy_group['relevant_facts'] ?? " ";

                // Consolidate policy details
                $selected_policy = $select_plrb_policy ?: $select_lirb_policy;
                $selected_policy_name = $select_plrb_policy_name ?: $select_lirb_policy_name;
                $selected_policy_state = $select_plrb_policy_state ?: $select_lirb_policy_state;
                $selected_policy_language = $select_plrb_policy_language ?: $select_lirb_policy_language;
                $selected_policy_relevant_facts = $select_plrb_policy_relevant_facts ?: $select_lirb_policy_relevant_facts;

                $attachment_urls = get_field('attachment_url', $post_id);
                if ($attachment_urls) {
                    $urls = explode(',', $attachment_urls);
                    $attachment_links = ''; // Initialize a variable to store all links
                    foreach ($urls as $url) {
                        $trimmed_url = esc_url(trim($url)); // Sanitize and trim the URL
                        $file_name = basename($trimmed_url); // Get the file name from the URL
                        $attachment_links .= '<a href="' . $trimmed_url . '" target="_blank">' . $file_name . '</a><br>'; // Append each link with filename
                    }
                }

                $to = $attorney_email_address;
                $subject = !empty(get_field('email_subject', 'option')) ? get_field('email_subject', 'option') : ''; 
                $from_name = !empty(get_field('from_name', 'option')) ? get_field('from_name', 'option') : '';  
                $from_email = !empty(get_field('from_email_address', 'option')) ? get_field('from_email_address', 'option') : '';
                $bcc_email = !empty(get_field('bcc_email_address', 'option')) ? get_field('bcc_email_address', 'option') : '';

                $headers[] = "From: $from_name <$from_email>";
                $headers[] = "Content-Type: text/html; charset=UTF-8";
                $headers[] = "BCC: $bcc_email";

                $message = '<html><body>';
                $message .= '<p><strong>New question submitted By:</strong><br />'
                    .$question_user_fullname.'<br />'
                    .$question_user_email.'<br /> Company Name: '.$question_user_branch.'<br />'.$question_user_phone.'<br />'.$questionfull_address.'</p>';
                $message .= '<p><strong>Submission Details:</strong></p>';
                $message .= '<table width="99%" border="0" cellpadding="1" cellspacing="0" bgcolor="#EAEAEA">
								<tr>
									<td>
										<table width="100%" border="0" cellpadding="5" cellspacing="0" bgcolor="#FFFFFF">
											<tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>Select Policy</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$selected_policy.'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>Attach policy and supporting documentation electronically</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;"><ul><li>'.$attachment_links.'</li></ul></font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>Enter Policy Name (If No Policy Selected Above) - Please note, if we do not have the policy on file, we may need to request a copy of the policy. This may delay the response.</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$selected_policy_name.'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>State Where Insured Premises is Located</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$selected_policy_state.'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>Specific Policy Language at Issue</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$selected_policy_language.'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>Relevant Facts -- the facts of the loss that gave rise to the claim, to the extent they are relevant, with sufficient detail so as to provide background and context for your question.</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$selected_policy_relevant_facts.'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>Question</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$submited_user_question.'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>Attorney Email</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$attorney_email_address.'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>Attorney Phone No.</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$attorney_phone.'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>Attorney id</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$new_attorney_id.'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>Attorney Name</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$attorney_fullname.'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>User Contact No</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$question_user_phone.'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>User Member Group ID</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$question_user_member_group.'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>User Member Group Name</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$question_user_member_group_name.'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>User Member Company Name</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$question_user_branch.'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>User Street Address</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$question_from['street_address'].'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>User City</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$question_from['city'].'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>User State</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$question_from['state'].'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>User Zipcode</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$question_from['zippostal_code'].'</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family: sans-serif; font-size:12px;"><strong>is Loged in?</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family: sans-serif; font-size:12px;">'.$question_user_fullname.'</font></td></tr>
										</table>
									</td>
								</tr>
							</table>';
                $message .= '</body></html>';
                wp_mail($to, $subject, $message, $headers);
            }
        }
    }
}

add_action('acf/save_post', 'send_email_on_acf_update', 10, 1);
