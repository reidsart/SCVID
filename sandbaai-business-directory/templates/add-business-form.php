<?php
function sb_render_add_business_form() {
    ob_start();
    ?>
    <form method="post" enctype="multipart/form-data">
        <h2>Add Your Business</h2>

        <!-- Required Fields -->
        <label for="business_name">Business Name (required):</label>
        <input type="text" id="business_name" name="business_name" required>

        <label for="address">Address (required):</label>
        <input type="text" id="address" name="address" required>

        <label for="suburb">Suburb (default: Sandbaai):</label>
        <input type="text" id="suburb" name="suburb" value="Sandbaai">

        <label for="phone">Phone (required):</label>
        <input type="text" id="phone" name="phone" required>

        <label for="email">Email (required):</label>
        <input type="email" id="email" name="email" required>

        <label for="description">Description (required):</label>
        <textarea id="description" name="description" required></textarea>

        <label for="location">Location in Sandbaai? (yes/no):</label>
        <input type="radio" id="location_yes" name="location" value="yes" required> Yes
        <input type="radio" id="location_no" name="location" value="no" required> No

        <hr>

        <!-- Optional Fields -->
        <label for="website">Website:</label>
        <input type="url" id="website" name="website" placeholder="https://">

        <label for="whatsapp">WhatsApp Number:</label>
        <input type="text" id="whatsapp" name="whatsapp">

        <label for="facebook">Facebook Page URL:</label>
        <input type="url" id="facebook" name="facebook" placeholder="https://">

        <label for="logo">Logo (JPEG/PNG, max 500KB):</label>
        <input type="file" id="logo" name="logo" accept="image/jpeg, image/png">

        <label for="gallery">Photo Gallery (up to 5, max 2MB each):</label>
        <input type="file" id="gallery" name="gallery[]" accept="image/jpeg, image/png" multiple>

        <label for="tags">Tags (separate with commas, add up to 2):</label>
        <input type="text" id="tags" name="tags">

        <label for="address_privacy">Hide Address? (yes/no):</label>
        <input type="radio" id="address_privacy_yes" name="address_privacy" value="yes"> Yes
        <input type="radio" id="address_privacy_no" name="address_privacy" value="no" checked> No

        <label for="suggestions">Suggestions:</label>
        <textarea id="suggestions" name="suggestions"></textarea>

        <br>
        <input type="submit" name="sb_submit_business" value="Submit Business">
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('sb_add_business_form', 'sb_render_add_business_form');