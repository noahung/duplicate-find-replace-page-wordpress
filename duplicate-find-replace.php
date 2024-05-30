<?php
/*
Plugin Name: Duplicate Wizard
Description: Duplicates a page and replaces specified words.
Version: 1.1
Author: Noah
*/

add_action('admin_menu', 'dpr_add_admin_menu');

function dpr_add_admin_menu() {
    $icon_url = plugin_dir_url(__FILE__) . 'adverto-logo.png';
    add_menu_page('Duplicate SEO Wizard', 'Duplicate SEO Wizard', 'manage_options', 'duplicate-page-replace', 'dpr_admin_page', $icon_url, 20);
}

function dpr_admin_page() {
    if (isset($_POST['dpr_submit'])) {
        $num_duplicates = intval($_POST['dpr_num_duplicates']);
        $replacements = [];
        for ($i = 1; $i <= $num_duplicates; $i++) {
            $replacements[] = $_POST["dpr_replace_$i"];
        }
        dpr_duplicate_pages($_POST['dpr_page_id'], $_POST['dpr_find'], $replacements);
    }

    ?>
    <div class="wrap">
        <h1>Duplicate SEO Wizard</h1>
        <form method="post">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Page to Duplicate</th>
                    <td>
                        <select name="dpr_page_id">
                            <?php
                            $pages = get_pages();
                            foreach ($pages as $page) {
                                echo '<option value="' . $page->ID . '">' . $page->post_title . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Find</th>
                    <td><input type="text" name="dpr_find" required /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Number of Duplicates</th>
                    <td><input type="number" name="dpr_num_duplicates" id="dpr_num_duplicates" value="1" min="1" required /></td>
                </tr>
                <tbody id="dpr_replacements">
                    <tr valign="top">
                        <th scope="row">Replace 1</th>
                        <td><input type="text" name="dpr_replace_1" required /></td>
                    </tr>
                </tbody>
            </table>
            <?php submit_button('Duplicate and Replace', 'primary', 'dpr_submit'); ?>
        </form>
    </div>
    <script>
    document.getElementById('dpr_num_duplicates').addEventListener('change', function () {
        var num = this.value;
        var replacementsDiv = document.getElementById('dpr_replacements');
        replacementsDiv.innerHTML = '';
        for (var i = 1; i <= num; i++) {
            var row = '<tr valign="top"><th scope="row">Replace ' + i + '</th><td><input type="text" name="dpr_replace_' + i + '" required /></td></tr>';
            replacementsDiv.innerHTML += row;
        }
    });
    </script>
    <?php
}

function dpr_duplicate_pages($page_id, $find, $replacements) {
    $page = get_post($page_id);

    if (!$page) {
        echo '<div class="error"><p>Page not found!</p></div>';
        return;
    }

    foreach ($replacements as $replace) {
        $new_page = array(
            'post_title' => str_replace($find, $replace, $page->post_title),
            'post_content' => str_replace($find, $replace, $page->post_content),
            'post_status' => 'draft',
            'post_type' => $page->post_type,
            'post_author' => $page->post_author,
        );

        $new_page_id = wp_insert_post($new_page);

        if ($new_page_id) {
            echo '<div class="updated"><p>Page duplicated successfully with replacement: ' . $replace . '</p></div>';
        } else {
            echo '<div class="error"><p>Failed to duplicate page with replacement: ' . $replace . '</p></div>';
        }
    }
}
?>
