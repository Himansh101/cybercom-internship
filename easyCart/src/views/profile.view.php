<?php
$this->partial('header', ['pageTitle' => $pageTitle, 'extraStyles' => $extraStyles ?? []]);
?>

<div class="profile-container">
    <div class="profile-header">
        <div class="profile-avatar">
            <i class="ri-user-line"></i>
        </div>
        <div class="profile-title">
            <h1><?php echo htmlspecialchars($profile['name']); ?></h1>
            <p class="profile-email"><?php echo htmlspecialchars($profile['email']); ?></p>
            <span class="member-since">Member since <?php echo date('M Y', strtotime($profile['created_at'])); ?></span>
        </div>
    </div>

    <form id="profile-form" class="profile-form">
        <input type="hidden" name="action" value="update_profile">

        <!-- Personal Information -->
        <div class="profile-section">
            <h2><i class="ri-user-settings-line"></i> Personal Information</h2>

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($profile['name']); ?>"
                    minlength="3" pattern="[a-zA-Z\s]+" required>
                <span class="error-message" id="name-error"></span>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                        value="<?php echo htmlspecialchars($profile['email']); ?>" required>
                    <span class="error-message" id="email-error"></span>
                </div>
                <div class="form-group">
                    <label for="mobile">Mobile Number</label>
                    <input type="tel" id="mobile" name="mobile"
                        value="<?php echo htmlspecialchars($profile['mobile'] ?? ''); ?>" pattern="(\+91)[6-9][0-9]{9}"
                        placeholder="+919876543210">
                    <span class="error-message" id="mobile-error"></span>
                </div>
            </div>
        </div>

        <!-- Saved Address -->
        <div class="profile-section">
            <h2><i class="ri-map-pin-line"></i> Saved Address</h2>
            <p class="section-desc">This address will be pre-filled during checkout.</p>

            <div class="form-group">
                <label for="street_address">Street Address</label>
                <textarea id="street_address" name="street_address" rows="2"
                    placeholder="House No, Street, Locality"><?php echo htmlspecialchars($profile['street_address'] ?? ''); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city"
                        value="<?php echo htmlspecialchars($profile['city'] ?? ''); ?>" placeholder="Mumbai">
                </div>
                <div class="form-group">
                    <label for="pincode">Pincode</label>
                    <input type="text" id="pincode" name="pincode"
                        value="<?php echo htmlspecialchars($profile['pincode'] ?? ''); ?>" pattern="[1-9][0-9]{5}"
                        placeholder="400001">
                    <span class="error-message" id="pincode-error"></span>
                </div>
            </div>
        </div>

        <!-- Password Section -->
        <div class="profile-section">
            <h2><i class="ri-lock-line"></i> Change Password</h2>
            <p class="section-desc">Leave blank to keep current password.</p>

            <div class="form-row">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password"
                        placeholder="Enter current password">
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" minlength="8"
                        placeholder="Min 8 characters">
                    <span class="error-message" id="new_password-error"></span>
                </div>
            </div>
        </div>

        <div class="profile-actions">
            <button type="submit" class="btn btn-primary" id="save-btn">
                <i class="ri-save-line"></i> Save Changes
            </button>
        </div>
    </form>
</div>

<?php
$this->partial('footer', ['extraScripts' => $extraScripts ?? []]);
?>