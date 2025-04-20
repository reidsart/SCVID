<?php
function sb_render_add_business_form() {
    ob_start();
    ?>
    <form method="post" enctype="multipart/form-data">
        <h2>Add Your Business</h2>

        <!-- Required Fields -->
        <label for="business_name">Business Name (required):</label>
        <input type="text" id="business_name" name="business_name" required>

        <label for="address">Business Address (required):</label>
        <input type="text" id="address" name="address" required>

        <label for="address_privacy">Hide Address? (yes/no):</label>
        <input type="radio" id="address_privacy_yes" name="address_privacy" value="yes"> Yes
        <input type="radio" id="address_privacy_no" name="address_privacy" value="no" checked> No

        <label for="suburb">Business Suburb (default: Sandbaai):</label>
        <input type="text" id="suburb" name="suburb" value="Sandbaai">

        <label for="phone">Business Phone (required):</label>
        <input type="text" id="phone" name="phone" required>

        <label for="email">Business Email (required):</label>
        <input type="email" id="email" name="email" required>

        <label for="description">Business Description (required):</label>
        <textarea id="description" name="description" required></textarea>

        <hr>

        <!-- Optional Fields -->
        <label for="website">Business Website:</label>
        <input type="url" id="website" name="website">

        <label for="whatsapp">Business WhatsApp Number:</label>
        <input type="text" id="whatsapp" name="whatsapp">

        <label for="facebook">Facebook Page URL:</label>
        <input type="url" id="facebook" name="facebook">

        <label for="logo">Business Logo (JPEG/PNG, max 500KB):</label>
        <input type="file" id="logo" name="logo" accept="image/jpeg, image/png">

        <label for="gallery">Photo Gallery (up to 5, max 2MB each):</label>
        <input type="file" id="gallery" name="gallery[]" accept="image/jpeg, image/png" multiple>

        <label for="tags">Tags (select up to 2):</label>
        <div id="tags-table">
            <?php
            $tags = get_tags(); // Fetch WordPress post tags
            if ($tags) {
                foreach ($tags as $tag) {
                    echo '<label>';
                    echo '<input type="checkbox" name="tags[]" value="' . esc_attr($tag->term_id) . '" onchange="limitTagSelection()"> ';
                    echo esc_html($tag->name);
                    echo '</label><br>';
                }
            } else {
                echo '<p>No tags available.</p>';
            }
            ?>
        </div>

        <br>
        <label for="suggestions">Suggestions:</label>
        <textarea id="suggestions" name="suggestions"></textarea>

        <input type="hidden" id="category" name="category" value=""> <!-- Default empty -->
        <br>
        <input type="submit" name="sb_submit_business" value="Submit Business">
    </form>

    <script>
        // JavaScript to limit tag selection to 2
        function limitTagSelection() {
            const checkboxes = document.querySelectorAll('input[name="tags[]"]');
            const checked = Array.from(checkboxes).filter(cb => cb.checked);
            if (checked.length > 2) {
                alert('You can select up to 2 tags only.');
                this.checked = false;
            }
        }

        // JavaScript to check Suburb field and set category
        document.getElementById('suburb').addEventListener('change', function () {
            const categoryInput = document.getElementById('category');
            if (this.value.toLowerCase() === 'sandbaai') {
                categoryInput.value = 'sb_business';
            } else {
                categoryInput.value = '';
            }
        });
    </script>
    <?php
    return ob_get_clean();
}

// Automatically add https:// to website or Facebook URL if missing
add_action('pre_post', function () {
    if (!empty($_POST['website']) && !preg_match('/^https?:\/\//', $_POST['website'])) {
        $_POST['website'] = 'https://' . $_POST['website'];
    }
    if (!empty($_POST['facebook']) && !preg_match('/^https?:\/\//', $_POST['facebook'])) {
        $_POST['facebook'] = 'https://' . $_POST['facebook'];
    }
});

add_shortcode('sb_add_business_form', 'sb_render_add_business_form');
