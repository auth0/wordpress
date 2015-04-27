<?php
$title = WP_Auth0_Options::get('form_title');
if (empty($title)) {
    $title = "Auth0";
}
?>

<style>
    #loginform{
        display: block !important;
    }
</style>
<div id="extra-options">
    <a href="?">← Back to <?= $title ?> login</a>
</div>
