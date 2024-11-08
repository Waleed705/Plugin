<?php
/**
 * Template Name: Registration
 */
get_header();
wp_head();
?>

<div class="body">
    <div class="main">
        <h1>CREATE ACCOUNT</h1>
        <form id="registration-form" method="POST">
            <div class="registration-template-main">
                <div class="input">
                    <input type="text" placeholder="Your name" name="name" id="name">
                    <span id="name-error" class="error-message"></span>

                    <input type="email" placeholder="Enter email" name="email" id="email">
                    <span id="email-error" class="error-message"></span>

                    <input type="password" placeholder="Enter Password" name="password" id="current-password">
                    <span id="password-error" class="error-message"></span>
                </div>
                <div class="radiobtn">
                    <input type="checkbox" id="checkbox"> 
                    <label>I agree with all the statements in <u>Terms of service</u></label>
                    <span id="check-error" class="error-message"></span>
                </div>
                <div class="btn">
                    <input type="submit" value="Sign Up" class="submit" id="signup">
                </div>
                <div class="login">
                    <p>Already have an account? <a href="<?php echo( home_url( '/login' ) ); ?>">Login here</a></p>
                    <p id="response"></p>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
wp_footer();
get_footer();
?>
