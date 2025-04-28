<?php
function sb_render_add_business_form() {
    ob_start();
    ?>
    <form method="post" enctype="multipart/form-data">
        <h2>Add Your Business</h2>

        <!-- Required Fields -->
        <label for="business_name">Business Name (required):</label>
        <input type="text" id="business_name" name="post_title" required> <!-- Save as Post Title -->

        <label for="business_address">Business Address (required):</label>
        <input type="text" id="business_address" name="business_address" required>

        <label for="address_privacy">Hide Address? (yes/no):</label>
        <input type="radio" id="address_privacy_yes" name="address_privacy" value="1"> Yes
        <input type="radio" id="address_privacy_no" name="address_privacy" value="0" checked> No

        <br><label for="business_suburb">Business Suburb (default: Sandbaai):</label>
        <input type="text" id="business_suburb" name="business_suburb" value="Sandbaai" required>

        <label for="business_phone">Business Phone (required):</label>
        <input type="text" id="business_phone" name="business_phone" required>

        <label for="business_email">Business Email (required):</label>
        <input type="email" id="business_email" name="business_email" required>

        <label for="business_description">Business Description (required):</label>
        <textarea id="business_description" name="business_description" required></textarea>

        <!-- Optional Fields -->
        <label for="business_website">Business Website (optional):</label>
        <input type="text" id="business_website" name="business_website" placeholder="Enter your website (e.g., example.com)">

        <label for="business_whatsapp">Business WhatsApp Number:</label>
        <input type="text" id="business_whatsapp" name="business_whatsapp">

        <label for="facebook">Facebook Page URL (optional):</label>
        <input type="text" id="facebook" name="facebook" placeholder="Enter your Facebook page (e.g., facebook.com/yourpage)">

        <label for="tags">Select up to 2 categories that fit your business:</label>
        <table id="tags-table" border="1" cellpadding="5" cellspacing="0">
            <thead>
                <tr>
                    <th>Select</th>
                    <th>Tag Name</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $tags = get_terms(array('taxonomy' => 'business_tag', 'hide_empty' => false));
                if ($tags) {
                    foreach ($tags as $tag) {
                        echo '<tr>';
                        echo '<td><input type="checkbox" name="tags[]" value="' . esc_attr($tag->term_id) . '" onchange="limitTagSelection(this)"></td>';
                        echo '<td>' . esc_html($tag->name) . '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="2">No tags available.</td></tr>';
                }
                ?>
            </tbody>
        </table>

        <br>
        <input type="hidden" id="category" name="category" value="sb_business"> <!-- Default sb_business -->
        <br>
        <input type="submit" name="sb_submit_business" value="Submit Business">
    </form>

    <script>
        // JavaScript to limit tag selection to 2
        function limitTagSelection(checkbox) {
            const checkboxes = document.querySelectorAll('input[name="tags[]"]');
            const checked = Array.from(checkboxes).filter(cb => cb.checked);
            if (checked.length > 2) {
                alert('You can select up to 2 tags only.');
                checkbox.checked = false;
            }
        }
    </script>
    <?php
    return ob_get_clean();
}

add_shortcode('sb_add_business_form', 'sb_render_add_business_form');