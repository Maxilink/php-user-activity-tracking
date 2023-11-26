<?php
/**
 * Support Doc Comment
 *
 * @category Views
 * @package  user-activity-tracking-and-log
 * @author   Moove Agency
 */

if (! defined('ABSPATH') ) {
    exit;
} // Exit if accessed directly

?>
<h2><?php esc_html_e('Support', 'user-activity-tracking-and-log'); ?></h2>
<hr />

<?php
    $forum_link = apply_filters('uat_forum_section_link', 'https://support.mooveagency.com/forum/user-activity-tracking-and-log/');
?>


<h4>Here are the best steps to find answers to your questions and resolve issues as fast as possible:</h4><br>

<h4>1. Check the Documentation section</h4>
<p>
  Most issues can be resolved quickly and easily. We compiled a list of the basic troubleshooting tips, hooks, and event tracking examples in our <a href="<?php echo esc_url(admin_url('admin.php?page=moove-activity-log&tab=documentation')); ?>" class="uat_admin_link">Documentation</a>.
</p>

<hr>

<h4>2. Search our <a href="<?php echo esc_url($forum_link); ?>" class="uat_admin_link" target="_blank">Support Forum</a></h4>
<p>Most questions have already been asked by other users so you can find answers quickly and resolve issues fast by searching for the problem on our <a href="<?php echo esc_url($forum_link); ?>" class="uat_admin_link" target="_blank">support forum</a>. Search bar is located in the top right corner.</p>
<hr>

<h4>3. Create a Support Ticket</h4>
<p>If you still need support, you can create a <a href="<?php echo esc_url($forum_link); ?>#new-post" class="uat_admin_link" target="_blank">new support ticket</a> in our Support Forum.</p>

<p>Please don’t forget to add screenshots or video recording of your screen that would help us see what issues you are experiencing.</p>

