
<html>
    <head>
        <title>Tappyn</title>
        <meta charset="utf-8" />
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="google-site-verification" content="4pmD1O3gQ8tvABnuXFko0Vn1L0MozLiFTyduIv6D_Xk" />
        <link href='https://fonts.googleapis.com/css?family=Lato:400,300' rel='stylesheet' type='text/css'>
        <link href="<?php echo base_url().'public/css/foundation.css' ?>" rel="stylesheet">
		<link href="<?php echo base_url().'public/css/app.css' ?>" rel="stylesheet">
		<link href="<?php echo base_url().'public/css/slick.css' ?>" rel="stylesheet">
		<link href="<?php echo base_url().'public/css/jcf.css' ?>" rel="stylesheet">
        <link rel="stylesheet" href="<?php echo base_url().'public/css/foundation-icons.css' ?>" />
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
		<script src="<?php echo base_url().'public/js/custom-select.js' ?>" type='text/javascript'></script>
		<script src="<?php echo base_url().'public/js/foundation.min.js'?>" type='text/javascript'></script>
        <script src="<?php echo base_url().'public/js/vendor/facebook.js' ?>" type='text/javascript'></script>
        <script src="<?php echo base_url().'public/js/vendor/google_analytics.js' ?>" type='text/javascript'></script>
        <script src="<?php echo base_url().'public/js/vendor/hotjar.js' ?>" type='text/javascript'></script>
        <script src="<?php echo base_url().'public/js/vendor/lucky_orange.js' ?>" type='text/javascript'></script>
        <script src="https://cdn.rawgit.com/nnattawat/flip/v1.0.19/dist/jquery.flip.min.js" type='text/javascript'></script>
	</head>

	<header>
        <script>
            !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
            n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
            document,'script','//connect.facebook.net/en_US/fbevents.js');

            fbq('init', '1019195258103569');
            fbq('track', "PageView");
        </script>
        <noscript><img height="1" width="1" style="display:none"
            src="https://www.facebook.com/tr?id=1019195258103569&ev=PageView&noscript=1"
        /></noscript>
<!-- End Facebook Pixel Code -->
            <div id="fb-root"></div>
            <script>(function(d, s, id) {
              var js, fjs = d.getElementsByTagName(s)[0];
              if (d.getElementById(id)) return;
              js = d.createElement(s); js.id = id;
              js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.5&appId=879448002090308";
              fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));</script>
	    <div class="row">
	        <div class="column medium-12">
	            <div class="columns small-3 medium-1">
	                <div class="logo">
	                    <a href="<?php echo base_url(); ?>">
	                    	<img src="<?php echo base_url().'public/img/TappynLogo2.png'; ?>" width='70'>
	                    </a>
	                </div>
	            </div>
	            <div class="columns small-9 medium-11">
	                <div id="nav-icon4" data-toggle='example-dropdown'>
	                    <span></span>
	                    <span></span>
	                    <span></span>
	                </div>

	                <nav>
	                    <ul>
	                    	<?php if($this->ion_auth->logged_in()) : ?>
	                    	<li><a href="<?php echo base_url().'dashboard'; ?>">Dashboard</a></li>
	                   		<?php endif ?>
	                        <li><a href="<?php echo base_url().'contests'; ?>">Contests</a></li>
	                        <li><a href="<?php echo base_url().'contests/create'; ?>">Launch</a></li>
	                        <li><a href="<?php echo base_url().'contact'; ?>">Contact Us</a></li>
	                        <li><a href="<?php echo base_url().'faq'; ?>">FAQ</a></li>
	                        <?php if($this->ion_auth->logged_in()) : ?>
                            <li style='float:right;'><a style='padding:0px;' href='#'>
                                <ul class='dropdown menu' data-dropdown-menu>
                                    <li>
                                        <a href='#'><?php echo ucfirst($this->ion_auth->user()->row()->first_name)." ".ucfirst($this->ion_auth->user()->row()->last_name[0]); ?></a>
                                        <ul class='menu vertical'>
                                            <li><a href="<?php echo base_url().'accounts/payment_methods'; ?>">Payment</a></li>
            	                            <li><a href="<?php echo base_url().'profile'; ?>">Profile</a></li>
            	                            <li><a href="<?php echo base_url().'logout'; ?>">Log out</a></li>
                                        </ul>
                                    </li>
                                </ul>
                                </a>
                            </li>
	                        <?php else : ?>
	                          	<li class="login-li">
	                                <div class="login-box">
	                                    <a href="<?php echo base_url().'login'; ?>">LOGIN</a>
	                                    <a href="<?php echo base_url().'signup'; ?>">SIGN UP</a>
	                                </div>
	                            </li>
                            <?php endif ?>
	                    </ul>
	                </nav>
	            </div>
	        </div>
	    </div>
        <div class='mobile-navbar' id='mobile-navbar' style='display:none'>
            <div  class='navbar-list-wrapper'>
                <ul class='navbar-lst'>
                    <li><a href="<?php echo base_url().'contests/index'; ?>">Contests</a></li>
                    <li><a href="<?php echo base_url().'contact' ?>">Contact Us</a></li>
                    <?php if($this->ion_auth->logged_in()): ?>
                    <li><a href="<?php echo base_url().'users/dashboard'; ?>">Dashboard</a></li>
                    <li><a href="<?php echo base_url().'accounts/details'; ?>">Payment</a></li>
                    <li><a href="<?php echo base_url().'users/profile'; ?>">Profile</a></li>
                    <li><a href="<?php echo base_url().'auth/logout' ?>">Log Out</a></li>
                    <?php else: ?>
                    <li><a href="<?php echo base_url().'signup' ?>">Sign Up</a></li>
                    <li><a href="<?php echo base_url().'login' ?>">Log In</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
	</header>

<script>
    $(document).ready(function() {
        $('#nav-icon4').click(function() {
            $('#mobile-navbar').toggle();
        });

        $(document).mouseup(function(e) {
            var container = $('#mobile-navbar');
            if(!container.is(e.target)
                && container.has(e.target).length === 0)
                {
                    container.hide();
                }
        })
    })
</script>
