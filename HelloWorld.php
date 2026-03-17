<?php
/**
 * GVS Icon Media - Registration Page
 * Multi-type registration (primarily for affiliates)
 */
require_once __DIR__ . '/bootstrap.php';

// If already logged in, redirect to dashboard
if (is_logged_in()) {
    redirect(get_user_type() . '/dashboard.php');
}

$error = '';
$success = false;
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Invalid security token. Please try again.';
    } else {
        // Get form data
        $formData = [
            'username' => sanitize($_POST['username'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'password_confirm' => $_POST['password_confirm'] ?? '',
            'first_name' => sanitize($_POST['first_name'] ?? ''),
            'last_name' => sanitize($_POST['last_name'] ?? ''),
            'company' => sanitize($_POST['company'] ?? ''),
            'skype' => sanitize($_POST['skype'] ?? ''),
            'telegram' => sanitize($_POST['telegram'] ?? ''),
        ];

        // Validate
        if (empty($formData['username']) || empty($formData['email']) || empty($formData['password'])) {
            $error = 'Please fill in all required fields.';
        } elseif (!validate_email($formData['email'])) {
            $error = 'Invalid email address.';
        } elseif (strlen($formData['password']) < PASSWORD_MIN_LENGTH) {
            $error = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
        } elseif ($formData['password'] !== $formData['password_confirm']) {
            $error = 'Passwords do not match.';
        } elseif (strlen($formData['username']) < 3) {
            $error = 'Username must be at least 3 characters.';
        } else {
            try {
                // Check if username or email exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$formData['username'], $formData['email']]);

                if ($stmt->fetch()) {
                    $error = 'Username or email already exists.';
                } else {
                    // Create user account
                    $pdo->beginTransaction();

                    $passwordHash = password_hash($formData['password'], PASSWORD_DEFAULT);

                    // Insert into users table (which contains most profile fields in this schema)
                    $stmt = $pdo->prepare("
    INSERT INTO users 
    (username, email, password_hash, user_type, status, email_verified,
     first_name, last_name, company_name, phone, skype, telegram) 
    VALUES (?, ?, ?, 'affiliate', 'pending', 0, ?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    $formData['username'],
    $formData['email'],
    $passwordHash,
    $formData['first_name'] ?: NULL,
    $formData['last_name'] ?: NULL,
    $formData['company'] ?: NULL,
    NULL, // phone
    $formData['skype'] ?: NULL,
    $formData['telegram'] ?: NULL
]);

                    $userId = $pdo->lastInsertId();

                    // Create basic user profile entry
                    $stmt = $pdo->prepare("
                        INSERT INTO user_profiles 
                        (user_id, created_at) 
                        VALUES (?, NOW())
                    ");
                    $stmt->execute([$userId]);

                    // Create affiliate account with auto-generated IDs
                    $affiliateId = 'AFF' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
                    $referralCode = substr(md5(uniqid(rand(), true)), 0, 8);

                    $stmt = $pdo->prepare("
                        INSERT INTO affiliate_accounts 
                        (user_id, affiliate_id, referral_code, status, balance, created_at) 
                        VALUES (?, ?, ?, 'pending', 0.00, NOW())
                    ");
                    $stmt->execute([$userId, $affiliateId, $referralCode]);

                    $pdo->commit();

                    log_activity('registration', "New affiliate registered: {$formData['username']}", $pdo);

                    $success = true;
                    $formData = []; // Clear form
                }
            } catch (PDOException $e) {
                $pdo->rollBack();
                if (DEBUG_MODE) {
                    $error = 'Registration failed: ' . $e->getMessage();
                } else {
                    $error = 'Registration failed. Please try again.';
                }
                error_log('Registration error: ' . $e->getMessage());
            }
        }
    }
}

$pageTitle = 'Register';
require_once 'includes/header.php';
?>

<div class="register-container">
    <div class="register-card">
        <div class="register-header">
            <h1>Create Account</h1>
            <p>Join <?php echo e(SITE_NAME); ?> as an Affiliate</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <h4>Registration Successful!</h4>
                <p>Your account has been created and is pending approval. You will receive an email once your account is
                    activated.</p>
                <p><a href="<?php echo url('login.php'); ?>" class="btn btn-primary">Go to Login</a></p>
            </div>
        <?php else: ?>
            <form method="POST" action="" class="register-form">
                <?php echo csrf_field(); ?>

                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" class="form-control"
                            value="<?php echo e($formData['username'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" class="form-control"
                            value="<?php echo e($formData['email'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" class="form-control"
                            value="<?php echo e($formData['first_name'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" class="form-control"
                            value="<?php echo e($formData['last_name'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="company">Company Name (Optional)</label>
                    <input type="text" id="company" name="company" class="form-control"
                        value="<?php echo e($formData['company'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="skype">Skype ID</label>
                        <input type="text" id="skype" name="skype" class="form-control"
                            value="<?php echo e($formData['skype'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="telegram">Telegram</label>
                        <input type="text" id="telegram" name="telegram" class="form-control"
                            value="<?php echo e($formData['telegram'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                        <small>Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters</small>
                    </div>

                    <div class="form-group">
                        <label for="password_confirm">Confirm Password *</label>
                        <input type="password" id="password_confirm" name="password_confirm" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="terms" value="1" required>
                        <span>I agree to the <a href="<?php echo url('terms.php'); ?>" target="_blank">Terms of
                                Service</a></span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Create Account
                </button>
            </form>

            <div class="register-footer">
                Already have an account? <a href="<?php echo url('login.php'); ?>">Login here</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>