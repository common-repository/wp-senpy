<?php
/*
Plugin Name: wp-senpy
Description: This plugin analyses the emotions and sentiments expressed by users on comments using Senpy.
Plugin URI: http://senpy.readthedocs.io/en/latest/index.html
Version: 0.1
Author: Álvaro Gericke
License: GPL 2+
Text Domain: wpsenpy-sentemt
Domain Path: /languages
*/

/*

    Copyright (C) 2017  Álvaro Gericke  (email: alvgericke@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


//Call function when plugin is activated
register_activation_hook( __FILE__, 'wpsenpy_install' );

//Function executed when plugin is activated. Any comprobations during installation should be done here.
function wpsenpy_install() {

	//Verify Wordpress version
	global $wp_version;

	if ( version_compare( $wp_version, '4.7', '<' ) ) {

		wp_die( 'This plugin requires Wordpress version 4.7 or higher' );
	}

	$wpsenpy_default_settings = array(

		//Sentiment options
		'wpsenpy_sent_field'       => 'sentiment-140',
		'wpsenpy_sent_lng'         => 'en',
		'wpsenpy_meaningcloud_key' => '',
		//Emotion options
		'wpsenpy_emt_field'        => 'emotion-anew',
		'wpsenpy_emt_lng'          => 'en',
		//Visualization options
		'wpsenpy_cmnt_sent_view'   => 'on',
		'wpsenpy_cmnt_emt_view'    => 'on'

	);

	update_option( 'wpsenpy_settings', $wpsenpy_default_settings );
}

/*//Call function when plugin is deactivated_plugin.
register_deactivation_hook( __FILE__, 'wpsenpy_deactivation' );
//Function executed when plugin is deactivated.
function wpsenpy_deactivation() {

}*/

/* Add the translation function */
add_action( 'plugins_loaded', 'wpsenpy_translation_load' );
/**
 * Loads a translation file
 *
 * @since 0.1
 */
function wpsenpy_translation_load() {

	load_plugin_textdomain( 'wpsenpy-sentemt', false, plugin_basename( dirname( __FILE__ ) . '/languages' ) );
}

//Action Hook to analyse the comment and save the results as metadata.
add_action( 'comment_post', 'wpsenpy_analyse_comment', 1000 );
/**
 * Analyses the comment sentiment and obtains the key phrases of it.
 *
 * @uses add_comment_meta()
 *
 * @param integer $comment_ID Comment's ID to analyse
 *
 */
function wpsenpy_analyse_comment( $comment_ID ) {

	//Get all the information of the comment
	$comment = get_comment( $comment_ID );

	//Get the used plugins
	$options     = get_option( 'wpsenpy_settings' );
	$sent_plugin = $options['wpsenpy_sent_field'];
	$emt_plugin  = $options['wpsenpy_emt_field'];

	//The URL
	$url  = "http://test.senpy.cluster.gsi.dit.upm.es/api/";
	$text = rawurlencode( $comment->comment_content );

	//Case Sentiment Plugin is sentiment-140
	if ( $sent_plugin == 'sentiment-140' ) {

		$language = $options['wpsenpy_sent_lng'];
		if ( $language == 'es' ) {
			$language = "es";
		} elseif ( $language == 'en' ) {
			$language = "en";
		} else {
			$language = "auto";
		}

		$payload = "?algo=sentiment-140&language=" . $language . "&i=" . $text;

		//Call to senpy for the sentiment analysis
		try {

			$resp_sent_json      = wp_remote_get( $url . $payload );
			$resp_sent_json_body = wp_remote_retrieve_body( $resp_sent_json );

		} catch ( Exception $ex ) {

			echo $ex;

		}

		//Extract of the sentiment information
		$resp_sent_body = json_decode( $resp_sent_json_body, true );

		if ( isset( $resp_sent_body['entries'][0]['sentiments'][0]['marl:hasPolarity'] ) ) {

			$sent = $resp_sent_body['entries'][0]['sentiments'][0]['marl:hasPolarity'];

		} else {

			$sent = "marl:Neutral";

		}

		//Add the sentiment analysis's result as meta content of the comment.
		add_comment_meta( $comment_ID, 'marl:hasPolarity', $sent, true );

	} //Case sentiment Plugin is sentiment-tass
    elseif ( $sent_plugin == 'sentiment-tass' ) {

		$language = $options['wpsenpy_sent_lng'];

		if ( $language == 'es' ) {
			$language = "es";
		} elseif ( $language == 'en' ) {
			$language = "en";
		} else {
			$language = "en";
		}

		$payload = "?algo=sentiment-tass&language=" . $language . "&i=" . $text;

		//Call to senpy for the sentiment analysis
		try {

			$resp_sent_json      = wp_remote_get( $url . $payload );
			$resp_sent_json_body = wp_remote_retrieve_body( $resp_sent_json );

		} catch ( Exception $ex ) {

			echo $ex;

		}

		//Extract of the sentiment information
		$resp_sent_body = json_decode( $resp_sent_json_body, true );

		if ( isset( $resp_sent_body['entries'][0]['sentiments'][0]['marl:hasPolarity'] ) ) {

			$sent = $resp_sent_body['entries'][0]['sentiments'][0]['marl:hasPolarity'];

		} else {

			$sent = "marl:Neutral";

		}

		//Add the sentiment analysis's result as meta content of the comment.
		add_comment_meta( $comment_ID, 'marl:hasPolarity', $sent, true );
	} //Case sentiment Plugin is sentiment-meaningCloud
    elseif ( $sent_plugin == 'sentiment-meaningCloud' ) {

		$language = $options['wpsenpy_sent_lng'];
		if ( $language == 'es' ) {
			$language = "es";
		} elseif ( $language == 'en' ) {
			$language = "en";
		} else {
			$language = "en";
		}

		$meaningcloud_key = $options['wpsenpy_meaningcloud_key'];

		$payload = "?algo=sentiment-meaningCloud&language=" . $language . "&i=" . $text . "&apiKey=" . $meaningcloud_key;

		//Call to senpy for the sentiment analysis
		try {

			$resp_sent_json      = wp_remote_get( $url . $payload );
			$resp_sent_json_body = wp_remote_retrieve_body( $resp_sent_json );

		} catch ( Exception $ex ) {

			echo $ex;

		}

		//Extract of the sentiment information
		$resp_sent_body = json_decode( $resp_sent_json_body, true );

		if ( isset( $resp_sent_body['entries'][0]['sentiments'][0]['marl:hasPolarity'] ) ) {

			$sent = $resp_sent_body['entries'][0]['sentiments'][0]['marl:hasPolarity'];

		} else {

			$sent = "marl:Neutral";

		}

		//Add the sentiment analysis's result as meta content of the comment.
		add_comment_meta( $comment_ID, 'marl:hasPolarity', $sent, true );
	} //Case sentiment Plugin is sentiment-vader
    elseif ( $sent_plugin == 'sentiment-vader' ) {

		$language = $options['wpsenpy_sent_lng'];
		if ( $language == 'es' ) {
			$language = "es";
		} elseif ( $language == 'en' ) {
			$language = "en";
		} else {
			$language = "auto";
		}

		$payload = "?algo=sentiment-vader&language=" . $language . "&i=" . $text . "&aggregate=true";

		//Call to senpy for the sentiment analysis
		try {

			$resp_sent_json      = wp_remote_get( $url . $payload );
			$resp_sent_json_body = wp_remote_retrieve_body( $resp_sent_json );

		} catch ( Exception $ex ) {

			echo $ex;

		}

		//Extract of the sentiment information
		$resp_sent_body = json_decode( $resp_sent_json_body, true );

		if ( isset( $resp_sent_body['entries'][0]['sentiments'][0]['marl:hasPolarity'] ) ) {

			$sent = $resp_sent_body['entries'][0]['sentiments'][0]['marl:hasPolarity'];

		} else {

			$sent = "marl:Neutral";

		}

		//Add the sentiment analysis's result as meta content of the comment.
		add_comment_meta( $comment_ID, 'marl:hasPolarity', $sent, true );
	} //Case sentiment Plugin is sentiment-basic
    elseif ( $sent_plugin == 'sentiment-basic' ) {

		$language = $options['wpsenpy_sent_lng'];
		if ( $language == 'es' ) {
			$language = "es";
		} elseif ( $language == 'en' ) {
			$language = "en";
		} elseif ( $language == 'it' ) {
			$language = "it";
		} elseif ( $language == 'fr' ) {
			$language = "fr";
		} else {
			$language = "auto";
		}

		$text_modif = str_replace( ".", ",", $text );

		$payload = "?algo=sentiment-basic&language=" . $language . "&i=" . $text_modif;

		//Call to senpy for the sentiment analysis
		try {

			$resp_sent_json      = wp_remote_get( $url . $payload );
			$resp_sent_json_body = wp_remote_retrieve_body( $resp_sent_json );

		} catch ( Exception $ex ) {

			echo $ex;

		}

		//Extract of the sentiment information
		$resp_sent_body = json_decode( $resp_sent_json_body, true );

		if ( isset( $resp_sent_body['entries'][0]['sentiments'][0]['marl:hasPolarity'] ) ) {

			$sent = $resp_sent_body['entries'][0]['sentiments'][0]['marl:hasPolarity'];

		} else {

			$sent = "marl:Neutral";

		}

		//Add the sentiment analysis's result as meta content of the comment.
		add_comment_meta( $comment_ID, 'marl:hasPolarity', $sent, true );
	}

	//Case Emotion Plugin is emotion-anew
	if ( $emt_plugin == 'emotion-anew' ) {

		$language = $options['wpsenpy_emt_lng'];
		if ( $language == 'es' ) {
			$language = "es";
		} elseif ( $language == 'en' ) {
			$language = "en";
		} else {
			$language = "en";
		}

		$payload = "?algo=emotion-anew&language=" . $language . "&i=" . $text;

		//Call to senpy for the sentiment analysis
		try {

			$resp_emt_json      = wp_remote_get( $url . $payload );
			$resp_emt_json_body = wp_remote_retrieve_body( $resp_emt_json );

		} catch ( Exception $ex ) {

			echo $ex;

		}

		//Extract of the emotion information
		$resp_emt_body = json_decode( $resp_emt_json_body, true );

		if ( isset( $resp_emt_body['entries'][0]['emotions'][0]['onyx:hasEmotion']['onyx:hasEmotionCategory'] ) ) {

			$emt = $resp_emt_body['entries'][0]['emotions'][0]['onyx:hasEmotion']['onyx:hasEmotionCategory'];
			preg_match( '/(?=#).*/', $emt, $emotion );
			$emotion[0] = substr( $emotion[0], 1 );

		} else {

			$emotion[0] = 'neutral-emotion';

		}

		//Add the sentiment analysis's result as meta content of the comment.
		add_comment_meta( $comment_ID, 'onyx:hasEmotionCategory', $emotion[0], true );
	} //Case Emotion Plugin is emotion-wnaffect
    elseif ( $emt_plugin == 'emotion-wnaffect' ) {

		$language = $options['wpsenpy_emt_lng'];
		if ( $language == 'en' ) {
			$language = "en";
		} else {
			$language = "en";
		}

		$payload = "?algo=emotion-wnaffect&language=" . $language . "&i=" . $text;

		//Call to senpy for the sentiment analysis
		try {

			$resp_emt_json      = wp_remote_get( $url . $payload );
			$resp_emt_json_body = wp_remote_retrieve_body( $resp_emt_json );

		} catch ( Exception $ex ) {

			echo $ex;

		}

		//Extract of the emotion information
		$resp_emt_body = json_decode( $resp_emt_json_body, true );

		if ( isset( $resp_emt_body['entries'][0]['emotions'][0]['onyx:hasEmotion'] ) ) {

			$emt = $resp_emt_body['entries'][0]['emotions'][0]['onyx:hasEmotion'];

			$emotion_text  = 'neutral-emotion';
			$emotion_value = 0;

			foreach ( $emt as $emtcat ) {

				if ( $emtcat['onyx:hasEmotionIntensity'] > $emotion_value ) {

					$emotion_text  = $emtcat['onyx:hasEmotionCategory'];
					$emotion_value = $emtcat['onyx:hasEmotionIntensity'];

				} elseif ( $emtcat['onyx:hasEmotionIntensity'] == $emotion_value ) {

					$emotion_text = "neutral-emotion";

				} else {

					continue;

				}
			}

		} else {

			$emotion_text = "neutral-emotion";

		}

		//Add the sentiment analysis's result as meta content of the comment.
		add_comment_meta( $comment_ID, 'onyx:hasEmotionCategory', $emotion_text, true );
	}
}

//Filter Hook to modify the view of the comment before it is shown.
add_filter( 'comment_text', 'wpsenpy_modify_comment', 99 );
/**
 * Modifies the html of the comment view
 *
 * @param string $comment_text
 * @param object $comment default null
 *
 * @return string
 */
function wpsenpy_modify_comment( $comment_text ) {

	$options = get_option( 'wpsenpy_settings' );

	$cmnt_id        = get_comment_ID();
	$cmnt_meta_sent = get_comment_meta( $cmnt_id, 'marl:hasPolarity', true );
	$cmnt_meta_sent = substr( $cmnt_meta_sent, 5 );
	$cmnt_meta_emt  = get_comment_meta( $cmnt_id, 'onyx:hasEmotionCategory', true );

	//Get visualization options
	$sent_visual = $options['wpsenpy_cmnt_sent_view'];
	$emt_visual  = $options ['wpsenpy_cmnt_emt_view'];

	$color  = '#000';
	$border = '#000';

	if ( $cmnt_meta_sent == 'Negative' ) {

		$color          = '#ff6666';
		$border         = '#ff0000';
		$cmnt_meta_sent = __( 'Negative', 'wpsenpy-sentemt' );

	} elseif ( $cmnt_meta_sent == 'Positive' ) {

		$color          = '#80ff80';
		$border         = '#00ff00';
		$cmnt_meta_sent = __( 'Positive', 'wpsenpy-sentemt' );

	} else {

		$color          = '#999999';
		$border         = '#808080';
		$cmnt_meta_sent = __( 'Neutral', 'wpsenpy-sentemt' );

	}

	if ( $sent_visual == 'on' && $emt_visual == 'on' ) {

		$comment_text = "<div class='analysis-info'>
                            <span id='sentiment' style='font-weight: 700;
                                                        font-size: small;
                                                        color: #ffffff;
                                                        background: " . $color . ";
                                                        margin: 0px 0px 10px 0px;
                                                        overflow: hidden;
                                                        padding: 2px;
                                                        border-radius: 10px 0px 10px 0px;
                                                        -webkit-border-radius: 10px 0px 10px 0px;
                                                        border: 1.5px solid " . $border . ";
                                                        width: 20%;
                                                        text-align: center;
                                                        float: left;'>" .
		                $cmnt_meta_sent .
		                "</span> 
                            <span id='emotion' style='  margin: 0px 0px 10px 10px;
                                                        overflow: hidden;
                                                        padding: 2px;
                                                        border-radius: 10px 0px 10px 0px;
                                                        -webkit-border-radius: 10px 0px 10px 0px;
                                                        width: 10%;
                                                        text-align: center;
                                                        float: left;'>
                                <img id='emotion-image' src='" . plugins_url( 'images/' . $cmnt_meta_emt . '.png', __FILE__ ) . "' style='height: 25px; width: auto'>
                            </span>
                        </div>
                        <div id='cmnt-text' style='width: 100%; overflow: auto'>" .
		                $comment_text .
		                "</div>";

	} elseif ( $sent_visual == 'on' ) {

		$comment_text = "<div class='analysis-info'>
                            <span id='sentiment' style='font-weight: 700;
                                                        font-size: small;
                                                        color: #ffffff;
                                                        background: " . $color . ";
                                                        margin: 0px 0px 10px 0px;
                                                        overflow: hidden;
                                                        padding: 2px;
                                                        border-radius: 10px 0px 10px 0px;
                                                        -webkit-border-radius: 10px 0px 10px 0px;
                                                        border: 1.5px solid " . $border . ";
                                                        width: 20%;
                                                        text-align: center;
                                                        float: left;'>" .
		                $cmnt_meta_sent .
		                "</span>
                        </div>
                        <div id='cmnt-text' style='width: 100%; overflow: auto'>" .
		                $comment_text .
		                "</div>";

	} elseif ( $emt_visual == 'on' ) {

		$comment_text = "<div class='analysis-info'> 
                            <span id='emotion' style='  margin: 0px 0px 10px 10px;
                                                        overflow: hidden;
                                                        padding: 2px;
                                                        border-radius: 10px 0px 10px 0px;
                                                        -webkit-border-radius: 10px 0px 10px 0px;
                                                        width: 10%;
                                                        text-align: center;
                                                        float: left;'>
                                <img id='emotion-image' src='" . plugins_url( 'images/' . $cmnt_meta_emt . '.png', __FILE__ ) . "' style='height: 25px; width: auto'>
                            </span>
                        </div>
                        <div id='cmnt-text' style='width: 100%; overflow: auto'>" .
		                $comment_text .
		                "</div>";
	}

	return $comment_text;
}


/* ------------------------------------------------------------------------------------------------------------------------------ */
/* Action Hook to create the settings menú of the plugin */
/* ------------------------------------------------------------------------------------------------------------------------------ */
add_action( 'admin_menu', 'wpsenpy_add_admin_menu' );
add_action( 'admin_init', 'wpsenpy_settings_init' );


function wpsenpy_add_admin_menu() {

	add_menu_page(
		__( 'Sentiment and Emotion Analysis Plugin', 'wpsenpy-sentemt' ),
		__( 'Emotion & Sentiment Menu', 'wpsenpy-sentemt' ),
		'manage_options',
		'sentiment_and_emotion_analysis_plugin_menu',
		'wpsenpy_options_page',
		'dashicons-smiley',
		50
	);

	add_submenu_page(
		'sentiment_and_emotion_analysis_plugin_menu',
		__( 'CSS & Layout Sentiment and Emotion Analysis Plugin', 'wpsenpy-sentemt' ),
		__( 'Comments Layout', 'wpsenpy-sentemt' ),
		'manage_options',
		'sentiment_and_emotion_analysis_plugin_menu_comment_layout',
		'wpsenpy_options_page_comment_layout'
	);

	add_submenu_page(
		'sentiment_and_emotion_analysis_plugin_menu',
		__( 'About Sentiment and Emotion Analysis Plugin', 'wpsenpy-sentemt' ),
		__( 'About', 'wpsenpy-sentemt' ),
		'manage_options',
		'sentiment_and_emotion_analysis_plugin_menu_about',
		'wpsenpy_options_page_about'
	);

}

function wpsenpy_settings_init() {

	register_setting(
		'wpsenpy_settings',
		'wpsenpy_settings',
		'wpsenpy_sanitize_settings'
	);

	$options = get_option( 'wpsenpy_settings' );

	//Section for selecting Senpy Plugins
	add_settings_section(
		'wpsenpy_pluginPage_section',
		__( 'Select the Plugins for Senpy', 'wpsenpy-sentemt' ),
		'wpsenpy_settings_section_callback',
		'sentiment_and_emotion_analysis_plugin_menu'
	);

	add_settings_field(
		'wpsenpy_sent_field',
		__( 'Select the Sentiment Analysis plugin', 'wpsenpy-sentemt' ),
		'wpsenpy_sent_field_render',
		'sentiment_and_emotion_analysis_plugin_menu',
		'wpsenpy_pluginPage_section'
	);

	if ( $options['wpsenpy_sent_field'] == 'sentiment-meaningCloud' ) {

		add_settings_field(
			'wpsenpy_sent_meaningCloud_key',
			__( '', 'wpsenpy-sentemt' ),
			'wpsenpy_sent_meaningcloud_key_callback',
			'sentiment_and_emotion_analysis_plugin_menu',
			'wpsenpy_pluginPage_section'
		);
	}

	add_settings_field(
		'wpsenpy_sent_lng_field',
		__( '', 'wpsenpy-sentemt' ),
		'wpsenpy_sent_lng_field_render',
		'sentiment_and_emotion_analysis_plugin_menu',
		'wpsenpy_pluginPage_section'
	);

	add_settings_field(
		'wpsenpy_emt_field',
		__( 'Select the Emotion Analysis plugin', 'wpsenpy-sentemt' ),
		'wpsenpy_emt_field_render',
		'sentiment_and_emotion_analysis_plugin_menu',
		'wpsenpy_pluginPage_section'
	);

	add_settings_field(
		'wpsenpy_emt_lng_field',
		__( '', 'wpsenpy-sentemt' ),
		'wpsenpy_emt_lng_field_render',
		'sentiment_and_emotion_analysis_plugin_menu',
		'wpsenpy_pluginPage_section'
	);

	add_settings_section(
		'wpsenpy_layoutPage_section',
		__( 'Configure the Layout for the Comments', 'wpsenpy-sentemt' ),
		'wpsenpy_cmnt_layout_section_callback',
		'sentiment_and_emotion_analysis_plugin_menu_comment_layout'
	);

	add_settings_field(
		'wpsenpy_cmnt_sent_view',
		__( "Select if you want the comment's sentiment to be shown", 'wpsenpy-sentemt' ),
		'wpsenpy_sent_cmnt_view',
		'sentiment_and_emotion_analysis_plugin_menu_comment_layout',
		'wpsenpy_layoutPage_section'
	);

	add_settings_field(
		'wpsenpy_cmnt_emt_view',
		__( "Select if you want the comment's emotion to be shown", 'wpsenpy-sentemt' ),
		'wpsenpy_emt_cmnt_view',
		'sentiment_and_emotion_analysis_plugin_menu_comment_layout',
		'wpsenpy_layoutPage_section'
	);

}

function wpsenpy_sanitize_settings( $options ) {

	$settings = get_option( 'wpsenpy_settings' );

	$clean = array();

	//Sentiment plugin sanitization
	if ( isset( $options['wpsenpy_sent_field'] ) ) {

		$valid_sent_field_values = array(
			'sentiment-140',
			'sentiment-tass',
			'sentiment-meaningCloud',
			'sentiment-vader',
			'sentiment-basic'
		);

		$sent_field = sanitize_text_field( $options['wpsenpy_sent_field'] );
		if ( in_array( $sent_field, $valid_sent_field_values ) ) {
			$clean['wpsenpy_sent_field'] = $sent_field;
		} else {
			wp_die( __( 'Invalid Sentiment Plugin value, go back and try again', 'wpsenpy-sentemt' ) );
		}

	} else {
		$clean['wpsenpy_sent_field'] = $settings['wpsenpy_sent_field'];
	}

	//Meaningcloud Key sanitization
	if ( isset( $options['wpsenpy_meaningcloud_key'] ) ) {

		$key = sanitize_text_field( $options['wpsenpy_meaningcloud_key'] );
		if ( preg_match( '/^[a-z0-9]{32}$/i', $key ) ) {
			$clean['wpsenpy_meaningcloud_key'] = $key;
		} else {
			wp_die( __( 'Invalid Meaning Cloud Key, go back and try again' ) );
		}
	} else {
		$clean['wpsenpy_meaningcloud_key'] = $settings['wpsenpy_meaningcloud_key'];
	}

	//Sentiment Language sanitization
	if ( isset( $options['wpsenpy_sent_lng'] ) ) {

		$valid_sent_lng_values = array(
			'es',
			'en',
			'auto',
			'it',
			'fr'
		);

		$sent_lng = sanitize_text_field( $options['wpsenpy_sent_lng'] );
		if ( in_array( $sent_lng, $valid_sent_lng_values ) ) {
			$clean['wpsenpy_sent_lng'] = $sent_lng;
		} else {
			wp_die( __( 'Invalid Sentiment Language value, go back and try again', 'wpsenpy-sentemt' ) );
		}

	} else {
		$clean['wpsenpy_sent_lng'] = $settings['wpsenpy_sent_lng'];
	}

	//Emotion plugin sanitization
	if ( isset( $options['wpsenpy_emt_field'] ) ) {

		$valid_emt_field_values = array(
			'emotion-anew',
			'emotion-wnaffect'
		);

		$emt_field = sanitize_text_field( $options['wpsenpy_emt_field'] );
		if ( in_array( $emt_field, $valid_emt_field_values ) ) {
			$clean['wpsenpy_emt_field'] = $emt_field;
		} else {
			wp_die( __( 'Invalid Emotion Plugin value, go back and try again', 'wpsenpy-sentemt' ) );
		}

	} else {
		$clean['wpsenpy_emt_field'] = $settings['wpsenpy_emt_field'];
	}

	//Emotion Language sanitization
	if ( isset( $options['wpsenpy_sent_lng'] ) ) {

		$valid_emt_lng_values = array(
			'es',
			'en'
		);

		$emt_lng = sanitize_text_field( $options['wpsenpy_emt_lng'] );
		if ( in_array( $emt_lng, $valid_emt_lng_values ) ) {
			$clean['wpsenpy_emt_lng'] = $emt_lng;
		} else {
			wp_die( __( 'Invalid Emotion Language value, go back and try again', 'wpsenpy-sentemt' ) );
		}

	} else {
		$clean['wpsenpyemt_lng'] = $settings['wpsenpyemt_lng'];
	}

    if ( isset( $options['wpsenpy_sent_lng'] ) ) {

	    $clean['wpsenpy_cmnt_sent_view']   = $settings['wpsenpy_cmnt_sent_view'];
	    $clean['wpsenpy_cmnt_emt_view']   = $settings['wpsenpy_cmnt_emt_view'];

    } else {

	    //Sentiment View setting sanitization
	    $options['wpsenpy_cmnt_sent_view'] = ( $options['wpsenpy_cmnt_sent_view'] == 'on' ) ? 'on' : '';
	    $clean['wpsenpy_cmnt_sent_view']   = $options['wpsenpy_cmnt_sent_view'];

	    //Emotion View setting sanitization
	    $options['wpsenpy_cmnt_emt_view'] = ( $options['wpsenpy_cmnt_emt_view'] == 'on' ) ? 'on' : '';
	    $clean['wpsenpy_cmnt_emt_view']   = $options['wpsenpy_cmnt_emt_view'];
    }

	return $clean;
}

function wpsenpy_sent_field_render() {

	$options = get_option( 'wpsenpy_settings' );
	?>
    <select name='wpsenpy_settings[wpsenpy_sent_field]'>
        <option value='sentiment-140' <?php selected( $options['wpsenpy_sent_field'], 'sentiment-140' ); ?>>
            sentiment-140
        </option>
        <option value='sentiment-tass' <?php selected( $options['wpsenpy_sent_field'], 'sentiment-tass' ); ?>>
            sentiment-tass
        </option>
        <option value='sentiment-meaningCloud' <?php selected( $options['wpsenpy_sent_field'], 'sentiment-meaningCloud' ); ?>>
            sentiment-meaningCloud
        </option>
        <option value='sentiment-vader' <?php selected( $options['wpsenpy_sent_field'], 'sentiment-vader' ); ?>>
            sentiment-vader
        </option>
        <option value='sentiment-basic' <?php selected( $options['wpsenpy_sent_field'], 'sentiment-basic' ); ?>>
            sentiment-basic
        </option>
        <!-- <option value='6' <?php //selected( $options['wpsenpy_sent_field'], 6 ); ?>>affect</option> -->
    </select>

	<?php
}

function wpsenpy_sent_meaningcloud_key_callback() {

	$options                  = get_option( 'wpsenpy_settings' );
	$wpsenpy_meaningcloud_key = $options['wpsenpy_meaningcloud_key'];

	echo '<p>' . __( "Introduce your Meaning cloud Api Key", 'wpsenpy-sentemt' ) . '</p>';
	echo "<input id='wpsenpy_meaningcloud_key' name='wpsenpy_settings[wpsenpy_meaningcloud_key]' type='text' value='" . esc_attr( $wpsenpy_meaningcloud_key ) . "' />";
}

function wpsenpy_sent_lng_field_render() {

	$options = get_option( 'wpsenpy_settings' );

	$wpsenpy_sent_field = $options['wpsenpy_sent_field'];
	echo '<p>' . __( "Select the Language Parameter for the Sentimet Plugin", 'wpsenpy-sentemt' ) . '</p>';

	if ( $wpsenpy_sent_field == 'sentiment-140' ) {
		?>
        <select name='wpsenpy_settings[wpsenpy_sent_lng]'>
            <option value='es' <?php selected( $options['wpsenpy_sent_lng'], 'es' ); ?>>es</option>
            <option value='en' <?php selected( $options['wpsenpy_sent_lng'], 'en' ); ?>>en</option>
            <option value='auto' <?php selected( $options['wpsenpy_sent_lng'], 'auto' ); ?>>auto</option>
        </select>
		<?php
	} elseif ( $wpsenpy_sent_field == 'sentiment-tass' ) {
		?>
        <select name='wpsenpy_settings[wpsenpy_sent_lng]'>
            <option value='es' <?php selected( $options['wpsenpy_sent_lng'], 'es' ); ?>>es</option>
            <option value='en' <?php selected( $options['wpsenpy_sent_lng'], 'en' ); ?>>en</option>
        </select>
		<?php
	} elseif ( $wpsenpy_sent_field == 'sentiment-meaningCloud' ) {
		?>
        <select name='wpsenpy_settings[wpsenpy_sent_lng]'>
            <option value='es' <?php selected( $options['wpsenpy_sent_lng'], 'es' ); ?>>es</option>
            <option value='en' <?php selected( $options['wpsenpy_sent_lng'], 'en' ); ?>>en</option>
        </select>
		<?php
	} elseif ( $wpsenpy_sent_field == 'sentiment-vader' ) {
		?>
        <select name='wpsenpy_settings[wpsenpy_sent_lng]'>
            <option value='es' <?php selected( $options['wpsenpy_sent_lng'], 'es' ); ?>>es</option>
            <option value='en' <?php selected( $options['wpsenpy_sent_lng'], 'en' ); ?>>en</option>
            <option value='auto' <?php selected( $options['wpsenpy_sent_lng'], 'auto' ); ?>>auto</option>
        </select>
		<?php
	} elseif ( $wpsenpy_sent_field == 'sentiment-basic' ) {
		?>
        <select name='wpsenpy_settings[wpsenpy_sent_lng]'>
            <option value='es' <?php selected( $options['wpsenpy_sent_lng'], 'es' ); ?>>es</option>
            <option value='en' <?php selected( $options['wpsenpy_sent_lng'], 'en' ); ?>>en</option>
            <option value='auto' <?php selected( $options['wpsenpy_sent_lng'], 'auto' ); ?>>auto</option>
            <option value='it' <?php selected( $options['wpsenpy_sent_lng'], 'it' ); ?>>it</option>
            <option value='fr' <?php selected( $options['wpsenpy_sent_lng'], 'fr' ); ?>>fr</option>
        </select>
		<?php
	} else {
		?>
        <select name='wpsenpy_settings[wpsenpy_sent_lng]'>
            <option value='en' <?php selected( $options['wpsenpy_sent_lng'], 'en' ); ?>>en</option>
        </select>
		<?php
	}
}

function wpsenpy_emt_field_render() {

	$options = get_option( 'wpsenpy_settings' );
	?>
    <select name='wpsenpy_settings[wpsenpy_emt_field]'>
        <option value='emotion-anew' <?php selected( $options['wpsenpy_emt_field'], 'emotion-anew' ); ?>>emotion-anew
        </option>
        <option value='emotion-wnaffect' <?php selected( $options['wpsenpy_emt_field'], 'emotion-wnaffect' ); ?>>
            emotion-wnaffect
        </option>
        <!-- <option value='3' <?php //selected( $options['wpsenpy_emt_field'], 3 ); ?>>affect</option> -->
    </select>

	<?php

}

function wpsenpy_emt_lng_field_render() {

	$options = get_option( 'wpsenpy_settings' );

	$wpsenpy_emt_field = $options['wpsenpy_emt_field'];
	echo '<p>' . __( "Select the Language Parameter for the Emotion Plugin", 'wpsenpy-sentemt' ) . '</p>';

	if ( $wpsenpy_emt_field == 'emotion-anew' ) {
		?>
        <select name='wpsenpy_settings[wpsenpy_emt_lng]'>
            <option value='es' <?php selected( $options['wpsenpy_emt_lng'], 'es' ); ?>>es</option>
            <option value='en' <?php selected( $options['wpsenpy_emt_lng'], 'en' ); ?>>en</option>
        </select>
		<?php
	} elseif ( $wpsenpy_emt_field == 'emotion-wnaffect' ) {
		?>
        <select name='wpsenpy_settings[wpsenpy_emt_lng]'>
            <option value='en' selected>en</option>
        </select>
		<?php
	} else {
		?>
        <select name='wpsenpy_settings[wpsenpy_emt_lng]'>
            <option value='en' <?php selected( $options['wpsenpy_emt_lng'], 'en' ); ?>>en</option>
        </select>
		<?php
	}
}

function wpsenpy_settings_section_callback() {

	echo __( 'Select between the different options for configuring the parameters', 'wpsenpy-sentemt' );

}

function wpsenpy_options_page() {

	?>
    <form action='options.php' method='post'>

        <h2><?php _e( 'Sentiment and Emotion Analysis Plugin', 'wpsenpy-sentemt' ); ?></h2>

		<?php
		settings_fields( 'wpsenpy_settings' );
		do_settings_sections( 'sentiment_and_emotion_analysis_plugin_menu' );
		submit_button();
		?>

    </form>
	<?php

}

function wpsenpy_sent_cmnt_view() {

	$options = get_option( 'wpsenpy_settings' );
	?>
    <input type='checkbox'
           name='wpsenpy_settings[wpsenpy_cmnt_sent_view]' <?php echo checked( $options['wpsenpy_cmnt_sent_view'], 'on' ); ?>
    >

	<?php

}

function wpsenpy_emt_cmnt_view() {

	$options = get_option( 'wpsenpy_settings' );
	?>
    <input type='checkbox'
           name='wpsenpy_settings[wpsenpy_cmnt_emt_view]' <?php checked( $options['wpsenpy_cmnt_emt_view'], 'on' ); ?>
    >

	<?php
}

function wpsenpy_cmnt_layout_section_callback() {

	echo __( '', 'wpsenpy-sentemt' );

}

function wpsenpy_options_page_comment_layout() {

	?>
    <form action='options.php' method='post'>

        <h2> <?php _e( "Visualization Configuration for the Comments", 'wpsenpy-sentemt' ); ?> </h2>

		<?php
		settings_fields( 'wpsenpy_settings' );
		do_settings_sections( 'sentiment_and_emotion_analysis_plugin_menu_comment_layout' );
		submit_button();
		?>

    </form>
	<?php

}

function wpsenpy_options_page_about() {
	?>
    <h3> <?php _e( "Available Plugins", 'wpsenpy-sentemt' ); ?></h3>

    <h4> <?php _e( "Sentiment Plugins", 'wpsenpy-sentemt' ); ?></h4>
    <ul>
        <li>
            <b><em>sentiment-140:</em></b>
			<?php
			_e( 'Sentiment classifier using rule-based classification for English and Spanish. This plugin uses sentiment140 data to perform classification. For more information:', 'wpsenpy-sentemt' );
			?>
            <a href="http://help.sentiment140.com/for-students/">http://help.sentiment140.com/for-students/</a>
        </li>
        <li>
            <b><em>sentiment-tass:</em></b>
			<?php
			_e( "Sentiment classifier using rule-based classification based on English and Spanish", 'wpsenpy-sentemt' );
			?>
        </li>
        <li>
            <b><em>sentiment-meaningCloud:</em></b>
			<?php
			_e( "Sentiment analysis with meaningCloud service. To use this plugin, you need to obtain an API key from meaningCloud signing up here:", 'wpsenpy-sentemt' );
			?>
            <a href="https://www.meaningcloud.com/developer/login">https://www.meaningcloud.com/developer/login.</a>
			<?php
			_e( "When you had obtained the meaningCloud API Key, you have to provide it to the plugin, using param apiKey. Example request:", 'wpsenpy-sentemt' );
			?>
            <a href="http://senpy.cluster.gsi.dit.upm.es/api/?algo=meaningCloud&language=en&apiKey=&input=I%20love%20Madrid.">http://senpy.cluster.gsi.dit.upm.es/api/?algo=meaningCloud&language=en&apiKey=&input=I%20love%20Madrid.</a>
        </li>
        <li>
            <b><em>sentiment-vader:</em></b>
			<?php
			_e( "Sentiment classifier using vaderSentiment module. Params accepted: Language: {en, es}. The output uses Marl ontology developed at GSI UPM for semantic web.", 'wpsenpy-sentemt' );
			?>
        </li>
        <li>
            <b><em>sentiment-basic:</em></b>
			<?php
			_e( "Sentiment classifier using rule-based classification for Spanish. Based on english to spanish translation and SentiWordNet sentiment knowledge. This is a demo plugin that uses only some features from the TASS 2015 classifier. To use the entirely functional classifier you can use the service in:", 'wpsenpy-sentemt' );
			?>
            <a href="http://senpy.cluster.gsi.dit.upm.es">http://senpy.cluster.gsi.dit.upm.es.</a>
        </li>
    </ul>

    <h4> <?php _e( "Emotion Plugins", 'wpsenpy-sentemt' ); ?> </h4>
    <ul>
        <li>
            <b><em>emotion-wnaffect:</em></b>
			<?php
			_e( "Emotion classifier using WordNet-Affect to calculate the percentage of each emotion. This plugin classifies among 6 emotions: anger,fear,disgust,joy,sadness or neutral. The only available language is English (en)", 'wpsenpy-sentemt' );
			?>
        </li>
        <li>
            <b><em>emotion-anew:</em></b>
			<?php
			_e( "This plugin consists on an emotion classifier using ANEW lexicon dictionary to calculate VAD (valence-arousal-dominance) of the sentence and determinate which emotion is closer to this value. Each emotion has a centroid, calculated according to this article:", 'wpsenpy-sentemt' );
			?>
            <a href="http://www.aclweb.org/anthology/W10-0208">http://www.aclweb.org/anthology/W10-0208</a>.
			<?php
			_e( "The plugin is going to look for the words in the sentence that appear in the ANEW dictionary and calculate the average VAD score for the sentence. Once this score is calculated, it is going to seek the emotion that is closest to this value.", 'wpsenpy-sentemt' );
			?>
        </li>
    </ul>
	<?php
}

?>
