<?php

global $porto_settings, $porto_layout, $event_countdown_vc;

?>

<?php 
	$event_start_date 	= get_post_meta($post->ID, 'event_start_date', true);
	$event_start_time 	= get_post_meta($post->ID, 'event_start_time', true);
	$event_location 	= get_post_meta($post->ID, 'event_location', true);
	$event_count_down   = get_post_meta($post->ID, 'event_time_counter', true);
	
	if(isset($event_countdown_vc) && $event_countdown_vc != ''){
		$event_count_down = $event_countdown_vc;
	}
	
	if($event_count_down == ''){
		$show_count_down = $porto_settings["event-archive-countdown"];
	}
	elseif($event_count_down == 'show'){
		$show_count_down = true;
	}
	else{
		$show_count_down = false;
	}
	
	if(isset($event_start_date) && $event_start_date != '') {
		$has_event_date = true;
		$event_date_parts = explode('/', $event_start_date);
		
		if(isset($event_date_parts) && count($event_date_parts) == 3) {
			$has_event_date 		= true;
			$event_year_numeric 	= isset($event_date_parts[0]) ? trim($event_date_parts[0]) : '';
			$event_month_numeric 	= isset($event_date_parts[1]) ? trim($event_date_parts[1]) : '';
			$event_date_numeric 	= isset($event_date_parts[2]) ? trim($event_date_parts[2]) : '';
			$event_month_short = date('M', mktime(0, 0, 0, $event_month_numeric, 1));
		}
		else
			$has_event_date 		= false;
	}
	else
		$has_event_date = false;
	
	if(isset($event_start_time) && $event_start_time != '')
		$event_time_js_format = date("H:i", strtotime($event_start_time));
	else
		$event_time_js_format = '00:00:00';
?>

  <!--<h2 class="text-color-dark font-weight-bold">Next Event</h2>-->
  <article class="thumb-info custom-thumb-info custom-box-shadow m-b-md"> 
  	<?php 
	$thumbnail = get_the_post_thumbnail_url();
	if ( $thumbnail ):
	?>
        <span class="thumb-info-wrapper"> <a href="<?php the_permalink(); ?>"> 
            <img src="<?php echo $thumbnail; ?>" alt class="img-responsive" /> </a> 
        </span> 
    <?php endif; ?>
    <span class="thumb-info-caption"> 
    	<span class="custom-thumb-info-wrapper-box center"> 
        	<?php if($has_event_date && $show_count_down): ?>
            		<?php echo do_shortcode('[porto_countdown 
datetime="'.$event_start_date.' '.$event_time_js_format.'" 
countdown_opts="sday,shr,smin,ssec" 
tick_col="#da7940" 
tick_style="bold" 
tick_sep_col="#2e353e" 
tick_sep_style="bold" 
el_class="m-b-none custom-newcomers-class" 
string_hours="Hr" 
string_hours2="Hrs" 
string_minutes="Min" 
string_minutes2="Mins" 
string_seconds="Sec" 
string_seconds2="Secs" 
tick_size="desktop:17px;" 
tick_sep_size="desktop:17px;"]'); ?>
            		<?php /*?><span id="countdown" data-countdown-title="" data-countdown-date="<?php echo $event_start_date; ?> <?php echo $event_time_js_format; ?>" class="custom-newcomers-class clock-one-events custom-newcomers-pos-2 text-color-dark font-weight-bold custom-secondary-font custom-box-shadow center"></span> <?php */?>
            <?php endif; ?>
        </span> 
   	<span class="custom-event-infos">
        <ul>
           <?php if(isset($event_start_time) && $event_start_time != ''): ?>
                <li> <i class="fa fa-clock-o"></i> <?php echo $event_start_time; ?> </li>
            <?php endif; ?>
            <?php if(isset($event_location) && $event_location != ''): ?>
                <li class="text-uppercase"> <i class="fa fa-map-marker"></i> <?php echo $event_location; ?></li>
            <?php endif; ?>
      </ul>
        </ul>
    </span> 
    
    <span class="thumb-info-caption-text">
    <h4 class="font-weight-bold mb-sm"> <a href="<?php the_permalink(); ?>" class="text-decoration-none custom-secondary-font text-color-dark"> <?php the_title(); ?> </a> </h4>
   <?php
		if ($porto_settings['event-excerpt']) {
			echo porto_get_excerpt( $porto_settings['event-excerpt-length'], false );
		} else {
			the_content();
		}
		?>
    </span> 
    </span> 
  </article>

