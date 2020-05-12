<?php

namespace WBCR\Titan\Tweaks;

require_once(dirname(__FILE__) . '/../login-interstitial/class-session.php');
require_once(dirname(__FILE__) . '/../login-interstitial/class-abstract-login-interstitial.php');
require_once(dirname(__FILE__) . '/../login-interstitial/class-config-driven.php');

/**
 * Class \WBCR\Titan\Tweaks\Login_Interstitial
 */
class Login_Interstitial {

	const SHOW_AFTER_LOGIN = 'titan_after_interstitial';
	const AJAX = 'titan-login-interstitial-ajax';

	const R_USER = 'titan_interstitial_user';
	const R_TOKEN = 'titan_interstitial_token';
	const R_SESSION = 'titan_interstitial_session';
	const R_EXPIRED = 'titan_interstitial_expired';
	const R_INTERSTITIAL = 'titan_interstitial';
	const R_ASYNC_ACTION = 'titan_interstitial_async_action';
	const R_GET_STATE = 'titan_interstitial_get_state';
	const R_SAME_BROWSER_DENY = 'titan_interstitial_browser_deny';

	const C_SAME_BROWSER = 'titan_interstitial_browser';
	const SAME_BROWSER_PAYLOAD = 'same-browser';

	/** @var \WBCR\Titan\Tweaks\Login_Interstitial_Base[] */
	private $registered = array();

	/** @var \WP_Error */
	private $error;

	/** @var string */
	private $session_token;

	/** @var Login_Interstitial_Session|null */
	private $current_session;

	/**
	 * Initialize the module.
	 *
	 * This registers hooks and filters.
	 */
	public function run()
	{

		/**
		 * Fires when the Login Interstitial framework is initialized.
		 *
		 * @param Login_Interstitial
		 */
		do_action('titan_login_interstitial_init', $this);

		if( !$this->registered ) {
			return;
		}

		uasort($this->registered, array($this, '_sort_interstitials'));

		add_action('login_enqueue_scripts', array($this, 'enqueue'));
		add_action('wp_login', array($this, 'wp_login'), -1000, 2);
		add_action('wp_login_errors', array($this, 'handle_token_expired'));
		add_action('login_init', array($this, 'force_interstitial'));
		add_action('login_form', array($this, 'ferry_after_login'));
		add_filter('auth_cookie', array($this, 'capture_session_token'), 10, 5);

		add_action('wp_ajax_' . self::AJAX, array($this, 'ajax_handler'));
		add_action('wp_ajax_nopriv_' . self::AJAX, array($this, 'ajax_handler'));

		foreach($this->registered as $id => $interstitial) {
			if( $interstitial->has_async_action() ) {
				add_action("login_form_titan-{$id}", array($this, 'async_action'), 8);
			}

			add_action("login_form_titan-{$id}", array($this, 'submit'), 9);
			add_action("login_form_titan-{$id}", array($this, 'display'));
		}
	}

	/**
	 * Register an interstitial.
	 *
	 * @param string $slug
	 * @param Login_Interstitial|callable $render_or_class
	 * @param array $opts
	 *
	 * @return bool
	 * @api
	 *
	 */
	public function register($slug, $render_or_class, $opts = array())
	{

		if( $render_or_class instanceof Login_Interstitial ) {
			$this->registered[$slug] = $render_or_class;

			return true;
		}

		$opts = wp_parse_args($opts, array(
			'force_completion' => true, // Will logout the user's session before displaying the interstitial.
			'show_to_user' => true, // Boolean or callable.
			'wp_login_only' => false, // Only show the interstitial if the login form is submitted from wp-login.php,
			'submit' => false, // Callable called with user when submitting the form.
			'async_action' => false, // Callable called when a user clicks a link to perform an interstitial action.
			'info_message' => false,
			'after_submit' => false,
			'ajax_handler' => false,
			'priority' => 5,
		));

		$opts['render'] = $render_or_class;

		try {
			$this->registered[$slug] = new Login_Interstitial_Config_Driven($opts);
		} catch( Exception $e ) {
			/** @noinspection ForgottenDebugOutputInspection */
			error_log($e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * Show the interstitial.
	 *
	 * @param Login_Interstitial_Session $session
	 *
	 * @return void
	 * @api
	 *
	 */
	public function show_interstitial(Login_Interstitial_Session $session)
	{

		if( !isset($this->registered[$session->get_current_interstitial()]) ) {
			return;
		}

		$this->current_session = $session;

		$interstitial = $this->registered[$session->get_current_interstitial()];

		if( $interstitial->is_completion_forced($session) ) {
			$this->destroy_session_token($session->get_user());
		}

		$this->login_html($session);
		die;
	}

	/**
	 * Handle proceeding the session to the next interstitial.
	 *
	 * @param Login_Interstitial_Session $session
	 *
	 * @return bool
	 * @api
	 *
	 */
	public function proceed_to_next(Login_Interstitial_Session $session)
	{

		$current = $session->get_current_interstitial();
		$session->add_completed_interstitial($current);

		$session->set_current_interstitial($this->get_next_interstitial($session));

		return $session->save();
	}

	/**
	 * Get the current interstitial session.
	 *
	 * @return Login_Interstitial_Session|null
	 * @api
	 *
	 */
	public function get_current_session()
	{
		return $this->current_session;
	}

	/**
	 * Get the URL to an async action.
	 *
	 * @param Login_Interstitial_Session $session
	 * @param string $action
	 *
	 * @return string
	 * @api
	 *
	 */
	public function get_async_action_url(Login_Interstitial_Session $session, $action)
	{

		$url = $this->get_base_wp_login_url();
		$url = add_query_arg(array(
			'action' => "titan-{$session->get_current_interstitial()}",
			self::R_USER => $session->get_user()->ID,
			self::R_TOKEN => $session->get_signature_for_payload($action),
			self::R_SESSION => $session->get_id(),
			self::R_ASYNC_ACTION => $action,
		), $url);

		return $url;
	}

	/**
	 * Initialize the same browser functionality.
	 *
	 * This sets a cookie with a signature payload.
	 *
	 * @param Login_Interstitial_Session $session
	 * @api
	 *
	 */
	public function initialize_same_browser(Login_Interstitial_Session $session)
	{
		self::set_cookie(self::C_SAME_BROWSER, $session->get_signature_for_payload(self::SAME_BROWSER_PAYLOAD));

		/**
		 * Fires when the login interstitial initializes the Same Browser API for async actions.
		 *
		 * @param Login_Interstitial_Session $session
		 */
		do_action('titan_login_interstitial_initialize_same_browser', $session);
	}

	/**
	 * Register the interstitial helper script.
	 *
	 * @internal
	 */
	public function enqueue()
	{
		wp_register_script('titan-login-interstitial-util', WTITAN_PLUGIN_URL . '/includes/tweaks/password-requirements/assets/js/login-interstitial-util.js', array(
			'jquery',
			'wp-util'
		), \WBCR\Titan\Plugin::app()->getPluginVersion());
		wp_add_inline_script('titan-login-interstitial-util', '(function() { window.wtitanLoginInterstitial = new WTitanLoginInterstitial(); window.wtitanLoginInterstitial.init() })()');
	}

	/**
	 * During the login process, check if we have any interstitials to display, and display them.
	 *
	 * @param string $username
	 * @param \WP_User $user
	 * @internal
	 *
	 */
	public function wp_login($username, $user = null)
	{
		$user = $user ? $user : wp_get_current_user();

		if( !$user || !$user->exists() ) {
			return;
		}

		foreach($this->get_applicable_interstitials($user) as $action => $opts) {
			$session = Login_Interstitial_Session::create($user, $action);

			if( is_wp_error($session) ) {
				wp_die($session);
			}

			$session->initialize_from_global_state();

			if( isset($_REQUEST[self::SHOW_AFTER_LOGIN], $this->registered[$_REQUEST[self::SHOW_AFTER_LOGIN]]) ) {
				$session->add_show_after($_REQUEST[self::SHOW_AFTER_LOGIN]);
			}

			$session->save();

			$this->show_interstitial($session);
		}

		if( isset($_REQUEST[self::SHOW_AFTER_LOGIN], $this->registered[$_REQUEST[self::SHOW_AFTER_LOGIN]]) ) {
			$session = Login_Interstitial_Session::create($user, $_REQUEST[self::SHOW_AFTER_LOGIN]);

			if( is_wp_error($session) ) {
				wp_die($session);
			}

			$session->initialize_from_global_state();
			$session->save();
			$this->show_interstitial($session);
		}
	}

	/**
	 * Add a message that the interstitial expired.
	 *
	 * @param \WP_Error $errors
	 *
	 * @return \WP_Error
	 * @internal
	 *
	 */
	public function handle_token_expired($errors)
	{

		if( isset($_GET[self::R_EXPIRED]) ) {
			$errors->add('titan-login-interstitial-invalid-token', esc_html__('Sorry, this request has expired. Please log in again.', 'titan-security'));
		}

		return $errors;
	}

	/**
	 * Force the requested interstitial to be displayed if the user is already logged-in.
	 *
	 * @internal
	 */
	public function force_interstitial()
	{

		if( empty($_REQUEST[self::SHOW_AFTER_LOGIN]) || !isset($this->registered[$_REQUEST[self::SHOW_AFTER_LOGIN]]) ) {
			return;
		}

		$slug = $_REQUEST[self::SHOW_AFTER_LOGIN];

		if( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			return;
		}

		if( !is_user_logged_in() ) {
			return;
		}

		$user = wp_get_current_user();
		$interstitial = $this->registered[$slug];

		if( !$interstitial->show_to_user($user, true) ) {
			wp_safe_redirect(admin_url());
			die;
		}

		$session = Login_Interstitial_Session::create($user, $slug);
		$this->show_interstitial($session);
	}

	/**
	 * Ferry the after login interstitial query var into the form.
	 *
	 * @internal
	 */
	public function ferry_after_login()
	{
		if( !empty($_REQUEST[self::SHOW_AFTER_LOGIN]) && isset($this->registered[$_REQUEST[self::SHOW_AFTER_LOGIN]]) ) {
			echo '<input type="hidden" name="' . esc_attr(self::SHOW_AFTER_LOGIN) . '" value="' . esc_attr($_REQUEST[self::SHOW_AFTER_LOGIN]) . '">';
		}
	}

	/**
	 * Capture the session token to log out the user.
	 *
	 * @param string $cookie
	 * @param int $user_id
	 * @param int $expiration
	 * @param string $scheme
	 * @param string $token
	 *
	 * @return string
	 * @internal
	 *
	 */
	public function capture_session_token($cookie, $user_id, $expiration, $scheme, $token)
	{
		$this->session_token = $token;

		return $cookie;
	}

	/**
	 * Handle submitting the interstitial form.
	 *
	 * @internal
	 */
	public function submit()
	{
		$session = $this->get_and_verify_session();
		$slug = $session->get_current_interstitial();

		// If we think we have all finished all the interstitials.
		// We need to check because another process may have moved the interstitial forward.
		if( !$slug ) {
			// Double check to ensure we are actually finished.
			if( $next = $this->get_next_interstitial($session) ) {
				// If not, display the next interstitial.
				$session->set_current_interstitial($next);
				$session->save();
			}

			$this->do_next_step($session);
		}

		if( 'POST' !== $_SERVER['REQUEST_METHOD'] || empty($_POST['action']) ) {
			return;
		}

		if( empty($this->registered[$slug]) ) {
			return;
		}

		$requested_slug = substr($_POST['action'], strlen('titan-'));

		if( $slug !== $requested_slug ) {
			// If we have already completed the action that was requested, then just display
			// the new interstitial.
			if( $session->is_interstitial_completed($requested_slug) ) {
				$this->show_interstitial($session);
			} else {
				$this->redirect_invalid_token();
			}
		}

		$this->current_session = $session;

		$interstitial = $this->registered[$slug];

		if( $interstitial->has_submit() ) {
			$maybe_error = $interstitial->submit($session, $_POST);

			if( is_wp_error($maybe_error) ) {
				$this->error = $maybe_error;

				return;
			}
		}

		$interstitial->after_submit($session, $_POST);

		$this->proceed_to_next($session);
		$this->do_next_step($session);
	}

	/**
	 * Handle an async GET action to the interstitial.
	 *
	 * @internal
	 */
	public function async_action()
	{

		if( empty($_GET[self::R_ASYNC_ACTION]) ) {
			return;
		}

		$session = $this->get_and_verify_session_for_async_action(true);

		if( is_wp_error($session) ) {
			$this->display_wp_login_message($session);
		}

		$action = $_GET[self::R_ASYNC_ACTION];
		$slug = $session->get_current_interstitial();

		if( empty($this->registered[$slug]) ) {
			$this->display_wp_login_message(new \WP_Error('unsupported', esc_html__('Unsupported Interstitial. Please login again.', 'titan-security')));
		}

		$interstitial = $this->registered[$slug];

		if( !$interstitial->has_async_action() ) {
			$this->display_wp_login_message(new \WP_Error('unsupported', esc_html__('Unsupported Interstitial. Please login again.', 'titan-security')));
		}

		if( isset($_REQUEST[self::R_SAME_BROWSER_DENY]) ) {
			$session->delete();
			wp_redirect(wp_login_url());
			die;
		}

		$args = array(
			'same_browser' => false,
		);

		if( isset($_COOKIE[self::C_SAME_BROWSER]) && true === $session->verify_signature_for_payload(self::SAME_BROWSER_PAYLOAD, $_COOKIE[self::C_SAME_BROWSER]) ) {
			$args['same_browser'] = true;
		}

		$this->current_session = $session;

		if( !$args['same_browser'] && 'GET' === $_SERVER['REQUEST_METHOD'] ) {
			$this->display_async_action_confirmation($session, $action);
		}

		$result = $interstitial->handle_async_action($session, $action, $args);

		if( null === $result ) {
			$this->display_wp_login_message(new \WP_Error('unsupported', esc_html__('Unsupported Interstitial. Please login again.', 'titan-security')));
		}

		if( is_wp_error($result) ) {
			$this->display_wp_login_message($result);
		}

		if( true === $result ) {
			$result = array();
		}

		if( $args['same_browser'] && empty($result['allow_same_browser']) ) {
			$this->do_next_step($session, array(
				'delete' => false,
				'allow_interim' => false,
			));
		}

		$result = wp_parse_args($result, array(
			'message' => esc_html__('Action processed. Please continue in your original browser.', 'titan-security'),
			'title' => esc_html__('Action Processed', 'titan-security'),
		));

		$this->display_wp_login_message($result);
	}

	/**
	 * Ajax Handler.
	 *
	 * @internal
	 */
	public function ajax_handler()
	{

		$session = $this->get_and_verify_session(true);
		$get_state = !empty($_REQUEST[self::R_GET_STATE]);

		if( is_wp_error($session) ) {
			if( $get_state && is_user_logged_in() ) {
				wp_send_json_success(array(
					'logged_in' => true,
				));
			}

			wp_send_json_error(array('message' => $session->get_error_message()));
		}

		if( $get_state ) {
			wp_send_json_success(array(
				'current' => $session->get_current_interstitial(),
				'completed' => $session->get_completed_interstitials(),
				'state' => $session->get_state(),
			));
		}

		$slug = $session->get_current_interstitial();

		if( empty($this->registered[$slug]) ) {
			wp_send_json_error(array('message' => esc_html__('Invalid Interstitial Action', 'titan-security')));
		}

		$interstitial = $this->registered[$slug];

		if( !$interstitial->has_ajax_handlers() ) {
			wp_send_json_error(array('message' => esc_html__('Invalid Interstitial Action', 'titan-security')));
		}

		$data = $_POST;
		unset($data[self::R_USER], $data[self::R_TOKEN], $data[self::R_SESSION]);

		$this->current_session = $session;
		$interstitial->handle_ajax($session, $data);
	}

	/**
	 * Handle displaying the interstitial form.
	 *
	 * @internal
	 */
	public function display()
	{

		$action = substr(current_action(), strlen('login_form_titan-'));

		if( empty($this->registered[$action]) ) {
			return;
		}

		$interstitial = $this->registered[$action];
		$session = $this->get_and_verify_session();

		if( !$interstitial->show_to_user($session->get_user(), $session->is_current_requested()) ) {
			wp_safe_redirect(set_url_scheme(wp_login_url(), 'login_post'));
			die;
		}

		$this->login_html($session);
		die;
	}

	/**
	 * Display an interstitial form during the login process.
	 *
	 * @param Login_Interstitial_Session $session
	 * @internal
	 *
	 */
	protected function login_html(Login_Interstitial_Session $session)
	{

		$user = $session->get_user();
		$action = $session->get_current_interstitial();
		$interstitial = $this->registered[$action];

		$wp_login_url = $this->get_base_wp_login_url();
		$wp_login_url = add_query_arg('action', "titan-{$action}", $wp_login_url);

		$interstitial->pre_render($session);

		// Prevent JetPack from attempting to SSO the update password form.
		add_filter('jetpack_sso_allowed_actions', '__return_empty_array');

		if( !function_exists('login_header') ) {
			require_once(dirname(__FILE__) . '/functions-login-header.php');
		}

		login_header();

		wp_enqueue_script('titan-login-interstitial-util');
		?>

		<?php if( $this->error ) : ?>
		<div id="login-error" class="message" style="border-left-color: #dc3232;">
			<?php echo $this->error->get_error_message(); ?>
		</div>
	<?php elseif( $message = $interstitial->get_info_message($session) ): ?>
		<p class="message"><?php echo $message; ?></p>
	<?php endif; ?>

		<form name="titan-<?php echo esc_attr($action); ?>" id="titan-<?php echo esc_attr($action); ?>"
		      action="<?php echo esc_url($wp_login_url); ?>" method="post" autocomplete="off">

			<?php $interstitial->render($session, compact('wp_login_url')); ?>

			<input type="hidden" name="action" value="<?php echo esc_attr("titan-{$action}"); ?>">

			<input type="hidden" name="<?php echo esc_attr(self::R_USER) ?>" value="<?php echo esc_attr($user->ID); ?>">
			<input type="hidden" name="<?php echo esc_attr(self::R_TOKEN) ?>" value="<?php echo esc_attr($session->get_signature()); ?>">
			<input type="hidden" name="<?php echo esc_attr(self::R_SESSION) ?>" value="<?php echo esc_attr($session->get_id()); ?>">
		</form>

		<p id="backtoblog">
			<a href="<?php echo esc_url(home_url('/')); ?>" title="<?php esc_attr_e('Are you lost?', 'titan-security'); ?>">
				<?php echo esc_html(sprintf(__('&larr; Back to %s', 'titan-security'), get_bloginfo('title', 'display'))); ?>
			</a>
		</p>

		</div>
		<?php do_action('login_footer'); ?>
		<div class="clear"></div>
		</body>
		</html>
		<?php
	}

	/**
	 * Handle the interim login screen.
	 */
	private function interim_login()
	{

		if( !function_exists('login_header') ) {
			require_once(dirname(__FILE__) . '/includes/function.login-header.php');
		}

		$GLOBALS['interim_login'] = 'success';
		$customize_login = isset($_REQUEST['customize-login']);

		if( $customize_login ) {
			wp_enqueue_script('customize-base');
		}

		login_header('', '<p class="message">' . __('You have logged in successfully.') . '</p>');
		?>
		</div>
		<?php

		do_action('login_footer'); ?>

		<?php if( $customize_login ) : ?>
		<script type="text/javascript">
			setTimeout(function() {
				new wp.customize.Messenger({
					url: '<?php echo wp_customize_url(); ?>',
					channel: 'login',
				}).send('login');
			}, 1000);
		</script>
	<?php endif; ?>

		</body></html>
		<?php die;
	}

	/**
	 * Display a message on the WP-Login screen.
	 *
	 * @param \WP_Error|array $message
	 */
	private function display_wp_login_message($message)
	{
		if( !function_exists('login_header') ) {
			require_once(dirname(__FILE__) . '/includes/function.login-header.php');
		}

		login_header();

		?>
		<?php if( is_wp_error($message) ) : ?>
		<div id="login-error" class="message" style="border-left-color: #dc3232;">
			<?php echo $message->get_error_message(); ?>
		</div>
	<?php elseif( !empty($message['message']) ): ?>
		<p class="message"><?php echo $message['message']; ?></p>
	<?php endif; ?>

		<p id="backtoblog">
			<a href="<?php echo esc_url(home_url('/')); ?>" title="<?php esc_attr_e('Are you lost?', 'titan-security'); ?>">
				<?php echo esc_html(sprintf(__('&larr; Back to %s', 'titan-security'), get_bloginfo('title', 'display'))); ?>
			</a>
		</p>

		</div>
		<?php do_action('login_footer'); ?>
		<div class="clear"></div>
		</body>
		</html>
		<?php
		die;
	}

	/**
	 * Display a confirmation button for an async action.
	 *
	 * @param Login_Interstitial_Session $session
	 * @param string $action
	 */
	private function display_async_action_confirmation(Login_Interstitial_Session $session, $action)
	{
		if( !function_exists('login_header') ) {
			require_once(dirname(__FILE__) . '/includes/function.login-header.php');
		}

		$form_action = $this->get_async_action_url($session, $action);

		login_header();
		?>
		<style type="text/css">
			.login h2 {
				margin-bottom: 10px;
				font-size: 14px;
			}

			.titan-login-interstitial-confirm-async-action {
				vertical-align: top;
				display: block;
				text-decoration: none;
				height: 28px;
				margin: 0 0 15px 0;
				cursor: pointer;
				-webkit-appearance: none;
				border-radius: 3px;
				white-space: nowrap;
				box-sizing: border-box;
				background: #0083E3;
				color: #fff;
				text-shadow: none;
				padding: 20px 30px;
				line-height: 0;
				box-shadow: none;
				font-weight: 300;
				font-size: 1.2em;
				border: none;
				width: 100%;
				text-align: center;
			}

			.titan-login-interstitial-confirm-async-action:last-child {
				margin-bottom: 0;
			}

			.titan-login-interstitial-confirm-async-action:hover,
			.titan-login-interstitial-confirm-async-action:focus {
				background: #006799;
				color: #fff;
			}

			.titan-login-interstitial-confirm-async-action.titan-login-interstitial-confirm-async-action--deny {
				background: #d54e21;
			}

			.titan-login-interstitial-confirm-async-action.titan-login-interstitial-confirm-async-action--deny:hover,
			.titan-login-interstitial-confirm-async-action.titan-login-interstitial-confirm-async-action--deny:focus {
				background: #983818;
			}
		</style>
		<form action="<?php echo esc_url($form_action); ?>" method="post" autocomplete="off">

			<h2><?php esc_html_e('Please Verify the Login Request', 'titan-security'); ?></h2>

			<?php do_action('titan_login_interstitial_async_action_confirmation_before_confirm', $session, $action); ?>

			<button class="titan-login-interstitial-confirm-async-action">
				<?php esc_html_e('Confirm Login', 'titan-security'); ?>
			</button>
			<button name="<?php echo esc_attr(self::R_SAME_BROWSER_DENY); ?>" class="titan-login-interstitial-confirm-async-action titan-login-interstitial-confirm-async-action--deny">
				<?php esc_html_e('Deny Login', 'titan-security'); ?>
			</button>
		</form>

		<p id="backtoblog">
			<a href="<?php echo esc_url(home_url('/')); ?>" title="<?php esc_attr_e('Are you lost?', 'titan-security'); ?>">
				<?php echo esc_html(sprintf(__('&larr; Back to %s', 'titan-security'), get_bloginfo('title', 'display'))); ?>
			</a>
		</p>

		</div>
		<?php do_action('login_footer'); ?>
		<div class="clear"></div>
		</body>
		</html>
		<?php
		die;
	}

	/**
	 * Do the next step for a session.
	 *
	 * If there are more steps, show the next step,  otherwise log the user in.
	 *
	 * @param Login_Interstitial_Session $session
	 * @param array $args
	 */
	private function do_next_step(Login_Interstitial_Session $session, array $args = array())
	{
		$args = wp_parse_args($args, array(
			'delete' => true,
			'allow_interim' => true,
		));

		if( $session->get_current_interstitial() ) {
			$this->show_interstitial($session);
		} else {
			if( true === $args['delete'] ) {
				$session->delete();
			}

			$this->handle_interstitials_completed($session, $args);
		}
	}

	/**
	 * Handle when all of the interstitials have been processed.
	 *
	 * @param Login_Interstitial_Session $session
	 * @param array $args
	 */
	private function handle_interstitials_completed(Login_Interstitial_Session $session, array $args)
	{

		$user = $session->get_user();
		$secure = '';

		// If the user wants SSL but the session is not SSL, force a secure cookie.
		if( !force_ssl_admin() && get_user_option('use_ssl', $user->ID) ) {
			$secure = true;
			force_ssl_admin(true);
		}

		if( !is_user_logged_in() ) {
			wp_set_auth_cookie($user->ID, $session->is_remember_me(), $secure);

			remove_action('wp_login', array($this, 'wp_login'), -1000);
			do_action('wp_login', $user->user_login, $user);

			/**
			 * Fires when a user is re-logged back in after submitting an interstitial.
			 *
			 * @param \WP_User $user
			 */
			do_action('titan_login_interstitial_logged_in', $user);
		}

		if( $args['allow_interim'] && $session->is_interim_login() ) {
			$this->interim_login();
		}

		if( $session->get_redirect_to() ) {
			$redirect_to = $requested = $session->get_redirect_to();

			if( $secure && false !== strpos($redirect_to, 'wp-admin') ) {
				$redirect_to = preg_replace('|^http://|', 'https://', $redirect_to);
			}
		} else {
			$redirect_to = admin_url();
			$requested = '';
		}

		if( !$redirect_to || $redirect_to === 'wp-admin/' || $redirect_to === admin_url() ) {
			// If the user doesn't belong to a blog, send them to user admin. If the user can't edit posts, send them to their profile.
			if( is_multisite() && !get_active_blog_for_user($user->ID) && !is_super_admin($user->ID) ) {
				$redirect_to = user_admin_url();
			} elseif( is_multisite() && !$user->has_cap('read') ) {
				$redirect_to = get_dashboard_url($user->ID);
			} elseif( !$user->has_cap('edit_posts') ) {
				$redirect_to = $user->has_cap('read') ? admin_url('profile.php') : home_url();
			}
		}

		$redirect_to = apply_filters('login_redirect', $redirect_to, $requested, $user);
		wp_safe_redirect($redirect_to);

		die;
	}

	/**
	 * Get the base wp login URL.
	 *
	 * @return string
	 */
	private function get_base_wp_login_url()
	{
		$wp_login_url = set_url_scheme(wp_login_url(), 'login_post');

		if( isset($_GET['wpe-login']) && !preg_match('/[&?]wpe-login=/', $wp_login_url) ) {
			$wp_login_url = add_query_arg('wpe-login', $_GET['wpe-login'], $wp_login_url);
		}

		return $wp_login_url;
	}

	/**
	 * Get the next interstitial to be displayed.
	 *
	 * @param Login_Interstitial_Session $session
	 *
	 * @return string|false
	 */
	private function get_next_interstitial(Login_Interstitial_Session $session)
	{

		foreach($this->get_applicable_interstitials($session->get_user()) as $action => $interstitial) {
			if( !$session->is_interstitial_completed($action) ) {
				return $action;
			}
		}

		foreach($session->get_show_after() as $action) {
			if( !$session->is_interstitial_completed($action) ) {
				return $action;
			}
		}

		return false;
	}

	/**
	 * Get all handlers that are applicable to the given user.
	 *
	 * @param \WP_User $user
	 *
	 * @return array
	 */
	private function get_applicable_interstitials($user)
	{

		$applicable = array();

		foreach($this->registered as $action => $interstitial) {
			if( $this->is_interstitial_applicable($action, $user) ) {
				$applicable[$action] = $interstitial;
			}
		}

		return $applicable;
	}

	/**
	 * Is the interstitial applicable to the given user.
	 *
	 * @param string $action
	 * @param \WP_User $user
	 *
	 * @return bool
	 */
	private function is_interstitial_applicable($action, $user)
	{

		$interstitial = $this->registered[$action];

		if( !$interstitial->show_to_user($user, false) ) {
			return false;
		}

		if( !did_action('login_init') && $interstitial->show_on_wp_login_only($user) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get the active session.
	 *
	 * @param bool $return_error
	 *
	 * @return Login_Interstitial_Session|\WP_Error
	 */
	private function get_and_verify_session($return_error = false)
	{

		$error = new \WP_Error('titan-login-interstitial-invalid-token', esc_html__('Sorry, this request has expired. Please log in again.', 'titan-security'));

		if( !isset($_REQUEST[self::R_USER], $_REQUEST[self::R_TOKEN], $_REQUEST[self::R_SESSION]) ) {
			return $return_error ? $error : $this->redirect_invalid_token();
		}

		$session = Login_Interstitial_Session::get($_REQUEST[self::R_SESSION]);

		if( is_wp_error($session) ) {
			return $return_error ? $error : $this->redirect_invalid_token();
		}

		$valid = $session->verify((int)$_REQUEST[self::R_USER], $_REQUEST[self::R_TOKEN]);

		if( true !== $valid ) {
			return $return_error ? $error : $this->redirect_invalid_token();
		}

		return $session;
	}

	/**
	 * Get the active session and verify for performing an async action.
	 *
	 * @param bool $return_error
	 *
	 * @return Login_Interstitial_Session|\WP_Error
	 */
	private function get_and_verify_session_for_async_action($return_error = false)
	{

		$error = new \WP_Error('titan-login-interstitial-invalid-token', esc_html__('Sorry, this request has expired. Please log in again.', 'titan-security'));

		if( !isset($_REQUEST[self::R_USER], $_REQUEST[self::R_TOKEN], $_REQUEST[self::R_SESSION], $_REQUEST[self::R_ASYNC_ACTION]) ) {
			return $return_error ? $error : $this->redirect_invalid_token();
		}

		$session = Login_Interstitial_Session::get($_REQUEST[self::R_SESSION]);

		if( is_wp_error($session) ) {
			return $return_error ? $error : $this->redirect_invalid_token();
		}

		$valid = $session->verify_for_payload($_REQUEST[self::R_ASYNC_ACTION], (int)$_REQUEST[self::R_USER], $_REQUEST[self::R_TOKEN]);

		if( true !== $valid ) {
			return $return_error ? $error : $this->redirect_invalid_token();
		}

		return $session;
	}

	/**
	 * Redirect back to the login page with a message that the token is invalid.
	 */
	private function redirect_invalid_token()
	{

		if( !isset($_REQUEST[self::SHOW_AFTER_LOGIN]) && is_user_logged_in() ) {
			wp_safe_redirect(admin_url());
			die;
		}

		$redirect = add_query_arg(self::R_EXPIRED, 1, wp_login_url());
		wp_safe_redirect(set_url_scheme($redirect, 'login_post'));
		die;
	}

	/**
	 * Destroy the session for a user.
	 *
	 * @param \WP_User $user
	 */
	private function destroy_session_token($user)
	{
		\WP_Session_Tokens::get_instance($user->ID)->destroy($this->session_token ? $this->session_token : wp_get_session_token());
		wp_clear_auth_cookie();
	}

	/**
	 * Sort interstitials according to priority.
	 *
	 * @param Login_Interstitial $a
	 * @param Login_Interstitial $b
	 *
	 * @return int
	 */
	private function _sort_interstitials($a, $b)
	{
		return $a->get_priority() - $b->get_priority();
	}

	/**
	 * Set a cookie.
	 *
	 * @param string $name
	 * @param string $value
	 * @param array $args
	 */
	public static function set_cookie($name, $value, $args = array())
	{

		$args = wp_parse_args(array(
			'length' => 0,
			'http_only' => true,
		), $args);

		$expires = $args['length'] ? current_time('timestamp', true) + $args['length'] : 0;

		setcookie($name, $value, $expires, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), $args['http_only']);
	}

	/**
	 * Clear a cookie.
	 *
	 * @param string $name
	 */
	public static function clear_cookie($name)
	{
		setcookie($name, ' ', current_time('timestamp', true) - YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, false, false);
	}
}
