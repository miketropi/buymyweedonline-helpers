<?php 
$active_age_gate = get_field('active_age_gate','options');
$data_age_gate = get_field('bh_age_gate_app','options');
$login_age_gate = $data_age_gate['bh_ignore_logged_in_age_gate'];
if($login_age_gate && is_user_logged_in()) {
  return;
}

$length_remember = 0;
if($data_age_gate['bh_remember_age_gate']) {
  $length_remember = $data_age_gate['bh_time_remember_age_gate'];
}

if($active_age_gate) {    
  ?>
    <div class="wrapper-age-gate-custom">
      <div class="overlay-custom-age-gate"></div>
      <div class="overlay-custom-age-gate-new"></div>
      <div class="inamate-loading-age-gate">
          <svg version="1.1" id="L5" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 100 100" enable-background="new 0 0 0 0" xml:space="preserve">
            <circle fill="currentColor" stroke="none" cx="6" cy="50" r="6">
                <animateTransform attributeName="transform" dur="1s" type="translate" values="0 15 ; 0 -15; 0 15" repeatCount="indefinite" begin="0.1"></animateTransform>
            </circle>
            <circle fill="currentColor" stroke="none" cx="30" cy="50" r="6">
                <animateTransform attributeName="transform" dur="1s" type="translate" values="0 10 ; 0 -10; 0 10" repeatCount="indefinite" begin="0.2"></animateTransform>
            </circle>
            <circle fill="currentColor" stroke="none" cx="54" cy="50" r="6">
                <animateTransform attributeName="transform" dur="1s" type="translate" values="0 5 ; 0 -5; 0 5" repeatCount="indefinite" begin="0.3"></animateTransform>
            </circle>
          </svg>  
        </div>
      <input type="hidden" class="remember-length-age-gate" value="<?php echo  $length_remember?>" />
      <div class="age-gate-note-content">
        <?php 
          if(!empty($data_age_gate['bh_content_restrict_age_gate'])) {
            $age_confirm = $data_age_gate['bh_default_age'];
            ?>
              <div class="content-restrict-age-gate">
                <?php 
                  echo sprintf(__($data_age_gate['bh_content_restrict_age_gate'], 'your__text'), $age_confirm);
                ?>
              </div>
            <?php
          } 
        ?>
        <div class="button-confirm-age-gate">
            <button class="confirm-yes">Yes</button>
            <button class="confirm-no" data-redirect="<?php echo $data_age_gate['bh_redirect_failures_age_gate']?>">
              No
            </button>
        </div>
        <div class="text-age-gate-error">
          <?php 
            echo $data_age_gate['bh_age_gate_failed_error'];
          ?>
        </div>
      </div>
    </div>
  <?php
}

?>